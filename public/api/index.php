<?php

/**
 * ModernQuiz Secure API
 *
 * All endpoints (except auth routes) require authentication via session token
 * Session token must be provided in Authorization header: "Bearer <token>"
 * or in cookie: session_token
 */

// Security Headers (except CORS - kept open as requested)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\AuthMiddleware;
use ModernQuiz\Core\Database;
use ModernQuiz\Modules\Auth\Login;
use ModernQuiz\Modules\Auth\Register;
use ModernQuiz\Modules\Auth\PasswordReset;
use ModernQuiz\Modules\Quiz\QuizEngine;
use ModernQuiz\Modules\Shop\ShopSystem;
use ModernQuiz\Modules\Jackpot\JackpotSystem;
use ModernQuiz\Modules\Leaderboard\LeaderboardSystem;
use ModernQuiz\Modules\Statistics\StatisticsManager;

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$path = trim($path, '/');
$segments = explode('/', $path);

// Initialize auth middleware
$authMiddleware = new AuthMiddleware();

// Error handler
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

/**
 * Send JSON response and exit
 */
function sendResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send error response
 */
function sendError(string $message, int $statusCode = 400): void
{
    sendResponse([
        'success' => false,
        'error' => $message
    ], $statusCode);
}

/**
 * Get and validate JSON input
 */
function getJsonInput(): array
{
    $input = file_get_contents('php://input');
    if (empty($input)) {
        return [];
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON: ' . json_last_error_msg(), 400);
    }

    return $data;
}

/**
 * Validate integer parameter
 */
function validateInt($value, string $name, int $min = null, int $max = null): int
{
    $intValue = filter_var($value, FILTER_VALIDATE_INT);
    if ($intValue === false) {
        sendError("Invalid $name: must be an integer");
    }
    if ($min !== null && $intValue < $min) {
        sendError("Invalid $name: must be at least $min");
    }
    if ($max !== null && $intValue > $max) {
        sendError("Invalid $name: must be at most $max");
    }
    return $intValue;
}

/**
 * Sanitize output to prevent XSS
 */
function sanitizeOutput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    if (is_string($data)) {
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return $data;
}

// Router
try {
    $response = [];
    $authenticatedUserId = null;

    // ========================================
    // AUTHENTICATION ENDPOINTS (PUBLIC)
    // ========================================

    if ($segments[0] === 'auth') {

        // POST /auth/register
        if ($method === 'POST' && $segments[1] === 'register') {
            $data = getJsonInput();

            $username = trim($data['username'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';
            $referralCode = trim($data['referral_code'] ?? '') ?: null;

            if (empty($username) || empty($email) || empty($password)) {
                sendError('Username, E-Mail und Passwort sind erforderlich');
            }

            $register = new Register();
            $result = $register->register($username, $email, $password, $referralCode);

            sendResponse($result, $result['success'] ? 201 : 400);
        }

        // POST /auth/login
        elseif ($method === 'POST' && $segments[1] === 'login') {
            $data = getJsonInput();

            $identifier = trim($data['identifier'] ?? '');
            $password = $data['password'] ?? '';

            if (empty($identifier) || empty($password)) {
                sendError('Benutzername/E-Mail und Passwort sind erforderlich');
            }

            $login = new Login();
            $result = $login->attemptLogin($identifier, $password);

            // Set session token in cookie (httpOnly, secure in production)
            if ($result['success'] && $result['session_token']) {
                $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                setcookie(
                    'session_token',
                    $result['session_token'],
                    [
                        'expires' => time() + (30 * 24 * 60 * 60), // 30 days
                        'path' => '/',
                        'secure' => $isSecure,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
            }

            sendResponse($result, $result['success'] ? 200 : 401);
        }

        // POST /auth/logout
        elseif ($method === 'POST' && $segments[1] === 'logout') {
            try {
                $authenticatedUserId = $authMiddleware->authenticate();
                $sessionToken = $_COOKIE['session_token'] ?? null;

                if ($sessionToken) {
                    $login = new Login();
                    $login->logout($sessionToken);

                    // Clear cookie
                    setcookie('session_token', '', time() - 3600, '/');
                }

                sendResponse(['success' => true, 'message' => 'Erfolgreich abgemeldet']);
            } catch (Exception $e) {
                sendError($e->getMessage(), 401);
            }
        }

        // POST /auth/forgot-password
        elseif ($method === 'POST' && $segments[1] === 'forgot-password') {
            $data = getJsonInput();
            $email = trim($data['email'] ?? '');

            if (empty($email)) {
                sendError('E-Mail ist erforderlich');
            }

            $passwordReset = new PasswordReset();
            $result = $passwordReset->requestReset($email);

            // Always return success (don't reveal if email exists)
            sendResponse(['success' => true, 'message' => 'Falls diese E-Mail registriert ist, wurde ein Reset-Link gesendet']);
        }

        // POST /auth/reset-password
        elseif ($method === 'POST' && $segments[1] === 'reset-password') {
            $data = getJsonInput();
            $token = trim($data['token'] ?? '');
            $newPassword = $data['new_password'] ?? '';

            if (empty($token) || empty($newPassword)) {
                sendError('Token und neues Passwort sind erforderlich');
            }

            $passwordReset = new PasswordReset();
            $result = $passwordReset->resetPassword($token, $newPassword);

            sendResponse([
                'success' => $result,
                'message' => $result ? 'Passwort erfolgreich zurückgesetzt' : 'Ungültiger oder abgelaufener Token'
            ], $result ? 200 : 400);
        }

        // GET /auth/verify-email
        elseif ($method === 'GET' && $segments[1] === 'verify-email') {
            $token = trim($_GET['token'] ?? '');

            if (empty($token)) {
                sendError('Token ist erforderlich');
            }

            $register = new Register();
            $result = $register->verifyEmail($token);

            sendResponse([
                'success' => $result,
                'message' => $result ? 'E-Mail erfolgreich bestätigt' : 'Ungültiger oder abgelaufener Token'
            ], $result ? 200 : 400);
        }

        else {
            sendError('Auth endpoint not found', 404);
        }
    }

    // ========================================
    // PROTECTED ENDPOINTS - REQUIRE AUTH
    // ========================================

    // Authenticate user for all non-auth routes
    try {
        $authenticatedUserId = $authMiddleware->authenticate();
    } catch (Exception $e) {
        sendError($e->getMessage(), 401);
    }

    // Initialize systems
    $pdo = Database::getInstance()->getConnection();

    // ========================================
    // QUIZ ENDPOINTS
    // ========================================

    if ($segments[0] === 'quiz') {
        $quizEngine = new QuizEngine();

        // POST /quiz/start
        if ($method === 'POST' && $segments[1] === 'start') {
            $data = getJsonInput();
            $categoryId = isset($data['category_id']) ? validateInt($data['category_id'], 'category_id', 1) : null;

            $response = $quizEngine->startSession($authenticatedUserId, $categoryId);
            sendResponse($response);
        }

        // GET /quiz/question
        elseif ($method === 'GET' && $segments[1] === 'question') {
            $categoryId = isset($_GET['category_id']) ? validateInt($_GET['category_id'], 'category_id', 1) : null;
            $excludeIds = isset($_GET['exclude']) ? array_map('intval', explode(',', $_GET['exclude'])) : [];

            $response = $quizEngine->getRandomQuestion($categoryId, $excludeIds);
            sendResponse($response);
        }

        // POST /quiz/answer
        elseif ($method === 'POST' && $segments[1] === 'answer') {
            $data = getJsonInput();

            $sessionId = validateInt($data['session_id'] ?? 0, 'session_id', 1);
            $questionId = validateInt($data['question_id'] ?? 0, 'question_id', 1);
            $answerId = validateInt($data['answer_id'] ?? 0, 'answer_id', 1);
            $timeTaken = validateInt($data['time_taken'] ?? 0, 'time_taken', 0);
            $powerupUsed = isset($data['powerup_used']) ? validateInt($data['powerup_used'], 'powerup_used', 1) : null;

            // Verify session belongs to authenticated user
            $stmt = $pdo->prepare("SELECT user_id FROM quiz_sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$session || (int)$session['user_id'] !== $authenticatedUserId) {
                sendError('Unauthorized: session does not belong to you', 403);
            }

            $response = $quizEngine->submitAnswer($sessionId, $questionId, $answerId, $timeTaken, $powerupUsed);

            // Check jackpot if answer correct
            if ($response['correct']) {
                $jackpotSystem = new JackpotSystem();
                $jackpotResults = $jackpotSystem->incrementJackpots($authenticatedUserId, $questionId, $sessionId);
                $response['jackpots'] = $jackpotResults;
            }

            sendResponse($response);
        }

        // POST /quiz/end
        elseif ($method === 'POST' && $segments[1] === 'end') {
            $data = getJsonInput();
            $sessionId = validateInt($data['session_id'] ?? 0, 'session_id', 1);

            // Verify session belongs to authenticated user
            $stmt = $pdo->prepare("SELECT user_id FROM quiz_sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$session || (int)$session['user_id'] !== $authenticatedUserId) {
                sendError('Unauthorized: session does not belong to you', 403);
            }

            $response = $quizEngine->endSession($sessionId);
            sendResponse($response);
        }

        // GET /quiz/categories (public, but allow authed access too)
        elseif ($method === 'GET' && $segments[1] === 'categories') {
            $response = $quizEngine->getCategories();
            sendResponse($response);
        }

        else {
            sendError('Quiz endpoint not found', 404);
        }
    }

    // ========================================
    // SHOP ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'shop') {
        $shopSystem = new ShopSystem();

        // GET /shop/powerups
        if ($method === 'GET' && $segments[1] === 'powerups') {
            $response = $shopSystem->getPowerups();
            sendResponse($response);
        }

        // GET /shop/inventory
        elseif ($method === 'GET' && $segments[1] === 'inventory') {
            // Return authenticated user's inventory
            $response = $shopSystem->getUserInventory($authenticatedUserId);
            sendResponse($response);
        }

        // POST /shop/purchase
        elseif ($method === 'POST' && $segments[1] === 'purchase') {
            $data = getJsonInput();
            $powerupId = validateInt($data['powerup_id'] ?? 0, 'powerup_id', 1);
            $quantity = validateInt($data['quantity'] ?? 1, 'quantity', 1, 100);
            $currency = $data['currency'] ?? 'auto'; // 'coins', 'bonus_coins', or 'auto'

            $response = $shopSystem->purchasePowerup($authenticatedUserId, $powerupId, $quantity, $currency);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // POST /shop/use
        elseif ($method === 'POST' && $segments[1] === 'use') {
            $data = getJsonInput();
            $powerupId = validateInt($data['powerup_id'] ?? 0, 'powerup_id', 1);

            $response = $shopSystem->usePowerup($authenticatedUserId, $powerupId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        else {
            sendError('Shop endpoint not found', 404);
        }
    }

    // ========================================
    // JACKPOT ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'jackpots') {
        $jackpotSystem = new JackpotSystem();

        // GET /jackpots
        if ($method === 'GET' && !isset($segments[1])) {
            $response = $jackpotSystem->getAllJackpots();
            sendResponse($response);
        }

        // GET /jackpots/winners
        elseif ($method === 'GET' && $segments[1] === 'winners') {
            $limit = validateInt($_GET['limit'] ?? 10, 'limit', 1, 100);
            $response = $jackpotSystem->getRecentWinners($limit);
            sendResponse($response);
        }

        else {
            sendError('Jackpot endpoint not found', 404);
        }
    }

    // ========================================
    // LEADERBOARD ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'leaderboard') {
        $leaderboardSystem = new LeaderboardSystem();

        if ($method === 'GET') {
            // GET /leaderboard/daily
            if (isset($segments[1]) && $segments[1] === 'daily') {
                $limit = validateInt($_GET['limit'] ?? 50, 'limit', 1, 200);
                $response = $leaderboardSystem->getDailyLeaderboard($limit);
                sendResponse($response);
            }

            // GET /leaderboard/weekly
            elseif (isset($segments[1]) && $segments[1] === 'weekly') {
                $limit = validateInt($_GET['limit'] ?? 50, 'limit', 1, 200);
                $response = $leaderboardSystem->getWeeklyLeaderboard($limit);
                sendResponse($response);
            }

            // GET /leaderboard/category
            elseif (isset($segments[1]) && $segments[1] === 'category') {
                $categoryId = validateInt($_GET['category_id'] ?? 0, 'category_id', 1);
                $limit = validateInt($_GET['limit'] ?? 50, 'limit', 1, 200);
                $response = $leaderboardSystem->getLeaderboardByCategory($categoryId, $limit);
                sendResponse($response);
            }

            // GET /leaderboard/user
            elseif (isset($segments[1]) && $segments[1] === 'user') {
                // Allow users to view their own ranking, or others if specified
                $userId = isset($_GET['user_id']) ? validateInt($_GET['user_id'], 'user_id', 1) : $authenticatedUserId;
                $response = $leaderboardSystem->getUserRanking($userId);
                sendResponse($response);
            }

            // GET /leaderboard (top players)
            else {
                $limit = validateInt($_GET['limit'] ?? 100, 'limit', 1, 500);
                $response = $leaderboardSystem->getTopPlayers($limit);
                sendResponse($response);
            }
        }
        else {
            sendError('Method not allowed', 405);
        }
    }

    // ========================================
    // USER STATS ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'user' && isset($segments[1]) && $segments[1] === 'stats') {
        $leaderboardSystem = new LeaderboardSystem();

        if ($method === 'GET') {
            // Allow viewing own stats or others if specified
            $userId = isset($_GET['user_id']) ? validateInt($_GET['user_id'], 'user_id', 1) : $authenticatedUserId;
            $response = $leaderboardSystem->getUserStats($userId);
            sendResponse($response);
        }
        else {
            sendError('Method not allowed', 405);
        }
    }

    // ========================================
    // STATISTICS ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'statistics') {
        $statsManager = new StatisticsManager($pdo);

        if ($method === 'GET') {
            // GET /statistics/dashboard
            if (isset($segments[1]) && $segments[1] === 'dashboard') {
                $response = $statsManager->getDashboardStats();
                sendResponse($response);
            }

            // GET /statistics/global
            elseif (isset($segments[1]) && $segments[1] === 'global') {
                $response = $statsManager->getGlobalStats();
                sendResponse($response);
            }

            // GET /statistics/users
            elseif (isset($segments[1]) && $segments[1] === 'users') {
                $response = $statsManager->getUserStats();
                sendResponse($response);
            }

            // GET /statistics/quizzes
            elseif (isset($segments[1]) && $segments[1] === 'quizzes') {
                $response = $statsManager->getQuizStats();
                sendResponse($response);
            }

            // GET /statistics/questions
            elseif (isset($segments[1]) && $segments[1] === 'questions') {
                $response = $statsManager->getQuestionStats();
                sendResponse($response);
            }

            // GET /statistics/multiplayer
            elseif (isset($segments[1]) && $segments[1] === 'multiplayer') {
                $response = $statsManager->getMultiplayerStats();
                sendResponse($response);
            }

            // GET /statistics/achievements
            elseif (isset($segments[1]) && $segments[1] === 'achievements') {
                $response = $statsManager->getAchievementStats();
                sendResponse($response);
            }

            // GET /statistics/trends
            elseif (isset($segments[1]) && $segments[1] === 'trends') {
                $days = validateInt($_GET['days'] ?? 30, 'days', 1, 365);
                $response = $statsManager->getTrendStats($days);
                sendResponse($response);
            }

            // GET /statistics/user/{userId}
            elseif (isset($segments[1]) && $segments[1] === 'user' && isset($segments[2])) {
                $userId = validateInt($segments[2], 'user_id', 1);
                $response = $statsManager->getUserDetailStats($userId);
                sendResponse($response);
            }

            else {
                sendError('Statistics endpoint not found', 404);
            }
        }
        else {
            sendError('Method not allowed', 405);
        }
    }

    // ========================================
    // USER PROFILE ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'user' && isset($segments[1]) && $segments[1] === 'profile') {

        if ($method === 'GET') {
            // GET /user/profile - get own profile
            $login = new Login();
            $sessionToken = $_COOKIE['session_token'] ?? null;

            if (!$sessionToken) {
                sendError('Session token not found', 401);
            }

            $user = $login->getCurrentUser($sessionToken);

            if (!$user) {
                sendError('User not found', 404);
            }

            // Don't expose sensitive fields
            unset($user['password_hash']);

            sendResponse(['success' => true, 'user' => $user]);
        }
        else {
            sendError('Method not allowed', 405);
        }
    }

    // ========================================
    // VOUCHER ENDPOINTS (User)
    // ========================================

    elseif ($segments[0] === 'vouchers') {
        $voucherManager = new \ModernQuiz\Modules\Voucher\VoucherManager($pdo);

        // POST /vouchers/redeem
        if ($method === 'POST' && $segments[1] === 'redeem') {
            $data = getJsonInput();

            if (!isset($data['code']) || empty(trim($data['code']))) {
                sendError('Gutscheincode ist erforderlich', 400);
            }

            // Get IP and User Agent
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            $response = $voucherManager->redeemVoucher(
                $authenticatedUserId,
                $data['code'],
                $ipAddress,
                $userAgent
            );

            sendResponse($response, $response['success'] ? 200 : 400);
        }
        else {
            sendError('Voucher endpoint not found', 404);
        }
    }

    // ========================================
    // ADMIN VOUCHER ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'admin' && $segments[1] === 'vouchers') {
        // Require admin role
        $authMiddleware->requireAdmin($authenticatedUserId);

        $voucherManager = new \ModernQuiz\Modules\Voucher\VoucherManager($pdo);

        // POST /admin/vouchers/create
        if ($method === 'POST' && (!isset($segments[2]) || $segments[2] === 'create')) {
            $data = getJsonInput();

            $response = $voucherManager->createVoucher($authenticatedUserId, $data);
            sendResponse($response, $response['success'] ? 201 : 400);
        }

        // GET /admin/vouchers
        elseif ($method === 'GET' && !isset($segments[2])) {
            $filters = [];

            if (isset($_GET['is_active'])) {
                $filters['is_active'] = (int)$_GET['is_active'];
            }

            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }

            $vouchers = $voucherManager->listVouchers($filters);
            sendResponse(['success' => true, 'vouchers' => $vouchers]);
        }

        // GET /admin/vouchers/{id}/stats
        elseif ($method === 'GET' && isset($segments[2]) && $segments[2] !== 'fraud-log' && isset($segments[3]) && $segments[3] === 'stats') {
            $voucherId = validateInt($segments[2], 'voucher_id', 1);
            $response = $voucherManager->getVoucherStats($voucherId);
            sendResponse($response, $response['success'] ? 200 : 404);
        }

        // DELETE /admin/vouchers/{id}
        elseif ($method === 'DELETE' && isset($segments[2])) {
            $voucherId = validateInt($segments[2], 'voucher_id', 1);
            $response = $voucherManager->deleteVoucher($voucherId, $authenticatedUserId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // GET /admin/vouchers/fraud-log
        elseif ($method === 'GET' && isset($segments[2]) && $segments[2] === 'fraud-log') {
            $filters = [];

            if (isset($_GET['user_id'])) {
                $filters['user_id'] = validateInt($_GET['user_id'], 'user_id', 1);
            }

            if (isset($_GET['is_suspicious'])) {
                $filters['is_suspicious'] = (bool)$_GET['is_suspicious'];
            }

            if (isset($_GET['admin_notified'])) {
                $filters['admin_notified'] = (bool)$_GET['admin_notified'];
            }

            $logs = $voucherManager->getFraudLog($filters);
            sendResponse(['success' => true, 'fraud_log' => $logs]);
        }

        else {
            sendError('Admin voucher endpoint not found', 404);
        }
    }

    // ========================================
    // BANK ENDPOINTS (User)
    // ========================================

    elseif ($segments[0] === 'bank') {
        $bankManager = new \ModernQuiz\Modules\Bank\BankManager($pdo);

        // POST /bank/deposit - Neue Einlage erstellen
        if ($method === 'POST' && $segments[1] === 'deposit') {
            $data = getJsonInput();

            $coins = validateInt($data['coins'] ?? 0, 'coins', 0);
            $bonusCoins = validateInt($data['bonus_coins'] ?? 0, 'bonus_coins', 0);

            $response = $bankManager->createDeposit($authenticatedUserId, $coins, $bonusCoins);
            sendResponse($response, $response['success'] ? 201 : 400);
        }

        // POST /bank/withdraw/{id}/early - Vorzeitige Auszahlung
        elseif ($method === 'POST' && isset($segments[2]) && $segments[3] === 'early') {
            $depositId = validateInt($segments[2], 'deposit_id', 1);
            $response = $bankManager->withdrawEarly($authenticatedUserId, $depositId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // POST /bank/withdraw/{id} - Normale Auszahlung (fällig)
        elseif ($method === 'POST' && isset($segments[2]) && $segments[1] === 'withdraw') {
            $depositId = validateInt($segments[2], 'deposit_id', 1);
            $response = $bankManager->withdrawMatured($authenticatedUserId, $depositId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // GET /bank/deposits - Eigene Einlagen
        elseif ($method === 'GET' && $segments[1] === 'deposits') {
            $filters = [];
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }

            $deposits = $bankManager->getUserDeposits($authenticatedUserId, $filters);
            sendResponse(['success' => true, 'deposits' => $deposits]);
        }

        // GET /bank/balance - Bank-Kontostand
        elseif ($method === 'GET' && $segments[1] === 'balance') {
            $balance = $bankManager->getUserBankBalance($authenticatedUserId);
            sendResponse(['success' => true, 'balance' => $balance]);
        }

        // GET /bank/statement - Kontoauszug
        elseif ($method === 'GET' && $segments[1] === 'statement') {
            $limit = validateInt($_GET['limit'] ?? 50, 'limit', 1, 100);
            $offset = validateInt($_GET['offset'] ?? 0, 'offset', 0);

            $coinManager = new \ModernQuiz\Modules\Coins\CoinManager($pdo);
            $transactions = $coinManager->getUserTransactions($authenticatedUserId, $limit, $offset);

            // Hole auch Bank-Transaktionen
            $stmt = $pdo->prepare("
                SELECT * FROM bank_transactions
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$authenticatedUserId, $limit, $offset]);
            $bankTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            sendResponse([
                'success' => true,
                'coin_transactions' => $transactions,
                'bank_transactions' => $bankTransactions
            ]);
        }

        else {
            sendError('Bank endpoint not found', 404);
        }
    }

    // ========================================
    // ADMIN BANK ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'admin' && $segments[1] === 'bank') {
        // Require admin role
        $authMiddleware->requireAdmin($authenticatedUserId);

        $bankManager = new \ModernQuiz\Modules\Bank\BankManager($pdo);

        // GET /admin/bank/deposits - Alle Einlagen
        if ($method === 'GET' && $segments[2] === 'deposits') {
            $filters = [];

            if (isset($_GET['user_id'])) {
                $filters['user_id'] = validateInt($_GET['user_id'], 'user_id', 1);
            }

            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }

            if (isset($_GET['is_locked'])) {
                $filters['is_locked'] = (bool)$_GET['is_locked'];
            }

            $deposits = $bankManager->getAllDeposits($filters);
            sendResponse(['success' => true, 'deposits' => $deposits]);
        }

        // PUT /admin/bank/deposits/{id}/lock - Einlage sperren
        elseif ($method === 'PUT' && isset($segments[3]) && $segments[4] === 'lock') {
            $depositId = validateInt($segments[3], 'deposit_id', 1);
            $data = getJsonInput();
            $reason = $data['reason'] ?? 'Keine Angabe';

            $response = $bankManager->lockDeposit($depositId, $authenticatedUserId, $reason);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // PUT /admin/bank/deposits/{id}/unlock - Einlage entsperren
        elseif ($method === 'PUT' && isset($segments[3]) && $segments[4] === 'unlock') {
            $depositId = validateInt($segments[3], 'deposit_id', 1);
            $response = $bankManager->unlockDeposit($depositId, $authenticatedUserId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // PUT /admin/bank/deposits/{id}/release - Sofort freigeben
        elseif ($method === 'PUT' && isset($segments[3]) && $segments[4] === 'release') {
            $depositId = validateInt($segments[3], 'deposit_id', 1);
            $response = $bankManager->releaseDeposit($depositId, $authenticatedUserId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        else {
            sendError('Admin bank endpoint not found', 404);
        }
    }

    // ========================================
    // REFERRAL ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'referral') {
        $referralManager = new \ModernQuiz\Modules\Referral\ReferralManager($pdo);

        // GET /referral/stats - User's referral statistics
        if ($method === 'GET' && $segments[1] === 'stats') {
            $stats = $referralManager->getReferralStats($authenticatedUserId);
            sendResponse(['success' => true, 'stats' => $stats]);
        }

        // GET /referral/referred-users - List of referred users
        elseif ($method === 'GET' && $segments[1] === 'referred-users') {
            $users = $referralManager->getReferredUsers($authenticatedUserId);
            sendResponse(['success' => true, 'referred_users' => $users]);
        }

        // GET /referral/earnings - Commission earnings history
        elseif ($method === 'GET' && $segments[1] === 'earnings') {
            $limit = validateInt($_GET['limit'] ?? 50, 'limit', 1, 100);
            $offset = validateInt($_GET['offset'] ?? 0, 'offset', 0);

            $earnings = $referralManager->getEarningsHistory($authenticatedUserId, $limit, $offset);
            sendResponse(['success' => true, 'earnings' => $earnings]);
        }

        // GET /referral/code - Get user's referral code
        elseif ($method === 'GET' && $segments[1] === 'code') {
            $stmt = $pdo->prepare("SELECT referral_code FROM users WHERE id = ?");
            $stmt->execute([$authenticatedUserId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            sendResponse([
                'success' => true,
                'referral_code' => $result['referral_code'] ?? null
            ]);
        }

        // POST /referral/generate-code - Generate new referral code
        elseif ($method === 'POST' && $segments[1] === 'generate-code') {
            $newCode = $referralManager->generateReferralCode($authenticatedUserId);

            // Update user's referral code
            $stmt = $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
            $stmt->execute([$newCode, $authenticatedUserId]);

            sendResponse([
                'success' => true,
                'referral_code' => $newCode,
                'message' => 'Neuer Referral-Code generiert'
            ]);
        }

        else {
            sendError('Referral endpoint not found', 404);
        }
    }

    // ========================================
    // ADMIN REFERRAL ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'admin' && $segments[1] === 'referral') {
        // Require admin role
        $authMiddleware->requireAdmin($authenticatedUserId);

        $referralManager = new \ModernQuiz\Modules\Referral\ReferralManager($pdo);

        // GET /admin/referral/top-referrers - Top referrers leaderboard
        if ($method === 'GET' && $segments[2] === 'top-referrers') {
            $limit = validateInt($_GET['limit'] ?? 10, 'limit', 1, 100);
            $topReferrers = $referralManager->getTopReferrers($limit);
            sendResponse(['success' => true, 'top_referrers' => $topReferrers]);
        }

        else {
            sendError('Admin referral endpoint not found', 404);
        }
    }

    // ========================================
    // ADMIN USER ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'admin' && $segments[1] === 'users') {
        // Require admin role
        $authMiddleware->requireAdmin($authenticatedUserId);

        $adminUserManager = new \ModernQuiz\Modules\Admin\AdminUserManager($pdo);

        // GET /admin/users - Alle User
        if ($method === 'GET' && !isset($segments[2])) {
            $filters = [];

            if (isset($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }

            if (isset($_GET['is_active'])) {
                $filters['is_active'] = (int)$_GET['is_active'];
            }

            if (isset($_GET['role'])) {
                $filters['role'] = $_GET['role'];
            }

            $users = $adminUserManager->getAllUsers($filters);
            sendResponse(['success' => true, 'users' => $users]);
        }

        // GET /admin/users/{id} - User-Details
        elseif ($method === 'GET' && isset($segments[2]) && is_numeric($segments[2])) {
            $userId = validateInt($segments[2], 'user_id', 1);
            $user = $adminUserManager->getUserDetails($userId);

            if ($user) {
                sendResponse(['success' => true, 'user' => $user]);
            } else {
                sendError('User nicht gefunden', 404);
            }
        }

        // PUT /admin/users/{id}/lock - User sperren
        elseif ($method === 'PUT' && isset($segments[2]) && $segments[3] === 'lock') {
            $userId = validateInt($segments[2], 'user_id', 1);
            $data = getJsonInput();
            $reason = $data['reason'] ?? 'Keine Angabe';

            $response = $adminUserManager->lockUser($userId, $authenticatedUserId, $reason);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // PUT /admin/users/{id}/unlock - User entsperren
        elseif ($method === 'PUT' && isset($segments[2]) && $segments[3] === 'unlock') {
            $userId = validateInt($segments[2], 'user_id', 1);
            $response = $adminUserManager->unlockUser($userId, $authenticatedUserId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // PUT /admin/users/{id}/email - Email ändern
        elseif ($method === 'PUT' && isset($segments[2]) && $segments[3] === 'email') {
            $userId = validateInt($segments[2], 'user_id', 1);
            $data = getJsonInput();

            if (!isset($data['email']) || empty($data['email'])) {
                sendError('Email ist erforderlich', 400);
            }

            $response = $adminUserManager->changeUserEmail($userId, $authenticatedUserId, $data['email']);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // PUT /admin/users/{id}/password - Passwort ändern
        elseif ($method === 'PUT' && isset($segments[2]) && $segments[3] === 'password') {
            $userId = validateInt($segments[2], 'user_id', 1);
            $data = getJsonInput();

            if (!isset($data['password']) || empty($data['password'])) {
                sendError('Passwort ist erforderlich', 400);
            }

            $response = $adminUserManager->changeUserPassword($userId, $authenticatedUserId, $data['password']);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // GET /admin/users/{id}/statement - Kontoauszug
        elseif ($method === 'GET' && isset($segments[2]) && $segments[3] === 'statement') {
            $userId = validateInt($segments[2], 'user_id', 1);
            $limit = validateInt($_GET['limit'] ?? 50, 'limit', 1, 100);
            $offset = validateInt($_GET['offset'] ?? 0, 'offset', 0);

            $statement = $adminUserManager->getUserBankStatement($userId, $limit, $offset);
            sendResponse(['success' => true, 'statement' => $statement]);
        }

        // GET /admin/actions - Admin-Aktionen Log
        elseif ($method === 'GET' && $segments[2] === 'actions') {
            $filters = [];

            if (isset($_GET['admin_user_id'])) {
                $filters['admin_user_id'] = validateInt($_GET['admin_user_id'], 'admin_user_id', 1);
            }

            if (isset($_GET['target_user_id'])) {
                $filters['target_user_id'] = validateInt($_GET['target_user_id'], 'target_user_id', 1);
            }

            if (isset($_GET['action_type'])) {
                $filters['action_type'] = $_GET['action_type'];
            }

            $actions = $adminUserManager->getAdminActions($filters);
            sendResponse(['success' => true, 'actions' => $actions]);
        }

        // PUT /admin/users/{id}/role - Rolle ändern
        elseif ($method === 'PUT' && isset($segments[2]) && $segments[3] === 'role') {
            $userId = validateInt($segments[2], 'user_id', 1);
            $data = getJsonInput();

            if (!isset($data['role']) || empty($data['role'])) {
                sendError('Rolle ist erforderlich', 400);
            }

            $response = $adminUserManager->changeUserRole($userId, $authenticatedUserId, $data['role']);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // DELETE /admin/users/{id} - User löschen (GDPR)
        elseif ($method === 'DELETE' && isset($segments[2]) && !isset($segments[3])) {
            $userId = validateInt($segments[2], 'user_id', 1);
            $data = getJsonInput();
            $reason = $data['reason'] ?? 'Admin deletion';

            $response = $adminUserManager->deleteUser($userId, $authenticatedUserId, $reason);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // POST /admin/users/{id}/logout-all - Alle Sessions beenden
        elseif ($method === 'POST' && isset($segments[2]) && $segments[3] === 'logout-all') {
            $userId = validateInt($segments[2], 'user_id', 1);

            $response = $adminUserManager->logoutAllSessions($userId, $authenticatedUserId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // PUT /admin/users/{id}/reset-2fa - 2FA zurücksetzen
        elseif ($method === 'PUT' && isset($segments[2]) && $segments[3] === 'reset-2fa') {
            $userId = validateInt($segments[2], 'user_id', 1);

            $response = $adminUserManager->reset2FA($userId, $authenticatedUserId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // POST /admin/users/batch-lock - Mehrere User sperren
        elseif ($method === 'POST' && $segments[2] === 'batch-lock') {
            $data = getJsonInput();

            if (!isset($data['user_ids']) || !is_array($data['user_ids'])) {
                sendError('user_ids Array ist erforderlich', 400);
            }

            $reason = $data['reason'] ?? 'Batch lock';
            $response = $adminUserManager->batchLockUsers($data['user_ids'], $authenticatedUserId, $reason);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        // POST /admin/users/batch-unlock - Mehrere User entsperren
        elseif ($method === 'POST' && $segments[2] === 'batch-unlock') {
            $data = getJsonInput();

            if (!isset($data['user_ids']) || !is_array($data['user_ids'])) {
                sendError('user_ids Array ist erforderlich', 400);
            }

            $response = $adminUserManager->batchUnlockUsers($data['user_ids'], $authenticatedUserId);
            sendResponse($response, $response['success'] ? 200 : 400);
        }

        else {
            sendError('Admin user endpoint not found', 404);
        }
    }

    // ========================================
    // ADMIN DASHBOARD ENDPOINTS
    // ========================================

    elseif ($segments[0] === 'admin' && $segments[1] === 'dashboard') {
        // Require admin role
        $authMiddleware->requireAdmin($authenticatedUserId);

        $adminUserManager = new \ModernQuiz\Modules\Admin\AdminUserManager($pdo);

        // GET /admin/dashboard/stats - Dashboard Statistiken
        if ($method === 'GET' && $segments[2] === 'stats') {
            $stats = $adminUserManager->getDashboardStats();
            sendResponse(['success' => true, 'stats' => $stats]);
        }

        else {
            sendError('Admin dashboard endpoint not found', 404);
        }
    }

    // ========================================
    // 404 - ENDPOINT NOT FOUND
    // ========================================

    else {
        sendError('Endpoint not found', 404);
    }

} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

    // In debug mode, show detailed error
    $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

    if ($debug) {
        sendError($e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine(), 500);
    } else {
        sendError('Internal server error', 500);
    }
}

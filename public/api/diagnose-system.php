<?php
/**
 * Comprehensive diagnostic tool for post-login issues
 * Tests: Profile, Admin access, Quiz questions
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\Database;
use ModernQuiz\Core\SessionManager;
use ModernQuiz\Core\AuthMiddleware;

try {
    $db = Database::getInstance()->getConnection();

    $report = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // ==========================================
    // TEST 1: Check session from cookie/header
    // ==========================================

    $sessionToken = $_COOKIE['session_token'] ?? null;
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    if (!$sessionToken && $authHeader && preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
        $sessionToken = $matches[1];
    }

    $report['test_1_session_token'] = [
        'has_cookie' => isset($_COOKIE['session_token']),
        'has_auth_header' => !empty($authHeader),
        'session_token_found' => !empty($sessionToken),
        'token_preview' => $sessionToken ? substr($sessionToken, 0, 20) . '...' : null
    ];

    // ==========================================
    // TEST 2: Validate session and get user
    // ==========================================

    if ($sessionToken) {
        $sessionManager = new SessionManager($db);
        $sessionData = $sessionManager->validateSession($sessionToken);

        if ($sessionData) {
            $report['test_2_session_validation'] = [
                'success' => true,
                'user_id' => $sessionData['user_id'],
                'username' => $sessionData['username'],
                'email' => $sessionData['email']
            ];

            // Get full user data
            $stmt = $db->prepare("
                SELECT id, username, email, role, is_admin, coins, points, level,
                       email_verified, is_active, created_at
                FROM users
                WHERE id = ?
            ");
            $stmt->execute([$sessionData['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $report['test_3_user_profile'] = [
                'success' => true,
                'user_data' => $user
            ];

        } else {
            $report['test_2_session_validation'] = [
                'success' => false,
                'error' => 'Session validation failed',
                'reason' => 'Session token invalid or expired'
            ];
        }
    } else {
        $report['test_2_session_validation'] = [
            'success' => false,
            'error' => 'No session token provided'
        ];
    }

    // ==========================================
    // TEST 4: Check admin role
    // ==========================================

    if (isset($user)) {
        $report['test_4_admin_check'] = [
            'is_admin' => (bool)$user['is_admin'],
            'role' => $user['role'],
            'should_see_admin_menu' => $user['role'] === 'admin' || (bool)$user['is_admin']
        ];
    }

    // ==========================================
    // TEST 5: Check quiz categories
    // ==========================================

    $stmt = $db->query("SELECT COUNT(*) as count FROM quiz_categories");
    $categoryCount = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT * FROM quiz_categories LIMIT 5");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $report['test_5_quiz_categories'] = [
        'total_count' => (int)$categoryCount['count'],
        'has_categories' => (int)$categoryCount['count'] > 0,
        'sample_categories' => $categories
    ];

    // ==========================================
    // TEST 6: Check quiz questions
    // ==========================================

    $stmt = $db->query("SELECT COUNT(*) as count FROM quiz_questions");
    $questionCount = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->query("
        SELECT q.id, q.question, q.difficulty, c.name as category_name
        FROM quiz_questions q
        LEFT JOIN quiz_categories c ON q.category_id = c.id
        LIMIT 5
    ");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $report['test_6_quiz_questions'] = [
        'total_count' => (int)$questionCount['count'],
        'has_questions' => (int)$questionCount['count'] > 0,
        'sample_questions' => $questions
    ];

    // ==========================================
    // TEST 7: Check quiz answers
    // ==========================================

    $stmt = $db->query("SELECT COUNT(*) as count FROM quiz_answers");
    $answerCount = $stmt->fetch(PDO::FETCH_ASSOC);

    $report['test_7_quiz_answers'] = [
        'total_count' => (int)$answerCount['count'],
        'has_answers' => (int)$answerCount['count'] > 0
    ];

    // ==========================================
    // TEST 8: API Endpoints accessibility
    // ==========================================

    $report['test_8_api_endpoints'] = [
        'profile_endpoint' => '/api/user/profile',
        'quiz_categories_endpoint' => '/api/quiz/categories',
        'quiz_question_endpoint' => '/api/quiz/question',
        'note' => 'Try calling these endpoints with the session token'
    ];

    // ==========================================
    // DIAGNOSIS & RECOMMENDATIONS
    // ==========================================

    $issues = [];
    $recommendations = [];

    if (!isset($report['test_2_session_validation']['success']) || !$report['test_2_session_validation']['success']) {
        $issues[] = 'Session validation failed - profile will not work';
        $recommendations[] = 'Check if session cookie is being sent with requests';
        $recommendations[] = 'Check browser console for CORS errors';
    }

    if (isset($user) && $user['role'] === 'admin' && !$report['test_4_admin_check']['should_see_admin_menu']) {
        $issues[] = 'Admin user but admin menu logic might be wrong';
        $recommendations[] = 'Check frontend admin menu visibility logic';
    }

    if ((int)$categoryCount['count'] === 0) {
        $issues[] = 'No quiz categories in database';
        $recommendations[] = 'Import database_setup.sql to add demo categories';
    }

    if ((int)$questionCount['count'] === 0) {
        $issues[] = 'No quiz questions in database';
        $recommendations[] = 'Import database_setup.sql to add demo questions';
    }

    if ((int)$answerCount['count'] === 0) {
        $issues[] = 'No quiz answers in database';
        $recommendations[] = 'Import database_setup.sql to add demo answers';
    }

    $report['diagnosis'] = [
        'issues_found' => $issues,
        'recommendations' => $recommendations,
        'next_steps' => empty($issues) ? ['All checks passed! Issues might be in frontend.'] : $recommendations
    ];

    echo json_encode($report, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString())
    ], JSON_PRETTY_PRINT);
}

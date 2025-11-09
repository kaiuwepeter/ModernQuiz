<?php
/**
 * Direct login test - bypasses frontend
 * Tests the complete login flow and shows detailed results
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Modules\Auth\Login;
use ModernQuiz\Core\Database;
use ModernQuiz\Core\SessionManager;

try {
    $db = Database::getInstance()->getConnection();

    // Test 1: Check admin user in database
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode([
            'success' => false,
            'error' => 'Admin user not found in database'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Test 2: Verify password hash
    $passwordTest = password_verify('admin123', $admin['password_hash']);

    // Test 3: Attempt login using Login class
    $login = new Login();
    $loginResult = $login->attemptLogin('admin', 'admin123');

    // Test 4: Check if session was created
    $sessionToken = $loginResult['session_token'] ?? null;
    $sessionDetails = null;

    if ($sessionToken) {
        $sessionManager = new SessionManager($db);
        $sessionDetails = $sessionManager->validateSession($sessionToken);
    }

    // Test 5: Check for failed login attempts (brute force protection)
    $stmt = $db->prepare("
        SELECT COUNT(*) as attempt_count, MAX(attempted_at) as last_attempt
        FROM login_attempts
        WHERE ip_address = ?
        AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt->execute([$clientIp]);
    $attemptInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Compile detailed report
    $report = [
        'success' => $loginResult['success'],
        'timestamp' => date('Y-m-d H:i:s'),
        'test_1_database_user' => [
            'found' => true,
            'username' => $admin['username'],
            'email' => $admin['email'],
            'is_active' => (bool)$admin['is_active'],
            'email_verified' => (bool)$admin['email_verified'],
            'role' => $admin['role'] ?? null
        ],
        'test_2_password_verification' => [
            'password_hash' => substr($admin['password_hash'], 0, 30) . '...',
            'test_password' => 'admin123',
            'verification_result' => $passwordTest ? 'PASS ✓' : 'FAIL ✗',
            'matches' => $passwordTest
        ],
        'test_3_login_attempt' => [
            'success' => $loginResult['success'],
            'message' => $loginResult['message'],
            'session_token' => $sessionToken ? substr($sessionToken, 0, 20) . '...' : null,
            'user_data' => $loginResult['user'] ?? null
        ],
        'test_4_session_validation' => $sessionDetails ? [
            'session_valid' => true,
            'session_id' => $sessionDetails['id'],
            'user_id' => $sessionDetails['user_id'],
            'device_hash' => substr($sessionDetails['device_hash'], 0, 16) . '...',
            'created_at' => $sessionDetails['created_at'],
            'expires_at' => $sessionDetails['expires_at']
        ] : [
            'session_valid' => false,
            'reason' => 'No session token returned from login'
        ],
        'test_5_brute_force_check' => [
            'client_ip' => $clientIp,
            'failed_attempts_last_15min' => (int)$attemptInfo['attempt_count'],
            'last_attempt' => $attemptInfo['last_attempt'],
            'is_blocked' => (int)$attemptInfo['attempt_count'] >= 5,
            'max_attempts' => 5
        ],
        'full_login_response' => $loginResult
    ];

    // Add error analysis if login failed
    if (!$loginResult['success']) {
        $report['error_analysis'] = [];

        if (!$passwordTest) {
            $report['error_analysis'][] = 'Password hash verification failed';
        }
        if (!$admin['email_verified']) {
            $report['error_analysis'][] = 'Email not verified';
        }
        if (!$admin['is_active']) {
            $report['error_analysis'][] = 'Account not active';
        }
        if ((int)$attemptInfo['attempt_count'] >= 5) {
            $report['error_analysis'][] = 'IP blocked due to too many failed attempts';
        }

        if (empty($report['error_analysis'])) {
            $report['error_analysis'][] = 'Unknown error - all checks passed but login still failed';
        }
    }

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

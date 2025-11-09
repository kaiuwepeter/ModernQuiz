<?php
/**
 * Simple session creation test
 * Tests only session creation without full login flow
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\Database;
use ModernQuiz\Core\SessionManager;

try {
    $db = Database::getInstance()->getConnection();

    // Test 1: Check if user exists
    $stmt = $db->prepare("SELECT id, username FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode([
            'success' => false,
            'error' => 'Admin user not found'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $report = [
        'success' => false,
        'step_1_user_found' => [
            'success' => true,
            'user_id' => $admin['id'],
            'username' => $admin['username']
        ]
    ];

    // Test 2: Create SessionManager instance
    try {
        $sessionManager = new SessionManager($db);
        $report['step_2_session_manager_created'] = [
            'success' => true,
            'message' => 'SessionManager instance created successfully'
        ];
    } catch (Exception $e) {
        $report['step_2_session_manager_created'] = [
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
        echo json_encode($report, JSON_PRETTY_PRINT);
        exit;
    }

    // Test 3: Try to create a session
    try {
        $sessionToken = $sessionManager->createSession((int)$admin['id']);

        if ($sessionToken) {
            $report['step_3_session_creation'] = [
                'success' => true,
                'session_token' => substr($sessionToken, 0, 20) . '...',
                'token_length' => strlen($sessionToken)
            ];
        } else {
            $report['step_3_session_creation'] = [
                'success' => false,
                'error' => 'createSession returned null',
                'possible_causes' => [
                    'Database insert failed',
                    'Missing required fields',
                    'Constraint violation'
                ]
            ];
        }
    } catch (Exception $e) {
        $report['step_3_session_creation'] = [
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ];
    }

    // Test 4: Validate the created session (if created)
    if (isset($sessionToken) && $sessionToken) {
        try {
            $validatedSession = $sessionManager->validateSession($sessionToken);

            if ($validatedSession) {
                $report['step_4_session_validation'] = [
                    'success' => true,
                    'validated_user_id' => $validatedSession['user_id'],
                    'username' => $validatedSession['username'],
                    'session_token_matches' => $validatedSession['session_token'] === $sessionToken
                ];
            } else {
                $report['step_4_session_validation'] = [
                    'success' => false,
                    'error' => 'validateSession returned null'
                ];
            }
        } catch (Exception $e) {
            $report['step_4_session_validation'] = [
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    // Test 5: Check sessions table
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM sessions");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $report['step_5_sessions_in_db'] = [
            'total_sessions' => (int)$result['count']
        ];

        // Get the last created session
        if ($result['count'] > 0) {
            $stmt = $db->query("SELECT * FROM sessions ORDER BY created_at DESC LIMIT 1");
            $lastSession = $stmt->fetch(PDO::FETCH_ASSOC);
            $report['step_5_sessions_in_db']['last_session'] = $lastSession;
        }
    } catch (Exception $e) {
        $report['step_5_sessions_in_db'] = [
            'error' => $e->getMessage()
        ];
    }

    $report['success'] = isset($report['step_3_session_creation']['success'])
        && $report['step_3_session_creation']['success'];

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

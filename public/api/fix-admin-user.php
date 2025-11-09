<?php
/**
 * Fix admin user settings
 * Ensures admin user is active and email is verified
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Check current admin user status
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode([
            'success' => false,
            'error' => 'Admin user not found in database',
            'hint' => 'Please reimport database_setup.sql'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Check current status
    $needsFix = false;
    $issues = [];

    if (!$admin['email_verified']) {
        $needsFix = true;
        $issues[] = 'email_verified is false';
    }

    if (!$admin['is_active']) {
        $needsFix = true;
        $issues[] = 'is_active is false';
    }

    // Test password
    $testPassword = 'admin123';
    $passwordMatch = password_verify($testPassword, $admin['password_hash']);

    if (!$passwordMatch) {
        $issues[] = 'Password does not match admin123';
    }

    $beforeStatus = [
        'id' => $admin['id'],
        'username' => $admin['username'],
        'email' => $admin['email'],
        'is_active' => (bool)$admin['is_active'],
        'email_verified' => (bool)$admin['email_verified'],
        'password_matches_admin123' => $passwordMatch,
        'issues' => $issues
    ];

    // Fix if needed
    if ($needsFix) {
        $stmt = $db->prepare("
            UPDATE users
            SET is_active = 1,
                email_verified = 1
            WHERE username = 'admin'
        ");
        $stmt->execute();

        // Get updated status
        $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
        $stmt->execute();
        $adminAfter = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'action' => 'FIXED',
            'before' => $beforeStatus,
            'after' => [
                'id' => $adminAfter['id'],
                'username' => $adminAfter['username'],
                'email' => $adminAfter['email'],
                'is_active' => (bool)$adminAfter['is_active'],
                'email_verified' => (bool)$adminAfter['email_verified'],
            ],
            'message' => 'Admin user has been fixed. You can now login with admin / admin123'
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => true,
            'action' => 'NO_FIX_NEEDED',
            'status' => $beforeStatus,
            'message' => $passwordMatch
                ? 'Admin user is correctly configured. Login should work with admin / admin123'
                : 'Admin user is active and verified, but password does not match admin123'
        ], JSON_PRETTY_PRINT);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

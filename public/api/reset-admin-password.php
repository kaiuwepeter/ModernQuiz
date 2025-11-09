<?php
/**
 * Reset admin password to 'admin123'
 * Generates a new bcrypt hash and updates the database
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Check if admin user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode([
            'success' => false,
            'error' => 'Admin user NOT FOUND in database',
            'hint' => 'Please reimport database_setup.sql'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Generate new password hash for 'admin123'
    $newPassword = 'admin123';
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    // Store old hash for comparison
    $oldHash = substr($admin['password_hash'], 0, 30) . '...';

    // Verify the new hash works
    $testVerify = password_verify($newPassword, $newHash);

    if (!$testVerify) {
        echo json_encode([
            'success' => false,
            'error' => 'Password hash generation failed - verification test failed',
            'debug' => [
                'new_hash' => $newHash,
                'test_password' => $newPassword,
                'verification_result' => $testVerify
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Update password hash in database
    $stmt = $db->prepare("
        UPDATE users
        SET password_hash = ?,
            email_verified = 1,
            is_active = 1
        WHERE username = 'admin'
    ");
    $stmt->execute([$newHash]);

    // Verify update
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);

    // Final verification test
    $finalTest = password_verify($newPassword, $updated['password_hash']);

    echo json_encode([
        'success' => true,
        'message' => 'Admin password has been reset to: admin123',
        'details' => [
            'username' => 'admin',
            'new_password' => 'admin123',
            'old_hash' => $oldHash,
            'new_hash' => substr($newHash, 0, 30) . '...',
            'hash_in_db' => substr($updated['password_hash'], 0, 30) . '...',
            'verification_test' => $finalTest ? 'PASS ✓' : 'FAIL ✗',
            'email_verified' => true,
            'is_active' => true
        ],
        'next_steps' => [
            '1. Try logging in with: admin / admin123',
            '2. If it fails, check /api/check-admin.php to verify',
            '3. Check browser console for any CORS or network errors'
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

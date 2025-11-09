<?php
/**
 * Direct database check - bypasses all middleware
 * Checks if admin user exists and password hash is correct
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

    // Check password hash
    $testPassword = 'admin123';
    $passwordMatch = password_verify($testPassword, $admin['password_hash']);

    // Get table columns
    $stmt = $db->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Check database structure
    $requiredColumns = ['email_verified', 'role', 'coins', 'points', 'level', 'avatar'];
    $missingColumns = array_diff($requiredColumns, $columns);

    echo json_encode([
        'success' => true,
        'admin_user_found' => true,
        'admin_data' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'is_active' => (bool)$admin['is_active'],
            'is_admin' => isset($admin['is_admin']) ? (bool)$admin['is_admin'] : 'COLUMN NOT EXISTS',
            'email_verified' => isset($admin['email_verified']) ? (bool)$admin['email_verified'] : 'COLUMN NOT EXISTS',
            'role' => $admin['role'] ?? 'COLUMN NOT EXISTS',
            'coins' => $admin['coins'] ?? 'COLUMN NOT EXISTS',
            'points' => $admin['points'] ?? 'COLUMN NOT EXISTS',
            'level' => $admin['level'] ?? 'COLUMN NOT EXISTS',
            'avatar' => $admin['avatar'] ?? 'COLUMN NOT EXISTS',
            'created_at' => $admin['created_at']
        ],
        'password_test' => [
            'test_password' => $testPassword,
            'password_hash' => substr($admin['password_hash'], 0, 20) . '...',
            'password_matches' => $passwordMatch,
            'hash_algorithm' => password_get_info($admin['password_hash'])
        ],
        'database_structure' => [
            'all_columns' => $columns,
            'missing_columns' => $missingColumns,
            'schema_ok' => empty($missingColumns)
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}

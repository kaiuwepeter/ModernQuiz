<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Check if admin exists
    $stmt = $db->prepare("SELECT id, username, email, is_active, is_admin FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Count total users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check database connection
    $dbTest = [
        'connection' => 'OK',
        'database' => $db->query("SELECT DATABASE()")->fetchColumn(),
    ];

    echo json_encode([
        'database' => $dbTest,
        'admin_user' => $admin ?: 'NOT FOUND',
        'total_users' => $userCount['count'],
        'password_verify_test' => password_verify('admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
        'env_loaded' => [
            'DB_NAME' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'NOT SET',
            'APP_ENV' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'NOT SET'
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\Database;

header('Content-Type: application/json');

try {
    $pdo = Database::getInstance()->getConnection();

    // 1. Show all columns in users table
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Try login query
    $identifier = 'admin';
    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, email_verified, is_active, role, is_admin,
               coins, bonus_coins, points, level, avatar, referral_code, created_at, last_login
        FROM users
        WHERE (username = ? OR email = ?)
        LIMIT 1
    ");

    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'columns' => $columns,
        'user_found' => $user ? true : false,
        'user' => $user
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}

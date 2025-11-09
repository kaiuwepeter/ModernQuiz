<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Check admin user
    $stmt = $db->query("SELECT id, username, email, role, is_admin FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'admin_user' => $admin
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Check current admin user
    $stmt = $db->query("SELECT id, username, email, role, is_admin FROM users WHERE username = 'admin'");
    $before = $stmt->fetch(PDO::FETCH_ASSOC);

    // Update admin user role and is_admin flag
    $stmt = $db->prepare("UPDATE users SET role = 'admin', is_admin = 1 WHERE username = 'admin'");
    $stmt->execute();

    // Get updated user
    $stmt = $db->query("SELECT id, username, email, role, is_admin FROM users WHERE username = 'admin'");
    $after = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Admin role fixed',
        'before' => $before,
        'after' => $after
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Direct database connection
$config = require __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    echo "=== USERS TABLE STRUCTURE ===\n\n";

    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo sprintf("%-20s %-20s %-10s\n", $col['Field'], $col['Type'], $col['Null']);
    }

    echo "\n\n=== ADMIN USER DATA ===\n\n";

    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        foreach ($admin as $key => $value) {
            if ($key === 'password_hash') {
                $value = substr($value, 0, 20) . '...';
            }
            echo sprintf("%-20s: %s\n", $key, $value);
        }
    } else {
        echo "Admin user not found!\n";
    }

    echo "\n\n=== TEST LOGIN QUERY ===\n\n";

    // Test the exact query from Login.php
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

    if ($user) {
        echo "SUCCESS - Query returned user data\n";
        echo "Role: " . ($user['role'] ?? 'NULL') . "\n";
        echo "is_admin: " . ($user['is_admin'] ?? 'NULL') . "\n";
    } else {
        echo "FAILED - No user found\n";
    }

} catch (PDOException $e) {
    echo "DATABASE ERROR: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

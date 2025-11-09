<?php
// Direct database connection without autoloader
$config = require __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // 1. Fix admin role
    $stmt = $pdo->prepare('UPDATE users SET role = ?, is_admin = ? WHERE username = ?');
    $stmt->execute(['admin', 1, 'admin']);

    $stmt = $pdo->query("SELECT id, username, email, role, is_admin FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Check powerups
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM powerups');
    $powerupCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query('SELECT id, name, category FROM powerups LIMIT 5');
    $samplePowerups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Check jackpots
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM jackpots');
    $jackpotCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query('SELECT id, name, type, current_value FROM jackpots LIMIT 5');
    $sampleJackpots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Check bank deposits
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM bank_deposits WHERE user_id = ' . $admin['id']);
    $depositCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'admin_fixed' => $admin,
        'powerup_count' => $powerupCount,
        'sample_powerups' => $samplePowerups,
        'jackpot_count' => $jackpotCount,
        'sample_jackpots' => $sampleJackpots,
        'admin_deposit_count' => $depositCount
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

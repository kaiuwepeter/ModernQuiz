<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Check powerups
    $stmt = $db->query("SELECT COUNT(*) as count FROM powerups");
    $powerupCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $db->query("SELECT * FROM powerups LIMIT 5");
    $samplePowerups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'powerup_count' => $powerupCount,
        'sample_powerups' => $samplePowerups
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

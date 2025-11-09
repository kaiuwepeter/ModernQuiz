<?php
/**
 * Check and fix sessions table structure
 * Ensures table matches SessionManager expectations
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Get current table structure
    $stmt = $db->query("SHOW COLUMNS FROM sessions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $columnNames = array_column($columns, 'Field');

    $report = [
        'success' => false,
        'current_structure' => $columns,
        'issues_found' => [],
        'fixes_applied' => [],
        'actions_needed' => []
    ];

    // Check for required columns
    $requiredColumns = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT NOT NULL',
        'session_token' => 'VARCHAR(64) UNIQUE NOT NULL',
        'device_hash' => 'VARCHAR(64)',
        'ip_address' => 'VARCHAR(45)',
        'user_agent' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'expires_at' => 'TIMESTAMP NOT NULL',
        'last_activity' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];

    // Check for session_id vs session_token confusion
    if (in_array('session_id', $columnNames) && !in_array('session_token', $columnNames)) {
        $report['issues_found'][] = 'Column named "session_id" should be "session_token"';

        // Rename column
        $db->exec("ALTER TABLE sessions CHANGE COLUMN session_id session_token VARCHAR(64) UNIQUE NOT NULL");
        $report['fixes_applied'][] = 'Renamed session_id to session_token';
    }

    // Refresh column list after rename
    $stmt = $db->query("SHOW COLUMNS FROM sessions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');

    // Check for device_hash column
    if (!in_array('device_hash', $columnNames)) {
        $report['issues_found'][] = 'Missing column: device_hash';

        // Add device_hash column
        $db->exec("ALTER TABLE sessions ADD COLUMN device_hash VARCHAR(64) AFTER session_token");
        $report['fixes_applied'][] = 'Added device_hash column';
    }

    // Check for expires_at column
    if (!in_array('expires_at', $columnNames)) {
        $report['issues_found'][] = 'Missing column: expires_at';

        // Add expires_at column with default value
        $db->exec("ALTER TABLE sessions ADD COLUMN expires_at TIMESTAMP NOT NULL DEFAULT (TIMESTAMP(DATE_ADD(NOW(), INTERVAL 1 HOUR)))");
        $report['fixes_applied'][] = 'Added expires_at column';
    }

    // Final structure check
    $stmt = $db->query("SHOW COLUMNS FROM sessions");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $report['final_structure'] = $finalColumns;
    $report['success'] = true;
    $report['message'] = empty($report['fixes_applied'])
        ? 'Sessions table structure is correct'
        : 'Sessions table has been fixed';

    echo json_encode($report, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString())
    ], JSON_PRETTY_PRINT);
}

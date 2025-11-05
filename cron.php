<?php
/**
 * ModernQuiz Cron Job
 *
 * Dieser Job sollte regelmäßig ausgeführt werden (z.B. jede Stunde):
 * 0 * * * * cd /path/to/modernquiz && php cron.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use ModernQuiz\Core\Config;
use ModernQuiz\Core\Email\Mailer;
use ModernQuiz\Modules\User\InactivityManager;

echo "[" . date('Y-m-d H:i:s') . "] ModernQuiz Cron Job gestartet\n";

try {
    // Config laden
    $config = Config::getInstance();
    $dbConfig = $config->getDbConfig();

    // Datenbank-Verbindung
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ATTR_MODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // === EMAIL QUEUE VERARBEITEN ===
    echo "[" . date('H:i:s') . "] Verarbeite Email-Queue...\n";
    $mailer = new Mailer($pdo);
    $processed = $mailer->processQueue(50);
    echo "[" . date('H:i:s') . "] $processed Emails versendet\n";

    // Cleanup alte Emails (älter als 30 Tage)
    $cleaned = $mailer->cleanupOldEmails(30);
    echo "[" . date('H:i:s') . "] $cleaned alte Emails gelöscht\n";

    // === INAKTIVITÄTS-CHECK ===
    echo "[" . date('H:i:s') . "] Prüfe inaktive User...\n";
    $inactivityManager = new InactivityManager($pdo, $mailer);
    $results = $inactivityManager->checkInactiveUsers();
    echo "[" . date('H:i:s') . "] {$results['warnings_sent']} Warnungen versendet\n";
    echo "[" . date('H:i:s') . "] {$results['users_deleted']} User gelöscht\n";

    // === SESSION CLEANUP ===
    echo "[" . date('H:i:s') . "] Cleanup alte Sessions...\n";
    $sessionLifetime = (int)$config->get('SESSION_LIFETIME', 3600);
    $stmt = $pdo->prepare(
        "DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)"
    );
    $stmt->execute([$sessionLifetime]);
    $cleaned = $stmt->rowCount();
    echo "[" . date('H:i:s') . "] $cleaned Sessions gelöscht\n";

    // === OLD GAME ROOMS CLEANUP ===
    echo "[" . date('H:i:s') . "] Cleanup alte Game Rooms...\n";
    $stmt = $pdo->prepare(
        "DELETE FROM game_rooms
         WHERE status = 'finished'
         AND finished_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
    $stmt->execute();
    $cleaned = $stmt->rowCount();
    echo "[" . date('H:i:s') . "] $cleaned Game Rooms gelöscht\n";

    // === ALTE NOTIFICATIONS CLEANUP ===
    echo "[" . date('H:i:s') . "] Cleanup alte Notifications...\n";
    $stmt = $pdo->prepare(
        "DELETE FROM notifications
         WHERE is_read = TRUE
         AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    $stmt->execute();
    $cleaned = $stmt->rowCount();
    echo "[" . date('H:i:s') . "] $cleaned Notifications gelöscht\n";

    echo "[" . date('Y-m-d H:i:s') . "] Cron Job erfolgreich beendet\n\n";

} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    error_log("ModernQuiz Cron Error: " . $e->getMessage());
    exit(1);
}

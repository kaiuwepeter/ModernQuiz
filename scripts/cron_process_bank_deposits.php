<?php
/**
 * Cron Job: Process Bank Deposits
 *
 * Dieses Script sollte täglich ausgeführt werden, um:
 * - Fällige Einlagen auf 'matured' zu setzen
 * - User über fällige Einlagen zu benachrichtigen
 *
 * Crontab Beispiel (täglich um 00:00 Uhr):
 * 0 0 * * * php /path/to/ModernQuiz/scripts/cron_process_bank_deposits.php >> /path/to/logs/cron_bank.log 2>&1
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ModernQuiz\Core\Database;
use ModernQuiz\Modules\Bank\BankManager;

try {
    echo "[" . date('Y-m-d H:i:s') . "] Starting Bank Deposits Cron Job...\n";

    $database = Database::getInstance();
    $bankManager = new BankManager($database);

    // Verarbeite fällige Einlagen
    $result = $bankManager->processMaturingDeposits();

    if ($result['success']) {
        $count = $result['matured_deposits'];
        echo "[" . date('Y-m-d H:i:s') . "] SUCCESS: {$count} Einlagen wurden als fällig markiert\n";

        // Benachrichtige User über fällige Einlagen
        if ($count > 0) {
            $notified = notifyUsersAboutMaturedDeposits($database);
            echo "[" . date('Y-m-d H:i:s') . "] {$notified} User über fällige Einlagen benachrichtigt\n";
        }
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] ERROR: Fehler beim Verarbeiten der Einlagen\n";
    }

    echo "[" . date('Y-m-d H:i:s') . "] Bank Deposits Cron Job completed\n\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n\n";
    exit(1);
}

/**
 * Benachrichtigt User über fällige Einlagen
 */
function notifyUsersAboutMaturedDeposits($db): int {
    $stmt = $db->prepare("
        SELECT DISTINCT bd.user_id, u.username, u.email, COUNT(*) as count
        FROM bank_deposits bd
        JOIN users u ON bd.user_id = u.id
        WHERE bd.status = 'matured'
        GROUP BY bd.user_id
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $notified = 0;

    while ($row = $result->fetch_assoc()) {
        $userId = $row['user_id'];
        $count = $row['count'];

        // Erstelle Benachrichtigung
        $title = "Festgeld-Einlagen fällig!";
        $message = "Du hast {$count} Festgeld-Einlage(n), die zur Auszahlung bereit sind. Logge dich ein, um sie abzurufen.";

        $stmtNotify = $db->prepare("
            INSERT INTO notifications (user_id, type, title, message)
            VALUES (?, 'bank_matured', ?, ?)
        ");
        $stmtNotify->bind_param('iss', $userId, $title, $message);
        $stmtNotify->execute();

        $notified++;
    }

    return $notified;
}

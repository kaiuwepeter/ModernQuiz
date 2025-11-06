<?php
// src/modules/voucher/VoucherManager.php

namespace ModernQuiz\Modules\Voucher;

use DateTime;

/**
 * VoucherManager - Sicheres Gutscheinsystem
 *
 * Security Features:
 * - Rate Limiting (5 Versuche, dann Sperre)
 * - Fraud Detection und Logging
 * - Admin-Benachrichtigungen bei verdächtigem Verhalten
 * - Transaction-basierte Coin-Vergabe
 * - IP-Tracking und User-Agent-Logging
 * - Prepared Statements gegen SQL-Injection
 */
class VoucherManager {
    private $db;

    // Security Konstanten
    private const MAX_ATTEMPTS = 5;
    private const BLOCK_DURATION_MINUTES = 60;
    private const SUSPICIOUS_THRESHOLD = 3; // Versuche bevor als verdächtig markiert

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Generiert einen sicheren Voucher-Code im Format: xxxxx-xxx-x-xxxxx-xxx
     *
     * @return string
     */
    public function generateVoucherCode(): string {
        $part1 = $this->generateRandomString(5); // xxxxx
        $part2 = $this->generateRandomString(3); // xxx
        $part3 = $this->generateRandomString(1); // x
        $part4 = $this->generateRandomString(5); // xxxxx
        $part5 = $this->generateRandomString(3); // xxx

        return strtoupper("{$part1}-{$part2}-{$part3}-{$part4}-{$part5}");
    }

    /**
     * Generiert zufälligen alphanumerischen String
     */
    private function generateRandomString(int $length): string {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Erstellt einen neuen Gutschein (Admin-Funktion)
     *
     * @param int $adminId Admin User ID
     * @param array $data Gutschein-Daten
     * @return array ['success' => bool, 'code' => string, 'voucher_id' => int, 'error' => string]
     */
    public function createVoucher(int $adminId, array $data): array {
        // Input Validation
        if (!isset($data['name']) || empty(trim($data['name']))) {
            return ['success' => false, 'error' => 'Name ist erforderlich'];
        }

        $coins = isset($data['coins']) ? max(0, (int)$data['coins']) : 0;
        $bonusCoins = isset($data['bonus_coins']) ? max(0, (int)$data['bonus_coins']) : 0;
        $powerups = isset($data['powerups']) ? $data['powerups'] : [];

        // Mindestens eine Belohnung muss vorhanden sein
        if ($coins === 0 && $bonusCoins === 0 && empty($powerups)) {
            return ['success' => false, 'error' => 'Mindestens eine Belohnung muss angegeben werden'];
        }

        $maxRedemptions = isset($data['max_redemptions']) ? max(1, (int)$data['max_redemptions']) : 1;
        $maxPerUser = isset($data['max_per_user']) ? max(1, (int)$data['max_per_user']) : 1;

        // Generiere eindeutigen Code
        $code = $this->generateVoucherCode();
        $attempts = 0;
        while ($this->voucherCodeExists($code) && $attempts < 10) {
            $code = $this->generateVoucherCode();
            $attempts++;
        }

        if ($attempts >= 10) {
            return ['success' => false, 'error' => 'Konnte keinen eindeutigen Code generieren'];
        }

        // Prepare Powerups JSON
        $powerupsJson = !empty($powerups) ? json_encode($powerups) : null;

        // Valid Until verarbeiten
        $validUntil = null;
        if (isset($data['valid_until']) && !empty($data['valid_until'])) {
            try {
                $validUntil = new DateTime($data['valid_until']);
                $validUntil = $validUntil->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return ['success' => false, 'error' => 'Ungültiges Datum für valid_until'];
            }
        }

        // Valid From verarbeiten
        $validFrom = new DateTime();
        if (isset($data['valid_from']) && !empty($data['valid_from'])) {
            try {
                $validFrom = new DateTime($data['valid_from']);
            } catch (\Exception $e) {
                return ['success' => false, 'error' => 'Ungültiges Datum für valid_from'];
            }
        }
        $validFromStr = $validFrom->format('Y-m-d H:i:s');

        // Insert Voucher
        $stmt = $this->db->prepare("
            INSERT INTO vouchers (
                code, name, description,
                coins, bonus_coins, powerups,
                max_redemptions, max_per_user,
                valid_from, valid_until,
                created_by, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");

        $description = $data['description'] ?? '';

        $stmt->bind_param(
            'sssiisisssii',
            $code,
            $data['name'],
            $description,
            $coins,
            $bonusCoins,
            $powerupsJson,
            $maxRedemptions,
            $maxPerUser,
            $validFromStr,
            $validUntil,
            $adminId
        );

        if ($stmt->execute()) {
            $voucherId = $stmt->insert_id;
            return [
                'success' => true,
                'code' => $code,
                'voucher_id' => $voucherId,
                'message' => 'Gutschein erfolgreich erstellt'
            ];
        }

        return ['success' => false, 'error' => 'Datenbankfehler beim Erstellen des Gutscheins'];
    }

    /**
     * Prüft ob ein Voucher-Code bereits existiert
     */
    private function voucherCodeExists(string $code): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM vouchers WHERE code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    /**
     * Gutschein einlösen (User-Funktion) - MIT SICHERHEITSPRÜFUNGEN
     *
     * @param int $userId
     * @param string $code
     * @param string $ipAddress
     * @param string $userAgent
     * @return array
     */
    public function redeemVoucher(int $userId, string $code, string $ipAddress, string $userAgent): array {
        // 1. Rate Limiting prüfen
        $rateLimitCheck = $this->checkRateLimit($userId, $ipAddress);
        if (!$rateLimitCheck['allowed']) {
            $this->logFraudAttempt($userId, $code, 'rate_limit_exceeded', $ipAddress, $userAgent);
            return [
                'success' => false,
                'error' => $rateLimitCheck['message'],
                'blocked_until' => $rateLimitCheck['blocked_until'] ?? null
            ];
        }

        // 2. Code normalisieren (Großbuchstaben, Leerzeichen entfernen)
        $code = strtoupper(trim($code));

        // 3. Gutschein aus Datenbank laden
        $stmt = $this->db->prepare("
            SELECT * FROM vouchers
            WHERE code = ? AND is_active = 1
        ");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $voucher = $stmt->get_result()->fetch_assoc();

        // 4. Voucher existiert nicht
        if (!$voucher) {
            $this->incrementFailedAttempt($userId, $ipAddress);
            $this->logFraudAttempt($userId, $code, 'invalid_code', $ipAddress, $userAgent);
            return ['success' => false, 'error' => 'Ungültiger Gutscheincode'];
        }

        // 5. Validierung: Noch nicht gültig
        $now = new DateTime();
        $validFrom = new DateTime($voucher['valid_from']);
        if ($now < $validFrom) {
            $this->incrementFailedAttempt($userId, $ipAddress);
            $this->logFraudAttempt($userId, $code, 'not_yet_valid', $ipAddress, $userAgent);
            return ['success' => false, 'error' => 'Dieser Gutschein ist noch nicht gültig'];
        }

        // 6. Validierung: Abgelaufen
        if ($voucher['valid_until'] !== null) {
            $validUntil = new DateTime($voucher['valid_until']);
            if ($now > $validUntil) {
                $this->incrementFailedAttempt($userId, $ipAddress);
                $this->logFraudAttempt($userId, $code, 'expired', $ipAddress, $userAgent);
                return ['success' => false, 'error' => 'Dieser Gutschein ist abgelaufen'];
            }
        }

        // 7. Validierung: Maximale Einlösungen erreicht
        if ($voucher['current_redemptions'] >= $voucher['max_redemptions']) {
            $this->incrementFailedAttempt($userId, $ipAddress);
            $this->logFraudAttempt($userId, $code, 'max_redemptions_reached', $ipAddress, $userAgent);
            return ['success' => false, 'error' => 'Dieser Gutschein wurde bereits vollständig eingelöst'];
        }

        // 8. Validierung: User hat diesen Gutschein bereits eingelöst
        $userRedemptions = $this->getUserVoucherRedemptionCount($userId, $voucher['id']);
        if ($userRedemptions >= $voucher['max_per_user']) {
            $this->incrementFailedAttempt($userId, $ipAddress);
            $this->logFraudAttempt($userId, $code, 'already_redeemed_by_user', $ipAddress, $userAgent);
            return ['success' => false, 'error' => 'Du hast diesen Gutschein bereits eingelöst'];
        }

        // 9. ALLES OK - Gutschein einlösen (Transaction)
        try {
            $this->db->begin_transaction();

            // 9.1 Coins und Bonus Coins vergeben
            $result = $this->grantRewards($userId, $voucher, $ipAddress, $userAgent);

            if (!$result['success']) {
                $this->db->rollback();
                return $result;
            }

            // 9.2 Redemption Counter erhöhen
            $stmt = $this->db->prepare("
                UPDATE vouchers
                SET current_redemptions = current_redemptions + 1
                WHERE id = ?
            ");
            $stmt->bind_param('i', $voucher['id']);
            $stmt->execute();

            // 9.3 Rate Limit zurücksetzen (erfolgreiche Einlösung)
            $this->resetFailedAttempts($userId, $ipAddress);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Gutschein erfolgreich eingelöst!',
                'rewards' => [
                    'coins' => $voucher['coins'],
                    'bonus_coins' => $voucher['bonus_coins'],
                    'powerups' => $voucher['powerups'] ? json_decode($voucher['powerups'], true) : []
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Voucher redemption error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.'];
        }
    }

    /**
     * Vergibt die Belohnungen an den User
     */
    private function grantRewards(int $userId, array $voucher, string $ipAddress, string $userAgent): array {
        // Aktuelle Coins abrufen
        $stmt = $this->db->prepare("SELECT coins, bonus_coins FROM user_stats WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();

        if (!$stats) {
            return ['success' => false, 'error' => 'Benutzer-Statistiken nicht gefunden'];
        }

        $coinsBefore = $stats['coins'];
        $bonusCoinsBefore = $stats['bonus_coins'];
        $coinsAfter = $coinsBefore + $voucher['coins'];
        $bonusCoinsAfter = $bonusCoinsBefore + $voucher['bonus_coins'];

        // Update user_stats
        $stmt = $this->db->prepare("
            UPDATE user_stats
            SET coins = coins + ?, bonus_coins = bonus_coins + ?
            WHERE user_id = ?
        ");
        $stmt->bind_param('iii', $voucher['coins'], $voucher['bonus_coins'], $userId);

        if (!$stmt->execute()) {
            return ['success' => false, 'error' => 'Fehler beim Aktualisieren der Coins'];
        }

        // Powerups hinzufügen
        $powerups = $voucher['powerups'] ? json_decode($voucher['powerups'], true) : [];
        if (!empty($powerups)) {
            foreach ($powerups as $powerup) {
                $this->grantPowerup($userId, $powerup['id'], $powerup['quantity']);
            }
        }

        // Redemption Record erstellen
        $powerupsJson = !empty($powerups) ? json_encode($powerups) : null;
        $stmt = $this->db->prepare("
            INSERT INTO voucher_redemptions (
                voucher_id, user_id,
                coins_received, bonus_coins_received, powerups_received,
                ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'iiiisss',
            $voucher['id'],
            $userId,
            $voucher['coins'],
            $voucher['bonus_coins'],
            $powerupsJson,
            $ipAddress,
            $userAgent
        );
        $stmt->execute();

        // Transaction Log erstellen
        $description = "Gutschein eingelöst: {$voucher['code']}";
        $metadata = json_encode([
            'voucher_id' => $voucher['id'],
            'voucher_code' => $voucher['code'],
            'powerups' => $powerups
        ]);

        $stmt = $this->db->prepare("
            INSERT INTO coin_transactions (
                user_id, transaction_type,
                coins_change, bonus_coins_change,
                coins_before, bonus_coins_before,
                coins_after, bonus_coins_after,
                reference_type, reference_id,
                description, metadata
            ) VALUES (?, 'voucher_redemption', ?, ?, ?, ?, ?, ?, 'voucher', ?, ?, ?)
        ");
        $stmt->bind_param(
            'iiiiiiiiiss',
            $userId,
            $voucher['coins'],
            $voucher['bonus_coins'],
            $coinsBefore,
            $bonusCoinsBefore,
            $coinsAfter,
            $bonusCoinsAfter,
            $voucher['id'],
            $description,
            $metadata
        );
        $stmt->execute();

        return ['success' => true];
    }

    /**
     * Vergibt Powerups an User
     */
    private function grantPowerup(int $userId, int $powerupId, int $quantity): void {
        // Prüfe ob User dieses Powerup bereits hat
        $stmt = $this->db->prepare("
            SELECT quantity FROM user_powerups
            WHERE user_id = ? AND powerup_id = ?
        ");
        $stmt->bind_param('ii', $userId, $powerupId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            // Update bestehende Quantity
            $stmt = $this->db->prepare("
                UPDATE user_powerups
                SET quantity = quantity + ?
                WHERE user_id = ? AND powerup_id = ?
            ");
            $stmt->bind_param('iii', $quantity, $userId, $powerupId);
            $stmt->execute();
        } else {
            // Neues Powerup hinzufügen
            $stmt = $this->db->prepare("
                INSERT INTO user_powerups (user_id, powerup_id, quantity)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param('iii', $userId, $powerupId, $quantity);
            $stmt->execute();
        }
    }

    /**
     * Prüft Rate Limiting
     */
    private function checkRateLimit(int $userId, string $ipAddress): array {
        $stmt = $this->db->prepare("
            SELECT * FROM voucher_rate_limits
            WHERE user_id = ? AND ip_address = ?
        ");
        $stmt->bind_param('is', $userId, $ipAddress);
        $stmt->execute();
        $limit = $stmt->get_result()->fetch_assoc();

        if (!$limit) {
            return ['allowed' => true];
        }

        // Permanente Sperre
        if ($limit['is_permanently_blocked']) {
            $this->notifyAdminAboutBlockedUser($userId, $ipAddress);
            return [
                'allowed' => false,
                'message' => 'Dein Account wurde wegen verdächtiger Aktivitäten gesperrt. Bitte kontaktiere den Support.'
            ];
        }

        // Temporäre Sperre
        if ($limit['blocked_until'] !== null) {
            $blockedUntil = new DateTime($limit['blocked_until']);
            $now = new DateTime();

            if ($now < $blockedUntil) {
                $minutesLeft = ceil(($blockedUntil->getTimestamp() - $now->getTimestamp()) / 60);
                return [
                    'allowed' => false,
                    'message' => "Zu viele Fehlversuche. Bitte warte noch {$minutesLeft} Minuten.",
                    'blocked_until' => $blockedUntil->format('Y-m-d H:i:s')
                ];
            } else {
                // Sperre abgelaufen, zurücksetzen
                $this->resetFailedAttempts($userId, $ipAddress);
                return ['allowed' => true];
            }
        }

        // Prüfe Anzahl failed attempts
        if ($limit['failed_attempts'] >= self::MAX_ATTEMPTS) {
            // Jetzt sperren
            $blockedUntil = (new DateTime())->modify('+' . self::BLOCK_DURATION_MINUTES . ' minutes');
            $blockedUntilStr = $blockedUntil->format('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                UPDATE voucher_rate_limits
                SET blocked_until = ?, is_permanently_blocked = 1
                WHERE user_id = ? AND ip_address = ?
            ");
            $stmt->bind_param('sis', $blockedUntilStr, $userId, $ipAddress);
            $stmt->execute();

            // Admin benachrichtigen
            $this->notifyAdminAboutBlockedUser($userId, $ipAddress);

            return [
                'allowed' => false,
                'message' => 'Zu viele Fehlversuche. Dein Account wurde gesperrt. Ein Administrator wurde benachrichtigt.',
                'blocked_until' => $blockedUntilStr
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Erhöht Failed Attempts Counter
     */
    private function incrementFailedAttempt(int $userId, string $ipAddress): void {
        $stmt = $this->db->prepare("
            INSERT INTO voucher_rate_limits (user_id, ip_address, failed_attempts, last_attempt_at)
            VALUES (?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE
                failed_attempts = failed_attempts + 1,
                last_attempt_at = NOW()
        ");
        $stmt->bind_param('is', $userId, $ipAddress);
        $stmt->execute();
    }

    /**
     * Setzt Failed Attempts zurück
     */
    private function resetFailedAttempts(int $userId, string $ipAddress): void {
        $stmt = $this->db->prepare("
            DELETE FROM voucher_rate_limits
            WHERE user_id = ? AND ip_address = ?
        ");
        $stmt->bind_param('is', $userId, $ipAddress);
        $stmt->execute();
    }

    /**
     * Loggt Betrugsversuche
     */
    private function logFraudAttempt(
        int $userId,
        string $code,
        string $reason,
        string $ipAddress,
        string $userAgent,
        bool $isSuspicious = false
    ): void {
        // Prüfe ob dieser User bereits viele Fehlversuche hat
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM voucher_fraud_log
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $attemptCount = $result['count'] + 1;

        // Ab 3 Versuchen als verdächtig markieren
        if ($attemptCount >= self::SUSPICIOUS_THRESHOLD) {
            $isSuspicious = true;
        }

        $stmt = $this->db->prepare("
            INSERT INTO voucher_fraud_log (
                user_id, attempted_code, failure_reason,
                ip_address, user_agent, attempt_count, is_suspicious
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('issssii', $userId, $code, $reason, $ipAddress, $userAgent, $attemptCount, $isSuspicious);
        $stmt->execute();

        // Bei verdächtigem Verhalten Admin benachrichtigen
        if ($isSuspicious) {
            $this->notifyAdminAboutSuspiciousActivity($userId, $code, $attemptCount, $ipAddress);
        }
    }

    /**
     * Benachrichtigt Admin über verdächtige Aktivität
     */
    private function notifyAdminAboutSuspiciousActivity(int $userId, string $code, int $attemptCount, string $ipAddress): void {
        // User-Daten laden
        $stmt = $this->db->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $message = "Verdächtige Gutschein-Aktivität erkannt:\n\n";
        $message .= "User: {$user['username']} (ID: {$userId})\n";
        $message .= "Email: {$user['email']}\n";
        $message .= "IP: {$ipAddress}\n";
        $message .= "Letzter versuchter Code: {$code}\n";
        $message .= "Fehlversuche in letzter Stunde: {$attemptCount}\n\n";
        $message .= "Bitte prüfe das Benutzerverhalten.";

        $this->sendAdminNotification('Verdächtige Gutschein-Aktivität', $message, $userId);
    }

    /**
     * Benachrichtigt Admin über gesperrten User
     */
    private function notifyAdminAboutBlockedUser(int $userId, string $ipAddress): void {
        $stmt = $this->db->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $message = "User wurde wegen zu vieler Fehlversuche automatisch gesperrt:\n\n";
        $message .= "User: {$user['username']} (ID: {$userId})\n";
        $message .= "Email: {$user['email']}\n";
        $message .= "IP: {$ipAddress}\n";
        $message .= "Sperrdauer: " . self::BLOCK_DURATION_MINUTES . " Minuten\n\n";
        $message .= "Der User wurde automatisch gesperrt.";

        $this->sendAdminNotification('User wegen Gutschein-Betrug gesperrt', $message, $userId);

        // Markiere als admin_notified
        $stmt = $this->db->prepare("
            UPDATE voucher_fraud_log
            SET admin_notified = 1, notified_at = NOW()
            WHERE user_id = ? AND admin_notified = 0
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    /**
     * Sendet Benachrichtigung an alle Admins
     */
    private function sendAdminNotification(string $title, string $message, int $userId): void {
        // Hole alle Admin-User
        $result = $this->db->query("
            SELECT id FROM users WHERE role = 'admin' OR role = 'super_admin'
        ");

        $data = json_encode([
            'user_id' => $userId,
            'timestamp' => (new DateTime())->format('Y-m-d H:i:s')
        ]);

        while ($admin = $result->fetch_assoc()) {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, data)
                VALUES (?, 'voucher_fraud', ?, ?, ?)
            ");
            $stmt->bind_param('isss', $admin['id'], $title, $message, $data);
            $stmt->execute();
        }
    }

    /**
     * Gibt Anzahl der Einlösungen eines Users für einen bestimmten Voucher zurück
     */
    private function getUserVoucherRedemptionCount(int $userId, int $voucherId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM voucher_redemptions
            WHERE user_id = ? AND voucher_id = ?
        ");
        $stmt->bind_param('ii', $userId, $voucherId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }

    /**
     * Listet alle Gutscheine auf (Admin)
     */
    public function listVouchers(array $filters = []): array {
        $where = [];
        $params = [];
        $types = '';

        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = $filters['is_active'];
            $types .= 'i';
        }

        if (isset($filters['search'])) {
            $where[] = "(code LIKE ? OR name LIKE ? OR description LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= 'sss';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT v.*, u.username as created_by_username,
                   (v.max_redemptions - v.current_redemptions) as remaining_redemptions
            FROM vouchers v
            LEFT JOIN users u ON v.created_by = u.id
            {$whereClause}
            ORDER BY v.created_at DESC
        ";

        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }

        $vouchers = [];
        while ($row = $result->fetch_assoc()) {
            $vouchers[] = $row;
        }

        return $vouchers;
    }

    /**
     * Löscht/Deaktiviert einen Gutschein (Admin)
     */
    public function deleteVoucher(int $voucherId, int $adminId): array {
        // Soft Delete - nur deaktivieren
        $stmt = $this->db->prepare("
            UPDATE vouchers
            SET is_active = 0, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param('i', $voucherId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Gutschein wurde deaktiviert'];
        }

        return ['success' => false, 'error' => 'Fehler beim Deaktivieren des Gutscheins'];
    }

    /**
     * Gibt Statistiken über einen Voucher zurück (Admin)
     */
    public function getVoucherStats(int $voucherId): array {
        $stmt = $this->db->prepare("
            SELECT
                v.*,
                COUNT(DISTINCT vr.user_id) as unique_users,
                SUM(vr.coins_received) as total_coins_given,
                SUM(vr.bonus_coins_received) as total_bonus_coins_given
            FROM vouchers v
            LEFT JOIN voucher_redemptions vr ON v.id = vr.voucher_id
            WHERE v.id = ?
            GROUP BY v.id
        ");
        $stmt->bind_param('i', $voucherId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();

        if (!$stats) {
            return ['success' => false, 'error' => 'Gutschein nicht gefunden'];
        }

        // Recent Redemptions
        $stmt = $this->db->prepare("
            SELECT vr.*, u.username
            FROM voucher_redemptions vr
            LEFT JOIN users u ON vr.user_id = u.id
            WHERE vr.voucher_id = ?
            ORDER BY vr.redeemed_at DESC
            LIMIT 10
        ");
        $stmt->bind_param('i', $voucherId);
        $stmt->execute();
        $result = $stmt->get_result();

        $recentRedemptions = [];
        while ($row = $result->fetch_assoc()) {
            $recentRedemptions[] = $row;
        }

        return [
            'success' => true,
            'stats' => $stats,
            'recent_redemptions' => $recentRedemptions
        ];
    }

    /**
     * Gibt Fraud Log zurück (Admin)
     */
    public function getFraudLog(array $filters = []): array {
        $where = [];
        $params = [];
        $types = '';

        if (isset($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
            $types .= 'i';
        }

        if (isset($filters['is_suspicious']) && $filters['is_suspicious']) {
            $where[] = "is_suspicious = 1";
        }

        if (isset($filters['admin_notified']) && !$filters['admin_notified']) {
            $where[] = "admin_notified = 0";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT vfl.*, u.username, u.email
            FROM voucher_fraud_log vfl
            LEFT JOIN users u ON vfl.user_id = u.id
            {$whereClause}
            ORDER BY vfl.created_at DESC
            LIMIT 100
        ";

        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }

        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }

        return $logs;
    }
}

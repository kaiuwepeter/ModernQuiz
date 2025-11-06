<?php
// src/modules/referral/ReferralManager.php

namespace ModernQuiz\Modules\Referral;

/**
 * ReferralManager - Referral/Affiliate-System
 *
 * Features:
 * - 300 Bonus Coins für Werber UND Geworbenen bei Registration
 * - 6% Provision für Werber von Quiz-Gewinnen des Geworbenen
 * - Vollständiges Tracking und Statistiken
 * - Transaction-Sicherheit
 */
class ReferralManager {
    private $db;

    // Default Settings
    private const DEFAULT_BONUS_COINS = 300.00;
    private const DEFAULT_COMMISSION_RATE = 6.00; // 6%

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Generiert einen eindeutigen Referral-Code
     *
     * @param int $userId
     * @return string
     */
    public function generateReferralCode(int $userId): string {
        // Format: USER{id}-{random}
        $random = strtoupper(substr(md5(uniqid($userId, true)), 0, 6));
        $code = "USER{$userId}-{$random}";

        // Stelle sicher dass Code eindeutig ist
        $attempts = 0;
        while ($this->referralCodeExists($code) && $attempts < 10) {
            $random = strtoupper(substr(md5(uniqid($userId, true)), 0, 6));
            $code = "USER{$userId}-{$random}";
            $attempts++;
        }

        return $code;
    }

    /**
     * Prüft ob Referral-Code existiert
     */
    private function referralCodeExists(string $code): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE referral_code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    /**
     * Verarbeitet Referral bei User-Registration
     * WICHTIG: Zahlt KEINE 300 Bonus Coins sofort aus!
     * Coins werden erst nach 10 abgeschlossenen Quizzes ausgezahlt.
     *
     * @param int $newUserId Der neue User
     * @param string|null $referralCode Code des Werbers
     * @return array
     */
    public function processRegistrationReferral(int $newUserId, ?string $referralCode): array {
        if (empty($referralCode)) {
            return ['success' => true, 'message' => 'Keine Referral verwendet'];
        }

        try {
            $this->db->begin_transaction();

            // 1. Finde Werber anhand Code
            $stmt = $this->db->prepare("
                SELECT id, username FROM users WHERE referral_code = ?
            ");
            $stmt->bind_param('s', $referralCode);
            $stmt->execute();
            $referrer = $stmt->get_result()->fetch_assoc();

            if (!$referrer) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Ungültiger Referral-Code'];
            }

            $referrerId = (int)$referrer['id'];

            // Prüfe: Kann nicht sich selbst werben
            if ($referrerId === $newUserId) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Du kannst dich nicht selbst werben'];
            }

            // 2. Setze referred_by beim neuen User
            $stmt = $this->db->prepare("
                UPDATE users
                SET referred_by = ?
                WHERE id = ?
            ");
            $stmt->bind_param('ii', $referrerId, $newUserId);
            $stmt->execute();

            // 3. Initialisiere Referral Stats für BEIDE User (bonus_paid = FALSE)
            // Werber Stats
            $this->updateReferralStats($referrerId, 'add_referral', [
                'referred_user_id' => $newUserId,
                'bonus_received' => 0 // KEINE Coins sofort
            ]);

            // 4. Erstelle Referral Stats für Geworbenen (bonus_paid = FALSE)
            $this->createReferralStats($newUserId);

            $this->db->commit();

            return [
                'success' => true,
                'referrer_id' => $referrerId,
                'referrer_username' => $referrer['username'],
                'message' => "Willkommen! Du und {$referrer['username']} erhaltet je 300 Bonus Coins nach 10 abgeschlossenen Quizzes!"
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("ReferralManager::processRegistrationReferral error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Verarbeiten des Referrals'];
        }
    }

    /**
     * Verarbeitet Provision für Werber wenn Geworbener Coins verdient
     *
     * Dies wird aufgerufen wenn ein User Coins durch ein Quiz verdient
     *
     * @param int $userId Der User der Coins verdient hat
     * @param float $coinsEarned Betrag der Coins
     * @param int $sourceTransactionId coin_transactions.id
     * @return array|null
     */
    public function processCommission(int $userId, float $coinsEarned, int $sourceTransactionId): ?array {
        // Prüfe ob dieser User von jemandem geworben wurde
        $stmt = $this->db->prepare("
            SELECT referred_by FROM users WHERE id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result || !$result['referred_by']) {
            // Kein Werber
            return null;
        }

        $referrerId = (int)$result['referred_by'];

        try {
            $this->db->begin_transaction();

            // Hole Commission Rate
            $settings = $this->getSettings();
            $commissionRate = (float)$settings['referral_commission_rate'];

            // Berechne Provision: 6% von Coins earned
            $commission = round($coinsEarned * ($commissionRate / 100), 2);

            if ($commission <= 0) {
                $this->db->rollback();
                return null;
            }

            // Gebe Werber die Provision als Bonus Coins
            $coinManager = new \ModernQuiz\Modules\Coins\CoinManager($this->db);
            $result = $coinManager->addCoins(
                $referrerId,
                0,
                $commission,
                \ModernQuiz\Modules\Coins\CoinManager::TX_REFERRAL_BONUS,
                'referral_commission',
                $userId,
                "Referral-Provision: {$commissionRate}% von User {$userId} ({$coinsEarned} Coins)",
                [
                    'referred_user_id' => $userId,
                    'coins_earned' => $coinsEarned,
                    'commission_rate' => $commissionRate,
                    'source_transaction_id' => $sourceTransactionId
                ]
            );

            if (!$result['success']) {
                $this->db->rollback();
                return null;
            }

            // Log in referral_earnings
            $description = "Quiz-Gewinn Provision: {$commissionRate}% von {$coinsEarned} Coins";
            $metadata = json_encode([
                'referred_user_id' => $userId,
                'source_transaction_id' => $sourceTransactionId
            ]);

            $stmt = $this->db->prepare("
                INSERT INTO referral_earnings (
                    referrer_user_id,
                    referred_user_id,
                    source_transaction_id,
                    coins_earned,
                    commission_earned,
                    commission_rate,
                    description,
                    metadata
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                'iiidddss',
                $referrerId,
                $userId,
                $sourceTransactionId,
                $coinsEarned,
                $commission,
                $commissionRate,
                $description,
                $metadata
            );
            $stmt->execute();

            // Update Stats
            $this->updateReferralStats($referrerId, 'add_commission', [
                'commission' => $commission
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'referrer_id' => $referrerId,
                'commission_earned' => $commission,
                'commission_rate' => $commissionRate
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("ReferralManager::processCommission error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Gibt Referral-Statistiken eines Users zurück
     */
    public function getReferralStats(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM referral_stats WHERE user_id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();

        if (!$stats) {
            $this->createReferralStats($userId);
            return [
                'total_referrals' => 0,
                'active_referrals' => 0,
                'total_commission_earned' => 0.00,
                'total_bonus_received' => 0.00
            ];
        }

        return $stats;
    }

    /**
     * Gibt alle geworbenen User eines Werbers zurück
     */
    public function getReferredUsers(int $referrerId): array {
        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.username,
                u.email,
                u.is_active,
                u.created_at,
                us.total_points,
                us.level,
                COALESCE(SUM(re.commission_earned), 0) as total_commission_generated
            FROM users u
            LEFT JOIN user_stats us ON u.id = us.user_id
            LEFT JOIN referral_earnings re ON u.id = re.referred_user_id AND re.referrer_user_id = ?
            WHERE u.referred_by = ?
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        $stmt->bind_param('ii', $referrerId, $referrerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    /**
     * Gibt Earnings-Historie zurück
     */
    public function getEarningsHistory(int $referrerId, int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT
                re.*,
                u.username as referred_username
            FROM referral_earnings re
            LEFT JOIN users u ON re.referred_user_id = u.id
            WHERE re.referrer_user_id = ?
            ORDER BY re.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param('iii', $referrerId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $earnings = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['metadata']) {
                $row['metadata'] = json_decode($row['metadata'], true);
            }
            $earnings[] = $row;
        }

        return $earnings;
    }

    /**
     * Update Referral Stats
     */
    private function updateReferralStats(int $userId, string $action, array $data): void {
        // Stelle sicher dass Stats existieren
        $stmt = $this->db->prepare("
            INSERT INTO referral_stats (user_id) VALUES (?)
            ON DUPLICATE KEY UPDATE user_id=user_id
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        if ($action === 'add_referral') {
            $bonusReceived = $data['bonus_received'] ?? 0;

            $stmt = $this->db->prepare("
                UPDATE referral_stats
                SET total_referrals = total_referrals + 1,
                    active_referrals = active_referrals + 1,
                    total_bonus_received = total_bonus_received + ?,
                    last_referral_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->bind_param('di', $bonusReceived, $userId);
            $stmt->execute();

        } elseif ($action === 'add_commission') {
            $commission = $data['commission'] ?? 0;

            $stmt = $this->db->prepare("
                UPDATE referral_stats
                SET total_commission_earned = total_commission_earned + ?,
                    total_bonus_received = total_bonus_received + ?
                WHERE user_id = ?
            ");
            $stmt->bind_param('ddi', $commission, $commission, $userId);
            $stmt->execute();
        }
    }

    /**
     * Erstellt Referral Stats für User
     */
    private function createReferralStats(int $userId): void {
        $stmt = $this->db->prepare("
            INSERT INTO referral_stats (user_id)
            VALUES (?)
            ON DUPLICATE KEY UPDATE user_id=user_id
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    /**
     * Gibt Settings zurück
     */
    private function getSettings(): array {
        $result = $this->db->query("
            SELECT setting_key, setting_value
            FROM bank_settings
            WHERE setting_key IN ('referral_bonus_coins', 'referral_commission_rate')
        ");

        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $settings['referral_bonus_coins'] = $settings['referral_bonus_coins'] ?? self::DEFAULT_BONUS_COINS;
        $settings['referral_commission_rate'] = $settings['referral_commission_rate'] ?? self::DEFAULT_COMMISSION_RATE;

        return $settings;
    }

    /**
     * Admin: Gibt Top Referrer zurück
     */
    public function getTopReferrers(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT
                rs.*,
                u.username,
                u.email
            FROM referral_stats rs
            JOIN users u ON rs.user_id = u.id
            ORDER BY rs.total_commission_earned DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $topReferrers = [];
        while ($row = $result->fetch_assoc()) {
            $topReferrers[] = $row;
        }

        return $topReferrers;
    }

    /**
     * Prüft ob User 10 Quizzes abgeschlossen hat und zahlt ggf. Registrierungs-Bonus aus
     *
     * WICHTIG: Diese Methode wird nach JEDEM Quiz-Ende (endSession) aufgerufen
     * Sie zahlt nur EINMAL die 300 Bonus Coins aus - nach dem 10. Quiz
     *
     * @param int $userId Der User der gerade ein Quiz beendet hat
     * @return array|null
     */
    public function checkAndPayRegistrationBonus(int $userId): ?array {
        try {
            // 1. Prüfe ob User geworben wurde
            $stmt = $this->db->prepare("
                SELECT referred_by FROM users WHERE id = ?
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result || !$result['referred_by']) {
                // User wurde nicht geworben
                return null;
            }

            $referrerId = (int)$result['referred_by'];

            // 2. Prüfe ob Bonus bereits ausgezahlt wurde
            $stmt = $this->db->prepare("
                SELECT registration_bonus_paid FROM referral_stats WHERE user_id = ?
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stats = $stmt->get_result()->fetch_assoc();

            if ($stats && $stats['registration_bonus_paid']) {
                // Bonus wurde bereits ausgezahlt
                return null;
            }

            // 3. Zähle abgeschlossene Quizzes
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as completed_quizzes
                FROM quiz_sessions
                WHERE user_id = ? AND status = 'completed'
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $quizCount = $stmt->get_result()->fetch_assoc();

            $completedQuizzes = (int)$quizCount['completed_quizzes'];

            if ($completedQuizzes < 10) {
                // Noch nicht 10 Quizzes abgeschlossen
                return null;
            }

            // 4. ALLE Bedingungen erfüllt! Zahle 300 Bonus Coins an BEIDE aus
            $this->db->begin_transaction();

            $settings = $this->getSettings();
            $bonusCoins = (float)$settings['referral_bonus_coins'];

            $coinManager = new \ModernQuiz\Modules\Coins\CoinManager($this->db);

            // Geworbener bekommt 300 Bonus Coins
            $coinManager->addCoins(
                $userId,
                0,
                $bonusCoins,
                \ModernQuiz\Modules\Coins\CoinManager::TX_REFERRAL_BONUS,
                'referral',
                $referrerId,
                "Referral-Bonus freigeschaltet: 10 Quizzes abgeschlossen!",
                ['referrer_user_id' => $referrerId, 'completed_quizzes' => $completedQuizzes]
            );

            // Werber bekommt 300 Bonus Coins
            $coinManager->addCoins(
                $referrerId,
                0,
                $bonusCoins,
                \ModernQuiz\Modules\Coins\CoinManager::TX_REFERRAL_BONUS,
                'referral',
                $userId,
                "Referral-Bonus: User {$userId} hat 10 Quizzes abgeschlossen!",
                ['referred_user_id' => $userId, 'completed_quizzes' => $completedQuizzes]
            );

            // 5. Markiere Bonus als ausgezahlt für BEIDE
            $stmt = $this->db->prepare("
                UPDATE referral_stats
                SET registration_bonus_paid = TRUE,
                    registration_bonus_paid_at = NOW(),
                    total_bonus_received = total_bonus_received + ?
                WHERE user_id IN (?, ?)
            ");
            $stmt->bind_param('dii', $bonusCoins, $userId, $referrerId);
            $stmt->execute();

            $this->db->commit();

            return [
                'success' => true,
                'bonus_paid' => $bonusCoins,
                'referred_user_id' => $userId,
                'referrer_user_id' => $referrerId,
                'completed_quizzes' => $completedQuizzes,
                'message' => "Glückwunsch! {$bonusCoins} Bonus Coins freigeschaltet nach {$completedQuizzes} Quizzes!"
            ];

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log("ReferralManager::checkAndPayRegistrationBonus error: " . $e->getMessage());
            return null;
        }
    }
}

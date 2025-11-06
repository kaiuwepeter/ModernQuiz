<?php
// src/modules/bank/BankManager.php

namespace ModernQuiz\Modules\Bank;

use DateTime;

/**
 * BankManager - Verwaltet Festgeld-Einlagen
 *
 * Features:
 * - 30 Tage Festgeld mit 4% Zinsen
 * - Vorzeitige Kündigung mit 12% Strafgebühr
 * - Automatische Zinsberechnung
 * - Admin-Verwaltung
 */
class BankManager {
    private $db;

    // Default Settings
    private const DEFAULT_INTEREST_RATE = 4.00;  // 4%
    private const DEFAULT_DURATION_DAYS = 30;
    private const DEFAULT_PENALTY_RATE = 12.00;  // 12%

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Erstellt eine neue Festgeld-Einlage
     *
     * @param int $userId
     * @param int $coins
     * @param int $bonusCoins
     * @return array
     */
    public function createDeposit(int $userId, int $coins, int $bonusCoins): array {
        // Validierung
        if ($coins < 0 || $bonusCoins < 0) {
            return ['success' => false, 'error' => 'Beträge können nicht negativ sein'];
        }

        if ($coins === 0 && $bonusCoins === 0) {
            return ['success' => false, 'error' => 'Mindestens eine Coin-Art muss > 0 sein'];
        }

        // Hole Einstellungen
        $settings = $this->getSettings();
        $minDeposit = (int)$settings['min_deposit'];
        $maxDeposit = (int)$settings['max_deposit'];
        $totalDeposit = $coins + $bonusCoins;

        if ($totalDeposit < $minDeposit) {
            return ['success' => false, 'error' => "Mindesteinlage beträgt {$minDeposit} Coins"];
        }

        if ($totalDeposit > $maxDeposit) {
            return ['success' => false, 'error' => "Maximaleinlage beträgt {$maxDeposit} Coins"];
        }

        // Prüfe ob User genug Coins hat
        $userCoins = $this->getUserWalletBalance($userId);
        if ($userCoins['coins'] < $coins || $userCoins['bonus_coins'] < $bonusCoins) {
            return ['success' => false, 'error' => 'Nicht genug Coins verfügbar'];
        }

        try {
            $this->db->begin_transaction();

            // 1. Coins von user_stats abziehen
            $coinManager = new \ModernQuiz\Modules\Coins\CoinManager($this->db);
            $result = $coinManager->deductCoins(
                $userId,
                $totalDeposit,
                \ModernQuiz\Modules\Coins\CoinManager::TX_ADMIN_ADJUSTMENT,
                'bank_deposit',
                null,
                'Einzahlung ins Festgeld-Konto'
            );

            if (!$result['success']) {
                $this->db->rollback();
                return $result;
            }

            // 2. Berechne Fälligkeitsdatum
            $durationDays = (int)$settings['duration_days'];
            $depositDate = new DateTime();
            $maturityDate = (clone $depositDate)->modify("+{$durationDays} days");

            // 3. Berechne erwartete Zinsen
            $interestRate = (float)$settings['interest_rate'];
            $interestEarned = (int)round($totalDeposit * ($interestRate / 100));

            // 4. Erstelle Einlage
            $stmt = $this->db->prepare("
                INSERT INTO bank_deposits (
                    user_id,
                    coins_deposited,
                    bonus_coins_deposited,
                    interest_rate,
                    interest_earned,
                    duration_days,
                    maturity_date,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
            ");

            $maturityDateStr = $maturityDate->format('Y-m-d H:i:s');
            $stmt->bind_param(
                'iiiiiis',
                $userId,
                $coins,
                $bonusCoins,
                $interestRate,
                $interestEarned,
                $durationDays,
                $maturityDateStr
            );
            $stmt->execute();
            $depositId = $stmt->insert_id;

            // 5. Erstelle oder Update Bank Account Balance
            $this->updateBankBalance($userId, $coins, $bonusCoins, 'add');

            // 6. Log Bank Transaction (Kontoauszug)
            $this->logBankTransaction(
                $userId,
                $depositId,
                'deposit',
                $coins,
                $bonusCoins,
                "Festgeld-Einlage erstellt (#{$depositId})",
                ['deposit_id' => $depositId, 'duration_days' => $durationDays, 'interest_rate' => $interestRate]
            );

            $this->db->commit();

            return [
                'success' => true,
                'deposit_id' => $depositId,
                'message' => 'Festgeld-Einlage erfolgreich erstellt',
                'details' => [
                    'deposited' => $totalDeposit,
                    'coins' => $coins,
                    'bonus_coins' => $bonusCoins,
                    'interest_rate' => $interestRate,
                    'expected_interest' => $interestEarned,
                    'expected_payout' => $totalDeposit + $interestEarned,
                    'maturity_date' => $maturityDateStr,
                    'duration_days' => $durationDays
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("BankManager::createDeposit error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Erstellen der Einlage'];
        }
    }

    /**
     * Beendet eine Einlage vorzeitig (mit Strafgebühr)
     *
     * @param int $userId
     * @param int $depositId
     * @return array
     */
    public function withdrawEarly(int $userId, int $depositId): array {
        try {
            $this->db->begin_transaction();

            // Hole Einlage
            $deposit = $this->getDepositById($depositId);

            if (!$deposit) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Einlage nicht gefunden'];
            }

            // Prüfe Besitzer
            if ((int)$deposit['user_id'] !== $userId) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Diese Einlage gehört dir nicht'];
            }

            // Prüfe Status
            if ($deposit['status'] !== 'active') {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Einlage ist nicht aktiv'];
            }

            // Prüfe ob gesperrt
            if ($deposit['is_locked']) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Einlage ist gesperrt'];
            }

            // Berechne Strafgebühr
            $settings = $this->getSettings();
            $penaltyRate = (float)$settings['penalty_rate'];
            $totalDeposited = (int)$deposit['total_deposited'];
            $penaltyFee = (int)round($totalDeposited * ($penaltyRate / 100));

            // Auszahlungsbetrag = Einlage - Strafgebühr (KEINE Zinsen)
            $totalPayout = $totalDeposited - $penaltyFee;

            // Verteile Auszahlung proportional auf Coins und Bonus Coins
            $coinsDeposited = (int)$deposit['coins_deposited'];
            $bonusCoinsDeposited = (int)$deposit['bonus_coins_deposited'];

            $coinsPayout = (int)round(($coinsDeposited / $totalDeposited) * $totalPayout);
            $bonusCoinsPayout = $totalPayout - $coinsPayout;

            // Update Einlage
            $stmt = $this->db->prepare("
                UPDATE bank_deposits
                SET status = 'cancelled',
                    is_early_withdrawal = 1,
                    withdrawal_date = NOW(),
                    cancelled_at = NOW(),
                    penalty_fee = ?,
                    coins_payout = ?,
                    bonus_coins_payout = ?,
                    total_payout = ?
                WHERE id = ?
            ");
            $stmt->bind_param('iiiii', $penaltyFee, $coinsPayout, $bonusCoinsPayout, $totalPayout, $depositId);
            $stmt->execute();

            // Coins zurück an User (über CoinManager)
            $coinManager = new \ModernQuiz\Modules\Coins\CoinManager($this->db);
            $coinManager->addCoins(
                $userId,
                $coinsPayout,
                $bonusCoinsPayout,
                \ModernQuiz\Modules\Coins\CoinManager::TX_ADMIN_ADJUSTMENT,
                'bank_withdrawal',
                $depositId,
                'Vorzeitige Auszahlung (mit Strafgebühr)',
                ['penalty_fee' => $penaltyFee, 'penalty_rate' => $penaltyRate]
            );

            // Update Bank Balance
            $this->updateBankBalance($userId, $coinsDeposited, $bonusCoinsDeposited, 'subtract');

            // Log Transactions
            $this->logBankTransaction(
                $userId,
                $depositId,
                'early_withdrawal',
                $coinsPayout,
                $bonusCoinsPayout,
                "Vorzeitige Auszahlung (#{$depositId})",
                ['penalty_fee' => $penaltyFee, 'penalty_rate' => $penaltyRate]
            );

            $this->logBankTransaction(
                $userId,
                $depositId,
                'penalty',
                -((int)round(($coinsDeposited / $totalDeposited) * $penaltyFee)),
                -((int)round(($bonusCoinsDeposited / $totalDeposited) * $penaltyFee)),
                "Strafgebühr {$penaltyRate}% (#{$depositId})",
                ['penalty_rate' => $penaltyRate]
            );

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Einlage vorzeitig beendet',
                'details' => [
                    'deposited' => $totalDeposited,
                    'penalty_fee' => $penaltyFee,
                    'penalty_rate' => $penaltyRate,
                    'payout' => $totalPayout,
                    'coins_payout' => $coinsPayout,
                    'bonus_coins_payout' => $bonusCoinsPayout,
                    'interest_lost' => $deposit['interest_earned']
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("BankManager::withdrawEarly error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler bei vorzeitiger Auszahlung'];
        }
    }

    /**
     * Zahlt eine fällige Einlage aus (mit Zinsen)
     *
     * @param int $userId
     * @param int $depositId
     * @return array
     */
    public function withdrawMatured(int $userId, int $depositId): array {
        try {
            $this->db->begin_transaction();

            // Hole Einlage
            $deposit = $this->getDepositById($depositId);

            if (!$deposit) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Einlage nicht gefunden'];
            }

            // Prüfe Besitzer
            if ((int)$deposit['user_id'] !== $userId) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Diese Einlage gehört dir nicht'];
            }

            // Prüfe Status
            if ($deposit['status'] !== 'matured' && $deposit['status'] !== 'active') {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Einlage kann nicht ausgezahlt werden'];
            }

            // Prüfe ob gesperrt
            if ($deposit['is_locked']) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Einlage ist gesperrt'];
            }

            // Prüfe ob wirklich fällig
            $now = new DateTime();
            $maturityDate = new DateTime($deposit['maturity_date']);

            if ($now < $maturityDate) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Einlage ist noch nicht fällig. Verwende vorzeitige Auszahlung.'];
            }

            // Berechne Auszahlung MIT Zinsen
            $totalDeposited = (int)$deposit['total_deposited'];
            $interestEarned = (int)$deposit['interest_earned'];
            $totalPayout = $totalDeposited + $interestEarned;

            // Verteile Auszahlung + Zinsen proportional
            $coinsDeposited = (int)$deposit['coins_deposited'];
            $bonusCoinsDeposited = (int)$deposit['bonus_coins_deposited'];

            // Hauptbetrag proportional
            $coinsPayout = $coinsDeposited;
            $bonusCoinsPayout = $bonusCoinsDeposited;

            // Zinsen proportional verteilen
            $coinsInterest = (int)round(($coinsDeposited / $totalDeposited) * $interestEarned);
            $bonusCoinsInterest = $interestEarned - $coinsInterest;

            $coinsPayout += $coinsInterest;
            $bonusCoinsPayout += $bonusCoinsInterest;

            // Update Einlage
            $stmt = $this->db->prepare("
                UPDATE bank_deposits
                SET status = 'completed',
                    withdrawal_date = NOW(),
                    coins_payout = ?,
                    bonus_coins_payout = ?,
                    total_payout = ?
                WHERE id = ?
            ");
            $stmt->bind_param('iiii', $coinsPayout, $bonusCoinsPayout, $totalPayout, $depositId);
            $stmt->execute();

            // Coins zurück an User
            $coinManager = new \ModernQuiz\Modules\Coins\CoinManager($this->db);

            // Hauptbetrag
            $coinManager->addCoins(
                $userId,
                $coinsDeposited,
                $bonusCoinsDeposited,
                \ModernQuiz\Modules\Coins\CoinManager::TX_ADMIN_ADJUSTMENT,
                'bank_withdrawal',
                $depositId,
                'Auszahlung Festgeld (Hauptbetrag)',
                ['deposit_id' => $depositId]
            );

            // Zinsen
            $coinManager->addCoins(
                $userId,
                $coinsInterest,
                $bonusCoinsInterest,
                \ModernQuiz\Modules\Coins\CoinManager::TX_ADMIN_ADJUSTMENT,
                'bank_interest',
                $depositId,
                'Zinsgutschrift',
                ['deposit_id' => $depositId, 'interest_rate' => $deposit['interest_rate']]
            );

            // Update Bank Balance
            $this->updateBankBalance($userId, $coinsDeposited, $bonusCoinsDeposited, 'subtract');

            // Update Bank Account Stats
            $stmt = $this->db->prepare("
                UPDATE bank_account_balances
                SET total_interest_earned = total_interest_earned + ?,
                    last_withdrawal_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->bind_param('ii', $interestEarned, $userId);
            $stmt->execute();

            // Log Transactions
            $this->logBankTransaction(
                $userId,
                $depositId,
                'withdrawal',
                $coinsDeposited,
                $bonusCoinsDeposited,
                "Auszahlung Festgeld (#{$depositId})",
                ['deposit_id' => $depositId]
            );

            $this->logBankTransaction(
                $userId,
                $depositId,
                'interest',
                $coinsInterest,
                $bonusCoinsInterest,
                "Zinsgutschrift {$deposit['interest_rate']}% (#{$depositId})",
                ['deposit_id' => $depositId, 'interest_rate' => $deposit['interest_rate']]
            );

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Einlage erfolgreich ausgezahlt',
                'details' => [
                    'deposited' => $totalDeposited,
                    'interest_earned' => $interestEarned,
                    'total_payout' => $totalPayout,
                    'coins_payout' => $coinsPayout,
                    'bonus_coins_payout' => $bonusCoinsPayout
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("BankManager::withdrawMatured error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler bei der Auszahlung'];
        }
    }

    /**
     * Gibt alle Einlagen eines Users zurück
     */
    public function getUserDeposits(int $userId, array $filters = []): array {
        $where = ['user_id = ?'];
        $params = [$userId];
        $types = 'i';

        if (isset($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT *,
                   DATEDIFF(maturity_date, NOW()) as days_remaining
            FROM bank_deposits
            WHERE {$whereClause}
            ORDER BY created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $deposits = [];
        while ($row = $result->fetch_assoc()) {
            $deposits[] = $row;
        }

        return $deposits;
    }

    /**
     * Gibt eine einzelne Einlage zurück
     */
    public function getDepositById(int $depositId): ?array {
        $stmt = $this->db->prepare("
            SELECT *,
                   DATEDIFF(maturity_date, NOW()) as days_remaining
            FROM bank_deposits
            WHERE id = ?
        ");
        $stmt->bind_param('i', $depositId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Gibt alle Bank-Einstellungen zurück
     */
    private function getSettings(): array {
        $result = $this->db->query("SELECT setting_key, setting_value FROM bank_settings");
        $settings = [];

        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // Defaults falls nicht vorhanden
        $settings['interest_rate'] = $settings['interest_rate'] ?? self::DEFAULT_INTEREST_RATE;
        $settings['duration_days'] = $settings['duration_days'] ?? self::DEFAULT_DURATION_DAYS;
        $settings['penalty_rate'] = $settings['penalty_rate'] ?? self::DEFAULT_PENALTY_RATE;
        $settings['min_deposit'] = $settings['min_deposit'] ?? 100;
        $settings['max_deposit'] = $settings['max_deposit'] ?? 100000;
        $settings['bank_enabled'] = $settings['bank_enabled'] ?? 1;

        return $settings;
    }

    /**
     * Gibt User Wallet Balance zurück (aus user_stats)
     */
    private function getUserWalletBalance(int $userId): array {
        $stmt = $this->db->prepare("SELECT coins, bonus_coins FROM user_stats WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result ?: ['coins' => 0, 'bonus_coins' => 0];
    }

    /**
     * Gibt User Bank Balance zurück
     */
    public function getUserBankBalance(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM bank_account_balances WHERE user_id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            // Erstelle neuen Eintrag
            $stmt = $this->db->prepare("
                INSERT INTO bank_account_balances (user_id) VALUES (?)
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();

            return [
                'coins_balance' => 0,
                'bonus_coins_balance' => 0,
                'total_balance' => 0,
                'total_deposits_count' => 0,
                'total_withdrawals_count' => 0,
                'total_interest_earned' => 0,
                'total_penalties_paid' => 0
            ];
        }

        return $result;
    }

    /**
     * Update Bank Balance
     */
    private function updateBankBalance(int $userId, int $coins, int $bonusCoins, string $operation): void {
        // Stelle sicher dass Eintrag existiert
        $this->getUserBankBalance($userId);

        if ($operation === 'add') {
            $stmt = $this->db->prepare("
                UPDATE bank_account_balances
                SET coins_balance = coins_balance + ?,
                    bonus_coins_balance = bonus_coins_balance + ?,
                    total_deposits_count = total_deposits_count + 1,
                    last_deposit_at = NOW()
                WHERE user_id = ?
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE bank_account_balances
                SET coins_balance = coins_balance - ?,
                    bonus_coins_balance = bonus_coins_balance - ?,
                    total_withdrawals_count = total_withdrawals_count + 1,
                    last_withdrawal_at = NOW()
                WHERE user_id = ?
            ");
        }

        $stmt->bind_param('iii', $coins, $bonusCoins, $userId);
        $stmt->execute();
    }

    /**
     * Loggt eine Bank-Transaktion (für Kontoauszug)
     */
    private function logBankTransaction(
        int $userId,
        ?int $depositId,
        string $transactionType,
        int $coinsAmount,
        int $bonusCoinsAmount,
        string $description,
        ?array $metadata = null
    ): void {
        // Hole aktuellen Bank-Balance
        $balance = $this->getUserBankBalance($userId);

        $coinsBefore = $balance['coins_balance'] ?? 0;
        $bonusCoinsBefore = $balance['bonus_coins_balance'] ?? 0;

        // Berechne nach Balance
        if ($transactionType === 'deposit') {
            $coinsAfter = $coinsBefore + $coinsAmount;
            $bonusCoinsAfter = $bonusCoinsBefore + $bonusCoinsAmount;
        } else {
            $coinsAfter = $coinsBefore - abs($coinsAmount);
            $bonusCoinsAfter = $bonusCoinsBefore - abs($bonusCoinsAmount);
        }

        $metadataJson = $metadata ? json_encode($metadata) : null;

        $stmt = $this->db->prepare("
            INSERT INTO bank_transactions (
                user_id, deposit_id, transaction_type,
                coins_amount, bonus_coins_amount,
                coins_balance_before, bonus_coins_balance_before,
                coins_balance_after, bonus_coins_balance_after,
                description, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            'iisiiiiiiss',
            $userId,
            $depositId,
            $transactionType,
            $coinsAmount,
            $bonusCoinsAmount,
            $coinsBefore,
            $bonusCoinsBefore,
            $coinsAfter,
            $bonusCoinsAfter,
            $description,
            $metadataJson
        );

        $stmt->execute();
    }

    /**
     * Cron-Job: Markiert fällige Einlagen als 'matured'
     */
    public function processMaturingDeposits(): array {
        $stmt = $this->db->prepare("
            UPDATE bank_deposits
            SET status = 'matured'
            WHERE status = 'active'
            AND maturity_date <= NOW()
            AND is_locked = 0
        ");
        $stmt->execute();
        $affected = $stmt->affected_rows;

        return [
            'success' => true,
            'matured_deposits' => $affected
        ];
    }

    /**
     * Admin: Gibt alle Einlagen zurück
     */
    public function getAllDeposits(array $filters = []): array {
        $where = ['1=1'];
        $params = [];
        $types = '';

        if (isset($filters['user_id'])) {
            $where[] = 'bd.user_id = ?';
            $params[] = $filters['user_id'];
            $types .= 'i';
        }

        if (isset($filters['status'])) {
            $where[] = 'bd.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (isset($filters['is_locked']) && $filters['is_locked']) {
            $where[] = 'bd.is_locked = 1';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT bd.*,
                   u.username,
                   u.email,
                   DATEDIFF(bd.maturity_date, NOW()) as days_remaining
            FROM bank_deposits bd
            JOIN users u ON bd.user_id = u.id
            WHERE {$whereClause}
            ORDER BY bd.created_at DESC
        ";

        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }

        $deposits = [];
        while ($row = $result->fetch_assoc()) {
            $deposits[] = $row;
        }

        return $deposits;
    }

    /**
     * Admin: Sperrt eine Einlage
     */
    public function lockDeposit(int $depositId, int $adminId, string $reason): array {
        $stmt = $this->db->prepare("
            UPDATE bank_deposits
            SET is_locked = 1,
                locked_by = ?,
                locked_at = NOW(),
                lock_reason = ?
            WHERE id = ?
        ");
        $stmt->bind_param('isi', $adminId, $reason, $depositId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Einlage gesperrt'];
        }

        return ['success' => false, 'error' => 'Fehler beim Sperren'];
    }

    /**
     * Admin: Entsperrt eine Einlage
     */
    public function unlockDeposit(int $depositId, int $adminId): array {
        $stmt = $this->db->prepare("
            UPDATE bank_deposits
            SET is_locked = 0,
                locked_by = NULL,
                locked_at = NULL,
                lock_reason = NULL
            WHERE id = ?
        ");
        $stmt->bind_param('i', $depositId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Einlage entsperrt'];
        }

        return ['success' => false, 'error' => 'Fehler beim Entsperren'];
    }

    /**
     * Admin: Gibt eine Einlage sofort frei (ohne Wartezeit)
     */
    public function releaseDeposit(int $depositId, int $adminId): array {
        try {
            $this->db->begin_transaction();

            $deposit = $this->getDepositById($depositId);

            if (!$deposit || $deposit['status'] !== 'active') {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Einlage nicht aktiv'];
            }

            // Setze Status auf matured
            $stmt = $this->db->prepare("
                UPDATE bank_deposits
                SET status = 'matured',
                    maturity_date = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param('i', $depositId);
            $stmt->execute();

            // Log Admin Action
            $this->logAdminAction(
                $adminId,
                (int)$deposit['user_id'],
                'bank_deposit_release',
                "Einlage #{$depositId} sofort freigegeben",
                ['deposit_id' => $depositId]
            );

            $this->db->commit();

            return ['success' => true, 'message' => 'Einlage sofort freigegeben'];

        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => 'Fehler beim Freigeben'];
        }
    }

    /**
     * Loggt Admin-Aktion
     */
    private function logAdminAction(
        int $adminId,
        ?int $targetUserId,
        string $actionType,
        string $details,
        ?array $metadata = null
    ): void {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $metadataJson = $metadata ? json_encode($metadata) : null;

        $stmt = $this->db->prepare("
            INSERT INTO admin_actions_log (
                admin_user_id, target_user_id, action_type,
                action_details, metadata, ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            'iisssss',
            $adminId,
            $targetUserId,
            $actionType,
            $details,
            $metadataJson,
            $ipAddress,
            $userAgent
        );

        $stmt->execute();
    }
}

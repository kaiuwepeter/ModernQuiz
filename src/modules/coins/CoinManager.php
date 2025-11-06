<?php
// src/modules/coins/CoinManager.php

namespace ModernQuiz\Modules\Coins;

/**
 * CoinManager - Zentraler Service für Coin-Verwaltung
 *
 * Features:
 * - Coins und Bonus Coins verwalten
 * - Vollständiger Audit Trail für alle Transaktionen
 * - Transaction-sichere Operationen
 * - Support für verschiedene Transaction-Typen
 */
class CoinManager {
    private $db;

    // Transaction Types
    public const TX_VOUCHER_REDEMPTION = 'voucher_redemption';
    public const TX_QUIZ_REWARD = 'quiz_reward';
    public const TX_SHOP_PURCHASE = 'shop_purchase';
    public const TX_ADMIN_ADJUSTMENT = 'admin_adjustment';
    public const TX_REFERRAL_BONUS = 'referral_bonus';
    public const TX_ACHIEVEMENT = 'achievement';
    public const TX_DAILY_REWARD = 'daily_reward';
    public const TX_WITHDRAWAL = 'withdrawal';

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Fügt Coins zu einem User hinzu
     *
     * @param int $userId
     * @param int $coins Normale Coins (können ausgezahlt werden)
     * @param int $bonusCoins Bonus Coins (können NICHT ausgezahlt werden)
     * @param string $transactionType
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $description
     * @param array|null $metadata
     * @return array
     */
    public function addCoins(
        int $userId,
        int $coins = 0,
        int $bonusCoins = 0,
        string $transactionType = self::TX_ADMIN_ADJUSTMENT,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?array $metadata = null
    ): array {
        if ($coins < 0 || $bonusCoins < 0) {
            return ['success' => false, 'error' => 'Coins müssen positiv sein'];
        }

        if ($coins === 0 && $bonusCoins === 0) {
            return ['success' => false, 'error' => 'Mindestens eine Coin-Art muss > 0 sein'];
        }

        try {
            $this->db->begin_transaction();

            // Hole aktuelle Coins
            $current = $this->getUserCoins($userId);
            if (!$current) {
                // User Stats existieren nicht, erstelle sie
                $this->createUserStats($userId);
                $current = ['coins' => 0, 'bonus_coins' => 0];
            }

            $coinsBefore = $current['coins'];
            $bonusCoinsBefore = $current['bonus_coins'];
            $coinsAfter = $coinsBefore + $coins;
            $bonusCoinsAfter = $bonusCoinsBefore + $bonusCoins;

            // Update user_stats
            $stmt = $this->db->prepare("
                UPDATE user_stats
                SET coins = coins + ?, bonus_coins = bonus_coins + ?
                WHERE user_id = ?
            ");
            $stmt->bind_param('iii', $coins, $bonusCoins, $userId);
            $stmt->execute();

            // Log Transaction
            $this->logTransaction(
                $userId,
                $transactionType,
                $coins,
                $bonusCoins,
                $coinsBefore,
                $bonusCoinsBefore,
                $coinsAfter,
                $bonusCoinsAfter,
                $referenceType,
                $referenceId,
                $description,
                $metadata
            );

            $this->db->commit();

            return [
                'success' => true,
                'coins_added' => $coins,
                'bonus_coins_added' => $bonusCoins,
                'new_balance' => [
                    'coins' => $coinsAfter,
                    'bonus_coins' => $bonusCoinsAfter
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("CoinManager::addCoins error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Hinzufügen von Coins'];
        }
    }

    /**
     * Entfernt Coins von einem User (für Shop-Käufe etc.)
     *
     * WICHTIG: Bonus Coins werden ZUERST ausgegeben, dann normale Coins
     *
     * @param int $userId
     * @param int $amount Gesamtbetrag
     * @param string $transactionType
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $description
     * @param array|null $metadata
     * @return array
     */
    public function deductCoins(
        int $userId,
        int $amount,
        string $transactionType = self::TX_SHOP_PURCHASE,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?array $metadata = null
    ): array {
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Betrag muss positiv sein'];
        }

        try {
            $this->db->begin_transaction();

            // Hole aktuelle Coins
            $current = $this->getUserCoins($userId);
            if (!$current) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User-Statistiken nicht gefunden'];
            }

            $coinsBefore = $current['coins'];
            $bonusCoinsBefore = $current['bonus_coins'];
            $totalAvailable = $coinsBefore + $bonusCoinsBefore;

            // Prüfe ob genug Coins vorhanden
            if ($totalAvailable < $amount) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Nicht genug Coins verfügbar'];
            }

            // Berechne wie viel von Bonus Coins und normalen Coins abgezogen wird
            // Strategie: Bonus Coins zuerst verwenden
            $bonusCoinsToDeduct = min($bonusCoinsBefore, $amount);
            $coinsToDeduct = $amount - $bonusCoinsToDeduct;

            $coinsAfter = $coinsBefore - $coinsToDeduct;
            $bonusCoinsAfter = $bonusCoinsBefore - $bonusCoinsToDeduct;

            // Update user_stats
            $stmt = $this->db->prepare("
                UPDATE user_stats
                SET coins = ?, bonus_coins = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param('iii', $coinsAfter, $bonusCoinsAfter, $userId);
            $stmt->execute();

            // Log Transaction (negative Werte)
            $this->logTransaction(
                $userId,
                $transactionType,
                -$coinsToDeduct,
                -$bonusCoinsToDeduct,
                $coinsBefore,
                $bonusCoinsBefore,
                $coinsAfter,
                $bonusCoinsAfter,
                $referenceType,
                $referenceId,
                $description,
                $metadata
            );

            $this->db->commit();

            return [
                'success' => true,
                'amount_deducted' => $amount,
                'from_coins' => $coinsToDeduct,
                'from_bonus_coins' => $bonusCoinsToDeduct,
                'new_balance' => [
                    'coins' => $coinsAfter,
                    'bonus_coins' => $bonusCoinsAfter
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("CoinManager::deductCoins error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Abziehen von Coins'];
        }
    }

    /**
     * Gibt die aktuellen Coins eines Users zurück
     *
     * @param int $userId
     * @return array|null ['coins' => int, 'bonus_coins' => int]
     */
    public function getUserCoins(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT coins, bonus_coins
            FROM user_stats
            WHERE user_id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result;
    }

    /**
     * Erstellt user_stats Eintrag falls nicht vorhanden
     */
    private function createUserStats(int $userId): void {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO user_stats (user_id, coins, bonus_coins)
            VALUES (?, 100, 0)
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    /**
     * Loggt eine Coin-Transaction
     */
    private function logTransaction(
        int $userId,
        string $transactionType,
        int $coinsChange,
        int $bonusCoinsChange,
        int $coinsBefore,
        int $bonusCoinsBefore,
        int $coinsAfter,
        int $bonusCoinsAfter,
        ?string $referenceType,
        ?int $referenceId,
        ?string $description,
        ?array $metadata
    ): void {
        $metadataJson = $metadata ? json_encode($metadata) : null;

        $stmt = $this->db->prepare("
            INSERT INTO coin_transactions (
                user_id, transaction_type,
                coins_change, bonus_coins_change,
                coins_before, bonus_coins_before,
                coins_after, bonus_coins_after,
                reference_type, reference_id,
                description, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            'isiiiiiisiss',
            $userId,
            $transactionType,
            $coinsChange,
            $bonusCoinsChange,
            $coinsBefore,
            $bonusCoinsBefore,
            $coinsAfter,
            $bonusCoinsAfter,
            $referenceType,
            $referenceId,
            $description,
            $metadataJson
        );

        $stmt->execute();
    }

    /**
     * Gibt die Transaction History eines Users zurück
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUserTransactions(int $userId, int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM coin_transactions
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            // Parse metadata if exists
            if ($row['metadata']) {
                $row['metadata'] = json_decode($row['metadata'], true);
            }
            $transactions[] = $row;
        }

        return $transactions;
    }

    /**
     * Gibt Statistiken über Coin-Transaktionen zurück (Admin)
     *
     * @param array $filters
     * @return array
     */
    public function getTransactionStats(array $filters = []): array {
        $where = ['1=1'];
        $params = [];
        $types = '';

        if (isset($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
            $types .= 'i';
        }

        if (isset($filters['transaction_type'])) {
            $where[] = "transaction_type = ?";
            $params[] = $filters['transaction_type'];
            $types .= 's';
        }

        if (isset($filters['from_date'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['from_date'];
            $types .= 's';
        }

        if (isset($filters['to_date'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['to_date'];
            $types .= 's';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                COUNT(*) as transaction_count,
                SUM(CASE WHEN coins_change > 0 THEN coins_change ELSE 0 END) as total_coins_added,
                SUM(CASE WHEN coins_change < 0 THEN ABS(coins_change) ELSE 0 END) as total_coins_deducted,
                SUM(CASE WHEN bonus_coins_change > 0 THEN bonus_coins_change ELSE 0 END) as total_bonus_coins_added,
                SUM(CASE WHEN bonus_coins_change < 0 THEN ABS(bonus_coins_change) ELSE 0 END) as total_bonus_coins_deducted,
                transaction_type,
                COUNT(DISTINCT user_id) as unique_users
            FROM coin_transactions
            WHERE {$whereClause}
            GROUP BY transaction_type
            ORDER BY transaction_count DESC
        ";

        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }

        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }

        return $stats;
    }

    /**
     * Gibt die Top Coin-Verdienste zurück (Leaderboard)
     *
     * @param int $limit
     * @param bool $includeBonus Bonus Coins einbeziehen?
     * @return array
     */
    public function getTopEarners(int $limit = 10, bool $includeBonus = false): array {
        if ($includeBonus) {
            $orderBy = "(us.coins + us.bonus_coins) DESC";
        } else {
            $orderBy = "us.coins DESC";
        }

        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.username,
                us.coins,
                us.bonus_coins,
                (us.coins + us.bonus_coins) as total_coins,
                us.level,
                us.total_points
            FROM user_stats us
            JOIN users u ON us.user_id = u.id
            ORDER BY {$orderBy}
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $topEarners = [];
        while ($row = $result->fetch_assoc()) {
            $topEarners[] = $row;
        }

        return $topEarners;
    }

    /**
     * Validiert ob User genug Coins hat
     *
     * @param int $userId
     * @param int $amount
     * @return bool
     */
    public function hasEnoughCoins(int $userId, int $amount): bool {
        $current = $this->getUserCoins($userId);
        if (!$current) {
            return false;
        }

        $totalAvailable = $current['coins'] + $current['bonus_coins'];
        return $totalAvailable >= $amount;
    }

    /**
     * Admin-Funktion: Setzt Coins eines Users (mit Logging)
     *
     * @param int $userId
     * @param int $coins
     * @param int $bonusCoins
     * @param int $adminId
     * @param string $reason
     * @return array
     */
    public function setCoins(int $userId, int $coins, int $bonusCoins, int $adminId, string $reason): array {
        if ($coins < 0 || $bonusCoins < 0) {
            return ['success' => false, 'error' => 'Coins können nicht negativ sein'];
        }

        try {
            $this->db->begin_transaction();

            // Hole aktuelle Coins
            $current = $this->getUserCoins($userId);
            if (!$current) {
                $this->createUserStats($userId);
                $current = ['coins' => 0, 'bonus_coins' => 0];
            }

            $coinsBefore = $current['coins'];
            $bonusCoinsBefore = $current['bonus_coins'];
            $coinsChange = $coins - $coinsBefore;
            $bonusCoinsChange = $bonusCoins - $bonusCoinsBefore;

            // Update user_stats
            $stmt = $this->db->prepare("
                UPDATE user_stats
                SET coins = ?, bonus_coins = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param('iii', $coins, $bonusCoins, $userId);
            $stmt->execute();

            // Log Transaction
            $metadata = [
                'admin_id' => $adminId,
                'reason' => $reason,
                'action' => 'set_coins'
            ];

            $this->logTransaction(
                $userId,
                self::TX_ADMIN_ADJUSTMENT,
                $coinsChange,
                $bonusCoinsChange,
                $coinsBefore,
                $bonusCoinsBefore,
                $coins,
                $bonusCoins,
                'admin_adjustment',
                $adminId,
                "Admin-Anpassung: {$reason}",
                $metadata
            );

            $this->db->commit();

            return [
                'success' => true,
                'new_balance' => [
                    'coins' => $coins,
                    'bonus_coins' => $bonusCoins
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("CoinManager::setCoins error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Setzen der Coins'];
        }
    }
}

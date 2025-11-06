<?php

namespace ModernQuiz\Modules\Shop;

use ModernQuiz\Core\Database;

class ShopSystem
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Holt alle verfügbaren Powerups
     */
    public function getPowerups(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM shop_powerups WHERE is_active = 1 ORDER BY price ASC"
        );
    }

    /**
     * Holt User-Inventar
     */
    public function getUserInventory(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT up.*, sp.name, sp.description, sp.effect_type, sp.icon
             FROM user_powerups up
             JOIN shop_powerups sp ON up.powerup_id = sp.id
             WHERE up.user_id = ? AND up.quantity > 0
             ORDER BY sp.name",
            [$userId]
        );
    }

    /**
     * Kauft ein Powerup
     *
     * @param int $userId User ID
     * @param int $powerupId Powerup ID
     * @param int $quantity Anzahl
     * @param string $currency Währung: 'coins', 'bonus_coins', oder 'auto' (Bonus Coins zuerst)
     */
    public function purchasePowerup(int $userId, int $powerupId, int $quantity = 1, string $currency = 'auto'): array
    {
        // Hole Powerup-Details
        $powerup = $this->db->fetch(
            "SELECT * FROM shop_powerups WHERE id = ? AND is_active = 1",
            [$powerupId]
        );

        if (!$powerup) {
            return ['success' => false, 'message' => 'Powerup nicht gefunden'];
        }

        // Hole User-Coins
        $user = $this->db->fetch(
            "SELECT coins, bonus_coins FROM user_stats WHERE user_id = ?",
            [$userId]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'User-Stats nicht gefunden'];
        }

        $totalCost = (float)($powerup['price'] * $quantity);
        $coins = (float)$user['coins'];
        $bonusCoins = (float)$user['bonus_coins'];

        // Validiere Währungswahl
        if (!in_array($currency, ['coins', 'bonus_coins', 'auto'])) {
            return ['success' => false, 'message' => 'Ungültige Währung'];
        }

        // Bestimme Zahlungsmethode
        $coinsToDeduct = 0;
        $bonusCoinsToDeduct = 0;

        if ($currency === 'coins') {
            // Nur normale Coins verwenden
            if ($coins < $totalCost) {
                return ['success' => false, 'message' => 'Nicht genügend Coins'];
            }
            $coinsToDeduct = $totalCost;

        } elseif ($currency === 'bonus_coins') {
            // Nur Bonus Coins verwenden
            if ($bonusCoins < $totalCost) {
                return ['success' => false, 'message' => 'Nicht genügend Bonus Coins'];
            }
            $bonusCoinsToDeduct = $totalCost;

        } else {
            // Auto: Bonus Coins zuerst, dann normale Coins
            $bonusCoinsToDeduct = min($bonusCoins, $totalCost);
            $coinsToDeduct = $totalCost - $bonusCoinsToDeduct;

            if ($coins < $coinsToDeduct) {
                return ['success' => false, 'message' => 'Nicht genügend Coins (brauche ' . $coinsToDeduct . ' Coins + ' . $bonusCoinsToDeduct . ' Bonus Coins)'];
            }
        }

        try {
            $this->db->beginTransaction();

            // Verwende CoinManager für Coin-Abzug (mit Transaction Logging)
            $coinManager = new \ModernQuiz\Modules\Coins\CoinManager($this->db->getConnection());

            $deductResult = $coinManager->deductCoins(
                $userId,
                (int)$totalCost, // Total amount
                \ModernQuiz\Modules\Coins\CoinManager::TX_SHOP_PURCHASE,
                'shop',
                $powerupId,
                "Shop-Kauf: {$powerup['name']} ({$quantity}x)",
                ['powerup_id' => $powerupId, 'quantity' => $quantity, 'currency' => $currency]
            );

            if (!$deductResult['success']) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Fehler beim Abbuchen: ' . ($deductResult['error'] ?? 'Unbekannter Fehler')];
            }

            // Füge zu Inventar hinzu
            $existing = $this->db->fetch(
                "SELECT id, quantity FROM user_powerups WHERE user_id = ? AND powerup_id = ?",
                [$userId, $powerupId]
            );

            if ($existing) {
                $this->db->query(
                    "UPDATE user_powerups SET quantity = quantity + ? WHERE id = ?",
                    [$quantity, $existing['id']]
                );
            } else {
                $this->db->query(
                    "INSERT INTO user_powerups (user_id, powerup_id, quantity) VALUES (?, ?, ?)",
                    [$userId, $powerupId, $quantity]
                );
            }

            // Speichere Kauf-Historie
            $this->db->query(
                "INSERT INTO shop_purchases (user_id, powerup_id, quantity, total_cost) VALUES (?, ?, ?, ?)",
                [$userId, $powerupId, $quantity, $totalCost]
            );

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Powerup erfolgreich gekauft',
                'remaining_coins' => $deductResult['coins_after'],
                'remaining_bonus_coins' => $deductResult['bonus_coins_after'],
                'currency_used' => $currency
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Fehler beim Kauf: ' . $e->getMessage()];
        }
    }

    /**
     * Verwendet ein Powerup
     */
    public function usePowerup(int $userId, int $powerupId): array
    {
        $powerup = $this->db->fetch(
            "SELECT up.*, sp.name, sp.effect_type, sp.effect_value
             FROM user_powerups up
             JOIN shop_powerups sp ON up.powerup_id = sp.id
             WHERE up.user_id = ? AND up.powerup_id = ? AND up.quantity > 0",
            [$userId, $powerupId]
        );

        if (!$powerup) {
            return ['success' => false, 'message' => 'Powerup nicht verfügbar'];
        }

        // Reduziere Quantity
        $this->db->query(
            "UPDATE user_powerups SET quantity = quantity - 1 WHERE user_id = ? AND powerup_id = ?",
            [$userId, $powerupId]
        );

        return [
            'success' => true,
            'powerup' => [
                'name' => $powerup['name'],
                'effect_type' => $powerup['effect_type'],
                'effect_value' => $powerup['effect_value']
            ]
        ];
    }

    /**
     * Holt Kauf-Historie
     */
    public function getPurchaseHistory(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT sp.name, sp.icon, p.quantity, p.total_cost, p.purchased_at
             FROM shop_purchases p
             JOIN shop_powerups sp ON p.powerup_id = sp.id
             WHERE p.user_id = ?
             ORDER BY p.purchased_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Holt User-Coins (beide Währungen)
     */
    public function getUserCoins(int $userId): array
    {
        $result = $this->db->fetch(
            "SELECT coins, bonus_coins FROM user_stats WHERE user_id = ?",
            [$userId]
        );
        return [
            'coins' => (float)($result['coins'] ?? 0),
            'bonus_coins' => (float)($result['bonus_coins'] ?? 0),
            'total' => (float)(($result['coins'] ?? 0) + ($result['bonus_coins'] ?? 0))
        ];
    }
}

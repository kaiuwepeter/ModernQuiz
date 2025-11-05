<?php

namespace ModernQuiz\Modules\Jackpot;

use ModernQuiz\Core\Database;

class JackpotSystem
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Holt alle Jackpots
     */
    public function getAllJackpots(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM jackpots ORDER BY
             CASE type
                WHEN 'bronze' THEN 1
                WHEN 'silver' THEN 2
                WHEN 'gold' THEN 3
                WHEN 'diamond' THEN 4
             END"
        );
    }

    /**
     * Erhöht Jackpot bei richtiger Antwort
     */
    public function incrementJackpots(int $userId, int $questionId, int $sessionId): array
    {
        $jackpots = $this->getAllJackpots();
        $results = [];

        foreach ($jackpots as $jackpot) {
            $increment = $jackpot['increment_per_correct'];

            // Update Jackpot
            $this->db->query(
                "UPDATE jackpots SET current_amount = current_amount + ? WHERE id = ?",
                [$increment, $jackpot['id']]
            );

            // Speichere Historie
            $this->db->query(
                "INSERT INTO jackpot_history (jackpot_id, old_amount, new_amount, change_type, user_id)
                 VALUES (?, ?, ?, 'increment', ?)",
                [
                    $jackpot['id'],
                    $jackpot['current_amount'],
                    $jackpot['current_amount'] + $increment,
                    $userId
                ]
            );

            // Prüfe auf Gewinn
            $won = $this->checkJackpotWin($jackpot, $userId, $questionId, $sessionId);

            $results[] = [
                'jackpot_id' => $jackpot['id'],
                'type' => $jackpot['type'],
                'name' => $jackpot['name'],
                'incremented' => $increment,
                'new_amount' => $jackpot['current_amount'] + $increment,
                'won' => $won['won'],
                'win_amount' => $won['amount'] ?? 0
            ];
        }

        return $results;
    }

    /**
     * Prüft ob User Jackpot gewonnen hat
     */
    private function checkJackpotWin(array $jackpot, int $userId, int $questionId, int $sessionId): array
    {
        // Zufallsprüfung basierend auf win_probability
        $random = mt_rand(1, 10000) / 10000;

        if ($random <= $jackpot['win_probability']) {
            return $this->awardJackpot($jackpot['id'], $userId, $questionId, $sessionId);
        }

        return ['won' => false];
    }

    /**
     * Vergibt Jackpot an User
     */
    private function awardJackpot(int $jackpotId, int $userId, int $questionId, int $sessionId): array
    {
        try {
            $this->db->beginTransaction();

            // Hole aktuellen Jackpot-Betrag
            $jackpot = $this->db->fetch(
                "SELECT current_amount, minimum_amount, type, name FROM jackpots WHERE id = ?",
                [$jackpotId]
            );

            $winAmount = $jackpot['current_amount'];

            // Speichere Gewinner
            $this->db->query(
                "INSERT INTO jackpot_winners (jackpot_id, user_id, amount_won, question_id, session_id)
                 VALUES (?, ?, ?, ?, ?)",
                [$jackpotId, $userId, $winAmount, $questionId, $sessionId]
            );

            // Gebe Coins an User
            $this->db->query(
                "UPDATE user_stats SET coins = coins + ? WHERE user_id = ?",
                [(int)$winAmount, $userId]
            );

            // Reset Jackpot auf Minimum
            $this->db->query(
                "UPDATE jackpots
                 SET current_amount = minimum_amount,
                     last_won_by = ?,
                     last_won_at = NOW(),
                     total_won = total_won + ?,
                     times_won = times_won + 1
                 WHERE id = ?",
                [$userId, $winAmount, $jackpotId]
            );

            // Speichere Historie
            $this->db->query(
                "INSERT INTO jackpot_history (jackpot_id, old_amount, new_amount, change_type, user_id)
                 VALUES (?, ?, ?, 'win', ?)",
                [$jackpotId, $winAmount, $jackpot['minimum_amount'], $userId]
            );

            $this->db->commit();

            return [
                'won' => true,
                'amount' => $winAmount,
                'jackpot_name' => $jackpot['name'],
                'jackpot_type' => $jackpot['type']
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['won' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Holt Jackpot-Gewinner
     */
    public function getRecentWinners(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT jw.*, j.name as jackpot_name, j.type, u.username
             FROM jackpot_winners jw
             JOIN jackpots j ON jw.jackpot_id = j.id
             JOIN users u ON jw.user_id = u.id
             ORDER BY jw.won_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Holt User-Jackpot-Gewinne
     */
    public function getUserWins(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT jw.*, j.name as jackpot_name, j.type, j.color
             FROM jackpot_winners jw
             JOIN jackpots j ON jw.jackpot_id = j.id
             WHERE jw.user_id = ?
             ORDER BY jw.won_at DESC",
            [$userId]
        );
    }

    /**
     * Holt Jackpot nach Typ
     */
    public function getJackpotByType(string $type): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM jackpots WHERE type = ?",
            [$type]
        );
    }
}

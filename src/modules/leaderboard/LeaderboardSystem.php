<?php

namespace ModernQuiz\Modules\Leaderboard;

use ModernQuiz\Core\Database;

class LeaderboardSystem
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Holt Top-Spieler
     */
    public function getTopPlayers(int $limit = 100): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM leaderboard_view ORDER BY ranking ASC LIMIT ?",
            [$limit]
        );
    }

    /**
     * Holt User-Ranking
     */
    public function getUserRanking(int $userId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM leaderboard_view WHERE id = ?",
            [$userId]
        );
    }

    /**
     * Holt Leaderboard nach Kategorie
     */
    public function getLeaderboardByCategory(int $categoryId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT
                u.id,
                u.username,
                COUNT(DISTINCT qs.id) as games_played,
                SUM(qs.total_points) as total_points,
                SUM(qs.correct_answers) as correct_answers,
                RANK() OVER (ORDER BY SUM(qs.total_points) DESC) as ranking
             FROM users u
             JOIN quiz_sessions qs ON u.id = qs.user_id
             WHERE qs.category_id = ? AND qs.status = 'completed'
             GROUP BY u.id, u.username
             ORDER BY total_points DESC
             LIMIT ?",
            [$categoryId, $limit]
        );
    }

    /**
     * Holt Leaderboard für heute
     */
    public function getDailyLeaderboard(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT
                u.id,
                u.username,
                COUNT(DISTINCT qs.id) as games_played,
                SUM(qs.total_points) as total_points,
                SUM(qs.correct_answers) as correct_answers,
                RANK() OVER (ORDER BY SUM(qs.total_points) DESC) as ranking
             FROM users u
             JOIN quiz_sessions qs ON u.id = qs.user_id
             WHERE DATE(qs.completed_at) = CURDATE() AND qs.status = 'completed'
             GROUP BY u.id, u.username
             ORDER BY total_points DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Holt Leaderboard für diese Woche
     */
    public function getWeeklyLeaderboard(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT
                u.id,
                u.username,
                COUNT(DISTINCT qs.id) as games_played,
                SUM(qs.total_points) as total_points,
                SUM(qs.correct_answers) as correct_answers,
                RANK() OVER (ORDER BY SUM(qs.total_points) DESC) as ranking
             FROM users u
             JOIN quiz_sessions qs ON u.id = qs.user_id
             WHERE YEARWEEK(qs.completed_at) = YEARWEEK(NOW()) AND qs.status = 'completed'
             GROUP BY u.id, u.username
             ORDER BY total_points DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Holt User-Statistiken
     */
    public function getUserStats(int $userId): ?array
    {
        $stats = $this->db->fetch(
            "SELECT us.*, u.username, u.email
             FROM user_stats us
             JOIN users u ON us.user_id = u.id
             WHERE us.user_id = ?",
            [$userId]
        );

        if (!$stats) {
            return null;
        }

        // Füge Achievements hinzu
        $achievements = $this->db->fetchAll(
            "SELECT a.*, ua.unlocked_at
             FROM user_achievements ua
             JOIN achievements a ON ua.achievement_id = a.id
             WHERE ua.user_id = ?
             ORDER BY ua.unlocked_at DESC",
            [$userId]
        );

        $stats['achievements'] = $achievements;

        return $stats;
    }

    /**
     * Vergleicht zwei User
     */
    public function compareUsers(int $userId1, int $userId2): array
    {
        $user1 = $this->getUserStats($userId1);
        $user2 = $this->getUserStats($userId2);

        return [
            'user1' => $user1,
            'user2' => $user2,
            'comparison' => [
                'points_difference' => $user1['total_points'] - $user2['total_points'],
                'games_difference' => $user1['total_games'] - $user2['total_games'],
                'accuracy_user1' => $user1['total_questions_answered'] > 0
                    ? round($user1['total_correct_answers'] / $user1['total_questions_answered'] * 100, 2)
                    : 0,
                'accuracy_user2' => $user2['total_questions_answered'] > 0
                    ? round($user2['total_correct_answers'] / $user2['total_questions_answered'] * 100, 2)
                    : 0,
            ]
        ];
    }

    /**
     * Holt Achievements
     */
    public function getAchievements(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM achievements ORDER BY requirement_value ASC"
        );
    }

    /**
     * Prüft und vergibt Achievements
     */
    public function checkAndAwardAchievements(int $userId): array
    {
        $stats = $this->db->fetch("SELECT * FROM user_stats WHERE user_id = ?", [$userId]);
        if (!$stats) {
            return [];
        }

        $achievements = $this->getAchievements();
        $awarded = [];

        foreach ($achievements as $achievement) {
            // Prüfe ob User das Achievement schon hat
            $has = $this->db->fetch(
                "SELECT id FROM user_achievements WHERE user_id = ? AND achievement_id = ?",
                [$userId, $achievement['id']]
            );

            if ($has) {
                continue;
            }

            // Prüfe Anforderungen
            $earned = false;
            switch ($achievement['requirement_type']) {
                case 'games_played':
                    $earned = $stats['total_games'] >= $achievement['requirement_value'];
                    break;
                case 'correct_answers':
                    $earned = $stats['total_correct_answers'] >= $achievement['requirement_value'];
                    break;
                case 'points':
                    $earned = $stats['total_points'] >= $achievement['requirement_value'];
                    break;
                case 'streak':
                    $earned = $stats['longest_streak'] >= $achievement['requirement_value'];
                    break;
            }

            if ($earned) {
                $this->db->query(
                    "INSERT INTO user_achievements (user_id, achievement_id) VALUES (?, ?)",
                    [$userId, $achievement['id']]
                );
                $awarded[] = $achievement;
            }
        }

        return $awarded;
    }
}

<?php
// src/modules/statistics/StatisticsManager.php
namespace ModernQuiz\Modules\Statistics;

class StatisticsManager {
    private $db;
    private $cacheTime = 3600; // 1 Stunde Cache

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Holt alle Statistiken für das Haupt-Dashboard
     */
    public function getDashboardStats(): array {
        return [
            'global' => $this->getGlobalStats(),
            'users' => $this->getUserStats(),
            'quizzes' => $this->getQuizStats(),
            'multiplayer' => $this->getMultiplayerStats(),
            'achievements' => $this->getAchievementStats(),
            'questions' => $this->getQuestionStats(),
            'trends' => $this->getTrendStats()
        ];
    }

    /**
     * GLOBALE PLATTFORM-STATISTIKEN
     */
    public function getGlobalStats(): array {
        $stats = [];

        // Registrierte User
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch()['total'];

        // Aktive User
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE is_active = TRUE");
        $stats['active_users'] = $stmt->fetch()['total'];

        // Neue User (letzte 7 Tage)
        $stmt = $this->db->query(
            "SELECT COUNT(*) as total FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stats['new_users_week'] = $stmt->fetch()['total'];

        // Neue User (letzte 30 Tage)
        $stmt = $this->db->query(
            "SELECT COUNT(*) as total FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $stats['new_users_month'] = $stmt->fetch()['total'];

        // Gesamt gespielte Quizze
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM quiz_results");
        $stats['total_quiz_plays'] = $stmt->fetch()['total'];

        // Gesamt Multiplayer-Spiele
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM game_rooms WHERE status = 'finished'");
        $stats['total_multiplayer_games'] = $stmt->fetch()['total'];

        // Gesamt Achievements freigeschaltet
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM user_achievements");
        $stats['total_achievements_unlocked'] = $stmt->fetch()['total'];

        // Gesamt beantwortete Fragen
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM user_answers");
        $stats['total_questions_answered'] = $stmt->fetch()['total'];

        // Gesamt richtige Antworten
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM user_answers WHERE is_correct = TRUE");
        $stats['total_correct_answers'] = $stmt->fetch()['total'];

        // Durchschnittliche Erfolgsquote
        if ($stats['total_questions_answered'] > 0) {
            $stats['average_success_rate'] = round(
                ($stats['total_correct_answers'] / $stats['total_questions_answered']) * 100,
                2
            );
        } else {
            $stats['average_success_rate'] = 0;
        }

        // Gesamt Freundschaften
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM friendships WHERE status = 'accepted'");
        $stats['total_friendships'] = $stmt->fetch()['total'] / 2; // Geteilt durch 2 wegen bidirektionaler Einträge

        // Gesamt Challenges
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM challenges");
        $stats['total_challenges'] = $stmt->fetch()['total'];

        // Abgeschlossene Challenges
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM challenges WHERE status = 'completed'");
        $stats['completed_challenges'] = $stmt->fetch()['total'];

        // Gesamt erstelle Quizze
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM quizzes");
        $stats['total_quizzes_created'] = $stmt->fetch()['total'];

        // Aktive Quizze
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM quizzes WHERE is_active = TRUE");
        $stats['active_quizzes'] = $stmt->fetch()['total'];

        // Durchschnittliche Quiz-Länge
        $stmt = $this->db->query(
            "SELECT AVG(question_count) as avg_length FROM (
                SELECT quiz_id, COUNT(*) as question_count
                FROM questions
                GROUP BY quiz_id
            ) as q"
        );
        $stats['average_quiz_length'] = round($stmt->fetch()['avg_length'] ?? 0, 1);

        // Gesamt Reviews
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM quiz_reviews");
        $stats['total_reviews'] = $stmt->fetch()['total'];

        // Durchschnittliche Rating
        $stmt = $this->db->query("SELECT AVG(rating) as avg_rating FROM quiz_reviews");
        $stats['average_rating'] = round($stmt->fetch()['avg_rating'] ?? 0, 2);

        // Gesamt Referrals
        $stmt = $this->db->query("SELECT SUM(referral_count) as total FROM users");
        $stats['total_referrals'] = $stmt->fetch()['total'] ?? 0;

        // Online User (letzte 15 Minuten)
        $stmt = $this->db->query(
            "SELECT COUNT(DISTINCT user_id) as total FROM sessions
             WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
        );
        $stats['online_users'] = $stmt->fetch()['total'];

        return $stats;
    }

    /**
     * USER-STATISTIKEN & RANGLISTEN
     */
    public function getUserStats(): array {
        $stats = [];

        // Top 10 Spieler nach Punkten
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar, us.total_points, us.total_quizzes_played
             FROM users u
             JOIN user_stats us ON u.id = us.user_id
             WHERE u.is_active = TRUE
             ORDER BY us.total_points DESC
             LIMIT 10"
        );
        $stats['top_players_points'] = $stmt->fetchAll();

        // Top 10 Spieler nach gespielten Quizzen
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar, us.total_quizzes_played, us.total_points
             FROM users u
             JOIN user_stats us ON u.id = us.user_id
             WHERE u.is_active = TRUE
             ORDER BY us.total_quizzes_played DESC
             LIMIT 10"
        );
        $stats['top_players_quizzes'] = $stmt->fetchAll();

        // Top 10 Spieler nach Erfolgsquote
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar,
                    us.correct_answers,
                    us.total_questions_answered,
                    ROUND((us.correct_answers / us.total_questions_answered) * 100, 2) as success_rate
             FROM users u
             JOIN user_stats us ON u.id = us.user_id
             WHERE u.is_active = TRUE
             AND us.total_questions_answered >= 50
             ORDER BY success_rate DESC
             LIMIT 10"
        );
        $stats['top_players_accuracy'] = $stmt->fetchAll();

        // Top 10 Multiplayer Champions
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar, us.multiplayer_wins, us.multiplayer_games,
                    ROUND((us.multiplayer_wins / us.multiplayer_games) * 100, 2) as win_rate
             FROM users u
             JOIN user_stats us ON u.id = us.user_id
             WHERE u.is_active = TRUE
             AND us.multiplayer_games >= 10
             ORDER BY us.multiplayer_wins DESC, win_rate DESC
             LIMIT 10"
        );
        $stats['top_multiplayer_players'] = $stmt->fetchAll();

        // Top 10 Quiz-Creator
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar, us.total_quizzes_created,
                    (SELECT AVG(play_count) FROM quizzes WHERE created_by = u.id) as avg_plays
             FROM users u
             JOIN user_stats us ON u.id = us.user_id
             WHERE u.is_active = TRUE
             ORDER BY us.total_quizzes_created DESC
             LIMIT 10"
        );
        $stats['top_quiz_creators'] = $stmt->fetchAll();

        // Top 10 Achievement-Sammler
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar, COUNT(ua.id) as achievement_count,
                    SUM(a.points) as achievement_points
             FROM users u
             LEFT JOIN user_achievements ua ON u.id = ua.user_id
             LEFT JOIN achievements a ON ua.achievement_id = a.id
             WHERE u.is_active = TRUE
             GROUP BY u.id
             ORDER BY achievement_count DESC
             LIMIT 10"
        );
        $stats['top_achievement_hunters'] = $stmt->fetchAll();

        // Top 10 Referrer
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar, u.referral_count, u.referral_code
             FROM users u
             WHERE u.is_active = TRUE
             AND u.referral_count > 0
             ORDER BY u.referral_count DESC
             LIMIT 10"
        );
        $stats['top_referrers'] = $stmt->fetchAll();

        // Top 10 längste Win-Streaks
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar, us.win_streak, us.best_win_streak
             FROM users u
             JOIN user_stats us ON u.id = us.user_id
             WHERE u.is_active = TRUE
             ORDER BY us.best_win_streak DESC
             LIMIT 10"
        );
        $stats['top_win_streaks'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * QUIZ-STATISTIKEN
     */
    public function getQuizStats(): array {
        $stats = [];

        // Beliebteste Quizze (nach Plays)
        $stmt = $this->db->query(
            "SELECT q.id, q.title, q.category, q.difficulty, q.play_count,
                    u.username as creator_name,
                    (SELECT AVG(rating) FROM quiz_reviews WHERE quiz_id = q.id) as avg_rating
             FROM quizzes q
             JOIN users u ON q.created_by = u.id
             WHERE q.is_active = TRUE
             ORDER BY q.play_count DESC
             LIMIT 10"
        );
        $stats['most_played_quizzes'] = $stmt->fetchAll();

        // Best bewertete Quizze
        $stmt = $this->db->query(
            "SELECT q.id, q.title, q.category, q.difficulty, q.play_count,
                    u.username as creator_name,
                    AVG(qr.rating) as avg_rating,
                    COUNT(qr.id) as review_count
             FROM quizzes q
             JOIN users u ON q.created_by = u.id
             LEFT JOIN quiz_reviews qr ON q.id = qr.quiz_id
             WHERE q.is_active = TRUE
             GROUP BY q.id
             HAVING review_count >= 5
             ORDER BY avg_rating DESC, review_count DESC
             LIMIT 10"
        );
        $stats['best_rated_quizzes'] = $stmt->fetchAll();

        // Neueste Quizze
        $stmt = $this->db->query(
            "SELECT q.id, q.title, q.category, q.difficulty, q.play_count, q.created_at,
                    u.username as creator_name
             FROM quizzes q
             JOIN users u ON q.created_by = u.id
             WHERE q.is_active = TRUE
             ORDER BY q.created_at DESC
             LIMIT 10"
        );
        $stats['newest_quizzes'] = $stmt->fetchAll();

        // Statistiken pro Kategorie
        $stmt = $this->db->query(
            "SELECT category,
                    COUNT(*) as quiz_count,
                    SUM(play_count) as total_plays,
                    AVG(play_count) as avg_plays,
                    (SELECT AVG(rating) FROM quiz_reviews qr
                     JOIN quizzes q2 ON qr.quiz_id = q2.id
                     WHERE q2.category = quizzes.category) as avg_rating
             FROM quizzes
             WHERE is_active = TRUE
             GROUP BY category
             ORDER BY total_plays DESC"
        );
        $stats['category_stats'] = $stmt->fetchAll();

        // Schwierigkeitsgrad-Verteilung
        $stmt = $this->db->query(
            "SELECT difficulty,
                    COUNT(*) as quiz_count,
                    SUM(play_count) as total_plays,
                    AVG((SELECT AVG(percentage) FROM quiz_results WHERE quiz_id = quizzes.id)) as avg_score
             FROM quizzes
             WHERE is_active = TRUE
             GROUP BY difficulty
             ORDER BY FIELD(difficulty, 'easy', 'medium', 'hard')"
        );
        $stats['difficulty_distribution'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * FRAGEN-STATISTIKEN & ANALYSE
     */
    public function getQuestionStats(): array {
        $stats = [];

        // Schwierigste Fragen (niedrigste Erfolgsquote)
        $stmt = $this->db->query(
            "SELECT q.id, q.question_text, qu.title as quiz_title, qu.category,
                    COUNT(ua.id) as times_answered,
                    SUM(CASE WHEN ua.is_correct = TRUE THEN 1 ELSE 0 END) as correct_count,
                    ROUND((SUM(CASE WHEN ua.is_correct = TRUE THEN 1 ELSE 0 END) / COUNT(ua.id)) * 100, 2) as success_rate
             FROM questions q
             JOIN quizzes qu ON q.quiz_id = qu.id
             LEFT JOIN user_answers ua ON q.id = ua.question_id
             WHERE qu.is_active = TRUE
             GROUP BY q.id
             HAVING times_answered >= 20
             ORDER BY success_rate ASC
             LIMIT 10"
        );
        $stats['hardest_questions'] = $stmt->fetchAll();

        // Leichteste Fragen (höchste Erfolgsquote)
        $stmt = $this->db->query(
            "SELECT q.id, q.question_text, qu.title as quiz_title, qu.category,
                    COUNT(ua.id) as times_answered,
                    SUM(CASE WHEN ua.is_correct = TRUE THEN 1 ELSE 0 END) as correct_count,
                    ROUND((SUM(CASE WHEN ua.is_correct = TRUE THEN 1 ELSE 0 END) / COUNT(ua.id)) * 100, 2) as success_rate
             FROM questions q
             JOIN quizzes qu ON q.quiz_id = qu.id
             LEFT JOIN user_answers ua ON q.id = ua.question_id
             WHERE qu.is_active = TRUE
             GROUP BY q.id
             HAVING times_answered >= 20
             ORDER BY success_rate DESC
             LIMIT 10"
        );
        $stats['easiest_questions'] = $stmt->fetchAll();

        // Meist beantwortete Fragen
        $stmt = $this->db->query(
            "SELECT q.id, q.question_text, qu.title as quiz_title, qu.category,
                    COUNT(ua.id) as times_answered,
                    ROUND((SUM(CASE WHEN ua.is_correct = TRUE THEN 1 ELSE 0 END) / COUNT(ua.id)) * 100, 2) as success_rate
             FROM questions q
             JOIN quizzes qu ON q.quiz_id = qu.id
             LEFT JOIN user_answers ua ON q.id = ua.question_id
             WHERE qu.is_active = TRUE
             GROUP BY q.id
             ORDER BY times_answered DESC
             LIMIT 10"
        );
        $stats['most_answered_questions'] = $stmt->fetchAll();

        // Durchschnittliche Antwortzeit
        $stmt = $this->db->query(
            "SELECT AVG(time_taken) as avg_time_seconds
             FROM user_answers
             WHERE time_taken > 0 AND time_taken < 120"
        );
        $stats['average_answer_time'] = round($stmt->fetch()['avg_time_seconds'] ?? 0, 1);

        // Fragen-Typen-Verteilung
        $stmt = $this->db->query(
            "SELECT question_type, COUNT(*) as count
             FROM questions
             GROUP BY question_type"
        );
        $stats['question_type_distribution'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * MULTIPLAYER-STATISTIKEN
     */
    public function getMultiplayerStats(): array {
        $stats = [];

        // Gesamt Multiplayer-Spiele
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM game_rooms");
        $stats['total_games'] = $stmt->fetch()['total'];

        // Abgeschlossene Spiele
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM game_rooms WHERE status = 'finished'");
        $stats['finished_games'] = $stmt->fetch()['total'];

        // Aktive Spiele
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM game_rooms WHERE status = 'in_progress'");
        $stats['active_games'] = $stmt->fetch()['total'];

        // Durchschnittliche Spieleranzahl
        $stmt = $this->db->query(
            "SELECT AVG(player_count) as avg_players FROM (
                SELECT room_id, COUNT(*) as player_count
                FROM game_participants
                GROUP BY room_id
            ) as g"
        );
        $stats['average_players_per_game'] = round($stmt->fetch()['avg_players'] ?? 0, 1);

        // Meistgespielte Multiplayer-Quizze
        $stmt = $this->db->query(
            "SELECT q.id, q.title, q.category, COUNT(gr.id) as times_played
             FROM game_rooms gr
             JOIN quizzes q ON gr.quiz_id = q.id
             WHERE gr.status = 'finished'
             GROUP BY q.id
             ORDER BY times_played DESC
             LIMIT 10"
        );
        $stats['most_played_multiplayer_quizzes'] = $stmt->fetchAll();

        // Spieler mit meisten Multiplayer-Siegen
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar, us.multiplayer_wins, us.multiplayer_games,
                    ROUND((us.multiplayer_wins / NULLIF(us.multiplayer_games, 0)) * 100, 2) as win_rate
             FROM users u
             JOIN user_stats us ON u.id = us.user_id
             WHERE us.multiplayer_games > 0
             ORDER BY us.multiplayer_wins DESC
             LIMIT 10"
        );
        $stats['top_multiplayer_winners'] = $stmt->fetchAll();

        // Spieler mit meisten Multiplayer-Niederlagen
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar,
                    us.multiplayer_games,
                    us.multiplayer_wins,
                    (us.multiplayer_games - us.multiplayer_wins) as losses
             FROM users u
             JOIN user_stats us ON u.id = us.user_id
             WHERE us.multiplayer_games > 0
             ORDER BY losses DESC
             LIMIT 10"
        );
        $stats['most_multiplayer_losses'] = $stmt->fetchAll();

        // Durchschnittliche Spieldauer (falls verfügbar)
        $stmt = $this->db->query(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, finished_at)) as avg_duration
             FROM game_rooms
             WHERE status = 'finished'
             AND started_at IS NOT NULL
             AND finished_at IS NOT NULL"
        );
        $stats['average_game_duration_minutes'] = round($stmt->fetch()['avg_duration'] ?? 0, 1);

        return $stats;
    }

    /**
     * ACHIEVEMENT-STATISTIKEN
     */
    public function getAchievementStats(): array {
        $stats = [];

        // Gesamt verfügbare Achievements
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM achievements");
        $stats['total_achievements_available'] = $stmt->fetch()['total'];

        // Gesamt freigeschaltete Achievements
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM user_achievements");
        $stats['total_achievements_unlocked'] = $stmt->fetch()['total'];

        // Seltenste Achievements (am wenigsten freigeschaltet)
        $stmt = $this->db->query(
            "SELECT a.id, a.name, a.description, a.icon, a.category, a.points,
                    COUNT(ua.id) as unlock_count,
                    ROUND((COUNT(ua.id) / (SELECT COUNT(*) FROM users WHERE is_active = TRUE)) * 100, 2) as unlock_percentage
             FROM achievements a
             LEFT JOIN user_achievements ua ON a.id = ua.achievement_id
             GROUP BY a.id
             ORDER BY unlock_count ASC
             LIMIT 10"
        );
        $stats['rarest_achievements'] = $stmt->fetchAll();

        // Häufigste Achievements (am meisten freigeschaltet)
        $stmt = $this->db->query(
            "SELECT a.id, a.name, a.description, a.icon, a.category, a.points,
                    COUNT(ua.id) as unlock_count,
                    ROUND((COUNT(ua.id) / (SELECT COUNT(*) FROM users WHERE is_active = TRUE)) * 100, 2) as unlock_percentage
             FROM achievements a
             LEFT JOIN user_achievements ua ON a.id = ua.achievement_id
             GROUP BY a.id
             ORDER BY unlock_count DESC
             LIMIT 10"
        );
        $stats['most_common_achievements'] = $stmt->fetchAll();

        // Achievements nach Kategorie
        $stmt = $this->db->query(
            "SELECT a.category,
                    COUNT(DISTINCT a.id) as achievement_count,
                    COUNT(ua.id) as total_unlocks,
                    SUM(a.points) as total_points_available
             FROM achievements a
             LEFT JOIN user_achievements ua ON a.id = ua.achievement_id
             GROUP BY a.category
             ORDER BY achievement_count DESC"
        );
        $stats['achievements_by_category'] = $stmt->fetchAll();

        // Spieler mit meisten Achievements
        $stmt = $this->db->query(
            "SELECT u.id, u.username, u.avatar,
                    COUNT(ua.id) as achievement_count,
                    SUM(a.points) as total_points,
                    ROUND((COUNT(ua.id) / (SELECT COUNT(*) FROM achievements)) * 100, 2) as completion_percentage
             FROM users u
             LEFT JOIN user_achievements ua ON u.id = ua.user_id
             LEFT JOIN achievements a ON ua.achievement_id = a.id
             WHERE u.is_active = TRUE
             GROUP BY u.id
             ORDER BY achievement_count DESC
             LIMIT 10"
        );
        $stats['top_achievement_collectors'] = $stmt->fetchAll();

        // Neueste Achievement-Freischaltungen
        $stmt = $this->db->query(
            "SELECT u.username, u.avatar, a.name, a.icon, a.points, ua.earned_at
             FROM user_achievements ua
             JOIN users u ON ua.user_id = u.id
             JOIN achievements a ON ua.achievement_id = a.id
             ORDER BY ua.earned_at DESC
             LIMIT 20"
        );
        $stats['recent_unlocks'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * TREND-STATISTIKEN (zeitbasiert)
     */
    public function getTrendStats(int $days = 30): array {
        $stats = [];

        // Neue User pro Tag (letzte X Tage)
        $stmt = $this->db->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count
             FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );
        $stmt->execute([$days]);
        $stats['new_users_per_day'] = $stmt->fetchAll();

        // Gespielte Quizze pro Tag
        $stmt = $this->db->prepare(
            "SELECT DATE(completed_at) as date, COUNT(*) as count
             FROM quiz_results
             WHERE completed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(completed_at)
             ORDER BY date ASC"
        );
        $stmt->execute([$days]);
        $stats['quiz_plays_per_day'] = $stmt->fetchAll();

        // Multiplayer-Spiele pro Tag
        $stmt = $this->db->prepare(
            "SELECT DATE(finished_at) as date, COUNT(*) as count
             FROM game_rooms
             WHERE finished_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND status = 'finished'
             GROUP BY DATE(finished_at)
             ORDER BY date ASC"
        );
        $stmt->execute([$days]);
        $stats['multiplayer_games_per_day'] = $stmt->fetchAll();

        // Achievement-Freischaltungen pro Tag
        $stmt = $this->db->prepare(
            "SELECT DATE(earned_at) as date, COUNT(*) as count
             FROM user_achievements
             WHERE earned_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(earned_at)
             ORDER BY date ASC"
        );
        $stmt->execute([$days]);
        $stats['achievements_per_day'] = $stmt->fetchAll();

        // Wachstums-Metriken
        $stmt = $this->db->query(
            "SELECT
                (SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as users_this_week,
                (SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)) as users_last_week,
                (SELECT COUNT(*) FROM quiz_results WHERE completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as plays_this_week,
                (SELECT COUNT(*) FROM quiz_results WHERE completed_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND completed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)) as plays_last_week"
        );
        $growth = $stmt->fetch();

        $stats['user_growth_percentage'] = $growth['users_last_week'] > 0
            ? round((($growth['users_this_week'] - $growth['users_last_week']) / $growth['users_last_week']) * 100, 2)
            : 0;

        $stats['activity_growth_percentage'] = $growth['plays_last_week'] > 0
            ? round((($growth['plays_this_week'] - $growth['plays_last_week']) / $growth['plays_last_week']) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Holt detaillierte Statistiken für einen bestimmten User
     */
    public function getUserDetailStats(int $userId): array {
        $stats = [];

        // Basis-Stats
        $stmt = $this->db->prepare(
            "SELECT * FROM user_stats WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $stats['basic'] = $stmt->fetch();

        // Rang basierend auf Punkten
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) + 1 as rank
             FROM user_stats
             WHERE total_points > (SELECT total_points FROM user_stats WHERE user_id = ?)"
        );
        $stmt->execute([$userId]);
        $stats['rank_by_points'] = $stmt->fetch()['rank'];

        // Performance pro Kategorie
        $stmt = $this->db->prepare(
            "SELECT q.category,
                    COUNT(qr.id) as quizzes_played,
                    AVG(qr.percentage) as avg_score,
                    MAX(qr.percentage) as best_score
             FROM quiz_results qr
             JOIN quizzes q ON qr.quiz_id = q.id
             WHERE qr.user_id = ?
             GROUP BY q.category
             ORDER BY quizzes_played DESC"
        );
        $stmt->execute([$userId]);
        $stats['performance_by_category'] = $stmt->fetchAll();

        // Letzte Aktivitäten
        $stmt = $this->db->prepare(
            "SELECT q.title, q.category, qr.score, qr.percentage, qr.completed_at
             FROM quiz_results qr
             JOIN quizzes q ON qr.quiz_id = q.id
             WHERE qr.user_id = ?
             ORDER BY qr.completed_at DESC
             LIMIT 10"
        );
        $stmt->execute([$userId]);
        $stats['recent_activities'] = $stmt->fetchAll();

        // Achievements
        $stmt = $this->db->prepare(
            "SELECT a.*, ua.earned_at
             FROM user_achievements ua
             JOIN achievements a ON ua.achievement_id = a.id
             WHERE ua.user_id = ?
             ORDER BY ua.earned_at DESC"
        );
        $stmt->execute([$userId]);
        $stats['achievements'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Holt Vergleichsstatistiken zwischen zwei Usern
     */
    public function compareUsers(int $userId1, int $userId2): array {
        $user1Stats = $this->getUserDetailStats($userId1);
        $user2Stats = $this->getUserDetailStats($userId2);

        return [
            'user1' => $user1Stats,
            'user2' => $user2Stats,
            'comparison' => [
                'points_difference' => $user1Stats['basic']['total_points'] - $user2Stats['basic']['total_points'],
                'quizzes_difference' => $user1Stats['basic']['total_quizzes_played'] - $user2Stats['basic']['total_quizzes_played'],
                'achievements_difference' => count($user1Stats['achievements']) - count($user2Stats['achievements']),
            ]
        ];
    }
}

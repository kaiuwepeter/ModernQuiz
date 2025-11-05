<?php

namespace ModernQuiz\Database\Migrations;

class CreateLeaderboardView
{
    public static function up($db)
    {
        // Achievements Tabelle
        $db->query("
            CREATE TABLE IF NOT EXISTS achievements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                icon VARCHAR(100),
                requirement_type ENUM('games_played', 'correct_answers', 'points', 'streak', 'jackpot_wins') NOT NULL,
                requirement_value INT NOT NULL,
                badge_color VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // User Achievements
        $db->query("
            CREATE TABLE IF NOT EXISTS user_achievements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                achievement_id INT NOT NULL,
                unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_achievement (user_id, achievement_id),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Leaderboard View (für Performance)
        $db->query("
            CREATE OR REPLACE VIEW leaderboard_view AS
            SELECT
                u.id,
                u.username,
                u.email,
                us.total_points,
                us.total_games,
                us.total_correct_answers,
                us.level,
                us.current_streak,
                us.longest_streak,
                COUNT(DISTINCT ua.id) as achievements_count,
                RANK() OVER (ORDER BY us.total_points DESC) as ranking
            FROM users u
            LEFT JOIN user_stats us ON u.id = us.user_id
            LEFT JOIN user_achievements ua ON u.id = ua.user_id
            GROUP BY u.id, u.username, u.email, us.total_points, us.total_games,
                     us.total_correct_answers, us.level, us.current_streak, us.longest_streak
        ");

        return true;
    }

    public static function down($db)
    {
        $db->query("DROP VIEW IF EXISTS leaderboard_view");
        $db->query("DROP TABLE IF EXISTS user_achievements");
        $db->query("DROP TABLE IF EXISTS achievements");
        return true;
    }
}

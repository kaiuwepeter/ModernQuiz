<?php
// src/database/migrations/20241231_000006_create_social_tables.php
namespace ModernQuiz\Database\Migrations;

class CreateSocialTables {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        // Freundschaften Tabelle
        $sql1 = "CREATE TABLE IF NOT EXISTS friendships (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            friend_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
            requested_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            accepted_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_friendship (user_id, friend_id),
            INDEX idx_user_friends (user_id, status),
            CHECK (user_id != friend_id)
        )";

        // Challenges Tabelle
        $sql2 = "CREATE TABLE IF NOT EXISTS challenges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            challenger_id INT NOT NULL,
            challenged_id INT NOT NULL,
            quiz_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'declined', 'completed') DEFAULT 'pending',
            challenger_score INT,
            challenged_score INT,
            winner_id INT,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            accepted_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            expires_at TIMESTAMP,
            FOREIGN KEY (challenger_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (challenged_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_challenged (challenged_id, status),
            INDEX idx_challenger (challenger_id)
        )";

        // Achievements Tabelle
        $sql3 = "CREATE TABLE IF NOT EXISTS achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(100),
            category VARCHAR(50),
            points INT DEFAULT 0,
            requirement_type VARCHAR(50),
            requirement_value INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // User Achievements Tabelle
        $sql4 = "CREATE TABLE IF NOT EXISTS user_achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            achievement_id INT NOT NULL,
            earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_achievement (user_id, achievement_id),
            INDEX idx_user_achievements (user_id)
        )";

        // User Stats Tabelle
        $sql5 = "CREATE TABLE IF NOT EXISTS user_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNIQUE NOT NULL,
            total_quizzes_played INT DEFAULT 0,
            total_quizzes_created INT DEFAULT 0,
            total_questions_answered INT DEFAULT 0,
            correct_answers INT DEFAULT 0,
            total_points INT DEFAULT 0,
            win_streak INT DEFAULT 0,
            best_win_streak INT DEFAULT 0,
            multiplayer_games INT DEFAULT 0,
            multiplayer_wins INT DEFAULT 0,
            challenges_sent INT DEFAULT 0,
            challenges_won INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";

        return $this->db->query($sql1) &&
               $this->db->query($sql2) &&
               $this->db->query($sql3) &&
               $this->db->query($sql4) &&
               $this->db->query($sql5);
    }
}

<?php

namespace ModernQuiz\Database\Migrations;

class CreateQuizTables
{
    public static function up($db)
    {
        // Quiz-Kategorien
        $db->query("
            CREATE TABLE IF NOT EXISTS quiz_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                icon VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Quiz-Fragen
        $db->query("
            CREATE TABLE IF NOT EXISTS quiz_questions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_id INT NOT NULL,
                question TEXT NOT NULL,
                difficulty ENUM('easy', 'medium', 'hard', 'expert') DEFAULT 'medium',
                points INT DEFAULT 10,
                time_limit INT DEFAULT 30,
                image_url VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES quiz_categories(id) ON DELETE CASCADE,
                INDEX idx_category (category_id),
                INDEX idx_difficulty (difficulty)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Antwortoptionen
        $db->query("
            CREATE TABLE IF NOT EXISTS quiz_answers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                question_id INT NOT NULL,
                answer_text TEXT NOT NULL,
                is_correct BOOLEAN DEFAULT FALSE,
                explanation TEXT,
                FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
                INDEX idx_question (question_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Quiz-Sessions
        $db->query("
            CREATE TABLE IF NOT EXISTS quiz_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                category_id INT,
                status ENUM('active', 'completed', 'abandoned') DEFAULT 'active',
                total_questions INT DEFAULT 0,
                correct_answers INT DEFAULT 0,
                total_points INT DEFAULT 0,
                bonus_points INT DEFAULT 0,
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES quiz_categories(id) ON DELETE SET NULL,
                INDEX idx_user (user_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // User-Antworten
        $db->query("
            CREATE TABLE IF NOT EXISTS user_answers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id INT NOT NULL,
                question_id INT NOT NULL,
                answer_id INT NOT NULL,
                is_correct BOOLEAN,
                points_earned INT DEFAULT 0,
                time_taken INT DEFAULT 0,
                powerup_used VARCHAR(50),
                answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
                FOREIGN KEY (answer_id) REFERENCES quiz_answers(id) ON DELETE CASCADE,
                INDEX idx_session (session_id),
                INDEX idx_question (question_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // User-Statistiken
        $db->query("
            CREATE TABLE IF NOT EXISTS user_stats (
                user_id INT PRIMARY KEY,
                total_games INT DEFAULT 0,
                total_questions_answered INT DEFAULT 0,
                total_correct_answers INT DEFAULT 0,
                total_points INT DEFAULT 0,
                highest_score INT DEFAULT 0,
                current_streak INT DEFAULT 0,
                longest_streak INT DEFAULT 0,
                level INT DEFAULT 1,
                experience INT DEFAULT 0,
                coins INT DEFAULT 100,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_points (total_points),
                INDEX idx_level (level)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        return true;
    }

    public static function down($db)
    {
        $db->query("DROP TABLE IF EXISTS user_answers");
        $db->query("DROP TABLE IF EXISTS quiz_sessions");
        $db->query("DROP TABLE IF EXISTS quiz_answers");
        $db->query("DROP TABLE IF EXISTS quiz_questions");
        $db->query("DROP TABLE IF EXISTS quiz_categories");
        $db->query("DROP TABLE IF EXISTS user_stats");
        return true;
    }
}

<?php
// src/database/migrations/20241231_000004_create_quiz_tables.php
namespace ModernQuiz\Database\Migrations;

class CreateQuizTables {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        // Quizze Tabelle
        $sql1 = "CREATE TABLE IF NOT EXISTS quizzes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            created_by INT NOT NULL,
            category VARCHAR(100),
            difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
            time_limit INT DEFAULT 0,
            is_public BOOLEAN DEFAULT TRUE,
            is_active BOOLEAN DEFAULT TRUE,
            play_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_category (category),
            INDEX idx_creator (created_by),
            INDEX idx_public (is_public, is_active)
        )";

        // Fragen Tabelle
        $sql2 = "CREATE TABLE IF NOT EXISTS questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            question_text TEXT NOT NULL,
            question_type ENUM('multiple_choice', 'true_false', 'text_input') DEFAULT 'multiple_choice',
            points INT DEFAULT 10,
            order_position INT DEFAULT 0,
            time_limit INT DEFAULT 30,
            image_url VARCHAR(500),
            explanation TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            INDEX idx_quiz (quiz_id)
        )";

        // Antworten Tabelle
        $sql3 = "CREATE TABLE IF NOT EXISTS answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question_id INT NOT NULL,
            answer_text TEXT NOT NULL,
            is_correct BOOLEAN DEFAULT FALSE,
            order_position INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
            INDEX idx_question (question_id)
        )";

        // Quiz-Ergebnisse Tabelle
        $sql4 = "CREATE TABLE IF NOT EXISTS quiz_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            user_id INT NOT NULL,
            score INT DEFAULT 0,
            max_score INT DEFAULT 0,
            percentage DECIMAL(5,2),
            time_taken INT,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_quiz_results (quiz_id, user_id),
            INDEX idx_user_results (user_id)
        )";

        // User-Antworten Tabelle
        $sql5 = "CREATE TABLE IF NOT EXISTS user_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            result_id INT NOT NULL,
            question_id INT NOT NULL,
            answer_id INT,
            text_answer TEXT,
            is_correct BOOLEAN DEFAULT FALSE,
            time_taken INT,
            answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (result_id) REFERENCES quiz_results(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
            FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE SET NULL,
            INDEX idx_result (result_id)
        )";

        return $this->db->query($sql1) &&
               $this->db->query($sql2) &&
               $this->db->query($sql3) &&
               $this->db->query($sql4) &&
               $this->db->query($sql5);
    }
}

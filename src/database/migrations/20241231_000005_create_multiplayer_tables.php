<?php
// src/database/migrations/20241231_000005_create_multiplayer_tables.php
namespace ModernQuiz\Database\Migrations;

class CreateMultiplayerTables {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        // Game Rooms Tabelle
        $sql1 = "CREATE TABLE IF NOT EXISTS game_rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_code VARCHAR(10) UNIQUE NOT NULL,
            quiz_id INT NOT NULL,
            host_user_id INT NOT NULL,
            max_players INT DEFAULT 10,
            status ENUM('waiting', 'in_progress', 'finished') DEFAULT 'waiting',
            current_question INT DEFAULT 0,
            is_private BOOLEAN DEFAULT FALSE,
            room_password VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            started_at TIMESTAMP NULL,
            finished_at TIMESTAMP NULL,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (host_user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_room_code (room_code),
            INDEX idx_status (status)
        )";

        // Game Participants Tabelle
        $sql2 = "CREATE TABLE IF NOT EXISTS game_participants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            user_id INT NOT NULL,
            nickname VARCHAR(50) NOT NULL,
            score INT DEFAULT 0,
            is_ready BOOLEAN DEFAULT FALSE,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            left_at TIMESTAMP NULL,
            FOREIGN KEY (room_id) REFERENCES game_rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_room (room_id, user_id),
            INDEX idx_room (room_id)
        )";

        // Game Answers (Multiplayer) Tabelle
        $sql3 = "CREATE TABLE IF NOT EXISTS game_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            participant_id INT NOT NULL,
            question_id INT NOT NULL,
            answer_id INT,
            text_answer TEXT,
            is_correct BOOLEAN DEFAULT FALSE,
            time_taken INT,
            points_earned INT DEFAULT 0,
            answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (room_id) REFERENCES game_rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (participant_id) REFERENCES game_participants(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
            FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE SET NULL,
            INDEX idx_room_answers (room_id, question_id),
            INDEX idx_participant (participant_id)
        )";

        return $this->db->query($sql1) &&
               $this->db->query($sql2) &&
               $this->db->query($sql3);
    }
}

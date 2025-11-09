<?php
// src/database/migrations/20241231_000003_create_bot_detection_table.php
namespace ModernQuiz\Database\Migrations;

class CreateBotDetectionTable {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        $sql = "CREATE TABLE IF NOT EXISTS bot_detection (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            score INT NOT NULL,
            detection_data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_score (ip_address, score)
        )";

        return $this->db->query($sql);
    }
}
<?php
// src/database/migrations/20241231_000002_create_sessions_table.php
namespace ModernQuiz\Database\Migrations;

class CreateSessionsTable {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        $sql = "CREATE TABLE IF NOT EXISTS sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            device_hash VARCHAR(64) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_session (session_id),
            INDEX idx_user_session (user_id, session_id)
        )";

        return $this->db->query($sql);
    }
}

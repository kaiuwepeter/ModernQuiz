<?php

namespace ModernQuiz\Database\Migrations;

use ModernQuiz\Core\Database;

class CreateLoginAttemptsTable
{
    public function up(): void
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "
        CREATE TABLE IF NOT EXISTS login_attempts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_time (ip_address, attempted_at),
            INDEX idx_identifier (identifier)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $pdo->exec($sql);
    }

    public function down(): void
    {
        $pdo = Database::getInstance()->getConnection();
        $pdo->exec("DROP TABLE IF EXISTS login_attempts");
    }
}

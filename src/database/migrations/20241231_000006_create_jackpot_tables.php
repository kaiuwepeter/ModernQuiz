<?php

namespace ModernQuiz\Database\Migrations;

class CreateJackpotTables
{
    public static function up($db)
    {
        // Jackpots (4 verschiedene Typen)
        $db->query("
            CREATE TABLE IF NOT EXISTS jackpots (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                type ENUM('bronze', 'silver', 'gold', 'diamond') NOT NULL UNIQUE,
                current_amount DECIMAL(10,2) DEFAULT 0.00,
                minimum_amount DECIMAL(10,2) DEFAULT 100.00,
                increment_per_correct DECIMAL(10,2) DEFAULT 1.00,
                win_probability DECIMAL(5,4) DEFAULT 0.0001,
                color VARCHAR(20),
                icon VARCHAR(50),
                last_won_by INT NULL,
                last_won_at TIMESTAMP NULL,
                total_won DECIMAL(10,2) DEFAULT 0.00,
                times_won INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (last_won_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Jackpot-Gewinner Historie
        $db->query("
            CREATE TABLE IF NOT EXISTS jackpot_winners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                jackpot_id INT NOT NULL,
                user_id INT NOT NULL,
                amount_won DECIMAL(10,2) NOT NULL,
                question_id INT,
                session_id INT,
                won_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (jackpot_id) REFERENCES jackpots(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE SET NULL,
                FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE SET NULL,
                INDEX idx_user (user_id),
                INDEX idx_jackpot (jackpot_id),
                INDEX idx_date (won_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Jackpot-Historie (für Tracking von Änderungen)
        $db->query("
            CREATE TABLE IF NOT EXISTS jackpot_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                jackpot_id INT NOT NULL,
                old_amount DECIMAL(10,2),
                new_amount DECIMAL(10,2),
                change_type ENUM('increment', 'win', 'reset', 'manual') NOT NULL,
                user_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (jackpot_id) REFERENCES jackpots(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_jackpot (jackpot_id),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Initialisiere die 4 Jackpots
        $db->query("
            INSERT INTO jackpots (name, type, current_amount, minimum_amount, increment_per_correct, win_probability, color, icon) VALUES
            ('Bronze Jackpot', 'bronze', 500.00, 500.00, 0.50, 0.0100, '#CD7F32', 'fa-medal'),
            ('Silber Jackpot', 'silver', 2000.00, 2000.00, 2.00, 0.0050, '#C0C0C0', 'fa-trophy'),
            ('Gold Jackpot', 'gold', 10000.00, 10000.00, 5.00, 0.0010, '#FFD700', 'fa-crown'),
            ('Diamant Jackpot', 'diamond', 50000.00, 50000.00, 10.00, 0.0001, '#B9F2FF', 'fa-gem')
        ");

        return true;
    }

    public static function down($db)
    {
        $db->query("DROP TABLE IF EXISTS jackpot_history");
        $db->query("DROP TABLE IF EXISTS jackpot_winners");
        $db->query("DROP TABLE IF EXISTS jackpots");
        return true;
    }
}

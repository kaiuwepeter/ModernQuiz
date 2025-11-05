<?php

namespace ModernQuiz\Database\Migrations;

class CreateShopTables
{
    public static function up($db)
    {
        // Powerups-Katalog
        $db->query("
            CREATE TABLE IF NOT EXISTS shop_powerups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                effect_type ENUM('50_50', 'skip_question', 'extra_time', 'double_points', 'freeze_time', 'reveal_hint') NOT NULL,
                effect_value VARCHAR(100),
                price INT NOT NULL,
                icon VARCHAR(100),
                is_active BOOLEAN DEFAULT TRUE,
                usage_limit INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // User Powerups (Inventar)
        $db->query("
            CREATE TABLE IF NOT EXISTS user_powerups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                powerup_id INT NOT NULL,
                quantity INT DEFAULT 1,
                acquired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (powerup_id) REFERENCES shop_powerups(id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_powerup (user_id, powerup_id),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Kauf-Historie
        $db->query("
            CREATE TABLE IF NOT EXISTS shop_purchases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                powerup_id INT NOT NULL,
                quantity INT DEFAULT 1,
                total_cost INT NOT NULL,
                purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (powerup_id) REFERENCES shop_powerups(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_date (purchased_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        return true;
    }

    public static function down($db)
    {
        $db->query("DROP TABLE IF EXISTS shop_purchases");
        $db->query("DROP TABLE IF EXISTS user_powerups");
        $db->query("DROP TABLE IF EXISTS shop_powerups");
        return true;
    }
}

<?php
// src/database/migrations/20241231_000007_create_admin_tables.php
namespace ModernQuiz\Database\Migrations;

class CreateAdminTables {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        // User Roles Tabelle
        $sql1 = "CREATE TABLE IF NOT EXISTS user_roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            permissions JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // User Role Assignments Tabelle
        $sql2 = "CREATE TABLE IF NOT EXISTS user_role_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            role_id INT NOT NULL,
            assigned_by INT NOT NULL,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_role (user_id, role_id),
            INDEX idx_user (user_id)
        )";

        // Admin Logs Tabelle
        $sql3 = "CREATE TABLE IF NOT EXISTS admin_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            target_type VARCHAR(50),
            target_id INT,
            details JSON,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_admin (admin_id),
            INDEX idx_action (action),
            INDEX idx_created (created_at)
        )";

        // Reports Tabelle
        $sql4 = "CREATE TABLE IF NOT EXISTS reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reporter_id INT NOT NULL,
            reported_type ENUM('user', 'quiz', 'question', 'comment') NOT NULL,
            reported_id INT NOT NULL,
            reason VARCHAR(255) NOT NULL,
            description TEXT,
            status ENUM('pending', 'reviewing', 'resolved', 'dismissed') DEFAULT 'pending',
            reviewed_by INT,
            resolution TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reviewed_at TIMESTAMP NULL,
            FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_status (status),
            INDEX idx_reported (reported_type, reported_id)
        )";

        // System Settings Tabelle
        $sql5 = "CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
            description TEXT,
            updated_by INT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
        )";

        // Banned Users Tabelle
        $sql6 = "CREATE TABLE IF NOT EXISTS banned_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            banned_by INT NOT NULL,
            reason TEXT NOT NULL,
            banned_until TIMESTAMP NULL,
            is_permanent BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (banned_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id)
        )";

        return $this->db->query($sql1) &&
               $this->db->query($sql2) &&
               $this->db->query($sql3) &&
               $this->db->query($sql4) &&
               $this->db->query($sql5) &&
               $this->db->query($sql6);
    }
}

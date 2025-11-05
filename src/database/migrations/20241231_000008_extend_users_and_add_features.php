<?php
// src/database/migrations/20241231_000008_extend_users_and_add_features.php
namespace ModernQuiz\Database\Migrations;

class ExtendUsersAndAddFeatures {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        // Erweitere users Tabelle
        $sql1 = "ALTER TABLE users
            ADD COLUMN profile_visibility ENUM('public', 'private', 'friends_only') DEFAULT 'public' AFTER two_factor_secret,
            ADD COLUMN bio TEXT AFTER profile_visibility,
            ADD COLUMN avatar VARCHAR(255) AFTER bio,
            ADD COLUMN location VARCHAR(100) AFTER avatar,
            ADD COLUMN website VARCHAR(255) AFTER location,
            ADD COLUMN referred_by INT NULL AFTER website,
            ADD COLUMN referral_code VARCHAR(20) UNIQUE AFTER referred_by,
            ADD COLUMN referral_count INT DEFAULT 0 AFTER referral_code,
            ADD COLUMN inactivity_warning_sent_at TIMESTAMP NULL AFTER last_login,
            ADD COLUMN scheduled_deletion_at TIMESTAMP NULL AFTER inactivity_warning_sent_at,
            ADD INDEX idx_referral_code (referral_code),
            ADD INDEX idx_scheduled_deletion (scheduled_deletion_at),
            ADD FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL";

        // Password Reset Tabelle
        $sql2 = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_token (token),
            INDEX idx_expires (expires_at)
        )";

        // Quiz Reviews Tabelle
        $sql3 = "CREATE TABLE IF NOT EXISTS quiz_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT,
            is_approved BOOLEAN DEFAULT TRUE,
            helpful_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_quiz_review (user_id, quiz_id),
            INDEX idx_quiz_rating (quiz_id, rating),
            INDEX idx_user_reviews (user_id)
        )";

        // Quiz Tags Tabelle
        $sql4 = "CREATE TABLE IF NOT EXISTS quiz_tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            usage_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_usage (usage_count)
        )";

        // Quiz-Tag Relations
        $sql5 = "CREATE TABLE IF NOT EXISTS quiz_tag_relations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            tag_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES quiz_tags(id) ON DELETE CASCADE,
            UNIQUE KEY unique_quiz_tag (quiz_id, tag_id),
            INDEX idx_quiz (quiz_id),
            INDEX idx_tag (tag_id)
        )";

        // Favoriten Tabelle
        $sql6 = "CREATE TABLE IF NOT EXISTS user_favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            quiz_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_favorite (user_id, quiz_id),
            INDEX idx_user_favorites (user_id)
        )";

        // Email Queue (für verzögerte/geplante Emails)
        $sql7 = "CREATE TABLE IF NOT EXISTS email_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            to_email VARCHAR(255) NOT NULL,
            to_name VARCHAR(100),
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            template VARCHAR(50),
            template_data JSON,
            priority INT DEFAULT 5,
            status ENUM('pending', 'sending', 'sent', 'failed') DEFAULT 'pending',
            attempts INT DEFAULT 0,
            max_attempts INT DEFAULT 3,
            error_message TEXT,
            scheduled_at TIMESTAMP NULL,
            sent_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_scheduled (scheduled_at),
            INDEX idx_priority (priority)
        )";

        // Notifications Tabelle
        $sql8 = "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT,
            data JSON,
            is_read BOOLEAN DEFAULT FALSE,
            action_url VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_unread (user_id, is_read),
            INDEX idx_created (created_at)
        )";

        // Review Helpful Votes
        $sql9 = "CREATE TABLE IF NOT EXISTS review_helpful_votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            review_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (review_id) REFERENCES quiz_reviews(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_vote (review_id, user_id)
        )";

        return $this->db->query($sql1) &&
               $this->db->query($sql2) &&
               $this->db->query($sql3) &&
               $this->db->query($sql4) &&
               $this->db->query($sql5) &&
               $this->db->query($sql6) &&
               $this->db->query($sql7) &&
               $this->db->query($sql8) &&
               $this->db->query($sql9);
    }
}

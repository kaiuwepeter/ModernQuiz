<?php
// src/database/migrations/20250106_000001_add_bonus_coins_and_voucher_system.php

namespace ModernQuiz\Database\Migrations;

/**
 * Migration für Bonus Coins und Gutscheinsystem
 *
 * Features:
 * - Bonus Coins (nicht auszahlbar) zusätzlich zu normalen Coins
 * - Gutscheinsystem mit Admin-Verwaltung
 * - Sicherheitsfeatures gegen Betrug
 * - Audit-Log für alle Aktionen
 */
class AddBonusCoinsAndVoucherSystem {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        // 1. Bonus Coins zur user_stats Tabelle hinzufügen
        $sql1 = "ALTER TABLE user_stats
            ADD COLUMN bonus_coins INT DEFAULT 0 AFTER coins,
            ADD INDEX idx_bonus_coins (bonus_coins)";

        // 2. Vouchers (Gutscheine) Tabelle
        $sql2 = "CREATE TABLE IF NOT EXISTS vouchers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(30) UNIQUE NOT NULL COMMENT 'Format: xxxxx-xxx-x-xxxxx-xxx',
            name VARCHAR(100) NOT NULL COMMENT 'Interner Name für Admin',
            description TEXT COMMENT 'Beschreibung für User',

            -- Belohnungen
            coins INT DEFAULT 0 COMMENT 'Normale Coins',
            bonus_coins INT DEFAULT 0 COMMENT 'Bonus Coins (nicht auszahlbar)',
            powerups JSON COMMENT 'Array von Powerup IDs und Mengen: [{\"id\": 1, \"quantity\": 5}]',

            -- Limits und Verfügbarkeit
            max_redemptions INT DEFAULT 1 COMMENT 'Wie oft insgesamt einlösbar',
            current_redemptions INT DEFAULT 0 COMMENT 'Wie oft bereits eingelöst',
            max_per_user INT DEFAULT 1 COMMENT 'Max. Einlösungen pro User',

            -- Gültigkeit
            valid_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            valid_until TIMESTAMP NULL COMMENT 'NULL = unbegrenzt gültig',

            -- Status und Metadata
            is_active BOOLEAN DEFAULT TRUE,
            created_by INT NOT NULL COMMENT 'Admin User ID',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_code (code),
            INDEX idx_active (is_active),
            INDEX idx_valid_until (valid_until),
            INDEX idx_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Gutschein-Verwaltung'";

        // 3. Voucher Redemptions (Einlösungen)
        $sql3 = "CREATE TABLE IF NOT EXISTS voucher_redemptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            voucher_id INT NOT NULL,
            user_id INT NOT NULL,

            -- Was wurde erhalten
            coins_received INT DEFAULT 0,
            bonus_coins_received INT DEFAULT 0,
            powerups_received JSON COMMENT 'Array von erhaltenen Powerups',

            -- Metadata
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_voucher (voucher_id),
            INDEX idx_user (user_id),
            INDEX idx_redeemed_at (redeemed_at),
            INDEX idx_ip (ip_address),
            UNIQUE KEY unique_user_voucher (user_id, voucher_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Gutschein-Einlösungen (erfolgreich)'";

        // 4. Voucher Fraud Log (Betrugsversuche)
        $sql4 = "CREATE TABLE IF NOT EXISTS voucher_fraud_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL COMMENT 'NULL wenn User gelöscht wurde',
            attempted_code VARCHAR(30) NOT NULL,

            -- Fehlertyp
            failure_reason ENUM(
                'invalid_code',
                'expired',
                'max_redemptions_reached',
                'already_redeemed_by_user',
                'voucher_inactive',
                'not_yet_valid',
                'suspicious_pattern',
                'rate_limit_exceeded'
            ) NOT NULL,

            -- Security Metadata
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            attempt_count INT DEFAULT 1 COMMENT 'Anzahl Versuche in dieser Session',
            is_suspicious BOOLEAN DEFAULT FALSE COMMENT 'Verdächtiges Verhalten erkannt',

            -- Admin Actions
            admin_notified BOOLEAN DEFAULT FALSE,
            notified_at TIMESTAMP NULL,
            user_blocked BOOLEAN DEFAULT FALSE,
            blocked_at TIMESTAMP NULL,
            blocked_by INT NULL COMMENT 'Admin User ID',

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (blocked_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_ip (ip_address),
            INDEX idx_created_at (created_at),
            INDEX idx_suspicious (is_suspicious),
            INDEX idx_admin_notified (admin_notified)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Betrugsversuche und fehlgeschlagene Einlösungen'";

        // 5. Voucher Rate Limits (Pro User/IP)
        $sql5 = "CREATE TABLE IF NOT EXISTS voucher_rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            ip_address VARCHAR(45) NOT NULL,

            failed_attempts INT DEFAULT 0,
            last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            blocked_until TIMESTAMP NULL COMMENT 'Temporäre Sperre',
            is_permanently_blocked BOOLEAN DEFAULT FALSE,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_ip (user_id, ip_address),
            INDEX idx_ip (ip_address),
            INDEX idx_blocked_until (blocked_until),
            INDEX idx_permanently_blocked (is_permanently_blocked)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Rate Limiting für Gutschein-Versuche'";

        // 6. Coin Transactions Log (Audit Trail)
        $sql6 = "CREATE TABLE IF NOT EXISTS coin_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,

            transaction_type ENUM(
                'voucher_redemption',
                'quiz_reward',
                'shop_purchase',
                'admin_adjustment',
                'referral_bonus',
                'achievement',
                'daily_reward',
                'withdrawal'
            ) NOT NULL,

            -- Beträge (positiv = Erhalt, negativ = Ausgabe)
            coins_change INT DEFAULT 0,
            bonus_coins_change INT DEFAULT 0,
            coins_before INT NOT NULL,
            bonus_coins_before INT NOT NULL,
            coins_after INT NOT NULL,
            bonus_coins_after INT NOT NULL,

            -- Referenz
            reference_type VARCHAR(50) COMMENT 'z.B. \"voucher\", \"quiz\", \"shop_purchase\"',
            reference_id INT COMMENT 'ID der referenzierten Entität',

            description TEXT,
            metadata JSON COMMENT 'Zusätzliche Daten',

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_type (transaction_type),
            INDEX idx_created_at (created_at),
            INDEX idx_reference (reference_type, reference_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Vollständiger Audit Trail für Coins'";

        // Alle Queries ausführen
        try {
            $this->db->query($sql1);
            $this->db->query($sql2);
            $this->db->query($sql3);
            $this->db->query($sql4);
            $this->db->query($sql5);
            $this->db->query($sql6);
            return true;
        } catch (\Exception $e) {
            error_log("Migration failed: " . $e->getMessage());
            return false;
        }
    }

    public function down(): bool {
        // Rollback in umgekehrter Reihenfolge
        try {
            $this->db->query("DROP TABLE IF EXISTS coin_transactions");
            $this->db->query("DROP TABLE IF EXISTS voucher_rate_limits");
            $this->db->query("DROP TABLE IF EXISTS voucher_fraud_log");
            $this->db->query("DROP TABLE IF EXISTS voucher_redemptions");
            $this->db->query("DROP TABLE IF EXISTS vouchers");
            $this->db->query("ALTER TABLE user_stats DROP COLUMN bonus_coins");
            return true;
        } catch (\Exception $e) {
            error_log("Rollback failed: " . $e->getMessage());
            return false;
        }
    }
}

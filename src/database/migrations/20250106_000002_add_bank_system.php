<?php
// src/database/migrations/20250106_000002_add_bank_system.php

namespace ModernQuiz\Database\Migrations;

/**
 * Migration für Bank-System
 *
 * Features:
 * - Festgeld-Einlagen mit 30 Tagen Laufzeit
 * - 4% Zinsen bei voller Laufzeit
 * - Vorzeitige Kündigung mit 12% Strafgebühr
 * - Vollständiger Kontoauszug
 * - Admin-Verwaltung aller Einlagen
 */
class AddBankSystem {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        // 1. Bank Settings Tabelle (Konfiguration)
        $sql1 = "CREATE TABLE IF NOT EXISTS bank_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) UNIQUE NOT NULL,
            setting_value VARCHAR(255) NOT NULL,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT NULL,

            FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bank-Konfiguration'";

        // 2. Bank Deposits Tabelle (Festgeld-Einlagen)
        $sql2 = "CREATE TABLE IF NOT EXISTS bank_deposits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,

            -- Einlage-Details
            coins_deposited INT DEFAULT 0 COMMENT 'Eingezahlte normale Coins',
            bonus_coins_deposited INT DEFAULT 0 COMMENT 'Eingezahlte Bonus Coins',
            total_deposited INT GENERATED ALWAYS AS (coins_deposited + bonus_coins_deposited) STORED,

            -- Zinsen und Auszahlung
            interest_rate DECIMAL(5,2) DEFAULT 4.00 COMMENT 'Zinssatz in Prozent',
            interest_earned INT DEFAULT 0 COMMENT 'Berechnete Zinsen',
            penalty_fee INT DEFAULT 0 COMMENT 'Strafgebühr bei vorzeitiger Kündigung',

            -- Finale Auszahlung
            coins_payout INT DEFAULT 0 COMMENT 'Ausgezahlte normale Coins',
            bonus_coins_payout INT DEFAULT 0 COMMENT 'Ausgezahlte Bonus Coins',
            total_payout INT DEFAULT 0 COMMENT 'Gesamtauszahlung',

            -- Zeitrahmen
            duration_days INT DEFAULT 30 COMMENT 'Laufzeit in Tagen',
            deposit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            maturity_date TIMESTAMP NOT NULL COMMENT 'Fälligkeitsdatum',
            withdrawal_date TIMESTAMP NULL COMMENT 'Tatsächliches Auszahlungsdatum',

            -- Status
            status ENUM(
                'active',           -- Läuft noch
                'matured',          -- Fällig, bereit zur Auszahlung
                'completed',        -- Ausgezahlt
                'cancelled',        -- Vorzeitig gekündigt
                'locked'            -- Von Admin gesperrt
            ) DEFAULT 'active',

            -- Admin-Aktionen
            is_locked BOOLEAN DEFAULT FALSE,
            locked_by INT NULL COMMENT 'Admin User ID',
            locked_at TIMESTAMP NULL,
            lock_reason TEXT,

            -- Metadata
            cancelled_at TIMESTAMP NULL,
            is_early_withdrawal BOOLEAN DEFAULT FALSE,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (locked_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            INDEX idx_maturity_date (maturity_date),
            INDEX idx_locked (is_locked)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bank Festgeld-Einlagen'";

        // 3. Bank Transactions Tabelle (Kontoauszug)
        $sql3 = "CREATE TABLE IF NOT EXISTS bank_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            deposit_id INT NULL COMMENT 'Referenz zur Einlage, falls zutreffend',

            transaction_type ENUM(
                'deposit',          -- Einzahlung
                'withdrawal',       -- Auszahlung (normal)
                'early_withdrawal', -- Vorzeitige Auszahlung
                'interest',         -- Zinsgutschrift
                'penalty',          -- Strafgebühr
                'admin_adjustment', -- Admin-Korrektur
                'deposit_return'    -- Rückbuchung bei Stornierung
            ) NOT NULL,

            -- Beträge (positiv = Erhalt, negativ = Abzug)
            coins_amount INT DEFAULT 0,
            bonus_coins_amount INT DEFAULT 0,
            total_amount INT GENERATED ALWAYS AS (coins_amount + bonus_coins_amount) STORED,

            -- Kontostand nach Transaktion (in der Bank)
            coins_balance_before INT NOT NULL,
            bonus_coins_balance_before INT NOT NULL,
            coins_balance_after INT NOT NULL,
            bonus_coins_balance_after INT NOT NULL,

            description TEXT NOT NULL,
            metadata JSON COMMENT 'Zusätzliche Informationen',

            -- Admin-Aktion?
            is_admin_action BOOLEAN DEFAULT FALSE,
            admin_user_id INT NULL,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (deposit_id) REFERENCES bank_deposits(id) ON DELETE SET NULL,
            FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_deposit (deposit_id),
            INDEX idx_type (transaction_type),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bank Kontoauszug'";

        // 4. Bank Account Balances (Aktueller Bank-Kontostand pro User)
        $sql4 = "CREATE TABLE IF NOT EXISTS bank_account_balances (
            user_id INT PRIMARY KEY,
            coins_balance INT DEFAULT 0 COMMENT 'Aktuell in Bank eingezahlte Coins',
            bonus_coins_balance INT DEFAULT 0 COMMENT 'Aktuell in Bank eingezahlte Bonus Coins',
            total_balance INT GENERATED ALWAYS AS (coins_balance + bonus_coins_balance) STORED,

            total_deposits_count INT DEFAULT 0,
            total_withdrawals_count INT DEFAULT 0,
            total_interest_earned INT DEFAULT 0,
            total_penalties_paid INT DEFAULT 0,

            last_deposit_at TIMESTAMP NULL,
            last_withdrawal_at TIMESTAMP NULL,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bank Kontostände'";

        // 5. Admin Actions Log (Admin-Aktionen auf Users/Bank)
        $sql5 = "CREATE TABLE IF NOT EXISTS admin_actions_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_user_id INT NOT NULL,
            target_user_id INT NULL COMMENT 'Betroffener User',

            action_type ENUM(
                'user_lock',
                'user_unlock',
                'user_email_change',
                'user_password_change',
                'user_delete',
                'bank_deposit_lock',
                'bank_deposit_unlock',
                'bank_deposit_release',
                'bank_deposit_cancel',
                'coins_adjustment',
                'other'
            ) NOT NULL,

            action_details TEXT NOT NULL,
            metadata JSON,

            -- Werte vor/nach der Aktion
            before_value TEXT,
            after_value TEXT,

            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_admin (admin_user_id),
            INDEX idx_target_user (target_user_id),
            INDEX idx_action_type (action_type),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Admin-Aktionen Log'";

        // Default Settings einfügen
        $sql6 = "INSERT INTO bank_settings (setting_key, setting_value, description) VALUES
            ('interest_rate', '4.00', 'Zinssatz in Prozent (Standard: 4%)'),
            ('duration_days', '30', 'Standard-Laufzeit in Tagen'),
            ('penalty_rate', '12.00', 'Strafgebühr bei vorzeitiger Kündigung in Prozent'),
            ('min_deposit', '100', 'Mindesteinlage'),
            ('max_deposit', '100000', 'Maximaleinlage pro Vorgang'),
            ('bank_enabled', '1', 'Bank-System aktiviert (1) oder deaktiviert (0)')
        ON DUPLICATE KEY UPDATE setting_value=setting_value";

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
            error_log("Bank System Migration failed: " . $e->getMessage());
            return false;
        }
    }

    public function down(): bool {
        // Rollback in umgekehrter Reihenfolge
        try {
            $this->db->query("DROP TABLE IF EXISTS admin_actions_log");
            $this->db->query("DROP TABLE IF EXISTS bank_account_balances");
            $this->db->query("DROP TABLE IF EXISTS bank_transactions");
            $this->db->query("DROP TABLE IF EXISTS bank_deposits");
            $this->db->query("DROP TABLE IF EXISTS bank_settings");
            return true;
        } catch (\Exception $e) {
            error_log("Bank System Rollback failed: " . $e->getMessage());
            return false;
        }
    }
}

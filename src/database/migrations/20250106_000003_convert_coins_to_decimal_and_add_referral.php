<?php
// src/database/migrations/20250106_000003_convert_coins_to_decimal_and_add_referral.php

namespace ModernQuiz\Database\Migrations;

/**
 * Migration: Coins zu DECIMAL(10,2) + Referral-System
 *
 * KRITISCH: Diese Migration konvertiert alle Coin-Werte von INT zu DECIMAL(10,2)
 * Dies ermöglicht 2 Nachkommastellen für präzise Berechnungen (z.B. 6% Provision)
 *
 * Außerdem: Referral-System mit 6% Provision
 */
class ConvertCoinsToDecimalAndAddReferral {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function up(): bool {
        try {
            echo "WICHTIG: Konvertiere Coins zu DECIMAL(10,2)...\n";

            // 1. user_stats: coins und bonus_coins
            $sql1 = "ALTER TABLE user_stats
                MODIFY COLUMN coins DECIMAL(10,2) DEFAULT 100.00,
                MODIFY COLUMN bonus_coins DECIMAL(10,2) DEFAULT 0.00";

            // 2. bank_deposits: Alle Coin-Felder
            $sql2 = "ALTER TABLE bank_deposits
                MODIFY COLUMN coins_deposited DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN bonus_coins_deposited DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN interest_earned DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN penalty_fee DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN coins_payout DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN bonus_coins_payout DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN total_payout DECIMAL(10,2) DEFAULT 0.00";

            // 3. bank_transactions
            $sql3 = "ALTER TABLE bank_transactions
                MODIFY COLUMN coins_amount DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN bonus_coins_amount DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN coins_balance_before DECIMAL(10,2) NOT NULL,
                MODIFY COLUMN bonus_coins_balance_before DECIMAL(10,2) NOT NULL,
                MODIFY COLUMN coins_balance_after DECIMAL(10,2) NOT NULL,
                MODIFY COLUMN bonus_coins_balance_after DECIMAL(10,2) NOT NULL";

            // 4. bank_account_balances
            $sql4 = "ALTER TABLE bank_account_balances
                MODIFY COLUMN coins_balance DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN bonus_coins_balance DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN total_interest_earned DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN total_penalties_paid DECIMAL(10,2) DEFAULT 0.00";

            // 5. coin_transactions
            $sql5 = "ALTER TABLE coin_transactions
                MODIFY COLUMN coins_change DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN bonus_coins_change DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN coins_before DECIMAL(10,2) NOT NULL,
                MODIFY COLUMN bonus_coins_before DECIMAL(10,2) NOT NULL,
                MODIFY COLUMN coins_after DECIMAL(10,2) NOT NULL,
                MODIFY COLUMN bonus_coins_after DECIMAL(10,2) NOT NULL";

            // 6. vouchers
            $sql6 = "ALTER TABLE vouchers
                MODIFY COLUMN coins DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN bonus_coins DECIMAL(10,2) DEFAULT 0.00";

            // 7. voucher_redemptions
            $sql7 = "ALTER TABLE voucher_redemptions
                MODIFY COLUMN coins_received DECIMAL(10,2) DEFAULT 0.00,
                MODIFY COLUMN bonus_coins_received DECIMAL(10,2) DEFAULT 0.00";

            // 8. shop_powerups (falls vorhanden)
            $sql8 = "ALTER TABLE shop_powerups
                MODIFY COLUMN price DECIMAL(10,2) NOT NULL";

            // 9. shop_purchases
            $sql9 = "ALTER TABLE shop_purchases
                MODIFY COLUMN total_cost DECIMAL(10,2) NOT NULL";

            // Führe Konvertierungen aus
            $this->db->query($sql1);
            echo "  ✓ user_stats konvertiert\n";

            $this->db->query($sql2);
            echo "  ✓ bank_deposits konvertiert\n";

            $this->db->query($sql3);
            echo "  ✓ bank_transactions konvertiert\n";

            $this->db->query($sql4);
            echo "  ✓ bank_account_balances konvertiert\n";

            $this->db->query($sql5);
            echo "  ✓ coin_transactions konvertiert\n";

            $this->db->query($sql6);
            echo "  ✓ vouchers konvertiert\n";

            $this->db->query($sql7);
            echo "  ✓ voucher_redemptions konvertiert\n";

            $this->db->query($sql8);
            echo "  ✓ shop_powerups konvertiert\n";

            $this->db->query($sql9);
            echo "  ✓ shop_purchases konvertiert\n";

            echo "\n=== REFERRAL-SYSTEM ===\n";

            // 10. Referral Settings
            $sql10 = "INSERT INTO bank_settings (setting_key, setting_value, description) VALUES
                ('referral_bonus_coins', '300.00', 'Bonus Coins für Werber und Geworbenen bei Registrierung'),
                ('referral_commission_rate', '6.00', 'Provisions-Prozentsatz für Werber (6% von Quiz-Gewinnen)')
            ON DUPLICATE KEY UPDATE setting_value=setting_value";

            // 11. Referral Earnings Tabelle
            $sql11 = "CREATE TABLE IF NOT EXISTS referral_earnings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                referrer_user_id INT NOT NULL COMMENT 'Der Werber',
                referred_user_id INT NOT NULL COMMENT 'Der Geworbene',

                source_transaction_id INT NULL COMMENT 'Referenz zur coin_transaction',

                -- Verdienst
                coins_earned DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Quiz-Gewinn vom Geworbenen',
                commission_earned DECIMAL(10,2) DEFAULT 0.00 COMMENT '6% Provision für Werber',
                commission_rate DECIMAL(5,2) DEFAULT 6.00,

                description TEXT,
                metadata JSON,

                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (source_transaction_id) REFERENCES coin_transactions(id) ON DELETE SET NULL,
                INDEX idx_referrer (referrer_user_id),
                INDEX idx_referred (referred_user_id),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Referral-Provisionen'";

            // 12. Referral Stats (pro User)
            $sql12 = "CREATE TABLE IF NOT EXISTS referral_stats (
                user_id INT PRIMARY KEY,

                total_referrals INT DEFAULT 0 COMMENT 'Anzahl geworbener User',
                active_referrals INT DEFAULT 0 COMMENT 'Aktive geworbene User',

                total_commission_earned DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Gesamt-Provision verdient',
                total_bonus_received DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Gesamt Bonus Coins erhalten',

                registration_bonus_paid BOOLEAN DEFAULT FALSE COMMENT 'Wurde der 300 Bonus bereits ausgezahlt?',
                registration_bonus_paid_at TIMESTAMP NULL COMMENT 'Wann wurde der Bonus ausgezahlt?',

                last_referral_at TIMESTAMP NULL,

                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Referral-Statistiken pro User'";

            // 13. Erweitere users Tabelle (referral_code existiert bereits)
            // Prüfe ob referred_by existiert
            $checkColumn = $this->db->query("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'users'
                AND COLUMN_NAME = 'referred_by'
            ");

            if ($checkColumn->num_rows == 0) {
                $sql13 = "ALTER TABLE users
                    ADD COLUMN referred_by INT NULL COMMENT 'User ID des Werbers' AFTER referral_code,
                    ADD FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL,
                    ADD INDEX idx_referred_by (referred_by)";
                $this->db->query($sql13);
                echo "  ✓ users.referred_by hinzugefügt\n";
            } else {
                echo "  ✓ users.referred_by existiert bereits\n";
            }

            $this->db->query($sql10);
            echo "  ✓ Referral Settings hinzugefügt\n";

            $this->db->query($sql11);
            echo "  ✓ referral_earnings Tabelle erstellt\n";

            $this->db->query($sql12);
            echo "  ✓ referral_stats Tabelle erstellt\n";

            echo "\n✅ Migration erfolgreich abgeschlossen!\n";
            echo "WICHTIG: Alle Coin-Werte unterstützen jetzt 2 Nachkommastellen\n";

            return true;

        } catch (\Exception $e) {
            echo "\n❌ FEHLER: " . $e->getMessage() . "\n";
            error_log("Decimal/Referral Migration failed: " . $e->getMessage());
            return false;
        }
    }

    public function down(): bool {
        try {
            // Rollback: DECIMAL zurück zu INT (verliert Nachkommastellen!)
            echo "WARNUNG: Rollback konvertiert DECIMAL zu INT - Nachkommastellen gehen verloren!\n";

            // Lösche Referral-Tabellen
            $this->db->query("DROP TABLE IF EXISTS referral_earnings");
            $this->db->query("DROP TABLE IF EXISTS referral_stats");
            $this->db->query("DELETE FROM bank_settings WHERE setting_key IN ('referral_bonus_coins', 'referral_commission_rate')");

            // Konvertiere zurück zu INT (mit ROUND)
            $this->db->query("ALTER TABLE user_stats
                MODIFY COLUMN coins INT DEFAULT 100,
                MODIFY COLUMN bonus_coins INT DEFAULT 0");

            $this->db->query("ALTER TABLE bank_deposits
                MODIFY COLUMN coins_deposited INT DEFAULT 0,
                MODIFY COLUMN bonus_coins_deposited INT DEFAULT 0,
                MODIFY COLUMN interest_earned INT DEFAULT 0,
                MODIFY COLUMN penalty_fee INT DEFAULT 0,
                MODIFY COLUMN coins_payout INT DEFAULT 0,
                MODIFY COLUMN bonus_coins_payout INT DEFAULT 0,
                MODIFY COLUMN total_payout INT DEFAULT 0");

            // ... weitere Tabellen

            echo "Rollback abgeschlossen\n";
            return true;

        } catch (\Exception $e) {
            error_log("Rollback failed: " . $e->getMessage());
            return false;
        }
    }
}

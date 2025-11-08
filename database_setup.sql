-- ============================================
-- ModernQuiz - Komplettes Datenbank-Setup
-- ============================================
-- Dieses SQL-Script erstellt alle benötigten Tabellen,
-- fügt Demo-Daten hinzu und legt einen Superadmin an.
--
-- SUPERADMIN Login:
-- Username: admin
-- Passwort: admin123
-- Email: admin@modernquiz.local
-- ============================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================
-- 1. USERS & AUTH
-- ============================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `is_admin` BOOLEAN DEFAULT FALSE,
  `verification_token` VARCHAR(64) NULL,
  `two_factor_enabled` BOOLEAN DEFAULT FALSE,
  `two_factor_secret` VARCHAR(32) NULL,
  `referral_code` VARCHAR(20) UNIQUE NOT NULL,
  `referred_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL,
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_referral_code` (`referral_code`),
  INDEX `idx_referred_by` (`referred_by`),
  FOREIGN KEY (`referred_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `session_token` VARCHAR(64) UNIQUE NOT NULL,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_session_token` (`session_token`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `identifier` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `success` BOOLEAN DEFAULT FALSE,
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_identifier` (`identifier`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bot_detection` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `request_count` INT DEFAULT 1,
  `last_request` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_blocked` BOOLEAN DEFAULT FALSE,
  `blocked_until` TIMESTAMP NULL,
  INDEX `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. USER STATS & ECONOMY
-- ============================================

CREATE TABLE IF NOT EXISTS `user_stats` (
  `user_id` INT PRIMARY KEY,
  `total_points` INT DEFAULT 0,
  `total_coins` INT DEFAULT 0,
  `coins` DECIMAL(10,2) DEFAULT 100.00,
  `bonus_coins` DECIMAL(10,2) DEFAULT 0.00,
  `level` INT DEFAULT 1,
  `experience` INT DEFAULT 0,
  `quizzes_completed` INT DEFAULT 0,
  `correct_answers` INT DEFAULT 0,
  `total_answers` INT DEFAULT 0,
  `current_streak` INT DEFAULT 0,
  `longest_streak` INT DEFAULT 0,
  `last_quiz_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `coin_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `transaction_type` ENUM('quiz_reward', 'shop_purchase', 'referral_bonus', 'referral_commission', 'voucher_redeem', 'bank_deposit', 'bank_withdrawal', 'bank_interest', 'bank_penalty', 'admin_adjustment') NOT NULL,
  `coins_change` DECIMAL(10,2) DEFAULT 0.00,
  `bonus_coins_change` DECIMAL(10,2) DEFAULT 0.00,
  `coins_before` DECIMAL(10,2) NOT NULL,
  `bonus_coins_before` DECIMAL(10,2) NOT NULL,
  `coins_after` DECIMAL(10,2) NOT NULL,
  `bonus_coins_after` DECIMAL(10,2) NOT NULL,
  `description` TEXT,
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_type` (`transaction_type`),
  INDEX `idx_created` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. REFERRAL SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `referral_stats` (
  `user_id` INT PRIMARY KEY,
  `total_referrals` INT DEFAULT 0,
  `active_referrals` INT DEFAULT 0,
  `total_commission_earned` DECIMAL(10,2) DEFAULT 0.00,
  `total_bonus_received` DECIMAL(10,2) DEFAULT 0.00,
  `registration_bonus_paid` BOOLEAN DEFAULT FALSE,
  `registration_bonus_paid_at` TIMESTAMP NULL,
  `last_referral_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `referral_earnings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `referrer_user_id` INT NOT NULL COMMENT 'Der Werber',
  `referred_user_id` INT NOT NULL COMMENT 'Der Geworbene',
  `source_transaction_id` INT NULL,
  `coins_earned` DECIMAL(10,2) DEFAULT 0.00,
  `commission_earned` DECIMAL(10,2) DEFAULT 0.00,
  `commission_rate` DECIMAL(5,2) DEFAULT 6.00,
  `description` TEXT,
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_referrer` (`referrer_user_id`),
  INDEX `idx_referred` (`referred_user_id`),
  INDEX `idx_created` (`created_at`),
  FOREIGN KEY (`referrer_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`referred_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`source_transaction_id`) REFERENCES `coin_transactions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. QUIZ SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `quiz_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `icon` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `question` TEXT NOT NULL,
  `difficulty` ENUM('easy', 'medium', 'hard', 'expert') DEFAULT 'medium',
  `points` INT DEFAULT 10,
  `time_limit` INT DEFAULT 30,
  `image_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category_id`),
  INDEX `idx_difficulty` (`difficulty`),
  FOREIGN KEY (`category_id`) REFERENCES `quiz_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_answers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT NOT NULL,
  `answer_text` TEXT NOT NULL,
  `is_correct` BOOLEAN DEFAULT FALSE,
  `explanation` TEXT,
  INDEX `idx_question` (`question_id`),
  FOREIGN KEY (`question_id`) REFERENCES `quiz_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT,
  `status` ENUM('active', 'completed', 'abandoned') DEFAULT 'active',
  `total_questions` INT DEFAULT 0,
  `correct_answers` INT DEFAULT 0,
  `total_points` INT DEFAULT 0,
  `bonus_points` INT DEFAULT 0,
  `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `completed_at` TIMESTAMP NULL,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `quiz_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_answers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_id` INT NOT NULL,
  `question_id` INT NOT NULL,
  `answer_id` INT NOT NULL,
  `is_correct` BOOLEAN,
  `points_earned` INT DEFAULT 0,
  `time_taken` INT DEFAULT 0,
  `powerup_used` VARCHAR(50),
  `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_session` (`session_id`),
  INDEX `idx_question` (`question_id`),
  FOREIGN KEY (`session_id`) REFERENCES `quiz_sessions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`question_id`) REFERENCES `quiz_questions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`answer_id`) REFERENCES `quiz_answers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. SHOP SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `shop_powerups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(50) DEFAULT 'Sonstige',
  `effect_type` ENUM('50_50', 'skip_question', 'extra_time', 'double_points', 'freeze_time', 'reveal_hint') NOT NULL,
  `effects` JSON,
  `price` DECIMAL(10,2) NOT NULL,
  `icon` VARCHAR(100),
  `is_active` BOOLEAN DEFAULT TRUE,
  `usage_limit` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_powerups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `powerup_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  `acquired_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_powerup` (`user_id`, `powerup_id`),
  INDEX `idx_user` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`powerup_id`) REFERENCES `shop_powerups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_purchases` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `powerup_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  `total_cost` DECIMAL(10,2) NOT NULL,
  `currency_used` ENUM('coins', 'bonus_coins', 'auto') DEFAULT 'auto',
  `purchased_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_date` (`purchased_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`powerup_id`) REFERENCES `shop_powerups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. VOUCHER SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) UNIQUE NOT NULL,
  `coins` DECIMAL(10,2) DEFAULT 0.00,
  `bonus_coins` DECIMAL(10,2) DEFAULT 0.00,
  `max_uses` INT DEFAULT 1,
  `current_uses` INT DEFAULT 0,
  `expires_at` TIMESTAMP NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_code` (`code`),
  INDEX `idx_active` (`is_active`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `voucher_redemptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `voucher_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `coins_received` DECIMAL(10,2) DEFAULT 0.00,
  `bonus_coins_received` DECIMAL(10,2) DEFAULT 0.00,
  `redeemed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_voucher` (`voucher_id`),
  INDEX `idx_user` (`user_id`),
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. BANK SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `bank_settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` VARCHAR(255) NOT NULL,
  `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bank_account_balances` (
  `user_id` INT PRIMARY KEY,
  `coins_balance` DECIMAL(10,2) DEFAULT 0.00,
  `bonus_coins_balance` DECIMAL(10,2) DEFAULT 0.00,
  `total_deposits` INT DEFAULT 0,
  `total_withdrawals` INT DEFAULT 0,
  `total_interest_earned` DECIMAL(10,2) DEFAULT 0.00,
  `total_penalties_paid` DECIMAL(10,2) DEFAULT 0.00,
  `last_deposit_at` TIMESTAMP NULL,
  `last_withdrawal_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bank_deposits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `deposit_type` ENUM('3_days', '7_days', '30_days') NOT NULL,
  `coins_deposited` DECIMAL(10,2) DEFAULT 0.00,
  `bonus_coins_deposited` DECIMAL(10,2) DEFAULT 0.00,
  `interest_rate` DECIMAL(5,2) NOT NULL,
  `interest_earned` DECIMAL(10,2) DEFAULT 0.00,
  `penalty_rate` DECIMAL(5,2) DEFAULT 50.00,
  `penalty_fee` DECIMAL(10,2) DEFAULT 0.00,
  `status` ENUM('active', 'completed', 'withdrawn_early') DEFAULT 'active',
  `coins_payout` DECIMAL(10,2) DEFAULT 0.00,
  `bonus_coins_payout` DECIMAL(10,2) DEFAULT 0.00,
  `total_payout` DECIMAL(10,2) DEFAULT 0.00,
  `deposited_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `matures_at` TIMESTAMP NOT NULL,
  `withdrawn_at` TIMESTAMP NULL,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_matures` (`matures_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bank_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `deposit_id` INT NULL,
  `transaction_type` ENUM('deposit', 'early_withdrawal', 'maturity_payout', 'interest_credit', 'penalty_deduction') NOT NULL,
  `coins_amount` DECIMAL(10,2) DEFAULT 0.00,
  `bonus_coins_amount` DECIMAL(10,2) DEFAULT 0.00,
  `coins_balance_before` DECIMAL(10,2) NOT NULL,
  `bonus_coins_balance_before` DECIMAL(10,2) NOT NULL,
  `coins_balance_after` DECIMAL(10,2) NOT NULL,
  `bonus_coins_balance_after` DECIMAL(10,2) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_deposit` (`deposit_id`),
  INDEX `idx_type` (`transaction_type`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`deposit_id`) REFERENCES `bank_deposits`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. CHAT SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `is_system_message` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_created` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. LEADERBOARDS (VIEW)
-- ============================================

CREATE OR REPLACE VIEW leaderboard_global AS
SELECT
    u.id AS user_id,
    u.username,
    us.total_points,
    us.quizzes_completed,
    CASE
        WHEN us.total_answers > 0 THEN ROUND((us.correct_answers / us.total_answers) * 100)
        ELSE 0
    END AS accuracy,
    us.level,
    us.current_streak
FROM users u
JOIN user_stats us ON u.id = us.user_id
WHERE u.is_active = TRUE
ORDER BY us.total_points DESC, us.quizzes_completed DESC
LIMIT 100;

-- ============================================
-- DEMO-DATEN: Quiz-Kategorien
-- ============================================

INSERT INTO `quiz_categories` (`name`, `description`, `icon`) VALUES
('Allgemeinwissen', 'Teste dein Allgemeinwissen', '🧠'),
('Geographie', 'Wie gut kennst du die Welt?', '🌍'),
('Geschichte', 'Reise durch die Zeit', '🏛️'),
('Wissenschaft', 'Naturwissenschaften und Technik', '🔬'),
('Kultur', 'Kunst, Musik und Literatur', '🎨'),
('Sport', 'Alles rund um Sport', '⚽'),
('Filme & Serien', 'Cinema und TV-Wissen', '🎬'),
('Musik', 'Von Klassik bis Pop', '🎵'),
('Technik', 'Computer und Technologie', '💻'),
('Natur', 'Tiere und Pflanzen', '🌿'),
('Politik', 'Weltpolitik und Gesellschaft', '🏛️'),
('Wirtschaft', 'Finanzen und Business', '💰'),
('Literatur', 'Bücher und Autoren', '📚'),
('Mathematik', 'Zahlen und Logik', '🔢'),
('Gaming', 'Videospiele und E-Sport', '🎮');

-- ============================================
-- DEMO-DATEN: Beispiel-Fragen (Allgemeinwissen)
-- ============================================

INSERT INTO `quiz_questions` (`category_id`, `question`, `difficulty`, `points`, `time_limit`) VALUES
(1, 'Was ist die Hauptstadt von Deutschland?', 'easy', 10, 30),
(1, 'Wie viele Kontinente gibt es?', 'easy', 10, 30),
(1, 'Welches ist das größte Land der Welt?', 'medium', 15, 30),
(1, 'In welchem Jahr landete der erste Mensch auf dem Mond?', 'medium', 15, 30),
(1, 'Wie heißt der höchste Berg der Welt?', 'easy', 10, 30),
(1, 'Welches Element hat das chemische Symbol H?', 'easy', 10, 30),
(1, 'Wie viele Spieler hat ein Fußballteam auf dem Feld?', 'easy', 10, 30),
(1, 'Wer malte die Mona Lisa?', 'medium', 15, 30),
(1, 'Wie viele Stunden hat ein Tag?', 'easy', 10, 30),
(1, 'Welcher Planet ist der Erde am nächsten?', 'medium', 15, 30);

-- Antworten für Frage 1 (Hauptstadt Deutschland)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(1, 'Berlin', TRUE, 'Berlin ist seit 1990 die Hauptstadt der Bundesrepublik Deutschland.'),
(1, 'München', FALSE, 'München ist die Hauptstadt Bayerns, aber nicht von Deutschland.'),
(1, 'Hamburg', FALSE, 'Hamburg ist ein Stadtstaat, aber nicht die Hauptstadt.'),
(1, 'Köln', FALSE, 'Köln ist eine Großstadt in Nordrhein-Westfalen.');

-- Antworten für Frage 2 (Kontinente)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(2, '5', FALSE, 'Es gibt mehr als 5 Kontinente.'),
(2, '6', FALSE, 'Die Anzahl der Kontinente wird unterschiedlich gezählt, aber 7 ist die gängigste Zählung.'),
(2, '7', TRUE, 'Afrika, Antarktika, Asien, Australien/Ozeanien, Europa, Nordamerika, Südamerika.'),
(2, '8', FALSE, 'Es gibt nur 7 Kontinente.');

-- Antworten für Frage 3 (Größtes Land)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(3, 'Kanada', FALSE, 'Kanada ist das zweitgrößte Land der Welt.'),
(3, 'USA', FALSE, 'Die USA sind das drittgrößte Land nach Fläche.'),
(3, 'Russland', TRUE, 'Russland ist mit über 17 Millionen km² das größte Land der Erde.'),
(3, 'China', FALSE, 'China ist das viertgrößte Land der Welt.');

-- Antworten für Frage 4 (Mondlandung)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(4, '1965', FALSE, 'Die erste Mondlandung war später.'),
(4, '1969', TRUE, 'Am 20. Juli 1969 landeten Neil Armstrong und Buzz Aldrin auf dem Mond.'),
(4, '1971', FALSE, 'Zu diesem Zeitpunkt gab es bereits weitere Mondmissionen.'),
(4, '1973', FALSE, 'Das Apollo-Programm endete 1972.');

-- Antworten für Frage 5 (Höchster Berg)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(5, 'K2', FALSE, 'Der K2 ist der zweithöchste Berg der Welt.'),
(5, 'Mount Everest', TRUE, 'Der Mount Everest ist mit 8.849 Metern der höchste Berg der Erde.'),
(5, 'Kangchendzönga', FALSE, 'Der Kangchendzönga ist der dritthöchste Berg.'),
(5, 'Lhotse', FALSE, 'Der Lhotse ist der vierthöchste Berg der Welt.');

-- Antworten für Frage 6 (Wasserstoff)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(6, 'Wasserstoff', TRUE, 'H steht für Wasserstoff (Hydrogenium).'),
(6, 'Helium', FALSE, 'Helium hat das Symbol He.'),
(6, 'Sauerstoff', FALSE, 'Sauerstoff hat das Symbol O.'),
(6, 'Stickstoff', FALSE, 'Stickstoff hat das Symbol N.');

-- Antworten für Frage 7 (Fußball)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(7, '10', FALSE, 'Ein Team hat mehr Spieler auf dem Feld.'),
(7, '11', TRUE, 'Ein Fußballteam besteht aus 11 Spielern auf dem Feld.'),
(7, '12', FALSE, 'Das wären zu viele Spieler.'),
(7, '9', FALSE, 'Das sind zu wenige Spieler.');

-- Antworten für Frage 8 (Mona Lisa)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(8, 'Leonardo da Vinci', TRUE, 'Die Mona Lisa wurde zwischen 1503 und 1519 von Leonardo da Vinci gemalt.'),
(8, 'Michelangelo', FALSE, 'Michelangelo war Bildhauer und Maler, aber nicht der Schöpfer der Mona Lisa.'),
(8, 'Raffael', FALSE, 'Raffael war ein italienischer Maler, aber nicht der Urheber dieses Werks.'),
(8, 'Caravaggio', FALSE, 'Caravaggio lebte später und malte in einem anderen Stil.');

-- Antworten für Frage 9 (Stunden pro Tag)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(9, '12', FALSE, 'Ein Tag hat mehr als 12 Stunden.'),
(9, '24', TRUE, 'Ein Tag hat 24 Stunden.'),
(9, '48', FALSE, 'Das wären zwei Tage.'),
(9, '36', FALSE, 'Ein Tag hat weniger Stunden.');

-- Antworten für Frage 10 (Planet)
INSERT INTO `quiz_answers` (`question_id`, `answer_text`, `is_correct`, `explanation`) VALUES
(10, 'Mars', FALSE, 'Mars ist weiter von der Erde entfernt als Venus.'),
(10, 'Venus', TRUE, 'Venus ist der erdnächste Planet mit durchschnittlich 41 Millionen km Entfernung.'),
(10, 'Merkur', FALSE, 'Merkur ist zwar der sonnennächste, aber nicht der erdnächste Planet.'),
(10, 'Jupiter', FALSE, 'Jupiter ist ein äußerer Planet und weit von der Erde entfernt.');

-- ============================================
-- DEMO-DATEN: Shop Powerups
-- ============================================

INSERT INTO `shop_powerups` (`name`, `description`, `category`, `effect_type`, `effects`, `price`, `icon`, `is_active`) VALUES
('50:50 Joker', 'Entfernt 2 falsche Antworten', 'Hilfs-Powerups', '50_50', '{"remove_answers": 2}', 50.00, '🎯', TRUE),
('Extra Zeit +15s', 'Gibt dir 15 Sekunden mehr Zeit', 'Zeit-Powerups', 'extra_time', '{"extra_time": 15}', 75.00, '⏰', TRUE),
('Extra Zeit +30s', 'Gibt dir 30 Sekunden mehr Zeit', 'Zeit-Powerups', 'extra_time', '{"extra_time": 30}', 120.00, '⏱️', TRUE),
('Doppelte Punkte', 'Verdoppelt die Punkte dieser Frage', 'Punkte-Powerups', 'double_points', '{"point_multiplier": 2}', 100.00, '⭐', TRUE),
('Frage überspringen', 'Überspringe diese Frage ohne Punktabzug', 'Spezial-Powerups', 'skip_question', '{"skip_question": true}', 150.00, '⏭️', TRUE),
('Zeit einfrieren', 'Friert die Zeit für 10 Sekunden ein', 'Zeit-Powerups', 'freeze_time', '{"freeze_time": 10}', 200.00, '❄️', TRUE);

-- ============================================
-- DEMO-DATEN: Bank Settings
-- ============================================

INSERT INTO `bank_settings` (`setting_key`, `setting_value`, `description`) VALUES
('interest_rate_3_days', '5.00', 'Zinssatz für 3-Tage-Einlagen (in Prozent)'),
('interest_rate_7_days', '12.00', 'Zinssatz für 7-Tage-Einlagen (in Prozent)'),
('interest_rate_30_days', '25.00', 'Zinssatz für 30-Tage-Einlagen (in Prozent)'),
('early_withdrawal_penalty', '50.00', 'Vorzeitige Abhebung: Strafgebühr in Prozent'),
('min_deposit_amount', '100.00', 'Minimale Einzahlungssumme'),
('referral_bonus_coins', '300.00', 'Bonus Coins für Werber und Geworbenen'),
('referral_commission_rate', '6.00', 'Provisions-Prozentsatz für Werber');

-- ============================================
-- SUPERADMIN Account erstellen
-- ============================================
-- Username: admin
-- Passwort: admin123
-- Email: admin@modernquiz.local
-- Password Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================

INSERT INTO `users` (`username`, `email`, `password_hash`, `is_active`, `is_admin`, `referral_code`, `created_at`) VALUES
('admin', 'admin@modernquiz.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, TRUE, 'ADMIN2024', NOW());

-- User Stats für Admin
INSERT INTO `user_stats` (`user_id`, `coins`, `bonus_coins`, `level`, `total_points`) VALUES
(1, 10000.00, 5000.00, 99, 999999);

-- Referral Stats für Admin
INSERT INTO `referral_stats` (`user_id`) VALUES (1);

-- Bank Account für Admin
INSERT INTO `bank_account_balances` (`user_id`) VALUES (1);

-- ============================================
-- Fertig!
-- ============================================

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================
-- Setup erfolgreich abgeschlossen!
--
-- SUPERADMIN Login-Daten:
-- Username: admin
-- Passwort: admin123
-- Email: admin@modernquiz.local
--
-- Der Admin hat:
-- - 10.000 Coins
-- - 5.000 Bonus Coins
-- - Level 99
-- - 999.999 Punkte
--
-- Es wurden erstellt:
-- - 15 Quiz-Kategorien
-- - 10 Beispiel-Fragen (Allgemeinwissen)
-- - 40 Antworten
-- - 6 Shop-Powerups
-- - Bank-Einstellungen
-- - Alle notwendigen Tabellen
-- ============================================

-- SQL script to add potentially missing columns to users table
-- Run this if you want full functionality (bonus coins, referral system, etc.)

-- Add bonus_coins column if it doesn't exist
ALTER TABLE users
ADD COLUMN IF NOT EXISTS bonus_coins INT DEFAULT 0 AFTER coins;

-- Add referral_code column if it doesn't exist
ALTER TABLE users
ADD COLUMN IF NOT EXISTS referral_code VARCHAR(20) UNIQUE AFTER avatar,
ADD INDEX idx_referral_code (referral_code);

-- Add last_login column if it doesn't exist
ALTER TABLE users
ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL DEFAULT NULL AFTER created_at;

-- Optional: Add referred_by column for referral system
ALTER TABLE users
ADD COLUMN IF NOT EXISTS referred_by INT NULL DEFAULT NULL AFTER referral_code,
ADD CONSTRAINT fk_referred_by FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL;

-- Generate referral codes for existing users without one
UPDATE users
SET referral_code = CONCAT(UPPER(SUBSTRING(username, 1, 3)), LPAD(id, 5, '0'))
WHERE referral_code IS NULL OR referral_code = '';

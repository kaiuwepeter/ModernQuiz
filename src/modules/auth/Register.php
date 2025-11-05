<?php

namespace ModernQuiz\Modules\Auth;

use ModernQuiz\Core\Database;
use ModernQuiz\Core\Email\Mailer;
use PDO;

class Register
{
    private Auth $auth;
    private PDO $db;
    private Mailer $mailer;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->db = Database::getInstance()->getConnection();
        $this->mailer = new Mailer();
    }

    /**
     * Register a new user with proper password hashing and email verification
     */
    public function register(string $username, string $email, string $password, ?string $referralCode = null): array
    {
        $response = ['success' => false, 'errors' => []];

        // Validate username
        $username = trim($username);
        if (strlen($username) < 3 || strlen($username) > 30) {
            $response['errors'][] = "Benutzername muss zwischen 3 und 30 Zeichen lang sein.";
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $response['errors'][] = "Benutzername darf nur Buchstaben, Zahlen und Unterstriche enthalten.";
        }

        // Validate email
        $email = trim(strtolower($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['errors'][] = "Ungültige E-Mail-Adresse.";
        }

        // Validate password
        $passwordErrors = $this->auth->validatePassword($password);
        if (!empty($passwordErrors)) {
            $response['errors'] = array_merge($response['errors'], $passwordErrors);
        }

        // Check if user already exists
        $existingCheck = $this->userExists($username, $email);
        if ($existingCheck['username']) {
            $response['errors'][] = "Benutzername bereits vergeben.";
        }
        if ($existingCheck['email']) {
            $response['errors'][] = "E-Mail-Adresse bereits registriert.";
        }

        // Validate referral code if provided
        $referredBy = null;
        if ($referralCode) {
            $referredBy = $this->validateReferralCode($referralCode);
            if (!$referredBy) {
                $response['errors'][] = "Ungültiger Empfehlungscode.";
            }
        }

        // If validation failed, return errors
        if (!empty($response['errors'])) {
            return $response;
        }

        try {
            $this->db->beginTransaction();

            // Hash password using bcrypt
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));

            // Generate unique referral code for new user
            $newReferralCode = $this->generateUniqueReferralCode();

            // Insert user into database
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    username,
                    email,
                    password_hash,
                    email_verified,
                    verification_token,
                    referred_by,
                    referral_code,
                    coins,
                    points,
                    level,
                    created_at
                ) VALUES (?, ?, ?, FALSE, ?, ?, ?, 100, 0, 1, NOW())
            ");

            $stmt->execute([
                $username,
                $email,
                $passwordHash,
                $verificationToken,
                $referredBy,
                $newReferralCode
            ]);

            $userId = (int)$this->db->lastInsertId();

            // Initialize user stats
            $stmt = $this->db->prepare("
                INSERT INTO user_stats (user_id, total_points, quizzes_played, correct_answers, wrong_answers)
                VALUES (?, 0, 0, 0, 0)
            ");
            $stmt->execute([$userId]);

            // Process referral bonus if applicable
            if ($referredBy) {
                $this->processReferralBonus($referredBy, $userId);
            }

            $this->db->commit();

            // Send verification email
            $verificationLink = $_ENV['APP_URL'] . "/verify-email?token=" . $verificationToken;

            $this->mailer->sendTemplate(
                $email,
                'Willkommen bei ModernQuiz - E-Mail bestätigen',
                'welcome',
                [
                    'username' => $username,
                    'verification_link' => $verificationLink
                ]
            );

            $response['success'] = true;
            $response['message'] = 'Registrierung erfolgreich! Bitte überprüfe deine E-Mails zur Verifizierung.';
            $response['user_id'] = $userId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            $response['errors'][] = "Registrierung fehlgeschlagen. Bitte versuche es später erneut.";
            error_log("Registration error: " . $e->getMessage());
        }

        return $response;
    }

    /**
     * Check if username or email already exists
     */
    private function userExists(string $username, string $email): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) FILTER (WHERE username = ?) as username_exists,
                COUNT(*) FILTER (WHERE email = ?) as email_exists
            FROM users
        ");
        $stmt->execute([$username, $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'username' => (bool)$result['username_exists'],
            'email' => (bool)$result['email_exists']
        ];
    }

    /**
     * Validate referral code and return referrer user ID
     */
    private function validateReferralCode(string $code): ?int
    {
        $stmt = $this->db->prepare("
            SELECT id FROM users WHERE referral_code = ? LIMIT 1
        ");
        $stmt->execute([$code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['id'] : null;
    }

    /**
     * Generate unique referral code for user
     */
    private function generateUniqueReferralCode(): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8));

            $stmt = $this->db->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->execute([$code]);

            if (!$stmt->fetch()) {
                return $code;
            }

            $attempt++;
        } while ($attempt < $maxAttempts);

        // Fallback: use random_bytes if collision persists
        return strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Process referral bonus for both referrer and new user
     */
    private function processReferralBonus(int $referrerId, int $newUserId): void
    {
        $referrerBonus = (int)($_ENV['REFERRAL_BONUS_FOR_REFERRER'] ?? 50);
        $newUserBonus = (int)($_ENV['REFERRAL_BONUS_POINTS'] ?? 100);

        // Award bonus to referrer
        $stmt = $this->db->prepare("
            UPDATE users
            SET coins = coins + ?,
                referral_count = referral_count + 1
            WHERE id = ?
        ");
        $stmt->execute([$referrerBonus, $referrerId]);

        // Award bonus to new user (already has 100 coins from INSERT, add bonus)
        $stmt = $this->db->prepare("
            UPDATE users
            SET coins = coins + ?
            WHERE id = ?
        ");
        $stmt->execute([$newUserBonus, $newUserId]);

        // Send notification to referrer
        $this->mailer->sendTemplate(
            $this->getUserEmail($referrerId),
            'Neuer Referral-Bonus erhalten!',
            'referral_success',
            ['bonus' => $referrerBonus]
        );
    }

    /**
     * Get user email by ID
     */
    private function getUserEmail(int $userId): string
    {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['email'] ?? '';
    }

    /**
     * Verify email with token
     */
    public function verifyEmail(string $token): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET email_verified = TRUE,
                verification_token = NULL
            WHERE verification_token = ?
            AND email_verified = FALSE
        ");
        $stmt->execute([$token]);

        return $stmt->rowCount() > 0;
    }
}
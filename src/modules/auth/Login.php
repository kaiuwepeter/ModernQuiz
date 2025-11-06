<?php

namespace ModernQuiz\Modules\Auth;

use ModernQuiz\Core\Database;
use ModernQuiz\Core\SessionManager;
use ModernQuiz\Core\Security;
use PDO;

class Login
{
    private PDO $db;
    private SessionManager $sessionManager;
    private Security $security;
    private int $maxAttempts = 5;
    private int $lockoutTime = 900; // 15 Minuten

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->sessionManager = new SessionManager();
        $this->security = new Security();
    }

    /**
     * Attempt login with credentials
     * Returns session token on success
     */
    public function attemptLogin(string $identifier, string $password): array
    {
        $response = [
            'success' => false,
            'message' => '',
            'session_token' => null,
            'user' => null
        ];

        // Anti-Bruteforce Check
        $clientIp = $this->security->getClientIP();
        if ($this->isIpBlocked($clientIp)) {
            $response['message'] = 'Zu viele Fehlversuche. Bitte warten Sie 15 Minuten.';
            return $response;
        }

        // Validate credentials
        $user = $this->validateCredentials($identifier, $password);

        if (!$user) {
            $this->logFailedAttempt($clientIp, $identifier);
            $response['message'] = 'Ungültige Anmeldedaten.';
            return $response;
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            $response['message'] = 'Bitte bestätige zuerst deine E-Mail-Adresse.';
            return $response;
        }

        // Check if account is active
        if (!$user['is_active']) {
            $response['message'] = 'Dein Account wurde deaktiviert. Bitte kontaktiere den Support.';
            return $response;
        }

        // Create session
        $sessionToken = $this->sessionManager->createSession(
            (int)$user['id'],
            $user['username']
        );

        if (!$sessionToken) {
            $response['message'] = 'Fehler beim Erstellen der Session. Bitte versuche es erneut.';
            return $response;
        }

        // Reset failed attempts on successful login
        $this->resetFailedAttempts($clientIp);

        // Update last login timestamp
        $this->updateLastLogin($user['id']);

        $response['success'] = true;
        $response['message'] = 'Login erfolgreich!';
        $response['session_token'] = $sessionToken;
        $response['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'coins' => (int)$user['coins'],
            'points' => (int)$user['points'],
            'level' => (int)$user['level'],
            'avatar' => $user['avatar']
        ];

        return $response;
    }

    /**
     * Validate user credentials
     */
    private function validateCredentials(string $identifier, string $password): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, username, email, password_hash, email_verified, is_active, coins, points, level, avatar
            FROM users
            WHERE (username = ? OR email = ?)
            LIMIT 1
        ");

        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Check if password needs rehashing (cost updated)
            if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
                $this->updatePasswordHash($user['id'], $password);
            }

            return $user;
        }

        return null;
    }

    /**
     * Check if IP is blocked due to too many failed attempts
     */
    private function isIpBlocked(string $ip): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempt_count
            FROM login_attempts
            WHERE ip_address = ?
            AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");

        $stmt->execute([$ip, $this->lockoutTime]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['attempt_count'] >= $this->maxAttempts;
    }

    /**
     * Log failed login attempt
     */
    private function logFailedAttempt(string $ip, string $identifier): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (ip_address, identifier, attempted_at)
            VALUES (?, ?, NOW())
        ");

        $stmt->execute([$ip, $identifier]);
    }

    /**
     * Reset failed attempts on successful login
     */
    private function resetFailedAttempts(string $ip): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts
            WHERE ip_address = ?
        ");

        $stmt->execute([$ip]);
    }

    /**
     * Update user's last login timestamp
     */
    private function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET last_login = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$userId]);
    }

    /**
     * Update password hash (for rehashing with new cost)
     */
    private function updatePasswordHash(int $userId, string $password): void
    {
        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $this->db->prepare("
            UPDATE users
            SET password_hash = ?
            WHERE id = ?
        ");

        $stmt->execute([$newHash, $userId]);
    }

    /**
     * Logout user by destroying session
     */
    public function logout(string $sessionToken): bool
    {
        return $this->sessionManager->destroySession($sessionToken);
    }

    /**
     * Get current user from session token
     */
    public function getCurrentUser(string $sessionToken): ?array
    {
        $session = $this->sessionManager->validateSession($sessionToken);

        if (!$session) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT id, username, email, coins, points, level, avatar, role
            FROM users
            WHERE id = ?
        ");

        $stmt->execute([$session['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
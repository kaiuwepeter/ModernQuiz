<?php
// src/modules/auth/PasswordReset.php
namespace ModernQuiz\Modules\Auth;

use ModernQuiz\Core\Email\Mailer;
use ModernQuiz\Core\Config;

class PasswordReset {
    private $db;
    private $mailer;
    private $tokenLifetime = 3600; // 1 Stunde

    public function __construct($database, Mailer $mailer) {
        $this->db = $database;
        $this->mailer = $mailer;
    }

    /**
     * Erstellt einen Password-Reset-Token
     */
    public function requestReset(string $email): bool {
        // Finde User
        $stmt = $this->db->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Aus Sicherheitsgründen: Gebe nicht preis, ob Email existiert
            return true;
        }

        // Lösche alte Tokens
        $this->cleanupOldTokens($user['id']);

        // Generiere neuen Token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->tokenLifetime);

        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (user_id, token, expires_at)
             VALUES (?, ?, ?)"
        );

        if ($stmt->execute([$user['id'], $token, $expiresAt])) {
            // Sende Email
            $resetUrl = Config::getInstance()->getAppUrl() . "/reset-password?token={$token}";

            $this->mailer->sendTemplate($email, 'Passwort zurücksetzen', 'password_reset', [
                'username' => $user['username'],
                'resetUrl' => $resetUrl,
                'expiresInMinutes' => $this->tokenLifetime / 60
            ]);

            return true;
        }

        return false;
    }

    /**
     * Validiert einen Reset-Token
     */
    public function validateToken(string $token): ?array {
        $stmt = $this->db->prepare(
            "SELECT pr.*, u.id as user_id, u.email, u.username
             FROM password_resets pr
             JOIN users u ON pr.user_id = u.id
             WHERE pr.token = ?
             AND pr.used = FALSE
             AND pr.expires_at > NOW()"
        );

        $stmt->execute([$token]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Setzt Passwort zurück
     */
    public function resetPassword(string $token, string $newPassword): bool {
        $reset = $this->validateToken($token);

        if (!$reset) {
            return false;
        }

        // Update Passwort
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "UPDATE users SET password_hash = ? WHERE id = ?"
        );

        if ($stmt->execute([$passwordHash, $reset['user_id']])) {
            // Markiere Token als benutzt
            $updateStmt = $this->db->prepare(
                "UPDATE password_resets SET used = TRUE WHERE id = ?"
            );
            $updateStmt->execute([$reset['id']]);

            // Lösche alle Sessions des Users
            $sessionStmt = $this->db->prepare(
                "DELETE FROM sessions WHERE user_id = ?"
            );
            $sessionStmt->execute([$reset['user_id']]);

            return true;
        }

        return false;
    }

    /**
     * Cleanup alte Tokens
     */
    private function cleanupOldTokens(int $userId): void {
        $stmt = $this->db->prepare(
            "DELETE FROM password_resets WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
    }
}

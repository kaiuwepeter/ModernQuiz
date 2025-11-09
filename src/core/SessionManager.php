<?php
// src/core/SessionManager.php
namespace ModernQuiz\Core;

class SessionManager {
    private $db;
    private $sessionLifetime = 3600; // 1 Stunde
    private $maxSessionsPerUser = 5;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Erstellt eine neue Session für einen Benutzer
     */
    public function createSession(int $userId, array $options = []): ?string {
        // Cleanup alte Sessions
        $this->cleanupExpiredSessions();
        $this->enforceSessionLimit($userId);

        $sessionId = $this->generateSecureSessionId();
        $deviceHash = $this->generateDeviceHash();
        $ipAddress = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Calculate expiration time
        $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);

        $stmt = $this->db->prepare(
            "INSERT INTO sessions (user_id, session_token, device_hash, ip_address, user_agent, created_at, expires_at, last_activity)
             VALUES (?, ?, ?, ?, ?, NOW(), ?, NOW())"
        );

        if ($stmt->execute([$userId, $sessionId, $deviceHash, $ipAddress, $userAgent, $expiresAt])) {
            // Setze Session-Cookie
            $this->setSessionCookie($sessionId);
            return $sessionId;
        }

        return null;
    }

    /**
     * Validiert eine bestehende Session
     */
    public function validateSession(string $sessionId): ?array {
        $stmt = $this->db->prepare(
            "SELECT s.*, u.username, u.email
             FROM sessions s
             JOIN users u ON s.user_id = u.id
             WHERE s.session_token = ?
             AND s.last_activity > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );

        $stmt->execute([$sessionId, $this->sessionLifetime]);
        $session = $stmt->fetch();

        if ($session) {
            // Prüfe Device-Hash (optional für zusätzliche Sicherheit)
            $currentDeviceHash = $this->generateDeviceHash();

            // Update last_activity
            $this->updateActivity($sessionId);

            return [
                'user_id' => $session['user_id'],
                'username' => $session['username'],
                'email' => $session['email'],
                'session_token' => $session['session_token'],
                'device_hash' => $session['device_hash']
            ];
        }

        return null;
    }

    /**
     * Aktualisiert die letzte Aktivität einer Session
     */
    public function updateActivity(string $sessionId): bool {
        $stmt = $this->db->prepare(
            "UPDATE sessions SET last_activity = NOW() WHERE session_token = ?"
        );
        return $stmt->execute([$sessionId]);
    }

    /**
     * Beendet eine Session
     */
    public function destroySession(string $sessionId): bool {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE session_token = ?");

        if ($stmt->execute([$sessionId])) {
            // Lösche Cookie
            setcookie('MODERNQUIZ_SESSION', '', time() - 3600, '/', '', true, true);
            return true;
        }

        return false;
    }

    /**
     * Beendet alle Sessions eines Benutzers (z.B. bei Passwort-Änderung)
     */
    public function destroyAllUserSessions(int $userId, ?string $exceptSessionId = null): bool {
        if ($exceptSessionId) {
            $stmt = $this->db->prepare(
                "DELETE FROM sessions WHERE user_id = ? AND session_token != ?"
            );
            return $stmt->execute([$userId, $exceptSessionId]);
        } else {
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE user_id = ?");
            return $stmt->execute([$userId]);
        }
    }

    /**
     * Gibt alle aktiven Sessions eines Benutzers zurück
     */
    public function getUserSessions(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT session_token, ip_address, user_agent, created_at, last_activity
             FROM sessions
             WHERE user_id = ?
             ORDER BY last_activity DESC"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Cleanup abgelaufener Sessions
     */
    public function cleanupExpiredSessions(): int {
        $stmt = $this->db->prepare(
            "DELETE FROM sessions
             WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );

        $stmt->execute([$this->sessionLifetime]);
        return $stmt->rowCount();
    }

    /**
     * Limitiert die Anzahl der Sessions pro Benutzer
     */
    private function enforceSessionLimit(int $userId): void {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM sessions WHERE user_id = ?"
        );

        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        if ($result['count'] >= $this->maxSessionsPerUser) {
            // Lösche die älteste Session
            $deleteStmt = $this->db->prepare(
                "DELETE FROM sessions
                 WHERE user_id = ?
                 ORDER BY last_activity ASC
                 LIMIT 1"
            );
            $deleteStmt->execute([$userId]);
        }
    }

    /**
     * Generiert eine sichere Session-ID
     */
    private function generateSecureSessionId(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generiert einen Device-Hash für zusätzliche Sicherheit
     */
    public function generateDeviceHash(): string {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';

        return hash('sha256', $userAgent . $acceptLanguage . $acceptEncoding);
    }

    /**
     * Ermittelt die Client-IP-Adresse
     */
    private function getClientIp(): string {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Bei X-Forwarded-For nimm die erste IP
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Setzt das Session-Cookie
     */
    private function setSessionCookie(string $sessionId): void {
        setcookie(
            'MODERNQUIZ_SESSION',
            $sessionId,
            [
                'expires' => time() + $this->sessionLifetime,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Session-Lifetime setzen
     */
    public function setSessionLifetime(int $seconds): void {
        $this->sessionLifetime = $seconds;
    }

    /**
     * Max Sessions pro User setzen
     */
    public function setMaxSessionsPerUser(int $max): void {
        $this->maxSessionsPerUser = $max;
    }
}

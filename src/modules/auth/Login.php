<?php
// src/modules/auth/Login.php
namespace ModernQuiz\Modules\Auth;

class Login {
    private $db;
    private $security;
    private $maxAttempts = 5;
    private $lockoutTime = 900; // 15 Minuten

    public function __construct($database, $security) {
        $this->db = $database;
        $this->security = $security;
    }

    public function attemptLogin($identifier, $password): array {
        $response = [
            'success' => false,
            'message' => '',
            'needsTwoFactor' => false
        ];

        // Anti-Bruteforce Check
        if ($this->isIpBlocked($_SERVER['REMOTE_ADDR'])) {
            $response['message'] = 'Zu viele Versuche. Bitte warten Sie 15 Minuten.';
            return $response;
        }

        // Device Fingerprinting
        $deviceHash = $this->security->generateDeviceHash();
        
        // Validiere Login
        $user = $this->validateCredentials($identifier, $password);
        
        if ($user) {
            if ($this->isKnownDevice($user['id'], $deviceHash)) {
                $this->createSession($user);
                $response['success'] = true;
            } else {
                $this->sendTwoFactorCode($user['email']);
                $response['needsTwoFactor'] = true;
            }
        } else {
            $this->logFailedAttempt($_SERVER['REMOTE_ADDR']);
        }

        return $response;
    }

    private function validateCredentials($identifier, $password): ?array {
        // SQL-Injection Prevention durch Prepared Statements
        $stmt = $this->db->prepare("
            SELECT id, email, password_hash, two_factor_enabled 
            FROM users 
            WHERE (username = ? OR email = ?) AND is_active = 1
        ");
        
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return null;
    }

    private function isKnownDevice($userId, $deviceHash): bool {
        $stmt = $this->db->prepare("
            SELECT id FROM known_devices 
            WHERE user_id = ? AND device_hash = ? AND last_seen > DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $stmt->bind_param("is", $userId, $deviceHash);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function createSession($user): void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login_time'] = time();
        $_SESSION['fingerprint'] = $this->security->generateSessionFingerprint();
    }
}
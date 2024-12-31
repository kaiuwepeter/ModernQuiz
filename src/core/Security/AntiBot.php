<?php
// src/core/Security/AntiBot.php
namespace ModernQuiz\Core\Security;

class AntiBot {
    private $db;
    private $scoreThreshold = 70;  // Score unter dem ein CAPTCHA gezeigt wird

    public function __construct($database) {
        $this->db = $database;
    }

    public function analyzeBehavior(): array {
        $score = 100;
        $needsCaptcha = false;
        
        // Prüfe Browser-Fingerprint
        if (!$this->hasValidBrowserProfile()) {
            $score -= 30;
        }

        // Prüfe Benutzerverhalten
        if ($this->hasUnnaturalTiming()) {
            $score -= 25;
        }

        // Prüfe IP-Reputation
        if ($this->hasSupiciousIPActivity()) {
            $score -= 20;
        }

        // Honeypot Check
        if ($this->checkHoneypotFields()) {
            $score = 0;
        }

        $needsCaptcha = $score < $this->scoreThreshold;

        return [
            'score' => $score,
            'needsCaptcha' => $needsCaptcha
        ];
    }

    private function hasValidBrowserProfile(): bool {
        $headers = getallheaders();
        
        // Prüfe grundlegende Browser-Header
        if (!isset($headers['User-Agent']) || 
            !isset($headers['Accept-Language']) || 
            !isset($headers['Accept'])) {
            return false;
        }

        // Prüfe JavaScript-Ausführung
        if (!isset($_COOKIE['js_check'])) {
            return false;
        }

        return true;
    }

    private function hasUnnaturalTiming(): bool {
        if (!isset($_SESSION['last_action_time'])) {
            $_SESSION['last_action_time'] = time();
            return false;
        }

        $timeDiff = time() - $_SESSION['last_action_time'];
        $_SESSION['last_action_time'] = time();

        // Wenn Aktionen zu schnell aufeinander folgen (< 1 Sekunde)
        return $timeDiff < 1;
    }

    private function hasSupiciousIPActivity(): bool {
        $ip = $_SERVER['REMOTE_ADDR'];
        $timeWindow = 3600; // 1 Stunde
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as request_count 
            FROM request_log 
            WHERE ip_address = ? 
            AND request_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        
        $stmt->bind_param("si", $ip, $timeWindow);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // Mehr als 1000 Requests pro Stunde sind verdächtig
        return $result['request_count'] > 1000;
    }

    private function checkHoneypotFields(): bool {
        // Prüfe versteckte Honeypot-Felder
        return !empty($_POST['website']) || !empty($_POST['email2']);
    }
}
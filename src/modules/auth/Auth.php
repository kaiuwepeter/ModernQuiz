<?php
// src/modules/auth/Auth.php
namespace ModernQuiz\Modules\Auth;

class Auth {
    private $db;
    private $security;

    public function __construct($database, $security) {
        $this->db = $database;
        $this->security = $security;
    }

    public function validatePassword($password): array {
        $errors = [];
        if (strlen($password) < 10 || strlen($password) > 30) {
            $errors[] = "Passwort muss zwischen 10 und 30 Zeichen lang sein.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Passwort muss mindestens einen Großbuchstaben enthalten.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Passwort muss mindestens einen Kleinbuchstaben enthalten.";
        }
        if (!preg_match('/[\W]{2,}/', $password)) {
            $errors[] = "Passwort muss mindestens zwei Sonderzeichen enthalten.";
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = "Passwort muss mindestens eine Zahl enthalten.";
        }
        return $errors;
    }

    public function generateVerificationTicket(): string {
        return bin2hex(random_bytes(32));
    }
}
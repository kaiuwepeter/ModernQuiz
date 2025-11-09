<?php

namespace ModernQuiz\Core;

class Security {
    public function generateDeviceHash(): string {
        $data = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $this->getClientIP(),
            // Browser-Fingerprinting
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
            $_SERVER['HTTP_ACCEPT'] ?? ''
        ];
        
        return hash('sha256', implode('|', $data));
    }

    public function generateSessionFingerprint(): string {
        return hash('sha256', 
            session_id() . 
            ($_SERVER['HTTP_USER_AGENT'] ?? '') . 
            $this->getClientIP()
        );
    }

    public function getClientIP(): string {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }
}
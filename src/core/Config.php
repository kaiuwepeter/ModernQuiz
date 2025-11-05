<?php
// src/core/Config.php
namespace ModernQuiz\Core;

class Config {
    private static $instance = null;
    private $config = [];
    private $envPath;

    private function __construct(string $envPath = null) {
        $this->envPath = $envPath ?? dirname(__DIR__, 2) . '/.env';
        $this->loadEnv();
    }

    public static function getInstance(string $envPath = null): self {
        if (self::$instance === null) {
            self::$instance = new self($envPath);
        }
        return self::$instance;
    }

    private function loadEnv(): void {
        if (!file_exists($this->envPath)) {
            // Fallback zu .env.example wenn .env nicht existiert
            $examplePath = dirname(__DIR__, 2) . '/.env.example';
            if (file_exists($examplePath)) {
                throw new \RuntimeException(
                    ".env file not found. Please copy .env.example to .env and configure it."
                );
            }
            throw new \RuntimeException(".env file not found at: " . $this->envPath);
        }

        $lines = file($this->envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes
                $value = trim($value, '"\'');

                // Set as environment variable
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }

                $this->config[$key] = $value;
            }
        }
    }

    public function get(string $key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, $value): void {
        $this->config[$key] = $value;
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }

    public function has(string $key): bool {
        return isset($this->config[$key]);
    }

    public function all(): array {
        return $this->config;
    }

    // Helper methods for common config values
    public function getDbConfig(): array {
        return [
            'host' => $this->get('DB_HOST', 'localhost'),
            'port' => $this->get('DB_PORT', 3306),
            'dbname' => $this->get('DB_NAME', 'modernquiz'),
            'username' => $this->get('DB_USER', 'root'),
            'password' => $this->get('DB_PASS', ''),
            'charset' => $this->get('DB_CHARSET', 'utf8mb4')
        ];
    }

    public function getMailConfig(): array {
        return [
            'driver' => $this->get('MAIL_DRIVER', 'smtp'),
            'host' => $this->get('MAIL_HOST'),
            'port' => $this->get('MAIL_PORT', 587),
            'username' => $this->get('MAIL_USERNAME'),
            'password' => $this->get('MAIL_PASSWORD'),
            'encryption' => $this->get('MAIL_ENCRYPTION', 'tls'),
            'from' => [
                'address' => $this->get('MAIL_FROM_ADDRESS', 'noreply@modernquiz.com'),
                'name' => $this->get('MAIL_FROM_NAME', 'ModernQuiz')
            ]
        ];
    }

    public function isDebug(): bool {
        return $this->get('APP_DEBUG', 'false') === 'true';
    }

    public function isProduction(): bool {
        return $this->get('APP_ENV', 'development') === 'production';
    }

    public function getAppUrl(): string {
        return rtrim($this->get('APP_URL', 'http://localhost'), '/');
    }

    public function getAppName(): string {
        return $this->get('APP_NAME', 'ModernQuiz');
    }

    public function generateAppKey(): string {
        $key = bin2hex(random_bytes(32));
        $this->set('APP_KEY', $key);
        return $key;
    }
}

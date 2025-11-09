<?php

namespace ModernQuiz\Core;

use ModernQuiz\Core\SessionManager;

/**
 * Authentication Middleware
 * Validates session tokens and enforces authentication on API endpoints
 */
class AuthMiddleware
{
    private SessionManager $sessionManager;
    private array $publicRoutes = [
        '/api/auth/login',
        '/api/auth/register',
        '/api/auth/forgot-password',
        '/api/auth/reset-password',
        '/api/quiz/categories',  // Public category list
        '/api/health',  // Health check endpoint
        '/api/test.php',  // Debug endpoints
        '/api/debug-login.php',
        '/api/test-root.php',
        '/api/check-admin.php',  // Admin user check endpoint
        '/api/fix-admin-user.php',  // Admin user fix endpoint
        '/api/reset-admin-password.php',  // Admin password reset endpoint
    ];

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->sessionManager = new SessionManager($db);
    }

    /**
     * Verify authentication for the current request
     * Returns authenticated user ID or throws exception
     */
    public function authenticate(): int
    {
        // Get session token from header or cookie
        $sessionToken = $this->getSessionToken();

        if (!$sessionToken) {
            http_response_code(401);
            throw new \Exception('Authentication required - no session token provided');
        }

        // Validate session
        $session = $this->sessionManager->validateSession($sessionToken);

        if (!$session) {
            http_response_code(401);
            throw new \Exception('Invalid or expired session');
        }

        // Verify device fingerprint
        $currentDeviceHash = $this->sessionManager->generateDeviceHash();
        if ($session['device_hash'] !== $currentDeviceHash) {
            // Device mismatch - potential session hijacking
            $this->sessionManager->destroySession($sessionToken);
            http_response_code(401);
            throw new \Exception('Session device mismatch - please login again');
        }

        // Update session activity
        $this->sessionManager->updateActivity($session['id']);

        return (int)$session['user_id'];
    }

    /**
     * Optional authentication - returns user ID if authenticated, null otherwise
     */
    public function optionalAuth(): ?int
    {
        try {
            return $this->authenticate();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if route is public (no authentication required)
     */
    private function isPublicRoute(string $path): bool
    {
        foreach ($this->publicRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get session token from Authorization header or cookie
     */
    private function getSessionToken(): ?string
    {
        // Check Authorization header first (Bearer token)
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        // Fallback to cookie
        return $_COOKIE['session_token'] ?? null;
    }

    /**
     * Verify user has admin role
     */
    public function requireAdmin(int $userId): void
    {
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("
            SELECT role FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            throw new \Exception('Admin access required');
        }
    }

    /**
     * Verify user is accessing their own resource
     */
    public function requireSelfOrAdmin(int $authenticatedUserId, int $requestedUserId): void
    {
        if ($authenticatedUserId === $requestedUserId) {
            return; // User accessing own resource
        }

        // Check if user is admin
        try {
            $this->requireAdmin($authenticatedUserId);
        } catch (\Exception $e) {
            http_response_code(403);
            throw new \Exception('Access denied - you can only access your own resources');
        }
    }
}

<?php
// src/modules/admin/AdminController.php
namespace ModernQuiz\Modules\Admin;

class AdminController {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    // ===== BENUTZER-VERWALTUNG =====

    /**
     * Holt alle Benutzer mit Pagination
     */
    public function listUsers(array $filters = [], int $limit = 50, int $offset = 0): array {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(username LIKE ? OR email LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = $filters['is_active'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare(
            "SELECT u.*, us.total_quizzes_played, us.total_points
             FROM users u
             LEFT JOIN user_stats us ON u.id = us.user_id
             $whereClause
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?"
        );

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Deaktiviert/Aktiviert einen Benutzer
     */
    public function toggleUserStatus(int $userId, int $adminId, bool $active): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET is_active = ? WHERE id = ?"
        );

        if ($stmt->execute([$active, $userId])) {
            $this->logAction($adminId, $active ? 'activate_user' : 'deactivate_user', 'user', $userId);
            return true;
        }

        return false;
    }

    /**
     * Löscht einen Benutzer
     */
    public function deleteUser(int $userId, int $adminId, string $reason): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");

        if ($stmt->execute([$userId])) {
            $this->logAction($adminId, 'delete_user', 'user', $userId, ['reason' => $reason]);
            return true;
        }

        return false;
    }

    /**
     * Bannt einen Benutzer
     */
    public function banUser(int $userId, int $adminId, string $reason, ?int $durationHours = null): bool {
        $isPermanent = ($durationHours === null);
        $bannedUntil = $isPermanent ? null : date('Y-m-d H:i:s', time() + ($durationHours * 3600));

        $stmt = $this->db->prepare(
            "INSERT INTO banned_users (user_id, banned_by, reason, banned_until, is_permanent)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([$userId, $adminId, $reason, $bannedUntil, $isPermanent])) {
            // Deaktiviere den User
            $this->toggleUserStatus($userId, $adminId, false);
            $this->logAction($adminId, 'ban_user', 'user', $userId, [
                'reason' => $reason,
                'duration' => $durationHours,
                'permanent' => $isPermanent
            ]);
            return true;
        }

        return false;
    }

    /**
     * Entbannt einen Benutzer
     */
    public function unbanUser(int $userId, int $adminId): bool {
        $stmt = $this->db->prepare("DELETE FROM banned_users WHERE user_id = ?");

        if ($stmt->execute([$userId])) {
            $this->toggleUserStatus($userId, $adminId, true);
            $this->logAction($adminId, 'unban_user', 'user', $userId);
            return true;
        }

        return false;
    }

    // ===== ROLLEN-VERWALTUNG =====

    /**
     * Erstellt eine neue Rolle
     */
    public function createRole(string $name, string $description, array $permissions): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO user_roles (name, description, permissions)
             VALUES (?, ?, ?)"
        );

        if ($stmt->execute([$name, $description, json_encode($permissions)])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Weist einem User eine Rolle zu
     */
    public function assignRole(int $userId, int $roleId, int $adminId): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO user_role_assignments (user_id, role_id, assigned_by)
             VALUES (?, ?, ?)"
        );

        if ($stmt->execute([$userId, $roleId, $adminId])) {
            $this->logAction($adminId, 'assign_role', 'user', $userId, ['role_id' => $roleId]);
            return true;
        }

        return false;
    }

    /**
     * Entfernt eine Rolle von einem User
     */
    public function removeRole(int $userId, int $roleId, int $adminId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM user_role_assignments WHERE user_id = ? AND role_id = ?"
        );

        if ($stmt->execute([$userId, $roleId])) {
            $this->logAction($adminId, 'remove_role', 'user', $userId, ['role_id' => $roleId]);
            return true;
        }

        return false;
    }

    /**
     * Holt Rollen eines Users
     */
    public function getUserRoles(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, ura.assigned_at
             FROM user_role_assignments ura
             JOIN user_roles r ON ura.role_id = r.id
             WHERE ura.user_id = ?"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Prüft ob ein User eine bestimmte Berechtigung hat
     */
    public function hasPermission(int $userId, string $permission): bool {
        $roles = $this->getUserRoles($userId);

        foreach ($roles as $role) {
            $permissions = json_decode($role['permissions'], true);
            if (in_array($permission, $permissions) || in_array('*', $permissions)) {
                return true;
            }
        }

        return false;
    }

    // ===== QUIZ-VERWALTUNG =====

    /**
     * Holt alle Quizze für Moderation
     */
    public function listAllQuizzes(array $filters = [], int $limit = 50, int $offset = 0): array {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = $filters['is_active'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare(
            "SELECT q.*, u.username as creator_name,
                    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count,
                    (SELECT COUNT(*) FROM reports WHERE reported_type = 'quiz' AND reported_id = q.id AND status = 'pending') as report_count
             FROM quizzes q
             JOIN users u ON q.created_by = u.id
             $whereClause
             ORDER BY q.created_at DESC
             LIMIT ? OFFSET ?"
        );

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Deaktiviert/Aktiviert ein Quiz
     */
    public function toggleQuizStatus(int $quizId, int $adminId, bool $active): bool {
        $stmt = $this->db->prepare(
            "UPDATE quizzes SET is_active = ? WHERE id = ?"
        );

        if ($stmt->execute([$active, $quizId])) {
            $this->logAction($adminId, $active ? 'activate_quiz' : 'deactivate_quiz', 'quiz', $quizId);
            return true;
        }

        return false;
    }

    /**
     * Löscht ein Quiz
     */
    public function deleteQuiz(int $quizId, int $adminId, string $reason): bool {
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE id = ?");

        if ($stmt->execute([$quizId])) {
            $this->logAction($adminId, 'delete_quiz', 'quiz', $quizId, ['reason' => $reason]);
            return true;
        }

        return false;
    }

    // ===== REPORTS VERWALTUNG =====

    /**
     * Erstellt einen Report
     */
    public function createReport(int $reporterId, string $type, int $targetId, string $reason, ?string $description = null): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO reports (reporter_id, reported_type, reported_id, reason, description)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([$reporterId, $type, $targetId, $reason, $description])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Holt alle Reports
     */
    public function listReports(string $status = 'pending', int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.username as reporter_name
             FROM reports r
             JOIN users u ON r.reporter_id = u.id
             WHERE r.status = ?
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?"
        );

        $stmt->execute([$status, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Bearbeitet einen Report
     */
    public function reviewReport(int $reportId, int $adminId, string $status, ?string $resolution = null): bool {
        $stmt = $this->db->prepare(
            "UPDATE reports
             SET status = ?, reviewed_by = ?, resolution = ?, reviewed_at = NOW()
             WHERE id = ?"
        );

        if ($stmt->execute([$status, $adminId, $resolution, $reportId])) {
            $this->logAction($adminId, 'review_report', 'report', $reportId, [
                'status' => $status,
                'resolution' => $resolution
            ]);
            return true;
        }

        return false;
    }

    // ===== SYSTEM-EINSTELLUNGEN =====

    /**
     * Setzt eine System-Einstellung
     */
    public function setSetting(string $key, $value, string $type, int $adminId, ?string $description = null): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO system_settings (setting_key, setting_value, setting_type, description, updated_by)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?, updated_at = NOW()"
        );

        if ($stmt->execute([$key, $value, $type, $description, $adminId, $value, $adminId])) {
            $this->logAction($adminId, 'update_setting', 'setting', null, ['key' => $key, 'value' => $value]);
            return true;
        }

        return false;
    }

    /**
     * Holt eine System-Einstellung
     */
    public function getSetting(string $key) {
        $stmt = $this->db->prepare(
            "SELECT setting_value, setting_type FROM system_settings WHERE setting_key = ?"
        );

        $stmt->execute([$key]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        // Type casting
        switch ($result['setting_type']) {
            case 'number':
                return (int)$result['setting_value'];
            case 'boolean':
                return (bool)$result['setting_value'];
            case 'json':
                return json_decode($result['setting_value'], true);
            default:
                return $result['setting_value'];
        }
    }

    /**
     * Holt alle System-Einstellungen
     */
    public function getAllSettings(): array {
        $stmt = $this->db->prepare("SELECT * FROM system_settings");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ===== STATISTIKEN =====

    /**
     * Holt Dashboard-Statistiken
     */
    public function getDashboardStats(): array {
        $stats = [];

        // User Stats
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];

        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE");
        $stats['active_users'] = $stmt->fetch()['count'];

        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['new_users_week'] = $stmt->fetch()['count'];

        // Quiz Stats
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM quizzes");
        $stats['total_quizzes'] = $stmt->fetch()['count'];

        $stmt = $this->db->query("SELECT COUNT(*) as count FROM quizzes WHERE is_active = TRUE");
        $stats['active_quizzes'] = $stmt->fetch()['count'];

        // Game Stats
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM game_rooms");
        $stats['total_games'] = $stmt->fetch()['count'];

        $stmt = $this->db->query("SELECT COUNT(*) as count FROM game_rooms WHERE status = 'in_progress'");
        $stats['active_games'] = $stmt->fetch()['count'];

        // Report Stats
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM reports WHERE status = 'pending'");
        $stats['pending_reports'] = $stmt->fetch()['count'];

        return $stats;
    }

    // ===== ADMIN-LOGGING =====

    /**
     * Loggt eine Admin-Aktion
     */
    private function logAction(int $adminId, string $action, ?string $targetType = null, ?int $targetId = null, ?array $details = null): void {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $stmt = $this->db->prepare(
            "INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $adminId,
            $action,
            $targetType,
            $targetId,
            $details ? json_encode($details) : null,
            $ipAddress
        ]);
    }

    /**
     * Holt Admin-Logs
     */
    public function getAdminLogs(array $filters = [], int $limit = 100, int $offset = 0): array {
        $where = [];
        $params = [];

        if (!empty($filters['admin_id'])) {
            $where[] = "admin_id = ?";
            $params[] = $filters['admin_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = "action = ?";
            $params[] = $filters['action'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare(
            "SELECT al.*, u.username as admin_name
             FROM admin_logs al
             JOIN users u ON al.admin_id = u.id
             $whereClause
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?"
        );

        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

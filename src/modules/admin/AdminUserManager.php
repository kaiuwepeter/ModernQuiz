<?php
// src/modules/admin/AdminUserManager.php

namespace ModernQuiz\Modules\Admin;

/**
 * AdminUserManager - Umfassende User-Verwaltung für Admins
 *
 * Features:
 * - User sperren/entsperren
 * - Email ändern
 * - Passwort zurücksetzen
 * - Kontoauszüge einsehen
 * - User-Details anzeigen
 * - Vollständiges Admin-Action-Logging
 */
class AdminUserManager {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Gibt alle User zurück (mit Filtern)
     */
    public function getAllUsers(array $filters = []): array {
        $where = ['1=1'];
        $params = [];
        $types = '';

        if (isset($filters['search']) && !empty($filters['search'])) {
            $where[] = '(u.username LIKE ? OR u.email LIKE ? OR u.id = ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = (int)$filters['search'];
            $types .= 'ssi';
        }

        if (isset($filters['is_active'])) {
            $where[] = 'u.is_active = ?';
            $params[] = (int)$filters['is_active'];
            $types .= 'i';
        }

        if (isset($filters['role'])) {
            $where[] = 'u.role = ?';
            $params[] = $filters['role'];
            $types .= 's';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                u.id,
                u.username,
                u.email,
                u.is_active,
                u.role,
                u.created_at,
                u.last_login,
                us.coins,
                us.bonus_coins,
                us.total_points,
                us.level,
                (SELECT COUNT(*) FROM bank_deposits WHERE user_id = u.id) as bank_deposits_count,
                (SELECT COUNT(*) FROM voucher_redemptions WHERE user_id = u.id) as vouchers_redeemed_count
            FROM users u
            LEFT JOIN user_stats us ON u.id = us.user_id
            WHERE {$whereClause}
            ORDER BY u.created_at DESC
        ";

        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    /**
     * Gibt User-Details zurück
     */
    public function getUserDetails(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT
                u.*,
                us.coins,
                us.bonus_coins,
                us.total_points,
                us.level,
                us.experience,
                us.total_games,
                us.total_questions_answered,
                us.total_correct_answers,
                us.current_streak,
                us.longest_streak
            FROM users u
            LEFT JOIN user_stats us ON u.id = us.user_id
            WHERE u.id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            return null;
        }

        // Entferne sensitive Daten
        unset($user['password_hash']);
        unset($user['two_factor_secret']);

        // Hole zusätzliche Statistiken
        $user['bank_deposits'] = $this->getUserBankStats($userId);
        $user['recent_activity'] = $this->getUserRecentActivity($userId);

        return $user;
    }

    /**
     * Sperrt einen User
     */
    public function lockUser(int $userId, int $adminId, string $reason): array {
        try {
            $this->db->begin_transaction();

            // Prüfe ob User existiert
            $user = $this->getUserById($userId);
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User nicht gefunden'];
            }

            // Prüfe ob bereits gesperrt
            if (!$user['is_active']) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User ist bereits gesperrt'];
            }

            // Sperre User
            $stmt = $this->db->prepare("
                UPDATE users
                SET is_active = 0
                WHERE id = ?
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();

            // Beende alle aktiven Sessions
            $stmt = $this->db->prepare("
                UPDATE sessions
                SET expires_at = NOW()
                WHERE user_id = ? AND expires_at > NOW()
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();

            // Log Admin Action
            $this->logAdminAction(
                $adminId,
                $userId,
                'user_lock',
                "User gesperrt. Grund: {$reason}",
                ['reason' => $reason],
                (bool)$user['is_active'],
                false
            );

            // Benachrichtige User
            $this->notifyUser($userId, 'account_locked', 'Dein Account wurde gesperrt', $reason);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'User erfolgreich gesperrt'
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("AdminUserManager::lockUser error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Sperren des Users'];
        }
    }

    /**
     * Entsperrt einen User
     */
    public function unlockUser(int $userId, int $adminId): array {
        try {
            $this->db->begin_transaction();

            // Prüfe ob User existiert
            $user = $this->getUserById($userId);
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User nicht gefunden'];
            }

            // Prüfe ob gesperrt
            if ($user['is_active']) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User ist nicht gesperrt'];
            }

            // Entsperre User
            $stmt = $this->db->prepare("
                UPDATE users
                SET is_active = 1
                WHERE id = ?
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();

            // Log Admin Action
            $this->logAdminAction(
                $adminId,
                $userId,
                'user_unlock',
                "User entsperrt",
                null,
                (bool)$user['is_active'],
                true
            );

            // Benachrichtige User
            $this->notifyUser($userId, 'account_unlocked', 'Dein Account wurde entsperrt', 'Du kannst dich wieder einloggen.');

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'User erfolgreich entsperrt'
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("AdminUserManager::unlockUser error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Entsperren des Users'];
        }
    }

    /**
     * Ändert die Email eines Users
     */
    public function changeUserEmail(int $userId, int $adminId, string $newEmail): array {
        // Validiere Email
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Ungültige Email-Adresse'];
        }

        try {
            $this->db->begin_transaction();

            // Prüfe ob User existiert
            $user = $this->getUserById($userId);
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User nicht gefunden'];
            }

            $oldEmail = $user['email'];

            // Prüfe ob Email bereits existiert
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param('si', $newEmail, $userId);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Email-Adresse wird bereits verwendet'];
            }

            // Update Email
            $stmt = $this->db->prepare("
                UPDATE users
                SET email = ?
                WHERE id = ?
            ");
            $stmt->bind_param('si', $newEmail, $userId);
            $stmt->execute();

            // Log Admin Action
            $this->logAdminAction(
                $adminId,
                $userId,
                'user_email_change',
                "Email geändert von {$oldEmail} zu {$newEmail}",
                ['old_email' => $oldEmail, 'new_email' => $newEmail],
                $oldEmail,
                $newEmail
            );

            // Benachrichtige User (an ALTE Email)
            $this->notifyUser($userId, 'email_changed', 'Deine Email-Adresse wurde geändert', "Alte Email: {$oldEmail}\nNeue Email: {$newEmail}");

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Email erfolgreich geändert',
                'old_email' => $oldEmail,
                'new_email' => $newEmail
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("AdminUserManager::changeUserEmail error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Ändern der Email'];
        }
    }

    /**
     * Setzt das Passwort eines Users zurück
     */
    public function changeUserPassword(int $userId, int $adminId, string $newPassword): array {
        // Validiere Passwort
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'Passwort muss mindestens 8 Zeichen lang sein'];
        }

        try {
            $this->db->begin_transaction();

            // Prüfe ob User existiert
            $user = $this->getUserById($userId);
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User nicht gefunden'];
            }

            // Hash Passwort
            $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);

            // Update Passwort
            $stmt = $this->db->prepare("
                UPDATE users
                SET password_hash = ?
                WHERE id = ?
            ");
            $stmt->bind_param('si', $passwordHash, $userId);
            $stmt->execute();

            // Beende alle aktiven Sessions (User muss sich neu einloggen)
            $stmt = $this->db->prepare("
                UPDATE sessions
                SET expires_at = NOW()
                WHERE user_id = ? AND expires_at > NOW()
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();

            // Log Admin Action
            $this->logAdminAction(
                $adminId,
                $userId,
                'user_password_change',
                "Passwort zurückgesetzt",
                null,
                '[PASSWORT]',
                '[NEUES PASSWORT]'
            );

            // Benachrichtige User
            $this->notifyUser($userId, 'password_changed', 'Dein Passwort wurde geändert', 'Dein Passwort wurde von einem Administrator zurückgesetzt. Bitte logge dich mit dem neuen Passwort ein.');

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Passwort erfolgreich geändert'
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("AdminUserManager::changeUserPassword error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Ändern des Passworts'];
        }
    }

    /**
     * Gibt den Bank-Kontoauszug eines Users zurück
     */
    public function getUserBankStatement(int $userId, int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM bank_transactions
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['metadata']) {
                $row['metadata'] = json_decode($row['metadata'], true);
            }
            $transactions[] = $row;
        }

        return $transactions;
    }

    /**
     * Gibt Admin-Aktionen zurück
     */
    public function getAdminActions(array $filters = []): array {
        $where = ['1=1'];
        $params = [];
        $types = '';

        if (isset($filters['admin_user_id'])) {
            $where[] = 'aal.admin_user_id = ?';
            $params[] = $filters['admin_user_id'];
            $types .= 'i';
        }

        if (isset($filters['target_user_id'])) {
            $where[] = 'aal.target_user_id = ?';
            $params[] = $filters['target_user_id'];
            $types .= 'i';
        }

        if (isset($filters['action_type'])) {
            $where[] = 'aal.action_type = ?';
            $params[] = $filters['action_type'];
            $types .= 's';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT
                aal.*,
                u1.username as admin_username,
                u2.username as target_username
            FROM admin_actions_log aal
            LEFT JOIN users u1 ON aal.admin_user_id = u1.id
            LEFT JOIN users u2 ON aal.target_user_id = u2.id
            WHERE {$whereClause}
            ORDER BY aal.created_at DESC
            LIMIT 100
        ";

        if (!empty($params)) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($sql);
        }

        $actions = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['metadata']) {
                $row['metadata'] = json_decode($row['metadata'], true);
            }
            $actions[] = $row;
        }

        return $actions;
    }

    /**
     * Hilfsfunktionen
     */

    private function getUserById(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function getUserBankStats(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_deposits,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_deposits,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_deposits,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_deposits,
                SUM(CASE WHEN status = 'completed' THEN interest_earned ELSE 0 END) as total_interest_earned,
                SUM(CASE WHEN status = 'cancelled' THEN penalty_fee ELSE 0 END) as total_penalties_paid
            FROM bank_deposits
            WHERE user_id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: [];
    }

    private function getUserRecentActivity(int $userId, int $limit = 10): array {
        // Kombiniere verschiedene Aktivitäten
        $activities = [];

        // Bank Transaktionen
        $stmt = $this->db->prepare("
            SELECT 'bank' as type, created_at, transaction_type as action, description
            FROM bank_transactions
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }

        // Coin Transaktionen
        $stmt = $this->db->prepare("
            SELECT 'coin' as type, created_at, transaction_type as action, description
            FROM coin_transactions
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }

        // Sortiere nach Datum
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($activities, 0, $limit);
    }

    private function logAdminAction(
        int $adminId,
        ?int $targetUserId,
        string $actionType,
        string $details,
        ?array $metadata = null,
        $beforeValue = null,
        $afterValue = null
    ): void {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $metadataJson = $metadata ? json_encode($metadata) : null;
        $beforeValueStr = is_scalar($beforeValue) ? (string)$beforeValue : json_encode($beforeValue);
        $afterValueStr = is_scalar($afterValue) ? (string)$afterValue : json_encode($afterValue);

        $stmt = $this->db->prepare("
            INSERT INTO admin_actions_log (
                admin_user_id, target_user_id, action_type,
                action_details, metadata,
                before_value, after_value,
                ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            'iissssss',
            $adminId,
            $targetUserId,
            $actionType,
            $details,
            $metadataJson,
            $beforeValueStr,
            $afterValueStr,
            $ipAddress,
            $userAgent
        );

        $stmt->execute();
    }

    private function notifyUser(int $userId, string $type, string $title, string $message): void {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, type, title, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('isss', $userId, $type, $title, $message);
        $stmt->execute();
    }

    /**
     * NEU: Ändert die Rolle eines Users
     * WICHTIG: User ID 1 kann NIEMALS geändert werden (Super-Admin)
     */
    public function changeUserRole(int $userId, int $adminId, string $newRole): array {
        // SCHUTZ: User ID 1 ist unantastbar
        if ($userId === 1) {
            return ['success' => false, 'error' => 'User ID 1 (Super-Admin) kann nicht geändert werden'];
        }

        // Validiere Rolle
        $validRoles = ['user', 'admin', 'moderator'];
        if (!in_array($newRole, $validRoles)) {
            return ['success' => false, 'error' => 'Ungültige Rolle'];
        }

        try {
            $this->db->begin_transaction();

            $user = $this->getUserById($userId);
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User nicht gefunden'];
            }

            $oldRole = $user['role'];

            $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param('si', $newRole, $userId);
            $stmt->execute();

            $this->logAdminAction(
                $adminId,
                $userId,
                'user_role_change',
                "Rolle geändert von {$oldRole} zu {$newRole}",
                ['old_role' => $oldRole, 'new_role' => $newRole],
                $oldRole,
                $newRole
            );

            $this->notifyUser($userId, 'role_changed', 'Deine Rolle wurde geändert', "Deine Rolle ist jetzt: {$newRole}");

            $this->db->commit();

            return ['success' => true, 'message' => 'Rolle erfolgreich geändert', 'old_role' => $oldRole, 'new_role' => $newRole];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("AdminUserManager::changeUserRole error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Ändern der Rolle'];
        }
    }

    /**
     * NEU: Löscht einen User (GDPR)
     */
    public function deleteUser(int $userId, int $adminId, string $reason): array {
        if ($userId === 1) {
            return ['success' => false, 'error' => 'User ID 1 (Super-Admin) kann nicht gelöscht werden'];
        }

        try {
            $this->db->begin_transaction();

            $user = $this->getUserById($userId);
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User nicht gefunden'];
            }

            // Lösche User (Cascade löscht automatisch zugehörige Daten)
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();

            $this->logAdminAction(
                $adminId,
                null, // User existiert nicht mehr
                'user_delete',
                "User gelöscht: {$user['username']} ({$user['email']}). Grund: {$reason}",
                ['deleted_user_id' => $userId, 'username' => $user['username'], 'email' => $user['email'], 'reason' => $reason],
                "User ID: {$userId}",
                "GELÖSCHT"
            );

            $this->db->commit();

            return ['success' => true, 'message' => 'User erfolgreich gelöscht'];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("AdminUserManager::deleteUser error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Löschen des Users'];
        }
    }

    /**
     * NEU: Beendet alle Sessions eines Users
     */
    public function logoutAllSessions(int $userId, int $adminId): array {
        try {
            $this->db->begin_transaction();

            $user = $this->getUserById($userId);
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User nicht gefunden'];
            }

            $stmt = $this->db->prepare("
                UPDATE sessions
                SET expires_at = NOW()
                WHERE user_id = ? AND expires_at > NOW()
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $affectedSessions = $stmt->affected_rows;

            $this->logAdminAction(
                $adminId,
                $userId,
                'user_logout_all',
                "Alle Sessions beendet ({$affectedSessions} Sessions)",
                ['sessions_terminated' => $affectedSessions]
            );

            $this->notifyUser($userId, 'sessions_terminated', 'Alle Sessions beendet', 'Ein Administrator hat alle deine aktiven Sessions beendet. Bitte logge dich erneut ein.');

            $this->db->commit();

            return ['success' => true, 'message' => "{$affectedSessions} Session(s) beendet"];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("AdminUserManager::logoutAllSessions error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Beenden der Sessions'];
        }
    }

    /**
     * NEU: Setzt 2FA zurück
     */
    public function reset2FA(int $userId, int $adminId): array {
        try {
            $this->db->begin_transaction();

            $user = $this->getUserById($userId);
            if (!$user) {
                $this->db->rollback();
                return ['success' => false, 'error' => 'User nicht gefunden'];
            }

            $stmt = $this->db->prepare("
                UPDATE users
                SET two_factor_enabled = 0,
                    two_factor_secret = NULL
                WHERE id = ?
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();

            $this->logAdminAction(
                $adminId,
                $userId,
                'user_2fa_reset',
                "2FA zurückgesetzt",
                null
            );

            $this->notifyUser($userId, '2fa_reset', '2FA zurückgesetzt', 'Deine Zwei-Faktor-Authentifizierung wurde von einem Administrator zurückgesetzt. Du kannst 2FA in den Einstellungen neu einrichten.');

            $this->db->commit();

            return ['success' => true, 'message' => '2FA erfolgreich zurückgesetzt'];

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("AdminUserManager::reset2FA error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Fehler beim Zurücksetzen von 2FA'];
        }
    }

    /**
     * NEU: Batch-Operation - Mehrere User sperren
     */
    public function batchLockUsers(array $userIds, int $adminId, string $reason): array {
        $locked = 0;
        $failed = 0;
        $errors = [];

        foreach ($userIds as $userId) {
            if ($userId === 1) {
                $errors[] = "User ID 1 übersprungen (Super-Admin)";
                $failed++;
                continue;
            }

            $result = $this->lockUser($userId, $adminId, $reason);
            if ($result['success']) {
                $locked++;
            } else {
                $failed++;
                $errors[] = "User {$userId}: {$result['error']}";
            }
        }

        return [
            'success' => true,
            'locked' => $locked,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * NEU: Batch-Operation - Mehrere User entsperren
     */
    public function batchUnlockUsers(array $userIds, int $adminId): array {
        $unlocked = 0;
        $failed = 0;
        $errors = [];

        foreach ($userIds as $userId) {
            $result = $this->unlockUser($userId, $adminId);
            if ($result['success']) {
                $unlocked++;
            } else {
                $failed++;
                $errors[] = "User {$userId}: {$result['error']}";
            }
        }

        return [
            'success' => true,
            'unlocked' => $unlocked,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * NEU: Dashboard-Statistiken
     */
    public function getDashboardStats(): array {
        $stats = [];

        // User-Statistiken
        $result = $this->db->query("
            SELECT
                COUNT(*) as total_users,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as locked_users,
                SUM(CASE WHEN created_at >= CURDATE() THEN 1 ELSE 0 END) as new_today,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count
            FROM users
        ");
        $stats['users'] = $result->fetch_assoc();

        // Bank-Statistiken
        $result = $this->db->query("
            SELECT
                COUNT(*) as total_deposits,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_deposits,
                SUM(CASE WHEN status = 'matured' THEN 1 ELSE 0 END) as matured_deposits,
                SUM(CASE WHEN status = 'completed' THEN total_payout ELSE 0 END) as total_paid_out,
                SUM(CASE WHEN status = 'completed' THEN interest_earned ELSE 0 END) as total_interest_paid,
                SUM(CASE WHEN status = 'cancelled' THEN penalty_fee ELSE 0 END) as total_penalties_collected
            FROM bank_deposits
        ");
        $stats['bank'] = $result->fetch_assoc();

        // Voucher-Statistiken
        $result = $this->db->query("
            SELECT
                COUNT(*) as total_vouchers,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_vouchers,
                (SELECT COUNT(*) FROM voucher_redemptions) as total_redemptions,
                (SELECT COUNT(*) FROM voucher_fraud_log WHERE created_at >= CURDATE()) as fraud_attempts_today
            FROM vouchers
        ");
        $stats['vouchers'] = $result->fetch_assoc();

        // Referral-Statistiken
        $result = $this->db->query("
            SELECT
                SUM(total_referrals) as total_referrals,
                SUM(total_commission_earned) as total_commission_paid,
                COUNT(*) as users_with_referrals
            FROM referral_stats
            WHERE total_referrals > 0
        ");
        $stats['referrals'] = $result->fetch_assoc();

        // Coin-Statistiken
        $result = $this->db->query("
            SELECT
                SUM(coins) as total_coins,
                SUM(bonus_coins) as total_bonus_coins,
                AVG(coins) as avg_coins_per_user,
                AVG(bonus_coins) as avg_bonus_coins_per_user
            FROM user_stats
        ");
        $stats['coins'] = $result->fetch_assoc();

        // Aktivität heute
        $result = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM sessions WHERE created_at >= CURDATE()) as sessions_today,
                (SELECT COUNT(*) FROM quiz_sessions WHERE started_at >= CURDATE()) as quizzes_today,
                (SELECT COUNT(*) FROM shop_purchases WHERE purchased_at >= CURDATE()) as purchases_today,
                (SELECT COUNT(*) FROM coin_transactions WHERE created_at >= CURDATE()) as transactions_today
        ");
        $stats['activity'] = $result->fetch_assoc();

        return $stats;
    }
}


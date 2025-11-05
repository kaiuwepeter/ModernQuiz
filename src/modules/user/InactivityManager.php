<?php
// src/modules/user/InactivityManager.php
namespace ModernQuiz\Modules\User;

use ModernQuiz\Core\Email\Mailer;
use ModernQuiz\Core\Config;

class InactivityManager {
    private $db;
    private $mailer;
    private $warningDays;
    private $deleteDays;

    public function __construct($database, Mailer $mailer) {
        $this->db = $database;
        $this->mailer = $mailer;
        $this->warningDays = (int)Config::getInstance()->get('USER_INACTIVITY_WARNING_DAYS', 30);
        $this->deleteDays = (int)Config::getInstance()->get('USER_INACTIVITY_DELETE_DAYS', 35);
    }

    /**
     * Prüft inaktive User und sendet Warnungen
     */
    public function checkInactiveUsers(): array {
        $results = [
            'warnings_sent' => 0,
            'users_deleted' => 0
        ];

        // Finde User die eine Warnung bekommen sollten
        $warningUsers = $this->findUsersNeedingWarning();
        foreach ($warningUsers as $user) {
            if ($this->sendInactivityWarning($user)) {
                $results['warnings_sent']++;
            }
        }

        // Finde User die gelöscht werden sollen
        $deleteUsers = $this->findUsersForDeletion();
        foreach ($deleteUsers as $user) {
            if ($this->deleteInactiveUser($user['id'])) {
                $results['users_deleted']++;
            }
        }

        return $results;
    }

    /**
     * Findet User die eine Warnung brauchen
     */
    private function findUsersNeedingWarning(): array {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.email, u.last_login,
                    DATEDIFF(NOW(), COALESCE(u.last_login, u.created_at)) as inactive_days,
                    us.total_quizzes_played, us.total_points, us.total_quizzes_created
             FROM users u
             LEFT JOIN user_stats us ON u.id = us.user_id
             WHERE u.is_active = TRUE
             AND u.inactivity_warning_sent_at IS NULL
             AND DATEDIFF(NOW(), COALESCE(u.last_login, u.created_at)) >= ?
             AND DATEDIFF(NOW(), COALESCE(u.last_login, u.created_at)) < ?"
        );

        $stmt->execute([$this->warningDays, $this->deleteDays]);
        return $stmt->fetchAll();
    }

    /**
     * Findet User die gelöscht werden sollen
     */
    private function findUsersForDeletion(): array {
        $stmt = $this->db->prepare(
            "SELECT id, username, email
             FROM users
             WHERE is_active = TRUE
             AND inactivity_warning_sent_at IS NOT NULL
             AND DATEDIFF(NOW(), COALESCE(last_login, created_at)) >= ?"
        );

        $stmt->execute([$this->deleteDays]);
        return $stmt->fetchAll();
    }

    /**
     * Sendet Inaktivitäts-Warnung
     */
    private function sendInactivityWarning(array $user): bool {
        $loginUrl = Config::getInstance()->getAppUrl() . '/login';

        // Berechne Freunde-Anzahl
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM friendships WHERE user_id = ? AND status = 'accepted'"
        );
        $stmt->execute([$user['id']]);
        $friendsCount = $stmt->fetch()['count'];

        // Berechne Achievements
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM user_achievements WHERE user_id = ?"
        );
        $stmt->execute([$user['id']]);
        $achievementsCount = $stmt->fetch()['count'];

        $this->mailer->queue(
            $user['email'],
            'Wir vermissen dich bei ModernQuiz!',
            '',
            $user['username'],
            'inactivity_warning',
            [
                'username' => $user['username'],
                'inactiveDays' => $user['inactive_days'],
                'loginUrl' => $loginUrl,
                'stats' => [
                    'quizzes_played' => $user['total_quizzes_played'] ?? 0,
                    'total_points' => $user['total_points'] ?? 0,
                    'achievements' => $achievementsCount,
                    'friends' => $friendsCount
                ]
            ],
            8 // Hohe Priorität
        );

        // Markiere Warnung als gesendet
        $stmt = $this->db->prepare(
            "UPDATE users
             SET inactivity_warning_sent_at = NOW(),
                 scheduled_deletion_at = DATE_ADD(NOW(), INTERVAL 5 DAY)
             WHERE id = ?"
        );

        return $stmt->execute([$user['id']]);
    }

    /**
     * Löscht inaktiven User
     */
    private function deleteInactiveUser(int $userId): bool {
        // Soft Delete könnte hier auch implementiert werden
        // Für jetzt: Hard Delete (Dank CASCADE werden alle Daten gelöscht)

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Reaktiviert einen User (wenn er sich anmeldet)
     */
    public function reactivateUser(int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET inactivity_warning_sent_at = NULL,
                 scheduled_deletion_at = NULL,
                 last_login = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$userId]);
    }

    /**
     * Holt geplante Löschungen
     */
    public function getScheduledDeletions(): array {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, scheduled_deletion_at,
                    DATEDIFF(scheduled_deletion_at, NOW()) as days_until_deletion
             FROM users
             WHERE scheduled_deletion_at IS NOT NULL
             AND scheduled_deletion_at > NOW()
             ORDER BY scheduled_deletion_at ASC"
        );

        $stmt->execute();
        return $stmt->fetchAll();
    }
}

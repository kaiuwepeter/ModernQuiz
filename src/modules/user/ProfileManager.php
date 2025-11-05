<?php
// src/modules/user/ProfileManager.php
namespace ModernQuiz\Modules\User;

class ProfileManager {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Holt Benutzerprofil
     */
    public function getProfile(int $userId, ?int $viewerId = null): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, us.*
             FROM users u
             LEFT JOIN user_stats us ON u.id = us.user_id
             WHERE u.id = ?"
        );

        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        if (!$profile) {
            return null;
        }

        // Prüfe Sichtbarkeit
        if (!$this->canViewProfile($userId, $viewerId, $profile['profile_visibility'])) {
            return null;
        }

        // Entferne sensitive Daten
        unset($profile['password_hash'], $profile['two_factor_secret'], $profile['verification_token']);

        // Füge zusätzliche Stats hinzu
        $profile['achievements'] = $this->getUserAchievements($userId);
        $profile['recent_quizzes'] = $this->getRecentQuizzes($userId, 5);
        $profile['badges'] = $this->getUserBadges($userId);

        return $profile;
    }

    /**
     * Prüft ob Profil angesehen werden darf
     */
    private function canViewProfile(int $userId, ?int $viewerId, string $visibility): bool {
        // Eigenes Profil
        if ($userId === $viewerId) {
            return true;
        }

        // Öffentlich
        if ($visibility === 'public') {
            return true;
        }

        // Privat
        if ($visibility === 'private') {
            return false;
        }

        // Friends only
        if ($visibility === 'friends_only' && $viewerId) {
            return $this->areFriends($userId, $viewerId);
        }

        return false;
    }

    /**
     * Prüft Freundschaft
     */
    private function areFriends(int $userId1, int $userId2): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM friendships
             WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?))
             AND status = 'accepted'"
        );

        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Aktualisiert Profil
     */
    public function updateProfile(int $userId, array $data): bool {
        $allowed = ['bio', 'location', 'website', 'profile_visibility', 'avatar'];
        $fields = [];
        $values = [];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $userId;

        $stmt = $this->db->prepare(
            "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?"
        );

        return $stmt->execute($values);
    }

    /**
     * Holt User Achievements
     */
    private function getUserAchievements(int $userId, int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, ua.earned_at
             FROM user_achievements ua
             JOIN achievements a ON ua.achievement_id = a.id
             WHERE ua.user_id = ?
             ORDER BY ua.earned_at DESC
             LIMIT ?"
        );

        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Holt zuletzt gespielte Quizze
     */
    private function getRecentQuizzes(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare(
            "SELECT q.id, q.title, q.category, qr.score, qr.percentage, qr.completed_at
             FROM quiz_results qr
             JOIN quizzes q ON qr.quiz_id = q.id
             WHERE qr.user_id = ?
             ORDER BY qr.completed_at DESC
             LIMIT ?"
        );

        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Holt User Badges (basierend auf Achievements)
     */
    private function getUserBadges(int $userId): array {
        // Beispiel-Badges basierend auf Stats
        $badges = [];

        $stmt = $this->db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();

        if ($stats) {
            if ($stats['total_quizzes_played'] >= 100) $badges[] = ['name' => 'Quiz-Meister', 'icon' => '🏆'];
            if ($stats['total_quizzes_created'] >= 10) $badges[] = ['name' => 'Quiz-Creator', 'icon' => '✍️'];
            if ($stats['multiplayer_wins'] >= 50) $badges[] = ['name' => 'Multiplayer-Champion', 'icon' => '👑'];
            if ($stats['win_streak'] >= 10) $badges[] = ['name' => 'Unschlagbar', 'icon' => '🔥'];
        }

        return $badges;
    }

    /**
     * Favorit hinzufügen/entfernen
     */
    public function toggleFavorite(int $userId, int $quizId): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM user_favorites WHERE user_id = ? AND quiz_id = ?"
        );
        $stmt->execute([$userId, $quizId]);
        $exists = $stmt->fetch()['count'] > 0;

        if ($exists) {
            $stmt = $this->db->prepare(
                "DELETE FROM user_favorites WHERE user_id = ? AND quiz_id = ?"
            );
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO user_favorites (user_id, quiz_id) VALUES (?, ?)"
            );
        }

        return $stmt->execute([$userId, $quizId]);
    }

    /**
     * Holt Favoriten
     */
    public function getFavorites(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT q.*, uf.created_at as favorited_at
             FROM user_favorites uf
             JOIN quizzes q ON uf.quiz_id = q.id
             WHERE uf.user_id = ?
             ORDER BY uf.created_at DESC"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

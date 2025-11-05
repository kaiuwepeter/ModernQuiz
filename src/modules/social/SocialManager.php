<?php
// src/modules/social/SocialManager.php
namespace ModernQuiz\Modules\Social;

class SocialManager {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    // ===== FREUNDSCHAFTEN =====

    /**
     * Sendet eine Freundschaftsanfrage
     */
    public function sendFriendRequest(int $userId, int $friendId): bool {
        if ($userId === $friendId) {
            return false;
        }

        // Prüfe ob bereits eine Anfrage existiert
        if ($this->getFriendship($userId, $friendId)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO friendships (user_id, friend_id, status, requested_by)
             VALUES (?, ?, 'pending', ?)"
        );

        return $stmt->execute([$userId, $friendId, $userId]);
    }

    /**
     * Akzeptiert eine Freundschaftsanfrage
     */
    public function acceptFriendRequest(int $userId, int $friendId): bool {
        $friendship = $this->getFriendship($friendId, $userId);

        if (!$friendship || $friendship['status'] !== 'pending') {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE friendships
             SET status = 'accepted', accepted_at = NOW()
             WHERE user_id = ? AND friend_id = ?"
        );

        if ($stmt->execute([$friendId, $userId])) {
            // Erstelle die Gegenrichtung
            $stmt2 = $this->db->prepare(
                "INSERT INTO friendships (user_id, friend_id, status, requested_by, accepted_at)
                 VALUES (?, ?, 'accepted', ?, NOW())"
            );
            return $stmt2->execute([$userId, $friendId, $friendId]);
        }

        return false;
    }

    /**
     * Lehnt eine Freundschaftsanfrage ab
     */
    public function declineFriendRequest(int $userId, int $friendId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM friendships
             WHERE user_id = ? AND friend_id = ? AND status = 'pending'"
        );

        return $stmt->execute([$friendId, $userId]);
    }

    /**
     * Entfernt einen Freund
     */
    public function removeFriend(int $userId, int $friendId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM friendships
             WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)"
        );

        return $stmt->execute([$userId, $friendId, $friendId, $userId]);
    }

    /**
     * Blockiert einen Benutzer
     */
    public function blockUser(int $userId, int $blockedUserId): bool {
        // Entferne existierende Freundschaft
        $this->removeFriend($userId, $blockedUserId);

        $stmt = $this->db->prepare(
            "INSERT INTO friendships (user_id, friend_id, status, requested_by)
             VALUES (?, ?, 'blocked', ?)
             ON DUPLICATE KEY UPDATE status = 'blocked'"
        );

        return $stmt->execute([$userId, $blockedUserId, $userId]);
    }

    /**
     * Holt eine Freundschaft
     */
    private function getFriendship(int $userId, int $friendId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM friendships
             WHERE user_id = ? AND friend_id = ?"
        );

        $stmt->execute([$userId, $friendId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Holt alle Freunde eines Benutzers
     */
    public function getFriends(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.created_at, f.accepted_at,
                    us.total_points, us.total_quizzes_played
             FROM friendships f
             JOIN users u ON f.friend_id = u.id
             LEFT JOIN user_stats us ON u.id = us.user_id
             WHERE f.user_id = ? AND f.status = 'accepted'
             ORDER BY u.username ASC"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Holt ausstehende Freundschaftsanfragen
     */
    public function getPendingFriendRequests(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.created_at, f.created_at as request_date
             FROM friendships f
             JOIN users u ON f.user_id = u.id
             WHERE f.friend_id = ? AND f.status = 'pending'
             ORDER BY f.created_at DESC"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // ===== CHALLENGES =====

    /**
     * Erstellt eine Challenge
     */
    public function createChallenge(int $challengerId, int $challengedId, int $quizId, ?string $message = null): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO challenges (challenger_id, challenged_id, quiz_id, message, expires_at)
             VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))"
        );

        if ($stmt->execute([$challengerId, $challengedId, $quizId, $message])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Akzeptiert eine Challenge
     */
    public function acceptChallenge(int $challengeId, int $userId): bool {
        $challenge = $this->getChallenge($challengeId);

        if (!$challenge || $challenge['challenged_id'] != $userId || $challenge['status'] !== 'pending') {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE challenges
             SET status = 'accepted', accepted_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$challengeId]);
    }

    /**
     * Lehnt eine Challenge ab
     */
    public function declineChallenge(int $challengeId, int $userId): bool {
        $challenge = $this->getChallenge($challengeId);

        if (!$challenge || $challenge['challenged_id'] != $userId || $challenge['status'] !== 'pending') {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE challenges
             SET status = 'declined'
             WHERE id = ?"
        );

        return $stmt->execute([$challengeId]);
    }

    /**
     * Speichert das Ergebnis einer Challenge
     */
    public function submitChallengeResult(int $challengeId, int $userId, int $score): bool {
        $challenge = $this->getChallenge($challengeId);

        if (!$challenge || $challenge['status'] !== 'accepted') {
            return false;
        }

        $isChallenger = ($challenge['challenger_id'] == $userId);
        $isChallenged = ($challenge['challenged_id'] == $userId);

        if (!$isChallenger && !$isChallenged) {
            return false;
        }

        $field = $isChallenger ? 'challenger_score' : 'challenged_score';

        $stmt = $this->db->prepare(
            "UPDATE challenges
             SET $field = ?
             WHERE id = ?"
        );

        if ($stmt->execute([$score, $challengeId])) {
            // Prüfe ob beide gespielt haben
            $this->checkChallengeCompletion($challengeId);
            return true;
        }

        return false;
    }

    /**
     * Prüft ob eine Challenge abgeschlossen ist
     */
    private function checkChallengeCompletion(int $challengeId): void {
        $challenge = $this->getChallenge($challengeId);

        if ($challenge && $challenge['challenger_score'] !== null && $challenge['challenged_score'] !== null) {
            $winnerId = null;

            if ($challenge['challenger_score'] > $challenge['challenged_score']) {
                $winnerId = $challenge['challenger_id'];
            } elseif ($challenge['challenged_score'] > $challenge['challenger_score']) {
                $winnerId = $challenge['challenged_id'];
            }

            $stmt = $this->db->prepare(
                "UPDATE challenges
                 SET status = 'completed', winner_id = ?, completed_at = NOW()
                 WHERE id = ?"
            );

            $stmt->execute([$winnerId, $challengeId]);
        }
    }

    /**
     * Holt eine Challenge
     */
    public function getChallenge(int $challengeId): ?array {
        $stmt = $this->db->prepare(
            "SELECT c.*, q.title as quiz_title,
                    u1.username as challenger_name,
                    u2.username as challenged_name
             FROM challenges c
             JOIN quizzes q ON c.quiz_id = q.id
             JOIN users u1 ON c.challenger_id = u1.id
             JOIN users u2 ON c.challenged_id = u2.id
             WHERE c.id = ?"
        );

        $stmt->execute([$challengeId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Holt Challenges eines Benutzers
     */
    public function getUserChallenges(int $userId, ?string $status = null): array {
        $sql = "SELECT c.*, q.title as quiz_title,
                       u1.username as challenger_name,
                       u2.username as challenged_name,
                       CASE
                           WHEN c.challenger_id = ? THEN 'sent'
                           WHEN c.challenged_id = ? THEN 'received'
                       END as type
                FROM challenges c
                JOIN quizzes q ON c.quiz_id = q.id
                JOIN users u1 ON c.challenger_id = u1.id
                JOIN users u2 ON c.challenged_id = u2.id
                WHERE (c.challenger_id = ? OR c.challenged_id = ?)";

        $params = [$userId, $userId, $userId, $userId];

        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ===== ACHIEVEMENTS =====

    /**
     * Erstellt ein Achievement
     */
    public function createAchievement(array $data): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO achievements (name, description, icon, category, points, requirement_type, requirement_value)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $data['icon'] ?? '',
            $data['category'] ?? 'general',
            $data['points'] ?? 0,
            $data['requirement_type'] ?? '',
            $data['requirement_value'] ?? 0
        ])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Verleiht einem User ein Achievement
     */
    public function awardAchievement(int $userId, int $achievementId): bool {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO user_achievements (user_id, achievement_id)
             VALUES (?, ?)"
        );

        return $stmt->execute([$userId, $achievementId]);
    }

    /**
     * Holt Achievements eines Users
     */
    public function getUserAchievements(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, ua.earned_at
             FROM user_achievements ua
             JOIN achievements a ON ua.achievement_id = a.id
             WHERE ua.user_id = ?
             ORDER BY ua.earned_at DESC"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // ===== USER STATS =====

    /**
     * Initialisiert Stats für einen neuen User
     */
    public function initializeUserStats(int $userId): bool {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO user_stats (user_id) VALUES (?)"
        );

        return $stmt->execute([$userId]);
    }

    /**
     * Holt User Stats
     */
    public function getUserStats(int $userId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM user_stats WHERE user_id = ?"
        );

        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Aktualisiert User Stats
     */
    public function updateUserStats(int $userId, array $updates): bool {
        $fields = [];
        $params = [];

        $allowedFields = [
            'total_quizzes_played', 'total_quizzes_created', 'total_questions_answered',
            'correct_answers', 'total_points', 'win_streak', 'best_win_streak',
            'multiplayer_games', 'multiplayer_wins', 'challenges_sent', 'challenges_won'
        ];

        foreach ($allowedFields as $field) {
            if (isset($updates[$field])) {
                $fields[] = "$field = $field + ?";
                $params[] = $updates[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $userId;
        $stmt = $this->db->prepare(
            "UPDATE user_stats SET " . implode(', ', $fields) . " WHERE user_id = ?"
        );

        return $stmt->execute($params);
    }

    /**
     * Holt die Bestenliste
     */
    public function getLeaderboard(string $type = 'total_points', int $limit = 100): array {
        $allowedTypes = ['total_points', 'total_quizzes_played', 'correct_answers', 'multiplayer_wins'];

        if (!in_array($type, $allowedTypes)) {
            $type = 'total_points';
        }

        $stmt = $this->db->prepare(
            "SELECT us.*, u.username
             FROM user_stats us
             JOIN users u ON us.user_id = u.id
             ORDER BY us.$type DESC
             LIMIT ?"
        );

        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}

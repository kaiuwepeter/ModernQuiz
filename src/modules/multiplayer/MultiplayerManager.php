<?php
// src/modules/multiplayer/MultiplayerManager.php
namespace ModernQuiz\Modules\Multiplayer;

class MultiplayerManager {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Erstellt einen neuen Game Room
     */
    public function createRoom(int $quizId, int $hostUserId, array $options = []): ?array {
        $roomCode = $this->generateRoomCode();

        $stmt = $this->db->prepare(
            "INSERT INTO game_rooms (room_code, quiz_id, host_user_id, max_players, is_private, room_password)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $password = !empty($options['password'])
            ? password_hash($options['password'], PASSWORD_DEFAULT)
            : null;

        if ($stmt->execute([
            $roomCode,
            $quizId,
            $hostUserId,
            $options['max_players'] ?? 10,
            !empty($options['is_private']),
            $password
        ])) {
            $roomId = $this->db->lastInsertId();

            // Host automatisch hinzufügen
            $this->joinRoom($roomId, $hostUserId, $options['nickname'] ?? 'Host');

            return $this->getRoom($roomId);
        }

        return null;
    }

    /**
     * Spieler tritt einem Room bei
     */
    public function joinRoom(int $roomId, int $userId, string $nickname, ?string $password = null): bool {
        $room = $this->getRoom($roomId);

        if (!$room) {
            return false;
        }

        // Prüfe Status
        if ($room['status'] !== 'waiting') {
            return false;
        }

        // Prüfe Passwort
        if ($room['is_private'] && $room['room_password']) {
            if (!$password || !password_verify($password, $room['room_password'])) {
                return false;
            }
        }

        // Prüfe Max Players
        $currentPlayers = $this->getRoomParticipants($roomId);
        if (count($currentPlayers) >= $room['max_players']) {
            return false;
        }

        // Prüfe ob User bereits im Room ist
        foreach ($currentPlayers as $participant) {
            if ($participant['user_id'] == $userId) {
                return false;
            }
        }

        $stmt = $this->db->prepare(
            "INSERT INTO game_participants (room_id, user_id, nickname)
             VALUES (?, ?, ?)"
        );

        return $stmt->execute([$roomId, $userId, $nickname]);
    }

    /**
     * Spieler verlässt einen Room
     */
    public function leaveRoom(int $roomId, int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE game_participants
             SET left_at = NOW()
             WHERE room_id = ? AND user_id = ? AND left_at IS NULL"
        );

        return $stmt->execute([$roomId, $userId]);
    }

    /**
     * Startet ein Spiel
     */
    public function startGame(int $roomId, int $hostUserId): bool {
        $room = $this->getRoom($roomId);

        if (!$room || $room['host_user_id'] != $hostUserId) {
            return false;
        }

        if ($room['status'] !== 'waiting') {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE game_rooms
             SET status = 'in_progress', started_at = NOW(), current_question = 1
             WHERE id = ?"
        );

        return $stmt->execute([$roomId]);
    }

    /**
     * Geht zur nächsten Frage
     */
    public function nextQuestion(int $roomId, int $hostUserId): bool {
        $room = $this->getRoom($roomId);

        if (!$room || $room['host_user_id'] != $hostUserId) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE game_rooms
             SET current_question = current_question + 1
             WHERE id = ?"
        );

        return $stmt->execute([$roomId]);
    }

    /**
     * Speichert eine Spieler-Antwort
     */
    public function submitAnswer(int $roomId, int $userId, int $questionId, array $answerData): ?int {
        // Hole Participant ID
        $participant = $this->getParticipant($roomId, $userId);
        if (!$participant) {
            return null;
        }

        // Prüfe ob bereits geantwortet
        $existing = $this->hasAnswered($roomId, $participant['id'], $questionId);
        if ($existing) {
            return null;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO game_answers (room_id, participant_id, question_id, answer_id, text_answer, is_correct, time_taken, points_earned)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([
            $roomId,
            $participant['id'],
            $questionId,
            $answerData['answer_id'] ?? null,
            $answerData['text_answer'] ?? null,
            $answerData['is_correct'] ?? false,
            $answerData['time_taken'] ?? 0,
            $answerData['points_earned'] ?? 0
        ])) {
            // Update Score
            $this->updateParticipantScore($participant['id'], $answerData['points_earned'] ?? 0);
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Beendet ein Spiel
     */
    public function finishGame(int $roomId, int $hostUserId): bool {
        $room = $this->getRoom($roomId);

        if (!$room || $room['host_user_id'] != $hostUserId) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE game_rooms
             SET status = 'finished', finished_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$roomId]);
    }

    /**
     * Holt einen Room mit Details
     */
    public function getRoom(int $roomId): ?array {
        $stmt = $this->db->prepare(
            "SELECT gr.*, q.title as quiz_title, u.username as host_name
             FROM game_rooms gr
             JOIN quizzes q ON gr.quiz_id = q.id
             JOIN users u ON gr.host_user_id = u.id
             WHERE gr.id = ?"
        );

        $stmt->execute([$roomId]);
        $room = $stmt->fetch();

        return $room ?: null;
    }

    /**
     * Holt einen Room via Code
     */
    public function getRoomByCode(string $roomCode): ?array {
        $stmt = $this->db->prepare(
            "SELECT gr.*, q.title as quiz_title, u.username as host_name
             FROM game_rooms gr
             JOIN quizzes q ON gr.quiz_id = q.id
             JOIN users u ON gr.host_user_id = u.id
             WHERE gr.room_code = ?"
        );

        $stmt->execute([$roomCode]);
        $room = $stmt->fetch();

        return $room ?: null;
    }

    /**
     * Holt alle Teilnehmer eines Rooms
     */
    public function getRoomParticipants(int $roomId): array {
        $stmt = $this->db->prepare(
            "SELECT gp.*, u.username
             FROM game_participants gp
             JOIN users u ON gp.user_id = u.id
             WHERE gp.room_id = ? AND gp.left_at IS NULL
             ORDER BY gp.score DESC"
        );

        $stmt->execute([$roomId]);
        return $stmt->fetchAll();
    }

    /**
     * Holt einen Teilnehmer
     */
    private function getParticipant(int $roomId, int $userId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM game_participants
             WHERE room_id = ? AND user_id = ? AND left_at IS NULL"
        );

        $stmt->execute([$roomId, $userId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Prüft ob ein Spieler bereits geantwortet hat
     */
    private function hasAnswered(int $roomId, int $participantId, int $questionId): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM game_answers
             WHERE room_id = ? AND participant_id = ? AND question_id = ?"
        );

        $stmt->execute([$roomId, $participantId, $questionId]);
        $result = $stmt->fetch();

        return $result && $result['count'] > 0;
    }

    /**
     * Aktualisiert den Score eines Teilnehmers
     */
    private function updateParticipantScore(int $participantId, int $points): void {
        $stmt = $this->db->prepare(
            "UPDATE game_participants
             SET score = score + ?
             WHERE id = ?"
        );

        $stmt->execute([$points, $participantId]);
    }

    /**
     * Setzt Ready-Status eines Spielers
     */
    public function setReady(int $roomId, int $userId, bool $ready = true): bool {
        $stmt = $this->db->prepare(
            "UPDATE game_participants
             SET is_ready = ?
             WHERE room_id = ? AND user_id = ? AND left_at IS NULL"
        );

        return $stmt->execute([$ready, $roomId, $userId]);
    }

    /**
     * Listet öffentliche Rooms auf
     */
    public function listPublicRooms(string $status = 'waiting'): array {
        $stmt = $this->db->prepare(
            "SELECT gr.*, q.title as quiz_title, u.username as host_name,
                    (SELECT COUNT(*) FROM game_participants WHERE room_id = gr.id AND left_at IS NULL) as player_count
             FROM game_rooms gr
             JOIN quizzes q ON gr.quiz_id = q.id
             JOIN users u ON gr.host_user_id = u.id
             WHERE gr.is_private = FALSE AND gr.status = ?
             ORDER BY gr.created_at DESC"
        );

        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    /**
     * Holt die Bestenliste für einen Room
     */
    public function getRoomLeaderboard(int $roomId): array {
        return $this->getRoomParticipants($roomId);
    }

    /**
     * Generiert einen eindeutigen Room-Code
     */
    private function generateRoomCode(): string {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM game_rooms WHERE room_code = ?");
            $stmt->execute([$code]);
            $result = $stmt->fetch();
        } while ($result['count'] > 0);

        return $code;
    }

    /**
     * Cleanup alte Rooms
     */
    public function cleanupOldRooms(int $hoursOld = 24): int {
        $stmt = $this->db->prepare(
            "DELETE FROM game_rooms
             WHERE created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
             AND status = 'finished'"
        );

        $stmt->execute([$hoursOld]);
        return $stmt->rowCount();
    }
}

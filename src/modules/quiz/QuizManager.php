<?php
// src/modules/quiz/QuizManager.php
namespace ModernQuiz\Modules\Quiz;

class QuizManager {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Erstellt ein neues Quiz
     */
    public function createQuiz(int $userId, array $quizData): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO quizzes (title, description, created_by, category, difficulty, time_limit, is_public)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([
            $quizData['title'],
            $quizData['description'] ?? '',
            $userId,
            $quizData['category'] ?? 'general',
            $quizData['difficulty'] ?? 'medium',
            $quizData['time_limit'] ?? 0,
            $quizData['is_public'] ?? true
        ])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Fügt eine Frage zu einem Quiz hinzu
     */
    public function addQuestion(int $quizId, array $questionData): ?int {
        // Prüfe ob Quiz existiert und User berechtigt ist
        $quiz = $this->getQuiz($quizId);
        if (!$quiz) {
            return null;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO questions (quiz_id, question_text, question_type, points, order_position, time_limit, image_url, explanation)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([
            $quizId,
            $questionData['question_text'],
            $questionData['question_type'] ?? 'multiple_choice',
            $questionData['points'] ?? 10,
            $questionData['order_position'] ?? 0,
            $questionData['time_limit'] ?? 30,
            $questionData['image_url'] ?? null,
            $questionData['explanation'] ?? null
        ])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Fügt eine Antwort zu einer Frage hinzu
     */
    public function addAnswer(int $questionId, string $answerText, bool $isCorrect, int $orderPosition = 0): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO answers (question_id, answer_text, is_correct, order_position)
             VALUES (?, ?, ?, ?)"
        );

        if ($stmt->execute([$questionId, $answerText, $isCorrect, $orderPosition])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Holt ein Quiz mit allen Details
     */
    public function getQuiz(int $quizId, bool $includeQuestions = false): ?array {
        $stmt = $this->db->prepare(
            "SELECT q.*, u.username as creator_name
             FROM quizzes q
             JOIN users u ON q.created_by = u.id
             WHERE q.id = ?"
        );

        $stmt->execute([$quizId]);
        $quiz = $stmt->fetch();

        if ($quiz && $includeQuestions) {
            $quiz['questions'] = $this->getQuizQuestions($quizId);
        }

        return $quiz ?: null;
    }

    /**
     * Holt alle Fragen eines Quizzes
     */
    public function getQuizQuestions(int $quizId, bool $includeAnswers = true): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM questions WHERE quiz_id = ? ORDER BY order_position ASC"
        );

        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();

        if ($includeAnswers) {
            foreach ($questions as &$question) {
                $question['answers'] = $this->getQuestionAnswers($question['id']);
            }
        }

        return $questions;
    }

    /**
     * Holt alle Antworten einer Frage
     */
    public function getQuestionAnswers(int $questionId, bool $hideCorrect = false): array {
        $stmt = $this->db->prepare(
            "SELECT " . ($hideCorrect ? "id, answer_text, order_position" : "*") . "
             FROM answers
             WHERE question_id = ?
             ORDER BY order_position ASC"
        );

        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    /**
     * Listet öffentliche Quizze auf
     */
    public function listPublicQuizzes(array $filters = [], int $limit = 20, int $offset = 0): array {
        $where = ["is_public = 1", "is_active = 1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['difficulty'])) {
            $where[] = "difficulty = ?";
            $params[] = $filters['difficulty'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare(
            "SELECT q.*, u.username as creator_name,
                    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
             FROM quizzes q
             JOIN users u ON q.created_by = u.id
             WHERE $whereClause
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?"
        );

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Holt Quizze eines Benutzers
     */
    public function getUserQuizzes(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT q.*,
                    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
             FROM quizzes q
             WHERE created_by = ?
             ORDER BY created_at DESC"
        );

        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Speichert ein Quiz-Ergebnis
     */
    public function saveQuizResult(int $quizId, int $userId, array $resultData): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO quiz_results (quiz_id, user_id, score, max_score, percentage, time_taken)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $percentage = $resultData['max_score'] > 0
            ? ($resultData['score'] / $resultData['max_score']) * 100
            : 0;

        if ($stmt->execute([
            $quizId,
            $userId,
            $resultData['score'],
            $resultData['max_score'],
            $percentage,
            $resultData['time_taken'] ?? 0
        ])) {
            // Update play_count
            $this->incrementPlayCount($quizId);
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Speichert eine User-Antwort
     */
    public function saveUserAnswer(int $resultId, array $answerData): ?int {
        $stmt = $this->db->prepare(
            "INSERT INTO user_answers (result_id, question_id, answer_id, text_answer, is_correct, time_taken)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([
            $resultId,
            $answerData['question_id'],
            $answerData['answer_id'] ?? null,
            $answerData['text_answer'] ?? null,
            $answerData['is_correct'] ?? false,
            $answerData['time_taken'] ?? 0
        ])) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Holt Ergebnisse eines Users für ein Quiz
     */
    public function getUserQuizResults(int $userId, int $quizId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM quiz_results
             WHERE user_id = ? AND quiz_id = ?
             ORDER BY completed_at DESC"
        );

        $stmt->execute([$userId, $quizId]);
        return $stmt->fetchAll();
    }

    /**
     * Holt Bestenliste für ein Quiz
     */
    public function getQuizLeaderboard(int $quizId, int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT qr.*, u.username
             FROM quiz_results qr
             JOIN users u ON qr.user_id = u.id
             WHERE qr.quiz_id = ?
             ORDER BY qr.percentage DESC, qr.time_taken ASC
             LIMIT ?"
        );

        $stmt->execute([$quizId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Aktualisiert ein Quiz
     */
    public function updateQuiz(int $quizId, int $userId, array $updateData): bool {
        // Prüfe Berechtigung
        $quiz = $this->getQuiz($quizId);
        if (!$quiz || $quiz['created_by'] != $userId) {
            return false;
        }

        $fields = [];
        $params = [];

        $allowedFields = ['title', 'description', 'category', 'difficulty', 'time_limit', 'is_public', 'is_active'];
        foreach ($allowedFields as $field) {
            if (isset($updateData[$field])) {
                $fields[] = "$field = ?";
                $params[] = $updateData[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $quizId;
        $stmt = $this->db->prepare(
            "UPDATE quizzes SET " . implode(', ', $fields) . " WHERE id = ?"
        );

        return $stmt->execute($params);
    }

    /**
     * Löscht ein Quiz
     */
    public function deleteQuiz(int $quizId, int $userId): bool {
        // Prüfe Berechtigung
        $quiz = $this->getQuiz($quizId);
        if (!$quiz || $quiz['created_by'] != $userId) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE id = ?");
        return $stmt->execute([$quizId]);
    }

    /**
     * Erhöht den Play-Counter eines Quizzes
     */
    private function incrementPlayCount(int $quizId): void {
        $stmt = $this->db->prepare(
            "UPDATE quizzes SET play_count = play_count + 1 WHERE id = ?"
        );
        $stmt->execute([$quizId]);
    }

    /**
     * Validiert eine Antwort
     */
    public function validateAnswer(int $answerId): bool {
        $stmt = $this->db->prepare(
            "SELECT is_correct FROM answers WHERE id = ?"
        );
        $stmt->execute([$answerId]);
        $result = $stmt->fetch();

        return $result ? (bool)$result['is_correct'] : false;
    }
}

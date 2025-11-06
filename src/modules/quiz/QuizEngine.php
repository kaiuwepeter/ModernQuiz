<?php

namespace ModernQuiz\Modules\Quiz;

use ModernQuiz\Core\Database;

class QuizEngine
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Startet eine neue Quiz-Session
     */
    public function startSession(int $userId, ?int $categoryId = null): array
    {
        $sql = "INSERT INTO quiz_sessions (user_id, category_id, status) VALUES (?, ?, 'active')";
        $this->db->query($sql, [$userId, $categoryId]);

        $sessionId = $this->db->lastInsertId();

        return [
            'success' => true,
            'session_id' => $sessionId,
            'message' => 'Quiz-Session gestartet'
        ];
    }

    /**
     * Lädt eine zufällige Frage
     */
    public function getRandomQuestion(?int $categoryId = null, array $excludeIds = []): ?array
    {
        $sql = "SELECT q.*, c.name as category_name
                FROM quiz_questions q
                LEFT JOIN quiz_categories c ON q.category_id = c.id
                WHERE 1=1";
        $params = [];

        if ($categoryId) {
            $sql .= " AND q.category_id = ?";
            $params[] = $categoryId;
        }

        if (!empty($excludeIds)) {
            $placeholders = str_repeat('?,', count($excludeIds) - 1) . '?';
            $sql .= " AND q.id NOT IN ($placeholders)";
            $params = array_merge($params, $excludeIds);
        }

        $sql .= " ORDER BY RAND() LIMIT 1";

        $question = $this->db->fetch($sql, $params);

        if (!$question) {
            return null;
        }

        // Lade Antworten
        $answers = $this->db->fetchAll(
            "SELECT id, answer_text FROM quiz_answers WHERE question_id = ? ORDER BY RAND()",
            [$question['id']]
        );

        return [
            'id' => $question['id'],
            'question' => $question['question'],
            'category' => $question['category_name'],
            'difficulty' => $question['difficulty'],
            'points' => $question['points'],
            'time_limit' => $question['time_limit'],
            'image_url' => $question['image_url'],
            'answers' => $answers
        ];
    }

    /**
     * Prüft eine Antwort und gibt Punkte
     */
    public function submitAnswer(int $sessionId, int $questionId, int $answerId, int $timeTaken, ?string $powerupUsed = null): array
    {
        // Prüfe ob Antwort korrekt ist
        $answer = $this->db->fetch(
            "SELECT is_correct, explanation FROM quiz_answers WHERE id = ? AND question_id = ?",
            [$answerId, $questionId]
        );

        if (!$answer) {
            return ['success' => false, 'message' => 'Ungültige Antwort'];
        }

        $isCorrect = (bool)$answer['is_correct'];

        // Hole Frage für Punkte
        $question = $this->db->fetch(
            "SELECT points, time_limit FROM quiz_questions WHERE id = ?",
            [$questionId]
        );

        $pointsEarned = 0;
        if ($isCorrect) {
            $pointsEarned = $question['points'];

            // Time-Bonus: Je schneller, desto mehr Bonus
            $timeBonus = max(0, ($question['time_limit'] - $timeTaken) / $question['time_limit']);
            $pointsEarned += (int)($pointsEarned * $timeBonus * 0.5);
        }

        // Speichere Antwort
        $this->db->query(
            "INSERT INTO user_answers (session_id, question_id, answer_id, is_correct, points_earned, time_taken, powerup_used)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$sessionId, $questionId, $answerId, $isCorrect, $pointsEarned, $timeTaken, $powerupUsed]
        );

        // Update Session
        if ($isCorrect) {
            $this->db->query(
                "UPDATE quiz_sessions
                 SET total_questions = total_questions + 1,
                     correct_answers = correct_answers + 1,
                     total_points = total_points + ?
                 WHERE id = ?",
                [$pointsEarned, $sessionId]
            );
        } else {
            $this->db->query(
                "UPDATE quiz_sessions
                 SET total_questions = total_questions + 1
                 WHERE id = ?",
                [$sessionId]
            );
        }

        // Update User Stats
        $session = $this->db->fetch("SELECT user_id FROM quiz_sessions WHERE id = ?", [$sessionId]);
        $this->updateUserStats($session['user_id'], $isCorrect, $pointsEarned);

        return [
            'success' => true,
            'correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'explanation' => $answer['explanation'],
            'total_session_points' => $this->getSessionPoints($sessionId)
        ];
    }

    /**
     * Beendet eine Quiz-Session
     */
    public function endSession(int $sessionId): array
    {
        $this->db->query(
            "UPDATE quiz_sessions SET status = 'completed', completed_at = NOW() WHERE id = ?",
            [$sessionId]
        );

        $stats = $this->db->fetch(
            "SELECT total_questions, correct_answers, total_points FROM quiz_sessions WHERE id = ?",
            [$sessionId]
        );

        return [
            'success' => true,
            'stats' => $stats
        ];
    }

    /**
     * Aktualisiert User-Statistiken
     */
    private function updateUserStats(int $userId, bool $isCorrect, int $points): void
    {
        // Prüfe ob Stats existieren
        $exists = $this->db->fetch("SELECT user_id FROM user_stats WHERE user_id = ?", [$userId]);

        if (!$exists) {
            $this->db->query(
                "INSERT INTO user_stats (user_id, total_questions_answered, total_correct_answers, total_points, coins)
                 VALUES (?, 1, ?, ?, 100)",
                [$userId, $isCorrect ? 1 : 0, $points]
            );
        } else {
            // Coins earned: 5 per correct answer (as DECIMAL now)
            $coinsEarned = $isCorrect ? 5.00 : 0.00;

            // Update stats (without coins - will be handled by CoinManager)
            $sql = "UPDATE user_stats SET
                    total_questions_answered = total_questions_answered + 1,
                    total_correct_answers = total_correct_answers + ?,
                    total_points = total_points + ?,
                    current_streak = IF(?, current_streak + 1, 0),
                    experience = experience + ?
                    WHERE user_id = ?";

            $this->db->query($sql, [
                $isCorrect ? 1 : 0,
                $points,
                $isCorrect,
                $points,
                $userId
            ]);

            // Update longest streak
            if ($isCorrect) {
                $this->db->query(
                    "UPDATE user_stats SET longest_streak = GREATEST(longest_streak, current_streak) WHERE user_id = ?",
                    [$userId]
                );
            }

            // Award coins using CoinManager (with transaction logging)
            if ($coinsEarned > 0) {
                $coinManager = new \ModernQuiz\Modules\Coins\CoinManager($this->db->getConnection());

                $coinResult = $coinManager->addCoins(
                    $userId,
                    $coinsEarned,
                    0, // No bonus coins for quiz
                    \ModernQuiz\Modules\Coins\CoinManager::TX_QUIZ_WIN,
                    'quiz',
                    null,
                    "Quiz-Gewinn: {$points} Punkte, korrekte Antwort",
                    ['points' => $points]
                );

                // Trigger referral commission (6% for referrer if user was referred)
                if ($coinResult['success'] && isset($coinResult['transaction_id'])) {
                    $referralManager = new \ModernQuiz\Modules\Referral\ReferralManager($this->db->getConnection());
                    $referralManager->processCommission($userId, $coinsEarned, $coinResult['transaction_id']);
                }
            }
        }
    }

    /**
     * Holt aktuelle Session-Punkte
     */
    public function getSessionPoints(int $sessionId): int
    {
        $result = $this->db->fetch(
            "SELECT total_points FROM quiz_sessions WHERE id = ?",
            [$sessionId]
        );
        return $result['total_points'] ?? 0;
    }

    /**
     * Holt alle Kategorien
     */
    public function getCategories(): array
    {
        return $this->db->fetchAll("SELECT * FROM quiz_categories ORDER BY name");
    }

    /**
     * Holt Session-Details
     */
    public function getSession(int $sessionId): ?array
    {
        return $this->db->fetch("SELECT * FROM quiz_sessions WHERE id = ?", [$sessionId]);
    }
}

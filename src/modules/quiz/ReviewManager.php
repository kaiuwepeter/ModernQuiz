<?php
// src/modules/quiz/ReviewManager.php
namespace ModernQuiz\Modules\Quiz;

class ReviewManager {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Erstellt oder aktualisiert eine Review
     */
    public function createOrUpdateReview(int $quizId, int $userId, int $rating, ?string $comment = null): bool {
        if ($rating < 1 || $rating > 5) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO quiz_reviews (quiz_id, user_id, rating, comment)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = ?, comment = ?, updated_at = NOW()"
        );

        return $stmt->execute([$quizId, $userId, $rating, $comment, $rating, $comment]);
    }

    /**
     * Holt alle Reviews für ein Quiz
     */
    public function getQuizReviews(int $quizId, int $limit = 20, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT qr.*, u.username, u.avatar,
                    (SELECT COUNT(*) FROM review_helpful_votes WHERE review_id = qr.id) as helpful_count
             FROM quiz_reviews qr
             JOIN users u ON qr.user_id = u.id
             WHERE qr.quiz_id = ? AND qr.is_approved = TRUE
             ORDER BY helpful_count DESC, qr.created_at DESC
             LIMIT ? OFFSET ?"
        );

        $stmt->execute([$quizId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Durchschnittsbewertung für ein Quiz
     */
    public function getQuizRating(int $quizId): array {
        $stmt = $this->db->prepare(
            "SELECT
                AVG(rating) as average_rating,
                COUNT(*) as total_reviews,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
             FROM quiz_reviews
             WHERE quiz_id = ? AND is_approved = TRUE"
        );

        $stmt->execute([$quizId]);
        return $stmt->fetch();
    }

    /**
     * Markiert Review als hilfreich
     */
    public function markAsHelpful(int $reviewId, int $userId): bool {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO review_helpful_votes (review_id, user_id) VALUES (?, ?)"
        );

        if ($stmt->execute([$reviewId, $userId])) {
            // Update helpful_count
            $updateStmt = $this->db->prepare(
                "UPDATE quiz_reviews
                 SET helpful_count = (SELECT COUNT(*) FROM review_helpful_votes WHERE review_id = ?)
                 WHERE id = ?"
            );
            $updateStmt->execute([$reviewId, $reviewId]);
            return true;
        }

        return false;
    }

    /**
     * Löscht eine Review
     */
    public function deleteReview(int $reviewId, int $userId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM quiz_reviews WHERE id = ? AND user_id = ?"
        );
        return $stmt->execute([$reviewId, $userId]);
    }

    /**
     * Holt User-Review für Quiz
     */
    public function getUserReview(int $quizId, int $userId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM quiz_reviews WHERE quiz_id = ? AND user_id = ?"
        );
        $stmt->execute([$quizId, $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}

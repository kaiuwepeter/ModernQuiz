<?php
// src/modules/search/SearchManager.php
namespace ModernQuiz\Modules\Search;

class SearchManager {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Umfassende Quiz-Suche
     */
    public function searchQuizzes(array $params = []): array {
        $where = ["is_active = 1"];
        $bindings = [];

        // Textsuche
        if (!empty($params['query'])) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $search = '%' . $params['query'] . '%';
            $bindings[] = $search;
            $bindings[] = $search;
        }

        // Kategorie
        if (!empty($params['category'])) {
            $where[] = "category = ?";
            $bindings[] = $params['category'];
        }

        // Schwierigkeit
        if (!empty($params['difficulty'])) {
            $where[] = "difficulty = ?";
            $bindings[] = $params['difficulty'];
        }

        // Creator
        if (!empty($params['created_by'])) {
            $where[] = "created_by = ?";
            $bindings[] = $params['created_by'];
        }

        // Nur öffentliche
        if (!isset($params['include_private']) || !$params['include_private']) {
            $where[] = "is_public = TRUE";
        }

        // Tags
        if (!empty($params['tags'])) {
            $tagList = is_array($params['tags']) ? $params['tags'] : [$params['tags']];
            $placeholders = str_repeat('?,', count($tagList) - 1) . '?';
            $where[] = "id IN (SELECT quiz_id FROM quiz_tag_relations qtr
                        JOIN quiz_tags qt ON qtr.tag_id = qt.id
                        WHERE qt.name IN ($placeholders))";
            $bindings = array_merge($bindings, $tagList);
        }

        // Min Rating
        if (isset($params['min_rating'])) {
            $where[] = "(SELECT AVG(rating) FROM quiz_reviews WHERE quiz_id = quizzes.id) >= ?";
            $bindings[] = $params['min_rating'];
        }

        // Sortierung
        $orderBy = "created_at DESC";
        if (!empty($params['sort_by'])) {
            switch ($params['sort_by']) {
                case 'popular':
                    $orderBy = "play_count DESC";
                    break;
                case 'rating':
                    $orderBy = "(SELECT AVG(rating) FROM quiz_reviews WHERE quiz_id = quizzes.id) DESC";
                    break;
                case 'recent':
                    $orderBy = "created_at DESC";
                    break;
                case 'title':
                    $orderBy = "title ASC";
                    break;
            }
        }

        // Pagination
        $limit = $params['limit'] ?? 20;
        $offset = $params['offset'] ?? 0;

        $bindings[] = $limit;
        $bindings[] = $offset;

        $whereClause = implode(' AND ', $where);

        $stmt = $this->db->prepare(
            "SELECT q.*, u.username as creator_name,
                    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count,
                    (SELECT AVG(rating) FROM quiz_reviews WHERE quiz_id = q.id) as avg_rating,
                    (SELECT COUNT(*) FROM quiz_reviews WHERE quiz_id = q.id) as review_count
             FROM quizzes q
             JOIN users u ON q.created_by = u.id
             WHERE $whereClause
             ORDER BY $orderBy
             LIMIT ? OFFSET ?"
        );

        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /**
     * User-Suche
     */
    public function searchUsers(string $query, int $limit = 20): array {
        $search = '%' . $query . '%';

        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.avatar, u.bio, u.location,
                    us.total_points, us.total_quizzes_played, us.total_quizzes_created
             FROM users u
             LEFT JOIN user_stats us ON u.id = us.user_id
             WHERE u.is_active = TRUE
             AND (u.username LIKE ? OR u.bio LIKE ?)
             AND u.profile_visibility != 'private'
             ORDER BY us.total_points DESC
             LIMIT ?"
        );

        $stmt->execute([$search, $search, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Tag-Suche
     */
    public function searchTags(string $query, int $limit = 10): array {
        $search = '%' . $query . '%';

        $stmt = $this->db->prepare(
            "SELECT * FROM quiz_tags
             WHERE name LIKE ?
             ORDER BY usage_count DESC
             LIMIT ?"
        );

        $stmt->execute([$search, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Beliebte Tags
     */
    public function getPopularTags(int $limit = 20): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM quiz_tags
             ORDER BY usage_count DESC
             LIMIT ?"
        );

        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Kategorien mit Stats
     */
    public function getCategoriesWithStats(): array {
        $stmt = $this->db->query(
            "SELECT category,
                    COUNT(*) as quiz_count,
                    SUM(play_count) as total_plays,
                    AVG((SELECT AVG(rating) FROM quiz_reviews WHERE quiz_id = quizzes.id)) as avg_rating
             FROM quizzes
             WHERE is_active = TRUE
             GROUP BY category
             ORDER BY quiz_count DESC"
        );

        return $stmt->fetchAll();
    }
}

<?php
// src/modules/user/ReferralManager.php
namespace ModernQuiz\Modules\User;

use ModernQuiz\Core\Email\Mailer;
use ModernQuiz\Core\Config;

class ReferralManager {
    private $db;
    private $mailer;

    public function __construct($database, Mailer $mailer) {
        $this->db = $database;
        $this->mailer = $mailer;
    }

    /**
     * Generiert einen eindeutigen Referral-Code
     */
    public function generateReferralCode(int $userId): string {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE referral_code = ?");
            $stmt->execute([$code]);
            $result = $stmt->fetch();
        } while ($result['count'] > 0);

        // Update User
        $stmt = $this->db->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
        $stmt->execute([$code, $userId]);

        return $code;
    }

    /**
     * Validiert einen Referral-Code
     */
    public function validateReferralCode(string $code): ?int {
        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE referral_code = ? AND is_active = TRUE"
        );
        $stmt->execute([$code]);
        $result = $stmt->fetch();

        return $result ? $result['id'] : null;
    }

    /**
     * Verarbeitet eine Empfehlung
     */
    public function processReferral(int $newUserId, string $referralCode): bool {
        $referrerId = $this->validateReferralCode($referralCode);

        if (!$referrerId) {
            return false;
        }

        // Verhindere Selbst-Empfehlung
        if ($referrerId === $newUserId) {
            return false;
        }

        // Update neuer User
        $stmt = $this->db->prepare(
            "UPDATE users SET referred_by = ? WHERE id = ?"
        );
        $stmt->execute([$referrerId, $newUserId]);

        // Increment Referral Count
        $stmt = $this->db->prepare(
            "UPDATE users SET referral_count = referral_count + 1 WHERE id = ?"
        );
        $stmt->execute([$referrerId]);

        // Bonuspunkte vergeben
        $bonusPoints = (int)Config::getInstance()->get('REFERRAL_BONUS_FOR_REFERRER', 50);
        $newUserBonus = (int)Config::getInstance()->get('REFERRAL_BONUS_POINTS', 100);

        // Update Stats für Referrer
        $stmt = $this->db->prepare(
            "UPDATE user_stats SET total_points = total_points + ? WHERE user_id = ?"
        );
        $stmt->execute([$bonusPoints, $referrerId]);

        // Update Stats für neuen User
        $stmt = $this->db->prepare(
            "INSERT INTO user_stats (user_id, total_points)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE total_points = total_points + ?"
        );
        $stmt->execute([$newUserId, $newUserBonus, $newUserBonus]);

        // Sende Benachrichtigung an Referrer
        $this->notifyReferrer($referrerId, $newUserId, $bonusPoints);

        return true;
    }

    /**
     * Benachrichtigt den Referrer über erfolgreiche Empfehlung
     */
    private function notifyReferrer(int $referrerId, int $newUserId, int $bonusPoints): void {
        $stmt = $this->db->prepare(
            "SELECT u1.email, u1.username, u1.referral_code, u1.referral_count,
                    u2.username as referred_username
             FROM users u1
             JOIN users u2 ON u2.id = ?
             WHERE u1.id = ?"
        );
        $stmt->execute([$newUserId, $referrerId]);
        $data = $stmt->fetch();

        if ($data) {
            // Berechne Total Bonus
            $totalBonusPoints = $bonusPoints * $data['referral_count'];

            $this->mailer->queue(
                $data['email'],
                'Neue Empfehlung!',
                '',
                $data['username'],
                'referral_success',
                [
                    'username' => $data['username'],
                    'referredUsername' => $data['referred_username'],
                    'bonusPoints' => $bonusPoints,
                    'totalReferrals' => $data['referral_count'],
                    'totalBonusPoints' => $totalBonusPoints,
                    'referralCode' => $data['referral_code']
                ],
                7 // Hohe Priorität
            );
        }
    }

    /**
     * Holt Empfehlungs-Statistiken
     */
    public function getReferralStats(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT u.referral_code, u.referral_count,
                    (SELECT COUNT(*) FROM users WHERE referred_by = ?) as total_referrals,
                    (SELECT SUM(total_points) FROM user_stats WHERE user_id IN
                        (SELECT id FROM users WHERE referred_by = ?)) as referred_points
             FROM users u
             WHERE u.id = ?"
        );

        $stmt->execute([$userId, $userId, $userId]);
        $stats = $stmt->fetch();

        // Holt Liste der geworbenen User
        $stmt = $this->db->prepare(
            "SELECT id, username, created_at
             FROM users
             WHERE referred_by = ?
             ORDER BY created_at DESC
             LIMIT 10"
        );
        $stmt->execute([$userId]);
        $stats['referrals'] = $stmt->fetchAll();

        return $stats;
    }

    /**
     * Holt Referral-Leaderboard
     */
    public function getLeaderboard(int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.referral_count, us.total_points
             FROM users u
             LEFT JOIN user_stats us ON u.id = us.user_id
             WHERE u.referral_count > 0
             ORDER BY u.referral_count DESC, us.total_points DESC
             LIMIT ?"
        );

        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}

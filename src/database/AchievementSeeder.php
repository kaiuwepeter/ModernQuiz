<?php
// src/database/AchievementSeeder.php
namespace ModernQuiz\Database;

use ModernQuiz\Modules\Social\SocialManager;

class AchievementSeeder {
    private $db;
    private $socialManager;

    public function __construct($database) {
        $this->db = $database;
        $this->socialManager = new SocialManager($database);
    }

    public function run(): void {
        echo "Starting Achievement Seeder...\n";

        $achievements = $this->getAllAchievements();

        $count = 0;
        foreach ($achievements as $achievement) {
            $id = $this->socialManager->createAchievement($achievement);
            if ($id) {
                $count++;
            }
        }

        echo "✅ $count Achievements erstellt!\n";
    }

    private function getAllAchievements(): array {
        return [
            // === QUIZ SPIELEN (30 Achievements) ===
            ['name' => 'Erste Schritte', 'description' => 'Spiele dein erstes Quiz', 'icon' => '🎯', 'category' => 'quiz_play', 'points' => 10, 'requirement_type' => 'quizzes_played', 'requirement_value' => 1],
            ['name' => 'Quiz-Neuling', 'description' => 'Spiele 5 Quizze', 'icon' => '🎮', 'category' => 'quiz_play', 'points' => 25, 'requirement_type' => 'quizzes_played', 'requirement_value' => 5],
            ['name' => 'Quiz-Fan', 'description' => 'Spiele 10 Quizze', 'icon' => '⭐', 'category' => 'quiz_play', 'points' => 50, 'requirement_type' => 'quizzes_played', 'requirement_value' => 10],
            ['name' => 'Quiz-Enthusiast', 'description' => 'Spiele 25 Quizze', 'icon' => '🌟', 'category' => 'quiz_play', 'points' => 100, 'requirement_type' => 'quizzes_played', 'requirement_value' => 25],
            ['name' => 'Quiz-Veteran', 'description' => 'Spiele 50 Quizze', 'icon' => '💫', 'category' => 'quiz_play', 'points' => 200, 'requirement_type' => 'quizzes_played', 'requirement_value' => 50],
            ['name' => 'Quiz-Meister', 'description' => 'Spiele 100 Quizze', 'icon' => '🏆', 'category' => 'quiz_play', 'points' => 500, 'requirement_type' => 'quizzes_played', 'requirement_value' => 100],
            ['name' => 'Quiz-Legende', 'description' => 'Spiele 250 Quizze', 'icon' => '👑', 'category' => 'quiz_play', 'points' => 1000, 'requirement_type' => 'quizzes_played', 'requirement_value' => 250],
            ['name' => 'Quiz-Gott', 'description' => 'Spiele 500 Quizze', 'icon' => '⚡', 'category' => 'quiz_play', 'points' => 2500, 'requirement_type' => 'quizzes_played', 'requirement_value' => 500],
            ['name' => 'Perfektionist', 'description' => 'Erreiche 100% in einem Quiz', 'icon' => '💯', 'category' => 'quiz_play', 'points' => 100, 'requirement_type' => 'perfect_score', 'requirement_value' => 1],
            ['name' => 'Unfehlbar', 'description' => 'Erreiche 100% in 10 Quizzen', 'icon' => '🎖️', 'category' => 'quiz_play', 'points' => 500, 'requirement_type' => 'perfect_score', 'requirement_value' => 10],

            // === PUNKTE (15 Achievements) ===
            ['name' => 'Punktesammler', 'description' => 'Erreiche 100 Punkte', 'icon' => '💰', 'category' => 'points', 'points' => 10, 'requirement_type' => 'total_points', 'requirement_value' => 100],
            ['name' => 'Punktejäger', 'description' => 'Erreiche 500 Punkte', 'icon' => '💎', 'category' => 'points', 'points' => 50, 'requirement_type' => 'total_points', 'requirement_value' => 500],
            ['name' => 'Punktekönig', 'description' => 'Erreiche 1.000 Punkte', 'icon' => '🏅', 'category' => 'points', 'points' => 100, 'requirement_type' => 'total_points', 'requirement_value' => 1000],
            ['name' => 'Punktemagnat', 'description' => 'Erreiche 5.000 Punkte', 'icon' => '🌠', 'category' => 'points', 'points' => 250, 'requirement_type' => 'total_points', 'requirement_value' => 5000],
            ['name' => 'Punktemillionär', 'description' => 'Erreiche 10.000 Punkte', 'icon' => '💸', 'category' => 'points', 'points' => 500, 'requirement_type' => 'total_points', 'requirement_value' => 10000],
            ['name' => 'Punktekaiser', 'description' => 'Erreiche 25.000 Punkte', 'icon' => '👑', 'category' => 'points', 'points' => 1000, 'requirement_type' => 'total_points', 'requirement_value' => 25000],
            ['name' => 'Punktelegende', 'description' => 'Erreiche 50.000 Punkte', 'icon' => '🎆', 'category' => 'points', 'points' => 2500, 'requirement_type' => 'total_points', 'requirement_value' => 50000],

            // === KATEGORIEN (15 Achievements - 1 pro Kategorie) ===
            ['name' => 'Allgemeinwissen-Experte', 'description' => 'Spiele 10 Allgemeinwissen-Quizze', 'icon' => '🧠', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],
            ['name' => 'Geografie-Meister', 'description' => 'Spiele 10 Geografie-Quizze', 'icon' => '🌍', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],
            ['name' => 'Geschichts-Buff', 'description' => 'Spiele 10 Geschichte-Quizze', 'icon' => '📚', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],
            ['name' => 'Wissenschafts-Nerd', 'description' => 'Spiele 10 Naturwissenschaften-Quizze', 'icon' => '🔬', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],
            ['name' => 'Technik-Guru', 'description' => 'Spiele 10 Technik-Quizze', 'icon' => '💻', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],
            ['name' => 'Sport-Kenner', 'description' => 'Spiele 10 Sport-Quizze', 'icon' => '⚽', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],
            ['name' => 'Kultur-Liebhaber', 'description' => 'Spiele 10 Kunst & Kultur-Quizze', 'icon' => '🎨', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],
            ['name' => 'Film-Buff', 'description' => 'Spiele 10 Film & Musik-Quizze', 'icon' => '🎬', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],
            ['name' => 'Literatur-Kenner', 'description' => 'Spiele 10 Literatur-Quizze', 'icon' => '📖', 'category' => 'categories', 'points' => 50, 'requirement_type' => 'category_quizzes', 'requirement_value' => 10],

            // === MULTIPLAYER (15 Achievements) ===
            ['name' => 'Multiplayer-Debüt', 'description' => 'Spiele dein erstes Multiplayer-Spiel', 'icon' => '🎮', 'category' => 'multiplayer', 'points' => 25, 'requirement_type' => 'multiplayer_games', 'requirement_value' => 1],
            ['name' => 'Multiplayer-Anfänger', 'description' => 'Spiele 5 Multiplayer-Spiele', 'icon' => '🕹️', 'category' => 'multiplayer', 'points' => 50, 'requirement_type' => 'multiplayer_games', 'requirement_value' => 5],
            ['name' => 'Multiplayer-Veteran', 'description' => 'Spiele 25 Multiplayer-Spiele', 'icon' => '🎯', 'category' => 'multiplayer', 'points' => 150, 'requirement_type' => 'multiplayer_games', 'requirement_value' => 25],
            ['name' => 'Multiplayer-Champion', 'description' => 'Spiele 50 Multiplayer-Spiele', 'icon' => '🏆', 'category' => 'multiplayer', 'points' => 300, 'requirement_type' => 'multiplayer_games', 'requirement_value' => 50],
            ['name' => 'Erster Sieg', 'description' => 'Gewinne dein erstes Multiplayer-Spiel', 'icon' => '🥇', 'category' => 'multiplayer', 'points' => 50, 'requirement_type' => 'multiplayer_wins', 'requirement_value' => 1],
            ['name' => 'Siegesserie', 'description' => 'Gewinne 5 Multiplayer-Spiele', 'icon' => '🥈', 'category' => 'multiplayer', 'points' => 100, 'requirement_type' => 'multiplayer_wins', 'requirement_value' => 5],
            ['name' => 'Unschlagbar', 'description' => 'Gewinne 25 Multiplayer-Spiele', 'icon' => '🥉', 'category' => 'multiplayer', 'points' => 250, 'requirement_type' => 'multiplayer_wins', 'requirement_value' => 25],
            ['name' => 'Multiplayer-König', 'description' => 'Gewinne 50 Multiplayer-Spiele', 'icon' => '👑', 'category' => 'multiplayer', 'points' => 500, 'requirement_type' => 'multiplayer_wins', 'requirement_value' => 50],

            // === SOCIAL (15 Achievements) ===
            ['name' => 'Sozialer Schmetterling', 'description' => 'Füge deinen ersten Freund hinzu', 'icon' => '👥', 'category' => 'social', 'points' => 25, 'requirement_type' => 'friends', 'requirement_value' => 1],
            ['name' => 'Freundeskreis', 'description' => 'Habe 5 Freunde', 'icon' => '👫', 'category' => 'social', 'points' => 50, 'requirement_type' => 'friends', 'requirement_value' => 5],
            ['name' => 'Beliebter Spieler', 'description' => 'Habe 10 Freunde', 'icon' => '👬', 'category' => 'social', 'points' => 100, 'requirement_type' => 'friends', 'requirement_value' => 10],
            ['name' => 'Social-Star', 'description' => 'Habe 25 Freunde', 'icon' => '⭐', 'category' => 'social', 'points' => 250, 'requirement_type' => 'friends', 'requirement_value' => 25],
            ['name' => 'Challenge-Starter', 'description' => 'Sende deine erste Challenge', 'icon' => '⚔️', 'category' => 'social', 'points' => 25, 'requirement_type' => 'challenges_sent', 'requirement_value' => 1],
            ['name' => 'Challenge-Meister', 'description' => 'Gewinne 10 Challenges', 'icon' => '🏅', 'category' => 'social', 'points' => 150, 'requirement_type' => 'challenges_won', 'requirement_value' => 10],
            ['name' => 'Challenge-Legende', 'description' => 'Gewinne 50 Challenges', 'icon' => '🎖️', 'category' => 'social', 'points' => 500, 'requirement_type' => 'challenges_won', 'requirement_value' => 50],

            // === QUIZ ERSTELLEN (15 Achievements) ===
            ['name' => 'Quiz-Creator', 'description' => 'Erstelle dein erstes Quiz', 'icon' => '✍️', 'category' => 'quiz_create', 'points' => 50, 'requirement_type' => 'quizzes_created', 'requirement_value' => 1],
            ['name' => 'Quiz-Autor', 'description' => 'Erstelle 5 Quizze', 'icon' => '📝', 'category' => 'quiz_create', 'points' => 100, 'requirement_type' => 'quizzes_created', 'requirement_value' => 5],
            ['name' => 'Quiz-Designer', 'description' => 'Erstelle 10 Quizze', 'icon' => '🎨', 'category' => 'quiz_create', 'points' => 250, 'requirement_type' => 'quizzes_created', 'requirement_value' => 10],
            ['name' => 'Quiz-Architekt', 'description' => 'Erstelle 25 Quizze', 'icon' => '🏗️', 'category' => 'quiz_create', 'points' => 500, 'requirement_type' => 'quizzes_created', 'requirement_value' => 25],
            ['name' => 'Quiz-Produzent', 'description' => 'Erstelle 50 Quizze', 'icon' => '🎬', 'category' => 'quiz_create', 'points' => 1000, 'requirement_type' => 'quizzes_created', 'requirement_value' => 50],

            // === STREAK & SPECIAL (20+ Achievements) ===
            ['name' => 'Streak-Starter', 'description' => 'Erreiche eine 3er-Siegesserie', 'icon' => '🔥', 'category' => 'streak', 'points' => 50, 'requirement_type' => 'win_streak', 'requirement_value' => 3],
            ['name' => 'On Fire', 'description' => 'Erreiche eine 5er-Siegesserie', 'icon' => '🌟', 'category' => 'streak', 'points' => 100, 'requirement_type' => 'win_streak', 'requirement_value' => 5],
            ['name' => 'Unstoppable', 'description' => 'Erreiche eine 10er-Siegesserie', 'icon' => '⚡', 'category' => 'streak', 'points' => 250, 'requirement_type' => 'win_streak', 'requirement_value' => 10],
            ['name' => 'Phänomenal', 'description' => 'Erreiche eine 20er-Siegesserie', 'icon' => '💫', 'category' => 'streak', 'points' => 500, 'requirement_type' => 'win_streak', 'requirement_value' => 20],

            // === REFERRAL ===
            ['name' => 'Botschafter', 'description' => 'Werbe deinen ersten Freund', 'icon' => '📣', 'category' => 'referral', 'points' => 100, 'requirement_type' => 'referrals', 'requirement_value' => 1],
            ['name' => 'Influencer', 'description' => 'Werbe 5 Freunde', 'icon' => '📢', 'category' => 'referral', 'points' => 250, 'requirement_type' => 'referrals', 'requirement_value' => 5],
            ['name' => 'Marketing-Genie', 'description' => 'Werbe 10 Freunde', 'icon' => '🎯', 'category' => 'referral', 'points' => 500, 'requirement_type' => 'referrals', 'requirement_value' => 10],
            ['name' => 'Wachstums-Champion', 'description' => 'Werbe 25 Freunde', 'icon' => '🚀', 'category' => 'referral', 'points' => 1000, 'requirement_type' => 'referrals', 'requirement_value' => 25],

            // === SPECIAL/HIDDEN ===
            ['name' => 'Nachteuler 🦉', 'description' => 'Spiele um 3 Uhr nachts', 'icon' => '🌙', 'category' => 'special', 'points' => 50, 'requirement_type' => 'special', 'requirement_value' => 0],
            ['name' => 'Frühaufsteher 🐓', 'description' => 'Spiele um 6 Uhr morgens', 'icon' => '☀️', 'category' => 'special', 'points' => 50, 'requirement_type' => 'special', 'requirement_value' => 0],
            ['name' => 'Wochenend-Krieger', 'description' => 'Spiele an Samstag und Sonntag', 'icon' => '🎉', 'category' => 'special', 'points' => 50, 'requirement_type' => 'special', 'requirement_value' => 0],
            ['name' => 'Geburtstagskind', 'description' => 'Spiele an deinem Geburtstag', 'icon' => '🎂', 'category' => 'special', 'points' => 100, 'requirement_type' => 'special', 'requirement_value' => 0],
            ['name' => 'Jahrestag', 'description' => 'Ein Jahr bei ModernQuiz', 'icon' => '🎊', 'category' => 'special', 'points' => 500, 'requirement_type' => 'special', 'requirement_value' => 0],
            ['name' => 'Beta-Tester', 'description' => 'Einer der ersten 100 User', 'icon' => '🚀', 'category' => 'special', 'points' => 1000, 'requirement_type' => 'special', 'requirement_value' => 0],
            ['name' => 'Bug-Hunter', 'description' => 'Melde einen Bug', 'icon' => '🐛', 'category' => 'special', 'points' => 250, 'requirement_type' => 'special', 'requirement_value' => 0],
            ['name' => 'Feedback-Geber', 'description' => 'Gib Feedback zur Plattform', 'icon' => '💬', 'category' => 'special', 'points' => 100, 'requirement_type' => 'special', 'requirement_value' => 0],
        ];
    }
}

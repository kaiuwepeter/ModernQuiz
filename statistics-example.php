<?php
/**
 * Beispiel: Wie man die Statistiken nutzt
 */

require_once __DIR__ . '/vendor/autoload.php';

use ModernQuiz\Core\Config;
use ModernQuiz\Modules\Statistics\StatisticsManager;

// Datenbank-Verbindung
$config = Config::getInstance();
$dbConfig = $config->getDbConfig();

$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
    PDO::ATTR_ERRMODE => PDO::ATTR_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$statsManager = new StatisticsManager($pdo);

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              ModernQuiz - Statistik-Dashboard            ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// ===== GLOBALE STATISTIKEN =====
echo "📊 GLOBALE PLATTFORM-STATISTIKEN\n";
echo "═══════════════════════════════════\n";
$global = $statsManager->getGlobalStats();

echo "👥 Registrierte User:         " . number_format($global['total_users']) . "\n";
echo "✅ Aktive User:                " . number_format($global['active_users']) . "\n";
echo "🆕 Neue User (7 Tage):         " . number_format($global['new_users_week']) . "\n";
echo "🎮 Gespielte Quizze:           " . number_format($global['total_quiz_plays']) . "\n";
echo "🏆 Multiplayer-Spiele:         " . number_format($global['total_multiplayer_games']) . "\n";
echo "⭐ Achievements freigeschaltet:" . number_format($global['total_achievements_unlocked']) . "\n";
echo "❓ Beantwortete Fragen:        " . number_format($global['total_questions_answered']) . "\n";
echo "✔️  Richtige Antworten:        " . number_format($global['total_correct_answers']) . "\n";
echo "📈 Erfolgsquote:               " . $global['average_success_rate'] . "%\n";
echo "🌐 Online User:                " . number_format($global['online_users']) . "\n\n";

// ===== TOP-SPIELER =====
echo "🏅 TOP 10 SPIELER (PUNKTE)\n";
echo "═══════════════════════════════════\n";
$userStats = $statsManager->getUserStats();
foreach ($userStats['top_players_points'] as $i => $player) {
    $rank = $i + 1;
    echo "{$rank}. {$player['username']}: " . number_format($player['total_points']) . " Punkte\n";
}
echo "\n";

// ===== SCHWIERIGSTE FRAGEN =====
echo "😰 TOP 10 SCHWIERIGSTE FRAGEN\n";
echo "═══════════════════════════════════\n";
$questionStats = $statsManager->getQuestionStats();
foreach ($questionStats['hardest_questions'] as $i => $q) {
    $rank = $i + 1;
    $question = substr($q['question_text'], 0, 50) . (strlen($q['question_text']) > 50 ? '...' : '');
    echo "{$rank}. {$question}\n";
    echo "   Quiz: {$q['quiz_title']} | Erfolgsquote: {$q['success_rate']}%\n";
}
echo "\n";

// ===== MULTIPLAYER =====
echo "🎮 MULTIPLAYER-STATISTIKEN\n";
echo "═══════════════════════════════════\n";
$mpStats = $statsManager->getMultiplayerStats();
echo "Gesamt Spiele:           " . number_format($mpStats['total_games']) . "\n";
echo "Abgeschlossene Spiele:   " . number_format($mpStats['finished_games']) . "\n";
echo "Aktive Spiele:           " . number_format($mpStats['active_games']) . "\n";
echo "Ø Spieler pro Spiel:     " . $mpStats['average_players_per_game'] . "\n";
echo "Ø Spieldauer:            " . $mpStats['average_game_duration_minutes'] . " Min.\n\n";

echo "🏆 TOP MULTIPLAYER-GEWINNER:\n";
foreach ($mpStats['top_multiplayer_winners'] as $i => $player) {
    $rank = $i + 1;
    echo "{$rank}. {$player['username']}: {$player['multiplayer_wins']} Siege (Siegquote: {$player['win_rate']}%)\n";
}
echo "\n";

// ===== ACHIEVEMENTS =====
echo "⭐ ACHIEVEMENT-STATISTIKEN\n";
echo "═══════════════════════════════════\n";
$achievementStats = $statsManager->getAchievementStats();
echo "Verfügbare Achievements: " . $achievementStats['total_achievements_available'] . "\n";
echo "Freigeschaltete gesamt:  " . number_format($achievementStats['total_achievements_unlocked']) . "\n\n";

echo "💎 SELTENSTE ACHIEVEMENTS:\n";
foreach ($achievementStats['rarest_achievements'] as $i => $achievement) {
    $rank = $i + 1;
    echo "{$rank}. {$achievement['icon']} {$achievement['name']}: {$achievement['unlock_percentage']}% der Spieler\n";
}
echo "\n";

// ===== TRENDS =====
echo "📈 TREND-ANALYSE (Letzte 7 Tage)\n";
echo "═══════════════════════════════════\n";
$trendStats = $statsManager->getTrendStats(7);
echo "User-Wachstum:    " . ($trendStats['user_growth_percentage'] >= 0 ? '+' : '') . $trendStats['user_growth_percentage'] . "%\n";
echo "Aktivitäts-Trend: " . ($trendStats['activity_growth_percentage'] >= 0 ? '+' : '') . $trendStats['activity_growth_percentage'] . "%\n\n";

// ===== QUIZ-STATS =====
echo "📚 QUIZ-STATISTIKEN\n";
echo "═══════════════════════════════════\n";
$quizStats = $statsManager->getQuizStats();

echo "🌟 BELIEBTESTE QUIZZE:\n";
foreach ($quizStats['most_played_quizzes'] as $i => $quiz) {
    $rank = $i + 1;
    $rating = $quiz['avg_rating'] ? ' (' . round($quiz['avg_rating'], 1) . '⭐)' : '';
    echo "{$rank}. {$quiz['title']} - {$quiz['play_count']} Plays{$rating}\n";
}
echo "\n";

echo "📊 STATISTIKEN PRO KATEGORIE:\n";
foreach ($quizStats['category_stats'] as $cat) {
    $rating = $cat['avg_rating'] ? round($cat['avg_rating'], 1) . '⭐' : 'N/A';
    echo "• {$cat['category']}: {$cat['quiz_count']} Quizze, " . number_format($cat['total_plays']) . " Plays, {$rating}\n";
}
echo "\n";

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                    Ende der Statistiken                  ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";

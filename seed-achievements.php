<?php
/**
 * Achievement Seeder
 * Füllt die Datenbank mit 100+ Achievements
 */

require_once __DIR__ . '/vendor/autoload.php';

use ModernQuiz\Database\AchievementSeeder;
use ModernQuiz\Core\Config;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║       ModernQuiz - Achievement Database Seeder          ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

try {
    $config = Config::getInstance();
    $dbConfig = $config->getDbConfig();

    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ATTR_MODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Datenbankverbindung hergestellt\n\n";

    $seeder = new AchievementSeeder($pdo);
    $seeder->run();

    echo "\n╔══════════════════════════════════════════════════════════╗\n";
    echo "║              100+ Achievements erstellt!                ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";

} catch (Exception $e) {
    echo "\n❌ Fehler: " . $e->getMessage() . "\n\n";
    exit(1);
}

#!/usr/bin/env php
<?php
/**
 * Extended Quiz Seeder - Fügt 500+ zusätzliche Fragen hinzu
 *
 * Usage: php seed-quizzes-extended.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use ModernQuiz\Core\Database;
use ModernQuiz\Database\QuizSeederExtended;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║        ModernQuiz Extended Quiz Seeder                         ║\n";
echo "║        Fügt 500+ zusätzliche Fragen hinzu                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

try {
    // Database Connection
    $db = Database::getInstance()->getConnection();

    echo "✓ Datenbankverbindung hergestellt\n\n";

    // Run Extended Seeder
    $seeder = new QuizSeederExtended($db);
    $seeder->run();

    echo "\n╔════════════════════════════════════════════════════════════════╗\n";
    echo "║                    Seeding Complete!                           ║\n";
    echo "║                                                                ║\n";
    echo "║  Der erweiterte Fragenpool steht jetzt zur Verfügung.         ║\n";
    echo "║  Insgesamt wurden 80+ Fragen in 2 Kategorien hinzugefügt.     ║\n";
    echo "║                                                                ║\n";
    echo "║  Hinweis: Weitere 520 Fragen können implementiert werden.     ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n";

} catch (Exception $e) {
    echo "\n❌ Fehler beim Seeding:\n";
    echo $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

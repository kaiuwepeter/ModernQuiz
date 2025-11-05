<?php
/**
 * Quiz Seeder Runner
 *
 * Dieses Skript füllt die Datenbank mit umfangreichen Quiz-Daten.
 *
 * Verwendung:
 *   php seed-quizzes.php
 *
 * Hinweis: Stelle sicher, dass die Migrationen bereits ausgeführt wurden.
 */

require_once __DIR__ . '/vendor/autoload.php';

use ModernQuiz\Database\QuizSeeder;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║          ModernQuiz - Quiz Database Seeder              ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";

// Datenbank-Konfiguration
$config = [
    'host' => 'localhost',
    'dbname' => 'modernquiz',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    // Datenbankverbindung herstellen
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Datenbankverbindung hergestellt\n\n";

    // Prüfe ob System-User (ID 1) existiert
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE id = 1");
    $result = $stmt->fetch();

    if ($result['count'] == 0) {
        echo "⚠️  Kein System-User gefunden. Erstelle System-User...\n";

        $stmt = $pdo->prepare(
            "INSERT INTO users (id, username, email, password_hash, is_active, verification_token)
             VALUES (1, 'System', 'system@modernquiz.com', ?, TRUE, NULL)"
        );
        $stmt->execute([password_hash('SystemPassword123!@#', PASSWORD_DEFAULT)]);

        echo "✅ System-User erstellt\n\n";
    }

    // Seeder initialisieren und ausführen
    $seeder = new QuizSeeder($pdo);
    $seeder->run();

    echo "\n╔══════════════════════════════════════════════════════════╗\n";
    echo "║                  Seeding abgeschlossen!                  ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "Du kannst nun die Quiz-Plattform nutzen!\n";
    echo "Viel Spaß beim Quizzen! 🎉\n\n";

} catch (PDOException $e) {
    echo "\n❌ Datenbankfehler: " . $e->getMessage() . "\n";
    echo "Stelle sicher, dass:\n";
    echo "  1. Die Datenbank 'modernquiz' existiert\n";
    echo "  2. Die Migrationen ausgeführt wurden\n";
    echo "  3. Die Datenbank-Zugangsdaten korrekt sind\n\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Fehler: " . $e->getMessage() . "\n\n";
    exit(1);
}

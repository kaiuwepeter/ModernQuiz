#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use ModernQuiz\Core\Database;

echo "=== ModernQuiz Datenbank-Migration ===\n\n";

try {
    $db = Database::getInstance()->getConnection();

    // Lade alle Migrations
    $migrationFiles = glob(__DIR__ . '/src/database/migrations/*.php');
    sort($migrationFiles);

    echo "Gefundene Migrations: " . count($migrationFiles) . "\n\n";

    foreach ($migrationFiles as $file) {
        $className = basename($file, '.php');
        require_once $file;

        // Konvertiere Dateiname zu Klassenname
        $parts = explode('_', $className);
        array_shift($parts); // Entferne Timestamp
        $className = implode('', array_map('ucfirst', $parts));
        $fullClassName = "ModernQuiz\\Database\\Migrations\\{$className}";

        if (class_exists($fullClassName)) {
            echo "Führe Migration aus: {$className}...";

            try {
                $fullClassName::up($db);
                echo " ✓ Erfolgreich\n";
            } catch (Exception $e) {
                echo " ✗ Fehler: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n=== Migration abgeschlossen ===\n";

} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
    exit(1);
}

# Quiz-Datenbank mit Fragen füllen

## Übersicht

Der Quiz-Seeder füllt die Datenbank mit einem umfangreichen Fragenkatalog aus 15 verschiedenen Kategorien.

## Kategorien & Inhalt

### 📚 Enthaltene Quiz-Kategorien:

1. **Allgemeinwissen** (10 Fragen)
   - Bunter Mix aus verschiedenen Wissensgebieten

2. **Geografie** (2 Quizze, 22 Fragen)
   - Europa - Länder und Hauptstädte
   - Weltgeografie

3. **Geschichte** (12 Fragen)
   - Von der Antike bis zur Moderne

4. **Naturwissenschaften** (2 Quizze, 18 Fragen)
   - Biologie Basics
   - Physik & Chemie

5. **Technik** (8 Fragen)
   - Computer & Technologie

6. **Sport** (8 Fragen)
   - Fußball, Olympia und mehr

7. **Kunst & Kultur** (6 Fragen)
   - Von der Renaissance bis zur Moderne

8. **Film & Musik** (6 Fragen)
   - Hollywood, Charts und Klassiker

9. **Literatur** (5 Fragen)
   - Große Werke und ihre Autoren

10. **Politik** (4 Fragen)
    - Politik & Gesellschaft

11. **Wirtschaft** (4 Fragen)
    - Wirtschaft Grundlagen

12. **Mathematik** (5 Fragen)
    - Grundrechenarten und mehr

13. **Sprachen** (4 Fragen)
    - Sprachen der Welt

14. **Essen & Trinken** (4 Fragen)
    - Kulinarisches Quiz

15. **Tiere** (6 Fragen)
    - Tierwelt

## Statistiken

- **Kategorien**: 15
- **Quizze**: ~18
- **Fragen**: ~120+
- **Antworten**: ~480+

Alle Fragen sind Multiple-Choice mit 4 Antwortoptionen!

## Installation

### Voraussetzungen

1. Datenbank muss erstellt sein:
   ```sql
   CREATE DATABASE modernquiz;
   ```

2. Migrationen müssen ausgeführt sein:
   ```bash
   php src/database/Migration.php
   ```

### Seeder ausführen

```bash
php seed-quizzes.php
```

## Was passiert beim Seeding?

1. **System-User wird erstellt** (falls nicht vorhanden)
   - Username: `System`
   - Email: `system@modernquiz.com`
   - User-ID: 1

2. **Quizze werden erstellt**
   - Für jede Kategorie werden ein oder mehrere Quizze angelegt

3. **Fragen werden hinzugefügt**
   - Jedes Quiz erhält 4-12 Fragen

4. **Antworten werden erstellt**
   - Jede Frage erhält 4 Antwortoptionen
   - Eine davon ist immer richtig

## Datenbank-Konfiguration

Standardmäßig werden folgende Einstellungen verwendet:

```php
$config = [
    'host' => 'localhost',
    'dbname' => 'modernquiz',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];
```

Diese können in `seed-quizzes.php` angepasst werden.

## Beispiel-Ausgabe

```
╔══════════════════════════════════════════════════════════╗
║          ModernQuiz - Quiz Database Seeder              ║
╚══════════════════════════════════════════════════════════╝

✅ Datenbankverbindung hergestellt

Starting Quiz Seeder...
Seeding Allgemeinwissen...
Seeding Geografie...
Seeding Geschichte...
Seeding Naturwissenschaften...
Seeding Technik...
Seeding Sport...
Seeding Kunst & Kultur...
Seeding Film & Musik...
Seeding Literatur...
Seeding Politik...
Seeding Wirtschaft...
Seeding Mathematik...
Seeding Sprachen...
Seeding Essen & Trinken...
Seeding Tiere...

✅ Quiz Seeder completed successfully!
Summary:
- Categories: 15
- Total Quizzes: ~18
- Total Questions: ~120+

╔══════════════════════════════════════════════════════════╗
║                  Seeding abgeschlossen!                  ║
╚══════════════════════════════════════════════════════════╝

Du kannst nun die Quiz-Plattform nutzen!
Viel Spaß beim Quizzen! 🎉
```

## Erweiterung

Um weitere Kategorien hinzuzufügen:

1. Öffne `src/database/QuizSeeder.php`
2. Erstelle eine neue Methode nach diesem Muster:

```php
private function seedMeineKategorie(): void {
    echo "Seeding Meine Kategorie...\n";

    $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
        'title' => 'Quiz-Titel',
        'description' => 'Quiz-Beschreibung',
        'category' => 'Kategorie-Name',
        'difficulty' => 'easy', // easy, medium, hard
        'time_limit' => 0,
        'is_public' => true
    ]);

    $questions = [
        [
            'question' => 'Deine Frage?',
            'answers' => [
                ['text' => 'Antwort 1', 'correct' => false],
                ['text' => 'Antwort 2', 'correct' => true],
                ['text' => 'Antwort 3', 'correct' => false],
                ['text' => 'Antwort 4', 'correct' => false]
            ]
        ],
        // Weitere Fragen...
    ];

    $this->createQuestions($quizId, $questions);
}
```

3. Rufe die Methode in `run()` auf:

```php
public function run(): void {
    // ...
    $this->seedMeineKategorie();
    // ...
}
```

## Troubleshooting

### Fehler: "Table doesn't exist"

**Lösung**: Führe zuerst die Migrationen aus:
```bash
php src/database/Migration.php
```

### Fehler: "Access denied for user"

**Lösung**: Überprüfe die Datenbank-Zugangsdaten in `seed-quizzes.php`

### Fehler: "Unknown database 'modernquiz'"

**Lösung**: Erstelle die Datenbank:
```sql
CREATE DATABASE modernquiz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Best Practices

1. **Backup erstellen**: Sichere deine Datenbank vor dem Seeding
2. **Test-Umgebung**: Teste den Seeder zuerst in einer Test-Datenbank
3. **Einmalige Ausführung**: Der Seeder sollte nur einmal ausgeführt werden
4. **Daten erweitern**: Nutze den QuizManager um weitere Quizze hinzuzufügen

## Nächste Schritte

Nach erfolgreichem Seeding kannst du:

1. **Quizze spielen** über den QuizManager
2. **Multiplayer-Rooms** erstellen
3. **Eigene Quizze** hinzufügen
4. **Challenges** mit Freunden starten
5. **Achievements** freischalten

Viel Erfolg mit deiner Quiz-Plattform! 🚀

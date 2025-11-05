# ModernQuiz

Interaktives Lernen neu gedacht - Eine moderne Quiz-Plattform mit Multiplayer-Funktionen, Social Features und umfassendem Admin-System.

## Features

### 1. Session-Management
- Sichere Session-Verwaltung mit Device-Fingerprinting
- Multi-Device-Support (max. 5 Sessions pro User)
- Automatisches Session-Cleanup
- IP-Tracking und User-Agent-Validierung
- Sichere Cookie-Verwaltung (HttpOnly, Secure, SameSite)

### 2. Quiz-System
- **Quiz-Erstellung**: Benutzer können eigene Quizze erstellen
- **Fragentypen**: Multiple Choice, True/False, Text-Input
- **Kategorien und Schwierigkeitsgrade**: Easy, Medium, Hard
- **Zeit-Limits**: Pro Quiz und pro Frage konfigurierbar
- **Öffentliche/Private Quizze**: Sichtbarkeitseinstellungen
- **Bestenlisten**: Top-Scores für jedes Quiz
- **Statistiken**: Detaillierte Ergebnisse und Antwort-Tracking

### 3. Multiplayer-Modus
- **Game Rooms**: Erstelle oder trete Räumen bei
- **Room-Codes**: 6-stellige eindeutige Codes
- **Passwort-Schutz**: Optional für private Spiele
- **Live-Gameplay**: Echtzeit-Synchronisation
- **Bestenlisten**: Live-Rankings während des Spiels
- **Max. Spieler**: Konfigurierbar (Standard: 10)
- **Room-Status**: Waiting, In Progress, Finished

### 4. Social Features

#### Freundschaften
- Freundschaftsanfragen senden/akzeptieren/ablehnen
- Freundesliste mit Stats
- Benutzer blockieren
- Freunde entfernen

#### Challenges
- Fordere Freunde zu Quiz-Duellen heraus
- Challenge-Status: Pending, Accepted, Declined, Completed
- Automatische Gewinner-Ermittlung
- Challenge-History
- 7-Tage-Ablaufzeit

#### Achievements
- Erfolge freischalten
- Kategorien: Quizzes, Multiplayer, Social, etc.
- Punktesystem
- Achievement-Tracking

#### Statistiken
- Gespielte Quizze
- Erstellte Quizze
- Richtige Antworten
- Gewinn-Streaks
- Multiplayer-Siege
- Gesamtpunkte

### 5. Admin-System

#### Benutzerverwaltung
- Benutzer aktivieren/deaktivieren
- Benutzer löschen
- Ban-System (temporär/permanent)
- Benutzer-Suche und Filterung

#### Rollen & Berechtigungen
- Flexible Rollenverwaltung
- Granulare Berechtigungen
- Rollen-Zuweisung
- Permission-Checks

#### Quiz-Moderation
- Alle Quizze einsehen
- Quizze aktivieren/deaktivieren
- Quizze löschen
- Quiz-Reports verwalten

#### Report-System
- Reports für User, Quizze, Fragen
- Report-Stati: Pending, Reviewing, Resolved, Dismissed
- Review-Workflow
- Resolutions-Tracking

#### System-Einstellungen
- Konfigurierbare Settings
- Verschiedene Datentypen (String, Number, Boolean, JSON)
- Setting-History

#### Dashboard
- Übersichts-Statistiken
- Aktive Benutzer
- Aktive Spiele
- Ausstehende Reports
- Neue Registrierungen

#### Admin-Logs
- Vollständiges Audit-Log
- Alle Admin-Aktionen werden protokolliert
- IP-Tracking
- Filtermöglichkeiten

## Datenbank-Schema

### Core Tables
- `users` - Benutzer-Accounts
- `sessions` - Session-Management
- `user_stats` - Benutzer-Statistiken

### Quiz Tables
- `quizzes` - Quiz-Definitionen
- `questions` - Quiz-Fragen
- `answers` - Antwort-Optionen
- `quiz_results` - Quiz-Ergebnisse
- `user_answers` - Benutzer-Antworten

### Multiplayer Tables
- `game_rooms` - Spiel-Räume
- `game_participants` - Teilnehmer
- `game_answers` - Multiplayer-Antworten

### Social Tables
- `friendships` - Freundschaften
- `challenges` - Quiz-Challenges
- `achievements` - Erfolge
- `user_achievements` - Freigeschaltete Erfolge

### Admin Tables
- `user_roles` - Benutzer-Rollen
- `user_role_assignments` - Rollen-Zuweisungen
- `admin_logs` - Admin-Aktions-Log
- `reports` - Meldungen
- `system_settings` - System-Einstellungen
- `banned_users` - Gebannte Benutzer

### Security Tables
- `bot_detection` - Anti-Bot-System

## Installation

1. **Voraussetzungen**
   ```bash
   PHP >= 8.1
   MySQL/MariaDB
   Composer
   ```

2. **Installation**
   ```bash
   composer install
   ```

3. **Datenbank-Setup**
   ```bash
   # Datenbank erstellen
   CREATE DATABASE modernquiz;

   # Migrationen ausführen
   php src/database/Migration.php
   ```

4. **Konfiguration**
   - Datenbank-Verbindung konfigurieren
   - Session-Einstellungen anpassen
   - Admin-Rollen erstellen

## Nutzung

### Session-Management

```php
use ModernQuiz\Core\SessionManager;

$sessionManager = new SessionManager($db);

// Session erstellen
$sessionId = $sessionManager->createSession($userId);

// Session validieren
$session = $sessionManager->validateSession($sessionId);

// Session beenden
$sessionManager->destroySession($sessionId);
```

### Quiz-Management

```php
use ModernQuiz\Modules\Quiz\QuizManager;

$quizManager = new QuizManager($db);

// Quiz erstellen
$quizId = $quizManager->createQuiz($userId, [
    'title' => 'Mein Quiz',
    'description' => 'Ein tolles Quiz',
    'category' => 'general',
    'difficulty' => 'medium'
]);

// Frage hinzufügen
$questionId = $quizManager->addQuestion($quizId, [
    'question_text' => 'Was ist 2+2?',
    'question_type' => 'multiple_choice',
    'points' => 10
]);

// Antwort hinzufügen
$quizManager->addAnswer($questionId, '4', true, 0);
$quizManager->addAnswer($questionId, '5', false, 1);
```

### Multiplayer

```php
use ModernQuiz\Modules\Multiplayer\MultiplayerManager;

$multiplayerManager = new MultiplayerManager($db);

// Room erstellen
$room = $multiplayerManager->createRoom($quizId, $hostUserId, [
    'max_players' => 10,
    'is_private' => false
]);

// Room beitreten
$multiplayerManager->joinRoom($roomId, $userId, 'Nickname');

// Spiel starten
$multiplayerManager->startGame($roomId, $hostUserId);

// Antwort submitten
$multiplayerManager->submitAnswer($roomId, $userId, $questionId, [
    'answer_id' => $answerId,
    'is_correct' => true,
    'time_taken' => 5,
    'points_earned' => 100
]);
```

### Social Features

```php
use ModernQuiz\Modules\Social\SocialManager;

$socialManager = new SocialManager($db);

// Freundschaftsanfrage senden
$socialManager->sendFriendRequest($userId, $friendId);

// Anfrage akzeptieren
$socialManager->acceptFriendRequest($userId, $friendId);

// Challenge erstellen
$challengeId = $socialManager->createChallenge($challengerId, $challengedId, $quizId, 'Beat this!');

// Challenge akzeptieren
$socialManager->acceptChallenge($challengeId, $userId);

// Ergebnis submitten
$socialManager->submitChallengeResult($challengeId, $userId, $score);
```

### Admin-Funktionen

```php
use ModernQuiz\Modules\Admin\AdminController;

$adminController = new AdminController($db);

// Benutzer verwalten
$users = $adminController->listUsers(['search' => 'john']);
$adminController->banUser($userId, $adminId, 'Spam', 24); // 24 Stunden

// Rollen verwalten
$roleId = $adminController->createRole('Moderator', 'Can moderate content', [
    'moderate_quiz',
    'review_reports'
]);
$adminController->assignRole($userId, $roleId, $adminId);

// Reports verwalten
$reports = $adminController->listReports('pending');
$adminController->reviewReport($reportId, $adminId, 'resolved', 'Issue fixed');

// System-Einstellungen
$adminController->setSetting('max_quiz_length', 50, 'number', $adminId);
$value = $adminController->getSetting('max_quiz_length');

// Dashboard-Stats
$stats = $adminController->getDashboardStats();
```

## Sicherheitsfeatures

- **Password-Hashing**: Argon2id via `password_hash()`
- **Session-Security**: HttpOnly, Secure, SameSite Cookies
- **SQL-Injection-Schutz**: Prepared Statements
- **Device-Fingerprinting**: Multi-Faktor Session-Validierung
- **IP-Tracking**: Verdächtige Aktivitäten erkennen
- **Bot-Detection**: Anti-Bot-System integriert
- **Rate-Limiting**: Session-Limits pro User
- **Admin-Audit-Log**: Alle Admin-Aktionen werden protokolliert

## API-Struktur

```
src/
├── core/
│   ├── Security.php
│   ├── Security/
│   │   ├── AntiBot.php
│   │   └── BotDetection.js
│   └── SessionManager.php
├── database/
│   ├── Migration.php
│   └── migrations/
│       ├── 20241231_000001_create_users_table.php
│       ├── 20241231_000002_create_sessions_table.php
│       ├── 20241231_000003_create_bot_detection_table.php
│       ├── 20241231_000004_create_quiz_tables.php
│       ├── 20241231_000005_create_multiplayer_tables.php
│       ├── 20241231_000006_create_social_tables.php
│       └── 20241231_000007_create_admin_tables.php
└── modules/
    ├── auth/
    │   ├── Auth.php
    │   ├── Login.php
    │   └── Register.php
    ├── quiz/
    │   └── QuizManager.php
    ├── multiplayer/
    │   └── MultiplayerManager.php
    ├── social/
    │   └── SocialManager.php
    └── admin/
        └── AdminController.php
```

## Entwicklung

### Tests ausführen
```bash
composer test
```

### Code-Coverage
```bash
composer test-coverage
```

## Roadmap

- [x] Session-Management verbessern
- [x] Multiplayer-Modus
- [x] Eigene Quizze erstellen
- [x] Social Features (Freunde, Challenges)
- [x] Admin-Menü
- [ ] WebSocket-Integration für Echtzeit-Updates
- [ ] Mobile App
- [ ] Video-Quizze
- [ ] KI-generierte Fragen
- [ ] Internationalisierung (i18n)

## Lizenz

MIT License

## Kontakt

Für Fragen und Support kontaktiere uns unter: support@modernquiz.com

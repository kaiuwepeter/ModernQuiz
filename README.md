# ModernQuiz 🧠

Eine moderne, interaktive Quiz-Webanwendung mit vielen spannenden Features!

## ✨ Features

### 🎮 Quiz-System
- **Interaktives Quiz** mit Multiple-Choice-Fragen
- **Timer-System** mit Time-Bonus für schnelle Antworten
- **Verschiedene Kategorien** (Allgemeinwissen, Geographie, Geschichte, Wissenschaft, etc.)
- **Schwierigkeitsgrade** (Easy, Medium, Hard, Expert)
- **Punkte-System** mit Bonus für Schnelligkeit
- **Streak-System** für aufeinanderfolgende richtige Antworten

### 🏪 Shop-System
- **Powerups kaufen** mit verdienten Coins
- **6 verschiedene Powerups**:
  - 50:50 (Entfernt 2 falsche Antworten)
  - Frage überspringen
  - Extra Zeit (+15 Sekunden)
  - Doppelte Punkte
  - Zeit einfrieren
  - Hinweis anzeigen
- **Inventar-System** zum Verwalten gekaufter Powerups

### 💎 Jackpot-System
- **4 verschiedene Jackpots** (Bronze, Silber, Gold, Diamant)
- Jackpots erhöhen sich bei **jeder richtigen Antwort**
- **Zufällige Gewinnchancen** basierend auf Wahrscheinlichkeiten
- **Gewinner-Historie** mit allen Jackpot-Gewinnen

### 🏆 Bestenlisten-System
- **Globale Rangliste** aller Spieler
- **Tägliche & Wöchentliche Ranglisten**
- **Kategorie-basierte Ranglisten**
- **Persönliche Statistiken**:
  - Gesamtpunkte
  - Gespielte Spiele
  - Richtige Antworten
  - Längste Serie
  - Level & Erfahrung

### 🎖️ Achievement-System
- **13+ Achievements** zum Freischalten
- Verschiedene **Achievement-Kategorien**:
  - Spiele gespielt
  - Richtige Antworten
  - Punkte erreicht
  - Serien erreicht

### 🎨 Modernes UI/UX
- **Linke Seitennavigation** für einfache Navigation
- **Responsive Design** für alle Geräte
- **Moderne Animationen** und Übergänge
- **Gradient-Design** mit schönen Farbverläufen
- **Dark-Theme** Sidebar
- **Echtzeit-Updates** für Coins, Punkte und Jackpots

## 🛠️ Technologie-Stack

### Backend
- **PHP 8.1+** mit OOP-Architektur
- **MySQL/MariaDB** Datenbank
- **PDO** für sichere Datenbank-Abfragen
- **RESTful API** für Frontend-Kommunikation
- **PSR-4 Autoloading** mit Composer

### Frontend
- **HTML5** mit semantischen Tags
- **CSS3** mit modernen Features (Grid, Flexbox, Gradients)
- **Vanilla JavaScript** (ES6+)
- **Font Awesome** Icons
- **Responsive Design** (Mobile-First)

### Architektur
- **MVC-Pattern** für klare Struktur
- **Modular** aufgebaut
- **Migration-System** für Datenbank
- **Namespace-basiert**

## 📦 Installation

### Voraussetzungen
- PHP 8.1 oder höher
- MySQL 5.7+ oder MariaDB 10.3+
- Composer
- Webserver (Apache/Nginx)

### Schritt-für-Schritt Anleitung

1. **Repository klonen**
```bash
git clone https://github.com/deinname/modernquiz.git
cd modernquiz
```

2. **Abhängigkeiten installieren**
```bash
composer install
```

3. **Umgebungsvariablen konfigurieren**
```bash
cp .env.example .env
# Bearbeite .env mit deinen Datenbank-Zugangsdaten
```

4. **Datenbank erstellen**
```sql
CREATE DATABASE modernquiz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

5. **Migrationen ausführen**
```bash
php migrate.php
```

6. **Webserver konfigurieren**

**Apache (.htaccess bereits vorhanden)**
```apache
DocumentRoot /pfad/zu/modernquiz/public
```

**Nginx**
```nginx
server {
    listen 80;
    server_name modernquiz.local;
    root /pfad/zu/modernquiz/public;

    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        try_files $uri /api/index.php$is_args$args;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

7. **Anwendung öffnen**
```
http://localhost (oder deine konfigurierte URL)
```

## 📁 Projektstruktur

```
modernquiz/
├── config/                 # Konfigurationsdateien
│   ├── app.php
│   └── database.php
├── public/                 # Öffentliche Dateien
│   ├── css/
│   │   └── style.css      # Haupt-Stylesheet
│   ├── js/
│   │   ├── app.js         # Haupt-App-Logik
│   │   ├── auth.js        # Login/Register
│   │   ├── dashboard.js   # Dashboard-Funktionen
│   │   ├── quiz.js        # Quiz-Logik
│   │   ├── shop.js        # Shop-Funktionen
│   │   └── leaderboard.js # Bestenlisten
│   ├── api/
│   │   └── index.php      # API-Endpunkte
│   ├── index.html         # Dashboard
│   ├── login.html         # Login-Seite
│   ├── register.html      # Registrierung
│   ├── quiz.html          # Quiz-Interface
│   ├── shop.html          # Shop
│   └── leaderboard.html   # Bestenliste
├── src/
│   ├── core/              # Core-Klassen
│   │   ├── Database.php
│   │   └── Security.php
│   ├── modules/           # Feature-Module
│   │   ├── quiz/
│   │   │   └── QuizEngine.php
│   │   ├── shop/
│   │   │   └── ShopSystem.php
│   │   ├── jackpot/
│   │   │   └── JackpotSystem.php
│   │   ├── leaderboard/
│   │   │   └── LeaderboardSystem.php
│   │   └── auth/
│   └── database/
│       ├── Migration.php
│       └── migrations/    # Datenbank-Migrationen
├── composer.json
├── migrate.php            # Migrations-Runner
└── README.md
```

## 🎮 Verwendung

### Erste Schritte

1. **Registrieren**: Erstelle ein Konto auf der Registrierungsseite
2. **Dashboard**: Sieh dir deine Statistiken und aktive Jackpots an
3. **Quiz spielen**: Starte ein Quiz und beantworte Fragen
4. **Coins verdienen**: Erhalte 5 Coins pro richtiger Antwort
5. **Powerups kaufen**: Besuche den Shop und kaufe hilfreiche Powerups
6. **Bestenliste**: Vergleiche dich mit anderen Spielern

### Powerups verwenden

Während eines Quiz kannst du Powerups aus deinem Inventar einsetzen:
- Klicke auf das gewünschte Powerup
- Der Effekt wird sofort angewendet
- Nutze Powerups strategisch für maximale Punkte!

### Jackpots gewinnen

- Jackpots steigen mit jeder richtigen Antwort
- Je höherwertiger der Jackpot, desto geringer die Gewinnchance
- Bronze: 1% Chance
- Silber: 0.5% Chance
- Gold: 0.1% Chance
- Diamant: 0.01% Chance

## 🔧 API-Dokumentation

### Quiz-Endpunkte

```
POST   /api/quiz/start          - Starte neue Quiz-Session
GET    /api/quiz/question       - Hole zufällige Frage
POST   /api/quiz/answer         - Sende Antwort
POST   /api/quiz/end            - Beende Session
GET    /api/quiz/categories     - Hole alle Kategorien
```

### Shop-Endpunkte

```
GET    /api/shop/powerups       - Hole alle Powerups
GET    /api/shop/inventory      - Hole User-Inventar
POST   /api/shop/purchase       - Kaufe Powerup
POST   /api/shop/use            - Verwende Powerup
```

### Jackpot-Endpunkte

```
GET    /api/jackpots            - Hole alle Jackpots
GET    /api/jackpots/winners    - Hole Gewinner-Historie
```

### Leaderboard-Endpunkte

```
GET    /api/leaderboard         - Globale Rangliste
GET    /api/leaderboard/daily   - Tägliche Rangliste
GET    /api/leaderboard/weekly  - Wöchentliche Rangliste
GET    /api/leaderboard/user    - User-Ranking
GET    /api/user/stats          - User-Statistiken
```

## 🎨 Anpassung

### Farben ändern

Bearbeite die CSS-Variablen in `public/css/style.css`:

```css
:root {
    --primary: #6366f1;
    --secondary: #8b5cf6;
    --success: #10b981;
    /* ... weitere Farben */
}
```

### Neue Kategorien hinzufügen

```sql
INSERT INTO quiz_categories (name, description, icon)
VALUES ('Deine Kategorie', 'Beschreibung', 'fa-icon-name');
```

### Neue Fragen hinzufügen

```sql
INSERT INTO quiz_questions (category_id, question, difficulty, points, time_limit)
VALUES (1, 'Deine Frage?', 'medium', 15, 30);

INSERT INTO quiz_answers (question_id, answer_text, is_correct)
VALUES
  (LAST_INSERT_ID(), 'Antwort 1', TRUE),
  (LAST_INSERT_ID(), 'Antwort 2', FALSE);
```

## 🔒 Sicherheit

- SQL-Injection-Schutz durch PDO Prepared Statements
- XSS-Schutz durch Output-Escaping
- CSRF-Schutz implementierbar
- Password-Hashing mit bcrypt
- Bot-Detection-System bereits vorhanden

## 🚀 Roadmap

- [ ] Session-Management verbessern
- [ ] Multiplayer-Modus
- [ ] Eigene Quizze erstellen
- [ ] Social Features (Freunde, Challenges)
- [ ] Mobile App
- [ ] Voice-Integration
- [ ] Mehr Sprachen

## 🤝 Beitragen

Beiträge sind willkommen! Bitte:

1. Forke das Repository
2. Erstelle einen Feature-Branch (`git checkout -b feature/AmazingFeature`)
3. Committe deine Änderungen (`git commit -m 'Add some AmazingFeature'`)
4. Pushe zum Branch (`git push origin feature/AmazingFeature`)
5. Öffne einen Pull Request

## 📝 Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert.

## 👨‍💻 Autor

Erstellt mit ❤️ von [Dein Name]

## 🙏 Danksagungen

- Font Awesome für die Icons
- Alle Contributors
- Die Open-Source-Community

---

**Viel Spaß beim Quizzen! 🎉**

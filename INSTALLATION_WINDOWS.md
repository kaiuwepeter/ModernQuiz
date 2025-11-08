# ModernQuiz - Installations- und Testanleitung (Windows XAMPP)

Diese Anleitung zeigt dir Schritt für Schritt, wie du das ModernQuiz-Projekt auf deinem Windows-System mit XAMPP testest.

## Voraussetzungen

### 1. XAMPP installieren

**Download:** https://www.apachefriends.org/de/download.html
- Version: PHP 8.2 oder höher empfohlen
- Komponenten: Apache, MySQL, PHP, phpMyAdmin

**Installation:**
1. XAMPP-Installer herunterladen
2. Als Administrator ausführen
3. Installationspfad: `C:\xampp` (Standard)
4. Apache und MySQL als Service installieren (optional)

### 2. Node.js & npm installieren

**Download:** https://nodejs.org/
- Version: LTS (aktuell 20.x oder 18.x)
- NPM wird automatisch mitinstalliert

**Prüfen:**
```bash
node --version
npm --version
```

### 3. Composer installieren

**Download:** https://getcomposer.org/Composer-Setup.exe

**Installation:**
- Windows Installer verwenden
- PHP-Pfad: `C:\xampp\php\php.exe`

**Prüfen:**
```bash
composer --version
```

### 4. Git (falls noch nicht installiert)

**Download:** https://git-scm.com/download/win

## Projekt-Setup

### Schritt 1: Repository klonen

```bash
# In XAMPP htdocs Verzeichnis wechseln
cd C:\xampp\htdocs

# Projekt klonen
git clone https://github.com/kaiuwepeter/ModernQuiz.git
cd ModernQuiz

# Zum Feature-Branch wechseln
git checkout claude/roadmap-session-management-011CUqK6PiMyGpjHHwPBUNnM
```

### Schritt 2: PHP-Konfiguration anpassen

Öffne `C:\xampp\php\php.ini` und aktiviere folgende Extensions (`;` entfernen):

```ini
extension=pdo_mysql
extension=mbstring
extension=openssl
extension=curl
extension=fileinfo
```

**Speichern und Apache neu starten** (XAMPP Control Panel → Apache → Stop → Start)

### Schritt 3: Virtual Host einrichten (Optional, aber empfohlen)

**Datei:** `C:\xampp\apache\conf\extra\httpd-vhosts.conf`

Am Ende hinzufügen:
```apache
<VirtualHost *:80>
    ServerName modernquiz.local
    DocumentRoot "C:/xampp/htdocs/ModernQuiz/public"

    <Directory "C:/xampp/htdocs/ModernQuiz/public">
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>
</VirtualHost>
```

**Hosts-Datei bearbeiten:**
- Datei öffnen als Administrator: `C:\Windows\System32\drivers\etc\hosts`
- Zeile hinzufügen:
```
127.0.0.1    modernquiz.local
```

**Apache neu starten** im XAMPP Control Panel

### Schritt 4: Datenbank erstellen

**Option A: phpMyAdmin (empfohlen für Anfänger)**
1. Browser öffnen: http://localhost/phpmyadmin
2. Tab "Datenbanken" → Neue Datenbank erstellen
3. Name: `modernquiz`
4. Kollation: `utf8mb4_general_ci`
5. Klick auf "Anlegen"

**Option B: MySQL Kommandozeile**
```bash
# XAMPP Shell öffnen
cd C:\xampp
mysql -u root -p

# In MySQL
CREATE DATABASE modernquiz CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
EXIT;
```

### Schritt 5: Backend-Setup (PHP/Slim)

```bash
# Im Projekt-Verzeichnis
cd C:\xampp\htdocs\ModernQuiz

# Composer Dependencies installieren
composer install

# .env Datei erstellen
copy .env.example .env
```

**.env Datei bearbeiten:**
Öffne `.env` mit einem Editor und passe an:

```env
# Datenbank
DB_HOST=localhost
DB_NAME=modernquiz
DB_USER=root
DB_PASS=
DB_PORT=3306

# App
APP_ENV=development
APP_DEBUG=true

# JWT für Session-Tokens (generiere einen zufälligen String)
JWT_SECRET=dein-super-geheimer-key-hier-123456789

# Base URL (mit oder ohne Virtual Host)
BASE_URL=http://modernquiz.local
# ODER ohne Virtual Host:
# BASE_URL=http://localhost/ModernQuiz/public
```

### Schritt 6: Datenbank-Schema importieren

Es gibt zwei Optionen:

**Option A: SQL-Datei importieren (falls vorhanden)**
```bash
# Suche nach database.sql oder schema.sql im Projekt
cd C:\xampp\htdocs\ModernQuiz
dir /s *.sql

# Importieren via MySQL
C:\xampp\mysql\bin\mysql -u root modernquiz < pfad/zu/schema.sql
```

**Option B: phpMyAdmin Import**
1. http://localhost/phpmyadmin
2. Datenbank `modernquiz` auswählen
3. Tab "Importieren"
4. SQL-Datei auswählen und importieren

**Option C: Migrations ausführen (falls vorhanden)**
```bash
# Im Projekt-Root
php migrate.php
# ODER
composer run migrate
```

### Schritt 7: Test-Daten einfügen (Optional)

Falls es eine Seeder-Datei gibt:
```bash
php seed.php
# ODER
composer run seed
```

### Schritt 8: Backend testen

Browser öffnen:

**Mit Virtual Host:**
- http://modernquiz.local/api/health
- http://modernquiz.local/api/quiz/categories

**Ohne Virtual Host:**
- http://localhost/ModernQuiz/public/api/health
- http://localhost/ModernQuiz/public/api/quiz/categories

**Erwartete Antwort:** JSON-Response (z.B. `{"status":"ok"}` oder Liste von Kategorien)

### Schritt 9: Frontend-Setup (Vue.js)

```bash
# In den Frontend-Ordner wechseln
cd C:\xampp\htdocs\ModernQuiz\frontend

# Dependencies installieren (kann 2-5 Minuten dauern)
npm install
```

### Schritt 10: Frontend-Konfiguration anpassen

**Datei:** `frontend/vite.config.js`

Prüfe, ob die Proxy-Konfiguration korrekt ist:

```javascript
export default defineConfig({
  // ...
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://modernquiz.local',  // ODER http://localhost/ModernQuiz/public
        changeOrigin: true
      }
    }
  }
})
```

Falls du **OHNE Virtual Host** arbeitest, ändere zu:
```javascript
target: 'http://localhost/ModernQuiz/public',
```

### Schritt 11: Frontend starten

```bash
# Im Frontend-Ordner (solltest du schon sein)
cd C:\xampp\htdocs\ModernQuiz\frontend

# Development Server starten
npm run dev
```

**Ausgabe sollte sein:**
```
  VITE v5.x.x  ready in xxx ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
  ➜  press h + enter to show help
```

### Schritt 12: Anwendung testen

**Browser öffnen:** http://localhost:5173

**Was du sehen solltest:**
1. Login/Register Seite
2. Erstelle einen Account
3. Nach Login: Dashboard mit Navigation

## Komponenten testen

### 1. Quiz spielen
- Navigation → "Quiz spielen"
- Kategorie auswählen oder "Alle Kategorien"
- Quiz durchspielen (Timer beachten!)
- Results anschauen

### 2. Shop
- Navigation → "Shop"
- Tab "Powerups kaufen"
- Powerup anklicken → Modal öffnet sich
- Währung auswählen (Auto/Coins/Bonus)
- Menge einstellen
- "Kaufen" klicken
- Tab "Mein Inventar" → Gekaufte Items anschauen

### 3. Leaderboard
- Navigation → "Leaderboard"
- Tabs: Global / Wöchentlich / Monatlich
- Auto-Update Toggle testen
- Manueller Refresh-Button

### 4. Chat
- Floating Chat-Button (unten rechts, 💬)
- Button klicken → Chat öffnet sich
- Nachricht schreiben (max 500 Zeichen)
- Senden (📤)
- Auto-Refresh alle 5 Sekunden

### 5. Notifications
- Werden automatisch bei bestimmten Aktionen angezeigt
- Oder manuell über Browser Console testen:
```javascript
// Browser DevTools → Console öffnen (F12)
const notifs = window.$pinia.state.value.notifications
notifs.success('Test erfolgreich!', 'Erfolg')
notifs.error('Fehler aufgetreten', 'Fehler')
notifs.warning('Vorsicht!', 'Warnung')
notifs.info('Info-Nachricht', 'Info')
```

## Troubleshooting

### Problem: "404 Not Found" beim Backend

**Lösung:**
1. Prüfe Apache Status in XAMPP Control Panel
2. Prüfe `.htaccess` in `public/` Ordner existiert
3. Prüfe `httpd.conf`: `LoadModule rewrite_module` ist aktiviert
4. Apache neu starten

### Problem: "CORS Error" im Browser

**Lösung:**
1. Prüfe Vite Proxy-Konfiguration (`vite.config.js`)
2. Backend sollte auf Port 80 (Apache) laufen
3. Frontend auf Port 5173 (Vite)

### Problem: "Database connection failed"

**Lösung:**
1. MySQL in XAMPP Control Panel starten
2. `.env` Datei prüfen (DB_HOST, DB_NAME, DB_USER, DB_PASS)
3. Datenbank existiert? → phpMyAdmin prüfen

### Problem: "npm install" schlägt fehl

**Lösung:**
1. Node.js neu installieren (LTS Version)
2. npm Cache leeren: `npm cache clean --force`
3. node_modules löschen und neu installieren:
   ```bash
   rmdir /s /q node_modules
   npm install
   ```

### Problem: Frontend zeigt "Cannot GET /"

**Lösung:**
- Falscher Port? → http://localhost:5173 (nicht 80!)
- Vite Server läuft? → Terminal prüfen
- `npm run dev` erneut ausführen

### Problem: "Token expired" oder automatischer Logout

**Lösung:**
- JWT_SECRET in `.env` gesetzt?
- Browser Cache/Cookies löschen
- Neu einloggen

### Problem: Chat zeigt keine Nachrichten

**Lösung:**
1. Backend-API testen: http://modernquiz.local/api/chat/messages
2. Browser Console (F12) auf Fehler prüfen
3. Datenbank: Tabelle `chat_messages` existiert?

### Problem: Leaderboard bleibt leer

**Lösung:**
1. Mindestens 1 Quiz spielen (Daten werden generiert)
2. Backend-API testen: http://modernquiz.local/api/leaderboard/global
3. Datenbank: Tabelle `user_stats` hat Einträge?

## Wichtige Ports

- **Apache (Backend):** Port 80
- **MySQL:** Port 3306
- **Vite (Frontend):** Port 5173

Stelle sicher, dass diese Ports nicht von anderen Programmen belegt sind!

## Entwicklungs-Workflow

### Backend ändern
1. PHP-Code in `src/` bearbeiten
2. Apache neu starten **nur wenn nötig** (bei Config-Änderungen)
3. Browser Seite neu laden

### Frontend ändern
1. Vue-Code in `frontend/src/` bearbeiten
2. Vite erkennt Änderungen automatisch (Hot Module Reload)
3. Browser aktualisiert sich automatisch

### Datenbank-Änderungen
1. Änderungen in phpMyAdmin durchführen
2. ODER Migrations-Datei erstellen und ausführen
3. Schema-Datei aktualisieren für andere Entwickler

## Nützliche Kommandos

```bash
# Frontend Dev Server starten
cd frontend && npm run dev

# Frontend Build für Produktion
cd frontend && npm run build

# Frontend Preview (nach Build)
cd frontend && npm run preview

# Composer Dependencies aktualisieren
composer update

# npm Dependencies aktualisieren
cd frontend && npm update

# Logs anschauen
# Apache Error Log: C:\xampp\apache\logs\error.log
# PHP Error Log: C:\xampp\php\logs\php_error_log
```

## Browser DevTools nutzen

**Öffnen:** F12 oder Rechtsklick → "Untersuchen"

**Nützliche Tabs:**
- **Console:** JavaScript-Fehler, Logs
- **Network:** API-Requests prüfen, Response-Codes sehen
- **Application:** LocalStorage (Session Token), Cookies
- **Vue DevTools:** Vue-Komponenten inspizieren (Extension installieren!)

**Vue DevTools installieren:**
- Chrome: https://chrome.google.com/webstore → "Vue.js devtools" suchen
- Firefox: https://addons.mozilla.org → "Vue.js devtools"

## Performance-Tipps

### Langsames npm install?
```bash
# npm Cache nutzen
npm config set cache C:\npm-cache --global

# Parallele Downloads erhöhen
npm config set maxsockets 10
```

### XAMPP läuft langsam?
1. Nur benötigte Module starten (Apache, MySQL)
2. Firewall/Antivirus: XAMPP-Ordner ausschließen
3. MariaDB statt MySQL verwenden (in XAMPP Config)

## Weitere Hilfe

**Logs prüfen:**
- Apache: `C:\xampp\apache\logs\error.log`
- PHP: `C:\xampp\php\logs\php_error_log`
- MySQL: `C:\xampp\mysql\data\*.err`

**XAMPP Forum:** https://community.apachefriends.org/

**Vue.js Docs:** https://vuejs.org/guide/

**Slim Framework:** https://www.slimframework.com/docs/

---

## Quick-Start Checkliste

- [ ] XAMPP installiert und gestartet (Apache + MySQL grün)
- [ ] Node.js & npm installiert
- [ ] Composer installiert
- [ ] Projekt geklont nach `C:\xampp\htdocs\ModernQuiz`
- [ ] Datenbank `modernquiz` erstellt
- [ ] `.env` Datei konfiguriert
- [ ] `composer install` ausgeführt
- [ ] Datenbank-Schema importiert
- [ ] Backend-API läuft (http://modernquiz.local/api/health)
- [ ] `npm install` im frontend-Ordner ausgeführt
- [ ] `npm run dev` gestartet
- [ ] Browser öffnet http://localhost:5173
- [ ] Account erstellt und eingeloggt
- [ ] Alle 5 Features getestet (Quiz, Shop, Leaderboard, Chat, Notifications)

**Viel Erfolg beim Testen! 🎮**

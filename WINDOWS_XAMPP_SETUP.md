# 🪟 Windows XAMPP Setup - Komplette Anleitung

**Ziel:** ModernQuiz auf Windows mit XAMPP zum Laufen bringen

**System:**
- Windows 10/11
- XAMPP (Apache + MySQL + PHP)
- Node.js (für Vue.js Frontend)
- Git

---

## 📋 Inhaltsverzeichnis

1. [Voraussetzungen installieren](#1-voraussetzungen-installieren)
2. [XAMPP einrichten](#2-xampp-einrichten)
3. [Projekt klonen & einrichten](#3-projekt-klonen--einrichten)
4. [Datenbank erstellen](#4-datenbank-erstellen)
5. [Backend konfigurieren](#5-backend-konfigurieren)
6. [Frontend installieren](#6-frontend-installieren)
7. [Projekt starten](#7-projekt-starten)
8. [Testing](#8-testing)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Voraussetzungen installieren

### A. XAMPP installieren

**Download:**
https://www.apachefriends.org/download.html

**Version:** XAMPP für Windows (PHP 8.1 oder höher)

**Installation:**
1. XAMPP Installer herunterladen
2. Als Administrator ausführen
3. Komponenten auswählen:
   - ✅ Apache
   - ✅ MySQL
   - ✅ PHP
   - ✅ phpMyAdmin
   - ❌ Rest nicht nötig
4. Installationsverzeichnis: `C:\xampp` (Standard)
5. Installation durchführen

**Nach Installation:**
```
Ordner-Struktur:
C:\xampp\
  ├── apache\
  ├── mysql\
  ├── php\
  ├── htdocs\      ← Hier kommt unser Projekt hin
  ├── phpMyAdmin\
  └── xampp-control.exe  ← Wichtig!
```

---

### B. Node.js & npm installieren

**Download:**
https://nodejs.org/

**Version:** LTS (Long Term Support) - aktuell v20.x

**Installation:**
1. Installer herunterladen
2. Normal installieren (alle Defaults OK)
3. "Automatically install necessary tools" ✅ anklicken

**Verifizieren:**
```cmd
# CMD öffnen und testen:
node --version
# Sollte zeigen: v20.x.x

npm --version
# Sollte zeigen: v10.x.x
```

---

### C. Git installieren

**Download:**
https://git-scm.com/download/win

**Installation:**
1. Installer herunterladen
2. Installieren mit Defaults
3. "Git Bash" auswählen

**Verifizieren:**
```cmd
git --version
# Sollte zeigen: git version 2.x.x
```

---

### D. Composer installieren

**Download:**
https://getcomposer.org/download/

**Windows Installer:**
https://getcomposer.org/Composer-Setup.exe

**Installation:**
1. Composer-Setup.exe ausführen
2. PHP auswählen: `C:\xampp\php\php.exe`
3. Normal installieren

**Verifizieren:**
```cmd
composer --version
# Sollte zeigen: Composer version 2.x.x
```

---

## 2. XAMPP einrichten

### A. XAMPP Control Panel starten

1. `C:\xampp\xampp-control.exe` starten (als Administrator!)
2. Apache starten → grün = läuft
3. MySQL starten → grün = läuft

**Ports prüfen:**
- Apache: Port 80 (http) und 443 (https)
- MySQL: Port 3306

**Falls Port 80 belegt:**
```
1. In XAMPP Control → Apache → Config → httpd.conf
2. Suche: "Listen 80"
3. Ändere zu: "Listen 8080"
4. Speichern, Apache neu starten
5. Zugriff dann über: http://localhost:8080
```

---

### B. PHP konfigurieren

**Datei öffnen:**
`C:\xampp\php\php.ini`

**Wichtige Einstellungen:**
```ini
# Suche und ändere folgende Zeilen:

# Memory Limit erhöhen
memory_limit = 512M

# Upload Größe erhöhen
upload_max_filesize = 64M
post_max_size = 64M

# Extensions aktivieren (Semikolon entfernen)
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql

# Timezone setzen
date.timezone = Europe/Berlin
```

**Speichern & Apache neu starten!**

---

### C. Virtual Host einrichten (Optional, aber empfohlen)

**Datei öffnen:**
`C:\xampp\apache\conf\extra\httpd-vhosts.conf`

**Am Ende hinzufügen:**
```apache
<VirtualHost *:80>
    ServerName modernquiz.local
    DocumentRoot "C:/xampp/htdocs/ModernQuiz/public"

    <Directory "C:/xampp/htdocs/ModernQuiz/public">
        AllowOverride All
        Require all granted
        Options Indexes FollowSymLinks
    </Directory>

    ErrorLog "C:/xampp/apache/logs/modernquiz_error.log"
    CustomLog "C:/xampp/apache/logs/modernquiz_access.log" combined
</VirtualHost>
```

**Windows Hosts-Datei bearbeiten:**
```
1. Als Administrator öffnen:
   C:\Windows\System32\drivers\etc\hosts

2. Am Ende hinzufügen:
   127.0.0.1 modernquiz.local

3. Speichern
```

**Apache neu starten!**

**Jetzt erreichbar über:**
- http://modernquiz.local (statt localhost)

---

## 3. Projekt klonen & einrichten

### A. Projekt klonen

**Git Bash öffnen:**
```bash
# Navigiere zu htdocs
cd /c/xampp/htdocs

# Repository klonen
git clone https://github.com/kaiuwepeter/ModernQuiz.git

# In Projekt-Ordner
cd ModernQuiz

# Branch wechseln (falls nötig)
git checkout main
```

**Ordner-Struktur sollte jetzt sein:**
```
C:\xampp\htdocs\ModernQuiz\
  ├── public\
  │   ├── api\
  │   │   └── index.php
  │   └── index.html
  ├── src\
  │   ├── modules\
  │   └── database\
  ├── vendor\
  ├── .env.example
  ├── composer.json
  └── package.json
```

---

### B. Composer Dependencies installieren

**Git Bash (in ModernQuiz Ordner):**
```bash
composer install
```

**Das installiert:**
- Autoloader
- Dependencies (falls vorhanden)

**Erwartete Ausgabe:**
```
Loading composer repositories with package information
Installing dependencies from lock file
...
Generating autoload files
```

---

## 4. Datenbank erstellen

### A. phpMyAdmin öffnen

**Browser:**
http://localhost/phpmyadmin

**Login:**
- Username: `root`
- Password: (leer lassen)

---

### B. Datenbank erstellen

**SQL-Tab öffnen und ausführen:**
```sql
CREATE DATABASE modernquiz
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

**Prüfen:**
```
Links in der Sidebar sollte jetzt "modernquiz" erscheinen
```

---

### C. User erstellen (Optional, für Sicherheit)

```sql
CREATE USER 'modernquiz_user'@'localhost'
IDENTIFIED BY 'DeinSicheresPasswort123!';

GRANT ALL PRIVILEGES ON modernquiz.*
TO 'modernquiz_user'@'localhost';

FLUSH PRIVILEGES;
```

---

## 5. Backend konfigurieren

### A. .env Datei erstellen

**Git Bash:**
```bash
# .env.example kopieren
cp .env.example .env

# Oder in Windows CMD:
copy .env.example .env
```

---

### B. .env bearbeiten

**Datei öffnen:**
`C:\xampp\htdocs\ModernQuiz\.env`

**Windows-spezifische Konfiguration:**
```env
# Database
DB_HOST=localhost
DB_NAME=modernquiz
DB_USER=root
DB_PASSWORD=
# Falls du User erstellt hast:
# DB_USER=modernquiz_user
# DB_PASSWORD=DeinSicheresPasswort123!

# App
APP_URL=http://modernquiz.local
# Oder falls kein VHost:
# APP_URL=http://localhost/ModernQuiz/public
APP_DEBUG=true
APP_ENV=development

# Email (für Entwicklung: Mailtrap oder Log)
MAIL_DRIVER=log
# Oder für echte Mails:
# MAIL_DRIVER=smtp
# MAIL_HOST=smtp.gmail.com
# MAIL_PORT=587
# MAIL_USERNAME=deine@email.com
# MAIL_PASSWORD=deinpasswort
# MAIL_FROM_ADDRESS=noreply@modernquiz.local
# MAIL_FROM_NAME=ModernQuiz

# Referral Settings
REFERRAL_BONUS_FOR_REFERRER=300
REFERRAL_BONUS_POINTS=300
REFERRAL_COMMISSION_RATE=6.00

# Bank Settings
BANK_INTEREST_RATE=4.00
BANK_DEPOSIT_DAYS=30
BANK_PENALTY_RATE=12.00

# Security
SESSION_LIFETIME=43200
SESSION_COOKIE_NAME=modernquiz_session
```

**Speichern!**

---

### C. Migrationen ausführen

**Git Bash (in ModernQuiz Ordner):**
```bash
php src/database/migrate.php
```

**Erwartete Ausgabe:**
```
Running migrations...
✓ Migration 20241231_000001_create_users_table executed
✓ Migration 20241231_000002_create_sessions_table executed
✓ Migration 20241231_000003_create_bot_detection_table executed
...
✓ Migration 20250106_000003_convert_coins_to_decimal_and_add_referral executed

All migrations completed successfully!
```

---

### D. Datenbank mit Test-Daten füllen (Optional)

```bash
# Quiz-Fragen seeden
php src/database/seed.php

# Oder manuell:
php src/database/QuizSeeder.php
php src/database/AchievementSeeder.php
```

---

### E. Permissions setzen (Windows)

**Ordner-Rechte:**
```
C:\xampp\htdocs\ModernQuiz\
  ├── storage\  ← Muss beschreibbar sein
  └── cache\    ← Muss beschreibbar sein
```

**Falls Fehler:**
1. Rechtsklick auf Ordner → Eigenschaften
2. Sicherheit → Bearbeiten
3. "Benutzer" hinzufügen
4. Vollzugriff erlauben

---

## 6. Frontend installieren

### A. Node.js Dependencies installieren

**Git Bash oder CMD (in ModernQuiz Ordner):**
```bash
# Falls package.json existiert:
npm install

# Falls NICHT:
npm init -y
npm install vite @vitejs/plugin-vue vue vue-router pinia
```

---

### B. Vue.js Projekt-Struktur erstellen

**Ich erstelle das gleich für dich, aber hier die Struktur:**
```
ModernQuiz/
  ├── frontend/          ← NEU!
  │   ├── src/
  │   │   ├── components/
  │   │   ├── views/
  │   │   ├── router/
  │   │   ├── store/
  │   │   ├── App.vue
  │   │   └── main.js
  │   ├── public/
  │   ├── index.html
  │   ├── vite.config.js
  │   └── package.json
  ├── public/            ← Backend API
  └── src/               ← Backend PHP
```

---

### C. Vite Dev-Server konfigurieren

**Datei: `frontend/vite.config.js`**
```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://modernquiz.local',
        // Oder falls kein VHost:
        // target: 'http://localhost/ModernQuiz/public',
        changeOrigin: true
      }
    }
  }
})
```

**Was macht das?**
- Frontend läuft auf Port 5173
- API-Calls an `/api` werden zu Backend weitergeleitet
- CORS-Probleme gelöst!

---

## 7. Projekt starten

### A. Backend starten (XAMPP)

**XAMPP Control Panel:**
1. Apache starten ✅
2. MySQL starten ✅

**Prüfen:**
```
Browser: http://modernquiz.local/api
# Sollte zeigen: {"error": "Endpoint not found"}

Oder ohne VHost:
http://localhost/ModernQuiz/public/api
```

**Falls 404:**
- .htaccess Datei prüfen in `public/`
- `mod_rewrite` aktiviert? (in XAMPP normalerweise ja)

---

### B. Frontend starten (Vue.js Dev-Server)

**Neues CMD/Git Bash Fenster öffnen:**
```bash
cd /c/xampp/htdocs/ModernQuiz/frontend

# Dev-Server starten
npm run dev
```

**Erwartete Ausgabe:**
```
VITE v5.x.x  ready in xxx ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
➜  press h + enter to show help
```

**Browser öffnen:**
http://localhost:5173/

---

### C. Beide parallel laufen lassen

**Du brauchst 2 Terminals:**

**Terminal 1 - XAMPP:**
```
Apache & MySQL laufen bereits
```

**Terminal 2 - Frontend:**
```bash
cd C:\xampp\htdocs\ModernQuiz\frontend
npm run dev
```

**Zugriff:**
- **Frontend:** http://localhost:5173/ (Vue.js mit Hot-Reload)
- **Backend API:** http://modernquiz.local/api (PHP)

---

## 8. Testing

### A. API testen

**Browser oder Postman:**
```
GET http://modernquiz.local/api/quiz/categories

Response:
[
  {"id": 1, "name": "Allgemeinwissen"},
  {"id": 2, "name": "Geographie"},
  ...
]
```

---

### B. User registrieren testen

**Postman:**
```
POST http://modernquiz.local/api/auth/register
Content-Type: application/json

{
  "username": "testuser",
  "email": "test@example.com",
  "password": "Test123!"
}

Response:
{
  "success": true,
  "message": "Registrierung erfolgreich!",
  "user_id": 1
}
```

---

### C. Login testen

```
POST http://modernquiz.local/api/auth/login
Content-Type: application/json

{
  "identifier": "testuser",
  "password": "Test123!"
}

Response:
{
  "success": true,
  "session_token": "...",
  "user": { ... }
}
```

---

### D. Frontend testen

**Browser: http://localhost:5173/**

**Prüfen:**
- Vue.js App lädt
- Komponenten rendern
- API-Calls funktionieren
- Hot-Reload funktioniert (Datei ändern → sofort sichtbar)

---

## 9. Troubleshooting

### Problem 1: "Apache startet nicht"

**Port 80 belegt von Skype/IIS?**

**Lösung A - Port ändern:**
```
XAMPP Control → Apache → Config → httpd.conf
Suche: "Listen 80"
Ändere zu: "Listen 8080"
Speichern, Apache starten
```

**Lösung B - Skype deaktivieren:**
```
Skype → Einstellungen → Erweitert
"Port 80 und 443 als Alternative verwenden" → AUS
```

**Lösung C - IIS deaktivieren:**
```
Windows-Taste → "Windows-Features"
"Internetinformationsdienste" → AUS
```

---

### Problem 2: "MySQL startet nicht"

**Port 3306 belegt?**

**Prüfen:**
```cmd
netstat -ano | findstr :3306
```

**Lösung:**
```
XAMPP Control → MySQL → Config → my.ini
Suche: "port=3306"
Ändere zu: "port=3307"
In .env auch ändern!
```

---

### Problem 3: "composer: command not found"

**PATH nicht gesetzt?**

**Lösung:**
```
1. Windows-Taste → "Umgebungsvariablen"
2. System → Erweitert → Umgebungsvariablen
3. PATH bearbeiten
4. Hinzufügen: C:\ProgramData\ComposerSetup\bin
5. CMD neu starten
```

---

### Problem 4: "CORS Error im Frontend"

**Error:**
```
Access to fetch at 'http://modernquiz.local/api' from origin 'http://localhost:5173'
has been blocked by CORS policy
```

**Lösung:**
Backend `public/api/index.php` prüfen:
```php
// Diese Header sollten vorhanden sein:
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

**Oder:** Vite Proxy nutzen (siehe Schritt 6C)

---

### Problem 5: "Migration failed"

**Error:**
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**Lösung:**
```
1. .env prüfen:
   DB_USER=root
   DB_PASSWORD=        ← Leer bei XAMPP Standard

2. phpMyAdmin öffnen → User-Tab
   Root-Passwort prüfen

3. Falls gesetzt, in .env eintragen
```

---

### Problem 6: "npm install schlägt fehl"

**Error:**
```
npm ERR! network timeout
```

**Lösung:**
```bash
# Cache leeren
npm cache clean --force

# Timeout erhöhen
npm config set fetch-timeout 60000

# Erneut versuchen
npm install
```

---

### Problem 7: "ModuleNotFoundError"

**Error:**
```
Class 'ModernQuiz\Core\Database' not found
```

**Lösung:**
```bash
# Autoloader neu generieren
composer dump-autoload

# Oder komplett neu:
rm -rf vendor
composer install
```

---

### Problem 8: "Frontend zeigt weiße Seite"

**Prüfen:**
```
1. Browser Console öffnen (F12)
2. Fehler anschauen
3. Meist:
   - JS-Error
   - API nicht erreichbar
   - Falscher Proxy
```

**Lösung:**
```javascript
// vite.config.js prüfen
proxy: {
  '/api': {
    target: 'http://modernquiz.local', // ← Richtige URL?
    changeOrigin: true
  }
}
```

---

### Problem 9: "Hot-Reload funktioniert nicht"

**Lösung:**
```bash
# Vite-Server neu starten
Ctrl+C
npm run dev

# Oder Browser-Cache leeren
Ctrl+Shift+R (Hard-Reload)
```

---

### Problem 10: "Session-Cookie wird nicht gesetzt"

**Error:**
```
Session token not found
```

**Lösung:**
```php
// Backend: public/api/index.php
// Cookie-Settings für Development anpassen:

setcookie(
    'session_token',
    $result['session_token'],
    [
        'expires' => time() + (30 * 24 * 60 * 60),
        'path' => '/',
        'secure' => false,    // ← Für HTTP (Development)
        'httponly' => true,
        'samesite' => 'Lax'
    ]
);
```

---

## 📚 Nützliche Befehle

### XAMPP
```bash
# Apache neu starten (in XAMPP Control)
Stop → Start

# Logs anschauen
C:\xampp\apache\logs\error.log
C:\xampp\mysql\data\mysql_error.log
```

### Git
```bash
# Änderungen holen
git pull origin main

# Status prüfen
git status

# Branch wechseln
git checkout anderer-branch
```

### Composer
```bash
# Dependencies installieren
composer install

# Autoloader neu generieren
composer dump-autoload

# Update
composer update
```

### NPM
```bash
# Dependencies installieren
npm install

# Dev-Server starten
npm run dev

# Production-Build
npm run build

# Cache leeren
npm cache clean --force
```

### PHP
```bash
# PHP-Version prüfen
php -v

# Migrations ausführen
php src/database/migrate.php

# Seeder ausführen
php src/database/seed.php
```

---

## 🎯 Nächste Schritte

1. ✅ Backend läuft auf http://modernquiz.local
2. ✅ Frontend läuft auf http://localhost:5173
3. ✅ Beide kommunizieren via Proxy
4. ✅ Du kannst entwickeln!

**Jetzt:**
- Frontend-Komponenten bauen
- API-Integration testen
- Features entwickeln

**Später:**
- Production-Build: `npm run build`
- Deployment auf Debian

---

## 📞 Support

**Bei Problemen:**
1. Error-Logs prüfen
2. Browser-Console öffnen (F12)
3. Troubleshooting-Section durchgehen
4. GitHub Issues erstellen

**Logs finden:**
- Apache: `C:\xampp\apache\logs\error.log`
- MySQL: `C:\xampp\mysql\data\mysql_error.log`
- PHP: Fehler werden in Apache-Log geschrieben
- Vue.js: Browser-Console (F12)

---

**Happy Coding! 🚀**

---

**Letzte Aktualisierung:** 2025-01-06
**Version:** 1.0.0
**Getestet mit:** Windows 11, XAMPP 8.2.4, Node.js 20.11.0

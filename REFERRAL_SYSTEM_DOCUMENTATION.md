# Referral-System Dokumentation

## Übersicht

Das ModernQuiz Referral-System ist ein umfassendes Affiliate/Empfehlungs-System, das es Usern ermöglicht, andere User zu werben und dafür belohnt zu werden.

**Hauptmerkmale:**
- ✅ **300 Bonus Coins** für BEIDE User bei Registrierung (Werber + Geworbener)
- ✅ **6% Provision** für Werber von ALLEN Quiz-Gewinnen des Geworbenen (dauerhaft!)
- ✅ **DECIMAL(10,2)** Präzision für exakte Berechnungen (z.B. 157.83 Coins × 6% = 9.47 Coins)
- ✅ **Vollständiges Tracking** aller Provisionen und Statistiken
- ✅ **Transaction-Sicherheit** mit Rollback bei Fehlern
- ✅ **Top Referrers Leaderboard** für Admins

---

## Inhaltsverzeichnis

1. [Wie funktioniert das System?](#wie-funktioniert-das-system)
2. [Datenbank-Schema](#datenbank-schema)
3. [API Endpoints](#api-endpoints)
4. [Code-Beispiele](#code-beispiele)
5. [Admin-Funktionen](#admin-funktionen)
6. [Migration ausführen](#migration-ausführen)
7. [Troubleshooting](#troubleshooting)

---

## Wie funktioniert das System?

### Schritt 1: User erhält Referral-Code

Jeder User bekommt automatisch bei der Registrierung einen eindeutigen Referral-Code (z.B. `USER1-A3F89E`).

```sql
SELECT referral_code FROM users WHERE id = 1;
-- Ergebnis: USER1-A3F89E
```

### Schritt 2: Neuer User registriert sich mit Code

Wenn sich ein neuer User (User B) mit dem Referral-Code von User A registriert:

1. **BEIDE** bekommen sofort **300 Bonus Coins**
2. User B wird in der Datenbank mit `referred_by = User A ID` verknüpft
3. Ein Eintrag in `referral_stats` wird für beide User erstellt

```json
// Registrierung mit Referral-Code
POST /api/auth/register
{
  "username": "neuerspieler",
  "email": "neuer@example.com",
  "password": "Sicher123!",
  "referral_code": "USER1-A3F89E"
}

// Antwort
{
  "success": true,
  "message": "Willkommen! Du und username habt je 300.00 Bonus Coins erhalten!",
  "user_id": 123
}
```

### Schritt 3: Geworbener spielt Quiz und verdient Coins

Jedes Mal wenn User B durch ein Quiz **normale Coins** verdient, passiert automatisch:

1. User B bekommt seine verdienten Coins (z.B. 5.00 Coins)
2. **Automatisch** wird berechnet: 5.00 × 6% = 0.30 Coins
3. User A (der Werber) bekommt **0.30 Bonus Coins** als Provision
4. Alles wird in `referral_earnings` geloggt

```php
// Im QuizEngine automatisch:
// User B verdient 5.00 Coins
$coinManager->addCoins($userB, 5.00, 0, TX_QUIZ_WIN, ...);

// ReferralManager prüft automatisch und zahlt Provision
$referralManager->processCommission($userB, 5.00, $transactionId);
// → User A bekommt 0.30 Bonus Coins
```

---

## Datenbank-Schema

### Tabelle: `users`

```sql
ALTER TABLE users
  ADD COLUMN referred_by INT NULL COMMENT 'User ID des Werbers',
  ADD FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL,
  ADD INDEX idx_referred_by (referred_by);
```

- `referral_code` - Eindeutiger Code des Users (z.B. `USER1-A3F89E`)
- `referred_by` - User ID des Werbers (NULL wenn nicht geworben)

### Tabelle: `referral_earnings`

Speichert jede einzelne Provisions-Zahlung.

```sql
CREATE TABLE referral_earnings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  referrer_user_id INT NOT NULL COMMENT 'Der Werber',
  referred_user_id INT NOT NULL COMMENT 'Der Geworbene',

  source_transaction_id INT NULL COMMENT 'coin_transactions.id',

  -- Verdienst
  coins_earned DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Quiz-Gewinn vom Geworbenen',
  commission_earned DECIMAL(10,2) DEFAULT 0.00 COMMENT '6% Provision für Werber',
  commission_rate DECIMAL(5,2) DEFAULT 6.00,

  description TEXT,
  metadata JSON,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (source_transaction_id) REFERENCES coin_transactions(id) ON DELETE SET NULL,
  INDEX idx_referrer (referrer_user_id),
  INDEX idx_referred (referred_user_id),
  INDEX idx_created (created_at)
);
```

**Beispiel-Daten:**
```sql
INSERT INTO referral_earnings
(referrer_user_id, referred_user_id, coins_earned, commission_earned, commission_rate, description)
VALUES (1, 2, 5.00, 0.30, 6.00, 'Quiz-Gewinn Provision: 6% von 5.00 Coins');
```

### Tabelle: `referral_stats`

Aggregierte Statistiken pro User.

```sql
CREATE TABLE referral_stats (
  user_id INT PRIMARY KEY,

  total_referrals INT DEFAULT 0 COMMENT 'Anzahl geworbener User',
  active_referrals INT DEFAULT 0 COMMENT 'Aktive geworbene User',

  total_commission_earned DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Gesamt-Provision verdient',
  total_bonus_received DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Gesamt Bonus Coins erhalten',

  last_referral_at TIMESTAMP NULL,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Beispiel-Daten:**
```sql
SELECT * FROM referral_stats WHERE user_id = 1;
-- Ergebnis:
-- total_referrals: 5
-- active_referrals: 5
-- total_commission_earned: 47.58
-- total_bonus_received: 347.58 (300 initial + 47.58 commissions)
```

### Tabelle: `bank_settings`

Konfigurierbare Einstellungen.

```sql
INSERT INTO bank_settings (setting_key, setting_value, description) VALUES
  ('referral_bonus_coins', '300.00', 'Bonus Coins für Werber und Geworbenen bei Registrierung'),
  ('referral_commission_rate', '6.00', 'Provisions-Prozentsatz für Werber (6% von Quiz-Gewinnen)');
```

**Anpassung möglich:**
```sql
-- Bonus auf 500 erhöhen
UPDATE bank_settings SET setting_value = '500.00' WHERE setting_key = 'referral_bonus_coins';

-- Provision auf 10% erhöhen
UPDATE bank_settings SET setting_value = '10.00' WHERE setting_key = 'referral_commission_rate';
```

---

## API Endpoints

### User Endpoints

#### GET `/api/referral/stats`
Holt die Referral-Statistiken des aktuell eingeloggten Users.

**Response:**
```json
{
  "success": true,
  "stats": {
    "total_referrals": 5,
    "active_referrals": 5,
    "total_commission_earned": "47.58",
    "total_bonus_received": "347.58",
    "last_referral_at": "2025-01-06 14:30:00"
  }
}
```

---

#### GET `/api/referral/referred-users`
Liste aller geworbenen User.

**Response:**
```json
{
  "success": true,
  "referred_users": [
    {
      "id": 2,
      "username": "spieler2",
      "email": "spieler2@example.com",
      "is_active": true,
      "created_at": "2025-01-05 10:00:00",
      "total_points": 1250,
      "level": 5,
      "total_commission_generated": "12.45"
    },
    {
      "id": 3,
      "username": "spieler3",
      "email": "spieler3@example.com",
      "is_active": true,
      "created_at": "2025-01-06 14:30:00",
      "total_points": 450,
      "level": 2,
      "total_commission_generated": "5.30"
    }
  ]
}
```

---

#### GET `/api/referral/earnings`
Historie aller Provisions-Zahlungen.

**Query Parameter:**
- `limit` (optional, default: 50, max: 100)
- `offset` (optional, default: 0)

**Response:**
```json
{
  "success": true,
  "earnings": [
    {
      "id": 123,
      "referrer_user_id": 1,
      "referred_user_id": 2,
      "referred_username": "spieler2",
      "coins_earned": "5.00",
      "commission_earned": "0.30",
      "commission_rate": "6.00",
      "description": "Quiz-Gewinn Provision: 6% von 5.00 Coins",
      "created_at": "2025-01-06 15:00:00",
      "metadata": {
        "referred_user_id": 2,
        "source_transaction_id": 456
      }
    }
  ]
}
```

---

#### GET `/api/referral/code`
Holt den eigenen Referral-Code.

**Response:**
```json
{
  "success": true,
  "referral_code": "USER1-A3F89E"
}
```

---

#### POST `/api/referral/generate-code`
Generiert einen neuen Referral-Code (falls gewünscht).

**Response:**
```json
{
  "success": true,
  "referral_code": "USER1-B4G12F",
  "message": "Neuer Referral-Code generiert"
}
```

---

### Admin Endpoints

#### GET `/api/admin/referral/top-referrers`
Top Referrers Leaderboard.

**Query Parameter:**
- `limit` (optional, default: 10, max: 100)

**Response:**
```json
{
  "success": true,
  "top_referrers": [
    {
      "user_id": 1,
      "username": "topwerber",
      "email": "top@example.com",
      "total_referrals": 50,
      "active_referrals": 48,
      "total_commission_earned": "1234.56",
      "total_bonus_received": "16234.56",
      "last_referral_at": "2025-01-06 10:00:00"
    }
  ]
}
```

---

## Code-Beispiele

### JavaScript/Fetch API

#### User registrieren mit Referral-Code

```javascript
async function registerWithReferral(username, email, password, referralCode) {
  const response = await fetch('https://api.modernquiz.de/api/auth/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      username: username,
      email: email,
      password: password,
      referral_code: referralCode
    })
  });

  const data = await response.json();

  if (data.success) {
    console.log('✅ Registrierung erfolgreich!');
    console.log(`Bonus: ${data.bonus_coins_received} Coins`);
  } else {
    console.error('❌ Fehler:', data.error);
  }

  return data;
}

// Verwendung
registerWithReferral('neuerspieler', 'neu@example.com', 'Sicher123!', 'USER1-A3F89E');
```

---

#### Eigene Referral-Statistiken abrufen

```javascript
async function getMyReferralStats(sessionToken) {
  const response = await fetch('https://api.modernquiz.de/api/referral/stats', {
    headers: {
      'Authorization': `Bearer ${sessionToken}`
    }
  });

  const data = await response.json();

  if (data.success) {
    console.log(`Du hast ${data.stats.total_referrals} User geworben`);
    console.log(`Gesamt-Provision: ${data.stats.total_commission_earned} Coins`);
  }

  return data;
}
```

---

#### Geworbene User anzeigen

```javascript
async function getReferredUsers(sessionToken) {
  const response = await fetch('https://api.modernquiz.de/api/referral/referred-users', {
    headers: {
      'Authorization': `Bearer ${sessionToken}`
    }
  });

  const data = await response.json();

  if (data.success) {
    console.log('Deine geworbenen User:');
    data.referred_users.forEach(user => {
      console.log(`- ${user.username}: ${user.total_commission_generated} Coins generiert`);
    });
  }

  return data;
}
```

---

#### Provisions-Historie anzeigen

```javascript
async function getEarningsHistory(sessionToken, limit = 50, offset = 0) {
  const response = await fetch(
    `https://api.modernquiz.de/api/referral/earnings?limit=${limit}&offset=${offset}`,
    {
      headers: {
        'Authorization': `Bearer ${sessionToken}`
      }
    }
  );

  const data = await response.json();

  if (data.success) {
    console.log('Provisions-Historie:');
    data.earnings.forEach(earning => {
      console.log(`${earning.created_at}: +${earning.commission_earned} Coins von ${earning.referred_username}`);
    });
  }

  return data;
}
```

---

### PHP Backend

#### Manuell Referral-Code validieren

```php
use ModernQuiz\Modules\Referral\ReferralManager;

$referralManager = new ReferralManager($db);

// Validiere Referral-Code
$stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
$stmt->bind_param('s', $referralCode);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result) {
    echo "Gültiger Code! Werber ID: " . $result['id'];
} else {
    echo "Ungültiger Code";
}
```

---

#### Manuell Provision triggern (für Tests)

```php
use ModernQuiz\Modules\Referral\ReferralManager;
use ModernQuiz\Modules\Coins\CoinManager;

$coinManager = new CoinManager($db);
$referralManager = new ReferralManager($db);

$userId = 2; // Geworbener User
$coinsEarned = 10.00;

// 1. Gebe dem User Coins
$result = $coinManager->addCoins(
    $userId,
    $coinsEarned,
    0,
    CoinManager::TX_QUIZ_WIN,
    'quiz',
    null,
    'Quiz-Gewinn',
    []
);

// 2. Trigger Referral-Provision automatisch
if ($result['success']) {
    $commission = $referralManager->processCommission(
        $userId,
        $coinsEarned,
        $result['transaction_id']
    );

    if ($commission) {
        echo "Provision gezahlt: " . $commission['commission_earned'] . " Coins";
    }
}
```

---

## Admin-Funktionen

### Top Referrers anzeigen

Admins können sehen, welche User die meisten Provisionen verdient haben:

```sql
SELECT
  rs.*,
  u.username,
  u.email
FROM referral_stats rs
JOIN users u ON rs.user_id = u.id
ORDER BY rs.total_commission_earned DESC
LIMIT 10;
```

Oder via API:

```bash
curl -X GET "https://api.modernquiz.de/api/admin/referral/top-referrers?limit=10" \
  -H "Authorization: Bearer ADMIN_SESSION_TOKEN"
```

---

### Referral-Settings ändern

```sql
-- Bonus erhöhen auf 500 Coins
UPDATE bank_settings
SET setting_value = '500.00'
WHERE setting_key = 'referral_bonus_coins';

-- Provision erhöhen auf 10%
UPDATE bank_settings
SET setting_value = '10.00'
WHERE setting_key = 'referral_commission_rate';
```

**WICHTIG:** Diese Änderungen gelten für NEUE Registrierungen/Provisionen. Alte Einträge behalten ihre ursprünglichen Werte.

---

### Alle Provisionen eines Users anzeigen

```sql
SELECT
  re.*,
  u_referred.username as referred_username
FROM referral_earnings re
JOIN users u_referred ON re.referred_user_id = u_referred.id
WHERE re.referrer_user_id = 1
ORDER BY re.created_at DESC
LIMIT 50;
```

---

## Migration ausführen

### Schritt 1: Backup erstellen

```bash
mysqldump -u root -p modernquiz > modernquiz_backup_$(date +%Y%m%d).sql
```

### Schritt 2: Migration ausführen

```bash
cd /home/user/ModernQuiz
php src/database/migrate.php
```

Die Migration `20250106_000003_convert_coins_to_decimal_and_add_referral.php` wird automatisch ausgeführt und:

1. ✅ Konvertiert ALLE Coin-Felder von INT zu DECIMAL(10,2)
2. ✅ Erstellt `referral_earnings` Tabelle
3. ✅ Erstellt `referral_stats` Tabelle
4. ✅ Fügt `referred_by` zur `users` Tabelle hinzu
5. ✅ Fügt Default-Settings zu `bank_settings` hinzu

**Erwartete Ausgabe:**
```
WICHTIG: Konvertiere Coins zu DECIMAL(10,2)...
  ✓ user_stats konvertiert
  ✓ bank_deposits konvertiert
  ✓ bank_transactions konvertiert
  ✓ bank_account_balances konvertiert
  ✓ coin_transactions konvertiert
  ✓ vouchers konvertiert
  ✓ voucher_redemptions konvertiert
  ✓ shop_powerups konvertiert
  ✓ shop_purchases konvertiert

=== REFERRAL-SYSTEM ===
  ✓ Referral Settings hinzugefügt
  ✓ referral_earnings Tabelle erstellt
  ✓ referral_stats Tabelle erstellt
  ✓ users.referred_by hinzugefügt

✅ Migration erfolgreich abgeschlossen!
WICHTIG: Alle Coin-Werte unterstützen jetzt 2 Nachkommastellen
```

---

## Troubleshooting

### Problem: "Ungültiger Referral-Code"

**Ursache:** Code existiert nicht oder wurde falsch eingegeben.

**Lösung:**
```sql
-- Prüfe ob Code existiert
SELECT id, username FROM users WHERE referral_code = 'USER1-A3F89E';

-- Falls leer: Code existiert nicht
-- Falls User gefunden: Code ist gültig
```

---

### Problem: Keine Provision erhalten

**Ursache 1:** User wurde nicht geworben (referred_by ist NULL)

```sql
SELECT referred_by FROM users WHERE id = 2;
-- Falls NULL: User wurde nicht geworben
```

**Lösung:** User muss sich mit Referral-Code registrieren. Nachträgliches Hinzufügen:
```sql
UPDATE users SET referred_by = 1 WHERE id = 2;
```

---

**Ursache 2:** Geworbener hat Bonus Coins verdient (keine Provision darauf)

Die Provision gilt nur für **normale Coins** aus Quizzes, nicht für Bonus Coins.

```sql
-- Prüfe letzte Transaktion
SELECT * FROM coin_transactions
WHERE user_id = 2
ORDER BY created_at DESC
LIMIT 1;

-- Wenn coins_change = 0 und bonus_coins_change > 0:
-- → Nur Bonus Coins, keine Provision
```

---

**Ursache 3:** QuizEngine verwendet noch alten Code

Stelle sicher, dass QuizEngine.php die neuen Methoden verwendet:

```php
// ALT (falsch):
UPDATE user_stats SET coins = coins + 5 WHERE user_id = ?

// NEU (richtig):
$coinManager->addCoins($userId, 5.00, 0, TX_QUIZ_WIN, ...);
$referralManager->processCommission($userId, 5.00, $transactionId);
```

---

### Problem: DECIMAL-Werte falsch

**Ursache:** Migration noch nicht ausgeführt.

**Lösung:**
```bash
php src/database/migrate.php
```

**Prüfen:**
```sql
SHOW COLUMNS FROM user_stats LIKE 'coins';
-- Type sollte sein: decimal(10,2)
```

---

### Problem: "User ID 1 kann nicht geworben werden"

**Erklärung:** Das System verhindert, dass User sich selbst werben.

```php
if ($referrerId === $newUserId) {
    return ['success' => false, 'error' => 'Du kannst dich nicht selbst werben'];
}
```

---

## Performance-Optimierung

### Indizes prüfen

```sql
SHOW INDEX FROM referral_earnings;
-- Sollte haben:
-- idx_referrer (referrer_user_id)
-- idx_referred (referred_user_id)
-- idx_created (created_at)

SHOW INDEX FROM referral_stats;
-- Primary key auf user_id sollte ausreichen
```

---

### Langsame Queries optimieren

```sql
-- Top Referrers (optimiert mit Index)
EXPLAIN SELECT
  rs.*,
  u.username
FROM referral_stats rs
JOIN users u ON rs.user_id = u.id
ORDER BY rs.total_commission_earned DESC
LIMIT 10;

-- Sollte "Using index" zeigen
```

---

## Support & Kontakt

Bei Fragen oder Problemen:
- GitHub Issues: https://github.com/kaiuwepeter/ModernQuiz/issues
- Dokumentation: /home/user/ModernQuiz/docs/

---

**Version:** 1.0.0
**Datum:** 2025-01-06
**Autor:** Claude Agent
**Lizenz:** MIT

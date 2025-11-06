# Gutschein- und Bonus Coins System - Dokumentation

## 📋 Inhaltsverzeichnis

1. [Überblick](#überblick)
2. [Sicherheitsfeatures](#sicherheitsfeatures)
3. [Datenbank-Schema](#datenbank-schema)
4. [API-Endpunkte](#api-endpunkte)
5. [Code-Beispiele](#code-beispiele)
6. [Migration ausführen](#migration-ausführen)
7. [Admin-Workflows](#admin-workflows)
8. [User-Workflows](#user-workflows)
9. [Fraud Detection](#fraud-detection)
10. [Best Practices](#best-practices)

---

## Überblick

Das System fügt zwei neue Hauptfeatures hinzu:

### 1. Bonus Coins (Zweite Währung)
- **Normale Coins**: Können ausgezahlt werden (existierendes System)
- **Bonus Coins**: Können NICHT ausgezahlt werden (neu)
- Beide können für Shop-Käufe verwendet werden
- Bei Ausgaben werden **zuerst Bonus Coins** verwendet, dann normale Coins
- Vollständiger Audit Trail für alle Transaktionen

### 2. Gutscheinsystem
- Admin kann Gutscheine erstellen mit Coins, Bonus Coins und Powerups
- Gutschein-Format: `xxxxx-xxx-x-xxxxx-xxx` (z.B. `A1B2C-D3E-4-F5G6H-I7J`)
- Limits: Max. Einlösungen total und pro User
- Ablaufdatum und Gültigkeitszeitraum
- **Nach 5 falschen Versuchen wird der User automatisch gesperrt**
- Admin wird bei verdächtigen Aktivitäten benachrichtigt

---

## Sicherheitsfeatures

### 🔒 Rate Limiting
- **5 Fehlversuche** pro User/IP-Kombination
- Nach 5 Versuchen: **60 Minuten Sperre**
- Nach Sperre: **Admin-Benachrichtigung**

### 🕵️ Fraud Detection
- Alle Fehlversuche werden geloggt mit:
  - User ID
  - IP-Adresse
  - User-Agent
  - Versuchter Code
  - Fehlergrund
  - Zeitstempel
- Ab **3 Fehlversuchen in einer Stunde** wird Aktivität als verdächtig markiert
- Admin erhält Benachrichtigung über verdächtige Aktivitäten

### 📊 Audit Trail
- Jede Coin-Transaktion wird vollständig geloggt:
  - Vorher/Nachher-Saldo
  - Transaktionstyp
  - Referenz zur Quelle (Voucher-ID, Quiz-ID, etc.)
  - Metadata (zusätzliche Informationen)
  - Zeitstempel

### 🛡️ Weitere Sicherheitsmaßnahmen
- SQL-Injection Schutz durch Prepared Statements
- Input-Validierung auf allen Endpunkten
- Admin-Authentifizierung für alle Admin-Endpunkte
- Transaction-basierte Datenbank-Operationen (Atomarität)
- IP-Tracking und User-Agent-Logging

---

## Datenbank-Schema

### Neue Tabellen

#### 1. `user_stats` - Erweiterung
```sql
ALTER TABLE user_stats
ADD COLUMN bonus_coins INT DEFAULT 0 AFTER coins
```

#### 2. `vouchers` - Gutscheine
```sql
CREATE TABLE vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,

    -- Belohnungen
    coins INT DEFAULT 0,
    bonus_coins INT DEFAULT 0,
    powerups JSON,  -- [{"id": 1, "quantity": 5}]

    -- Limits
    max_redemptions INT DEFAULT 1,
    current_redemptions INT DEFAULT 0,
    max_per_user INT DEFAULT 1,

    -- Gültigkeit
    valid_from TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valid_until TIMESTAMP NULL,

    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

#### 3. `voucher_redemptions` - Einlösungen
```sql
CREATE TABLE voucher_redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucher_id INT NOT NULL,
    user_id INT NOT NULL,
    coins_received INT DEFAULT 0,
    bonus_coins_received INT DEFAULT 0,
    powerups_received JSON,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_voucher (user_id, voucher_id)
)
```

#### 4. `voucher_fraud_log` - Betrugsversuche
```sql
CREATE TABLE voucher_fraud_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    attempted_code VARCHAR(30) NOT NULL,
    failure_reason ENUM(
        'invalid_code',
        'expired',
        'max_redemptions_reached',
        'already_redeemed_by_user',
        'voucher_inactive',
        'not_yet_valid',
        'suspicious_pattern',
        'rate_limit_exceeded'
    ) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    attempt_count INT DEFAULT 1,
    is_suspicious BOOLEAN DEFAULT FALSE,
    admin_notified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

#### 5. `voucher_rate_limits` - Rate Limiting
```sql
CREATE TABLE voucher_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45) NOT NULL,
    failed_attempts INT DEFAULT 0,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP NULL,
    is_permanently_blocked BOOLEAN DEFAULT FALSE
)
```

#### 6. `coin_transactions` - Audit Trail
```sql
CREATE TABLE coin_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_type ENUM(
        'voucher_redemption',
        'quiz_reward',
        'shop_purchase',
        'admin_adjustment',
        'referral_bonus',
        'achievement',
        'daily_reward',
        'withdrawal'
    ) NOT NULL,
    coins_change INT DEFAULT 0,
    bonus_coins_change INT DEFAULT 0,
    coins_before INT NOT NULL,
    bonus_coins_before INT NOT NULL,
    coins_after INT NOT NULL,
    bonus_coins_after INT NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    description TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

---

## API-Endpunkte

### User-Endpunkte

#### POST `/api/vouchers/redeem` - Gutschein einlösen
**Authentifizierung:** Erforderlich (Bearer Token)

**Request Body:**
```json
{
    "code": "A1B2C-D3E-4-F5G6H-I7J"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Gutschein erfolgreich eingelöst!",
    "rewards": {
        "coins": 1000,
        "bonus_coins": 500,
        "powerups": [
            {"id": 1, "quantity": 5},
            {"id": 3, "quantity": 2}
        ]
    }
}
```

**Error Response (400):**
```json
{
    "success": false,
    "error": "Ungültiger Gutscheincode"
}
```

**Error Response (429) - Rate Limit:**
```json
{
    "success": false,
    "error": "Zu viele Fehlversuche. Bitte warte noch 45 Minuten.",
    "blocked_until": "2025-01-06 15:30:00"
}
```

---

### Admin-Endpunkte

Alle Admin-Endpunkte erfordern **Admin-Rolle** und **Authentifizierung**.

#### POST `/api/admin/vouchers/create` - Gutschein erstellen

**Request Body:**
```json
{
    "name": "Willkommensbonus",
    "description": "Danke für deine Registrierung!",
    "coins": 1000,
    "bonus_coins": 500,
    "powerups": [
        {"id": 1, "quantity": 5},
        {"id": 2, "quantity": 3}
    ],
    "max_redemptions": 100,
    "max_per_user": 1,
    "valid_from": "2025-01-01 00:00:00",
    "valid_until": "2025-12-31 23:59:59"
}
```

**Response (201):**
```json
{
    "success": true,
    "code": "A1B2C-D3E-4-F5G6H-I7J",
    "voucher_id": 42,
    "message": "Gutschein erfolgreich erstellt"
}
```

#### GET `/api/admin/vouchers` - Alle Gutscheine auflisten

**Query Parameters:**
- `is_active` (optional): `0` oder `1`
- `search` (optional): Suchbegriff für Code/Name/Beschreibung

**Response (200):**
```json
{
    "success": true,
    "vouchers": [
        {
            "id": 1,
            "code": "A1B2C-D3E-4-F5G6H-I7J",
            "name": "Willkommensbonus",
            "description": "Danke für deine Registrierung!",
            "coins": 1000,
            "bonus_coins": 500,
            "powerups": "[{\"id\":1,\"quantity\":5}]",
            "max_redemptions": 100,
            "current_redemptions": 42,
            "remaining_redemptions": 58,
            "max_per_user": 1,
            "valid_from": "2025-01-01 00:00:00",
            "valid_until": "2025-12-31 23:59:59",
            "is_active": true,
            "created_by_username": "admin",
            "created_at": "2025-01-06 10:00:00"
        }
    ]
}
```

#### GET `/api/admin/vouchers/{id}/stats` - Statistiken eines Gutscheins

**Response (200):**
```json
{
    "success": true,
    "stats": {
        "id": 1,
        "code": "A1B2C-D3E-4-F5G6H-I7J",
        "name": "Willkommensbonus",
        "unique_users": 42,
        "total_coins_given": 42000,
        "total_bonus_coins_given": 21000,
        "current_redemptions": 42,
        "max_redemptions": 100
    },
    "recent_redemptions": [
        {
            "id": 123,
            "username": "user123",
            "coins_received": 1000,
            "bonus_coins_received": 500,
            "redeemed_at": "2025-01-06 14:30:00",
            "ip_address": "192.168.1.1"
        }
    ]
}
```

#### DELETE `/api/admin/vouchers/{id}` - Gutschein deaktivieren

**Response (200):**
```json
{
    "success": true,
    "message": "Gutschein wurde deaktiviert"
}
```

#### GET `/api/admin/vouchers/fraud-log` - Betrugsversuche anzeigen

**Query Parameters:**
- `user_id` (optional): Filter nach User
- `is_suspicious` (optional): Nur verdächtige Aktivitäten
- `admin_notified` (optional): Filter nach Admin-Benachrichtigung

**Response (200):**
```json
{
    "success": true,
    "fraud_log": [
        {
            "id": 1,
            "user_id": 123,
            "username": "suspicious_user",
            "email": "user@example.com",
            "attempted_code": "XXXXX-XXX-X-XXXXX-XXX",
            "failure_reason": "invalid_code",
            "ip_address": "192.168.1.100",
            "user_agent": "Mozilla/5.0...",
            "attempt_count": 5,
            "is_suspicious": true,
            "admin_notified": true,
            "created_at": "2025-01-06 14:00:00"
        }
    ]
}
```

---

## Code-Beispiele

### Frontend: Gutschein einlösen

```javascript
async function redeemVoucher(code) {
    try {
        const response = await fetch('/api/vouchers/redeem', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${sessionToken}`
            },
            body: JSON.stringify({ code })
        });

        const data = await response.json();

        if (data.success) {
            alert(`Erfolgreich! Du hast ${data.rewards.coins} Coins und ${data.rewards.bonus_coins} Bonus Coins erhalten!`);
        } else {
            alert(`Fehler: ${data.error}`);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
```

### Backend: CoinManager verwenden

```php
use ModernQuiz\Modules\Coins\CoinManager;

$coinManager = new CoinManager($pdo);

// Coins hinzufügen
$result = $coinManager->addCoins(
    userId: 123,
    coins: 1000,
    bonusCoins: 500,
    transactionType: CoinManager::TX_QUIZ_REWARD,
    referenceType: 'quiz',
    referenceId: 456,
    description: 'Quiz absolviert',
    metadata: ['quiz_score' => 950]
);

// Coins abziehen (Shop-Kauf)
$result = $coinManager->deductCoins(
    userId: 123,
    amount: 250,
    transactionType: CoinManager::TX_SHOP_PURCHASE,
    referenceType: 'powerup',
    referenceId: 5,
    description: 'Powerup gekauft'
);

// User-Balance abfragen
$balance = $coinManager->getUserCoins(123);
echo "Coins: {$balance['coins']}, Bonus: {$balance['bonus_coins']}";
```

### Backend: Voucher erstellen

```php
use ModernQuiz\Modules\Voucher\VoucherManager;

$voucherManager = new VoucherManager($pdo);

$result = $voucherManager->createVoucher(
    adminId: 1,
    data: [
        'name' => 'Neujahrsbonus 2025',
        'description' => 'Frohes neues Jahr!',
        'coins' => 2000,
        'bonus_coins' => 1000,
        'powerups' => [
            ['id' => 1, 'quantity' => 10],  // 10x 50/50 Powerup
            ['id' => 2, 'quantity' => 5]    // 5x Extra Time
        ],
        'max_redemptions' => 1000,
        'max_per_user' => 1,
        'valid_from' => '2025-01-01 00:00:00',
        'valid_until' => '2025-01-07 23:59:59'
    ]
);

if ($result['success']) {
    echo "Gutscheincode: {$result['code']}";
}
```

---

## Migration ausführen

### 1. Migration-Datei ausführen

```php
<?php
// scripts/run_voucher_migration.php

require_once __DIR__ . '/../vendor/autoload.php';

use ModernQuiz\Core\Database;
use ModernQuiz\Database\Migrations\AddBonusCoinsAndVoucherSystem;

$database = Database::getInstance();
$migration = new AddBonusCoinsAndVoucherSystem($database);

echo "Führe Voucher-System Migration aus...\n";

if ($migration->up()) {
    echo "✅ Migration erfolgreich abgeschlossen!\n";
    echo "\nNeue Tabellen erstellt:\n";
    echo "  - vouchers\n";
    echo "  - voucher_redemptions\n";
    echo "  - voucher_fraud_log\n";
    echo "  - voucher_rate_limits\n";
    echo "  - coin_transactions\n";
    echo "\nuser_stats Tabelle erweitert:\n";
    echo "  - bonus_coins Spalte hinzugefügt\n";
} else {
    echo "❌ Migration fehlgeschlagen!\n";
    echo "Bitte prüfe die Logs für Details.\n";
}
```

### 2. Migration ausführen

```bash
php scripts/run_voucher_migration.php
```

---

## Admin-Workflows

### Workflow 1: Willkommens-Gutschein erstellen

1. **Gutschein erstellen:**
```bash
POST /api/admin/vouchers/create
{
    "name": "Willkommen bei ModernQuiz!",
    "description": "Danke für deine Registrierung",
    "coins": 500,
    "bonus_coins": 250,
    "max_redemptions": 10000,
    "max_per_user": 1,
    "valid_until": "2025-12-31 23:59:59"
}
```

2. **Code an neue User senden** (z.B. per E-Mail)

3. **Statistiken überprüfen:**
```bash
GET /api/admin/vouchers/1/stats
```

### Workflow 2: Event-Gutschein mit Powerups

```bash
POST /api/admin/vouchers/create
{
    "name": "Halloween Special 2025",
    "description": "Gruselige Belohnungen!",
    "coins": 2000,
    "bonus_coins": 1000,
    "powerups": [
        {"id": 1, "quantity": 10},
        {"id": 2, "quantity": 5},
        {"id": 3, "quantity": 3}
    ],
    "max_redemptions": 500,
    "max_per_user": 1,
    "valid_from": "2025-10-25 00:00:00",
    "valid_until": "2025-11-01 23:59:59"
}
```

### Workflow 3: Betrugsversuche überwachen

```bash
# Alle verdächtigen Aktivitäten anzeigen
GET /api/admin/vouchers/fraud-log?is_suspicious=1

# Noch nicht benachrichtigte Vorfälle
GET /api/admin/vouchers/fraud-log?admin_notified=0
```

---

## User-Workflows

### Workflow 1: Gutschein einlösen

```javascript
// 1. User gibt Code ein
const voucherCode = "A1B2C-D3E-4-F5G6H-I7J";

// 2. Code einlösen
const response = await fetch('/api/vouchers/redeem', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ code: voucherCode })
});

const result = await response.json();

// 3. Feedback anzeigen
if (result.success) {
    showSuccess(`Du hast erhalten:
        ${result.rewards.coins} Coins
        ${result.rewards.bonus_coins} Bonus Coins
        ${result.rewards.powerups.length} Powerups
    `);
} else {
    showError(result.error);
}
```

---

## Fraud Detection

### Automatische Maßnahmen

#### Bei 3 Fehlversuchen:
- ⚠️ Aktivität wird als verdächtig markiert
- 📧 Admin erhält Benachrichtigung

#### Bei 5 Fehlversuchen:
- 🚫 User wird für 60 Minuten gesperrt
- 🔒 Permanente Sperre im System markiert
- 📧 Admin wird sofort benachrichtigt
- 📝 Alle Versuche werden geloggt

### Logged Information:
- User-ID und Username
- IP-Adresse
- User-Agent (Browser/Device)
- Versuchter Code
- Fehlergrund
- Zeitstempel
- Anzahl der Versuche

### Admin-Dashboard Features (empfohlen zu implementieren):
- 📊 Echtzeit-Übersicht verdächtiger Aktivitäten
- 🔍 Filter nach User, IP, Zeitraum
- 📈 Statistiken über Fehlversuche
- 🔓 Manuelle User-Entsperrung
- 📧 Email-Benachrichtigungen

---

## Best Practices

### Für Admins:

1. **Gutschein-Codes sicher verteilen:**
   - Nicht öffentlich posten
   - Per E-Mail an verifizierte User senden
   - In geschlossenen Gruppen teilen

2. **Limits setzen:**
   - `max_redemptions`: Nicht zu hoch setzen
   - `max_per_user`: Meist auf 1 lassen
   - `valid_until`: Immer ein Ablaufdatum setzen

3. **Regelmäßig überwachen:**
   - Fraud Log täglich prüfen
   - Statistiken über Einlösungen analysieren
   - Verdächtige Muster erkennen

4. **Wertvolle Gutscheine:**
   - Höhere Sicherheit durch kürzere Gültigkeit
   - Niedrigere `max_redemptions`
   - Monitore Einlösungen in Echtzeit

### Für Entwickler:

1. **CoinManager verwenden:**
   - Immer den CoinManager für Coin-Operationen nutzen
   - Nie direkt user_stats aktualisieren
   - Vollständiger Audit Trail

2. **Bonus Coins Strategie:**
   - Bonus Coins für Promotions und Belohnungen
   - Normale Coins für Quizze und Achievements
   - Klare Kommunikation an User über Unterschiede

3. **Error Handling:**
   - Immer auf `success` prüfen
   - User-freundliche Fehlermeldungen
   - Logs für Debugging

4. **Testing:**
   - Teste Rate Limiting
   - Teste Transaction Rollbacks
   - Teste Edge Cases (abgelaufene Gutscheine, etc.)

---

## Troubleshooting

### Problem: "Zu viele Fehlversuche" Fehler

**Lösung (Admin):**
```sql
-- User manuell entsperren
DELETE FROM voucher_rate_limits
WHERE user_id = 123 AND ip_address = '192.168.1.1';
```

### Problem: Gutschein wurde nicht eingelöst aber als eingelöst markiert

**Diagnose:**
```sql
-- Prüfe Transaktionen
SELECT * FROM coin_transactions
WHERE user_id = 123
AND reference_type = 'voucher'
ORDER BY created_at DESC;

-- Prüfe Einlösungen
SELECT * FROM voucher_redemptions
WHERE user_id = 123 AND voucher_id = 1;
```

### Problem: Coins wurden nicht hinzugefügt

**Diagnose:**
```sql
-- Prüfe ob Transaction geloggt wurde
SELECT * FROM coin_transactions
WHERE user_id = 123
ORDER BY created_at DESC
LIMIT 10;

-- Prüfe user_stats
SELECT coins, bonus_coins FROM user_stats WHERE user_id = 123;
```

---

## Sicherheits-Checkliste

- ✅ SQL-Injection Schutz (Prepared Statements)
- ✅ Rate Limiting (5 Versuche)
- ✅ IP-Tracking
- ✅ User-Agent Logging
- ✅ Admin-Benachrichtigungen
- ✅ Transaction-basierte DB-Operationen
- ✅ Vollständiger Audit Trail
- ✅ Input-Validierung
- ✅ Error Handling
- ✅ Automatische User-Sperre
- ✅ Fraud Detection Logging
- ✅ Admin-Authentifizierung

---

## Support und Weiterentwicklung

### Mögliche Erweiterungen:

1. **Dashboard für Admins:**
   - Echtzeit-Statistiken
   - Gutschein-Verwaltung UI
   - Fraud Detection Dashboard

2. **User-Features:**
   - Coin-History im Profil
   - Transaktions-Übersicht
   - Bonus Coins Badge/Icon

3. **Erweiterte Gutscheine:**
   - Prozentuale Rabatte
   - Zeit-basierte Powerups
   - Level-Boost Items

4. **Analytics:**
   - Einlösungsraten
   - Beliebteste Gutscheine
   - User-Engagement Metriken

---

**Version:** 1.0
**Letzte Aktualisierung:** 2025-01-06
**Autor:** ModernQuiz Development Team

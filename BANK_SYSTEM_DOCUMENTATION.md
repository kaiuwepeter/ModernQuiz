# Bank-System Dokumentation 🏦

## Inhaltsverzeichnis

1. [Überblick](#überblick)
2. [Features](#features)
3. [Datenbank-Schema](#datenbank-schema)
4. [API-Endpunkte](#api-endpunkte)
5. [User-Workflows](#user-workflows)
6. [Admin-Workflows](#admin-workflows)
7. [Zinsberechnung](#zinsberechnung)
8. [Strafgebühren](#strafgebühren)
9. [Kontoauszug](#kontoauszug)
10. [Cron-Jobs](#cron-jobs)
11. [Code-Beispiele](#code-beispiele)
12. [Migration](#migration)
13. [Best Practices](#best-practices)
14. [Troubleshooting](#troubleshooting)

---

## Überblick

Das Bank-System ermöglicht es Usern, Coins und Bonus Coins als Festgeld anzulegen und Zinsen zu verdienen.

### Kernkonzept:

- **Festgeld-Einlagen**: 30 Tage Laufzeit
- **Zinssatz**: 4% auf die Einlage
- **Vorzeitige Kündigung**: Möglich, aber mit 12% Strafgebühr
- **Keine Zinsen bei vorzeitiger Kündigung**
- **Vollständiger Kontoauszug**: Alle Transaktionen nachvollziehbar

### Use Cases:

1. **User**: Coins "parken" um Zinsen zu verdienen
2. **Admin**: Übersicht über alle Einlagen, User-Verwaltung
3. **System**: Automatische Verarbeitung fälliger Einlagen

---

## Features

### User-Features:

✅ **Einlage erstellen**
- Mit Coins und/oder Bonus Coins
- Mindest-/Maximal-Einlage konfigurierbar
- Automatische Zinsberechnung

✅ **Vorzeitige Auszahlung**
- Jederzeit möglich
- 12% Strafgebühr vom Einlagebetrag
- Keine Zinsen

✅ **Normale Auszahlung**
- Nach 30 Tagen
- Mit voller Zinsgutschrift (4%)

✅ **Kontoauszug**
- Alle Bank-Transaktionen
- Alle Coin-Transaktionen
- Vollständig nachvollziehbar

✅ **Balance-Übersicht**
- Aktueller Bank-Kontostand
- Statistiken (Zinsen verdient, Strafen bezahlt)

### Admin-Features:

✅ **User-Verwaltung**
- User sperren/entsperren
- Email ändern
- Passwort zurücksetzen
- User-Details anzeigen
- Kontoauszüge einsehen

✅ **Bank-Verwaltung**
- Alle Einlagen anzeigen
- Einlagen sperren
- Einlagen entsperren
- Einlagen sofort freigeben
- Filter nach User, Status, etc.

✅ **Admin-Actions-Log**
- Vollständiges Logging aller Admin-Aktionen
- IP-Adresse und User-Agent
- Vorher/Nachher-Werte

---

## Datenbank-Schema

### 1. `bank_settings` - Konfiguration

```sql
CREATE TABLE bank_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    description TEXT
);
```

**Default Settings:**
- `interest_rate`: 4.00 (4%)
- `duration_days`: 30
- `penalty_rate`: 12.00 (12%)
- `min_deposit`: 100
- `max_deposit`: 100000
- `bank_enabled`: 1

### 2. `bank_deposits` - Einlagen

```sql
CREATE TABLE bank_deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,

    -- Einlage
    coins_deposited INT DEFAULT 0,
    bonus_coins_deposited INT DEFAULT 0,
    total_deposited INT GENERATED ALWAYS AS (coins_deposited + bonus_coins_deposited) STORED,

    -- Zinsen
    interest_rate DECIMAL(5,2) DEFAULT 4.00,
    interest_earned INT DEFAULT 0,
    penalty_fee INT DEFAULT 0,

    -- Auszahlung
    coins_payout INT DEFAULT 0,
    bonus_coins_payout INT DEFAULT 0,
    total_payout INT DEFAULT 0,

    -- Zeitrahmen
    duration_days INT DEFAULT 30,
    deposit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    maturity_date TIMESTAMP NOT NULL,
    withdrawal_date TIMESTAMP NULL,

    -- Status
    status ENUM('active', 'matured', 'completed', 'cancelled', 'locked') DEFAULT 'active',
    is_locked BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Status-Bedeutungen:**
- `active`: Läuft noch, nicht fällig
- `matured`: Fällig, bereit zur Auszahlung
- `completed`: Ausgezahlt (mit Zinsen)
- `cancelled`: Vorzeitig gekündigt (mit Strafgebühr)
- `locked`: Von Admin gesperrt

### 3. `bank_transactions` - Kontoauszug

```sql
CREATE TABLE bank_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    deposit_id INT NULL,

    transaction_type ENUM(
        'deposit',
        'withdrawal',
        'early_withdrawal',
        'interest',
        'penalty',
        'admin_adjustment'
    ) NOT NULL,

    coins_amount INT DEFAULT 0,
    bonus_coins_amount INT DEFAULT 0,
    total_amount INT GENERATED ALWAYS AS (coins_amount + bonus_coins_amount) STORED,

    -- Kontostand (in der Bank)
    coins_balance_before INT NOT NULL,
    bonus_coins_balance_before INT NOT NULL,
    coins_balance_after INT NOT NULL,
    bonus_coins_balance_after INT NOT NULL,

    description TEXT NOT NULL,
    metadata JSON,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (deposit_id) REFERENCES bank_deposits(id) ON DELETE SET NULL
);
```

### 4. `bank_account_balances` - Kontostände

```sql
CREATE TABLE bank_account_balances (
    user_id INT PRIMARY KEY,
    coins_balance INT DEFAULT 0,
    bonus_coins_balance INT DEFAULT 0,
    total_balance INT GENERATED ALWAYS AS (coins_balance + bonus_coins_balance) STORED,

    total_deposits_count INT DEFAULT 0,
    total_withdrawals_count INT DEFAULT 0,
    total_interest_earned INT DEFAULT 0,
    total_penalties_paid INT DEFAULT 0,

    last_deposit_at TIMESTAMP NULL,
    last_withdrawal_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 5. `admin_actions_log` - Admin-Aktionen

```sql
CREATE TABLE admin_actions_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    target_user_id INT NULL,

    action_type ENUM(
        'user_lock',
        'user_unlock',
        'user_email_change',
        'user_password_change',
        'bank_deposit_lock',
        'bank_deposit_unlock',
        'bank_deposit_release',
        'other'
    ) NOT NULL,

    action_details TEXT NOT NULL,
    metadata JSON,
    before_value TEXT,
    after_value TEXT,

    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## API-Endpunkte

### User-Endpunkte

#### POST `/api/bank/deposit` - Einlage erstellen

**Request:**
```json
{
    "coins": 5000,
    "bonus_coins": 2500
}
```

**Response (201):**
```json
{
    "success": true,
    "deposit_id": 42,
    "message": "Festgeld-Einlage erfolgreich erstellt",
    "details": {
        "deposited": 7500,
        "coins": 5000,
        "bonus_coins": 2500,
        "interest_rate": 4.00,
        "expected_interest": 300,
        "expected_payout": 7800,
        "maturity_date": "2025-02-05 14:30:00",
        "duration_days": 30
    }
}
```

#### POST `/api/bank/withdraw/{id}/early` - Vorzeitige Auszahlung

**Response (200):**
```json
{
    "success": true,
    "message": "Einlage vorzeitig beendet",
    "details": {
        "deposited": 7500,
        "penalty_fee": 900,
        "penalty_rate": 12.00,
        "payout": 6600,
        "coins_payout": 4400,
        "bonus_coins_payout": 2200,
        "interest_lost": 300
    }
}
```

#### POST `/api/bank/withdraw/{id}` - Normale Auszahlung

**Response (200):**
```json
{
    "success": true,
    "message": "Einlage erfolgreich ausgezahlt",
    "details": {
        "deposited": 7500,
        "interest_earned": 300,
        "total_payout": 7800,
        "coins_payout": 5200,
        "bonus_coins_payout": 2600
    }
}
```

#### GET `/api/bank/deposits` - Eigene Einlagen

**Query Parameters:**
- `status` (optional): `active`, `matured`, `completed`, `cancelled`

**Response (200):**
```json
{
    "success": true,
    "deposits": [
        {
            "id": 42,
            "user_id": 123,
            "coins_deposited": 5000,
            "bonus_coins_deposited": 2500,
            "total_deposited": 7500,
            "interest_rate": 4.00,
            "interest_earned": 300,
            "status": "active",
            "deposit_date": "2025-01-06 14:30:00",
            "maturity_date": "2025-02-05 14:30:00",
            "days_remaining": 25
        }
    ]
}
```

#### GET `/api/bank/balance` - Bank-Kontostand

**Response (200):**
```json
{
    "success": true,
    "balance": {
        "coins_balance": 5000,
        "bonus_coins_balance": 2500,
        "total_balance": 7500,
        "total_deposits_count": 10,
        "total_withdrawals_count": 8,
        "total_interest_earned": 1200,
        "total_penalties_paid": 300
    }
}
```

#### GET `/api/bank/statement` - Kontoauszug

**Query Parameters:**
- `limit` (default: 50, max: 100)
- `offset` (default: 0)

**Response (200):**
```json
{
    "success": true,
    "coin_transactions": [...],
    "bank_transactions": [
        {
            "id": 1,
            "user_id": 123,
            "deposit_id": 42,
            "transaction_type": "deposit",
            "coins_amount": 5000,
            "bonus_coins_amount": 2500,
            "total_amount": 7500,
            "description": "Festgeld-Einlage erstellt (#42)",
            "created_at": "2025-01-06 14:30:00"
        }
    ]
}
```

---

### Admin-Endpunkte

#### GET `/api/admin/bank/deposits` - Alle Einlagen

**Query Parameters:**
- `user_id` (optional)
- `status` (optional)
- `is_locked` (optional)

**Response (200):**
```json
{
    "success": true,
    "deposits": [
        {
            "id": 42,
            "user_id": 123,
            "username": "testuser",
            "email": "test@example.com",
            "total_deposited": 7500,
            "interest_earned": 300,
            "status": "active",
            "days_remaining": 25,
            "is_locked": false
        }
    ]
}
```

#### PUT `/api/admin/bank/deposits/{id}/lock` - Einlage sperren

**Request:**
```json
{
    "reason": "Verdächtige Aktivität"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Einlage gesperrt"
}
```

#### PUT `/api/admin/bank/deposits/{id}/unlock` - Einlage entsperren

**Response (200):**
```json
{
    "success": true,
    "message": "Einlage entsperrt"
}
```

#### PUT `/api/admin/bank/deposits/{id}/release` - Sofort freigeben

**Response (200):**
```json
{
    "success": true,
    "message": "Einlage sofort freigegeben"
}
```

---

### Admin User-Verwaltung

#### GET `/api/admin/users` - Alle User

**Query Parameters:**
- `search` (optional): Suche nach Username, Email oder ID
- `is_active` (optional): 0 oder 1
- `role` (optional): `user`, `admin`, etc.

**Response (200):**
```json
{
    "success": true,
    "users": [
        {
            "id": 123,
            "username": "testuser",
            "email": "test@example.com",
            "is_active": true,
            "role": "user",
            "coins": 10000,
            "bonus_coins": 5000,
            "total_points": 50000,
            "level": 15,
            "bank_deposits_count": 5,
            "vouchers_redeemed_count": 3,
            "created_at": "2024-12-01 10:00:00",
            "last_login": "2025-01-06 09:00:00"
        }
    ]
}
```

#### GET `/api/admin/users/{id}` - User-Details

**Response (200):**
```json
{
    "success": true,
    "user": {
        "id": 123,
        "username": "testuser",
        "email": "test@example.com",
        "is_active": true,
        "coins": 10000,
        "bonus_coins": 5000,
        "bank_deposits": {
            "total_deposits": 10,
            "active_deposits": 2,
            "completed_deposits": 7,
            "cancelled_deposits": 1,
            "total_interest_earned": 1500,
            "total_penalties_paid": 300
        },
        "recent_activity": [...]
    }
}
```

#### PUT `/api/admin/users/{id}/lock` - User sperren

**Request:**
```json
{
    "reason": "Verstoß gegen Nutzungsbedingungen"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "User erfolgreich gesperrt"
}
```

#### PUT `/api/admin/users/{id}/unlock` - User entsperren

**Response (200):**
```json
{
    "success": true,
    "message": "User erfolgreich entsperrt"
}
```

#### PUT `/api/admin/users/{id}/email` - Email ändern

**Request:**
```json
{
    "email": "newemail@example.com"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Email erfolgreich geändert",
    "old_email": "old@example.com",
    "new_email": "newemail@example.com"
}
```

#### PUT `/api/admin/users/{id}/password` - Passwort ändern

**Request:**
```json
{
    "password": "NewSecurePassword123!"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Passwort erfolgreich geändert"
}
```

#### GET `/api/admin/users/{id}/statement` - User-Kontoauszug

**Query Parameters:**
- `limit` (default: 50, max: 100)
- `offset` (default: 0)

**Response (200):**
```json
{
    "success": true,
    "statement": [...]
}
```

#### GET `/api/admin/users/actions` - Admin-Aktionen Log

**Query Parameters:**
- `admin_user_id` (optional)
- `target_user_id` (optional)
- `action_type` (optional)

**Response (200):**
```json
{
    "success": true,
    "actions": [
        {
            "id": 1,
            "admin_user_id": 1,
            "admin_username": "admin",
            "target_user_id": 123,
            "target_username": "testuser",
            "action_type": "user_lock",
            "action_details": "User gesperrt. Grund: Verstoß gegen Nutzungsbedingungen",
            "before_value": "true",
            "after_value": "false",
            "ip_address": "192.168.1.1",
            "created_at": "2025-01-06 15:00:00"
        }
    ]
}
```

---

## User-Workflows

### Workflow 1: Einlage erstellen

```javascript
// 1. Prüfe verfügbare Coins
const response = await fetch('/api/user/profile', {
    headers: { 'Authorization': `Bearer ${token}` }
});
const user = await response.json();

console.log(`Verfügbar: ${user.coins} Coins, ${user.bonus_coins} Bonus Coins`);

// 2. Erstelle Einlage
const depositResponse = await fetch('/api/bank/deposit', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
        coins: 5000,
        bonus_coins: 2500
    })
});

const deposit = await depositResponse.json();

if (deposit.success) {
    console.log(`Einlage erstellt! ID: ${deposit.deposit_id}`);
    console.log(`Fällig am: ${deposit.details.maturity_date}`);
    console.log(`Erwartete Zinsen: ${deposit.details.expected_interest}`);
}
```

### Workflow 2: Vorzeitige Auszahlung

```javascript
// 1. Hole Einlagen
const response = await fetch('/api/bank/deposits?status=active', {
    headers: { 'Authorization': `Bearer ${token}` }
});
const data = await response.json();
const deposits = data.deposits;

// 2. Wähle Einlage
const depositId = deposits[0].id;

// 3. Zeige Warnung
const penalty = deposits[0].total_deposited * 0.12;
console.log(`WARNUNG: Bei vorzeitiger Auszahlung verlierst du:`);
console.log(`- Strafgebühr: ${penalty} Coins (12%)`);
console.log(`- Zinsen: ${deposits[0].interest_earned} Coins`);
console.log(`Gesamt-Verlust: ${penalty + deposits[0].interest_earned} Coins`);

// 4. Bestätigung
if (confirm('Wirklich vorzeitig beenden?')) {
    const withdrawResponse = await fetch(`/api/bank/withdraw/${depositId}/early`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` }
    });

    const result = await withdrawResponse.json();
    console.log(`Ausgezahlt: ${result.details.payout} Coins`);
}
```

### Workflow 3: Normale Auszahlung (fällig)

```javascript
// 1. Hole fällige Einlagen
const response = await fetch('/api/bank/deposits?status=matured', {
    headers: { 'Authorization': `Bearer ${token}` }
});
const data = await response.json();
const maturedDeposits = data.deposits;

// 2. Zahle alle aus
for (const deposit of maturedDeposits) {
    const withdrawResponse = await fetch(`/api/bank/withdraw/${deposit.id}`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` }
    });

    const result = await withdrawResponse.json();
    console.log(`Einlage #${deposit.id} ausgezahlt:`);
    console.log(`- Hauptbetrag: ${result.details.deposited}`);
    console.log(`- Zinsen: ${result.details.interest_earned}`);
    console.log(`- Gesamt: ${result.details.total_payout}`);
}
```

---

## Admin-Workflows

### Workflow 1: User-Übersicht

```javascript
// Suche User
const response = await fetch('/api/admin/users?search=testuser', {
    headers: { 'Authorization': `Bearer ${adminToken}` }
});

const data = await response.json();
const users = data.users;

// Zeige Details
users.forEach(user => {
    console.log(`${user.username} (${user.email})`);
    console.log(`  Coins: ${user.coins} / Bonus: ${user.bonus_coins}`);
    console.log(`  Level: ${user.level}`);
    console.log(`  Bankeinlagen: ${user.bank_deposits_count}`);
    console.log(`  Status: ${user.is_active ? 'Aktiv' : 'Gesperrt'}`);
});
```

### Workflow 2: User sperren

```javascript
const userId = 123;
const reason = "Mehrfacher Verstoß gegen Regeln";

const response = await fetch(`/api/admin/users/${userId}/lock`, {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${adminToken}`
    },
    body: JSON.stringify({ reason })
});

const result = await response.json();
console.log(result.message);
```

### Workflow 3: Bankeinlagen überwachen

```javascript
// Alle aktiven Einlagen
const response = await fetch('/api/admin/bank/deposits?status=active', {
    headers: { 'Authorization': `Bearer ${adminToken}` }
});

const data = await response.json();
const deposits = data.deposits;

// Finde verdächtige Einlagen (z.B. sehr hoch)
const suspicious = deposits.filter(d => d.total_deposited > 50000);

suspicious.forEach(deposit => {
    console.log(`Verdächtig: User ${deposit.username}`);
    console.log(`  Einlage: ${deposit.total_deposited} Coins`);
    console.log(`  Fällig in: ${deposit.days_remaining} Tagen`);
});

// Optional: Sperre verdächtige Einlage
if (confirm('Einlage sperren?')) {
    await fetch(`/api/admin/bank/deposits/${depositId}/lock`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${adminToken}`
        },
        body: JSON.stringify({
            reason: 'Verdächtig hohe Einlage - Prüfung erforderlich'
        })
    });
}
```

---

## Zinsberechnung

### Formel:
```
Zinsen = Einlage × (Zinssatz / 100)
Zinsen = 7.500 × (4 / 100) = 300 Coins
```

### Beispiel:
- Einlage: 5.000 normale Coins + 2.500 Bonus Coins = 7.500 gesamt
- Zinssatz: 4%
- Zinsen: 300 Coins
- Auszahlung nach 30 Tagen: 7.800 Coins

### Verteilung der Zinsen:
Zinsen werden **proportional** auf Coins und Bonus Coins verteilt:

```
Coins-Anteil: 5.000 / 7.500 = 66,67%
Bonus-Coins-Anteil: 2.500 / 7.500 = 33,33%

Zinsen auf Coins: 300 × 0,6667 = 200 Coins
Zinsen auf Bonus Coins: 300 × 0,3333 = 100 Coins

Auszahlung:
- Normale Coins: 5.000 + 200 = 5.200
- Bonus Coins: 2.500 + 100 = 2.600
- Gesamt: 7.800
```

---

## Strafgebühren

### Bei vorzeitiger Kündigung:
- **12% Strafgebühr** vom Einlagebetrag
- **Keine Zinsen**

### Formel:
```
Strafgebühr = Einlage × (Strafgebühr-Satz / 100)
Strafgebühr = 7.500 × (12 / 100) = 900 Coins

Auszahlung = Einlage - Strafgebühr
Auszahlung = 7.500 - 900 = 6.600 Coins
```

### Beispiel:
- Einlage: 7.500 Coins
- Strafgebühr (12%): 900 Coins
- Entgangene Zinsen: 300 Coins
- **Gesamt-Verlust**: 1.200 Coins
- Auszahlung: 6.600 Coins

### Warnung an User:
```javascript
const deposit = {
    total_deposited: 7500,
    interest_earned: 300,
    penalty_rate: 12
};

const penalty = deposit.total_deposited * (deposit.penalty_rate / 100);
const totalLoss = penalty + deposit.interest_earned;

alert(`
WARNUNG: Vorzeitige Kündigung

Du verlierst:
- Strafgebühr: ${penalty} Coins (${deposit.penalty_rate}%)
- Entgangene Zinsen: ${deposit.interest_earned} Coins

Gesamt-Verlust: ${totalLoss} Coins
Du erhältst: ${deposit.total_deposited - penalty} Coins

Möchtest du wirklich fortfahren?
`);
```

---

## Kontoauszug

Der Kontoauszug zeigt **alle** Bank-Transaktionen eines Users:

### Transaktions-Typen:

1. **deposit**: Einzahlung ins Festgeld
2. **withdrawal**: Normale Auszahlung (mit Zinsen)
3. **early_withdrawal**: Vorzeitige Auszahlung
4. **interest**: Zinsgutschrift
5. **penalty**: Strafgebühr
6. **admin_adjustment**: Admin-Korrektur

### Beispiel-Kontoauszug:

```
Datum               Typ              Betrag        Balance
===================================================================
2025-01-06 14:30   Einzahlung        +7.500       7.500
2025-02-05 10:00   Auszahlung        -7.500       0
2025-02-05 10:00   Zinsgutschrift    +300         300
2025-02-05 10:01   Einzahlung        +10.000      10.300
2025-02-10 15:00   Vorzeitig         -10.000      300
2025-02-10 15:00   Strafgebühr       -1.200       -900
```

### Export-Funktion (empfohlen):

```javascript
// CSV-Export
function exportStatement(transactions) {
    const csv = [
        ['Datum', 'Typ', 'Coins', 'Bonus Coins', 'Gesamt', 'Balance', 'Beschreibung'],
        ...transactions.map(t => [
            t.created_at,
            t.transaction_type,
            t.coins_amount,
            t.bonus_coins_amount,
            t.total_amount,
            t.coins_balance_after + t.bonus_coins_balance_after,
            t.description
        ])
    ];

    const csvContent = csv.map(row => row.join(',')).join('\n');
    downloadCSV(csvContent, 'kontoauszug.csv');
}
```

---

## Cron-Jobs

### Automatische Verarbeitung fälliger Einlagen

**Datei:** `scripts/cron_process_bank_deposits.php`

**Funktion:**
- Markiert Einlagen als `matured` wenn Fälligkeitsdatum erreicht
- Benachrichtigt User über fällige Einlagen

**Crontab-Eintrag** (täglich um 00:00 Uhr):
```bash
0 0 * * * php /path/to/ModernQuiz/scripts/cron_process_bank_deposits.php >> /var/log/cron_bank.log 2>&1
```

**Oder via systemd** (empfohlen):

1. Erstelle Service: `/etc/systemd/system/modernquiz-bank.service`
```ini
[Unit]
Description=ModernQuiz Bank Deposits Processing
After=network.target

[Service]
Type=oneshot
User=www-data
ExecStart=/usr/bin/php /path/to/ModernQuiz/scripts/cron_process_bank_deposits.php
```

2. Erstelle Timer: `/etc/systemd/system/modernquiz-bank.timer`
```ini
[Unit]
Description=Daily ModernQuiz Bank Processing
Requires=modernquiz-bank.service

[Timer]
OnCalendar=daily
Persistent=true

[Install]
WantedBy=timers.target
```

3. Aktiviere:
```bash
sudo systemctl enable modernquiz-bank.timer
sudo systemctl start modernquiz-bank.timer
```

---

## Code-Beispiele

### Backend: Einlage erstellen

```php
use ModernQuiz\Modules\Bank\BankManager;

$bankManager = new BankManager($pdo);

$result = $bankManager->createDeposit(
    userId: 123,
    coins: 5000,
    bonusCoins: 2500
);

if ($result['success']) {
    echo "Einlage #{$result['deposit_id']} erstellt\n";
    echo "Fällig am: {$result['details']['maturity_date']}\n";
    echo "Erwartete Zinsen: {$result['details']['expected_interest']}\n";
}
```

### Backend: User sperren

```php
use ModernQuiz\Modules\Admin\AdminUserManager;

$adminUserManager = new AdminUserManager($pdo);

$result = $adminUserManager->lockUser(
    userId: 123,
    adminId: 1,
    reason: 'Verstoß gegen Nutzungsbedingungen'
);

if ($result['success']) {
    echo "User gesperrt und benachrichtigt\n";
}
```

### Frontend: Dashboard Widget

```javascript
// Bank-Balance Widget
async function loadBankBalance() {
    const response = await fetch('/api/bank/balance', {
        headers: { 'Authorization': `Bearer ${token}` }
    });

    const data = await response.json();
    const balance = data.balance;

    document.getElementById('bank-balance').innerHTML = `
        <h3>Bank-Guthaben</h3>
        <div class="balance">
            <div>Coins: ${balance.coins_balance}</div>
            <div>Bonus Coins: ${balance.bonus_coins_balance}</div>
            <div class="total">Gesamt: ${balance.total_balance}</div>
        </div>
        <div class="stats">
            <div>Zinsen verdient: ${balance.total_interest_earned}</div>
            <div>Strafen bezahlt: ${balance.total_penalties_paid}</div>
        </div>
    `;
}
```

---

## Migration

### Ausführen:

```bash
# Via PHP-Script
php scripts/run_migrations.php

# Oder manuell:
mysql -u username -p database_name < src/database/migrations/20250106_000002_add_bank_system.sql
```

### Verifizierung:

```sql
-- Prüfe ob Tabellen existieren
SHOW TABLES LIKE 'bank%';

-- Prüfe Settings
SELECT * FROM bank_settings;

-- Teste Insert
INSERT INTO bank_deposits (user_id, coins_deposited, bonus_coins_deposited, maturity_date)
VALUES (1, 1000, 500, DATE_ADD(NOW(), INTERVAL 30 DAY));

SELECT * FROM bank_deposits;
```

---

## Best Practices

### Für User:

1. **Nur "parken" was du nicht brauchst**: Einlagen sind 30 Tage gesperrt
2. **Vorzeitige Kündigung vermeiden**: 12% Strafgebühr + entgangene Zinsen
3. **Regelmäßig reinvestieren**: Zinsen wieder anlegen für Zinseszins-Effekt
4. **Kontoauszug prüfen**: Regelmäßig Transaktionen überprüfen

### Für Admins:

1. **Regelmäßig überwachen**:
   - Hohe Einlagen (> 50.000 Coins)
   - Viele Einlagen von einem User
   - Verdächtige Muster

2. **Logging prüfen**:
   - Admin-Actions-Log regelmäßig reviewen
   - Verdächtige Aktivitäten untersuchen

3. **Einstellungen anpassen**:
   - Zinssatz bei Bedarf ändern
   - Min/Max-Limits anpassen
   - Strafgebühr-Satz ändern

4. **Kommunikation**:
   - User über gesperrte Einlagen informieren
   - Bei Auffälligkeiten User kontaktieren

### Für Entwickler:

1. **Transaction-Sicherheit**:
   - Immer Transactions verwenden
   - Rollback bei Fehlern

2. **Logging**:
   - Alle Bank-Operationen loggen
   - Admin-Aktionen loggen

3. **Error Handling**:
   - Klare Fehlermeldungen
   - Fehler loggen

4. **Testing**:
   - Edge Cases testen
   - Zinsberechnung testen
   - Strafgebühren testen

---

## Troubleshooting

### Problem: "Nicht genug Coins verfügbar"

**Diagnose:**
```sql
-- Prüfe User Wallet
SELECT coins, bonus_coins FROM user_stats WHERE user_id = 123;

-- Prüfe aktive Bank-Einlagen
SELECT SUM(coins_deposited), SUM(bonus_coins_deposited)
FROM bank_deposits
WHERE user_id = 123 AND status = 'active';
```

**Lösung:**
- User muss warten bis Einlagen ausgezahlt werden
- Oder vorzeitig kündigen (mit Strafgebühr)

### Problem: Einlage kann nicht ausgezahlt werden

**Diagnose:**
```sql
SELECT status, is_locked, maturity_date, NOW() as current_time
FROM bank_deposits
WHERE id = 42;
```

**Mögliche Gründe:**
- Status nicht `matured` → Noch nicht fällig oder falsche Auszahlungsfunktion
- `is_locked = 1` → Von Admin gesperrt
- `maturity_date > NOW()` → Noch nicht fällig

### Problem: Zinsen wurden nicht gutgeschrieben

**Diagnose:**
```sql
-- Prüfe Bank-Transaktionen
SELECT * FROM bank_transactions
WHERE deposit_id = 42 AND transaction_type = 'interest';

-- Prüfe Einlage
SELECT interest_earned, status, withdrawal_date
FROM bank_deposits
WHERE id = 42;
```

**Lösung:**
- Zinsen werden nur bei normaler Auszahlung gutgeschrieben
- Bei vorzeitiger Kündigung: Keine Zinsen

### Problem: Cron-Job läuft nicht

**Diagnose:**
```bash
# Prüfe Crontab
crontab -l

# Prüfe Logs
tail -f /var/log/cron_bank.log

# Manuell testen
php scripts/cron_process_bank_deposits.php
```

**Lösung:**
- Crontab-Eintrag überprüfen
- Pfade überprüfen
- PHP-Fehler im Log suchen

### Problem: Admin kann User nicht sperren

**Diagnose:**
```sql
-- Prüfe Admin-Rolle
SELECT role FROM users WHERE id = 1;

-- Prüfe Sessions
SELECT * FROM sessions WHERE user_id = 1 AND expires_at > NOW();
```

**Lösung:**
- Admin braucht `role = 'admin'` in users Tabelle
- Session muss gültig sein
- Admin-Authentifizierung überprüfen

---

## Support

### Dokumentation:
- Diese Datei: `BANK_SYSTEM_DOCUMENTATION.md`
- Voucher-System: `VOUCHER_SYSTEM_DOCUMENTATION.md`
- Quick-Start: `BANK_QUICKSTART.md` (in Arbeit)

### Logs:
- Cron-Jobs: `/var/log/cron_bank.log`
- PHP-Errors: PHP error log
- Admin-Actions: `admin_actions_log` Tabelle

### Hilfe:
- GitHub Issues: https://github.com/kaiuwepeter/ModernQuiz/issues
- Code-Review: Alle Klassen sind vollständig dokumentiert

---

**Version:** 1.0
**Letzte Aktualisierung:** 2025-01-06
**Autor:** ModernQuiz Development Team

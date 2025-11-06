# Gutschein-System Quick Start Guide 🚀

## 1. Migration ausführen

```bash
# In der MySQL-Konsole oder via PHPMyAdmin
# Die Migration-Datei befindet sich hier:
# src/database/migrations/20250106_000001_add_bonus_coins_and_voucher_system.php

# Oder via Migrations-Script (falls vorhanden):
php scripts/run_migrations.php
```

## 2. Ersten Gutschein erstellen (als Admin)

```bash
curl -X POST http://localhost/api/admin/vouchers/create \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "name": "Willkommensbonus",
    "description": "Danke für deine Registrierung!",
    "coins": 1000,
    "bonus_coins": 500,
    "max_redemptions": 100,
    "max_per_user": 1
  }'
```

**Response:**
```json
{
    "success": true,
    "code": "A1B2C-D3E-4-F5G6H-I7J",
    "voucher_id": 1
}
```

## 3. Gutschein einlösen (als User)

```bash
curl -X POST http://localhost/api/vouchers/redeem \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_USER_TOKEN" \
  -d '{
    "code": "A1B2C-D3E-4-F5G6H-I7J"
  }'
```

**Response:**
```json
{
    "success": true,
    "message": "Gutschein erfolgreich eingelöst!",
    "rewards": {
        "coins": 1000,
        "bonus_coins": 500,
        "powerups": []
    }
}
```

## 4. User-Balance anzeigen

Normale Coins und Bonus Coins sind jetzt in `user_stats`:

```sql
SELECT
    u.username,
    us.coins,
    us.bonus_coins,
    (us.coins + us.bonus_coins) as total_coins
FROM users u
JOIN user_stats us ON u.id = us.user_id
WHERE u.id = 1;
```

## 5. Wichtige Endpunkte

### User:
- `POST /api/vouchers/redeem` - Gutschein einlösen

### Admin:
- `POST /api/admin/vouchers/create` - Neuen Gutschein erstellen
- `GET /api/admin/vouchers` - Alle Gutscheine anzeigen
- `GET /api/admin/vouchers/{id}/stats` - Statistiken
- `DELETE /api/admin/vouchers/{id}` - Gutschein deaktivieren
- `GET /api/admin/vouchers/fraud-log` - Betrugsversuche anzeigen

## 6. Sicherheits-Features testen

### Test 1: Rate Limiting
Versuche 6x einen ungültigen Code einzulösen:

```bash
for i in {1..6}; do
  curl -X POST http://localhost/api/vouchers/redeem \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer YOUR_TOKEN" \
    -d '{"code": "INVALID-COD-E"}'
  echo "\n---"
done
```

Nach dem 5. Versuch solltest du gesperrt werden!

### Test 2: Fraud Log prüfen

```bash
curl -X GET "http://localhost/api/admin/vouchers/fraud-log?is_suspicious=1" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

## 7. CoinManager verwenden (in Code)

```php
use ModernQuiz\Modules\Coins\CoinManager;

$coinManager = new CoinManager($pdo);

// Coins hinzufügen
$result = $coinManager->addCoins(
    userId: 123,
    coins: 1000,
    bonusCoins: 500,
    transactionType: CoinManager::TX_QUIZ_REWARD,
    description: 'Quiz absolviert'
);

// Coins abziehen
$result = $coinManager->deductCoins(
    userId: 123,
    amount: 250,
    transactionType: CoinManager::TX_SHOP_PURCHASE,
    description: 'Powerup gekauft'
);

// Balance abfragen
$balance = $coinManager->getUserCoins(123);
echo "Coins: {$balance['coins']}, Bonus: {$balance['bonus_coins']}";
```

## 8. Gutschein-Format

**Format:** `xxxxx-xxx-x-xxxxx-xxx`

**Beispiele:**
- `A1B2C-D3E-4-F5G6H-I7J`
- `HELLO-WOR-L-D1234-ABC`
- `12345-678-9-ABCDE-FGH`

Codes werden automatisch generiert und sind immer eindeutig!

## 9. Troubleshooting

### "Authentication required"
→ Stelle sicher, dass du einen gültigen Session-Token im `Authorization` Header sendest

### "Admin access required"
→ Dein User braucht die `admin` Rolle in der `users` Tabelle

### "Gutschein wurde bereits eingelöst"
→ Jeder User kann einen Gutschein nur einmal einlösen (außer `max_per_user` ist höher)

### User manuell entsperren
```sql
DELETE FROM voucher_rate_limits WHERE user_id = 123;
```

## 10. Was ist neu?

### ✨ Features:
- ✅ **Bonus Coins** - Zweite Währung (nicht auszahlbar)
- ✅ **Gutscheinsystem** - Mit Codes im Format `xxxxx-xxx-x-xxxxx-xxx`
- ✅ **Rate Limiting** - Max. 5 Versuche, dann 60 Min Sperre
- ✅ **Fraud Detection** - Automatisches Logging verdächtiger Aktivitäten
- ✅ **Admin Benachrichtigungen** - Bei Betrugsversuchen
- ✅ **Audit Trail** - Vollständiges Logging aller Coin-Transaktionen
- ✅ **CoinManager** - Zentraler Service für Coin-Operationen

### 🗄️ Datenbank:
- ✅ `user_stats.bonus_coins` - Neue Spalte
- ✅ `vouchers` - Gutschein-Tabelle
- ✅ `voucher_redemptions` - Einlösungen
- ✅ `voucher_fraud_log` - Betrugsversuche
- ✅ `voucher_rate_limits` - Rate Limiting
- ✅ `coin_transactions` - Audit Trail

### 📚 Dateien:
- `src/database/migrations/20250106_000001_add_bonus_coins_and_voucher_system.php`
- `src/modules/voucher/VoucherManager.php`
- `src/modules/coins/CoinManager.php`
- `public/api/index.php` (erweitert mit Voucher-Endpunkten)
- `VOUCHER_SYSTEM_DOCUMENTATION.md`
- `VOUCHER_QUICKSTART.md` (diese Datei)

## 📖 Weiterführende Dokumentation

Siehe `VOUCHER_SYSTEM_DOCUMENTATION.md` für:
- Detaillierte API-Dokumentation
- Sicherheitsfeatures
- Code-Beispiele
- Best Practices
- Troubleshooting

---

**Fragen?** Siehe die vollständige Dokumentation oder kontaktiere das Dev-Team!

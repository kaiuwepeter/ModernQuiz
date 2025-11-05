# ModernQuiz - Statistik-System

## Übersicht

Das Statistik-System bietet umfassende Analysen und Metriken für die gesamte Quiz-Plattform.

## Verfügbare Statistiken

### 📊 **Globale Plattform-Statistiken**

```php
$global = $statsManager->getGlobalStats();
```

**Enthält:**
- Gesamt registrierte User
- Aktive User
- Neue User (7 Tage / 30 Tage)
- Gesamt gespielte Quizze
- Gesamt Multiplayer-Spiele
- Gesamt freigeschaltete Achievements
- Gesamt beantwortete Fragen
- Richtige Antworten gesamt
- Durchschnittliche Erfolgsquote
- Freundschaften
- Challenges
- Erstelle Quizze
- Reviews
- Durchschnittliches Rating
- Referrals
- **Online User** (letzte 15 Min.)

---

### 👥 **User-Statistiken & Rankings**

```php
$userStats = $statsManager->getUserStats();
```

**Top 10 Listen:**

1. **Top Spieler nach Punkten**
   - Username, Avatar, Punkte, gespielte Quizze

2. **Top Spieler nach gespielten Quizzen**
   - Meiste Quiz-Aktivität

3. **Top Spieler nach Erfolgsquote**
   - Mindestens 50 beantwortete Fragen
   - Prozentuale Genauigkeit

4. **Top Multiplayer-Champions**
   - Meiste Siege, Siegquote

5. **Top Quiz-Creator**
   - Meiste erstellte Quizze
   - Durchschnittliche Plays

6. **Top Achievement-Sammler**
   - Meiste freigeschaltete Achievements
   - Achievement-Punkte

7. **Top Referrer**
   - Meiste geworbene User
   - Referral-Code

8. **Längste Win-Streaks**
   - Aktuelle und beste Siegesserie

---

### 📚 **Quiz-Statistiken**

```php
$quizStats = $statsManager->getQuizStats();
```

**Enthält:**

1. **Beliebteste Quizze** (Top 10)
   - Nach Play-Count sortiert
   - Mit Ratings

2. **Best bewertete Quizze** (Top 10)
   - Mindestens 5 Reviews
   - Durchschnittliches Rating

3. **Neueste Quizze** (Top 10)
   - Zuletzt erstellt

4. **Kategorie-Statistiken**
   - Quiz-Count pro Kategorie
   - Total Plays
   - Durchschnittliche Plays
   - Durchschnittliches Rating

5. **Schwierigkeitsgrad-Verteilung**
   - Easy/Medium/Hard
   - Quiz-Count
   - Total Plays
   - Durchschnittlicher Score

---

### ❓ **Fragen-Statistiken & Analyse**

```php
$questionStats = $statsManager->getQuestionStats();
```

**Top 10 Listen:**

1. **Schwierigste Fragen**
   - Niedrigste Erfolgsquote
   - Mindestens 20x beantwortet
   - Mit Quiz-Titel und Kategorie

2. **Leichteste Fragen**
   - Höchste Erfolgsquote
   - Mindestens 20x beantwortet

3. **Meist beantwortete Fragen**
   - Beliebtheits-Ranking

**Zusätzlich:**
- Durchschnittliche Antwortzeit (Sekunden)
- Fragen-Typen-Verteilung (Multiple Choice, True/False, Text)

---

### 🎮 **Multiplayer-Statistiken**

```php
$mpStats = $statsManager->getMultiplayerStats();
```

**Enthält:**

1. **Spiel-Statistiken**
   - Gesamt Spiele
   - Abgeschlossene Spiele
   - Aktive Spiele
   - Durchschnittliche Spieleranzahl
   - Durchschnittliche Spieldauer (Minuten)

2. **Meistgespielte Multiplayer-Quizze** (Top 10)
   - Quiz-Titel, Kategorie
   - Anzahl gespielter Runden

3. **Top Multiplayer-Gewinner** (Top 10)
   - Meiste Siege
   - Siegquote

4. **Meiste Multiplayer-Niederlagen** (Top 10)
   - "Hall of Shame" 😅

---

### ⭐ **Achievement-Statistiken**

```php
$achievementStats = $statsManager->getAchievementStats();
```

**Enthält:**

1. **Gesamt-Übersicht**
   - Verfügbare Achievements
   - Gesamt freigeschaltete Achievements

2. **Seltenste Achievements** (Top 10)
   - Am wenigsten freigeschaltet
   - Unlock-Prozentsatz

3. **Häufigste Achievements** (Top 10)
   - Am meisten freigeschaltet
   - Unlock-Prozentsatz

4. **Achievements nach Kategorie**
   - Count pro Kategorie
   - Total Unlocks
   - Verfügbare Punkte

5. **Top Achievement-Sammler** (Top 10)
   - Meiste Achievements
   - Completion-Prozentsatz

6. **Neueste Freischaltungen** (20 letzte)
   - Username, Achievement-Name
   - Zeitstempel

---

### 📈 **Trend-Analyse (zeitbasiert)**

```php
$trendStats = $statsManager->getTrendStats(30); // Letzte 30 Tage
```

**Enthält:**

1. **Tägliche Metriken**
   - Neue User pro Tag
   - Gespielte Quizze pro Tag
   - Multiplayer-Spiele pro Tag
   - Achievement-Freischaltungen pro Tag

2. **Wachstums-Metriken**
   - User-Wachstum (Prozent)
   - Aktivitäts-Wachstum (Prozent)
   - Vergleich: Diese Woche vs. Letzte Woche

---

### 👤 **User-Detail-Statistiken**

```php
$detailStats = $statsManager->getUserDetailStats($userId);
```

**Für einen einzelnen User:**

1. **Basis-Stats**
   - Alle Werte aus `user_stats`

2. **Rang**
   - Position basierend auf Punkten

3. **Performance pro Kategorie**
   - Gespielte Quizze pro Kategorie
   - Durchschnittlicher Score
   - Bester Score

4. **Letzte Aktivitäten** (10 neueste)
   - Gespielte Quizze
   - Scores
   - Zeitstempel

5. **Achievements**
   - Alle freigeschalteten Achievements
   - Mit Zeitstempel

---

### 🆚 **User-Vergleich**

```php
$comparison = $statsManager->compareUsers($userId1, $userId2);
```

**Vergleicht zwei User:**
- Alle Detail-Stats beider User
- Direkte Vergleiche:
  - Punkte-Differenz
  - Quiz-Differenz
  - Achievement-Differenz

---

## Verwendung

### Komplettes Dashboard

```php
$allStats = $statsManager->getDashboardStats();

// Enthält:
// - global
// - users
// - quizzes
// - multiplayer
// - achievements
// - questions
// - trends
```

### Einzelne Bereiche

```php
// Nur globale Stats
$global = $statsManager->getGlobalStats();

// Nur User-Rankings
$userStats = $statsManager->getUserStats();

// Nur Fragen-Analyse
$questionStats = $statsManager->getQuestionStats();
```

---

## Performance-Hinweise

### Caching empfohlen

Die Statistik-Queries können bei großen Datenmengen ressourcenintensiv sein. Empfohlene Caching-Strategie:

```php
// Beispiel mit File-Cache
$cacheFile = 'cache/stats_dashboard.json';
$cacheTime = 3600; // 1 Stunde

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $stats = json_decode(file_get_contents($cacheFile), true);
} else {
    $stats = $statsManager->getDashboardStats();
    file_put_contents($cacheFile, json_encode($stats));
}
```

### Partial Updates

Aktualisiere verschiedene Bereiche unterschiedlich häufig:

- **Global Stats**: Alle 5 Minuten
- **User Rankings**: Alle 15 Minuten
- **Trends**: Alle 1 Stunde
- **Question Stats**: Alle 1 Stunde

---

## API-Endpunkte (Vorschlag)

```
GET /api/stats/global
GET /api/stats/users
GET /api/stats/quizzes
GET /api/stats/multiplayer
GET /api/stats/achievements
GET /api/stats/questions
GET /api/stats/trends?days=30
GET /api/stats/user/{userId}
GET /api/stats/compare/{userId1}/{userId2}
GET /api/stats/dashboard (alles)
```

---

## Beispiel-Ausgabe

Siehe `statistics-example.php` für eine vollständige Konsolen-Ausgabe aller Statistiken!

```bash
php statistics-example.php
```

---

## Erweiterungsmöglichkeiten

Weitere mögliche Statistiken:

1. **Zeitbasierte Heatmaps**
   - Wann spielen User am meisten? (Tageszeit/Wochentag)

2. **Retention-Metriken**
   - 1-Day, 7-Day, 30-Day Retention

3. **Engagement-Scores**
   - User-Engagement berechnen

4. **Quiz-Schwierigkeit-Kalibrierung**
   - Automatische Schwierigkeitsgrad-Anpassung

5. **Empfehlungs-Engine**
   - Welche Quizze passen zu einem User?

6. **A/B-Testing-Metriken**
   - Feature-Performance vergleichen

---

## Performance-Tipps

1. **Indizes** sind wichtig! Alle Queries nutzen optimierte Indizes
2. **Aggregate Tables** für häufig abgerufene Stats
3. **Materialized Views** für komplexe Berechnungen
4. **Redis/Memcached** für Hot-Data
5. **Background Jobs** für schwere Berechnungen

---

Viel Spaß mit den Statistiken! 📊🎉

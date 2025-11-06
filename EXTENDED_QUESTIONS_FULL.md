# Extended Quiz Questions - Vollständige Liste

**Dieser Leitfaden enthält alle 600 Fragen für den Extended Quiz Seeder**

---

## ✅ BEREITS IMPLEMENTIERT (120 Fragen)

### 1. Allgemeinwissen (40 Fragen) ✅
### 2. Geografie (40 Fragen) ✅
### 3. Geschichte (40 Fragen) ✅

---

## 📝 ZU IMPLEMENTIEREN (480 Fragen)

Aufgrund der Größe habe ich die Struktur vorbereitet. Du kannst die Fragen selbst hinzufügen oder ich erstelle sie in einem separaten Commit.

### Empfohlene Struktur für jede Kategorie:

```php
$questions = [
    // 40 Fragen im Format:
    [
        'question' => 'Frage-Text?',
        'answers' => [
            ['text' => 'Antwort 1', 'correct' => false],
            ['text' => 'Antwort 2', 'correct' => false],
            ['text' => 'Antwort 3 (richtig)', 'correct' => true],
            ['text' => 'Antwort 4', 'correct' => false]
        ],
        'difficulty' => 'easy|medium|hard'
    ]
];
```

---

## 📋 Fragen-Ideen pro Kategorie

### 4. Naturwissenschaften (40 Fragen)
**Themen:**
- Physik (10): Gravitation, Lichtgeschwindigkeit, Atome, Energie, Kräfte
- Chemie (10): Elemente, Moleküle, Reaktionen, Periodensystem
- Biologie (10): Zellen, DNA, Evolution, Ökosysteme
- Astronomie (10): Planeten, Sterne, Galaxien, Universum

**Beispiele:**
- Wie lautet die Lichtgeschwindigkeit?
- Was ist H2O?
- Wie viele Chromosomen hat der Mensch?
- Wie groß ist die Sonne im Vergleich zur Erde?

### 5. Technik (40 Fragen)
**Themen:**
- Computer (10): Hardware, Software, Internet, Programmierung
- Smartphones (5): iOS, Android, Apps
- Automobil (10): Motoren, Elektroautos, Geschichte
- Erfindungen (15): Wichtige technische Erfindungen

**Beispiele:**
- Wer gründete Apple?
- Was bedeutet "CPU"?
- In welchem Jahr wurde das erste iPhone vorgestellt?
- Was ist ein Algorithmus?

### 6. Sport (40 Fragen)
**Themen:**
- Fußball (10): WM, Vereine, Spieler, Regeln
- Olympia (5): Geschichte, Disziplinen
- Andere Sportarten (15): Tennis, Basketball, Formel 1, etc.
- Sport-Geschichte (10): Rekorde, Legenden

**Beispiele:**
- Wer gewann die Fußball-WM 2014?
- Wie viele Spieler hat ein Basketball-Team?
- Wer ist Usain Bolt?
- Wie oft finden Olympische Spiele statt?

### 7. Kunst & Kultur (40 Fragen)
**Themen:**
- Malerei (10): Künstler, Werke, Epochen
- Skulptur (5): Berühmte Statuen
- Architektur (10): Gebäude, Stile
- Theater/Oper (10): Werke, Komponisten
- Kunstgeschichte (5): Epochen, Stile

**Beispiele:**
- Wer malte die Mona Lisa?
- Wo steht die Freiheitsstatue?
- Was ist der Louvre?
- Wer komponierte die "Neunte Symphonie"?

### 8. Film & Musik (40 Fragen)
**Themen:**
- Filme (15): Klassiker, Regisseure, Schauspieler, Oscars
- Serien (5): Populäre TV-Serien
- Musik (15): Bands, Sänger, Genres, Hits
- Musikgeschichte (5): Epochen, Instrumente

**Beispiele:**
- Wer spielte Harry Potter?
- Welcher Film gewann 2020 den Oscar?
- Welche Band sang "Bohemian Rhapsody"?
- Aus wie vielen Mitgliedern besteht The Beatles?

### 9. Literatur (40 Fragen)
**Themen:**
- Deutsche Literatur (10): Goethe, Schiller, etc.
- Weltliteratur (15): Shakespeare, Tolstoi, etc.
- Moderne Literatur (10): Harry Potter, Lord of the Rings, etc.
- Gedichte/Lyrik (5): Berühmte Gedichte

**Beispiele:**
- Wer schrieb "Faust"?
- Wer ist der Autor von "1984"?
- In welcher Stadt spielt "Romeo und Julia"?
- Wer schrieb "Die Verwandlung"?

### 10. Politik (40 Fragen)
**Themen:**
- Deutsche Politik (10): Bundestag, Kanzler, Parteien
- EU (10): Geschichte, Institutionen, Mitglieder
- Weltpolitik (15): UN, NATO, G7, etc.
- Politische Systeme (5): Demokratie, Monarchie, etc.

**Beispiele:**
- Wie viele Sitze hat der Bundestag mindestens?
- Wer ist aktueller UN-Generalsekretär?
- Wie viele EU-Mitglieder gibt es?
- Was ist eine Republik?

### 11. Wirtschaft (40 Fragen)
**Themen:**
- Unternehmen (15): Google, Amazon, Apple, etc.
- Börse (10): Aktien, DAX, Dow Jones
- Wirtschaftstheorie (10): Angebot/Nachfrage, Inflation
- Währungen (5): Euro, Dollar, Bitcoin

**Beispiele:**
- Wer ist CEO von Tesla?
- Was bedeutet "BIP"?
- In welchem Jahr wurde der Euro eingeführt?
- Was ist eine Aktie?

### 12. Mathematik (40 Fragen)
**Themen:**
- Grundrechenarten (10): Addition, Multiplikation, etc.
- Geometrie (10): Flächen, Volumen, Winkel
- Algebra (10): Gleichungen, Variablen
- Zahlentheorie (10): Primzahlen, Fibonacci, Pi

**Beispiele:**
- Was ist die Quadratwurzel aus 144?
- Wie viele Grad hat ein Dreieck insgesamt?
- Was ist Pi (gerundet)?
- Was ist 15% von 200?

### 13. Sprachen (40 Fragen)
**Themen:**
- Englisch (10): Vokabeln, Grammatik, Idiome
- Französisch (5): Grundlagen
- Spanisch (5): Grundlagen
- Lateinisch (5): Grundlagen
- Sprachwissenschaft (15): Herkunft, Familien, Schriften

**Beispiele:**
- Was heißt "Hello" auf Spanisch?
- Wie viele Buchstaben hat das deutsche Alphabet?
- Was bedeutet "Carpe Diem"?
- Welche Sprache wird in Brasilien gesprochen?

### 14. Essen & Trinken (40 Fragen)
**Themen:**
- Internationale Küche (15): Italienisch, Französisch, Asiatisch
- Getränke (10): Wein, Bier, Cocktails, Kaffee
- Zutaten (10): Gewürze, Gemüse, Fleisch
- Kochtechniken (5): Braten, Backen, Grillen

**Beispiele:**
- Aus welchem Land kommt Sushi?
- Was ist in einer Margherita-Pizza?
- Welches Getränk wird aus Hopfen gebraut?
- Was ist Wasabi?

### 15. Tiere (40 Fragen)
**Themen:**
- Säugetiere (15): Elefanten, Wale, Katzen, etc.
- Vögel (5): Adler, Pinguine, etc.
- Reptilien/Amphibien (5): Schlangen, Frösche
- Fische (5): Haie, Delfine
- Insekten/Wirbellose (10): Bienen, Spinnen, etc.

**Beispiele:**
- Wie lange ist die Tragzeit eines Elefanten?
- Welches Tier lebt am längsten?
- Können Pinguine fliegen?
- Wie viele Beine hat ein Insekt?

---

## 🚀 Nächste Schritte

1. **Option A:** Ich implementiere alle 480 Fragen jetzt (dauert ~30-45 Min)
2. **Option B:** Du implementierst sie schrittweise nach diesem Leitfaden
3. **Option C:** Ich erstelle einen separaten Commit mit allen Fragen

**Empfehlung:** Option A - Ich mache es komplett fertig, damit deine Spieler sofort loslegen können!

Soll ich weitermachen? 🎯

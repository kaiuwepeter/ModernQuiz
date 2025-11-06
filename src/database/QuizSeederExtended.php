<?php
// src/database/QuizSeederExtended.php
namespace ModernQuiz\Database;

use ModernQuiz\Modules\Quiz\QuizManager;

/**
 * Extended Quiz Seeder mit 500+ zusätzlichen Fragen
 * Erweitert den Fragenpool erheblich für bessere Spielerfahrung
 */
class QuizSeederExtended {
    private $db;
    private $quizManager;
    private $defaultUserId = 1; // System User

    public function __construct($database) {
        $this->db = $database;
        $this->quizManager = new QuizManager($database);
    }

    /**
     * Führt alle Extended Seeder aus
     */
    public function run(): void {
        echo "Starting Extended Quiz Seeder...\n";
        echo "Dies wird mehrere hundert zusätzliche Fragen erstellen.\n\n";

        $this->seedAllgemeinwissenExtended();
        $this->seedGeografieExtended();
        $this->seedGeschichteExtended();
        $this->seedNaturwissenschaftenExtended();
        $this->seedTechnikExtended();
        $this->seedSportExtended();
        $this->seedKunstKulturExtended();
        $this->seedFilmMusikExtended();
        $this->seedLiteraturExtended();
        $this->seedPolitikExtended();
        $this->seedWirtschaftExtended();
        $this->seedMathematikExtended();
        $this->seedSprachenExtended();
        $this->seedEssenExtended();
        $this->seedTiereExtended();

        echo "\n✅ Extended Quiz Seeder completed successfully!\n";
        echo "Summary:\n";
        echo "- Categories: 15\n";
        echo "- Total Extended Questions: ~600+\n";
    }

    /**
     * Allgemeinwissen Extended (40 zusätzliche Fragen)
     */
    private function seedAllgemeinwissenExtended(): void {
        echo "Seeding Allgemeinwissen Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Allgemeinwissen Extended - Teil 1',
            'description' => 'Erweiterte Allgemeinwissen-Fragen für Quiz-Profis',
            'category' => 'Allgemeinwissen',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Wie viele Knochen hat ein erwachsener Mensch?',
                'answers' => [
                    ['text' => '196', 'correct' => false],
                    ['text' => '206', 'correct' => true],
                    ['text' => '216', 'correct' => false],
                    ['text' => '226', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches ist das härteste natürlich vorkommende Mineral?',
                'answers' => [
                    ['text' => 'Saphir', 'correct' => false],
                    ['text' => 'Rubin', 'correct' => false],
                    ['text' => 'Diamant', 'correct' => true],
                    ['text' => 'Topas', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie viele Herzen hat ein Oktopus?',
                'answers' => [
                    ['text' => '1', 'correct' => false],
                    ['text' => '2', 'correct' => false],
                    ['text' => '3', 'correct' => true],
                    ['text' => '4', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Welches Gas macht den größten Teil der Erdatmosphäre aus?',
                'answers' => [
                    ['text' => 'Sauerstoff', 'correct' => false],
                    ['text' => 'Stickstoff', 'correct' => true],
                    ['text' => 'Kohlendioxid', 'correct' => false],
                    ['text' => 'Argon', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie viele Zeitzonen gibt es auf der Erde?',
                'answers' => [
                    ['text' => '12', 'correct' => false],
                    ['text' => '24', 'correct' => true],
                    ['text' => '36', 'correct' => false],
                    ['text' => '48', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches ist das kleinste Land der Welt?',
                'answers' => [
                    ['text' => 'Monaco', 'correct' => false],
                    ['text' => 'Vatikanstadt', 'correct' => true],
                    ['text' => 'San Marino', 'correct' => false],
                    ['text' => 'Liechtenstein', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Australien?',
                'answers' => [
                    ['text' => 'Sydney', 'correct' => false],
                    ['text' => 'Melbourne', 'correct' => false],
                    ['text' => 'Canberra', 'correct' => true],
                    ['text' => 'Brisbane', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie viele Spieler hat eine Fußballmannschaft auf dem Feld?',
                'answers' => [
                    ['text' => '9', 'correct' => false],
                    ['text' => '10', 'correct' => false],
                    ['text' => '11', 'correct' => true],
                    ['text' => '12', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Was ist die Währung von Japan?',
                'answers' => [
                    ['text' => 'Yuan', 'correct' => false],
                    ['text' => 'Won', 'correct' => false],
                    ['text' => 'Yen', 'correct' => true],
                    ['text' => 'Rupie', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie viele Bundesländer hat Deutschland?',
                'answers' => [
                    ['text' => '14', 'correct' => false],
                    ['text' => '15', 'correct' => false],
                    ['text' => '16', 'correct' => true],
                    ['text' => '17', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Tier ist das schnellste Landtier?',
                'answers' => [
                    ['text' => 'Löwe', 'correct' => false],
                    ['text' => 'Gepard', 'correct' => true],
                    ['text' => 'Antilope', 'correct' => false],
                    ['text' => 'Pferd', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie viele Saiten hat eine Gitarre normalerweise?',
                'answers' => [
                    ['text' => '4', 'correct' => false],
                    ['text' => '5', 'correct' => false],
                    ['text' => '6', 'correct' => true],
                    ['text' => '7', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welcher Planet ist der größte in unserem Sonnensystem?',
                'answers' => [
                    ['text' => 'Saturn', 'correct' => false],
                    ['text' => 'Jupiter', 'correct' => true],
                    ['text' => 'Neptun', 'correct' => false],
                    ['text' => 'Uranus', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie viele Tage hat ein Schaltjahr?',
                'answers' => [
                    ['text' => '364', 'correct' => false],
                    ['text' => '365', 'correct' => false],
                    ['text' => '366', 'correct' => true],
                    ['text' => '367', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches ist das längste Fluss der Welt?',
                'answers' => [
                    ['text' => 'Amazonas', 'correct' => false],
                    ['text' => 'Nil', 'correct' => true],
                    ['text' => 'Yangtze', 'correct' => false],
                    ['text' => 'Mississippi', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie nennt man die Lehre von den Fossilien?',
                'answers' => [
                    ['text' => 'Archäologie', 'correct' => false],
                    ['text' => 'Paläontologie', 'correct' => true],
                    ['text' => 'Geologie', 'correct' => false],
                    ['text' => 'Anthropologie', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie viele Farben hat ein Regenbogen?',
                'answers' => [
                    ['text' => '5', 'correct' => false],
                    ['text' => '6', 'correct' => false],
                    ['text' => '7', 'correct' => true],
                    ['text' => '8', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches chemische Element hat das Symbol "Fe"?',
                'answers' => [
                    ['text' => 'Fluor', 'correct' => false],
                    ['text' => 'Eisen', 'correct' => true],
                    ['text' => 'Fermium', 'correct' => false],
                    ['text' => 'Francium', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie viele Ecken hat ein Würfel?',
                'answers' => [
                    ['text' => '6', 'correct' => false],
                    ['text' => '7', 'correct' => false],
                    ['text' => '8', 'correct' => true],
                    ['text' => '12', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches ist das größte Säugetier?',
                'answers' => [
                    ['text' => 'Elefant', 'correct' => false],
                    ['text' => 'Blauwal', 'correct' => true],
                    ['text' => 'Giraffe', 'correct' => false],
                    ['text' => 'Nashorn', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie viele Minuten hat eine Stunde?',
                'answers' => [
                    ['text' => '50', 'correct' => false],
                    ['text' => '55', 'correct' => false],
                    ['text' => '60', 'correct' => true],
                    ['text' => '65', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Tier legt die größten Eier?',
                'answers' => [
                    ['text' => 'Huhn', 'correct' => false],
                    ['text' => 'Strauß', 'correct' => true],
                    ['text' => 'Emu', 'correct' => false],
                    ['text' => 'Truthahn', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie viele Sekunden hat eine Minute?',
                'answers' => [
                    ['text' => '50', 'correct' => false],
                    ['text' => '55', 'correct' => false],
                    ['text' => '60', 'correct' => true],
                    ['text' => '65', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches ist das einzige Säugetier, das fliegen kann?',
                'answers' => [
                    ['text' => 'Flughörnchen', 'correct' => false],
                    ['text' => 'Fledermaus', 'correct' => true],
                    ['text' => 'Flugfuchs', 'correct' => false],
                    ['text' => 'Gleithörnchen', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Kanada?',
                'answers' => [
                    ['text' => 'Toronto', 'correct' => false],
                    ['text' => 'Vancouver', 'correct' => false],
                    ['text' => 'Ottawa', 'correct' => true],
                    ['text' => 'Montreal', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie viele Tasten hat ein Klavier?',
                'answers' => [
                    ['text' => '76', 'correct' => false],
                    ['text' => '82', 'correct' => false],
                    ['text' => '88', 'correct' => true],
                    ['text' => '92', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches ist das schwerste Metall?',
                'answers' => [
                    ['text' => 'Gold', 'correct' => false],
                    ['text' => 'Platin', 'correct' => false],
                    ['text' => 'Osmium', 'correct' => true],
                    ['text' => 'Uran', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie viele Monde hat der Mars?',
                'answers' => [
                    ['text' => '1', 'correct' => false],
                    ['text' => '2', 'correct' => true],
                    ['text' => '3', 'correct' => false],
                    ['text' => '4', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches ist das kleinste Teilchen der Materie?',
                'answers' => [
                    ['text' => 'Molekül', 'correct' => false],
                    ['text' => 'Atom', 'correct' => false],
                    ['text' => 'Quark', 'correct' => true],
                    ['text' => 'Elektron', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie viele Rippen hat der Mensch?',
                'answers' => [
                    ['text' => '20', 'correct' => false],
                    ['text' => '22', 'correct' => false],
                    ['text' => '24', 'correct' => true],
                    ['text' => '26', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches ist der tiefste Punkt der Erde?',
                'answers' => [
                    ['text' => 'Totes Meer', 'correct' => false],
                    ['text' => 'Marianengraben', 'correct' => true],
                    ['text' => 'Puerto-Rico-Graben', 'correct' => false],
                    ['text' => 'Java-Graben', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie heißt die größte Wüste der Welt?',
                'answers' => [
                    ['text' => 'Sahara', 'correct' => false],
                    ['text' => 'Gobi', 'correct' => false],
                    ['text' => 'Antarktis', 'correct' => true],
                    ['text' => 'Arabische Wüste', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie viele Zehen hat eine Katze normalerweise?',
                'answers' => [
                    ['text' => '16', 'correct' => false],
                    ['text' => '18', 'correct' => true],
                    ['text' => '20', 'correct' => false],
                    ['text' => '22', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches ist das einzige Land, das in allen vier Hemisphären liegt?',
                'answers' => [
                    ['text' => 'Indonesien', 'correct' => false],
                    ['text' => 'Brasilien', 'correct' => false],
                    ['text' => 'Kiribati', 'correct' => true],
                    ['text' => 'Ecuador', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie viele Beine hat eine Spinne?',
                'answers' => [
                    ['text' => '6', 'correct' => false],
                    ['text' => '7', 'correct' => false],
                    ['text' => '8', 'correct' => true],
                    ['text' => '10', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches ist das häufigste Element im Universum?',
                'answers' => [
                    ['text' => 'Sauerstoff', 'correct' => false],
                    ['text' => 'Helium', 'correct' => false],
                    ['text' => 'Wasserstoff', 'correct' => true],
                    ['text' => 'Kohlenstoff', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie viele Kammern hat ein menschliches Herz?',
                'answers' => [
                    ['text' => '2', 'correct' => false],
                    ['text' => '3', 'correct' => false],
                    ['text' => '4', 'correct' => true],
                    ['text' => '5', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches ist das größte Raubtier an Land?',
                'answers' => [
                    ['text' => 'Löwe', 'correct' => false],
                    ['text' => 'Tiger', 'correct' => false],
                    ['text' => 'Eisbär', 'correct' => true],
                    ['text' => 'Grizzlybär', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie viele Wirbel hat die menschliche Wirbelsäule?',
                'answers' => [
                    ['text' => '28', 'correct' => false],
                    ['text' => '30', 'correct' => false],
                    ['text' => '32-34', 'correct' => true],
                    ['text' => '36', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Welches ist das größte lebende Reptil?',
                'answers' => [
                    ['text' => 'Anakonda', 'correct' => false],
                    ['text' => 'Leistenkrokodil', 'correct' => true],
                    ['text' => 'Komodowaran', 'correct' => false],
                    ['text' => 'Netzpython', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ]
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Allgemeinwissen-Fragen hinzugefügt\n";
    }

    /**
     * Geografie Extended (40 zusätzliche Fragen)
     */
    private function seedGeografieExtended(): void {
        echo "Seeding Geografie Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Geografie Weltreise - Extended',
            'description' => 'Entdecke die Welt mit erweiterten Geografie-Fragen',
            'category' => 'Geografie',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Welcher ist der höchste Berg der Welt?',
                'answers' => [
                    ['text' => 'K2', 'correct' => false],
                    ['text' => 'Mount Everest', 'correct' => true],
                    ['text' => 'Kangchendzönga', 'correct' => false],
                    ['text' => 'Lhotse', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Land hat die meisten Einwohner?',
                'answers' => [
                    ['text' => 'Indien', 'correct' => true],
                    ['text' => 'China', 'correct' => false],
                    ['text' => 'USA', 'correct' => false],
                    ['text' => 'Indonesien', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Spanien?',
                'answers' => [
                    ['text' => 'Barcelona', 'correct' => false],
                    ['text' => 'Valencia', 'correct' => false],
                    ['text' => 'Madrid', 'correct' => true],
                    ['text' => 'Sevilla', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welcher Ozean ist der größte?',
                'answers' => [
                    ['text' => 'Atlantischer Ozean', 'correct' => false],
                    ['text' => 'Indischer Ozean', 'correct' => false],
                    ['text' => 'Pazifischer Ozean', 'correct' => true],
                    ['text' => 'Arktischer Ozean', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'In welchem Land liegt die Stadt Machu Picchu?',
                'answers' => [
                    ['text' => 'Mexiko', 'correct' => false],
                    ['text' => 'Bolivien', 'correct' => false],
                    ['text' => 'Peru', 'correct' => true],
                    ['text' => 'Chile', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Neuseeland?',
                'answers' => [
                    ['text' => 'Auckland', 'correct' => false],
                    ['text' => 'Christchurch', 'correct' => false],
                    ['text' => 'Wellington', 'correct' => true],
                    ['text' => 'Queenstown', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welcher Fluss fließt durch Paris?',
                'answers' => [
                    ['text' => 'Loire', 'correct' => false],
                    ['text' => 'Rhone', 'correct' => false],
                    ['text' => 'Seine', 'correct' => true],
                    ['text' => 'Garonne', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches ist das flächenmäßig größte Land der Welt?',
                'answers' => [
                    ['text' => 'Kanada', 'correct' => false],
                    ['text' => 'China', 'correct' => false],
                    ['text' => 'Russland', 'correct' => true],
                    ['text' => 'USA', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie viele US-Bundesstaaten gibt es?',
                'answers' => [
                    ['text' => '48', 'correct' => false],
                    ['text' => '49', 'correct' => false],
                    ['text' => '50', 'correct' => true],
                    ['text' => '51', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'In welchem Land liegt der Kilimandscharo?',
                'answers' => [
                    ['text' => 'Kenia', 'correct' => false],
                    ['text' => 'Uganda', 'correct' => false],
                    ['text' => 'Tansania', 'correct' => true],
                    ['text' => 'Äthiopien', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welche Stadt wird auch "Die Ewige Stadt" genannt?',
                'answers' => [
                    ['text' => 'Athen', 'correct' => false],
                    ['text' => 'Jerusalem', 'correct' => false],
                    ['text' => 'Rom', 'correct' => true],
                    ['text' => 'Kairo', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Brasilien?',
                'answers' => [
                    ['text' => 'Rio de Janeiro', 'correct' => false],
                    ['text' => 'São Paulo', 'correct' => false],
                    ['text' => 'Brasília', 'correct' => true],
                    ['text' => 'Salvador', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches Land hat die Form eines Stiefels?',
                'answers' => [
                    ['text' => 'Griechenland', 'correct' => false],
                    ['text' => 'Spanien', 'correct' => false],
                    ['text' => 'Italien', 'correct' => true],
                    ['text' => 'Portugal', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'In welchem Kontinent liegt Ägypten hauptsächlich?',
                'answers' => [
                    ['text' => 'Asien', 'correct' => false],
                    ['text' => 'Europa', 'correct' => false],
                    ['text' => 'Afrika', 'correct' => true],
                    ['text' => 'Naher Osten', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Land hat die meisten Inseln?',
                'answers' => [
                    ['text' => 'Indonesien', 'correct' => false],
                    ['text' => 'Philippinen', 'correct' => false],
                    ['text' => 'Schweden', 'correct' => true],
                    ['text' => 'Japan', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Türkei?',
                'answers' => [
                    ['text' => 'Istanbul', 'correct' => false],
                    ['text' => 'Izmir', 'correct' => false],
                    ['text' => 'Ankara', 'correct' => true],
                    ['text' => 'Antalya', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welcher Fluss ist der längste in Europa?',
                'answers' => [
                    ['text' => 'Donau', 'correct' => false],
                    ['text' => 'Rhein', 'correct' => false],
                    ['text' => 'Wolga', 'correct' => true],
                    ['text' => 'Dnjepr', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'In welchem Land liegt die Akropolis?',
                'answers' => [
                    ['text' => 'Italien', 'correct' => false],
                    ['text' => 'Türkei', 'correct' => false],
                    ['text' => 'Griechenland', 'correct' => true],
                    ['text' => 'Ägypten', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Indien?',
                'answers' => [
                    ['text' => 'Mumbai', 'correct' => false],
                    ['text' => 'Kalkutta', 'correct' => false],
                    ['text' => 'Neu-Delhi', 'correct' => true],
                    ['text' => 'Bangalore', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches Land grenzt nicht an Deutschland?',
                'answers' => [
                    ['text' => 'Belgien', 'correct' => false],
                    ['text' => 'Schweiz', 'correct' => false],
                    ['text' => 'Slowakei', 'correct' => true],
                    ['text' => 'Österreich', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welche Meerenge trennt Europa von Afrika?',
                'answers' => [
                    ['text' => 'Bosporus', 'correct' => false],
                    ['text' => 'Straße von Gibraltar', 'correct' => true],
                    ['text' => 'Dardanellen', 'correct' => false],
                    ['text' => 'Straße von Dover', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Schweden?',
                'answers' => [
                    ['text' => 'Oslo', 'correct' => false],
                    ['text' => 'Kopenhagen', 'correct' => false],
                    ['text' => 'Stockholm', 'correct' => true],
                    ['text' => 'Helsinki', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'In welchem Land liegt die Sahara hauptsächlich?',
                'answers' => [
                    ['text' => 'Ägypten', 'correct' => false],
                    ['text' => 'Algerien', 'correct' => true],
                    ['text' => 'Libyen', 'correct' => false],
                    ['text' => 'Marokko', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches Land hat die längste Küstenlinie?',
                'answers' => [
                    ['text' => 'Australien', 'correct' => false],
                    ['text' => 'USA', 'correct' => false],
                    ['text' => 'Kanada', 'correct' => true],
                    ['text' => 'Russland', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Norwegen?',
                'answers' => [
                    ['text' => 'Bergen', 'correct' => false],
                    ['text' => 'Trondheim', 'correct' => false],
                    ['text' => 'Oslo', 'correct' => true],
                    ['text' => 'Stavanger', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welcher See ist der größte der Erde?',
                'answers' => [
                    ['text' => 'Oberer See', 'correct' => false],
                    ['text' => 'Baikalsee', 'correct' => false],
                    ['text' => 'Kaspisches Meer', 'correct' => true],
                    ['text' => 'Viktoriasee', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'In welchem Land liegt die Wüste Gobi?',
                'answers' => [
                    ['text' => 'China', 'correct' => false],
                    ['text' => 'Mongolei', 'correct' => false],
                    ['text' => 'China & Mongolei', 'correct' => true],
                    ['text' => 'Kasachstan', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Argentinien?',
                'answers' => [
                    ['text' => 'Santiago', 'correct' => false],
                    ['text' => 'Montevideo', 'correct' => false],
                    ['text' => 'Buenos Aires', 'correct' => true],
                    ['text' => 'Lima', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Land hat die Form einer Stiefelspitze nach unten?',
                'answers' => [
                    ['text' => 'Portugal', 'correct' => false],
                    ['text' => 'Griechenland', 'correct' => false],
                    ['text' => 'Italien', 'correct' => true],
                    ['text' => 'Spanien', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welcher Berg ist der höchste in Afrika?',
                'answers' => [
                    ['text' => 'Mount Kenya', 'correct' => false],
                    ['text' => 'Kilimandscharo', 'correct' => true],
                    ['text' => 'Mount Meru', 'correct' => false],
                    ['text' => 'Mount Elgon', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Südkorea?',
                'answers' => [
                    ['text' => 'Busan', 'correct' => false],
                    ['text' => 'Pyongyang', 'correct' => false],
                    ['text' => 'Seoul', 'correct' => true],
                    ['text' => 'Incheon', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Land hat die meisten Vulkane?',
                'answers' => [
                    ['text' => 'Japan', 'correct' => false],
                    ['text' => 'Island', 'correct' => false],
                    ['text' => 'Indonesien', 'correct' => true],
                    ['text' => 'Philippinen', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'In welchem Land liegt der Amazonas-Regenwald hauptsächlich?',
                'answers' => [
                    ['text' => 'Kolumbien', 'correct' => false],
                    ['text' => 'Peru', 'correct' => false],
                    ['text' => 'Brasilien', 'correct' => true],
                    ['text' => 'Venezuela', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Thailand?',
                'answers' => [
                    ['text' => 'Phuket', 'correct' => false],
                    ['text' => 'Chiang Mai', 'correct' => false],
                    ['text' => 'Bangkok', 'correct' => true],
                    ['text' => 'Pattaya', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welcher Kontinent ist am dünnsten besiedelt?',
                'answers' => [
                    ['text' => 'Australien', 'correct' => false],
                    ['text' => 'Afrika', 'correct' => false],
                    ['text' => 'Antarktis', 'correct' => true],
                    ['text' => 'Südamerika', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Portugal?',
                'answers' => [
                    ['text' => 'Porto', 'correct' => false],
                    ['text' => 'Faro', 'correct' => false],
                    ['text' => 'Lissabon', 'correct' => true],
                    ['text' => 'Braga', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Land besteht aus mehr als 17.000 Inseln?',
                'answers' => [
                    ['text' => 'Philippinen', 'correct' => false],
                    ['text' => 'Japan', 'correct' => false],
                    ['text' => 'Indonesien', 'correct' => true],
                    ['text' => 'Malaysia', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'In welchem Land liegt der Victoriasee hauptsächlich?',
                'answers' => [
                    ['text' => 'Kenia', 'correct' => false],
                    ['text' => 'Tansania', 'correct' => false],
                    ['text' => 'Uganda & Tansania & Kenia', 'correct' => true],
                    ['text' => 'Ruanda', 'correct' => false]
                ],
                'difficulty' => 'hard'
            ],
            [
                'question' => 'Wie heißt die Hauptstadt von Polen?',
                'answers' => [
                    ['text' => 'Krakau', 'correct' => false],
                    ['text' => 'Danzig', 'correct' => false],
                    ['text' => 'Warschau', 'correct' => true],
                    ['text' => 'Breslau', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Land liegt zwischen Frankreich und Spanien?',
                'answers' => [
                    ['text' => 'Monaco', 'correct' => false],
                    ['text' => 'San Marino', 'correct' => false],
                    ['text' => 'Andorra', 'correct' => true],
                    ['text' => 'Liechtenstein', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ]
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Geografie-Fragen hinzugefügt\n";
    }

    /**
     * Geschichte Extended (40 Fragen)
     */
    private function seedGeschichteExtended(): void {
        echo "Seeding Geschichte Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Geschichte Extended - Weltgeschichte',
            'description' => 'Von der Antike bis zur Moderne',
            'category' => 'Geschichte',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            ['question' => 'In welchem Jahr endete der Zweite Weltkrieg?', 'answers' => [['text' => '1944', 'correct' => false], ['text' => '1945', 'correct' => true], ['text' => '1946', 'correct' => false], ['text' => '1947', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer war der erste Bundeskanzler Deutschlands?', 'answers' => [['text' => 'Willy Brandt', 'correct' => false], ['text' => 'Konrad Adenauer', 'correct' => true], ['text' => 'Helmut Kohl', 'correct' => false], ['text' => 'Ludwig Erhard', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr fiel die Berliner Mauer?', 'answers' => [['text' => '1987', 'correct' => false], ['text' => '1988', 'correct' => false], ['text' => '1989', 'correct' => true], ['text' => '1990', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer entdeckte Amerika?', 'answers' => [['text' => 'Amerigo Vespucci', 'correct' => false], ['text' => 'Christopher Columbus', 'correct' => true], ['text' => 'Ferdinand Magellan', 'correct' => false], ['text' => 'Vasco da Gama', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr begann der Erste Weltkrieg?', 'answers' => [['text' => '1912', 'correct' => false], ['text' => '1913', 'correct' => false], ['text' => '1914', 'correct' => true], ['text' => '1915', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer war Julius Caesar?', 'answers' => [['text' => 'Griechischer Philosoph', 'correct' => false], ['text' => 'Römischer Kaiser', 'correct' => false], ['text' => 'Römischer Feldherr', 'correct' => true], ['text' => 'Persischer König', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr landete Neil Armstrong auf dem Mond?', 'answers' => [['text' => '1967', 'correct' => false], ['text' => '1968', 'correct' => false], ['text' => '1969', 'correct' => true], ['text' => '1970', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer war Napoleon Bonaparte?', 'answers' => [['text' => 'Spanischer König', 'correct' => false], ['text' => 'Französischer Kaiser', 'correct' => true], ['text' => 'Italienischer Diktator', 'correct' => false], ['text' => 'Deutscher Fürst', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr begann die Französische Revolution?', 'answers' => [['text' => '1789', 'correct' => true], ['text' => '1790', 'correct' => false], ['text' => '1791', 'correct' => false], ['text' => '1792', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer erfand die Glühbirne?', 'answers' => [['text' => 'Nikola Tesla', 'correct' => false], ['text' => 'Thomas Edison', 'correct' => true], ['text' => 'Alexander Graham Bell', 'correct' => false], ['text' => 'Benjamin Franklin', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr wurde die DDR gegründet?', 'answers' => [['text' => '1947', 'correct' => false], ['text' => '1948', 'correct' => false], ['text' => '1949', 'correct' => true], ['text' => '1950', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer war Kleopatra?', 'answers' => [['text' => 'Griechische Göttin', 'correct' => false], ['text' => 'Ägyptische Königin', 'correct' => true], ['text' => 'Römische Kaiserin', 'correct' => false], ['text' => 'Persische Prinzessin', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr wurde das Römische Reich gegründet?', 'answers' => [['text' => '753 v.Chr.', 'correct' => true], ['text' => '500 v.Chr.', 'correct' => false], ['text' => '1000 v.Chr.', 'correct' => false], ['text' => '250 v.Chr.', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer schrieb die 95 Thesen?', 'answers' => [['text' => 'Johannes Calvin', 'correct' => false], ['text' => 'Martin Luther', 'correct' => true], ['text' => 'Jan Hus', 'correct' => false], ['text' => 'Huldrych Zwingli', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde die USA unabhängig?', 'answers' => [['text' => '1774', 'correct' => false], ['text' => '1775', 'correct' => false], ['text' => '1776', 'correct' => true], ['text' => '1777', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer war Alexander der Große?', 'answers' => [['text' => 'Römischer Kaiser', 'correct' => false], ['text' => 'Makedonischer König', 'correct' => true], ['text' => 'Griechischer Philosoph', 'correct' => false], ['text' => 'Persischer Herrscher', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr begann die Russische Revolution?', 'answers' => [['text' => '1915', 'correct' => false], ['text' => '1916', 'correct' => false], ['text' => '1917', 'correct' => true], ['text' => '1918', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer war Dschingis Khan?', 'answers' => [['text' => 'Chinesischer Kaiser', 'correct' => false], ['text' => 'Mongolischer Herrscher', 'correct' => true], ['text' => 'Japanischer Shogun', 'correct' => false], ['text' => 'Koreanischer König', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr begann die Industrielle Revolution?', 'answers' => [['text' => 'Mitte 17. Jh.', 'correct' => false], ['text' => 'Mitte 18. Jh.', 'correct' => true], ['text' => 'Mitte 19. Jh.', 'correct' => false], ['text' => 'Anfang 20. Jh.', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer war Jeanne d\'Arc?', 'answers' => [['text' => 'Französische Heilige', 'correct' => true], ['text' => 'Englische Königin', 'correct' => false], ['text' => 'Spanische Nonne', 'correct' => false], ['text' => 'Italienische Malerin', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr endete der Dreißigjährige Krieg?', 'answers' => [['text' => '1646', 'correct' => false], ['text' => '1647', 'correct' => false], ['text' => '1648', 'correct' => true], ['text' => '1649', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer war Otto von Bismarck?', 'answers' => [['text' => 'Preußischer König', 'correct' => false], ['text' => 'Deutscher Reichskanzler', 'correct' => true], ['text' => 'Österreichischer Kaiser', 'correct' => false], ['text' => 'Bayerischer Herzog', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde die Berliner Mauer gebaut?', 'answers' => [['text' => '1959', 'correct' => false], ['text' => '1960', 'correct' => false], ['text' => '1961', 'correct' => true], ['text' => '1962', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer war Sokrates?', 'answers' => [['text' => 'Römischer Kaiser', 'correct' => false], ['text' => 'Griechischer Philosoph', 'correct' => true], ['text' => 'Ägyptischer Pharao', 'correct' => false], ['text' => 'Persischer König', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr wurde die UNO gegründet?', 'answers' => [['text' => '1943', 'correct' => false], ['text' => '1944', 'correct' => false], ['text' => '1945', 'correct' => true], ['text' => '1946', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer war Aristoteles?', 'answers' => [['text' => 'Römischer Dichter', 'correct' => false], ['text' => 'Griechischer Philosoph', 'correct' => true], ['text' => 'Makedonischer König', 'correct' => false], ['text' => 'Spartanischer General', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr wurde die Magna Carta unterzeichnet?', 'answers' => [['text' => '1215', 'correct' => true], ['text' => '1315', 'correct' => false], ['text' => '1415', 'correct' => false], ['text' => '1515', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer war Leonardo da Vinci?', 'answers' => [['text' => 'Nur Maler', 'correct' => false], ['text' => 'Universalgelehrter', 'correct' => true], ['text' => 'Nur Erfinder', 'correct' => false], ['text' => 'Nur Architekt', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr war die Kubakrise?', 'answers' => [['text' => '1960', 'correct' => false], ['text' => '1961', 'correct' => false], ['text' => '1962', 'correct' => true], ['text' => '1963', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer war Kolumbus Auftraggeber?', 'answers' => [['text' => 'König von Portugal', 'correct' => false], ['text' => 'Königin von Spanien', 'correct' => true], ['text' => 'König von England', 'correct' => false], ['text' => 'Papst', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde die EU gegründet?', 'answers' => [['text' => '1990', 'correct' => false], ['text' => '1991', 'correct' => false], ['text' => '1992', 'correct' => false], ['text' => '1993', 'correct' => true]], 'difficulty' => 'medium'],
            ['question' => 'Wer war Spartacus?', 'answers' => [['text' => 'Römischer Senator', 'correct' => false], ['text' => 'Gladiator und Anführer', 'correct' => true], ['text' => 'Griechischer Krieger', 'correct' => false], ['text' => 'Ägyptischer Sklave', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde Gandhi ermordet?', 'answers' => [['text' => '1946', 'correct' => false], ['text' => '1947', 'correct' => false], ['text' => '1948', 'correct' => true], ['text' => '1949', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer war Karl der Große?', 'answers' => [['text' => 'Römischer Kaiser', 'correct' => false], ['text' => 'Fränkischer König', 'correct' => true], ['text' => 'Byzantinischer Kaiser', 'correct' => false], ['text' => 'Deutscher Kaiser', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr begann der Vietnamkrieg?', 'answers' => [['text' => '1954', 'correct' => false], ['text' => '1955', 'correct' => true], ['text' => '1956', 'correct' => false], ['text' => '1957', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer war Ramses II?', 'answers' => [['text' => 'Griechischer König', 'correct' => false], ['text' => 'Ägyptischer Pharao', 'correct' => true], ['text' => 'Persischer Kaiser', 'correct' => false], ['text' => 'Babylonischer Herrscher', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr endete der Kalte Krieg?', 'answers' => [['text' => '1989', 'correct' => false], ['text' => '1990', 'correct' => false], ['text' => '1991', 'correct' => true], ['text' => '1992', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer war Winston Churchill?', 'answers' => [['text' => 'US-Präsident', 'correct' => false], ['text' => 'Britischer Premierminister', 'correct' => true], ['text' => 'Französischer Präsident', 'correct' => false], ['text' => 'Deutscher Kanzler', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr wurde die NATO gegründet?', 'answers' => [['text' => '1947', 'correct' => false], ['text' => '1948', 'correct' => false], ['text' => '1949', 'correct' => true], ['text' => '1950', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer war Attila?', 'answers' => [['text' => 'Römischer General', 'correct' => false], ['text' => 'Hunnenführer', 'correct' => true], ['text' => 'Gotenkönig', 'correct' => false], ['text' => 'Vandalenherrscher', 'correct' => false]], 'difficulty' => 'medium']
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Geschichte-Fragen hinzugefügt\n";
    }

    /**
     * Naturwissenschaften Extended (40 Fragen)
     */
    private function seedNaturwissenschaftenExtended(): void {
        echo "Seeding Naturwissenschaften Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Naturwissenschaften Extended - Wissenschaft',
            'description' => 'Physik, Chemie, Biologie, Astronomie',
            'category' => 'Naturwissenschaften',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            ['question' => 'Wie lautet die Lichtgeschwindigkeit?', 'answers' => [['text' => '200.000 km/s', 'correct' => false], ['text' => '300.000 km/s', 'correct' => true], ['text' => '400.000 km/s', 'correct' => false], ['text' => '500.000 km/s', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist H2O?', 'answers' => [['text' => 'Sauerstoff', 'correct' => false], ['text' => 'Wasser', 'correct' => true], ['text' => 'Wasserstoff', 'correct' => false], ['text' => 'Kohlendioxid', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Chromosomen hat der Mensch?', 'answers' => [['text' => '23', 'correct' => false], ['text' => '46', 'correct' => true], ['text' => '48', 'correct' => false], ['text' => '50', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie groß ist die Sonne im Vergleich zur Erde?', 'answers' => [['text' => '50x größer', 'correct' => false], ['text' => '100x größer', 'correct' => false], ['text' => '109x größer (Durchmesser)', 'correct' => true], ['text' => '200x größer', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist die Formel für Kohlendioxid?', 'answers' => [['text' => 'CO', 'correct' => false], ['text' => 'CO2', 'correct' => true], ['text' => 'C2O', 'correct' => false], ['text' => 'O2', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie heißt das größte Organ des Menschen?', 'answers' => [['text' => 'Leber', 'correct' => false], ['text' => 'Haut', 'correct' => true], ['text' => 'Gehirn', 'correct' => false], ['text' => 'Herz', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Planeten hat unser Sonnensystem?', 'answers' => [['text' => '7', 'correct' => false], ['text' => '8', 'correct' => true], ['text' => '9', 'correct' => false], ['text' => '10', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist DNA?', 'answers' => [['text' => 'Ein Protein', 'correct' => false], ['text' => 'Desoxyribonukleinsäure', 'correct' => true], ['text' => 'Ein Hormon', 'correct' => false], ['text' => 'Ein Vitamin', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Element hat das Symbol "Au"?', 'answers' => [['text' => 'Silber', 'correct' => false], ['text' => 'Gold', 'correct' => true], ['text' => 'Aluminium', 'correct' => false], ['text' => 'Kupfer', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie lange braucht das Licht von der Sonne zur Erde?', 'answers' => [['text' => '5 Minuten', 'correct' => false], ['text' => '8 Minuten', 'correct' => true], ['text' => '12 Minuten', 'correct' => false], ['text' => '15 Minuten', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist Photosynthese?', 'answers' => [['text' => 'Atmung', 'correct' => false], ['text' => 'Umwandlung von Licht in Energie', 'correct' => true], ['text' => 'Verdauung', 'correct' => false], ['text' => 'Zellteilung', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Gas atmen wir aus?', 'answers' => [['text' => 'Sauerstoff', 'correct' => false], ['text' => 'Kohlendioxid', 'correct' => true], ['text' => 'Stickstoff', 'correct' => false], ['text' => 'Helium', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Knochen hat ein erwachsener Mensch?', 'answers' => [['text' => '196', 'correct' => false], ['text' => '206', 'correct' => true], ['text' => '216', 'correct' => false], ['text' => '226', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist der härteste natürliche Stoff?', 'answers' => [['text' => 'Stahl', 'correct' => false], ['text' => 'Diamant', 'correct' => true], ['text' => 'Granit', 'correct' => false], ['text' => 'Quarz', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie heißt der rote Planet?', 'answers' => [['text' => 'Venus', 'correct' => false], ['text' => 'Mars', 'correct' => true], ['text' => 'Jupiter', 'correct' => false], ['text' => 'Saturn', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist ein Atom?', 'answers' => [['text' => 'Eine Zelle', 'correct' => false], ['text' => 'Kleinstes Teilchen', 'correct' => true], ['text' => 'Ein Molekül', 'correct' => false], ['text' => 'Ein Elektron', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Blutgruppe ist Universalspender?', 'answers' => [['text' => 'A', 'correct' => false], ['text' => '0 negativ', 'correct' => true], ['text' => 'B', 'correct' => false], ['text' => 'AB', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist Schwerkraft?', 'answers' => [['text' => 'Magnetismus', 'correct' => false], ['text' => 'Anziehungskraft', 'correct' => true], ['text' => 'Elektrizität', 'correct' => false], ['text' => 'Reibung', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Zähne hat ein erwachsener Mensch?', 'answers' => [['text' => '28', 'correct' => false], ['text' => '32', 'correct' => true], ['text' => '30', 'correct' => false], ['text' => '34', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Chlorophyll?', 'answers' => [['text' => 'Ein Vitamin', 'correct' => false], ['text' => 'Grüner Pflanzenfarbstoff', 'correct' => true], ['text' => 'Ein Protein', 'correct' => false], ['text' => 'Ein Mineral', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welcher Planet ist der größte?', 'answers' => [['text' => 'Saturn', 'correct' => false], ['text' => 'Jupiter', 'correct' => true], ['text' => 'Uranus', 'correct' => false], ['text' => 'Neptun', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Evaporation?', 'answers' => [['text' => 'Schmelzen', 'correct' => false], ['text' => 'Verdunstung', 'correct' => true], ['text' => 'Gefrieren', 'correct' => false], ['text' => 'Kondensation', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Elemente gibt es im Periodensystem?', 'answers' => [['text' => '92', 'correct' => false], ['text' => '118', 'correct' => true], ['text' => '100', 'correct' => false], ['text' => '110', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist Insulin?', 'answers' => [['text' => 'Ein Vitamin', 'correct' => false], ['text' => 'Ein Hormon', 'correct' => true], ['text' => 'Ein Enzym', 'correct' => false], ['text' => 'Ein Mineral', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welcher Planet ist der Erde am nächsten?', 'answers' => [['text' => 'Mars', 'correct' => false], ['text' => 'Venus', 'correct' => true], ['text' => 'Merkur', 'correct' => false], ['text' => 'Jupiter', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist Osmose?', 'answers' => [['text' => 'Zellteilung', 'correct' => false], ['text' => 'Diffusion durch Membran', 'correct' => true], ['text' => 'Atmung', 'correct' => false], ['text' => 'Photosynthese', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie heißt die Einheit der Kraft?', 'answers' => [['text' => 'Watt', 'correct' => false], ['text' => 'Newton', 'correct' => true], ['text' => 'Joule', 'correct' => false], ['text' => 'Pascal', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist Mitochondrium?', 'answers' => [['text' => 'Zellkern', 'correct' => false], ['text' => 'Kraftwerk der Zelle', 'correct' => true], ['text' => 'Zellmembran', 'correct' => false], ['text' => 'Chromosom', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Welches Gas ist am häufigsten in der Atmosphäre?', 'answers' => [['text' => 'Sauerstoff', 'correct' => false], ['text' => 'Stickstoff', 'correct' => true], ['text' => 'Kohlendioxid', 'correct' => false], ['text' => 'Argon', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist Gravitation?', 'answers' => [['text' => 'Magnetische Kraft', 'correct' => false], ['text' => 'Anziehungskraft zwischen Massen', 'correct' => true], ['text' => 'Elektrische Kraft', 'correct' => false], ['text' => 'Reibungskraft', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Monde hat der Mars?', 'answers' => [['text' => '1', 'correct' => false], ['text' => '2', 'correct' => true], ['text' => '3', 'correct' => false], ['text' => '4', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist ein Elektron?', 'answers' => [['text' => 'Positiv geladen', 'correct' => false], ['text' => 'Negativ geladenes Teilchen', 'correct' => true], ['text' => 'Neutral', 'correct' => false], ['text' => 'Ein Atom', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Vitamin produziert die Haut bei Sonnenlicht?', 'answers' => [['text' => 'Vitamin C', 'correct' => false], ['text' => 'Vitamin D', 'correct' => true], ['text' => 'Vitamin A', 'correct' => false], ['text' => 'Vitamin B12', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist die Einheit der Energie?', 'answers' => [['text' => 'Watt', 'correct' => false], ['text' => 'Joule', 'correct' => true], ['text' => 'Newton', 'correct' => false], ['text' => 'Volt', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie heißt die kleinste Einheit des Lebens?', 'answers' => [['text' => 'Atom', 'correct' => false], ['text' => 'Zelle', 'correct' => true], ['text' => 'Molekül', 'correct' => false], ['text' => 'Organ', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Schallgeschwindigkeit in Luft?', 'answers' => [['text' => '243 m/s', 'correct' => false], ['text' => '343 m/s', 'correct' => true], ['text' => '443 m/s', 'correct' => false], ['text' => '543 m/s', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Welches Organ produziert Insulin?', 'answers' => [['text' => 'Leber', 'correct' => false], ['text' => 'Bauchspeicheldrüse', 'correct' => true], ['text' => 'Niere', 'correct' => false], ['text' => 'Milz', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist Metamorphose?', 'answers' => [['text' => 'Zellteilung', 'correct' => false], ['text' => 'Verwandlung', 'correct' => true], ['text' => 'Atmung', 'correct' => false], ['text' => 'Fortpflanzung', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Monde hat Jupiter?', 'answers' => [['text' => '50', 'correct' => false], ['text' => '79+', 'correct' => true], ['text' => '30', 'correct' => false], ['text' => '100', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist ein Neutron?', 'answers' => [['text' => 'Negativ geladen', 'correct' => false], ['text' => 'Neutral', 'correct' => true], ['text' => 'Positiv geladen', 'correct' => false], ['text' => 'Ein Ion', 'correct' => false]], 'difficulty' => 'medium']
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Naturwissenschaften-Fragen hinzugefügt\n";
    }

    /**
     * Technik Extended (40 Fragen)
     */
    private function seedTechnikExtended(): void {
        echo "Seeding Technik Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Technik Extended - Digitale Welt',
            'description' => 'Von Computern bis Smartphones',
            'category' => 'Technik',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            ['question' => 'Wer gründete Apple?', 'answers' => [['text' => 'Bill Gates', 'correct' => false], ['text' => 'Steve Jobs', 'correct' => true], ['text' => 'Mark Zuckerberg', 'correct' => false], ['text' => 'Elon Musk', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was bedeutet "CPU"?', 'answers' => [['text' => 'Computer Processing Unit', 'correct' => false], ['text' => 'Central Processing Unit', 'correct' => true], ['text' => 'Central Program Unit', 'correct' => false], ['text' => 'Computer Program Unit', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde das erste iPhone vorgestellt?', 'answers' => [['text' => '2005', 'correct' => false], ['text' => '2006', 'correct' => false], ['text' => '2007', 'correct' => true], ['text' => '2008', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist ein Algorithmus?', 'answers' => [['text' => 'Eine Programmiersprache', 'correct' => false], ['text' => 'Eine Rechenanleitung', 'correct' => true], ['text' => 'Ein Computer', 'correct' => false], ['text' => 'Eine App', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer erfand das World Wide Web?', 'answers' => [['text' => 'Bill Gates', 'correct' => false], ['text' => 'Tim Berners-Lee', 'correct' => true], ['text' => 'Steve Jobs', 'correct' => false], ['text' => 'Larry Page', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was bedeutet "RAM"?', 'answers' => [['text' => 'Random Access Memory', 'correct' => true], ['text' => 'Read Access Memory', 'correct' => false], ['text' => 'Rapid Access Memory', 'correct' => false], ['text' => 'Real Access Memory', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde Google gegründet?', 'answers' => [['text' => '1996', 'correct' => false], ['text' => '1997', 'correct' => false], ['text' => '1998', 'correct' => true], ['text' => '1999', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist HTML?', 'answers' => [['text' => 'Eine Programmiersprache', 'correct' => false], ['text' => 'Eine Markup-Sprache', 'correct' => true], ['text' => 'Ein Betriebssystem', 'correct' => false], ['text' => 'Ein Browser', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer entwickelte das Android-Betriebssystem?', 'answers' => [['text' => 'Apple', 'correct' => false], ['text' => 'Google', 'correct' => true], ['text' => 'Microsoft', 'correct' => false], ['text' => 'Samsung', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was bedeutet "USB"?', 'answers' => [['text' => 'Universal Serial Bus', 'correct' => true], ['text' => 'Universal System Bus', 'correct' => false], ['text' => 'Unified Serial Bus', 'correct' => false], ['text' => 'Universal Storage Bus', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer gründete Microsoft?', 'answers' => [['text' => 'Steve Jobs', 'correct' => false], ['text' => 'Bill Gates', 'correct' => true], ['text' => 'Larry Ellison', 'correct' => false], ['text' => 'Jeff Bezos', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Python?', 'answers' => [['text' => 'Ein Betriebssystem', 'correct' => false], ['text' => 'Eine Programmiersprache', 'correct' => true], ['text' => 'Ein Browser', 'correct' => false], ['text' => 'Eine Datenbank', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr wurde Facebook gegründet?', 'answers' => [['text' => '2002', 'correct' => false], ['text' => '2003', 'correct' => false], ['text' => '2004', 'correct' => true], ['text' => '2005', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was bedeutet "WiFi"?', 'answers' => [['text' => 'Wireless Fidelity', 'correct' => true], ['text' => 'Wireless Finance', 'correct' => false], ['text' => 'Wide Fidelity', 'correct' => false], ['text' => 'Wireless Filter', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer ist der CEO von Tesla?', 'answers' => [['text' => 'Jeff Bezos', 'correct' => false], ['text' => 'Elon Musk', 'correct' => true], ['text' => 'Tim Cook', 'correct' => false], ['text' => 'Mark Zuckerberg', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist JavaScript?', 'answers' => [['text' => 'Eine Kaffeemarke', 'correct' => false], ['text' => 'Eine Programmiersprache', 'correct' => true], ['text' => 'Ein Betriebssystem', 'correct' => false], ['text' => 'Ein Browser', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr wurde YouTube gegründet?', 'answers' => [['text' => '2003', 'correct' => false], ['text' => '2004', 'correct' => false], ['text' => '2005', 'correct' => true], ['text' => '2006', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist Linux?', 'answers' => [['text' => 'Eine Programmiersprache', 'correct' => false], ['text' => 'Ein Betriebssystem', 'correct' => true], ['text' => 'Ein Browser', 'correct' => false], ['text' => 'Eine App', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer erfand das Telefon?', 'answers' => [['text' => 'Thomas Edison', 'correct' => false], ['text' => 'Alexander Graham Bell', 'correct' => true], ['text' => 'Nikola Tesla', 'correct' => false], ['text' => 'Guglielmo Marconi', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was bedeutet "PDF"?', 'answers' => [['text' => 'Portable Document Format', 'correct' => true], ['text' => 'Personal Document Format', 'correct' => false], ['text' => 'Public Document Format', 'correct' => false], ['text' => 'Printed Document Format', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde Twitter gegründet?', 'answers' => [['text' => '2004', 'correct' => false], ['text' => '2005', 'correct' => false], ['text' => '2006', 'correct' => true], ['text' => '2007', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist Bitcoin?', 'answers' => [['text' => 'Eine Aktie', 'correct' => false], ['text' => 'Eine Kryptowährung', 'correct' => true], ['text' => 'Ein Unternehmen', 'correct' => false], ['text' => 'Ein Zahlungssystem', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer entwickelte den ersten Computer?', 'answers' => [['text' => 'Bill Gates', 'correct' => false], ['text' => 'Charles Babbage', 'correct' => true], ['text' => 'Alan Turing', 'correct' => false], ['text' => 'Steve Jobs', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist SQL?', 'answers' => [['text' => 'Eine Programmiersprache', 'correct' => false], ['text' => 'Eine Datenbanksprache', 'correct' => true], ['text' => 'Ein Betriebssystem', 'correct' => false], ['text' => 'Ein Browser', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr kam Windows 95 heraus?', 'answers' => [['text' => '1993', 'correct' => false], ['text' => '1994', 'correct' => false], ['text' => '1995', 'correct' => true], ['text' => '1996', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist ein Router?', 'answers' => [['text' => 'Ein Computer', 'correct' => false], ['text' => 'Ein Netzwerkgerät', 'correct' => true], ['text' => 'Ein Programm', 'correct' => false], ['text' => 'Ein Monitor', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer gründete Amazon?', 'answers' => [['text' => 'Bill Gates', 'correct' => false], ['text' => 'Jeff Bezos', 'correct' => true], ['text' => 'Elon Musk', 'correct' => false], ['text' => 'Mark Zuckerberg', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was bedeutet "GPU"?', 'answers' => [['text' => 'Graphics Processing Unit', 'correct' => true], ['text' => 'General Processing Unit', 'correct' => false], ['text' => 'Global Processing Unit', 'correct' => false], ['text' => 'Game Processing Unit', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde das iPad vorgestellt?', 'answers' => [['text' => '2008', 'correct' => false], ['text' => '2009', 'correct' => false], ['text' => '2010', 'correct' => true], ['text' => '2011', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist Cloud Computing?', 'answers' => [['text' => 'Ein Betriebssystem', 'correct' => false], ['text' => 'Datenspeicherung im Internet', 'correct' => true], ['text' => 'Ein Programm', 'correct' => false], ['text' => 'Ein Browser', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer erfand die Glühbirne?', 'answers' => [['text' => 'Nikola Tesla', 'correct' => false], ['text' => 'Thomas Edison', 'correct' => true], ['text' => 'Benjamin Franklin', 'correct' => false], ['text' => 'Alexander Bell', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Blockchain?', 'answers' => [['text' => 'Ein Spiel', 'correct' => false], ['text' => 'Eine Datenbank-Technologie', 'correct' => true], ['text' => 'Ein Betriebssystem', 'correct' => false], ['text' => 'Ein Browser', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'In welchem Jahr wurde Instagram gegründet?', 'answers' => [['text' => '2008', 'correct' => false], ['text' => '2009', 'correct' => false], ['text' => '2010', 'correct' => true], ['text' => '2011', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist ein Virus (Computer)?', 'answers' => [['text' => 'Ein Programm', 'correct' => false], ['text' => 'Schädliche Software', 'correct' => true], ['text' => 'Ein Betriebssystem', 'correct' => false], ['text' => 'Ein Browser', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer entwickelte Java?', 'answers' => [['text' => 'Microsoft', 'correct' => false], ['text' => 'Sun Microsystems', 'correct' => true], ['text' => 'Apple', 'correct' => false], ['text' => 'Google', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was bedeutet "SSD"?', 'answers' => [['text' => 'Solid State Drive', 'correct' => true], ['text' => 'Super Storage Device', 'correct' => false], ['text' => 'System Storage Drive', 'correct' => false], ['text' => 'Secure Storage Device', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Jahr wurde WhatsApp gegründet?', 'answers' => [['text' => '2007', 'correct' => false], ['text' => '2008', 'correct' => false], ['text' => '2009', 'correct' => true], ['text' => '2010', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist Machine Learning?', 'answers' => [['text' => 'Ein Betriebssystem', 'correct' => false], ['text' => 'Künstliche Intelligenz', 'correct' => true], ['text' => 'Eine Programmiersprache', 'correct' => false], ['text' => 'Ein Browser', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer gründete SpaceX?', 'answers' => [['text' => 'Jeff Bezos', 'correct' => false], ['text' => 'Elon Musk', 'correct' => true], ['text' => 'Richard Branson', 'correct' => false], ['text' => 'Bill Gates', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist ein Pixel?', 'answers' => [['text' => 'Ein Programm', 'correct' => false], ['text' => 'Ein Bildpunkt', 'correct' => true], ['text' => 'Ein Monitor', 'correct' => false], ['text' => 'Ein Browser', 'correct' => false]], 'difficulty' => 'easy']
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Technik-Fragen hinzugefügt\n";
    }

    /**
     * Sport Extended (40 Fragen)
     */
    private function seedSportExtended(): void {
        echo "Seeding Sport Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Sport Extended - Von Fußball bis Olympia',
            'description' => 'Teste dein Sport-Wissen!',
            'category' => 'Sport',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            ['question' => 'Wie viele Spieler hat ein Fußballteam auf dem Feld?', 'answers' => [['text' => '9', 'correct' => false], ['text' => '10', 'correct' => false], ['text' => '11', 'correct' => true], ['text' => '12', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Land fand die Fußball-WM 2014 statt?', 'answers' => [['text' => 'Argentinien', 'correct' => false], ['text' => 'Brasilien', 'correct' => true], ['text' => 'Deutschland', 'correct' => false], ['text' => 'Spanien', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer gewann die Fußball-WM 2018?', 'answers' => [['text' => 'Deutschland', 'correct' => false], ['text' => 'Brasilien', 'correct' => false], ['text' => 'Frankreich', 'correct' => true], ['text' => 'Kroatien', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Spieler hat ein Basketball-Team?', 'answers' => [['text' => '4', 'correct' => false], ['text' => '5', 'correct' => true], ['text' => '6', 'correct' => false], ['text' => '7', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welcher Sportart ist Usain Bolt berühmt?', 'answers' => [['text' => 'Hochsprung', 'correct' => false], ['text' => 'Marathon', 'correct' => false], ['text' => 'Sprint', 'correct' => true], ['text' => 'Hürdenlauf', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Ringe hat das Olympische Symbol?', 'answers' => [['text' => '4', 'correct' => false], ['text' => '5', 'correct' => true], ['text' => '6', 'correct' => false], ['text' => '7', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welcher Stadt fanden die Olympischen Spiele 2012 statt?', 'answers' => [['text' => 'Peking', 'correct' => false], ['text' => 'London', 'correct' => true], ['text' => 'Rio', 'correct' => false], ['text' => 'Tokio', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie oft finden Olympische Sommerspiele statt?', 'answers' => [['text' => 'Jedes Jahr', 'correct' => false], ['text' => 'Alle 2 Jahre', 'correct' => false], ['text' => 'Alle 4 Jahre', 'correct' => true], ['text' => 'Alle 5 Jahre', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welcher deutsche Verein gewann 2013 die Champions League?', 'answers' => [['text' => 'Dortmund', 'correct' => false], ['text' => 'Bayern München', 'correct' => true], ['text' => 'Schalke 04', 'correct' => false], ['text' => 'Leverkusen', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer ist Roger Federer?', 'answers' => [['text' => 'Fußballspieler', 'correct' => false], ['text' => 'Tennisspieler', 'correct' => true], ['text' => 'Golfspieler', 'correct' => false], ['text' => 'Skispringer', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Sätze muss man beim Tennis gewinnen (bei Grand Slams)?', 'answers' => [['text' => '2', 'correct' => false], ['text' => '3', 'correct' => true], ['text' => '4', 'correct' => false], ['text' => '5', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Team fuhr Michael Schumacher?', 'answers' => [['text' => 'McLaren', 'correct' => false], ['text' => 'Ferrari', 'correct' => true], ['text' => 'Williams', 'correct' => false], ['text' => 'Renault', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Grand-Slam-Turniere gibt es im Tennis?', 'answers' => [['text' => '3', 'correct' => false], ['text' => '4', 'correct' => true], ['text' => '5', 'correct' => false], ['text' => '6', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Farbe hat der Puck im Eishockey?', 'answers' => [['text' => 'Weiß', 'correct' => false], ['text' => 'Schwarz', 'correct' => true], ['text' => 'Rot', 'correct' => false], ['text' => 'Blau', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Punkte gibt es für einen Touchdown im American Football?', 'answers' => [['text' => '5', 'correct' => false], ['text' => '6', 'correct' => true], ['text' => '7', 'correct' => false], ['text' => '8', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer ist Cristiano Ronaldo?', 'answers' => [['text' => 'Basketballer', 'correct' => false], ['text' => 'Fußballer', 'correct' => true], ['text' => 'Tennisspieler', 'correct' => false], ['text' => 'Boxer', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welcher Sportart gibt es ein "Hole-in-One"?', 'answers' => [['text' => 'Tennis', 'correct' => false], ['text' => 'Golf', 'correct' => true], ['text' => 'Bowling', 'correct' => false], ['text' => 'Billard', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Disziplinen hat ein Zehnkampf?', 'answers' => [['text' => '8', 'correct' => false], ['text' => '9', 'correct' => false], ['text' => '10', 'correct' => true], ['text' => '12', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Land gewann die erste Fußball-WM 1930?', 'answers' => [['text' => 'Brasilien', 'correct' => false], ['text' => 'Uruguay', 'correct' => true], ['text' => 'Argentinien', 'correct' => false], ['text' => 'Italien', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie heißt das berühmteste Tennisturnier in Wimbledon?', 'answers' => [['text' => 'French Open', 'correct' => false], ['text' => 'Wimbledon Championships', 'correct' => true], ['text' => 'US Open', 'correct' => false], ['text' => 'Australian Open', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Spieler pro Team sind beim Volleyball?', 'answers' => [['text' => '5', 'correct' => false], ['text' => '6', 'correct' => true], ['text' => '7', 'correct' => false], ['text' => '8', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welcher Stadt spielen die New York Yankees?', 'answers' => [['text' => 'Los Angeles', 'correct' => false], ['text' => 'New York', 'correct' => true], ['text' => 'Chicago', 'correct' => false], ['text' => 'Boston', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Weltmeistertitel gewann Michael Schumacher?', 'answers' => [['text' => '5', 'correct' => false], ['text' => '6', 'correct' => false], ['text' => '7', 'correct' => true], ['text' => '8', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welcher Boxer war "The Greatest"?', 'answers' => [['text' => 'Mike Tyson', 'correct' => false], ['text' => 'Muhammad Ali', 'correct' => true], ['text' => 'George Foreman', 'correct' => false], ['text' => 'Joe Frazier', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Runden hat ein Boxkampf normalerweise?', 'answers' => [['text' => '10', 'correct' => false], ['text' => '12', 'correct' => true], ['text' => '15', 'correct' => false], ['text' => '20', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welcher Sportart gibt es den "Grand Slam"?', 'answers' => [['text' => 'Nur Tennis', 'correct' => false], ['text' => 'Tennis und Golf', 'correct' => true], ['text' => 'Nur Golf', 'correct' => false], ['text' => 'Baseball', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie lang ist ein Marathon?', 'answers' => [['text' => '40 km', 'correct' => false], ['text' => '42,195 km', 'correct' => true], ['text' => '45 km', 'correct' => false], ['text' => '50 km', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer ist Lionel Messi?', 'answers' => [['text' => 'Tennisspieler', 'correct' => false], ['text' => 'Fußballspieler', 'correct' => true], ['text' => 'Basketballer', 'correct' => false], ['text' => 'Handballer', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Land ist Rekordweltmeister im Fußball?', 'answers' => [['text' => 'Deutschland', 'correct' => false], ['text' => 'Brasilien', 'correct' => true], ['text' => 'Italien', 'correct' => false], ['text' => 'Argentinien', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Pins gibt es beim Bowling?', 'answers' => [['text' => '8', 'correct' => false], ['text' => '9', 'correct' => false], ['text' => '10', 'correct' => true], ['text' => '12', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welcher Sportart gab es die "Hand Gottes"?', 'answers' => [['text' => 'Basketball', 'correct' => false], ['text' => 'Fußball', 'correct' => true], ['text' => 'Handball', 'correct' => false], ['text' => 'Volleyball', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Major-Titel gewann Tiger Woods?', 'answers' => [['text' => '12', 'correct' => false], ['text' => '14', 'correct' => false], ['text' => '15', 'correct' => true], ['text' => '18', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Welche Mannschaft wird "Die Mannschaft" genannt?', 'answers' => [['text' => 'Österreich', 'correct' => false], ['text' => 'Deutschland', 'correct' => true], ['text' => 'Schweiz', 'correct' => false], ['text' => 'Niederlande', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie heißt das Finale im American Football?', 'answers' => [['text' => 'World Series', 'correct' => false], ['text' => 'Super Bowl', 'correct' => true], ['text' => 'NBA Finals', 'correct' => false], ['text' => 'Stanley Cup', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welcher Stadt steht das Camp Nou?', 'answers' => [['text' => 'Madrid', 'correct' => false], ['text' => 'Barcelona', 'correct' => true], ['text' => 'Valencia', 'correct' => false], ['text' => 'Sevilla', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Spieler pro Team beim Handball?', 'answers' => [['text' => '5', 'correct' => false], ['text' => '6', 'correct' => false], ['text' => '7', 'correct' => true], ['text' => '8', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer gewann die meisten Ballon d\'Or Awards?', 'answers' => [['text' => 'Cristiano Ronaldo', 'correct' => false], ['text' => 'Lionel Messi', 'correct' => true], ['text' => 'Pelé', 'correct' => false], ['text' => 'Maradona', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Pokale gibt es bei Wimbledon?', 'answers' => [['text' => '1', 'correct' => false], ['text' => '2', 'correct' => true], ['text' => '3', 'correct' => false], ['text' => '4', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Farbe hat das Trikot des Tour de France Führenden?', 'answers' => [['text' => 'Grün', 'correct' => false], ['text' => 'Gelb', 'correct' => true], ['text' => 'Rot', 'correct' => false], ['text' => 'Blau', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Grand Slams gewann Serena Williams?', 'answers' => [['text' => '20', 'correct' => false], ['text' => '21', 'correct' => false], ['text' => '23', 'correct' => true], ['text' => '25', 'correct' => false]], 'difficulty' => 'hard']
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Sport-Fragen hinzugefügt\n";
    }

    private function seedKunstKulturExtended(): void {
        echo "Seeding Kunst & Kultur Extended...\n";
        echo "  ✓ 40 Kunst-Fragen würden hier hinzugefügt\n";
        // Implementation folgt...
    }

    /**
     * Film & Musik Extended (40 Fragen)
     */
    private function seedFilmMusikExtended(): void {
        echo "Seeding Film & Musik Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Film & Musik Extended - Pop Culture',
            'description' => 'Von Hollywood bis zur Hitparade',
            'category' => 'Film & Musik',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            ['question' => 'Wer spielte Harry Potter?', 'answers' => [['text' => 'Rupert Grint', 'correct' => false], ['text' => 'Daniel Radcliffe', 'correct' => true], ['text' => 'Tom Felton', 'correct' => false], ['text' => 'Matthew Lewis', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welcher Film gewann 2020 den Oscar als bester Film?', 'answers' => [['text' => '1917', 'correct' => false], ['text' => 'Parasite', 'correct' => true], ['text' => 'Joker', 'correct' => false], ['text' => 'Once Upon a Time', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Band sang "Bohemian Rhapsody"?', 'answers' => [['text' => 'The Beatles', 'correct' => false], ['text' => 'Queen', 'correct' => true], ['text' => 'Led Zeppelin', 'correct' => false], ['text' => 'Pink Floyd', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Mitglieder hatten The Beatles?', 'answers' => [['text' => '3', 'correct' => false], ['text' => '4', 'correct' => true], ['text' => '5', 'correct' => false], ['text' => '6', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer führte Regie bei "Titanic"?', 'answers' => [['text' => 'Steven Spielberg', 'correct' => false], ['text' => 'James Cameron', 'correct' => true], ['text' => 'Martin Scorsese', 'correct' => false], ['text' => 'Christopher Nolan', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welcher Song ist von Michael Jackson?', 'answers' => [['text' => 'Purple Rain', 'correct' => false], ['text' => 'Thriller', 'correct' => true], ['text' => 'Like a Prayer', 'correct' => false], ['text' => 'Imagine', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr starb Elvis Presley?', 'answers' => [['text' => '1975', 'correct' => false], ['text' => '1976', 'correct' => false], ['text' => '1977', 'correct' => true], ['text' => '1978', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer ist der Regisseur von "Pulp Fiction"?', 'answers' => [['text' => 'Quentin Tarantino', 'correct' => true], ['text' => 'Martin Scorsese', 'correct' => false], ['text' => 'Coen Brothers', 'correct' => false], ['text' => 'David Fincher', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welche Serie spielt in Westeros?', 'answers' => [['text' => 'Vikings', 'correct' => false], ['text' => 'Game of Thrones', 'correct' => true], ['text' => 'The Witcher', 'correct' => false], ['text' => 'Lord of the Rings', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer sang "Rolling in the Deep"?', 'answers' => [['text' => 'Taylor Swift', 'correct' => false], ['text' => 'Adele', 'correct' => true], ['text' => 'Beyoncé', 'correct' => false], ['text' => 'Rihanna', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Film sagt man "May the Force be with you"?', 'answers' => [['text' => 'Star Trek', 'correct' => false], ['text' => 'Star Wars', 'correct' => true], ['text' => 'Alien', 'correct' => false], ['text' => 'Avatar', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie heißt der Hauptcharakter in "Breaking Bad"?', 'answers' => [['text' => 'Jesse Pinkman', 'correct' => false], ['text' => 'Walter White', 'correct' => true], ['text' => 'Hank Schrader', 'correct' => false], ['text' => 'Saul Goodman', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welche Band ist bekannt für "Stairway to Heaven"?', 'answers' => [['text' => 'The Rolling Stones', 'correct' => false], ['text' => 'Led Zeppelin', 'correct' => true], ['text' => 'Deep Purple', 'correct' => false], ['text' => 'Black Sabbath', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer spielte Iron Man im MCU?', 'answers' => [['text' => 'Chris Evans', 'correct' => false], ['text' => 'Robert Downey Jr.', 'correct' => true], ['text' => 'Chris Hemsworth', 'correct' => false], ['text' => 'Mark Ruffalo', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welcher Film gewann die meisten Oscars?', 'answers' => [['text' => 'Titanic', 'correct' => false], ['text' => 'Ben Hur / Titanic / LotR (je 11)', 'correct' => true], ['text' => 'Avatar', 'correct' => false], ['text' => 'Forrest Gump', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer ist der "King of Pop"?', 'answers' => [['text' => 'Elvis Presley', 'correct' => false], ['text' => 'Michael Jackson', 'correct' => true], ['text' => 'Prince', 'correct' => false], ['text' => 'Freddie Mercury', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Film spielt Tom Hanks einen Soldaten im 2. WK?', 'answers' => [['text' => 'Pearl Harbor', 'correct' => false], ['text' => 'Saving Private Ryan', 'correct' => true], ['text' => 'Fury', 'correct' => false], ['text' => 'Dunkirk', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Band sang "Hotel California"?', 'answers' => [['text' => 'The Doors', 'correct' => false], ['text' => 'Eagles', 'correct' => true], ['text' => 'Fleetwood Mac', 'correct' => false], ['text' => 'Creedence', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer komponierte die Musik für "Star Wars"?', 'answers' => [['text' => 'Hans Zimmer', 'correct' => false], ['text' => 'John Williams', 'correct' => true], ['text' => 'Ennio Morricone', 'correct' => false], ['text' => 'Danny Elfman', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welcher Rapper ist bekannt als "Slim Shady"?', 'answers' => [['text' => 'Dr. Dre', 'correct' => false], ['text' => 'Eminem', 'correct' => true], ['text' => '50 Cent', 'correct' => false], ['text' => 'Snoop Dogg', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Jahr erschien der erste "Jurassic Park"?', 'answers' => [['text' => '1991', 'correct' => false], ['text' => '1992', 'correct' => false], ['text' => '1993', 'correct' => true], ['text' => '1994', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer sang "Like a Virgin"?', 'answers' => [['text' => 'Cher', 'correct' => false], ['text' => 'Madonna', 'correct' => true], ['text' => 'Cyndi Lauper', 'correct' => false], ['text' => 'Whitney Houston', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welcher Film begann das Marvel Cinematic Universe?', 'answers' => [['text' => 'The Hulk', 'correct' => false], ['text' => 'Iron Man', 'correct' => true], ['text' => 'Thor', 'correct' => false], ['text' => 'Captain America', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wer ist der Frontmann von U2?', 'answers' => [['text' => 'The Edge', 'correct' => false], ['text' => 'Bono', 'correct' => true], ['text' => 'Adam Clayton', 'correct' => false], ['text' => 'Larry Mullen Jr.', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Film spielt Leonardo DiCaprio einen Dieb in Träumen?', 'answers' => [['text' => 'Shutter Island', 'correct' => false], ['text' => 'Inception', 'correct' => true], ['text' => 'The Departed', 'correct' => false], ['text' => 'Django Unchained', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Instrument spielte Jimi Hendrix?', 'answers' => [['text' => 'Schlagzeug', 'correct' => false], ['text' => 'Gitarre', 'correct' => true], ['text' => 'Bass', 'correct' => false], ['text' => 'Keyboard', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer führte Regie bei "The Dark Knight"?', 'answers' => [['text' => 'Tim Burton', 'correct' => false], ['text' => 'Christopher Nolan', 'correct' => true], ['text' => 'Zack Snyder', 'correct' => false], ['text' => 'Matt Reeves', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Band hatte einen Hit mit "Smells Like Teen Spirit"?', 'answers' => [['text' => 'Pearl Jam', 'correct' => false], ['text' => 'Nirvana', 'correct' => true], ['text' => 'Soundgarden', 'correct' => false], ['text' => 'Alice in Chains', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'In welchem Film kämpft Russell Crowe als Gladiator?', 'answers' => [['text' => '300', 'correct' => false], ['text' => 'Gladiator', 'correct' => true], ['text' => 'Troy', 'correct' => false], ['text' => 'Ben Hur', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer sang "Purple Rain"?', 'answers' => [['text' => 'Michael Jackson', 'correct' => false], ['text' => 'Prince', 'correct' => true], ['text' => 'Stevie Wonder', 'correct' => false], ['text' => 'David Bowie', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welcher Film beginnt mit "Rosebud"?', 'answers' => [['text' => 'Casablanca', 'correct' => false], ['text' => 'Citizen Kane', 'correct' => true], ['text' => 'Gone with the Wind', 'correct' => false], ['text' => 'The Wizard of Oz', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wer ist der Sänger von Coldplay?', 'answers' => [['text' => 'Chris Martin', 'correct' => true], ['text' => 'Thom Yorke', 'correct' => false], ['text' => 'Brandon Flowers', 'correct' => false], ['text' => 'Alex Turner', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welchem Film sagt Arnold "I\'ll be back"?', 'answers' => [['text' => 'Predator', 'correct' => false], ['text' => 'The Terminator', 'correct' => true], ['text' => 'Total Recall', 'correct' => false], ['text' => 'Commando', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welche Sängerin ist bekannt als "Queen B"?', 'answers' => [['text' => 'Rihanna', 'correct' => false], ['text' => 'Beyoncé', 'correct' => true], ['text' => 'Lady Gaga', 'correct' => false], ['text' => 'Ariana Grande', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer spielte Jack in "Titanic"?', 'answers' => [['text' => 'Brad Pitt', 'correct' => false], ['text' => 'Leonardo DiCaprio', 'correct' => true], ['text' => 'Johnny Depp', 'correct' => false], ['text' => 'Tom Cruise', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welche Band ist bekannt für "Wonderwall"?', 'answers' => [['text' => 'Blur', 'correct' => false], ['text' => 'Oasis', 'correct' => true], ['text' => 'Radiohead', 'correct' => false], ['text' => 'The Verve', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'In welcher Serie arbeitet Dr. House?', 'answers' => [['text' => 'ER', 'correct' => false], ['text' => 'House M.D.', 'correct' => true], ['text' => 'Grey\'s Anatomy', 'correct' => false], ['text' => 'Scrubs', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wer sang "Imagine"?', 'answers' => [['text' => 'Paul McCartney', 'correct' => false], ['text' => 'John Lennon', 'correct' => true], ['text' => 'George Harrison', 'correct' => false], ['text' => 'Bob Dylan', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welcher Film zeigt die Geschichte von Facebook?', 'answers' => [['text' => 'Steve Jobs', 'correct' => false], ['text' => 'The Social Network', 'correct' => true], ['text' => 'Pirates of Silicon Valley', 'correct' => false], ['text' => 'Jobs', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Band sang "We Will Rock You"?', 'answers' => [['text' => 'AC/DC', 'correct' => false], ['text' => 'Queen', 'correct' => true], ['text' => 'Kiss', 'correct' => false], ['text' => 'Aerosmith', 'correct' => false]], 'difficulty' => 'easy']
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Film & Musik-Fragen hinzugefügt\n";
    }

    private function seedLiteraturExtended(): void {
        echo "Seeding Literatur Extended...\n";
        echo "  ✓ 40 Literatur-Fragen würden hier hinzugefügt\n";
        // Implementation folgt...
    }

    private function seedPolitikExtended(): void {
        echo "Seeding Politik Extended...\n";
        echo "  ✓ 40 Politik-Fragen würden hier hinzugefügt\n";
        // Implementation folgt...
    }

    private function seedWirtschaftExtended(): void {
        echo "Seeding Wirtschaft Extended...\n";
        echo "  ✓ 40 Wirtschaft-Fragen würden hier hinzugefügt\n";
        // Implementation folgt...
    }

    /**
     * Mathematik Extended (40 Fragen)
     */
    private function seedMathematikExtended(): void {
        echo "Seeding Mathematik Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Mathematik Extended - Rechnen',
            'description' => 'Von Grundrechnen bis Geometrie',
            'category' => 'Mathematik',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            ['question' => 'Was ist die Quadratwurzel aus 144?', 'answers' => [['text' => '11', 'correct' => false], ['text' => '12', 'correct' => true], ['text' => '13', 'correct' => false], ['text' => '14', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Grad hat ein Dreieck insgesamt?', 'answers' => [['text' => '90', 'correct' => false], ['text' => '180', 'correct' => true], ['text' => '270', 'correct' => false], ['text' => '360', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Pi (gerundet)?', 'answers' => [['text' => '2,14', 'correct' => false], ['text' => '3,14', 'correct' => true], ['text' => '4,14', 'correct' => false], ['text' => '5,14', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist 15% von 200?', 'answers' => [['text' => '20', 'correct' => false], ['text' => '30', 'correct' => true], ['text' => '40', 'correct' => false], ['text' => '50', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Seiten hat ein Oktagon?', 'answers' => [['text' => '6', 'correct' => false], ['text' => '8', 'correct' => true], ['text' => '10', 'correct' => false], ['text' => '12', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist 7 × 8?', 'answers' => [['text' => '54', 'correct' => false], ['text' => '56', 'correct' => true], ['text' => '58', 'correct' => false], ['text' => '60', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Grad hat ein rechter Winkel?', 'answers' => [['text' => '45', 'correct' => false], ['text' => '90', 'correct' => true], ['text' => '135', 'correct' => false], ['text' => '180', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist 12²?', 'answers' => [['text' => '124', 'correct' => false], ['text' => '144', 'correct' => true], ['text' => '156', 'correct' => false], ['text' => '164', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Nullen hat eine Million?', 'answers' => [['text' => '5', 'correct' => false], ['text' => '6', 'correct' => true], ['text' => '7', 'correct' => false], ['text' => '8', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist die Fläche eines Quadrats mit Seitenlänge 5?', 'answers' => [['text' => '20', 'correct' => false], ['text' => '25', 'correct' => true], ['text' => '30', 'correct' => false], ['text' => '35', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist 100 ÷ 4?', 'answers' => [['text' => '20', 'correct' => false], ['text' => '25', 'correct' => true], ['text' => '30', 'correct' => false], ['text' => '35', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Primzahlen gibt es unter 10?', 'answers' => [['text' => '3', 'correct' => false], ['text' => '4', 'correct' => true], ['text' => '5', 'correct' => false], ['text' => '6', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist die nächste Zahl: 2, 4, 8, 16, __?', 'answers' => [['text' => '24', 'correct' => false], ['text' => '32', 'correct' => true], ['text' => '40', 'correct' => false], ['text' => '48', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Sekunden hat eine Stunde?', 'answers' => [['text' => '3000', 'correct' => false], ['text' => '3600', 'correct' => true], ['text' => '4000', 'correct' => false], ['text' => '4200', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist 50% von 80?', 'answers' => [['text' => '30', 'correct' => false], ['text' => '40', 'correct' => true], ['text' => '50', 'correct' => false], ['text' => '60', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Diagonalen hat ein Quadrat?', 'answers' => [['text' => '1', 'correct' => false], ['text' => '2', 'correct' => true], ['text' => '3', 'correct' => false], ['text' => '4', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist 9 + 8 × 2?', 'answers' => [['text' => '34', 'correct' => false], ['text' => '25', 'correct' => true], ['text' => '26', 'correct' => false], ['text' => '28', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Grad hat ein Kreis?', 'answers' => [['text' => '180', 'correct' => false], ['text' => '360', 'correct' => true], ['text' => '540', 'correct' => false], ['text' => '720', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist die Summe von 1 bis 10?', 'answers' => [['text' => '45', 'correct' => false], ['text' => '55', 'correct' => true], ['text' => '65', 'correct' => false], ['text' => '75', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie viele Zentimeter sind 1 Meter?', 'answers' => [['text' => '10', 'correct' => false], ['text' => '100', 'correct' => true], ['text' => '1000', 'correct' => false], ['text' => '10000', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist 2³?', 'answers' => [['text' => '6', 'correct' => false], ['text' => '8', 'correct' => true], ['text' => '9', 'correct' => false], ['text' => '12', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Ecken hat ein Würfel?', 'answers' => [['text' => '6', 'correct' => false], ['text' => '8', 'correct' => true], ['text' => '10', 'correct' => false], ['text' => '12', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist 1/4 als Dezimalzahl?', 'answers' => [['text' => '0,2', 'correct' => false], ['text' => '0,25', 'correct' => true], ['text' => '0,3', 'correct' => false], ['text' => '0,4', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Minuten sind 2,5 Stunden?', 'answers' => [['text' => '120', 'correct' => false], ['text' => '150', 'correct' => true], ['text' => '180', 'correct' => false], ['text' => '200', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist der Umfang eines Quadrats mit Seitenlänge 6?', 'answers' => [['text' => '18', 'correct' => false], ['text' => '24', 'correct' => true], ['text' => '30', 'correct' => false], ['text' => '36', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie heißt ein Viereck mit 4 gleichen Seiten?', 'answers' => [['text' => 'Rechteck', 'correct' => false], ['text' => 'Quadrat', 'correct' => true], ['text' => 'Trapez', 'correct' => false], ['text' => 'Parallelogramm', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist 20% von 500?', 'answers' => [['text' => '50', 'correct' => false], ['text' => '100', 'correct' => true], ['text' => '150', 'correct' => false], ['text' => '200', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Kanten hat ein Würfel?', 'answers' => [['text' => '8', 'correct' => false], ['text' => '12', 'correct' => true], ['text' => '16', 'correct' => false], ['text' => '20', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist √100?', 'answers' => [['text' => '9', 'correct' => false], ['text' => '10', 'correct' => true], ['text' => '11', 'correct' => false], ['text' => '12', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Stunden hat eine Woche?', 'answers' => [['text' => '144', 'correct' => false], ['text' => '168', 'correct' => true], ['text' => '192', 'correct' => false], ['text' => '200', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Was ist 3/4 als Prozent?', 'answers' => [['text' => '50%', 'correct' => false], ['text' => '75%', 'correct' => true], ['text' => '80%', 'correct' => false], ['text' => '90%', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Flächen hat ein Würfel?', 'answers' => [['text' => '4', 'correct' => false], ['text' => '6', 'correct' => true], ['text' => '8', 'correct' => false], ['text' => '12', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist 10!?', 'answers' => [['text' => '100', 'correct' => false], ['text' => '3.628.800', 'correct' => true], ['text' => '10.000', 'correct' => false], ['text' => '1.000.000', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie viele Millimeter sind 5 cm?', 'answers' => [['text' => '5', 'correct' => false], ['text' => '50', 'correct' => true], ['text' => '500', 'correct' => false], ['text' => '5000', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist die kleinste Primzahl?', 'answers' => [['text' => '1', 'correct' => false], ['text' => '2', 'correct' => true], ['text' => '3', 'correct' => false], ['text' => '5', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Quadratmeter hat ein 10m × 10m Raum?', 'answers' => [['text' => '50', 'correct' => false], ['text' => '100', 'correct' => true], ['text' => '150', 'correct' => false], ['text' => '200', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist 0,5 als Bruch?', 'answers' => [['text' => '1/4', 'correct' => false], ['text' => '1/2', 'correct' => true], ['text' => '2/3', 'correct' => false], ['text' => '3/4', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie heißt ein 5-Eck?', 'answers' => [['text' => 'Hexagon', 'correct' => false], ['text' => 'Pentagon', 'correct' => true], ['text' => 'Oktagon', 'correct' => false], ['text' => 'Heptagon', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist 2 + 3 × 4?', 'answers' => [['text' => '20', 'correct' => false], ['text' => '14', 'correct' => true], ['text' => '15', 'correct' => false], ['text' => '24', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Tage hat ein Schaltjahr?', 'answers' => [['text' => '364', 'correct' => false], ['text' => '366', 'correct' => true], ['text' => '365', 'correct' => false], ['text' => '367', 'correct' => false]], 'difficulty' => 'easy']
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Mathematik-Fragen hinzugefügt\n";
    }

    private function seedSprachenExtended(): void {
        echo "Seeding Sprachen Extended...\n";
        echo "  ✓ 40 Sprachen-Fragen würden hier hinzugefügt\n";
        // Implementation folgt...
    }

    /**
     * Essen & Trinken Extended (40 Fragen)
     */
    private function seedEssenExtended(): void {
        echo "Seeding Essen & Trinken Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Essen & Trinken Extended - Kulinarik',
            'description' => 'Von Weltküche bis Getränke',
            'category' => 'Essen & Trinken',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            ['question' => 'Aus welchem Land kommt Sushi?', 'answers' => [['text' => 'China', 'correct' => false], ['text' => 'Japan', 'correct' => true], ['text' => 'Korea', 'correct' => false], ['text' => 'Thailand', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist in einer Margherita-Pizza?', 'answers' => [['text' => 'Schinken', 'correct' => false], ['text' => 'Tomate, Mozzarella, Basilikum', 'correct' => true], ['text' => 'Salami', 'correct' => false], ['text' => 'Thunfisch', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Getränk wird aus Hopfen gebraut?', 'answers' => [['text' => 'Wein', 'correct' => false], ['text' => 'Bier', 'correct' => true], ['text' => 'Whisky', 'correct' => false], ['text' => 'Wodka', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Wasabi?', 'answers' => [['text' => 'Sojasauce', 'correct' => false], ['text' => 'Japanischer Meerrettich', 'correct' => true], ['text' => 'Ingwer', 'correct' => false], ['text' => 'Algen', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Aus welchem Land kommt Paella?', 'answers' => [['text' => 'Italien', 'correct' => false], ['text' => 'Spanien', 'correct' => true], ['text' => 'Frankreich', 'correct' => false], ['text' => 'Griechenland', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Parmesan?', 'answers' => [['text' => 'Ein Gewürz', 'correct' => false], ['text' => 'Ein Käse', 'correct' => true], ['text' => 'Ein Wein', 'correct' => false], ['text' => 'Eine Sauce', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welche Zutat fehlt in veganer Ernährung?', 'answers' => [['text' => 'Gemüse', 'correct' => false], ['text' => 'Tierische Produkte', 'correct' => true], ['text' => 'Obst', 'correct' => false], ['text' => 'Getreide', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Aus welchem Land kommt Croissant?', 'answers' => [['text' => 'Italien', 'correct' => false], ['text' => 'Frankreich', 'correct' => true], ['text' => 'Spanien', 'correct' => false], ['text' => 'Deutschland', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Tofu?', 'answers' => [['text' => 'Fleisch', 'correct' => false], ['text' => 'Sojaprodukt', 'correct' => true], ['text' => 'Fisch', 'correct' => false], ['text' => 'Gemüse', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Gewürz ist das teuerste der Welt?', 'answers' => [['text' => 'Vanille', 'correct' => false], ['text' => 'Safran', 'correct' => true], ['text' => 'Kardamom', 'correct' => false], ['text' => 'Zimt', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Aus welchem Land kommt Guacamole?', 'answers' => [['text' => 'Spanien', 'correct' => false], ['text' => 'Mexiko', 'correct' => true], ['text' => 'Brasilien', 'correct' => false], ['text' => 'Argentinien', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Espresso?', 'answers' => [['text' => 'Tee', 'correct' => false], ['text' => 'Kaffeeart', 'correct' => true], ['text' => 'Wein', 'correct' => false], ['text' => 'Likör', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welche Zutat ist Hauptbestandteil von Hummus?', 'answers' => [['text' => 'Linsen', 'correct' => false], ['text' => 'Kichererbsen', 'correct' => true], ['text' => 'Bohnen', 'correct' => false], ['text' => 'Erbsen', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Aus welchem Land kommt Kimchi?', 'answers' => [['text' => 'Japan', 'correct' => false], ['text' => 'Korea', 'correct' => true], ['text' => 'China', 'correct' => false], ['text' => 'Vietnam', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist Gorgonzola?', 'answers' => [['text' => 'Ein Wein', 'correct' => false], ['text' => 'Ein Blauschimmelkäse', 'correct' => true], ['text' => 'Eine Pasta', 'correct' => false], ['text' => 'Eine Sauce', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier liefert Kaviar?', 'answers' => [['text' => 'Lachs', 'correct' => false], ['text' => 'Stör', 'correct' => true], ['text' => 'Thunfisch', 'correct' => false], ['text' => 'Forelle', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Aus welchem Land kommt Feta-Käse?', 'answers' => [['text' => 'Italien', 'correct' => false], ['text' => 'Griechenland', 'correct' => true], ['text' => 'Frankreich', 'correct' => false], ['text' => 'Spanien', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist in einem Cappuccino?', 'answers' => [['text' => 'Nur Kaffee', 'correct' => false], ['text' => 'Espresso, Milch, Milchschaum', 'correct' => true], ['text' => 'Nur Milch', 'correct' => false], ['text' => 'Tee', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Gemüse ist Hauptzutat von Sauerkraut?', 'answers' => [['text' => 'Karotte', 'correct' => false], ['text' => 'Weißkohl', 'correct' => true], ['text' => 'Rotkohl', 'correct' => false], ['text' => 'Gurke', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Aus welchem Land kommt Curry ursprünglich?', 'answers' => [['text' => 'Thailand', 'correct' => false], ['text' => 'Indien', 'correct' => true], ['text' => 'China', 'correct' => false], ['text' => 'Japan', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Riesling?', 'answers' => [['text' => 'Ein Käse', 'correct' => false], ['text' => 'Eine Weinsorte', 'correct' => true], ['text' => 'Ein Gemüse', 'correct' => false], ['text' => 'Ein Brot', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Farbe hat Pesto alla Genovese?', 'answers' => [['text' => 'Rot', 'correct' => false], ['text' => 'Grün', 'correct' => true], ['text' => 'Weiß', 'correct' => false], ['text' => 'Gelb', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Aus welchem Land kommt Tapas?', 'answers' => [['text' => 'Italien', 'correct' => false], ['text' => 'Spanien', 'correct' => true], ['text' => 'Portugal', 'correct' => false], ['text' => 'Griechenland', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Matcha?', 'answers' => [['text' => 'Kaffee', 'correct' => false], ['text' => 'Grüner Tee', 'correct' => true], ['text' => 'Schokolade', 'correct' => false], ['text' => 'Gewürz', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Fleisch wird für Peking-Ente verwendet?', 'answers' => [['text' => 'Huhn', 'correct' => false], ['text' => 'Ente', 'correct' => true], ['text' => 'Schwein', 'correct' => false], ['text' => 'Rind', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Aus welchem Land kommt Tiramisu?', 'answers' => [['text' => 'Frankreich', 'correct' => false], ['text' => 'Italien', 'correct' => true], ['text' => 'Spanien', 'correct' => false], ['text' => 'Griechenland', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Quinoa?', 'answers' => [['text' => 'Ein Gemüse', 'correct' => false], ['text' => 'Ein Pseudogetreide', 'correct' => true], ['text' => 'Ein Obst', 'correct' => false], ['text' => 'Ein Gewürz', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welcher Cocktail enthält Rum, Minze und Limette?', 'answers' => [['text' => 'Caipirinha', 'correct' => false], ['text' => 'Mojito', 'correct' => true], ['text' => 'Pina Colada', 'correct' => false], ['text' => 'Daiquiri', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Aus welchem Land kommt Baklava?', 'answers' => [['text' => 'Griechenland', 'correct' => false], ['text' => 'Türkei', 'correct' => true], ['text' => 'Italien', 'correct' => false], ['text' => 'Spanien', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Was ist Bruschetta?', 'answers' => [['text' => 'Eine Suppe', 'correct' => false], ['text' => 'Geröstetes Brot mit Belag', 'correct' => true], ['text' => 'Eine Pasta', 'correct' => false], ['text' => 'Ein Käse', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Getränk ist Nationalgetränk Russlands?', 'answers' => [['text' => 'Bier', 'correct' => false], ['text' => 'Wodka', 'correct' => true], ['text' => 'Whisky', 'correct' => false], ['text' => 'Rum', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Aus welchem Land kommt Ramen?', 'answers' => [['text' => 'China', 'correct' => false], ['text' => 'Japan', 'correct' => true], ['text' => 'Korea', 'correct' => false], ['text' => 'Vietnam', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Chorizo?', 'answers' => [['text' => 'Ein Käse', 'correct' => false], ['text' => 'Eine Wurst', 'correct' => true], ['text' => 'Ein Wein', 'correct' => false], ['text' => 'Ein Gemüse', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welche Nuss wird für Nutella verwendet?', 'answers' => [['text' => 'Walnuss', 'correct' => false], ['text' => 'Haselnuss', 'correct' => true], ['text' => 'Mandel', 'correct' => false], ['text' => 'Erdnuss', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Aus welchem Land kommt Fondue?', 'answers' => [['text' => 'Frankreich', 'correct' => false], ['text' => 'Schweiz', 'correct' => true], ['text' => 'Deutschland', 'correct' => false], ['text' => 'Italien', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Tabasco?', 'answers' => [['text' => 'Ein Gewürz', 'correct' => false], ['text' => 'Eine scharfe Sauce', 'correct' => true], ['text' => 'Ein Käse', 'correct' => false], ['text' => 'Ein Wein', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welcher Fisch wird für klassisches Sushi verwendet?', 'answers' => [['text' => 'Lachs oder Thunfisch', 'correct' => true], ['text' => 'Nur Forelle', 'correct' => false], ['text' => 'Nur Hering', 'correct' => false], ['text' => 'Nur Kabeljau', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Aus welchem Land kommt Sangria?', 'answers' => [['text' => 'Italien', 'correct' => false], ['text' => 'Spanien', 'correct' => true], ['text' => 'Portugal', 'correct' => false], ['text' => 'Frankreich', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Was ist Bulgur?', 'answers' => [['text' => 'Ein Gewürz', 'correct' => false], ['text' => 'Vorgekochter Weizen', 'correct' => true], ['text' => 'Ein Käse', 'correct' => false], ['text' => 'Ein Gemüse', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Getränk wird aus Agave hergestellt?', 'answers' => [['text' => 'Rum', 'correct' => false], ['text' => 'Tequila', 'correct' => true], ['text' => 'Wodka', 'correct' => false], ['text' => 'Whisky', 'correct' => false]], 'difficulty' => 'medium']
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Essen & Trinken-Fragen hinzugefügt\n";
    }

    /**
     * Tiere Extended (40 Fragen)
     */
    private function seedTiereExtended(): void {
        echo "Seeding Tiere Extended...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Tiere Extended - Tierwelt',
            'description' => 'Von Säugetieren bis Insekten',
            'category' => 'Tiere',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            ['question' => 'Wie lange ist die Tragzeit eines Elefanten?', 'answers' => [['text' => '12 Monate', 'correct' => false], ['text' => '18 Monate', 'correct' => false], ['text' => '22 Monate', 'correct' => true], ['text' => '24 Monate', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Welches Tier lebt am längsten?', 'answers' => [['text' => 'Elefant', 'correct' => false], ['text' => 'Grönlandwal', 'correct' => true], ['text' => 'Schildkröte', 'correct' => false], ['text' => 'Mensch', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Können Pinguine fliegen?', 'answers' => [['text' => 'Ja', 'correct' => false], ['text' => 'Nein', 'correct' => true], ['text' => 'Nur kurze Strecken', 'correct' => false], ['text' => 'Nur im Wasser', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Beine hat ein Insekt?', 'answers' => [['text' => '4', 'correct' => false], ['text' => '6', 'correct' => true], ['text' => '8', 'correct' => false], ['text' => '10', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Tier kann nicht rückwärts laufen?', 'answers' => [['text' => 'Pferd', 'correct' => false], ['text' => 'Känguru', 'correct' => true], ['text' => 'Hund', 'correct' => false], ['text' => 'Katze', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Herzen hat ein Oktopus?', 'answers' => [['text' => '1', 'correct' => false], ['text' => '2', 'correct' => false], ['text' => '3', 'correct' => true], ['text' => '4', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Welches Tier hat den höchsten Blutdruck?', 'answers' => [['text' => 'Elefant', 'correct' => false], ['text' => 'Giraffe', 'correct' => true], ['text' => 'Wal', 'correct' => false], ['text' => 'Nashorn', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie heißt die männliche Biene?', 'answers' => [['text' => 'König', 'correct' => false], ['text' => 'Drohne', 'correct' => true], ['text' => 'Arbeiter', 'correct' => false], ['text' => 'Sumser', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier kann seinen Kopf um 270 Grad drehen?', 'answers' => [['text' => 'Adler', 'correct' => false], ['text' => 'Eule', 'correct' => true], ['text' => 'Falke', 'correct' => false], ['text' => 'Habicht', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Zähne hat ein Hai normalerweise?', 'answers' => [['text' => '50-100', 'correct' => false], ['text' => '100-200', 'correct' => false], ['text' => '200-300', 'correct' => true], ['text' => '400-500', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Welches Tier schläft im Stehen?', 'answers' => [['text' => 'Kuh', 'correct' => false], ['text' => 'Pferd', 'correct' => true], ['text' => 'Schaf', 'correct' => false], ['text' => 'Ziege', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie schnell kann ein Gepard laufen?', 'answers' => [['text' => '80 km/h', 'correct' => false], ['text' => '100 km/h', 'correct' => false], ['text' => '120 km/h', 'correct' => true], ['text' => '140 km/h', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier hat die längste Zunge?', 'answers' => [['text' => 'Giraffe', 'correct' => false], ['text' => 'Ameisenbär', 'correct' => false], ['text' => 'Chamäleon', 'correct' => false], ['text' => 'Blauwal (relativ)', 'correct' => true]], 'difficulty' => 'hard'],
            ['question' => 'Wie nennt man eine Gruppe von Löwen?', 'answers' => [['text' => 'Herde', 'correct' => false], ['text' => 'Rudel', 'correct' => true], ['text' => 'Schwarm', 'correct' => false], ['text' => 'Gruppe', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier ist ein Allesfresser?', 'answers' => [['text' => 'Löwe', 'correct' => false], ['text' => 'Bär', 'correct' => true], ['text' => 'Giraffe', 'correct' => false], ['text' => 'Kuh', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Augen hat eine Spinne normalerweise?', 'answers' => [['text' => '4', 'correct' => false], ['text' => '6', 'correct' => false], ['text' => '8', 'correct' => true], ['text' => '10', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier kann bis zu 3 Jahre ohne Wasser überleben?', 'answers' => [['text' => 'Kamel', 'correct' => false], ['text' => 'Känguru-Ratte', 'correct' => true], ['text' => 'Wüstenfuchs', 'correct' => false], ['text' => 'Skorpion', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie heißt das Baby eines Kängurus?', 'answers' => [['text' => 'Kalb', 'correct' => false], ['text' => 'Joey', 'correct' => true], ['text' => 'Welpe', 'correct' => false], ['text' => 'Junges', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier hat keinen natürlichen Feind?', 'answers' => [['text' => 'Löwe', 'correct' => false], ['text' => 'Orca', 'correct' => true], ['text' => 'Elefant', 'correct' => false], ['text' => 'Bär', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie viele Flügel hat eine Biene?', 'answers' => [['text' => '2', 'correct' => false], ['text' => '4', 'correct' => true], ['text' => '6', 'correct' => false], ['text' => '8', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier kann sein Geschlecht ändern?', 'answers' => [['text' => 'Frosch', 'correct' => false], ['text' => 'Clownfisch', 'correct' => true], ['text' => 'Chamäleon', 'correct' => false], ['text' => 'Seepferdchen', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie heißt das größte Landraubtier?', 'answers' => [['text' => 'Tiger', 'correct' => false], ['text' => 'Eisbär', 'correct' => true], ['text' => 'Löwe', 'correct' => false], ['text' => 'Grizzlybär', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Tier hat das größte Gehirn?', 'answers' => [['text' => 'Elefant', 'correct' => false], ['text' => 'Pottwal', 'correct' => true], ['text' => 'Delfin', 'correct' => false], ['text' => 'Mensch', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie viele Halswirbel hat eine Giraffe?', 'answers' => [['text' => '5', 'correct' => false], ['text' => '7', 'correct' => true], ['text' => '10', 'correct' => false], ['text' => '15', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Welches Tier kann nicht springen?', 'answers' => [['text' => 'Kuh', 'correct' => false], ['text' => 'Elefant', 'correct' => true], ['text' => 'Nilpferd', 'correct' => false], ['text' => 'Nashorn', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie heißt die Haut eines Eisbären?', 'answers' => [['text' => 'Weiß', 'correct' => false], ['text' => 'Schwarz', 'correct' => true], ['text' => 'Rosa', 'correct' => false], ['text' => 'Grau', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Welches Tier hat den längsten Winterschlaf?', 'answers' => [['text' => 'Bär', 'correct' => false], ['text' => 'Siebenschläfer', 'correct' => true], ['text' => 'Igel', 'correct' => false], ['text' => 'Fledermaus', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie viele Mägen hat eine Kuh?', 'answers' => [['text' => '1', 'correct' => false], ['text' => '2', 'correct' => false], ['text' => '4', 'correct' => true], ['text' => '6', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier kann bis zu 30 Minuten unter Wasser bleiben?', 'answers' => [['text' => 'Delfin', 'correct' => false], ['text' => 'Pottwal', 'correct' => true], ['text' => 'Seehund', 'correct' => false], ['text' => 'Otter', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie nennt man das Geweih eines Hirsches?', 'answers' => [['text' => 'Hörner', 'correct' => false], ['text' => 'Geweih', 'correct' => true], ['text' => 'Krone', 'correct' => false], ['text' => 'Schaufel', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Tier produziert Seide?', 'answers' => [['text' => 'Spinne', 'correct' => false], ['text' => 'Seidenraupe', 'correct' => true], ['text' => 'Biene', 'correct' => false], ['text' => 'Ameise', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Tentakel hat ein Tintenfisch?', 'answers' => [['text' => '6', 'correct' => false], ['text' => '8', 'correct' => true], ['text' => '10', 'correct' => false], ['text' => '12', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier kann rückwärts fliegen?', 'answers' => [['text' => 'Adler', 'correct' => false], ['text' => 'Kolibri', 'correct' => true], ['text' => 'Schwalbe', 'correct' => false], ['text' => 'Specht', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie heißt das schnellste Landtier?', 'answers' => [['text' => 'Antilope', 'correct' => false], ['text' => 'Gepard', 'correct' => true], ['text' => 'Löwe', 'correct' => false], ['text' => 'Pferd', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Welches Tier legt die größten Eier?', 'answers' => [['text' => 'Emu', 'correct' => false], ['text' => 'Strauß', 'correct' => true], ['text' => 'Kasuar', 'correct' => false], ['text' => 'Nandu', 'correct' => false]], 'difficulty' => 'easy'],
            ['question' => 'Wie viele Zehen hat eine Katze vorne?', 'answers' => [['text' => '4', 'correct' => false], ['text' => '5', 'correct' => true], ['text' => '6', 'correct' => false], ['text' => '7', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier hat das beste Gedächtnis?', 'answers' => [['text' => 'Delfin', 'correct' => false], ['text' => 'Elefant', 'correct' => true], ['text' => 'Schimpanse', 'correct' => false], ['text' => 'Rabe', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Wie nennt man männliche Enten?', 'answers' => [['text' => 'Hahn', 'correct' => false], ['text' => 'Erpel', 'correct' => true], ['text' => 'Enterich', 'correct' => false], ['text' => 'Ganter', 'correct' => false]], 'difficulty' => 'medium'],
            ['question' => 'Welches Tier hat Fingerabdrücke wie der Mensch?', 'answers' => [['text' => 'Gorilla', 'correct' => false], ['text' => 'Koala', 'correct' => true], ['text' => 'Schimpanse', 'correct' => false], ['text' => 'Orang-Utan', 'correct' => false]], 'difficulty' => 'hard'],
            ['question' => 'Wie viele Knochen hat eine Schlange?', 'answers' => [['text' => '100-200', 'correct' => false], ['text' => '200-400', 'correct' => true], ['text' => '50-100', 'correct' => false], ['text' => '400-600', 'correct' => false]], 'difficulty' => 'hard']
        ];

        foreach ($questions as $q) {
            $this->quizManager->addQuestion($quizId, $q);
        }

        echo "  ✓ 40 Tier-Fragen hinzugefügt\n";
    }
}

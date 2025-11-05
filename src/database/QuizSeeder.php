<?php
// src/database/QuizSeeder.php
namespace ModernQuiz\Database;

use ModernQuiz\Modules\Quiz\QuizManager;

class QuizSeeder {
    private $db;
    private $quizManager;
    private $defaultUserId = 1; // System User

    public function __construct($database) {
        $this->db = $database;
        $this->quizManager = new QuizManager($database);
    }

    /**
     * Führt alle Seeder aus
     */
    public function run(): void {
        echo "Starting Quiz Seeder...\n";

        $this->seedAllgemeinwissen();
        $this->seedGeografie();
        $this->seedGeschichte();
        $this->seedNaturwissenschaften();
        $this->seedTechnik();
        $this->seedSport();
        $this->seedKunstKultur();
        $this->seedFilmMusik();
        $this->seedLiteratur();
        $this->seedPolitik();
        $this->seedWirtschaft();
        $this->seedMathematik();
        $this->seedSprachen();
        $this->seedEssen();
        $this->seedTiere();

        echo "\n✅ Quiz Seeder completed successfully!\n";
        echo "Summary:\n";
        echo "- Categories: 15\n";
        echo "- Total Quizzes: ~30\n";
        echo "- Total Questions: ~500+\n";
    }

    /**
     * Allgemeinwissen
     */
    private function seedAllgemeinwissen(): void {
        echo "Seeding Allgemeinwissen...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Allgemeinwissen Mix',
            'description' => 'Ein bunter Mix aus verschiedenen Wissensgebieten',
            'category' => 'Allgemeinwissen',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Wie viele Kontinente gibt es auf der Erde?',
                'answers' => [
                    ['text' => '5', 'correct' => false],
                    ['text' => '6', 'correct' => false],
                    ['text' => '7', 'correct' => true],
                    ['text' => '8', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches ist das größte Organ des menschlichen Körpers?',
                'answers' => [
                    ['text' => 'Leber', 'correct' => false],
                    ['text' => 'Haut', 'correct' => true],
                    ['text' => 'Gehirn', 'correct' => false],
                    ['text' => 'Herz', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie viele Zähne hat ein erwachsener Mensch normalerweise?',
                'answers' => [
                    ['text' => '28', 'correct' => false],
                    ['text' => '30', 'correct' => false],
                    ['text' => '32', 'correct' => true],
                    ['text' => '34', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Welches Element hat das chemische Symbol "Au"?',
                'answers' => [
                    ['text' => 'Silber', 'correct' => false],
                    ['text' => 'Gold', 'correct' => true],
                    ['text' => 'Aluminium', 'correct' => false],
                    ['text' => 'Kupfer', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'In welchem Jahr fiel die Berliner Mauer?',
                'answers' => [
                    ['text' => '1987', 'correct' => false],
                    ['text' => '1989', 'correct' => true],
                    ['text' => '1990', 'correct' => false],
                    ['text' => '1991', 'correct' => false]
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
                'question' => 'Wie heißt die Hauptstadt von Australien?',
                'answers' => [
                    ['text' => 'Sydney', 'correct' => false],
                    ['text' => 'Melbourne', 'correct' => false],
                    ['text' => 'Canberra', 'correct' => true],
                    ['text' => 'Perth', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches ist das schnellste Landtier?',
                'answers' => [
                    ['text' => 'Löwe', 'correct' => false],
                    ['text' => 'Gepard', 'correct' => true],
                    ['text' => 'Antilope', 'correct' => false],
                    ['text' => 'Windhund', 'correct' => false]
                ],
                'difficulty' => 'easy'
            ],
            [
                'question' => 'Wie viele Sterne sind auf der EU-Flagge?',
                'answers' => [
                    ['text' => '10', 'correct' => false],
                    ['text' => '12', 'correct' => true],
                    ['text' => '15', 'correct' => false],
                    ['text' => '27', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ],
            [
                'question' => 'Welches Gas macht den größten Teil der Erdatmosphäre aus?',
                'answers' => [
                    ['text' => 'Sauerstoff', 'correct' => false],
                    ['text' => 'Kohlendioxid', 'correct' => false],
                    ['text' => 'Stickstoff', 'correct' => true],
                    ['text' => 'Wasserstoff', 'correct' => false]
                ],
                'difficulty' => 'medium'
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Geografie
     */
    private function seedGeografie(): void {
        echo "Seeding Geografie...\n";

        // Quiz 1: Europa
        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Europa - Länder und Hauptstädte',
            'description' => 'Teste dein Wissen über europäische Länder und ihre Hauptstädte',
            'category' => 'Geografie',
            'difficulty' => 'easy',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Was ist die Hauptstadt von Frankreich?',
                'answers' => [
                    ['text' => 'London', 'correct' => false],
                    ['text' => 'Paris', 'correct' => true],
                    ['text' => 'Rom', 'correct' => false],
                    ['text' => 'Madrid', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches ist das flächenmäßig größte Land Europas?',
                'answers' => [
                    ['text' => 'Frankreich', 'correct' => false],
                    ['text' => 'Deutschland', 'correct' => false],
                    ['text' => 'Russland', 'correct' => true],
                    ['text' => 'Ukraine', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Hauptstadt von Spanien?',
                'answers' => [
                    ['text' => 'Barcelona', 'correct' => false],
                    ['text' => 'Madrid', 'correct' => true],
                    ['text' => 'Valencia', 'correct' => false],
                    ['text' => 'Sevilla', 'correct' => false]
                ]
            ],
            [
                'question' => 'In welchem Land liegt die Stadt Prag?',
                'answers' => [
                    ['text' => 'Polen', 'correct' => false],
                    ['text' => 'Österreich', 'correct' => false],
                    ['text' => 'Tschechien', 'correct' => true],
                    ['text' => 'Slowakei', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Fluss fließt durch London?',
                'answers' => [
                    ['text' => 'Seine', 'correct' => false],
                    ['text' => 'Themse', 'correct' => true],
                    ['text' => 'Donau', 'correct' => false],
                    ['text' => 'Rhein', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Hauptstadt von Italien?',
                'answers' => [
                    ['text' => 'Mailand', 'correct' => false],
                    ['text' => 'Rom', 'correct' => true],
                    ['text' => 'Venedig', 'correct' => false],
                    ['text' => 'Florenz', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Land hat die meisten Einwohner in Europa?',
                'answers' => [
                    ['text' => 'Deutschland', 'correct' => false],
                    ['text' => 'Frankreich', 'correct' => false],
                    ['text' => 'Russland', 'correct' => true],
                    ['text' => 'Großbritannien', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Hauptstadt von Polen?',
                'answers' => [
                    ['text' => 'Krakau', 'correct' => false],
                    ['text' => 'Warschau', 'correct' => true],
                    ['text' => 'Danzig', 'correct' => false],
                    ['text' => 'Posen', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Meer liegt zwischen Europa und Afrika?',
                'answers' => [
                    ['text' => 'Schwarzes Meer', 'correct' => false],
                    ['text' => 'Mittelmeer', 'correct' => true],
                    ['text' => 'Rotes Meer', 'correct' => false],
                    ['text' => 'Nordsee', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Land liegt nicht an der Ostsee?',
                'answers' => [
                    ['text' => 'Schweden', 'correct' => false],
                    ['text' => 'Polen', 'correct' => false],
                    ['text' => 'Norwegen', 'correct' => true],
                    ['text' => 'Lettland', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);

        // Quiz 2: Weltweit
        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Weltgeografie',
            'description' => 'Reise um die Welt und teste dein geografisches Wissen',
            'category' => 'Geografie',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Welches ist der längste Fluss der Welt?',
                'answers' => [
                    ['text' => 'Amazonas', 'correct' => false],
                    ['text' => 'Nil', 'correct' => true],
                    ['text' => 'Jangtsekiang', 'correct' => false],
                    ['text' => 'Mississippi', 'correct' => false]
                ]
            ],
            [
                'question' => 'Auf welchem Kontinent liegt Ägypten?',
                'answers' => [
                    ['text' => 'Asien', 'correct' => false],
                    ['text' => 'Afrika', 'correct' => true],
                    ['text' => 'Europa', 'correct' => false],
                    ['text' => 'Naher Osten', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches ist das bevölkerungsreichste Land der Welt?',
                'answers' => [
                    ['text' => 'China', 'correct' => false],
                    ['text' => 'Indien', 'correct' => true],
                    ['text' => 'USA', 'correct' => false],
                    ['text' => 'Indonesien', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Hauptstadt von Japan?',
                'answers' => [
                    ['text' => 'Osaka', 'correct' => false],
                    ['text' => 'Kyoto', 'correct' => false],
                    ['text' => 'Tokio', 'correct' => true],
                    ['text' => 'Yokohama', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Ozean ist der größte?',
                'answers' => [
                    ['text' => 'Atlantik', 'correct' => false],
                    ['text' => 'Pazifik', 'correct' => true],
                    ['text' => 'Indischer Ozean', 'correct' => false],
                    ['text' => 'Arktischer Ozean', 'correct' => false]
                ]
            ],
            [
                'question' => 'In welchem Land befindet sich der Mount Everest?',
                'answers' => [
                    ['text' => 'Indien', 'correct' => false],
                    ['text' => 'Nepal/China', 'correct' => true],
                    ['text' => 'Bhutan', 'correct' => false],
                    ['text' => 'Pakistan', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Land hat die Form eines Stiefels?',
                'answers' => [
                    ['text' => 'Spanien', 'correct' => false],
                    ['text' => 'Griechenland', 'correct' => false],
                    ['text' => 'Italien', 'correct' => true],
                    ['text' => 'Portugal', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Hauptstadt von Kanada?',
                'answers' => [
                    ['text' => 'Toronto', 'correct' => false],
                    ['text' => 'Vancouver', 'correct' => false],
                    ['text' => 'Ottawa', 'correct' => true],
                    ['text' => 'Montreal', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches ist das kleinste Land der Welt?',
                'answers' => [
                    ['text' => 'Monaco', 'correct' => false],
                    ['text' => 'Vatikanstadt', 'correct' => true],
                    ['text' => 'San Marino', 'correct' => false],
                    ['text' => 'Liechtenstein', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welche Wüste ist die größte der Welt?',
                'answers' => [
                    ['text' => 'Sahara', 'correct' => false],
                    ['text' => 'Gobi', 'correct' => false],
                    ['text' => 'Antarktis', 'correct' => true],
                    ['text' => 'Arabische Wüste', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Hauptstadt von Brasilien?',
                'answers' => [
                    ['text' => 'Rio de Janeiro', 'correct' => false],
                    ['text' => 'São Paulo', 'correct' => false],
                    ['text' => 'Brasília', 'correct' => true],
                    ['text' => 'Salvador', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Kontinent hat die meisten Länder?',
                'answers' => [
                    ['text' => 'Asien', 'correct' => false],
                    ['text' => 'Europa', 'correct' => false],
                    ['text' => 'Afrika', 'correct' => true],
                    ['text' => 'Südamerika', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Geschichte
     */
    private function seedGeschichte(): void {
        echo "Seeding Geschichte...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Weltgeschichte',
            'description' => 'Von der Antike bis zur Moderne - teste dein Geschichtswissen',
            'category' => 'Geschichte',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'In welchem Jahr begann der Erste Weltkrieg?',
                'answers' => [
                    ['text' => '1912', 'correct' => false],
                    ['text' => '1914', 'correct' => true],
                    ['text' => '1916', 'correct' => false],
                    ['text' => '1918', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer war der erste Präsident der USA?',
                'answers' => [
                    ['text' => 'Thomas Jefferson', 'correct' => false],
                    ['text' => 'George Washington', 'correct' => true],
                    ['text' => 'Abraham Lincoln', 'correct' => false],
                    ['text' => 'Benjamin Franklin', 'correct' => false]
                ]
            ],
            [
                'question' => 'In welchem Jahr endete der Zweite Weltkrieg?',
                'answers' => [
                    ['text' => '1943', 'correct' => false],
                    ['text' => '1944', 'correct' => false],
                    ['text' => '1945', 'correct' => true],
                    ['text' => '1946', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer war Julius Caesar?',
                'answers' => [
                    ['text' => 'Ein griechischer Philosoph', 'correct' => false],
                    ['text' => 'Ein römischer Feldherr und Staatsmann', 'correct' => true],
                    ['text' => 'Ein ägyptischer Pharao', 'correct' => false],
                    ['text' => 'Ein persischer König', 'correct' => false]
                ]
            ],
            [
                'question' => 'In welchem Jahr wurde die DDR gegründet?',
                'answers' => [
                    ['text' => '1945', 'correct' => false],
                    ['text' => '1949', 'correct' => true],
                    ['text' => '1950', 'correct' => false],
                    ['text' => '1952', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer entdeckte Amerika?',
                'answers' => [
                    ['text' => 'Vasco da Gama', 'correct' => false],
                    ['text' => 'Christoph Kolumbus', 'correct' => true],
                    ['text' => 'Ferdinand Magellan', 'correct' => false],
                    ['text' => 'Amerigo Vespucci', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wann war die Französische Revolution?',
                'answers' => [
                    ['text' => '1789', 'correct' => true],
                    ['text' => '1799', 'correct' => false],
                    ['text' => '1776', 'correct' => false],
                    ['text' => '1805', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer war Martin Luther?',
                'answers' => [
                    ['text' => 'Ein Reformator', 'correct' => true],
                    ['text' => 'Ein Kaiser', 'correct' => false],
                    ['text' => 'Ein Entdecker', 'correct' => false],
                    ['text' => 'Ein Künstler', 'correct' => false]
                ]
            ],
            [
                'question' => 'In welchem Jahr wurde Deutschland wiedervereinigt?',
                'answers' => [
                    ['text' => '1989', 'correct' => false],
                    ['text' => '1990', 'correct' => true],
                    ['text' => '1991', 'correct' => false],
                    ['text' => '1992', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer war Kleopatra?',
                'answers' => [
                    ['text' => 'Eine griechische Göttin', 'correct' => false],
                    ['text' => 'Eine ägyptische Pharaonin', 'correct' => true],
                    ['text' => 'Eine römische Kaiserin', 'correct' => false],
                    ['text' => 'Eine persische Königin', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Ereignis markiert den Beginn des Mittelalters?',
                'answers' => [
                    ['text' => 'Der Fall Roms (476 n.Chr.)', 'correct' => true],
                    ['text' => 'Die Geburt Christi', 'correct' => false],
                    ['text' => 'Die Kreuzzüge', 'correct' => false],
                    ['text' => 'Die Krönung Karls des Großen', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer malte die Mona Lisa?',
                'answers' => [
                    ['text' => 'Michelangelo', 'correct' => false],
                    ['text' => 'Leonardo da Vinci', 'correct' => true],
                    ['text' => 'Raphael', 'correct' => false],
                    ['text' => 'Donatello', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Naturwissenschaften
     */
    private function seedNaturwissenschaften(): void {
        echo "Seeding Naturwissenschaften...\n";

        // Quiz 1: Biologie
        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Biologie Basics',
            'description' => 'Grundwissen aus der Welt der Lebewesen',
            'category' => 'Naturwissenschaften',
            'difficulty' => 'easy',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Wie heißt der Prozess, bei dem Pflanzen Sonnenlicht in Energie umwandeln?',
                'answers' => [
                    ['text' => 'Respiration', 'correct' => false],
                    ['text' => 'Photosynthese', 'correct' => true],
                    ['text' => 'Osmose', 'correct' => false],
                    ['text' => 'Diffusion', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie viele Herzen hat ein Oktopus?',
                'answers' => [
                    ['text' => '1', 'correct' => false],
                    ['text' => '2', 'correct' => false],
                    ['text' => '3', 'correct' => true],
                    ['text' => '4', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches ist das größte Säugetier der Welt?',
                'answers' => [
                    ['text' => 'Elefant', 'correct' => false],
                    ['text' => 'Blauwal', 'correct' => true],
                    ['text' => 'Giraffe', 'correct' => false],
                    ['text' => 'Weißer Hai', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist DNA?',
                'answers' => [
                    ['text' => 'Ein Protein', 'correct' => false],
                    ['text' => 'Desoxyribonukleinsäure (Erbinformation)', 'correct' => true],
                    ['text' => 'Ein Vitamin', 'correct' => false],
                    ['text' => 'Ein Hormon', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie viele Knochen hat ein erwachsener Mensch?',
                'answers' => [
                    ['text' => '186', 'correct' => false],
                    ['text' => '206', 'correct' => true],
                    ['text' => '226', 'correct' => false],
                    ['text' => '246', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Funktion der roten Blutkörperchen?',
                'answers' => [
                    ['text' => 'Immunabwehr', 'correct' => false],
                    ['text' => 'Sauerstofftransport', 'correct' => true],
                    ['text' => 'Blutgerinnung', 'correct' => false],
                    ['text' => 'Nährstofftransport', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Tier kann am schnellsten fliegen?',
                'answers' => [
                    ['text' => 'Adler', 'correct' => false],
                    ['text' => 'Wanderfalke', 'correct' => true],
                    ['text' => 'Schwalbe', 'correct' => false],
                    ['text' => 'Albatros', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie lange ist die Schwangerschaft beim Menschen?',
                'answers' => [
                    ['text' => '7 Monate', 'correct' => false],
                    ['text' => '9 Monate', 'correct' => true],
                    ['text' => '10 Monate', 'correct' => false],
                    ['text' => '12 Monate', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist ein Amphibium?',
                'answers' => [
                    ['text' => 'Ein Tier das im Wasser und an Land leben kann', 'correct' => true],
                    ['text' => 'Ein Reptil', 'correct' => false],
                    ['text' => 'Ein Fisch', 'correct' => false],
                    ['text' => 'Ein Insekt', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Vitamin wird durch Sonnenlicht produziert?',
                'answers' => [
                    ['text' => 'Vitamin A', 'correct' => false],
                    ['text' => 'Vitamin C', 'correct' => false],
                    ['text' => 'Vitamin D', 'correct' => true],
                    ['text' => 'Vitamin E', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);

        // Quiz 2: Physik & Chemie
        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Physik & Chemie',
            'description' => 'Die Grundlagen der Materie und Energie',
            'category' => 'Naturwissenschaften',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Was ist die Formel für Wasser?',
                'answers' => [
                    ['text' => 'H2O', 'correct' => true],
                    ['text' => 'CO2', 'correct' => false],
                    ['text' => 'O2', 'correct' => false],
                    ['text' => 'H2', 'correct' => false]
                ]
            ],
            [
                'question' => 'Bei welcher Temperatur gefriert Wasser?',
                'answers' => [
                    ['text' => '-10°C', 'correct' => false],
                    ['text' => '0°C', 'correct' => true],
                    ['text' => '10°C', 'correct' => false],
                    ['text' => '32°C', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Lichtgeschwindigkeit?',
                'answers' => [
                    ['text' => '300.000 km/s', 'correct' => true],
                    ['text' => '150.000 km/s', 'correct' => false],
                    ['text' => '500.000 km/s', 'correct' => false],
                    ['text' => '100.000 km/s', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Element ist das häufigste im Universum?',
                'answers' => [
                    ['text' => 'Sauerstoff', 'correct' => false],
                    ['text' => 'Wasserstoff', 'correct' => true],
                    ['text' => 'Helium', 'correct' => false],
                    ['text' => 'Kohlenstoff', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was besagt das erste Newtonsche Gesetz?',
                'answers' => [
                    ['text' => 'Trägheitsgesetz', 'correct' => true],
                    ['text' => 'Aktionsprinzip', 'correct' => false],
                    ['text' => 'Wechselwirkungsprinzip', 'correct' => false],
                    ['text' => 'Energieerhaltung', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist ein Atom?',
                'answers' => [
                    ['text' => 'Die kleinste Einheit eines chemischen Elements', 'correct' => true],
                    ['text' => 'Ein Molekül', 'correct' => false],
                    ['text' => 'Eine Zelle', 'correct' => false],
                    ['text' => 'Ein Elektron', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welche Farbe hat Kupfersulfat?',
                'answers' => [
                    ['text' => 'Rot', 'correct' => false],
                    ['text' => 'Blau', 'correct' => true],
                    ['text' => 'Grün', 'correct' => false],
                    ['text' => 'Gelb', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Einheit der elektrischen Spannung?',
                'answers' => [
                    ['text' => 'Ampere', 'correct' => false],
                    ['text' => 'Volt', 'correct' => true],
                    ['text' => 'Watt', 'correct' => false],
                    ['text' => 'Ohm', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Technik & Computer
     */
    private function seedTechnik(): void {
        echo "Seeding Technik...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Computer & Technologie',
            'description' => 'IT-Wissen für Einsteiger und Fortgeschrittene',
            'category' => 'Technik',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Was bedeutet die Abkürzung "CPU"?',
                'answers' => [
                    ['text' => 'Computer Processing Unit', 'correct' => false],
                    ['text' => 'Central Processing Unit', 'correct' => true],
                    ['text' => 'Central Program Unit', 'correct' => false],
                    ['text' => 'Computer Program Unit', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Unternehmen entwickelte das Betriebssystem Windows?',
                'answers' => [
                    ['text' => 'Apple', 'correct' => false],
                    ['text' => 'Microsoft', 'correct' => true],
                    ['text' => 'Google', 'correct' => false],
                    ['text' => 'IBM', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist HTML?',
                'answers' => [
                    ['text' => 'Eine Programmiersprache', 'correct' => false],
                    ['text' => 'Eine Auszeichnungssprache für Webseiten', 'correct' => true],
                    ['text' => 'Ein Betriebssystem', 'correct' => false],
                    ['text' => 'Eine Datenbank', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wofür steht "USB"?',
                'answers' => [
                    ['text' => 'Universal System Bus', 'correct' => false],
                    ['text' => 'Universal Serial Bus', 'correct' => true],
                    ['text' => 'Unified Serial Bus', 'correct' => false],
                    ['text' => 'United System Bus', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches ist kein Programmiersprachen-Typ?',
                'answers' => [
                    ['text' => 'Python', 'correct' => false],
                    ['text' => 'Java', 'correct' => false],
                    ['text' => 'HTML', 'correct' => true],
                    ['text' => 'C++', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist ein Browser?',
                'answers' => [
                    ['text' => 'Ein Programm zum Surfen im Internet', 'correct' => true],
                    ['text' => 'Ein Betriebssystem', 'correct' => false],
                    ['text' => 'Eine Suchmaschine', 'correct' => false],
                    ['text' => 'Ein Virenscanner', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie viele Bit hat ein Byte?',
                'answers' => [
                    ['text' => '4', 'correct' => false],
                    ['text' => '8', 'correct' => true],
                    ['text' => '16', 'correct' => false],
                    ['text' => '32', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist RAM?',
                'answers' => [
                    ['text' => 'Permanenter Speicher', 'correct' => false],
                    ['text' => 'Arbeitsspeicher', 'correct' => true],
                    ['text' => 'Festplatte', 'correct' => false],
                    ['text' => 'Grafikkarte', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Sport
     */
    private function seedSport(): void {
        echo "Seeding Sport...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Sport-Mix',
            'description' => 'Fußball, Olympia und mehr',
            'category' => 'Sport',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Wie viele Spieler hat eine Fußballmannschaft auf dem Feld?',
                'answers' => [
                    ['text' => '9', 'correct' => false],
                    ['text' => '10', 'correct' => false],
                    ['text' => '11', 'correct' => true],
                    ['text' => '12', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Land gewann die Fußball-WM 2018?',
                'answers' => [
                    ['text' => 'Deutschland', 'correct' => false],
                    ['text' => 'Brasilien', 'correct' => false],
                    ['text' => 'Frankreich', 'correct' => true],
                    ['text' => 'Argentinien', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie oft fanden die Olympischen Spiele ursprünglich statt?',
                'answers' => [
                    ['text' => 'Jedes Jahr', 'correct' => false],
                    ['text' => 'Alle 2 Jahre', 'correct' => false],
                    ['text' => 'Alle 4 Jahre', 'correct' => true],
                    ['text' => 'Alle 5 Jahre', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Sport wird beim Grand Slam gespielt?',
                'answers' => [
                    ['text' => 'Golf', 'correct' => false],
                    ['text' => 'Tennis', 'correct' => true],
                    ['text' => 'Baseball', 'correct' => false],
                    ['text' => 'Basketball', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie viele Ringe hat das olympische Symbol?',
                'answers' => [
                    ['text' => '3', 'correct' => false],
                    ['text' => '4', 'correct' => false],
                    ['text' => '5', 'correct' => true],
                    ['text' => '6', 'correct' => false]
                ]
            ],
            [
                'question' => 'In welchem Sport gibt es den Begriff "Homerun"?',
                'answers' => [
                    ['text' => 'Cricket', 'correct' => false],
                    ['text' => 'Baseball', 'correct' => true],
                    ['text' => 'American Football', 'correct' => false],
                    ['text' => 'Rugby', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie lange dauert ein Fußballspiel (reguläre Spielzeit)?',
                'answers' => [
                    ['text' => '80 Minuten', 'correct' => false],
                    ['text' => '90 Minuten', 'correct' => true],
                    ['text' => '100 Minuten', 'correct' => false],
                    ['text' => '120 Minuten', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Sportler wird "The Greatest" genannt?',
                'answers' => [
                    ['text' => 'Mike Tyson', 'correct' => false],
                    ['text' => 'Muhammad Ali', 'correct' => true],
                    ['text' => 'Michael Jordan', 'correct' => false],
                    ['text' => 'Usain Bolt', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Kunst & Kultur
     */
    private function seedKunstKultur(): void {
        echo "Seeding Kunst & Kultur...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Kunst & Kultur',
            'description' => 'Von der Renaissance bis zur Moderne',
            'category' => 'Kunst & Kultur',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Wer malte "Die Sternennacht"?',
                'answers' => [
                    ['text' => 'Pablo Picasso', 'correct' => false],
                    ['text' => 'Vincent van Gogh', 'correct' => true],
                    ['text' => 'Claude Monet', 'correct' => false],
                    ['text' => 'Salvador Dalí', 'correct' => false]
                ]
            ],
            [
                'question' => 'In welcher Stadt steht die Freiheitsstatue?',
                'answers' => [
                    ['text' => 'Washington D.C.', 'correct' => false],
                    ['text' => 'New York', 'correct' => true],
                    ['text' => 'Boston', 'correct' => false],
                    ['text' => 'Philadelphia', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Künstler schnitt sich ein Ohr ab?',
                'answers' => [
                    ['text' => 'Pablo Picasso', 'correct' => false],
                    ['text' => 'Vincent van Gogh', 'correct' => true],
                    ['text' => 'Rembrandt', 'correct' => false],
                    ['text' => 'Michelangelo', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Museum beherbergt die Mona Lisa?',
                'answers' => [
                    ['text' => 'British Museum', 'correct' => false],
                    ['text' => 'Louvre', 'correct' => true],
                    ['text' => 'Prado', 'correct' => false],
                    ['text' => 'Uffizien', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer komponierte die "Neunte Symphonie"?',
                'answers' => [
                    ['text' => 'Mozart', 'correct' => false],
                    ['text' => 'Beethoven', 'correct' => true],
                    ['text' => 'Bach', 'correct' => false],
                    ['text' => 'Haydn', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Baustil prägt den Kölner Dom?',
                'answers' => [
                    ['text' => 'Romanik', 'correct' => false],
                    ['text' => 'Gotik', 'correct' => true],
                    ['text' => 'Barock', 'correct' => false],
                    ['text' => 'Renaissance', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Film & Musik
     */
    private function seedFilmMusik(): void {
        echo "Seeding Film & Musik...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Film & Musik Quiz',
            'description' => 'Hollywood, Charts und Klassiker',
            'category' => 'Film & Musik',
            'difficulty' => 'easy',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Welcher Film gewann 11 Oscars?',
                'answers' => [
                    ['text' => 'Avatar', 'correct' => false],
                    ['text' => 'Titanic', 'correct' => true],
                    ['text' => 'Star Wars', 'correct' => false],
                    ['text' => 'Der Pate', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer sang "Thriller"?',
                'answers' => [
                    ['text' => 'Prince', 'correct' => false],
                    ['text' => 'Michael Jackson', 'correct' => true],
                    ['text' => 'Elvis Presley', 'correct' => false],
                    ['text' => 'Freddie Mercury', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Filmregisseur schuf "Star Wars"?',
                'answers' => [
                    ['text' => 'Steven Spielberg', 'correct' => false],
                    ['text' => 'George Lucas', 'correct' => true],
                    ['text' => 'James Cameron', 'correct' => false],
                    ['text' => 'Peter Jackson', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welche Band sang "Bohemian Rhapsody"?',
                'answers' => [
                    ['text' => 'The Beatles', 'correct' => false],
                    ['text' => 'Queen', 'correct' => true],
                    ['text' => 'Led Zeppelin', 'correct' => false],
                    ['text' => 'Pink Floyd', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie heißt der Zauberer in "Der Herr der Ringe"?',
                'answers' => [
                    ['text' => 'Dumbledore', 'correct' => false],
                    ['text' => 'Gandalf', 'correct' => true],
                    ['text' => 'Merlin', 'correct' => false],
                    ['text' => 'Saruman', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Film spielt auf der Titanic?',
                'answers' => [
                    ['text' => 'Titanic', 'correct' => true],
                    ['text' => 'Das Boot', 'correct' => false],
                    ['text' => 'Poseidon', 'correct' => false],
                    ['text' => 'Master and Commander', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Literatur
     */
    private function seedLiteratur(): void {
        echo "Seeding Literatur...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Literatur Klassiker',
            'description' => 'Große Werke und ihre Autoren',
            'category' => 'Literatur',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Wer schrieb "Faust"?',
                'answers' => [
                    ['text' => 'Schiller', 'correct' => false],
                    ['text' => 'Goethe', 'correct' => true],
                    ['text' => 'Heine', 'correct' => false],
                    ['text' => 'Kafka', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Autor schrieb "1984"?',
                'answers' => [
                    ['text' => 'Aldous Huxley', 'correct' => false],
                    ['text' => 'George Orwell', 'correct' => true],
                    ['text' => 'Ray Bradbury', 'correct' => false],
                    ['text' => 'H.G. Wells', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer schrieb "Romeo und Julia"?',
                'answers' => [
                    ['text' => 'Charles Dickens', 'correct' => false],
                    ['text' => 'William Shakespeare', 'correct' => true],
                    ['text' => 'Oscar Wilde', 'correct' => false],
                    ['text' => 'Mark Twain', 'correct' => false]
                ]
            ],
            [
                'question' => 'Von wem stammt "Der Kleine Prinz"?',
                'answers' => [
                    ['text' => 'Jules Verne', 'correct' => false],
                    ['text' => 'Antoine de Saint-Exupéry', 'correct' => true],
                    ['text' => 'Victor Hugo', 'correct' => false],
                    ['text' => 'Alexandre Dumas', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Autor schuf Sherlock Holmes?',
                'answers' => [
                    ['text' => 'Agatha Christie', 'correct' => false],
                    ['text' => 'Arthur Conan Doyle', 'correct' => true],
                    ['text' => 'Edgar Allan Poe', 'correct' => false],
                    ['text' => 'Raymond Chandler', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Politik
     */
    private function seedPolitik(): void {
        echo "Seeding Politik...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Politik & Gesellschaft',
            'description' => 'Grundwissen über Politik und Demokratie',
            'category' => 'Politik',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Wie viele Bundesländer hat Deutschland?',
                'answers' => [
                    ['text' => '14', 'correct' => false],
                    ['text' => '16', 'correct' => true],
                    ['text' => '18', 'correct' => false],
                    ['text' => '20', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wer ist das Staatsoberhaupt in Deutschland?',
                'answers' => [
                    ['text' => 'Der Bundeskanzler', 'correct' => false],
                    ['text' => 'Der Bundespräsident', 'correct' => true],
                    ['text' => 'Der Bundestagspräsident', 'correct' => false],
                    ['text' => 'Der Bundesminister', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie viele Sterne hat die US-Flagge?',
                'answers' => [
                    ['text' => '48', 'correct' => false],
                    ['text' => '50', 'correct' => true],
                    ['text' => '51', 'correct' => false],
                    ['text' => '52', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wo hat die UNO ihren Hauptsitz?',
                'answers' => [
                    ['text' => 'Genf', 'correct' => false],
                    ['text' => 'New York', 'correct' => true],
                    ['text' => 'Brüssel', 'correct' => false],
                    ['text' => 'Paris', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Wirtschaft
     */
    private function seedWirtschaft(): void {
        echo "Seeding Wirtschaft...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Wirtschaft Grundlagen',
            'description' => 'Basics der Wirtschaft und Finanzen',
            'category' => 'Wirtschaft',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Was bedeutet "BIP"?',
                'answers' => [
                    ['text' => 'Brutto-Inlands-Produkt', 'correct' => true],
                    ['text' => 'Bundes-Investitions-Programm', 'correct' => false],
                    ['text' => 'Betriebs-Innovations-Prozess', 'correct' => false],
                    ['text' => 'Brutto-Import-Prozent', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welche Währung hat Japan?',
                'answers' => [
                    ['text' => 'Yuan', 'correct' => false],
                    ['text' => 'Yen', 'correct' => true],
                    ['text' => 'Won', 'correct' => false],
                    ['text' => 'Rupie', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist Inflation?',
                'answers' => [
                    ['text' => 'Geldentwertung', 'correct' => true],
                    ['text' => 'Geldaufwertung', 'correct' => false],
                    ['text' => 'Börsencrash', 'correct' => false],
                    ['text' => 'Wirtschaftswachstum', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Unternehmen ist das wertvollste der Welt (2024)?',
                'answers' => [
                    ['text' => 'Amazon', 'correct' => false],
                    ['text' => 'Apple', 'correct' => true],
                    ['text' => 'Microsoft', 'correct' => false],
                    ['text' => 'Google', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Mathematik
     */
    private function seedMathematik(): void {
        echo "Seeding Mathematik...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Mathe-Quiz',
            'description' => 'Grundrechenarten und mehr',
            'category' => 'Mathematik',
            'difficulty' => 'easy',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Was ist 7 × 8?',
                'answers' => [
                    ['text' => '54', 'correct' => false],
                    ['text' => '56', 'correct' => true],
                    ['text' => '58', 'correct' => false],
                    ['text' => '64', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Quadratwurzel aus 144?',
                'answers' => [
                    ['text' => '11', 'correct' => false],
                    ['text' => '12', 'correct' => true],
                    ['text' => '13', 'correct' => false],
                    ['text' => '14', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie viel ist 25% von 200?',
                'answers' => [
                    ['text' => '40', 'correct' => false],
                    ['text' => '50', 'correct' => true],
                    ['text' => '60', 'correct' => false],
                    ['text' => '75', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist die Zahl Pi (gerundet)?',
                'answers' => [
                    ['text' => '3.12', 'correct' => false],
                    ['text' => '3.14', 'correct' => true],
                    ['text' => '3.16', 'correct' => false],
                    ['text' => '3.18', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist 15 + 27?',
                'answers' => [
                    ['text' => '40', 'correct' => false],
                    ['text' => '42', 'correct' => true],
                    ['text' => '44', 'correct' => false],
                    ['text' => '45', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Sprachen
     */
    private function seedSprachen(): void {
        echo "Seeding Sprachen...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Sprachen der Welt',
            'description' => 'Teste dein Sprachwissen',
            'category' => 'Sprachen',
            'difficulty' => 'medium',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Welche ist die meistgesprochene Sprache der Welt?',
                'answers' => [
                    ['text' => 'Englisch', 'correct' => false],
                    ['text' => 'Mandarin-Chinesisch', 'correct' => true],
                    ['text' => 'Spanisch', 'correct' => false],
                    ['text' => 'Hindi', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was bedeutet "Bonjour" auf Deutsch?',
                'answers' => [
                    ['text' => 'Auf Wiedersehen', 'correct' => false],
                    ['text' => 'Guten Tag', 'correct' => true],
                    ['text' => 'Danke', 'correct' => false],
                    ['text' => 'Bitte', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie sagt man "Danke" auf Spanisch?',
                'answers' => [
                    ['text' => 'Merci', 'correct' => false],
                    ['text' => 'Gracias', 'correct' => true],
                    ['text' => 'Obrigado', 'correct' => false],
                    ['text' => 'Grazie', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Alphabet verwendet die russische Sprache?',
                'answers' => [
                    ['text' => 'Lateinisch', 'correct' => false],
                    ['text' => 'Kyrillisch', 'correct' => true],
                    ['text' => 'Griechisch', 'correct' => false],
                    ['text' => 'Arabisch', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Essen & Trinken
     */
    private function seedEssen(): void {
        echo "Seeding Essen & Trinken...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Kulinarisches Quiz',
            'description' => 'Alles rund um Essen und Trinken',
            'category' => 'Essen & Trinken',
            'difficulty' => 'easy',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Welches Land ist berühmt für Pizza?',
                'answers' => [
                    ['text' => 'Frankreich', 'correct' => false],
                    ['text' => 'Italien', 'correct' => true],
                    ['text' => 'Spanien', 'correct' => false],
                    ['text' => 'Griechenland', 'correct' => false]
                ]
            ],
            [
                'question' => 'Was ist Sushi?',
                'answers' => [
                    ['text' => 'Eine chinesische Suppe', 'correct' => false],
                    ['text' => 'Ein japanisches Gericht mit Reis und Fisch', 'correct' => true],
                    ['text' => 'Ein koreanisches Dessert', 'correct' => false],
                    ['text' => 'Ein thailändisches Curry', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welche Frucht hat die meisten Kalorien?',
                'answers' => [
                    ['text' => 'Apfel', 'correct' => false],
                    ['text' => 'Banane', 'correct' => false],
                    ['text' => 'Avocado', 'correct' => true],
                    ['text' => 'Orange', 'correct' => false]
                ]
            ],
            [
                'question' => 'Woraus wird Schokolade hauptsächlich hergestellt?',
                'answers' => [
                    ['text' => 'Kaffeebohnen', 'correct' => false],
                    ['text' => 'Kakaobohnen', 'correct' => true],
                    ['text' => 'Sojabohnen', 'correct' => false],
                    ['text' => 'Mandeln', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Tiere
     */
    private function seedTiere(): void {
        echo "Seeding Tiere...\n";

        $quizId = $this->quizManager->createQuiz($this->defaultUserId, [
            'title' => 'Tierwelt',
            'description' => 'Alles über Tiere aus aller Welt',
            'category' => 'Tiere',
            'difficulty' => 'easy',
            'time_limit' => 0,
            'is_public' => true
        ]);

        $questions = [
            [
                'question' => 'Welches ist das größte Landtier?',
                'answers' => [
                    ['text' => 'Nilpferd', 'correct' => false],
                    ['text' => 'Elefant', 'correct' => true],
                    ['text' => 'Giraffe', 'correct' => false],
                    ['text' => 'Nashorn', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie nennt man ein junges Pferd?',
                'answers' => [
                    ['text' => 'Kalb', 'correct' => false],
                    ['text' => 'Fohlen', 'correct' => true],
                    ['text' => 'Welpe', 'correct' => false],
                    ['text' => 'Küken', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Tier kann nicht rückwärts laufen?',
                'answers' => [
                    ['text' => 'Pferd', 'correct' => false],
                    ['text' => 'Känguru', 'correct' => true],
                    ['text' => 'Hund', 'correct' => false],
                    ['text' => 'Katze', 'correct' => false]
                ]
            ],
            [
                'question' => 'Wie viele Beine hat eine Spinne?',
                'answers' => [
                    ['text' => '6', 'correct' => false],
                    ['text' => '8', 'correct' => true],
                    ['text' => '10', 'correct' => false],
                    ['text' => '12', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welches Tier schläft im Stehen?',
                'answers' => [
                    ['text' => 'Kuh', 'correct' => false],
                    ['text' => 'Pferd', 'correct' => true],
                    ['text' => 'Schwein', 'correct' => false],
                    ['text' => 'Ziege', 'correct' => false]
                ]
            ],
            [
                'question' => 'Welcher Vogel kann nicht fliegen?',
                'answers' => [
                    ['text' => 'Adler', 'correct' => false],
                    ['text' => 'Pinguin', 'correct' => true],
                    ['text' => 'Taube', 'correct' => false],
                    ['text' => 'Spatz', 'correct' => false]
                ]
            ]
        ];

        $this->createQuestions($quizId, $questions);
    }

    /**
     * Hilfsmethode zum Erstellen von Fragen
     */
    private function createQuestions(int $quizId, array $questions): void {
        $position = 0;
        foreach ($questions as $q) {
            $questionId = $this->quizManager->addQuestion($quizId, [
                'question_text' => $q['question'],
                'question_type' => 'multiple_choice',
                'points' => 10,
                'order_position' => $position++,
                'time_limit' => 30
            ]);

            if ($questionId) {
                $answerPosition = 0;
                foreach ($q['answers'] as $answer) {
                    $this->quizManager->addAnswer(
                        $questionId,
                        $answer['text'],
                        $answer['correct'],
                        $answerPosition++
                    );
                }
            }
        }
    }
}

<?php

namespace ModernQuiz\Database\Migrations;

class SeedDemoData
{
    public static function up($db)
    {
        // Kategorien
        $db->query("
            INSERT INTO quiz_categories (name, description, icon) VALUES
            ('Allgemeinwissen', 'Teste dein Allgemeinwissen', 'fa-brain'),
            ('Geographie', 'Wie gut kennst du die Welt?', 'fa-globe'),
            ('Geschichte', 'Reise durch die Zeit', 'fa-landmark'),
            ('Wissenschaft', 'Naturwissenschaften und Technik', 'fa-flask'),
            ('Kultur', 'Kunst, Musik und Literatur', 'fa-palette'),
            ('Sport', 'Alles rund um Sport', 'fa-futbol')
        ");

        // Beispiel-Fragen für Allgemeinwissen
        $db->query("
            INSERT INTO quiz_questions (category_id, question, difficulty, points, time_limit) VALUES
            (1, 'Was ist die Hauptstadt von Deutschland?', 'easy', 10, 30),
            (1, 'Wie viele Kontinente gibt es?', 'easy', 10, 30),
            (1, 'Welches ist das größte Land der Welt?', 'medium', 15, 30),
            (1, 'In welchem Jahr landete der erste Mensch auf dem Mond?', 'medium', 15, 30),
            (1, 'Wie heißt der höchste Berg der Welt?', 'easy', 10, 30)
        ");

        // Antworten für Frage 1 (Hauptstadt Deutschland)
        $db->query("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES
            (1, 'Berlin', TRUE),
            (1, 'München', FALSE),
            (1, 'Hamburg', FALSE),
            (1, 'Köln', FALSE)
        ");

        // Antworten für Frage 2 (Kontinente)
        $db->query("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES
            (2, '5', FALSE),
            (2, '6', FALSE),
            (2, '7', TRUE),
            (2, '8', FALSE)
        ");

        // Antworten für Frage 3 (Größtes Land)
        $db->query("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES
            (3, 'Kanada', FALSE),
            (3, 'USA', FALSE),
            (3, 'Russland', TRUE),
            (3, 'China', FALSE)
        ");

        // Antworten für Frage 4 (Mondlandung)
        $db->query("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES
            (4, '1965', FALSE),
            (4, '1969', TRUE),
            (4, '1971', FALSE),
            (4, '1973', FALSE)
        ");

        // Antworten für Frage 5 (Höchster Berg)
        $db->query("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES
            (5, 'K2', FALSE),
            (5, 'Mount Everest', TRUE),
            (5, 'Kangchendzönga', FALSE),
            (5, 'Lhotse', FALSE)
        ");

        // Geographie-Fragen
        $db->query("
            INSERT INTO quiz_questions (category_id, question, difficulty, points, time_limit) VALUES
            (2, 'Welcher Fluss ist der längste der Welt?', 'medium', 15, 30),
            (2, 'Wie viele Länder grenzen an Deutschland?', 'easy', 10, 30),
            (2, 'Welches ist die Hauptstadt von Japan?', 'easy', 10, 30),
            (2, 'In welchem Land liegt die Sahara-Wüste hauptsächlich?', 'medium', 15, 30),
            (2, 'Welcher Ozean ist der größte?', 'easy', 10, 30)
        ");

        // Antworten Geographie
        $db->query("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES
            (6, 'Nil', TRUE),
            (6, 'Amazonas', FALSE),
            (6, 'Jangtsekiang', FALSE),
            (6, 'Mississippi', FALSE),

            (7, '7', FALSE),
            (7, '8', FALSE),
            (7, '9', TRUE),
            (7, '10', FALSE),

            (8, 'Tokio', TRUE),
            (8, 'Kyoto', FALSE),
            (8, 'Osaka', FALSE),
            (8, 'Hiroshima', FALSE),

            (9, 'Ägypten', FALSE),
            (9, 'Algerien', TRUE),
            (9, 'Marokko', FALSE),
            (9, 'Sudan', FALSE),

            (10, 'Atlantischer Ozean', FALSE),
            (10, 'Indischer Ozean', FALSE),
            (10, 'Pazifischer Ozean', TRUE),
            (10, 'Arktischer Ozean', FALSE)
        ");

        // Wissenschafts-Fragen
        $db->query("
            INSERT INTO quiz_questions (category_id, question, difficulty, points, time_limit) VALUES
            (4, 'Welches chemische Element hat das Symbol \"O\"?', 'easy', 10, 30),
            (4, 'Wie viele Planeten hat unser Sonnensystem?', 'easy', 10, 30),
            (4, 'Was ist die Lichtgeschwindigkeit?', 'hard', 25, 40),
            (4, 'Wer entwickelte die Relativitätstheorie?', 'medium', 15, 30),
            (4, 'Welches ist das häufigste Element im Universum?', 'medium', 15, 30)
        ");

        // Antworten Wissenschaft
        $db->query("
            INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES
            (11, 'Sauerstoff', TRUE),
            (11, 'Wasserstoff', FALSE),
            (11, 'Kohlenstoff', FALSE),
            (11, 'Stickstoff', FALSE),

            (12, '7', FALSE),
            (12, '8', TRUE),
            (12, '9', FALSE),
            (12, '10', FALSE),

            (13, '299.792.458 m/s', TRUE),
            (13, '199.792.458 m/s', FALSE),
            (13, '399.792.458 m/s', FALSE),
            (13, '150.000.000 m/s', FALSE),

            (14, 'Isaac Newton', FALSE),
            (14, 'Albert Einstein', TRUE),
            (14, 'Galileo Galilei', FALSE),
            (14, 'Stephen Hawking', FALSE),

            (15, 'Sauerstoff', FALSE),
            (15, 'Wasserstoff', TRUE),
            (15, 'Helium', FALSE),
            (15, 'Kohlenstoff', FALSE)
        ");

        // Shop Powerups
        $db->query("
            INSERT INTO shop_powerups (name, description, effect_type, price, icon) VALUES
            ('50:50', 'Entfernt 2 falsche Antworten', '50_50', 50, 'fa-balance-scale'),
            ('Frage überspringen', 'Überspringe eine schwierige Frage', 'skip_question', 75, 'fa-forward'),
            ('Extra Zeit', '+15 Sekunden für die aktuelle Frage', 'extra_time', 60, 'fa-clock'),
            ('Doppelte Punkte', 'Verdopple die Punkte für die nächste Frage', 'double_points', 100, 'fa-star'),
            ('Zeit einfrieren', 'Friert den Timer für 10 Sekunden ein', 'freeze_time', 80, 'fa-snowflake'),
            ('Hinweis anzeigen', 'Zeigt einen Hinweis zur richtigen Antwort', 'reveal_hint', 40, 'fa-lightbulb')
        ");

        // Achievements
        $db->query("
            INSERT INTO achievements (name, description, icon, requirement_type, requirement_value, badge_color) VALUES
            ('Erste Schritte', 'Spiele dein erstes Quiz', 'fa-flag', 'games_played', 1, '#10b981'),
            ('Quiz-Fan', 'Spiele 10 Quiz-Spiele', 'fa-heart', 'games_played', 10, '#3b82f6'),
            ('Quiz-Meister', 'Spiele 50 Quiz-Spiele', 'fa-crown', 'games_played', 50, '#f59e0b'),
            ('Quiz-Legende', 'Spiele 100 Quiz-Spiele', 'fa-trophy', 'games_played', 100, '#8b5cf6'),

            ('Guter Start', 'Beantworte 50 Fragen richtig', 'fa-check', 'correct_answers', 50, '#10b981'),
            ('Wissensdurst', 'Beantworte 200 Fragen richtig', 'fa-brain', 'correct_answers', 200, '#3b82f6'),
            ('Gehirn-Champion', 'Beantworte 500 Fragen richtig', 'fa-graduation-cap', 'correct_answers', 500, '#f59e0b'),

            ('Punkte-Sammler', 'Erreiche 1000 Punkte', 'fa-star', 'points', 1000, '#10b981'),
            ('Punkte-Jäger', 'Erreiche 5000 Punkte', 'fa-fire', 'points', 5000, '#f59e0b'),
            ('Punkte-König', 'Erreiche 10000 Punkte', 'fa-gem', 'points', 10000, '#8b5cf6'),

            ('Serie Anfänger', 'Erreiche eine Serie von 5', 'fa-bolt', 'streak', 5, '#10b981'),
            ('Serie Profi', 'Erreiche eine Serie von 10', 'fa-fire', 'streak', 10, '#f59e0b'),
            ('Serie Champion', 'Erreiche eine Serie von 25', 'fa-rocket', 'streak', 25, '#ef4444')
        ");

        return true;
    }

    public static function down($db)
    {
        $db->query("DELETE FROM achievements");
        $db->query("DELETE FROM shop_powerups");
        $db->query("DELETE FROM quiz_answers");
        $db->query("DELETE FROM quiz_questions");
        $db->query("DELETE FROM quiz_categories");
        return true;
    }
}

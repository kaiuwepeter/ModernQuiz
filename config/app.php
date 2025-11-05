<?php

return [
    'name' => 'ModernQuiz',
    'version' => '1.0.0',
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'debug' => getenv('APP_DEBUG') === 'true',
    'timezone' => 'Europe/Berlin',

    // Session
    'session_lifetime' => 7200, // 2 Stunden
    'session_name' => 'modernquiz_session',

    // Quiz Settings
    'quiz' => [
        'default_time_limit' => 30, // Sekunden
        'points_per_question' => 10,
        'time_bonus_multiplier' => 0.1,
        'streak_bonus' => 5,
    ],

    // Jackpot Settings
    'jackpot' => [
        'enabled' => true,
        'check_on_correct_answer' => true,
    ],

    // Shop Settings
    'shop' => [
        'starting_coins' => 100,
        'coins_per_correct_answer' => 5,
    ],
];

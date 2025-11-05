<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../vendor/autoload.php';

use ModernQuiz\Core\Database;
use ModernQuiz\Modules\Quiz\QuizEngine;
use ModernQuiz\Modules\Shop\ShopSystem;
use ModernQuiz\Modules\Jackpot\JackpotSystem;
use ModernQuiz\Modules\Leaderboard\LeaderboardSystem;

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$path = trim($path, '/');
$segments = explode('/', $path);

// Initialisiere Systeme
$quizEngine = new QuizEngine();
$shopSystem = new ShopSystem();
$jackpotSystem = new JackpotSystem();
$leaderboardSystem = new LeaderboardSystem();

// Router
try {
    $response = [];

    // Quiz-Endpunkte
    if ($segments[0] === 'quiz') {
        if ($method === 'POST' && $segments[1] === 'start') {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $quizEngine->startSession($data['user_id'], $data['category_id'] ?? null);
        } elseif ($method === 'GET' && $segments[1] === 'question') {
            $categoryId = $_GET['category_id'] ?? null;
            $excludeIds = isset($_GET['exclude']) ? explode(',', $_GET['exclude']) : [];
            $response = $quizEngine->getRandomQuestion($categoryId, $excludeIds);
        } elseif ($method === 'POST' && $segments[1] === 'answer') {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $quizEngine->submitAnswer(
                $data['session_id'],
                $data['question_id'],
                $data['answer_id'],
                $data['time_taken'],
                $data['powerup_used'] ?? null
            );

            // Prüfe Jackpot bei richtiger Antwort
            if ($response['correct']) {
                $session = $quizEngine->getSession($data['session_id']);
                $jackpotResults = $jackpotSystem->incrementJackpots(
                    $session['user_id'],
                    $data['question_id'],
                    $data['session_id']
                );
                $response['jackpots'] = $jackpotResults;
            }
        } elseif ($method === 'POST' && $segments[1] === 'end') {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $quizEngine->endSession($data['session_id']);
        } elseif ($method === 'GET' && $segments[1] === 'categories') {
            $response = $quizEngine->getCategories();
        }
    }

    // Shop-Endpunkte
    elseif ($segments[0] === 'shop') {
        if ($method === 'GET' && $segments[1] === 'powerups') {
            $response = $shopSystem->getPowerups();
        } elseif ($method === 'GET' && $segments[1] === 'inventory') {
            $userId = $_GET['user_id'];
            $response = $shopSystem->getUserInventory($userId);
        } elseif ($method === 'POST' && $segments[1] === 'purchase') {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $shopSystem->purchasePowerup(
                $data['user_id'],
                $data['powerup_id'],
                $data['quantity'] ?? 1
            );
        } elseif ($method === 'POST' && $segments[1] === 'use') {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $shopSystem->usePowerup($data['user_id'], $data['powerup_id']);
        }
    }

    // Jackpot-Endpunkte
    elseif ($segments[0] === 'jackpots') {
        if ($method === 'GET') {
            if (isset($segments[1]) && $segments[1] === 'winners') {
                $limit = $_GET['limit'] ?? 10;
                $response = $jackpotSystem->getRecentWinners($limit);
            } else {
                $response = $jackpotSystem->getAllJackpots();
            }
        }
    }

    // Leaderboard-Endpunkte
    elseif ($segments[0] === 'leaderboard') {
        if ($method === 'GET') {
            if (isset($segments[1])) {
                switch ($segments[1]) {
                    case 'daily':
                        $limit = $_GET['limit'] ?? 50;
                        $response = $leaderboardSystem->getDailyLeaderboard($limit);
                        break;
                    case 'weekly':
                        $limit = $_GET['limit'] ?? 50;
                        $response = $leaderboardSystem->getWeeklyLeaderboard($limit);
                        break;
                    case 'category':
                        $categoryId = $_GET['category_id'];
                        $limit = $_GET['limit'] ?? 50;
                        $response = $leaderboardSystem->getLeaderboardByCategory($categoryId, $limit);
                        break;
                    case 'user':
                        $userId = $_GET['user_id'];
                        $response = $leaderboardSystem->getUserRanking($userId);
                        break;
                }
            } else {
                $limit = $_GET['limit'] ?? 100;
                $response = $leaderboardSystem->getTopPlayers($limit);
            }
        }
    }

    // User-Stats
    elseif ($segments[0] === 'user' && $segments[1] === 'stats') {
        if ($method === 'GET') {
            $userId = $_GET['user_id'];
            $response = $leaderboardSystem->getUserStats($userId);
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

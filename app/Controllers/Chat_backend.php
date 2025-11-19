<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/ChatController.php';
    require_once __DIR__ . '/../../config/config.php'; 

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if ($message === '') {
            echo json_encode(['reply' => 'Meldingen kan ikke være tom.']);
            exit;
        }

        $controller = new ChatController();
        $reply = $controller->handleUserMessage($message);

        echo json_encode(['reply' => $reply]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Kun POST er tillatt.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

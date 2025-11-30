<?php
require_once __DIR__ . '/ChatController.php';
require_once __DIR__ . '/../../config/config.php'; 

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            echo json_encode(['error' => 'Ugyldig JSON i forespørsel.']);
            exit;
        }

        $message = trim($input['message'] ?? '');

        if ($message === '') {
            echo json_encode(['reply' => 'Meldingen kan ikke være tom.']);
            exit;
        }

        $controller = new ChatController();
        $reply = $controller->handleUserMessage($message);

        echo json_encode(['reply' => $reply]);
        exit;
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Kun POST er tillatt.']);
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
}

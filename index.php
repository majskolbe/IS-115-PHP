<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/ChatController.php';

$controller = new ChatController();

require_once __DIR__ . '/app/Views/Chatview.php';



?>
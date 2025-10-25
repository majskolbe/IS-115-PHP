<?php
require_once __DIR__ . '/ProductController.php';
require_once __DIR__ . '/ChatController.php';

class MainController {
    public function handleRequest() {
        if (isset($_REQUEST['driver']) && $_REQUEST['driver'] === 'web') {
            $chat = new ChatController();
            $chat->listen();
        } else {
            $product = new ProductController();
            $product->search();
        }
    }
}
?>
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Controllers/MainController.php';

$main = new MainController();
$main->handleRequest();
?>
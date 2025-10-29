<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Controllers/ProductController.php';

$controller = new ProductController();
$controller->search();
?>
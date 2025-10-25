<?php

require_once __DIR__ . '/../Models/KassalappAPI.php';
require_once __DIR__ . '/../Models/StrekkodeScanner.php';
require_once __DIR__ . '/../../config/config.php';


class ProductController {
    private $scanner;

    public function __construct() {
        $api = new KassalappAPI(KASSALAPP_API_KEY);
        $this->scanner = new StrekkkodeScanner($api);
    }

    public function search() {
        $resultat = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ean'])) {
            $ean = trim($_POST['ean']);
            $resultat = $this->scanner->skannProdukt($ean);
        }

        require __DIR__ . '/../Views/searchForm.php';
        if ($resultat) {
            require __DIR__ . '/../Views/searchResult.php';
        }
    }
}

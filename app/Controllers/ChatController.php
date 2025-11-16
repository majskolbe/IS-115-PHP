<?php
require_once __DIR__ . '/../Models/ChatModel.php';
require_once __DIR__ . '/../Models/KassalappAPI.php';
require_once __DIR__ . '/../Models/StrekkodeScanner.php';


class ChatController {
    private $model;
    private $scanner;

    public function __construct() {
        $this->model = new ChatModel();

        // Opprett API-klient og scanner
        $api = new KassalappAPI(KASSALAPP_API_KEY); 
        $this->scanner = new StrekkodeScanner($api);
    }

    public function handleUserMessage($message) {
        $ean = $this->extractEAN($message);
        $reply = $this->model->getHintReply($message);

        if ($ean) {
            $produkt = $this->scanner->skannProdukt($ean);
            if ($produkt && !empty($produkt['priser'])) {
                $billigst = $this->scanner->finnBilligstePris($produkt['priser']);
                $reply .= "<br>Den billigste prisen for {$produkt['navn']} er {$billigst['pris']} kr hos {$billigst['butikk']}.";
            } else {
                $reply .= "<br>Fant ingen prisinformasjon for EAN $ean.";
            }
        }

        return $reply ?: "Beklager, jeg forstod ikke spørsmålet.";
    }

    private function extractEAN($text) {
        return preg_match('/\b\d{13}\b/', $text, $matches) ? $matches[0] : null;
    }
}

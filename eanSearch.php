<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once 'inc/KassalappApi_inc.php';

$api = new KassalappAPI(KASSALAPP_API_KEY);

//finner priser fra butikker
class StrekkkodeScanner {
    private $api;

    public function __construct(KassalappAPI $api) {
        $this->api = $api;
    }

    public function skannProdukt(string $ean) {
        try {
            $produkt = $this->api->request("products/ean/{$ean}");

            if (empty($produkt['data'])) {
                return null;
            }

            return $this->formaterProduktInfo($produkt['data']);
        } catch (Exception $e) {
            return null;
        }
    }

    private function formaterProduktInfo($data) {
        // Sjekk at products finnes
        if (empty($data['products']) || !is_array($data['products'])) {
            return null;
        }

        $info = [
            'ean' => $data['ean'] ?? null,
            'navn' => $data['products'][0]['name'] ?? 'Ukjent produkt',
            'merke' => $data['products'][0]['brand'] ?? 'Ukjent merke',
            'beskrivelse' => $data['products'][0]['description'] ?? 'Ingen beskrivelse',
            'bilde' => $data['products'][0]['image'] ?? null,
            'priser' => []
        ];

        // Hent alle butikker og priser
        foreach ($data['products'] as $produkt) {
        $pris = $produkt['current_price']['price'] ?? null;
        $butikk = $produkt['store']['name'] ?? null;

        // Hopp over produkter uten pris eller butikk
        if (empty($pris) || empty($butikk)) {
            continue;
        }

        $info['priser'][] = [
            'butikk' => $butikk,
            'pris' => $pris,
            'url' => $produkt['url'] ?? null,
        ];
        }
        
        // Sorter etter pris
        usort($info['priser'], fn($a, $b) => $a['pris'] <=> $b['pris']);

        return $info;

        
    }
}


?>
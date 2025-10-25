<?php
require_once __DIR__ . '/KassalappAPI.php';

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

        foreach ($data['products'] as $produkt) {
            $pris = $produkt['current_price']['price'] ?? null;
            $butikk = $produkt['store']['name'] ?? null;

            if (empty($pris) || empty($butikk)) continue;

            $info['priser'][] = [
                'butikk' => $butikk,
                'pris' => $pris,
                'url' => $produkt['url'] ?? null,
            ];
        }

        usort($info['priser'], fn($a, $b) => $a['pris'] <=> $b['pris']);

        return $info;
    }
}

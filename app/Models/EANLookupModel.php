<?php
// inkluderer API klassen
require_once __DIR__ . '/KassalappAPIModel.php';

/* Klasse med ansvar for å hente og formatere produktinformasjon basert på EAN
bruker KassalappAPIModel for API-kall, ekstraherer generell produktinfo,
henter og sorterer priser fra ulike butikker
*/
class EANLookupModel {
    private $api;

    // mottar KassalappAPI-instans, og lagrer den
    public function __construct(KassalappAPIModel $api){
        $this->api = $api;
    }

    // henter produktinfo basert på EAN
    public function lookupProductByEAN(string $ean){
        try {
            $product = $this->api->request("products/ean/{$ean}");

            if (empty($product['data'])) {
                return null;
            }

            return $this->formatProductInfo($product['data']);
        } catch (\Throwable $e) {
            // logger feil
            error_log("EAN lookup failed: " . $e->getMessage());
            // returner en trygg verdi til frontend
            return null;
        }
    }

    // henter ut relevant data om produktet
    private function extractGeneralInfo($data){
        try {
            if (empty($data['products']) || !is_array($data['products'])) {
                return null;
            }

            //returnerer ean, navn, merke, bilde og beskrivelse for produkt
            return [
                'ean' => $data['ean'] ?? null,
                'name' => $data['products'][0]['name'] ?? 'Ukjent produkt',
                'brand' => $data['products'][0]['brand'] ?? 'Ukjent merke',
                'image' => $data['products'][0]['image'] ?? null,
                'description' => $data['products'][0]['description'] ?? null
            ];
        } catch (\Throwable $e) {
            error_log("Extract general info failed: " . $e->getMessage());
            return null;
        }
    }

    // henter pris for produktet, returnerer liste med priser
    private function extractPrices($data){
        $prices = [];
        try {
            foreach ($data['products'] as $product) {
                $price = $product['current_price']['price'] ?? null;
                $store = $product['store']['name'] ?? null;

                if (empty($price) || empty($store)) continue;

                $prices[] = [
                    'store' => $store,
                    'price' => $price
                ];
            }
        } catch (\Throwable $e) {
            error_log("Extract prices failed: " . $e->getMessage());
        }
        return $prices;
    }

    // formaterer infoen om produktet til strukturert array
    private function formatProductInfo($data) {
        try {
            $info = $this->extractGeneralInfo($data);
            if ($info === null) return null;

            $info['prices'] = $this->extractPrices($data);
            usort($info['prices'], fn($a, $b) => $a['price'] <=> $b['price']);

            return $info;
        } catch (\Throwable $e) {
            error_log("Format product info failed: " . $e->getMessage());
            return null;
        }
    }

    // returnerer billigste pris
    public function findLowestPrice(array $prices){
        try {
            return $prices[0] ?? null;
        } catch (\Throwable $e) {
            error_log("Find lowest price failed: " . $e->getMessage());
            return null;
        }
    }

    // returnerer alternative priser(alle utenom sen billigste)
    public function getAlternativePrices(array $prices){
        try {
            return count($prices) > 1 ? array_slice($prices, 1) : [];
        } catch (\Throwable $e) {
            error_log("Get alternative prices failed: " . $e->getMessage());
            return [];
        }
    }
}

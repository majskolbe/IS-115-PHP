<?php
/*
Klasse med ansvar for roetningslogikken i chatten
tar imot intent og melding fra chatController og bruker
ChatModel og EANLookupModel til å hente data.

*/
class ChatService {
    private $model;
    private $lookup;

    public function __construct(ChatModel $model, EANLookupModel $lookup) {
        $this->model = $model;
        $this->lookup = $lookup;
    }

    //prosseserer melding basert på intent
    public function processIntent(string $intent, string $message, ?string $ean): array {
        switch ($intent) {
            case 'store_price_lookup':
                return $this->handleStorePriceLookup($message);
            case 'product_description':
                return $this->handleProductDescription($ean);
            case 'ean_lookup':
                return $this->handleEANLookup($ean);
            default:
                return $this->handlePatternOrFallback($message);
        }
    }

    //håndterer oppslag av pris på spesifikk butikk
    private function handleStorePriceLookup(string $message): array {
        $pattern = $this->model->getPatternByIntent('store_price_lookup');
        if (!$pattern) return ['error' => "Beklager, mønsteret for denne intenten finnes ikke."];

        $regex = '/^' . $pattern . '\??$/iu';

        if (preg_match($regex, $message, $matches)) {
            $ean = $matches[1];
            $store = mb_convert_case(trim($matches[3]), MB_CASE_TITLE, 'UTF-8');

            $result = $this->lookup->lookupProductByEAN($ean);

            if (!$result) {
                return ['error' => "Fant ingen prisinformasjon for EAN $ean."];
            }

            return [
                'ean' => $ean,
                'store' => $store,
                'result' => $result
            ];
        }

        return ['error' => "Beklager, jeg kunne ikke forstå butikkspørringen."];
    }

    //henter produktbeskrivelse på en vare basert på EAN
    private function handleProductDescription(?string $ean): array {
        if (!$ean) return ['error' => "Ingen EAN oppgitt i meldingen."];

        $result = $this->lookup->lookupProductByEAN($ean);
        if (!$result) return ['error' => "Beklager, jeg fant ingen beskrivelse for EAN $ean."];

        return [
            'ean' => $ean,
            'result' => $result
        ];
    }

    //henter billigste pris og alternative priser basert på EAN
    private function handleEANLookup(?string $ean): array {
        if (!$ean) return ['error' => "Ingen EAN oppgitt."];

        $result = $this->lookup->lookupProductByEAN($ean);
        if (!$result || empty($result['prices'])) {
            return ['error' => "Fant ingen prisinformasjon for EAN $ean."];
        }

        return [
            'ean' => $ean,
            'name' => $result['name'] ?? 'Ukjent',
            'brand' => $result['brand'] ?? '',
            'image' => $result['image'] ?? null,
            'prices' => $result['prices']
        ];
    }

    //fallback hvis ikke intent ble gjenkjent
    private function handlePatternOrFallback(string $message): array {
        $patternReply = $this->model->getResponseByPattern($message);
        if ($patternReply) return ['reply' => $patternReply];

        return ['reply' => $this->model->getResponseByIntent('unknown') 
            ?? "Beklager, jeg forstod ikke helt. Prøv gjerne å sende en EAN-kode."];
    }
}

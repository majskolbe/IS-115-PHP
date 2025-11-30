<?php
require_once __DIR__ . '/../Models/ChatModel.php';
require_once __DIR__ . '/../Models/KassalappAPIModel.php';
require_once __DIR__ . '/../Models/EANLookupModel.php';

class ChatController {
    private $model;
    private $lookup;

    public function __construct() {
        $this->model = new ChatModel();
        $api = new KassalappAPIModel(KASSALAPP_API_KEY);
        $this->lookup = new EANLookupModel($api);
    }


    public function handleUserMessage($message){
        $message = $this->normalizeMessage($message);
        $intent = $this->model->detectIntentByPattern($message);
        $ean = $this->extractEAN($message);

        switch ($intent){
            case 'store_price_lookup':
                return $this->handleStorePriceLookup($message);
            case 'product_description':
                return $this->handleProductDescription($message);
            case 'ean_lookup':
                return $this->handleEANLookup($ean);
            default:
                return $this->handlePatternOrFallback($message);
        }
    }

    private function normalizeMessage(string $message): string{
        return trim(preg_replace('/\s+/', ' ', $message));
    }

    private function handleStorePriceLookup(string $message): string {
        $pattern = $this->model->getPatternByIntent('store_price_lookup');
        if (!$pattern) return "Beklager, mønsteret for denne intenten finnes ikke.";

        $regex = '/^' . $pattern . '\??$/iu';

        if (preg_match($regex, $message, $matches)) {
            $ean = $matches[1];
            $store = mb_convert_case(trim($matches[3]), MB_CASE_TITLE, 'UTF-8');

            $result = $this->lookup->lookupProductByEAN($ean);

            if ($result && !empty($result['prices'])) {
                foreach ($result['prices'] as $price) {
                    if (strcasecmp($price['store'], $store) === 0) {
                        return "Prisen på EAN $ean hos $store er {$price['price']} kr.";
                    }
                }
                return "Beklager, jeg fant ingen pris for EAN $ean hos $store.";
            }

            return "Beklager, jeg fant ingen prisinformasjon for EAN $ean.";
        }

        return "Beklager, jeg kunne ikke forstå butikkspørringen.";
    }

    private function handleProductDescription(string $message): string {
        $ean = $this->extractEAN($message);
        if (!$ean) return "Ingen EAN oppgitt i meldingen.";

        $result = $this->lookup->lookupProductByEAN($ean);
        if ($result && !empty($result['description'])) {
            $description = htmlspecialchars($result['description']);
            return "<div class=\"product-info\"><p>$description</p></div>";
        }

        return "Beklager, jeg fant ingen beskrivelse for EAN $ean.";
    }



    private function handleEANLookup(?string $ean): string{
        if (!$ean) return "Ingen EAN oppgitt.";

        $result = $this->lookup->lookupProductByEAN($ean);
        if (!$result || empty($result['prices'])) return "Fant ingen prisinformasjon for EAN $ean.";

        $lowestPrice = $this->lookup->findLowestPrice($result['prices']);
        $alternativePrices = $this->lookup->getAlternativePrices($result['prices']);

        $name = htmlspecialchars($result['name'] ?? 'Ukjent');
        $brand = htmlspecialchars($result['brand'] ?? '');
        $image = $result['image'] ?? null;

        $html = "<div class='ean-result'>";
        
        if ($image) $html .= "<img src='$image' alt='Produktbilde' class='product-image' />";

        if ($lowestPrice) {
            $price = htmlspecialchars($lowestPrice['price']);
            $store = htmlspecialchars($lowestPrice['store']);
            $html .= "<p class='price-best'>Billigste pris for $name er <strong>$price kr </strong> hos <strong> $store</strong>.</p>";
        }

        if (!empty($alternativePrices)) {
            $html .= "Her er også prisene på andre butikker:<ul class='price-list'>";
            foreach ($alternativePrices as $price) {
                $store = htmlspecialchars($price['store']);
                $amount = htmlspecialchars($price['price']);
                $html .= "<li><strong> $store </strong> - $amount kr</li>";
            }
            $html .= "</ul>";
        }

        $html .= "</div>";
        return $html;
    }

    private function handlePatternOrFallback(string $message): string {
        $patternReply = $this->model->getResponseByPattern($message);
        if ($patternReply) return $patternReply;

        return $this->model->getResponseByIntent('unknown') ?? "Beklager, jeg forstod ikke helt.";
    }

    private function extractEAN($text) {
        return preg_match('/\b\d{13}\b/', $text, $matches) ? $matches[0] : null;
    }
}

?>
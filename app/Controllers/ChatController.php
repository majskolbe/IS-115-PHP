<?php
require_once __DIR__ . '/../Models/ChatModel.php';
require_once __DIR__ . '/../Models/KassalappAPI.php';
require_once __DIR__ . '/../Models/EANLookupModel.php';

class ChatController {
    private $model;
    private $scanner;

    public function __construct() {
        $this->model = new ChatModel();
        $api = new KassalappAPI(KASSALAPP_API_KEY);
        $this->scanner = new EANLookupModel($api);
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

            $resultat = $this->scanner->skannProdukt($ean);

            if ($resultat && !empty($resultat['priser'])) {
                foreach ($resultat['priser'] as $pris) {
                    if (strcasecmp($pris['butikk'], $store) === 0) {
                        return "Prisen på EAN $ean hos $store er {$pris['pris']} kr.";
                    }
                }
                return "Fant ingen pris for EAN $ean hos $store.";
            }

            return "Fant ingen prisinformasjon for EAN $ean.";
        }

        return "Beklager, jeg kunne ikke forstå butikkspørringen.";
    }

    private function handleProductDescription(string $message): string {
        $ean = $this->extractEAN($message);
        if (!$ean) return "Ingen EAN oppgitt i meldingen.";

        $resultat = $this->scanner->skannProdukt($ean);
        if ($resultat && !empty($resultat['beskrivelse'])) {
            $navn = htmlspecialchars($resultat['navn'] ?? 'Produktet');
            $beskrivelse = htmlspecialchars($resultat['beskrivelse']);
            return "<div class=\"product-info\"><p><strong>$navn</strong></p><p>$beskrivelse</p></div>";
        }

        return "Beklager, jeg fant ingen beskrivelse for EAN $ean.";
    }



    private function handleEANLookup(?string $ean): string{
        if (!$ean) return "Ingen EAN oppgitt.";

        $resultat = $this->scanner->skannProdukt($ean);
        if (!$resultat || empty($resultat['priser'])) return "Fant ingen prisinformasjon for EAN $ean.";

        $billigst = $this->scanner->finnBilligstePris($resultat['priser']);
        $andrePriser = $this->scanner->hentAndrePriser($resultat['priser']);

        $navn = htmlspecialchars($resultat['navn'] ?? 'Ukjent');
        $merke = htmlspecialchars($resultat['merke'] ?? '');
        $bilde = $resultat['bilde'] ?? null;
        $produktTittel = $merke ? "$navn <span class='brand'>($merke)</span>" : $navn;

        $html = "<div class='ean-result'>";
        $html .= "<h3 class='product-title'>$produktTittel</h3>";
        if ($bilde) $html .= "<img src='$bilde' alt='Produktbilde' class='product-image' />";

        if ($billigst) {
            $pris = htmlspecialchars($billigst['pris']);
            $butikk = htmlspecialchars($billigst['butikk']);
            $html .= "<p class='price-best'><strong>Billigste pris:</strong> $pris kr hos $butikk</p>";
        }

        if (!empty($andrePriser)) {
            $html .= "<h4>Andre butikker</h4><ul class='price-list'>";
            foreach ($andrePriser as $pris) {
                $butikk = htmlspecialchars($pris['butikk']);
                $beløp = htmlspecialchars($pris['pris']);
                $html .= "<li>$butikk — $beløp kr</li>";
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

<?php
require_once __DIR__ . '/../Models/ChatModel.php';
require_once __DIR__ . '/../Models/KassalappAPI.php';
require_once __DIR__ . '/../Models/StrekkodeScanner.php';

class ChatController {
    private $model;
    private $scanner;

    public function __construct() {
        $this->model = new ChatModel();
        $api = new KassalappAPI(KASSALAPP_API_KEY);
        $this->scanner = new StrekkodeScanner($api);
    }

    public function handleUserMessage($message) {
        $message = trim(preg_replace('/\s+/', ' ', $message));
        $intent = $this->model->detectIntentByPattern($message);
        $ean = $this->extractEAN($message);
        
        // Butikkspørring
        if ($intent === 'store_price_lookup') {
            if (preg_match('/^hva koster\s+(\d{13})\s+(hos|i|på)\s+([A-Za-zÆØÅæøå\s]+)\??$/iu', $message, $matches)) {
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
        }

        // Produktbeskrivelse 
        if ($intent === 'product_description') {
            $ean = $this->extractEAN($message);
            if ($ean) {
                $resultat = $this->scanner->skannProdukt($ean);
                if ($resultat && !empty($resultat['beskrivelse'])) {
                    $navn = htmlspecialchars($resultat['navn'] ?? 'Produktet');
                    $beskrivelse = htmlspecialchars($resultat['beskrivelse']);
                    return "<div class=\"product-info\"><p><strong>$navn</strong></p><p>$beskrivelse</p></div>";
                }
                return "Beklager, jeg fant ingen beskrivelse for EAN $ean.";
            }
        }

        // EAN-oppslag
        if ($intent === 'ean_lookup' && $ean) {
            $resultat = $this->scanner->skannProdukt($ean);
            if ($resultat && !empty($resultat['priser'])) {
                $billigst = $this->scanner->finnBilligstePris($resultat['priser']);
                $andrePriser = $this->scanner->hentAndrePriser($resultat['priser']);

                $navn = $resultat['navn'] ?? 'Ukjent';
                $merke = $resultat['merke'] ?? '';
                $bilde = $resultat['bilde'] ?? null;

                $produktTekst = $merke ? "$navn ($merke)" : $navn;
                $replyText = "Produkt: $produktTekst";

                if ($bilde) {
                    $replyText .= "\nBilde: $bilde";
                }

                if ($billigst) {
                    $prisVerdi = $billigst['pris'];
                    $butikkNavn = $billigst['butikk'];
                    $replyText .= "\nBilligste pris: $prisVerdi kr hos $butikkNavn";
                } else {
                    $replyText .= "\nIngen priser funnet.";
                }

                if (!empty($andrePriser)) {
                    $replyText .= "\nAndre butikker:";
                    foreach ($andrePriser as $pris) {
                        $replyText .= "\n- {$pris['butikk']}: {$pris['pris']} kr";
                    }
                }

                return $replyText;
            }
            return "Fant ingen prisinformasjon for EAN $ean.";
        }


        // Mønsterbasert svar
        $patternReply = $this->model->getResponseByPattern($message);
        if ($patternReply) return $patternReply;

        // Fallback
        return $this->model->getResponseByIntent('unknown') ?? "Beklager, jeg forstod ikke helt.";
    }

    private function extractEAN($text) {
        return preg_match('/\b\d{13}\b/', $text, $matches) ? $matches[0] : null;
    }
}
?>

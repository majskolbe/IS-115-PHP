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
        // Normaliser input (trim + enkel whitespace)
        $message = trim(preg_replace('/\s+/', ' ', $message));
        $ean = $this->extractEAN($message);

        // 🆕 Direkte: pris for EAN hos spesifikk butikk (fra API) regex matcher innhold i melding
            if (preg_match('/^hva koster\s+(\d{13})\s+(hos|i|på)\s+([A-Za-zÆØÅæøå\s]+)\??$/iu', $message, $matches)) {

            $ean = $matches[1];
            $store = mb_convert_case(trim($matches[3]), MB_CASE_TITLE, 'UTF-8');

            $resultat = $this->scanner->skannProdukt($ean);
            if ($resultat && !empty($resultat['priser'])) {
                foreach ($resultat['priser'] as $pris) {
                    // Sammenlign butikknavn uavhengig av store/små bokstaver
                    if (strcasecmp($pris['butikk'], $store) === 0) {
                        return "Prisen på EAN $ean hos $store er {$pris['pris']} kr.";
                    }
                }
                return "Fant ingen pris for EAN $ean hos $store.";
            }
            return "Fant ingen prisinformasjon for EAN $ean.";
        }

        // 📦 Standardflyt: EAN-oppslag via Kassalapp + vis billigste + andre butikker
        if ($ean) {
            $resultat = $this->scanner->skannProdukt($ean);
            if ($resultat && !empty($resultat['priser'])) {
                $billigst = $this->scanner->finnBilligstePris($resultat['priser']);
                $andrePriser = $this->scanner->hentAndrePriser($resultat['priser']);

                $navn = htmlspecialchars($resultat['navn'] ?? 'Ukjent');
                $merke = htmlspecialchars($resultat['merke'] ?? '');
                $bilde = !empty($resultat['bilde']) ? htmlspecialchars($resultat['bilde']) : null;

                $reply = '<div class="result"><h3>' . $navn . ' (' . $merke . ')</h3>';

                if ($bilde) {
                    $reply .= '<img src="' . $bilde . '" alt="Produktbilde" style="max-width: 150px;"><br>';
                }

                if ($billigst) {
                    $reply .= '<p>Billigste pris: <b>' . htmlspecialchars($billigst['pris']) . ' kr</b> hos <b>' . htmlspecialchars($billigst['butikk']) . '</b></p>';
                } else {
                    $reply .= '<p>Ingen priser funnet.</p>';
                }

                $reply .= '</div>';

                if (!empty($andrePriser)) {
                    $reply .= '<h4>Andre butikker:</h4><ul>';
                    foreach ($andrePriser as $pris) {
                        $butikk = htmlspecialchars($pris['butikk']);
                        $reply .= '<li>' . $butikk . ': <b>' . htmlspecialchars($pris['pris']) . ' kr</b></li>';
                    }
                    $reply .= '</ul>';
                }

                return $reply;
            }
            return "Fant ingen prisinformasjon for EAN $ean.";
        }

        return "Beklager, jeg forstod ikke spørsmålet.";
    }

    private function extractEAN($text) {
        return preg_match('/\b\d{13}\b/', $text, $matches) ? $matches[0] : null;
    }
}

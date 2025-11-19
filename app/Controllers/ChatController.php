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
        $intent = $this->detectIntent($message);   // <-- legg til her
        $ean = $this->extractEAN($message);
        $reply = $this->model->getHintReply($message);
        switch ($intent) {
            case 'greeting':
                return "Hei! Send meg en EAN-kode så finner jeg den billigste prisen for deg.";
            case 'thanks':
                return "Bare hyggelig! Si ifra hvis du trenger mer hjelp.";
            case 'capabilities':
                return "Jeg kan finne priser på matvarer basert på EAN-koder og vise hvor du sparer mest. Prøv å sende en EAN-kode!";
            case 'ean_lookup':
                if ($ean) {
                    $resultat = $this->scanner->skannProdukt($ean);
                    if ($resultat && !empty($resultat['priser'])) {
                        $billigst = $this->scanner->finnBilligstePris($resultat['priser']);
                        $andrePriser = $this->scanner->hentAndrePriser($resultat['priser']);

                        $navn = htmlspecialchars($resultat['navn'] ?? 'Ukjent');
                        $merke = htmlspecialchars($resultat['merke'] ?? '');
                        $bilde = !empty($resultat['bilde']) ? htmlspecialchars($resultat['bilde']) : null;

                        // Start et mer naturlig, brukervennlig svar
                        $replyHtml = '<div class="result"><h3>' . $navn;
                        if ($merke) $replyHtml .= ' (' . $merke . ')';
                        $replyHtml .= '</h3>';

                        if ($bilde) {
                            $replyHtml .= '<img src="' . $bilde . '" alt="Produktbilde" class="product-image"><br>';
                        }

                        // Billigste pris
                        $prisVerdi = htmlspecialchars($billigst['pris']);
                        $butikkNavn = htmlspecialchars($billigst['butikk']);
                        $replyHtml .= '<p>Den laveste prisen jeg finner akkurat nå er <strong>' . $prisVerdi . ' kr</strong> hos <strong>' . $butikkNavn . '</strong>.</p>';

                        // Beregn forskjell til neste billigste om mulig
                        $allPrices = [];
                        foreach ($resultat['priser'] as $p) {
                            $val = floatval($p['pris']);
                            $allPrices[] = $val;
                        }
                        sort($allPrices);
                        if (count($allPrices) > 1) {
                            $next = $allPrices[1];
                            $diff = $next - floatval($prisVerdi);
                            if ($diff > 0) {
                                $replyHtml .= '<p>Det betyr omtrent <strong>' . number_format($diff, 0) . ' kr</strong> i besparelse sammenlignet med neste alternativ.</p>';
                            }
                        }

                        // Andre butikker
                        if (!empty($andrePriser)) {
                            $replyHtml .= '<h4>Andre butikker og priser</h4><ul>';
                            foreach ($andrePriser as $pris) {
                                $butikk = htmlspecialchars($pris['butikk']);
                                $replyHtml .= '<li>' . $butikk . ': <b>' . htmlspecialchars($pris['pris']) . ' kr</b></li>';
                            }
                            $replyHtml .= '</ul>';
                        }

                        $replyHtml .= '<p>Ønsker du at jeg skal søke etter et annet produkt?</p>';
                        $replyHtml .= '</div>';

                        return $replyHtml;
                    }
                    return "Beklager — jeg fant ingen prisinformasjon for EAN $ean.";
                }
                return "Jeg fant ingen gyldig EAN-kode i meldingen. En EAN er vanligvis 13 siffer lang.";
            default:
                return "Beklager, jeg forstod ikke helt. Prøv å sende en EAN-kode eller spør hva jeg kan gjøre.";
        }
    }

    private function extractEAN($text) {
        return preg_match('/\b\d{13}\b/', $text, $matches) ? $matches[0] : null;
    }

    private function detectIntent($message) {
        $msg = strtolower($message);
        if (preg_match('/\b\d{13}\b/', $msg)) return 'ean_lookup';
        if (strpos($msg, 'hei') !== false || strpos($msg, 'hallo') !== false) return 'greeting';
        if (strpos($msg, 'takk') !== false) return 'thanks';
        if (strpos($msg, 'hva kan du gjøre') !== false || strpos($msg, 'hjelp') !== false) return 'capabilities';
        return 'unknown';
    }
}
?>
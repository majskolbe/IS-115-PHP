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
        // Normaliser input
        $message = trim(preg_replace('/\s+/', ' ', $message));
        $intent = $this->detectIntent($message);
        $ean = $this->extractEAN($message);
        $reply = $this->model->getHintReply($message);

        // Sjekk spørsmål om produktbeskrivelse: "hva er [EAN]" eller "fortell om [EAN]"
        if (preg_match('/(hva er|fortell om|fortelle om|beskrivelse på|info om|produktinformasjon om)\s+(\d{13})\??$/iu', $message, $matches)) {
            $ean = $matches[2];
            $resultat = $this->scanner->skannProdukt($ean);
            if ($resultat && !empty($resultat['beskrivelse'])) {
                $navn = htmlspecialchars($resultat['navn'] ?? 'Produktet');
                $beskrivelse = htmlspecialchars($resultat['beskrivelse']);
                return '<div class="product-info"><p><strong>' . $navn . '</strong></p><p>' . $beskrivelse . '</p></div>';
            }
            return "Beklager, jeg fant ingen beskrivelse for EAN $ean.";
        }

        // Sjekk spesifikk butikk-spørring: "hva koster [EAN] hos [butikk]"
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

        // Ellers: bruk intensjon
        switch ($intent) {
            case 'greeting':
                return "Hei! Send meg en EAN-kode så finner jeg den billigste prisen.";
            case 'thanks':
                return "Bare hyggelig! Si ifra hvis du trenger mer hjelp.";
            case 'capabilities':
                return "Jeg kan finne priser på matvarer basert på EAN-koder og vise hvor du sparer mest.";
            case 'ean_lookup':
                if ($ean) {
                    $resultat = $this->scanner->skannProdukt($ean);
                    if ($resultat && !empty($resultat['priser'])) {
                        $billigst = $this->scanner->finnBilligstePris($resultat['priser']);
                        $andrePriser = $this->scanner->hentAndrePriser($resultat['priser']);

                        $navn = htmlspecialchars($resultat['navn'] ?? 'Ukjent');
                        $merke = htmlspecialchars($resultat['merke'] ?? '');
                        $bilde = !empty($resultat['bilde']) ? htmlspecialchars($resultat['bilde']) : null;

                        $replyHtml = '<div class="result"><h3>' . $navn;
                        if ($merke) $replyHtml .= ' (' . $merke . ')';
                        $replyHtml .= '</h3>';

                        if ($bilde) {
                            $replyHtml .= '<img src="' . $bilde . '" alt="Produktbilde" class="product-image"><br>';
                        }

                        if ($billigst) {
                            $prisVerdi = htmlspecialchars($billigst['pris']);
                            $butikkNavn = htmlspecialchars($billigst['butikk']);
                            $replyHtml .= '<p>Den laveste prisen jeg finner er <strong>' . $prisVerdi . ' kr</strong> hos <strong>' . $butikkNavn . '</strong>.</p>';
                        } else {
                            $replyHtml .= '<p>Ingen priser funnet.</p>';
                        }

                        if (!empty($andrePriser)) {
                            $replyHtml .= '<h4>Andre butikker:</h4><ul>';
                            foreach ($andrePriser as $pris) {
                                $butikk = htmlspecialchars($pris['butikk']);
                                $replyHtml .= '<li>' . $butikk . ': <b>' . htmlspecialchars($pris['pris']) . ' kr</b></li>';
                            }
                            $replyHtml .= '</ul>';
                        }

                        $replyHtml .= '</div>';
                        return $replyHtml;
                    }
                    return "Fant ingen prisinformasjon for EAN $ean.";
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

<?php
//inkluderer nødvendige filer
require_once __DIR__ . '/../Models/KassalappAPI.php';
require_once __DIR__ . '/../Models/StrekkodeScanner.php';
require_once __DIR__ . '/../../config/config.php';

/*
Klasse med ansvar for å håndtere brukerens søk via EAN-kode
kobler sammen modeller (API og skanner) og views (form og resultat)
*/
class ProductController {
    //instans av StrekkodeSkanner for å hente produktdata
    private $scanner;

    //initialiserer kassalappAPI og StrekkodeSkanner, bruker API-nøkkel fra config
    public function __construct() {
        $api = new KassalappAPI(KASSALAPP_API_KEY);
        $this->scanner = new StrekkkodeScanner($api);
    }

    //søk basert på EAN-kode sendt via POST, viser skjema og resultat
    public function search() {
        $resultat = null;
        $billigst = null;
        $andrePriser = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ean'])) {
            $ean = trim($_POST['ean']);
            $resultat = $this->scanner->skannProdukt($ean);

            if ($resultat && !empty($resultat['priser'])) {
                $billigst = $this->scanner->finnBilligstePris($resultat['priser']);
                $andrePriser = $this->scanner->hentAndrePriser($resultat['priser']);
            }
        }

        require __DIR__ . '/../Views/searchForm.php';

        if ($resultat) {
            // gjør variablene tilgjengelige for viewet
            $navn = htmlspecialchars($resultat['navn'] ?? 'Ukjent');
            $merke = htmlspecialchars($resultat['merke'] ?? '');
            $bilde = !empty($resultat['bilde']) ? htmlspecialchars($resultat['bilde']) : null;

            require __DIR__ . '/../Views/searchResult.php';
        }
    }

}

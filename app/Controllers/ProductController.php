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

        //sjekker om skjemaet er send og at EAN-kode er oppgitt
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ean'])) {
            $ean = trim($_POST['ean']);
            $resultat = $this->scanner->skannProdukt($ean);
        }

        //viser søkeskjema
        require __DIR__ . '/../Views/searchForm.php';

        //viser resultat om produktet ble funent
        if ($resultat) {
            require __DIR__ . '/../Views/searchResult.php';
        }
    }
}

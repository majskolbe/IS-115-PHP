
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Billigste Vare Chatbot</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        input { padding: 8px; width: 200px; }
        button { padding: 8px 12px; margin-left: 5px; }
        .result { margin-top: 20px; padding: 10px; background: #f7f7f7; border-radius: 8px; }
        img { max-width: 150px; border-radius: 6px; }
    </style>
</head>
<body>
    <h2>Søk billigste pris med EAN</h2>
    <form method="POST">
        <input type="text" name="ean" placeholder="Skriv inn EAN-kode..." required>
        <button type="submit">Søk</button>
    </form>


    <?php
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/vendor/autoload.php';
    require_once 'inc/KassalappApi_inc.php';
    require_once 'eanSearch.php';

    // Opprett API-klient og scanner
    $api = new KassalappAPI(KASSALAPP_API_KEY);
    $scanner = new StrekkkodeScanner($api);

    $resultat = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ean'])) {
        $ean = trim($_POST['ean']);
        $resultat = $scanner->skannProdukt($ean);
    }

    if ($resultat) {
        echo '<div class="result">';

        echo '<h3>' . htmlspecialchars($resultat['navn']) . ' (' . htmlspecialchars($resultat['merke']) . ')</h3>';

        if (!empty($resultat['bilde'])) {
            echo '<img src="' . htmlspecialchars($resultat['bilde']) . '" alt="Produktbilde"><br>';
        }

        if (!empty($resultat['priser'])) {
            $billigst = $resultat['priser'][0];
            echo '<p>Billigste pris: <b>' . $billigst['pris'] . ' kr</b> hos <b>' . htmlspecialchars($billigst['butikk']) . '</b></p>';
        } else {
            echo '<p>Ingen priser funnet.</p>';
        }

        echo '</div>';
    }

    //skriver ut pris fra andre butikker i stigende rekkefølge
    if (!empty($resultat['priser']) && count($resultat['priser']) > 1) {
        echo '<h4>Andre butikker:</h4><ul>';
        foreach (array_slice($resultat['priser'], 1) as $pris) {
            echo '<li>' . htmlspecialchars($pris['butikk']) . ': <b>' . $pris['pris'] . ' kr</b></li>';
        }
        echo '</ul>';
    }

?>


   
</body>
</html>

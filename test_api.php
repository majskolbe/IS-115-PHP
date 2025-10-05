<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once 'inc/KassalappApi_inc.php';

$api = new KassalappAPI(KASSALAPP_API_KEY);

// Søk etter melk
$resultater = $api->request('products', [
    'search' => 'lettmelk'
]);

foreach ($resultater['data'] as $produkt) {
    echo "{$produkt['name']}\n";

    if (is_array($produkt['current_price']) && isset($produkt['current_price']['price'], $produkt['current_price']['store']['name'])) {
        echo "   Pris: {$produkt['current_price']['price']} kr hos {$produkt['current_price']['store']['name']}\n";
    } else {
        echo "   Pris: Ikke tilgjengelig\n";
    }

    echo "   EAN: {$produkt['ean']}\n\n   </br>";
}

/*må finne ut hvordan man får skrevet ut pris, evt bare fra et par valgte butikker
må også få input feltet til å fungere så man kan skrive inn et produkt også fungerer det
*/

?>
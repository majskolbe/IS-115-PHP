<?php
// dersom resultat er tom er det ingenting å returnerer
if (empty($resultat)) {
    return;
}

//henter ut og sikrer dataen
$navn = htmlspecialchars($resultat['navn'] ?? 'Ukjent');
$merke = htmlspecialchars($resultat['merke'] ?? '');
$bilde = !empty($resultat['bilde']) ? htmlspecialchars($resultat['bilde']) : null;
$priser = $resultat['priser'] ?? [];

//finner billigste pris ved å hente ut element 0 i priser
$billigst = !empty($priser) ? $priser[0] : null;
$andrePriser = count($priser) > 1 ? array_slice($priser, 1) : [];


echo '<div class="result"> <h3>' . $navn . ' (' . $merke . ')</h3>';

//skriver ut bilde om det er bilde å skrive ut
if($bilde){
    echo '<img src="' . $bilde . '" alt="Produktbilde" style="max-width: 150px;"></br>';
} 

//skriver ut billigste pris hvis det er funnet pris
if($billigst){
    echo '<p>Billigste pris: <b>' . $billigst['pris'] . ' kr</b> hos <b>' . htmlspecialchars($billigst['butikk']) . '</b></p>';
}else{
    echo '<p>Ingen priser funnet.</p>';
}

echo '</div>';

//dersom andrePriser ikke er tom, skrives liste med butikk og pris ut
if(!empty($andrePriser)){
    echo '<h4>Andre butikker:</h4><ul>';
    foreach($andrePriser as $pris){
        $butikk = htmlspecialchars($pris['butikk']);
        echo '<li>' . $butikk . ': <b>' . $pris['pris'] . ' kr</b></li>';
    }
    echo '</ul>';
}

?>



<?php
// dersom resultat er tom er det ingenting å returnerer
if (empty($resultat)) {
    return;
}

echo '<div class="result"> <h3>' . $navn . ' (' . $merke . ')</h3>';

//printer bilde dersom det finnes bilde
if ($bilde) {
    echo '<img src="' . $bilde . '" alt="Produktbilde" style="max-width: 150px;"></br>';
}

//printer ut billigste pris dersom pris er funent
if ($billigst) {
    echo '<p>Billigste pris: <b>' . $billigst['pris'] . ' kr</b> hos <b>' . htmlspecialchars($billigst['butikk']) . '</b></p>';
} else {
    echo '<p>Ingen priser funnet.</p>';
}

echo '</div>';

//skriver ut priser fra andre butikker dersom det finnes
if (!empty($andrePriser)) {
    echo '<h4>Andre butikker:</h4><ul>';
    foreach ($andrePriser as $pris) {
        $butikk = htmlspecialchars($pris['butikk']);
        echo '<li>' . $butikk . ': <b>' . $pris['pris'] . ' kr</b></li>';
    }
    echo '</ul>';
}
?>





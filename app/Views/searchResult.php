<?php
// Expects $resultat from controller
if (empty($resultat)) {
    return;
}
?>
<div class="result">
    <h3><?php echo htmlspecialchars($resultat['navn'] ?? 'Ukjent'); ?> (<?php echo htmlspecialchars($resultat['merke'] ?? ''); ?>)</h3>

    <?php if (!empty($resultat['bilde'])): ?>
        <img src="<?php echo htmlspecialchars($resultat['bilde']); ?>" alt="Produktbilde" style="max-width:150px;border-radius:6px;"><br>
    <?php endif; ?>

    <?php if (!empty($resultat['priser'])): ?>
        <?php $billigst = $resultat['priser'][0]; ?>
        <p>Billigste pris: <b><?php echo $billigst['pris']; ?> kr</b> hos <b><?php echo htmlspecialchars($billigst['butikk']); ?></b></p>
    <?php else: ?>
        <p>Ingen priser funnet.</p>
    <?php endif; ?>
</div>

<?php if (!empty($resultat['priser']) && count($resultat['priser']) > 1): ?>
    <h4>Andre butikker:</h4>
    <ul>
        <?php foreach (array_slice($resultat['priser'], 1) as $pris): ?>
            <li><?php echo htmlspecialchars($pris['butikk']); ?>: <b><?php echo $pris['pris']; ?> kr</b></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

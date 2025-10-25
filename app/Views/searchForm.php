<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Søk produkt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        input { padding: 8px; width: 220px; }
        button { padding: 8px 12px; margin-left: 8px; }
        .result { margin-top: 20px; padding: 10px; background: #f7f7f7; border-radius: 8px; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>
<script>
  var botmanWidget = {
    aboutText: 'EAN-prisbot',
    introMessage: "Hei! Skriv 'EAN 1234567890123' for å finne billigste pris 📦",
    chatServer: '/botman.php' // ← dette må matche filen du nettopp laget
  };
</script>


</head>
<body>
<h1>Søk billigste pris med EAN</h1>
<form method="POST" action="">
    <input type="text" name="ean" placeholder="Skriv inn EAN-kode..." required value="<?php echo htmlspecialchars($_POST['ean'] ?? ''); ?>">
    <button type="submit">Søk</button>
</form>

</body>
</html>
<?php
// LayoutView.php
// Brukes som felles ramme for alle sider
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'App') ?></title>
    <link rel="stylesheet" href="public/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="<?= htmlspecialchars($bodyClass ?? '') ?>">
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</body>
</html>
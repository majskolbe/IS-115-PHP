<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrer</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <h2>Registrer bruker</h2>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="register">
        <input type="text" name="fname" placeholder="Fornavn" required>
        <input type="text" name="lname" placeholder="Etternavn" required>
        <input type="email" name="email" placeholder="E-post" required>
        <input type="text" name="username" placeholder="Brukernavn" required>
        <input type="password" name="password" placeholder="Passord" required>
        <button type="submit">Registrer</button>
    </form>

    <?php if (!empty($_GET['error'])): ?>
        <p class="message error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
    <?php if (!empty($_GET['message'])): ?>
        <p class="message success"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php endif; ?>

    <p>Allerede bruker? <a href="index.php?page=login">Logg inn her</a></p>
</div>
</body>
</html>

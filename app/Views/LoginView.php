<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logg inn</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <h2>Logg inn</h2>
    <form method="post" action="index.php">
        <input type="text" name="username" placeholder="Brukernavn" required>
        <input type="password" name="password" placeholder="Passord" required>
        <button type="submit">Logg inn</button>
    </form>

    <?php 
    if (!empty($_GET['error'])){
        echo '<p class="message error">' . htmlspecialchars($_GET['error']) . '</p>';

    }
    if (!empty($_GET['message'])){
        echo '<p class="message success">' .  htmlspecialchars($_GET['message']) . '</p>';
    }
    ?>

    <p>Har du ikke bruker? <a href="index.php?page=register">Registrer her</a></p>
</div>
</body>
</html>

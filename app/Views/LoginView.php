<?php
$title = "Logg inn";
$bodyClass = "auth-body";

//defaluts om ingenting blir sent fra index
$error = $error ?? ($_GET['error'] ?? null);
$message = $message ?? ($_GET['message'] ?? null);

ob_start();
?>
<div class="auth-container">
    <h2>Logg inn</h2>
    <form method="post" action="index.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CsrfHelper::generateToken()) ?>">
        <input type="text" name="username" placeholder="Brukernavn" required>
        <input type="password" name="password" placeholder="Passord" required>
        <button type="submit">Logg inn</button>
    </form>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <p>Har du ikke bruker? <a href="index.php?page=register">Registrer her</a></p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/LayoutView.php';

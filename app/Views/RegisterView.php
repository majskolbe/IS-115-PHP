<?php
$title = "Registrer";
$bodyClass = "auth-body";

//defaluts om ingenting blir sent fra index
$error = $error ?? ($_GET['error'] ?? null);
$message = $message ?? ($_GET['message'] ?? null);


ob_start();
?>
<div class="auth-container">
    <h2>Registrer bruker</h2>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CsrfHelper::generateToken()) ?>">
        <input type="hidden" name="action" value="register">
        <input type="text" name="fname" placeholder="Fornavn" required>
        <input type="text" name="lname" placeholder="Etternavn" required>
        <input type="email" name="email" placeholder="E-post" required>
        <input type="text" name="username" placeholder="Brukernavn" required>
        <input type="password" name="password" placeholder="Passord" required>
        <button type="submit">Registrer</button>
    </form>

    <?php if (!empty($error)): ?>
        <p class="message error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <p class="message success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <p>Allerede bruker? <a href="index.php?page=login">Logg inn her</a></p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/LayoutView.php';

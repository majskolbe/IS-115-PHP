<?php
// AdminView.php
$title = "Admin Panel";
$bodyClass = "admin-body";

ob_start(); // start buffer
?>
<div class="admin-container">
    <h1>Admin Panel</h1>
    <p>Innlogget som: <strong><?= htmlspecialchars($username) ?></strong></p>

    <div class="button-group">
        <a href="index.php?page=chat">Gå til chat</a>
        <form action="index.php?page=logout" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CsrfHelper::generateToken()) ?>">
            <button type="submit">Logg ut</button>
        </form>
    </div>

    <h2>Registrerte brukere</h2>
    <table>
        <thead>
            <tr>
                <th>Fullt navn</th>
                <th>E-post</th>
                <th>Brukernavn</th>
                <th>Rolle</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['fname'] . ' ' . $u['lname']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><span class="role-badge role-<?= htmlspecialchars($u['role']) ?>">
                        <?= ucfirst($u['role']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/LayoutView.php';

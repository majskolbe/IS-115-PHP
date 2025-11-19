<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="admin-body">
<div class="admin-container">
    <h1>Admin Panel</h1>
    <p>Innlogget som: <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></p>
    
    <div class="button-group">
        <a href="index.php?page=chat">Gå til chat</a>
        <form action="index.php?page=logout" method="post">
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
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td>
                        <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                            <?= htmlspecialchars(ucfirst($user['role'])) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
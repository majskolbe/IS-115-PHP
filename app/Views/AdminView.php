<?php
// Access already checked in index.php before including this view
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .button-group {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
        }
        .button-group a, .button-group form button {
            padding: 10px 20px;
            background: #cc0000;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .button-group a:hover, .button-group form button:hover {
            background: #990000;
        }
        .button-group form {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background: #cc0000;
            color: white;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table tbody tr:hover {
            background: #f9f9f9;
        }
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .role-admin {
            background: #cc0000;
            color: white;
        }
        .role-user {
            background: #e0e0e0;
            color: #333;
        }
    </style>
</head>
<body>
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
<?php
$username = htmlspecialchars($_SESSION['user']['username'] ?? '');
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="admin-body">

<div class="admin-container">
    <h1>Admin Panel</h1>
    <p>Innlogget som: <strong><?= $username ?></strong></p>

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
            <?php 
            foreach ($users as $u){
                echo '<tr>
                    <td>' . htmlspecialchars($u['fname'] . ' ' . $u['lname']) .'</td>
                    <td>'. htmlspecialchars($u['email']) .'</td>
                    <td>'. htmlspecialchars($u['username']) .'</td>
                    <td><span class="role-badge role-' . $u['role'] .'">' . ucfirst($u['role']) .'</span></td>
                </tr>';
            }
            ?>
        </tbody>
    </table>

</div>

</body>
</html>

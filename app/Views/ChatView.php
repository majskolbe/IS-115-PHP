<?php
//henter brukerens rolle, eller "user" om ingen rolle finnes
$role = $_SESSION['user']['role'] ?? 'user';
$username = htmlspecialchars($_SESSION['user']['username'] ?? '');
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="chat-body">

<div class="chat-wrapper">

  <!-- Eksempelspørsmål -->
  <div class="question-sidebar">
    <h3>Eksempler på spørsmål</h3>
    <table class="question-table">
      <thead>
        <tr><th>Spørsmål</th></tr>
      </thead>
      <tbody>
        <?php 
        if (!empty($exampleQuestions)){
          foreach ($exampleQuestions as $q){
            echo '<tr><td>' . htmlspecialchars($q['question']) . '</td></tr>';
          }
        }else{
          echo '<tr><td class="no-data">Ingen spørsmål registrert</td></tr>';
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Chat -->
  <div class="chat-container">
    <h2>Finn laveste pris på en matvare!</h2>
    <p>Innlogget som: <strong><?= $username ?></strong> (<?= $role ?>)</p>

    <div class="button-group">
        <a href="index.php?page=admin" class="nav-button">Gå til admin</a>
        <form action="index.php?page=logout" method="post">
            <button type="submit">Logg ut</button>
        </form>
    </div>

    <?php 
    if (!empty($_GET['error'])){
      echo '<div class="alert alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    ?>

    <div class="chat-box" id="chatBox"></div>
    <input type="text" id="userInput" placeholder="Skriv en melding...">
    <button id="sendBtn">Send</button>

  </div>

  <!-- EAN-koder -->
  <div class="ean-sidebar">
    <h3>Foreslåtte varer</h3>
    <table class="ean-table">
      <thead>
        <tr>
          <th>Produktnavn</th>
          <th>EAN-kode</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        if (!empty($eanCodes)){
          foreach ($eanCodes as $ean){
            echo '<tr>
              <td>' . htmlspecialchars($ean['product_name']) .'</td>
              <td>' . htmlspecialchars($ean['ean_code']) . '</td>
            </tr>';
          }
        }else{
          echo '<tr><td colspan="2" class="no-data">Ingen varer registrert</td></tr>';
        }
        ?>
      </tbody>
    </table>
  </div>

</div>

<script src="public/js/chat.js"></script>

</body>
</html>

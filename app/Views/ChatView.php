<?php
// Access already checked in index.php before including this view
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Finn laveste pris</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="chat-body">
<?php
$role = $_SESSION['user']['role'];
$username = htmlspecialchars($_SESSION['user']['username']);
?>
<div class="chat-container">
  <h2>Finn laveste pris på en matvare!</h2>
  <p>Innlogget som: <strong><?= $username ?></strong> (<?= $role ?>)</p>
  
  <div class="button-group">
    <?php if ($role === 'admin'): ?>
      <a href="index.php?page=admin" class="nav-button">Gå til admin</a>
    <?php endif; ?>
    <form action="index.php?page=logout" method="post" style="margin: 0;">
      <button type="submit">Logg ut</button>
    </form>
  </div>

  <div class="chat-box" id="chatBox"></div>
  <input type="text" id="userInput" placeholder="Skriv en melding..." />
  <button onclick="sendMessage()">Send</button>
</div>

<script>
  async function sendMessage() {
    const input = document.getElementById("userInput");
    const chatBox = document.getElementById("chatBox");
    const message = input.value.trim();

    if (message === "") return;

    chatBox.innerHTML += `<div class="message user"><strong>Du:</strong> ${message}</div>`;
    input.value = "";

    try {
      const response = await fetch("./app/Controllers/Chat_backend.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message })
      });

      const data = await response.json();
      const reply = data.reply || data.error || "Ingen svar mottatt.";

      chatBox.innerHTML += `<div class="message bot"><strong>Bot:</strong> ${reply}</div>`;
      chatBox.scrollTop = chatBox.scrollHeight;
    } catch (error) {
      console.error("Fetch error:", error);
      chatBox.innerHTML += `<div class="message bot"><strong>Bot:</strong> Feil ved henting av svar: ${error.message}</div>`;
    }
  }

  // Legg til Enter-tast som trigger sendMessage()
  document.getElementById("userInput").addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault(); // Hindrer linjeskift
      sendMessage();
    }
  });
</script>
    
</body>
</html>

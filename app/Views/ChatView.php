<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

     <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff;
      margin: 0;
      padding: 0;
    }
    .chat-container {
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(255,0,0,0.2);
      padding: 20px;
      border: 2px solid #cc0000;
    }
    .chat-box {
      height: 300px;
      overflow-y: auto;
      border: 1px solid #cc0000;
      padding: 10px;
      margin-bottom: 15px;
      background: #ffecec;
    }
    .message {
      margin: 10px 0;
    }
    .user {
      text-align: right;
      color: #cc0000;
    }
    .bot {
      text-align: left;
      color: #990000;
    }
    input[type="text"] {
      width: 80%;
      padding: 10px;
      border: 1px solid #cc0000;
      border-radius: 4px;
      background: #fff;
      color: #000;
    }
    button {
      padding: 10px 15px;
      border: none;
      background: #cc0000;
      color: white;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background: #990000;
    }
  </style>
</head>
<body>

<div class="chat-container">
  <h2>🗨️ Finn laveste pris på en matvare!</h2>
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

</script>
    
</body>
</html>
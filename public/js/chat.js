(function() {
  async function sendMessage() {
    const input = document.getElementById("userInput");
    const chatBox = document.getElementById("chatBox");
    if (!input || !chatBox) return;

    const message = input.value.trim();
    if (!message) return;

    // Vis brukerens melding
    const userDiv = document.createElement('div');
    userDiv.className = 'message user';
    userDiv.innerHTML = `<strong>Du:</strong> ${message}`;
    chatBox.appendChild(userDiv);
    input.value = "";
    chatBox.scrollTop = chatBox.scrollHeight;

    try {
      //fetch sender HTTP-forespørsel til server uten at sidne må lastes på nytt
      const res = await fetch("./app/Controllers/ChatBackend.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message })
      });

      const data = await res.json();
      const reply = data.reply || data.error || "Ingen svar mottatt.";

      const botDiv = document.createElement('div');
      botDiv.className = 'message bot';
      const label = document.createElement('strong');
      label.textContent = 'Bot: ';
      botDiv.appendChild(label);

      const contentDiv = document.createElement('div');
      if (window.DOMPurify) {
        contentDiv.innerHTML = DOMPurify.sanitize(reply, {
          ALLOWED_TAGS: ['div','h3','h4','p','ul','li','strong','b','img','br'],
          ALLOWED_ATTR: ['src','alt','class']
        });
      } else {
        contentDiv.innerHTML = reply; // fallback
      }

      botDiv.appendChild(contentDiv);
      chatBox.appendChild(botDiv);
      chatBox.scrollTop = chatBox.scrollHeight;

    } catch (err) {
      const errDiv = document.createElement('div');
      errDiv.className = 'message bot';
      errDiv.innerHTML = `<strong>Bot:</strong> Feil ved henting: ${err.message || err}`;
      chatBox.appendChild(errDiv);
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    const inputEl = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');

    if (inputEl) {
      inputEl.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          sendMessage();
        }
      });
    }

    if (sendBtn) {
      sendBtn.addEventListener('click', function(e) {
        e.preventDefault();
        sendMessage();
      });
    }
  });

  window.sendMessage = sendMessage;
})();
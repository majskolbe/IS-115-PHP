(function() {
  //asynkron funksjon som sender melding til server og oppdaterer chatvinduet
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

      //tolker JSON-respons fra server
      const data = await res.json();
      const reply = data.reply || data.error || "Ingen svar mottatt.";

      //oppretter ny melding fra bot
      const botDiv = document.createElement('div');
      botDiv.className = 'message bot';
      const label = document.createElement('strong');
      label.textContent = 'Bot: ';
      botDiv.appendChild(label);

      //legg til innholdet fra serverens svar
      const contentDiv = document.createElement('div');
      if (window.DOMPurify) {
        //bruker DOMPurify for å sanitere HTML og forhindre XXS-angrep
        contentDiv.innerHTML = DOMPurify.sanitize(reply, {
          ALLOWED_TAGS: ['div','h3','h4','p','ul','li','strong','b','img','br'],
          ALLOWED_ATTR: ['src','alt','class']
        });
      } else {
        //viser rå HTML
        contentDiv.innerHTML = reply; // fallback
      }

      botDiv.appendChild(contentDiv);
      chatBox.appendChild(botDiv);
      chatBox.scrollTop = chatBox.scrollHeight;

    } catch (err) {
      //viser feilmelding i chatvindu hvis serverkallet feiler
      const errDiv = document.createElement('div');
      errDiv.className = 'message bot';
      errDiv.innerHTML = `<strong>Bot:</strong> Feil ved henting: ${err.message || err}`;
      chatBox.appendChild(errDiv);
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  }

  //når DOM-en er lastet inne, koble event listeners til inputfelt og knapp
  document.addEventListener('DOMContentLoaded', function() {
    const inputEl = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');

    if (inputEl) {
      //lytt etter enter-tast i inputfelt
      inputEl.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          sendMessage();
        }
      });
    }

    if (sendBtn) {
      //lytt etter klikk på send-knapp
      sendBtn.addEventListener('click', function(e) {
        e.preventDefault();
        sendMessage();
      });
    }
  });

  //eksporterer sendMessage til globalt scope så det kan kalles fra andre script
  window.sendMessage = sendMessage;
})();
// Chat frontend logic (moved out from inline script in ChatView.php)
(function(){
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  async function sendMessage() {
    const input = document.getElementById("userInput");
    const chatBox = document.getElementById("chatBox");
    if (!input || !chatBox) return;

    const message = input.value.trim();
    if (!message) return;

    chatBox.innerHTML += `<div class="message user"><strong>Du:</strong> ${escapeHtml(message)}</div>`;
    input.value = "";

    try {
      const res = await fetch("./app/Controllers/Chat_backend.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({message})
      });

      const data = await res.json();
      const reply = data.reply || data.error || "Ingen svar mottatt.";

      // If DOMPurify is available, sanitize and render as HTML.
      // Otherwise, render escaped text and start loading DOMPurify in background for future messages.
      if (window.DOMPurify) {
        const clean = DOMPurify.sanitize(reply);
        chatBox.innerHTML += `<div class="message bot"><strong>Bot:</strong> ${clean}</div>`;
      } else {
        chatBox.innerHTML += `<div class="message bot"><strong>Bot:</strong> ${escapeHtml(reply)}</div>`;
        // Load DOMPurify in background so subsequent replies can be rendered as sanitized HTML
        if (!window.__dompurify_loading) {
          window.__dompurify_loading = true;
          const s = document.createElement('script');
          s.src = 'https://cdn.jsdelivr.net/npm/dompurify@2.4.0/dist/purify.min.js';
          s.integrity = 'sha384-E9mM1aHhXKX2s0YvZl9wqQZ2Y+T0r6tX1D5v6X7Yy6GQ6k9h1ZfQ8k1lWq2uJ5fP';
          s.crossOrigin = 'anonymous';
          s.onload = function(){ console.info('DOMPurify loaded'); };
          s.onerror = function(){ console.warn('Failed to load DOMPurify'); };
          document.head.appendChild(s);
        }
      }
      chatBox.scrollTop = chatBox.scrollHeight;
    } catch (err) {
      console.error('Chat fetch error', err);
      chatBox.innerHTML += `<div class="message bot"><strong>Bot:</strong> Feil ved henting: ${escapeHtml(err.message || String(err))}</div>`;
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    const inputEl = document.getElementById('userInput');
    if (inputEl) {
      inputEl.addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
          e.preventDefault();
          sendMessage();
        }
      });
    }

    const sendBtn = document.querySelector('button[onclick="sendMessage()"]');
    if (sendBtn) {
      sendBtn.addEventListener('click', function(e){
        e.preventDefault();
        sendMessage();
      });
    }
  });

  // expose for inline onclick compatibility (if any)
  window.sendMessage = sendMessage;
})();

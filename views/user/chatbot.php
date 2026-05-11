<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant Nutrition IA — Nutrivert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root{ --coral:#FF7E67; --mint:#7DCFB6; --sage:#4A6B4A; --light-bg:#f4f8f4; }
        body{ background:var(--light-bg); color:var(--sage); }
        .navbar-top{ background:#fff; padding:15px 0; border-bottom:2px solid var(--mint); margin-bottom:30px; }
        .navbar-top .container{ display:flex; justify-content:space-between; align-items:center; }
        .logo{ font-size:1.5rem; font-weight:700; color:var(--coral); }
        .btn-logout{ background:var(--coral); color:#fff; border:none; padding:8px 20px; border-radius:20px; text-decoration:none; font-weight:600; }
        .btn-logout:hover{ background:#e56a55; color:#fff; }
        .btn-back{ background:var(--mint); color:#fff; border:none; padding:8px 20px; border-radius:20px; text-decoration:none; font-weight:600; }
        .btn-back:hover{ background:#6ab89d; color:#fff; }
        .chat-container{ max-width:800px; margin:0 auto; background:#fff; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,.08); overflow:hidden; }
        .chat-header{ background:linear-gradient(135deg,var(--mint),var(--sage)); color:#fff; padding:20px; text-align:center; }
        .chat-messages{ height:500px; overflow-y:auto; padding:20px; background:#f8f9fa; }
        .message{ margin-bottom:15px; padding:12px 16px; border-radius:18px; max-width:75%; word-wrap:break-word; }
        .message.user{ background:var(--coral); color:#fff; margin-left:auto; text-align:right; }
        .message.bot{ background:#fff; color:var(--sage); border:1px solid #e9ecef; }
        .chat-input-container{ padding:20px; background:#fff; border-top:1px solid #e9ecef; }
        .chat-input{ display:flex; gap:10px; }
        .chat-input input{ flex:1; padding:12px 16px; border:2px solid #e9ecef; border-radius:25px; outline:none; }
        .chat-input input:focus{ border-color:var(--mint); }
        .chat-input button{ background:var(--coral); color:#fff; border:none; padding:12px 20px; border-radius:25px; cursor:pointer; font-weight:600; }
        .chat-input button:hover{ background:#e56a55; }
        .chat-input button:disabled{ background:#ccc; cursor:not-allowed; }
        .typing{ display:none; padding:12px 16px; color:#666; font-style:italic; }
        .model-tag{ font-size:.7rem; color:#aaa; display:block; margin-top:4px; }
    </style>
</head>
<body>

<div class="navbar-top">
    <div class="container">
        <div class="logo"><i class="fas fa-leaf"></i> Nutrivert</div>
        <div class="d-flex gap-2">
            <a href="index.php?controller=user_dashboard&action=index" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="index.php?controller=user&action=logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="chat-container">
        <div class="chat-header">
            <h2><i class="fas fa-robot"></i> Assistant Nutrition IA — NutriVert</h2>
            <p>Propulsé par Gemini · Posez vos questions sur NutriVert et la nutrition</p>
        </div>

        <div class="chat-messages" id="chatMessages">
            <!-- Historique chargé depuis la DB -->
            <?php if (!empty($chats)): ?>
                <?php foreach ($chats as $chat): ?>
                    <div class="message user"><?= htmlspecialchars($chat['user_message']) ?></div>
                    <div class="message bot">
                        <strong>Assistant :</strong> <?= nl2br(htmlspecialchars($chat['ai_response'])) ?>
                        <span class="model-tag"><?= htmlspecialchars($chat['model'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="message bot">
                    <strong>Assistant :</strong> Bonjour <?= htmlspecialchars($_SESSION['user']['nom'] ?? 'Utilisateur') ?> !
                    Je suis votre assistant NutriVert. Comment puis-je vous aider aujourd'hui ?
                </div>
            <?php endif; ?>
        </div>

        <div class="typing" id="typing">
            <i class="fas fa-circle-notch fa-spin"></i> L'assistant rédige une réponse…
        </div>

        <div class="chat-input-container">
            <div class="chat-input">
                <input type="text" id="msgInput" placeholder="Posez votre question…" maxlength="2000"
                       autocomplete="off">
                <button id="sendBtn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
const messagesDiv = document.getElementById('chatMessages');
const input       = document.getElementById('msgInput');
const sendBtn     = document.getElementById('sendBtn');
const typing      = document.getElementById('typing');

// Scroll to bottom on load
messagesDiv.scrollTop = messagesDiv.scrollHeight;

function addMessage(text, sender, model) {
    const div = document.createElement('div');
    div.className = 'message ' + sender;
    if (sender === 'bot') {
        div.innerHTML = '<strong>Assistant :</strong> '
            + text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>')
            + (model ? '<span class="model-tag">' + model + '</span>' : '');
    } else {
        div.textContent = text;
    }
    messagesDiv.appendChild(div);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

async function sendMessage() {
    const message = input.value.trim();
    if (!message) return;

    addMessage(message, 'user');
    input.value = '';
    sendBtn.disabled = true;
    typing.style.display = 'block';
    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    try {
        const fd = new FormData();
        fd.append('message', message);
        const resp = await fetch('index.php?controller=chat&action=message', {
            method: 'POST',
            body: fd
        });
        const data = await resp.json();

        typing.style.display = 'none';
        sendBtn.disabled = false;

        if (data.success) {
            addMessage(data.bot, 'bot', data.provider);
        } else {
            addMessage('Erreur : ' + (data.error || data.bot || 'Réponse indisponible.'), 'bot');
        }
    } catch (e) {
        typing.style.display = 'none';
        sendBtn.disabled = false;
        addMessage('Erreur réseau : ' + e.message, 'bot');
    }
}

input.addEventListener('keypress', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});
</script>
</body>
</html>

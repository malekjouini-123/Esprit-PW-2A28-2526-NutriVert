<?php if (!isLoggedIn()) { header('Location: index.php?action=login'); exit; } ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant IA - NutriVert</title>
    <style>
        body { background: #f0f9ea; font-family: Arial, sans-serif; color: #1a3a1a; }
        .container { max-width: 860px; margin: 40px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .chat-box { height: 460px; overflow-y: auto; border: 1px solid #c8e6c9; border-radius: 1rem; padding: 1rem; background: #fbfff8; }
        .message { margin: 0.7rem 0; padding: 0.8rem 1rem; border-radius: 1rem; max-width: 82%; line-height: 1.45; white-space: pre-wrap; }
        .user { margin-left: auto; background: #2e7d32; color: white; }
        .assistant { background: #e8f5e9; color: #1a3a1a; }
        .composer { display: flex; gap: 0.7rem; margin-top: 1rem; }
        textarea { flex: 1; min-height: 70px; padding: 0.9rem; border: 1px solid #c8e6c9; border-radius: 1rem; resize: vertical; font-family: inherit; }
        button { background: #2e7d32; color: white; border: none; border-radius: 1rem; padding: 0.8rem 1.3rem; font-weight: bold; cursor: pointer; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        .status { margin-top: 0.7rem; color: #2e7d32; font-size: 0.9rem; }
        .error { color: #b00020; }
        a { color: #2e7d32; text-decoration: none; }
        @media (max-width: 700px) { .composer { flex-direction: column; } .message { max-width: 100%; } }
    </style>
</head>
<body>
<div class="container">
    <h2>Assistant IA NutriVert</h2>
    <p><a href="index.php">Accueil</a> | <a href="index.php?action=profile">Mon profil</a></p>

    <div class="chat-box" id="chatBox">
        <?php if (empty($chats)): ?>
            <div class="message assistant">Bonjour, je suis l'assistant NutriVert. Posez-moi une question sur vos recettes, vos objectifs nutritionnels ou une habitude plus durable.</div>
        <?php endif; ?>
        <?php foreach ($chats as $chat): ?>
            <div class="message user"><?= htmlspecialchars($chat['user_message']) ?></div>
            <div class="message assistant"><?= htmlspecialchars($chat['ai_response']) ?></div>
        <?php endforeach; ?>
    </div>

    <form class="composer" id="chatForm">
        <textarea id="messageInput" maxlength="2000" placeholder="Votre question..." required></textarea>
        <button type="submit" id="sendBtn">Envoyer</button>
    </form>
    <div class="status" id="chatStatus"></div>
</div>

<script>
(function() {
    const form = document.getElementById('chatForm');
    const input = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const chatBox = document.getElementById('chatBox');
    const status = document.getElementById('chatStatus');

    function appendMessage(text, type) {
        const item = document.createElement('div');
        item.className = 'message ' + type;
        item.textContent = text;
        chatBox.appendChild(item);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function setStatus(message, isError) {
        status.textContent = message;
        status.className = isError ? 'status error' : 'status';
    }

    chatBox.scrollTop = chatBox.scrollHeight;

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        appendMessage(message, 'user');
        input.value = '';
        sendBtn.disabled = true;
        setStatus('Assistant en cours de reponse...', false);

        try {
            const response = await fetch('index.php?action=chat-ask', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            });
            const result = await response.json();

            if (result.success) {
                appendMessage(result.reply, 'assistant');
                setStatus('', false);
                return;
            }

            appendMessage(result.error || 'Erreur assistant IA.', 'assistant');
            setStatus(result.error || 'Erreur assistant IA.', true);
        } catch (error) {
            appendMessage('Erreur reseau pendant la demande IA.', 'assistant');
            setStatus('Erreur reseau pendant la demande IA.', true);
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    });
})();
</script>
</body>
</html>

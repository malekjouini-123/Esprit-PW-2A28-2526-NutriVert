<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Connexion - NutriVert</title>
    <style>
        body { background: #f0f9ea; font-family: Arial; }
        .form { max-width: 400px; margin: 50px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 2rem; border: 1px solid #ccc; box-sizing: border-box; }
        button { background: #2e7d32; color: white; font-weight: bold; cursor: pointer; border: none; }
        .error { color: red; }
        .success { color: green; }
        a { color: #2e7d32; text-decoration: none; }
        .divider { text-align: center; color: #777; margin: 1rem 0; }
        .face-panel { display: none; margin-top: 1rem; }
        video { width: 100%; border-radius: 1rem; background: #111; }
        .face-status { font-size: 0.9rem; margin-top: 0.5rem; color: #2e7d32; }
        .secondary { background: #4caf50; }
    </style>
</head>
<body>
<div class="form">
    <h2>Connexion</h2>
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=login">
        <input type="text" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>

    <div class="divider">ou</div>
    <input type="email" id="faceEmail" placeholder="Email pour Face ID" autocomplete="email">
    <button type="button" id="startFaceLogin" class="secondary">Continuer avec Face ID</button>
    <div id="faceLoginPanel" class="face-panel">
        <video id="faceVideo" autoplay playsinline></video>
        <canvas id="faceCanvas" style="display:none;"></canvas>
        <button type="button" id="captureFace">Capturer et se connecter</button>
        <div id="faceStatus" class="face-status"></div>
    </div>

    <p><a href="index.php?action=forgot-password">Mot de passe oublie ?</a></p>
    <p><a href="index.php?action=register">Pas de compte ? Inscrivez-vous</a></p>
    <p><a href="index.php">Retour a l'accueil</a></p>
</div>
<script>
(function() {
    const emailInput = document.getElementById('faceEmail');
    const startBtn = document.getElementById('startFaceLogin');
    const captureBtn = document.getElementById('captureFace');
    const panel = document.getElementById('faceLoginPanel');
    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceCanvas');
    const status = document.getElementById('faceStatus');
    let stream = null;

    function setStatus(message, isError) {
        status.textContent = message;
        status.style.color = isError ? '#b00020' : '#2e7d32';
    }

    function emailIsValid(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    startBtn.addEventListener('click', async function() {
        const email = emailInput.value.trim();
        panel.style.display = 'block';

        if (!emailIsValid(email)) {
            setStatus('Entrez votre email avant la capture Face ID.', true);
            emailInput.focus();
            return;
        }

        setStatus('Activation de la camera...', false);

        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            video.srcObject = stream;
            setStatus('Cadrez votre visage puis lancez la capture.', false);
        } catch (error) {
            setStatus("Impossible d'acceder a la camera.", true);
        }
    });

    captureBtn.addEventListener('click', async function() {
        const email = emailInput.value.trim();
        if (!emailIsValid(email)) {
            setStatus('Email invalide.', true);
            return;
        }

        if (!video.videoWidth) {
            setStatus('Camera pas encore prete.', true);
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const faceImage = canvas.toDataURL('image/jpeg', 0.9);
        setStatus('Verification Face ID...', false);

        try {
            const response = await fetch('index.php?action=face-login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, face_image: faceImage })
            });
            const responseText = await response.text();
            let result = {};
            try {
                result = JSON.parse(responseText);
            } catch (error) {
                setStatus(responseText.trim() || 'Reponse Face ID invalide.', true);
                return;
            }

            if (result.success && result.match) {
                setStatus('Visage reconnu. Connexion...', false);
                window.location.href = result.redirect || 'index.php?action=profile';
                return;
            }

            setStatus(result.error || 'Visage non reconnu.', true);
        } catch (error) {
            setStatus('Erreur pendant la verification Face ID.', true);
        }
    });

    window.addEventListener('beforeunload', function() {
        if (stream) {
            stream.getTracks().forEach(function(track) { track.stop(); });
        }
    });
})();
</script>
</body>
</html>

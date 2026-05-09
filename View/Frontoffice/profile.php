<?php if (!isset($user)) die("Acces interdit."); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mon profil - NutriVert</title>
    <style>
        body { background: #f0f9ea; font-family: Arial; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 1rem; border: 1px solid #ccc; box-sizing: border-box; }
        button { background: #2e7d32; color: white; font-weight: bold; cursor: pointer; border: none; }
        .success { color: green; }
        .error { color: red; }
        .form-error-message { color: #b00020; background: #fdecea; border: 1px solid #f5c6cb; padding: 0.8rem 1rem; border-radius: 1rem; margin-bottom: 1rem; }
        .face-box { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #c8e6c9; }
        .danger { background: #b00020; }
        .secondary { background: #4caf50; }
        .muted { color: #666; font-size: 0.9rem; }
        .face-panel { display: none; margin-top: 1rem; }
        video { width: 100%; border-radius: 1rem; background: #111; }
        .face-status { font-size: 0.9rem; margin-top: 0.5rem; color: #2e7d32; }
        a { color: #2e7d32; text-decoration: none; }
    </style>
    <link rel="stylesheet" href="css/form-validation.css">
</head>
<body>
<div class="container">
    <h2>Mon profil</h2>
    <p>Bonjour <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)</p>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form id="profileForm" method="post">
        <label>Poids (kg)</label>
        <input type="text" name="poids" value="<?= htmlspecialchars($user['poids']) ?>">
        <label>Taille (cm)</label>
        <input type="text" name="taille" value="<?= htmlspecialchars($user['taille']) ?>">
        <label>Objectif nutritionnel</label>
        <input type="text" name="objectif" value="<?= htmlspecialchars($user['objectif_nutritionnel']) ?>" placeholder="Ex: Perte de poids, prise de muscle...">
        <label>Regime alimentaire</label>
        <input type="text" name="regime" value="<?= htmlspecialchars($user['regime_alimentaire']) ?>" placeholder="Vegetarien, sans gluten, vegan...">
        <button type="submit">Enregistrer les modifications</button>
    </form>

    <div class="face-box">
        <h3>Face ID</h3>
        <p class="muted">Statut : <strong id="faceState"><?= !empty($hasFace) ? 'enregistre' : 'non configure' ?></strong></p>
        <button type="button" id="startProfileFace" class="secondary"><?= !empty($hasFace) ? 'Remplacer avec la webcam' : 'Ajouter avec la webcam' ?></button>
        <div id="profileFacePanel" class="face-panel">
            <video id="profileFaceVideo" autoplay playsinline></video>
            <canvas id="profileFaceCanvas" style="display:none;"></canvas>
            <button type="button" id="saveProfileFace"><?= !empty($hasFace) ? 'Capturer et remplacer Face ID' : 'Capturer et ajouter Face ID' ?></button>
            <div id="profileFaceStatus" class="face-status"></div>
        </div>
        <?php if (!empty($hasFace)): ?>
            <form method="post" action="index.php?action=delete-face" onsubmit="return confirm('Supprimer votre Face ID ?')">
                <button type="submit" class="danger">Supprimer Face ID</button>
            </form>
        <?php endif; ?>
    </div>
    <p><a href="index.php?action=logout">Se deconnecter</a> | <a href="index.php">Accueil</a></p>
</div>
<script src="js/form-validation.js"></script>
<script>
(function() {
    const hasFace = <?= !empty($hasFace) ? 'true' : 'false' ?>;
    const startBtn = document.getElementById('startProfileFace');
    const saveBtn = document.getElementById('saveProfileFace');
    const panel = document.getElementById('profileFacePanel');
    const video = document.getElementById('profileFaceVideo');
    const canvas = document.getElementById('profileFaceCanvas');
    const status = document.getElementById('profileFaceStatus');
    const faceState = document.getElementById('faceState');
    let stream = null;

    function setStatus(message, isError) {
        status.textContent = message;
        status.style.color = isError ? '#b00020' : '#2e7d32';
    }

    async function startCamera() {
        panel.style.display = 'block';
        setStatus('Activation de la camera...', false);

        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            video.srcObject = stream;
            setStatus('Cadrez votre visage puis capturez.', false);
        } catch (error) {
            setStatus("Impossible d'acceder a la camera.", true);
        }
    }

    async function saveFace() {
        if (!video.videoWidth) {
            setStatus('Camera pas encore prete.', true);
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const faceImage = canvas.toDataURL('image/jpeg', 0.9);
        setStatus('Analyse du visage...', false);

        try {
            const response = await fetch('index.php?action=' + (hasFace ? 'update-face' : 'add-face'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ face_image: faceImage })
            });
            const result = await response.json();

            if (result.success) {
                faceState.textContent = 'enregistre';
                setStatus(result.message || 'Face ID enregistre.', false);
                setTimeout(function() { window.location.reload(); }, 800);
                return;
            }

            setStatus(result.error || "Impossible d'enregistrer Face ID.", true);
        } catch (error) {
            setStatus('Erreur pendant l_enregistrement Face ID.', true);
        }
    }

    startBtn.addEventListener('click', startCamera);
    saveBtn.addEventListener('click', saveFace);

    window.addEventListener('beforeunload', function() {
        if (stream) {
            stream.getTracks().forEach(function(track) { track.stop(); });
        }
    });
})();
</script>
</body>
</html>

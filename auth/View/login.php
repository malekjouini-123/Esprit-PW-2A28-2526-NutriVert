<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Nutrivert</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{
            font-family:'Quicksand',sans-serif;
            background: linear-gradient(135deg, #FFF9F0 0%, #E8F5E9 100%);
            min-height:100vh;
            display:flex; align-items:center; justify-content:center;
        }
        .card{
            background:#fff;
            border-radius:32px;
            padding:44px 40px;
            width:100%; max-width:460px;
            box-shadow:0 24px 48px -12px rgba(0,0,0,.12);
        }
        .logo{
            text-align:center;
            margin-bottom:28px;
        }
        .logo i{ font-size:2.4rem; color:#FF7E67; }
        .logo span{
            display:block;
            font-size:1.8rem; font-weight:700;
            background:linear-gradient(135deg,#FF7E67,#7DCFB6);
            -webkit-background-clip:text; background-clip:text; color:transparent;
        }
        h2{ text-align:center; font-size:1.3rem; color:#4A6B4A; margin-bottom:22px; }

        .flash{
            background:#FFF0EE; border:1px solid #FF7E67;
            color:#c0392b; border-radius:16px;
            padding:10px 16px; font-size:.9rem;
            margin-bottom:18px; text-align:center;
        }

        label{ display:block; font-weight:600; color:#4A5B4A; margin-bottom:6px; font-size:.9rem; }
        input[type=email], input[type=password]{
            width:100%; padding:12px 18px;
            border:2px solid #E8F0E8; border-radius:40px;
            font-family:inherit; font-size:1rem;
            outline:none; transition:.2s;
            margin-bottom:18px;
        }
        input:focus{ border-color:#7DCFB6; }

        .btn{
            width:100%; padding:13px;
            border:none; border-radius:40px;
            background:linear-gradient(135deg,#FF7E67,#FFB347);
            color:#fff; font-family:inherit; font-size:1rem; font-weight:700;
            cursor:pointer; transition:.2s;
        }
        .btn:hover{ transform:translateY(-2px); box-shadow:0 8px 20px rgba(255,126,103,.35); }

        .divider{ text-align:center; color:#aaa; margin:18px 0; font-size:.9rem; }

        .link-btn{
            display:block; text-align:center;
            padding:11px;
            border:2px solid #7DCFB6; border-radius:40px;
            color:#4A6B4A; font-weight:700;
            text-decoration:none; transition:.2s;
        }
        .link-btn:hover{ background:#7DCFB6; color:#fff; }

        .back{ text-align:center; margin-top:16px; }
        .back a{ color:#aaa; font-size:.85rem; text-decoration:none; }
        .back a:hover{ color:#FF7E67; }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">
        <i class="fas fa-leaf"></i>
        <span>Nutrivert</span>
    </div>
    <h2>Connexion</h2>

    <?php if (!empty($flashMessage)): ?>
        <div class="flash"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?controller=user&action=doLogin">
        <label for="email"><i class="fas fa-envelope"></i> Email</label>
        <input type="email" id="email" name="email" placeholder="votre@email.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>

        <button type="submit" class="btn">Se connecter</button>
    </form>

    <div style="text-align:center;margin-top:6px;margin-bottom:16px;">
        <a href="index.php?view=forgot-password" style="color:#7DCFB6;font-size:.85rem;text-decoration:none;">
            <i class="fas fa-key"></i> Mot de passe oublié ?
        </a>
    </div>

    <div class="divider">— ou —</div>

    <!-- Face ID login -->
    <div id="faceIdSection" style="margin-bottom:16px;">
        <button type="button" onclick="toggleFaceId()"
                style="width:100%;padding:13px;border:2px solid #7DCFB6;border-radius:40px;
                       background:transparent;color:#4A6B4A;font-family:inherit;font-size:1rem;
                       font-weight:700;cursor:pointer;transition:.2s;"
                onmouseover="this.style.background='#7DCFB6';this.style.color='#fff'"
                onmouseout="this.style.background='transparent';this.style.color='#4A6B4A'">
            <i class="fas fa-camera"></i> Connexion par Face ID
        </button>

        <div id="faceIdContainer" style="display:none;margin-top:14px;">
            <input type="email" id="faceEmail" placeholder="Votre email" required
                   style="width:100%;padding:12px 18px;border:2px solid #E8F0E8;border-radius:40px;
                          font-family:inherit;font-size:1rem;outline:none;margin-bottom:10px;">
            <video id="faceVideo" autoplay playsinline
                   style="width:100%;border-radius:16px;background:#000;display:none;"></video>
            <canvas id="faceCanvas" style="display:none;"></canvas>
            <div id="faceStatus" style="text-align:center;color:#888;font-size:.85rem;margin:8px 0;"></div>
            <button type="button" id="faceBtn" onclick="startFaceLogin()"
                    style="width:100%;padding:11px;border:none;border-radius:40px;
                           background:linear-gradient(135deg,#7DCFB6,#4A6B4A);
                           color:#fff;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;">
                <i class="fas fa-camera"></i> Activer la caméra
            </button>
        </div>
    </div>

    <div class="divider">— ou —</div>

    <a href="index.php?view=register" class="link-btn">
        <i class="fas fa-user-plus"></i> Créer un compte
    </a>

    <div class="back">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    </div>
</div>

<script>
let faceStream = null;
let faceLoginActive = false;

function toggleFaceId() {
    const c = document.getElementById('faceIdContainer');
    c.style.display = c.style.display === 'none' ? 'block' : 'none';
    if (c.style.display === 'none' && faceStream) {
        faceStream.getTracks().forEach(t => t.stop());
        faceStream = null;
        faceLoginActive = false;
        document.getElementById('faceVideo').style.display = 'none';
        document.getElementById('faceBtn').textContent = 'Activer la caméra';
        document.getElementById('faceBtn').onclick = startFaceLogin;
    }
}

async function startFaceLogin() {
    const email = document.getElementById('faceEmail').value.trim();
    if (!email) {
        document.getElementById('faceStatus').textContent = 'Veuillez entrer votre email.';
        return;
    }

    const video = document.getElementById('faceVideo');
    const status = document.getElementById('faceStatus');
    const btn = document.getElementById('faceBtn');

    try {
        faceStream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = faceStream;
        video.style.display = 'block';
        status.textContent = 'Caméra activée. Regardez la caméra puis cliquez "Capturer".';
        btn.innerHTML = '<i class="fas fa-circle"></i> Capturer et se connecter';
        btn.onclick = captureAndLogin;
    } catch (e) {
        status.textContent = "Impossible d'accéder à la caméra : " + e.message;
    }
}

async function captureAndLogin() {
    const email = document.getElementById('faceEmail').value.trim();
    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceCanvas');
    const status = document.getElementById('faceStatus');
    const btn = document.getElementById('faceBtn');

    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

    if (faceStream) {
        faceStream.getTracks().forEach(t => t.stop());
        faceStream = null;
    }
    video.style.display = 'none';

    status.textContent = 'Vérification en cours…';
    btn.disabled = true;

    try {
        const resp = await fetch('index.php?controller=face-login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, face_image: dataUrl })
        });
        const data = await resp.json();

        if (data.success) {
            status.style.color = '#2e7d32';
            status.textContent = 'Visage reconnu ! Redirection…';
            window.location.href = data.redirect;
        } else {
            status.style.color = '#c0392b';
            status.textContent = data.error || 'Visage non reconnu.';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-camera"></i> Réessayer';
            btn.onclick = startFaceLogin;
        }
    } catch (e) {
        status.style.color = '#c0392b';
        status.textContent = 'Erreur réseau : ' + e.message;
        btn.disabled = false;
    }
}
</script>

</body>
</html>
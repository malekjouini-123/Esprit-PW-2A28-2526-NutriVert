<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email envoyé — Nutrivert</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{
            font-family:'Quicksand',sans-serif;
            background:linear-gradient(135deg,#FFF9F0 0%,#E8F5E9 100%);
            min-height:100vh; display:flex; align-items:center; justify-content:center;
        }
        .card{
            background:#fff; border-radius:32px; padding:44px 40px;
            width:100%; max-width:480px; text-align:center;
            box-shadow:0 24px 48px -12px rgba(0,0,0,.12);
        }
        .icon{ font-size:4rem; color:#7DCFB6; margin-bottom:20px; }
        h2{ font-size:1.5rem; color:#4A6B4A; margin-bottom:12px; }
        p{ color:#666; margin-bottom:24px; line-height:1.6; }
        .btn{
            display:inline-block; padding:13px 32px; border-radius:40px;
            background:linear-gradient(135deg,#FF7E67,#FFB347);
            color:#fff; font-weight:700; text-decoration:none; font-size:1rem;
            transition:.2s;
        }
        .btn:hover{ transform:translateY(-2px); box-shadow:0 8px 20px rgba(255,126,103,.35); }
    </style>
</head>
<body>
<div class="card">
    <div class="icon"><i class="fas fa-envelope-open-text"></i></div>
    <h2>Email envoyé !</h2>
    <p>
        Un lien de réinitialisation a été envoyé à<br>
        <strong><?= htmlspecialchars($email ?? '') ?></strong>.<br><br>
        Vérifiez votre boîte mail (et les spams). Le lien expire dans <strong>1 heure</strong>.
    </p>
    <?php if (!($emailSent ?? true)): ?>
        <p style="color:#FF7E67;font-size:.85rem;">
            <i class="fas fa-exclamation-triangle"></i>
            L'envoi d'email a échoué. Vérifiez la configuration dans <code>config/.env</code>.
        </p>
    <?php endif; ?>
    <a href="index.php?view=login" class="btn"><i class="fas fa-sign-in-alt"></i> Retour à la connexion</a>
</div>
</body>
</html>

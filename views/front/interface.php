<?php
// =============================================================================
//  interface.php — Page d'accueil Nutrivert
//  - Bouton "Connexion" → modal login (email + mdp)
//  - Login via AJAX → vérifie en BDD → session PHP → retour JSON
//  - Si role=user  : affiche profil (poids, taille, IMC, calories)
//  - Si role=coach : affiche lien vers ses programmes de coaching
// =============================================================================

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/database.php';

// ── Traitement AJAX login ──────────────────────────────────────────────────
if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' &&
    ($_POST['ajax_action'] ?? '') === 'login'
) {
    header('Content-Type: application/json');

    $email    = strtolower(trim((string)($_POST['email']    ?? '')));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        echo json_encode(['ok' => false, 'msg' => 'Veuillez remplir tous les champs.']);
        exit;
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($password, $row['password'])) {
        echo json_encode(['ok' => false, 'msg' => 'Email ou mot de passe incorrect.']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'        => (int)$row['id'],
        'nom'       => $row['nom'],
        'email'     => $row['email'],
        'role'      => $row['role'],
        'poids'     => $row['poids'],
        'taille'    => $row['taille'],
        'imc'       => $row['imc'],
        'calories'  => $row['calories'],
        'objectif'  => $row['objectif'] ?? 'maintien',
        'specialite'=> $row['specialite'] ?? null,
    ];

    // Interprétation IMC
    $imc = (float)$row['imc'];
    $imcInterpret = $imc < 18.5 ? 'Insuffisance pondérale'
        : ($imc < 25 ? 'Poids normal ✅'
        : ($imc < 30 ? 'Surpoids' : 'Obésité'));

    // Redirection selon le rôle
    $redirect = $row['role'] === 'coach'
        ? 'index.php?controller=coaching&action=index&coach_id=' . (int)$row['id']
        : null; // reste sur la même page, on rechargera la section profil

    echo json_encode([
        'ok'          => true,
        'role'        => $row['role'],
        'nom'         => $row['nom'],
        'poids'       => $row['poids'],
        'taille'      => $row['taille'],
        'imc'         => $row['imc'],
        'imcInterpret'=> $imcInterpret,
        'calories'    => $row['calories'],
        'objectif'    => $row['objectif'] ?? 'maintien',
        'specialite'  => $row['specialite'] ?? '',
        'redirect'    => $redirect,
    ]);
    exit;
}

// ── Déconnexion ───────────────────────────────────────────────────────────
if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ── Utilisateur connecté ? ────────────────────────────────────────────────
$user = $_SESSION['user'] ?? null;

// Interprétation IMC si connecté
$imcInterpret = '';
if ($user && $user['imc']) {
    $imc = (float)$user['imc'];
    $imcInterpret = $imc < 18.5 ? 'Insuffisance pondérale'
        : ($imc < 25 ? 'Poids normal ✅'
        : ($imc < 30 ? 'Surpoids' : 'Obésité'));
}

$objLabels = ['perte' => 'Perte de poids', 'maintien' => 'Maintien', 'muscle' => 'Prise de muscle'];
$objLabel  = $objLabels[$user['objectif'] ?? 'maintien'] ?? 'Maintien';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrivert ✿ Nutrition Intelligente & Durable</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        :root {
            --coral:   #FF7E67;
            --mint:    #7DCFB6;
            --lavender:#C8A2F0;
            --sunset:  #FFB347;
            --sage:    #A3C4A2;
            --deep:    #4A6B4A;
            --bg:      #FFF9F0;
            --white:   #FFFFFF;
            --shadow:  0 20px 40px -12px rgba(0,0,0,.10);
            --radius:  32px;
        }

        body {
            font-family: 'Quicksand', sans-serif;
            background: var(--bg);
            color: #2D3E2B;
            scroll-behavior: smooth;
        }

        .container { max-width: 1280px; margin: 0 auto; padding: 0 30px; }

        /* ── NAVBAR ── */
        .navbar {
            display: flex; justify-content: space-between;
            align-items: center; padding: 22px 0; flex-wrap: wrap; gap: 12px;
        }
        .logo { display: flex; align-items: center; gap: 10px; }
        .logo i { font-size: 2.2rem; color: var(--coral); }
        .logo span {
            font-size: 1.9rem; font-weight: 700;
            background: linear-gradient(135deg, #FF7E67, #7DCFB6);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .nav-links { display: flex; gap: 18px; align-items: center; flex-wrap: wrap; }
        .nav-links a {
            text-decoration: none; font-weight: 600; color: #4A5B4A; transition: .2s;
        }
        .nav-links a:hover { color: var(--coral); }

        /* ── BOUTONS NAVBAR ── */
        .btn-login {
            padding: 8px 22px; border-radius: 40px; font-weight: 700; cursor: pointer;
            border: 2px solid var(--mint); background: transparent; color: var(--deep);
            font-family: inherit; font-size: .95rem; transition: .2s;
        }
        .btn-login:hover { background: var(--mint); color: white; }
        .btn-signup {
            padding: 8px 22px; border-radius: 40px; font-weight: 700; cursor: pointer;
            border: none; background: linear-gradient(135deg, var(--coral), var(--sunset));
            color: white; font-family: inherit; font-size: .95rem; transition: .2s;
            text-decoration: none; display: inline-block;
        }
        .btn-signup:hover { transform: translateY(-2px); }

        /* ── BANNIÈRE CONNECTÉ ── */
        .user-banner {
            background: linear-gradient(135deg, var(--mint), var(--sage));
            border-radius: 60px; padding: 13px 26px;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 10px; margin-bottom: 18px;
        }
        .user-info { display: flex; align-items: center; gap: 12px; font-weight: 600; }
        .user-info i { font-size: 1.4rem; }
        .user-stat {
            background: white; padding: 4px 14px; border-radius: 40px;
            font-size: .88rem; color: var(--deep);
        }
        .logout-btn {
            background: white; border: none; padding: 7px 20px;
            border-radius: 40px; font-weight: 700; color: var(--coral);
            cursor: pointer; font-family: inherit; transition: .2s;
        }
        .logout-btn:hover { background: var(--coral); color: white; }

        /* ── BANNIÈRE NON CONNECTÉ ── */
        .guest-banner {
            text-align: center; padding: 22px 20px;
            background: linear-gradient(135deg, #E8F5E9, #FFF0EE);
            border-radius: 60px; margin-bottom: 18px;
        }
        .guest-banner p { margin-bottom: 14px; font-weight: 600; color: var(--deep); font-size: 1.05rem; }
        .guest-banner .btn-login { margin-right: 10px; }

        /* ── HERO ── */
        .hero { text-align: center; margin: 36px 0 28px; }
        .hero h1 {
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            background: linear-gradient(120deg, #FF7E67, #C8A2F0, #7DCFB6);
            -webkit-background-clip: text; background-clip: text; color: transparent;
            line-height: 1.3;
        }
        .hero p { color: #7A8B7A; margin-top: 10px; font-size: 1.05rem; }

        /* ── SECTION CARDS ── */
        .section-card {
            background: var(--white); border-radius: var(--radius);
            padding: 30px; margin: 32px 0; box-shadow: var(--shadow);
        }
        .section-title {
            font-size: 1.4rem; font-weight: 700;
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 18px; color: var(--deep);
        }
        .section-title i { color: var(--coral); }

        /* ── PROFIL USER ── */
        .profil-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px; margin-bottom: 18px;
        }
        .info-badge {
            background: #FEF7E8; border-radius: 22px;
            padding: 14px 16px; font-weight: 600; font-size: .92rem;
            display: flex; flex-direction: column; gap: 4px;
        }
        .info-badge span { font-size: 1.15rem; color: var(--coral); }
        .edit-btn {
            background: var(--mint); border: none;
            padding: 10px 26px; border-radius: 60px;
            font-weight: 700; cursor: pointer; font-family: inherit;
            transition: .2s; font-size: .95rem;
        }
        .edit-btn:hover { background: var(--deep); color: white; }

        /* ── PROFIL COACH ── */
        .coach-card {
            background: linear-gradient(135deg, #F3EAFF, #E8F0FF);
            border-radius: 28px; padding: 24px; margin-bottom: 18px;
        }
        .coach-card h3 { color: #6A3B9A; font-size: 1.1rem; margin-bottom: 8px; }
        .coach-card p  { color: #5A4A7A; font-size: .92rem; line-height: 1.6; }
        .coach-link {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #C8A2F0, #7DCFB6);
            color: white; text-decoration: none;
            padding: 11px 28px; border-radius: 40px;
            font-weight: 700; transition: .2s; margin-top: 12px;
        }
        .coach-link:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(200,162,240,.4); }

        /* ── RECETTE IA ── */
        .ingredient-group { display: flex; gap: 12px; flex-wrap: wrap; margin: 18px 0; }
        .ingredient-group input {
            flex: 3; padding: 12px 20px; border-radius: 60px;
            border: 2px solid #FFD5C2; font-family: inherit; font-size: .97rem; outline: none;
            transition: .2s;
        }
        .ingredient-group input:focus { border-color: var(--coral); }
        .btn-primary {
            background: var(--sunset); border: none; padding: 12px 26px;
            border-radius: 60px; font-weight: 700; cursor: pointer;
            font-family: inherit; font-size: .95rem; transition: .2s; color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); }
        .recette-card {
            background: #FFF9EF; border-radius: 22px;
            padding: 20px; margin-top: 16px;
            border-left: 6px solid var(--coral);
            display: none; line-height: 1.7;
        }

        /* ── FOOTER ── */
        footer {
            text-align: center; padding: 35px;
            color: #8A9A86; border-top: 2px dashed #FFD9C5; margin-top: 50px;
        }

        /* ── MODAL OVERLAY ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.45); z-index: 1000;
            justify-content: center; align-items: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: white; border-radius: 32px;
            padding: 40px 36px; width: 100%; max-width: 420px;
            box-shadow: 0 32px 64px -16px rgba(0,0,0,.22);
            animation: modalIn .25s ease;
        }
        @keyframes modalIn {
            from { transform: translateY(20px) scale(.97); opacity: 0; }
            to   { transform: translateY(0)    scale(1);   opacity: 1; }
        }

        .modal-box h2 {
            text-align: center; font-size: 1.35rem;
            color: var(--deep); margin-bottom: 24px;
        }
        .modal-box h2 i { color: var(--coral); margin-right: 8px; }

        .modal-error {
            background: #FFF0EE; border: 1px solid var(--coral);
            color: #c0392b; border-radius: 14px;
            padding: 10px 14px; font-size: .88rem;
            margin-bottom: 16px; display: none; text-align: center;
        }

        .modal-field { margin-bottom: 16px; }
        .modal-field label {
            display: block; font-weight: 600;
            color: #4A5B4A; margin-bottom: 6px; font-size: .88rem;
        }
        .modal-field input {
            width: 100%; padding: 12px 18px; border-radius: 40px;
            border: 2px solid #E8F0E8; font-family: inherit;
            font-size: .97rem; outline: none; transition: .2s;
        }
        .modal-field input:focus { border-color: var(--mint); }

        .modal-btn {
            width: 100%; padding: 13px; border: none; border-radius: 40px;
            background: linear-gradient(135deg, var(--coral), var(--sunset));
            color: white; font-family: inherit; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: .2s; position: relative;
        }
        .modal-btn:hover:not(:disabled) { transform: translateY(-2px); }
        .modal-btn:disabled { opacity: .65; cursor: default; }

        .modal-footer {
            text-align: center; margin-top: 16px;
            font-size: .88rem; color: #888;
        }
        .modal-footer a { color: var(--mint); font-weight: 600; text-decoration: none; }
        .modal-footer a:hover { color: var(--coral); }

        /* Spinner */
        .spinner {
            display: inline-block; width: 16px; height: 16px;
            border: 3px solid rgba(255,255,255,.4);
            border-top-color: white; border-radius: 50%;
            animation: spin .6s linear infinite; vertical-align: middle;
            margin-right: 6px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="container">

    <!-- ── NAVBAR ── -->
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-seedling"></i>
            <span>Nutrivert</span>
        </div>
        <div class="nav-links">
            <a href="#">Accueil</a>
            <a href="#profilSec">Profil</a>
            <a href="#iaSec">Recettes</a>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'coach'): ?>
                    <a href="index.php?controller=coaching&action=index"
                       style="color:var(--lavender); font-weight:700;">
                        <i class="fas fa-dumbbell"></i> Mes programmes
                    </a>
                <?php endif; ?>
                <a href="index.php?controller=user&action=logout" class="btn-login" style="color:var(--coral); border-color:var(--coral);">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            <?php else: ?>
                <button class="btn-login" onclick="location.href='index.php?view=login'">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </button>
                <a href="index.php?view=register" class="btn-signup">
                    <i class="fas fa-user-plus"></i> Inscription
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- ── ZONE CONNECTÉ / VISITEUR ── -->
    <?php if ($user): ?>

        <div class="user-banner">
            <div class="user-info">
                <i class="fas fa-<?= $user['role'] === 'coach' ? 'dumbbell' : 'user-circle' ?>"></i>
                <span>Bonjour, <strong><?= htmlspecialchars($user['nom']) ?></strong> 👋</span>
                <?php if ($user['role'] === 'user' && $user['imc']): ?>
                    <span class="user-stat">📊 IMC <?= htmlspecialchars((string)$user['imc']) ?></span>
                    <span class="user-stat">🔥 <?= htmlspecialchars((string)$user['calories']) ?> kcal/j</span>
                <?php elseif ($user['role'] === 'coach'): ?>
                    <span class="user-stat" style="background:#F3EAFF; color:#6A3B9A;">
                        🏅 Coach
                        <?php if ($user['specialite']): ?>
                            — <?= htmlspecialchars($user['specialite']) ?>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <a href="?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>

    <?php else: ?>

        <div class="guest-banner">
            <p>👋 Rejoignez Nutrivert pour des recettes personnalisées !</p>
            <button class="btn-login" onclick="openLoginModal()">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </button>
            <a href="index.php?controller=user&action=register" class="btn-signup">
                <i class="fas fa-user-plus"></i> Inscription
            </a>
        </div>

    <?php endif; ?>

    <!-- ── HERO ── -->
    <div class="hero">
        <h1>✨ Manger malin, zéro gaspillage ✨</h1>
        <p>Nutrivert — intelligence nutritionnelle & marketplace durable</p>
    </div>

    <!-- ── SECTION PROFIL ── -->
    <div class="section-card" id="profilSec">
        <div class="section-title">
            <i class="fas fa-user-astronaut"></i>
            <span>Mon profil nutritionnel</span>
        </div>

        <?php if (!$user): ?>
            <!-- Non connecté -->
            <p style="color:#888; text-align:center; padding:20px 0;">
                <i class="fas fa-lock" style="color:var(--coral); font-size:1.4rem;"></i><br><br>
                Connectez-vous pour voir votre profil nutritionnel personnalisé.
            </p>
            <div style="text-align:center;">
                <button class="btn-login" onclick="openLoginModal()">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </div>

        <?php elseif ($user['role'] === 'user'): ?>
            <!-- Profil USER : poids, taille, IMC, calories -->
            <div class="profil-grid">
                <div class="info-badge">
                    ⚖️ Poids
                    <span><?= $user['poids'] ? htmlspecialchars((string)$user['poids']) . ' kg' : '—' ?></span>
                </div>
                <div class="info-badge">
                    📏 Taille
                    <span><?= $user['taille'] ? htmlspecialchars((string)$user['taille']) . ' cm' : '—' ?></span>
                </div>
                <div class="info-badge">
                    📊 IMC
                    <span><?= $user['imc'] ? htmlspecialchars((string)$user['imc']) : '—' ?></span>
                    <?php if ($imcInterpret): ?>
                        <small style="font-weight:500; color:#7A8B7A; font-size:.78rem;"><?= htmlspecialchars($imcInterpret) ?></small>
                    <?php endif; ?>
                </div>
                <div class="info-badge">
                    🔥 Calories
                    <span><?= $user['calories'] ? htmlspecialchars((string)$user['calories']) . ' kcal/j' : '—' ?></span>
                </div>
                <div class="info-badge">
                    🎯 Objectif
                    <span style="font-size:.95rem;"><?= htmlspecialchars($objLabel) ?></span>
                </div>
            </div>
            <button class="edit-btn" onclick="alert('Fonctionnalité de modification de profil à implémenter.')">
                ✏️ Modifier mon profil
            </button>

        <?php else: ?>
            <!-- Profil COACH -->
            <div class="coach-card">
                <h3><i class="fas fa-dumbbell"></i> Espace Coach</h3>
                <p>
                    Bienvenue <strong><?= htmlspecialchars($user['nom']) ?></strong> !
                    <?php if ($user['specialite']): ?>
                        Votre spécialité : <strong><?= htmlspecialchars($user['specialite']) ?></strong>.
                    <?php endif; ?>
                    <br>Gérez vos programmes de coaching depuis votre espace dédié.
                </p>
                <a href="index.php?controller=coaching&action=index&coach_id=<?= $user['id'] ?>"
                   class="coach-link">
                    <i class="fas fa-list-ul"></i> Voir mes programmes de coaching
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── SECTION RECETTES IA ── -->
    <div class="section-card" id="iaSec">
        <div class="section-title">
            <i class="fas fa-robot"></i>
            <span>🤖 IA Anti-gaspillage</span>
        </div>
        <p>Tape tes ingrédients → recette personnalisée</p>
        <div class="ingredient-group">
            <input type="text" id="ingredientsField" placeholder="ex: courgette, tomates, oeufs, riz">
            <button class="btn-primary" onclick="generateRecipe()">Générer recette</button>
        </div>
        <div id="recipeResult" class="recette-card"></div>
    </div>

    <footer>
        <i class="fas fa-leaf"></i> Nutrivert – Nutrition intelligente & durable
    </footer>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!--  MODAL CONNEXION                                               -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="loginModal" onclick="handleOverlayClick(event)">
    <div class="modal-box">
        <h2><i class="fas fa-lock"></i> Connexion</h2>

        <div class="modal-error" id="loginError"></div>

        <div class="modal-field">
            <label for="loginEmail"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" id="loginEmail" placeholder="votre@email.com"
                   onkeydown="if(event.key==='Enter') doLogin()">
        </div>
        <div class="modal-field">
            <label for="loginPassword"><i class="fas fa-lock"></i> Mot de passe</label>
            <input type="password" id="loginPassword" placeholder="••••••••"
                   onkeydown="if(event.key==='Enter') doLogin()">
        </div>

        <button class="modal-btn" id="loginBtn" onclick="doLogin()">
            <i class="fas fa-sign-in-alt"></i> Se connecter
        </button>

        <div class="modal-footer">
            Pas encore de compte ?
            <a href="index.php?controller=user&action=register">Créer un compte</a>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!--  JAVASCRIPT                                                    -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<script>
    // ── Modal ─────────────────────────────────────────────────────
    function openLoginModal() {
        document.getElementById('loginModal').classList.add('open');
        setTimeout(() => document.getElementById('loginEmail').focus(), 150);
    }
    function closeLoginModal() {
        document.getElementById('loginModal').classList.remove('open');
        document.getElementById('loginError').style.display = 'none';
    }
    function handleOverlayClick(e) {
        if (e.target.id === 'loginModal') closeLoginModal();
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLoginModal();
    });

    // ── Login AJAX ────────────────────────────────────────────────
    function doLogin() {
        const email    = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;
        const btn      = document.getElementById('loginBtn');
        const errDiv   = document.getElementById('loginError');

        errDiv.style.display = 'none';

        if (!email || !password) {
            showError('Veuillez remplir tous les champs.');
            return;
        }

        // Spinner
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Connexion…';

        const formData = new FormData();
        formData.append('ajax_action', 'login');
        formData.append('email', email);
        formData.append('password', password);

        fetch(window.location.pathname, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) {
                showError(data.msg);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
                return;
            }

            // Connexion réussie
            closeLoginModal();

            if (data.redirect) {
                // Coach → page coaching
                window.location.href = data.redirect;
            } else {
                // User → recharger la page (session PHP active)
                window.location.reload();
            }
        })
        .catch(() => {
            showError('Erreur réseau. Réessayez.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
        });
    }

    function showError(msg) {
        const e = document.getElementById('loginError');
        e.textContent = '⚠️ ' + msg;
        e.style.display = 'block';
    }

    // ── Recette IA ────────────────────────────────────────────────
    function generateRecipe() {
        <?php if (!$user): ?>
            openLoginModal();
            return;
        <?php endif; ?>

        const ingredients = document.getElementById('ingredientsField').value.trim();
        if (!ingredients) { alert('🍅 Entrez vos ingrédients !'); return; }

        const result = document.getElementById('recipeResult');
        <?php if ($user): ?>
        const nom     = <?= json_encode($user['nom']) ?>;
        const objectif= <?= json_encode($objLabel) ?>;
        const imc     = <?= json_encode($user['imc'] ?? '') ?>;
        const interpret = <?= json_encode($imcInterpret) ?>;
        const role    = <?= json_encode($user['role']) ?>;

        let html = `✨ <strong>Recette personnalisée pour ${nom}</strong><br>
        🥗 Ingrédients : ${ingredients}<br>`;

        if (role === 'user' && imc) {
            html += `📊 Profil : ${objectif} | IMC ${imc} (${interpret})<br><br>`;
        } else {
            html += `<br>`;
        }

        html += `🧑‍🍳 <strong>Idée recette :</strong> Salade équilibrée — mélangez vos ingrédients avec une vinaigrette légère.<br>
        🎯 Astuce : `;

        <?php if ($user['role'] === 'user'): ?>
            const obj = <?= json_encode($user['objectif'] ?? 'maintien') ?>;
            if      (obj === 'perte')  html += 'Privilégiez les légumes verts et réduisez les féculents.';
            else if (obj === 'muscle') html += 'Ajoutez une source de protéines (œufs, pois chiches, tofu).';
            else                       html += 'Maintenez un bon équilibre glucides/lipides.';
        <?php else: ?>
            html += 'Adaptez les portions à l\'objectif de votre client.';
        <?php endif; ?>

        result.innerHTML = html;
        result.style.display = 'block';
        <?php endif; ?>
    }
</script>

</body>
</html>

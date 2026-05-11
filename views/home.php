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

require_once __DIR__ . '/../config/database.php';

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
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($password, $row['mot_de_passe'])) {
        echo json_encode(['ok' => false, 'msg' => 'Email ou mot de passe incorrect.']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'         => (int)$row['id_utilisateur'],
        'nom'        => $row['nom'],
        'prenom'     => $row['prenom'] ?? null,
        'email'      => $row['email'],
        'role'       => $row['role'],
        'poids'      => $row['poids'],
        'taille'     => $row['taille'],
        'age'        => $row['age'] ?? null,
        'sexe'       => $row['sexe'] ?? null,
        'imc'        => $row['imc'],
        'calories'   => $row['calories'],
        'objectif'   => $row['objectif'] ?? 'maintien',
        'specialite' => $row['specialite'] ?? null,
        'bio'        => $row['bio'] ?? null,
    ];

    // Interprétation IMC
    $imc = (float)$row['imc'];
    $imcInterpret = $imc < 18.5 ? 'Insuffisance pondérale'
        : ($imc < 25 ? 'Poids normal ✅'
        : ($imc < 30 ? 'Surpoids' : 'Obésité'));

    // Tout le monde retourne à la page d'accueil après connexion
    $redirect = null;

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

// ── Utilisateur connecté ? ────────────────────────────────────────────────
$user = $_SESSION['user'] ?? null;

// ── Coaching programs for coach home page ────────────────────────────────
$coachPrograms = [];
if ($user && $user['role'] === 'coach') {
    $pdo = getDB();
    $stmt = $pdo->query(
        'SELECT cp.*, COUNT(e.id) AS exercise_count
         FROM coaching_programs cp
         LEFT JOIN exercises e ON e.coaching_id = cp.id
         GROUP BY cp.id
         ORDER BY cp.created_at DESC
         LIMIT 6'
    );
    $coachPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

        /* ── TABLEAU DE BORD COACHING (user) ── */
        .coaching-dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px; margin-bottom: 10px;
        }
        .coaching-action-card {
            background: linear-gradient(135deg, #F3EAFF, #E8F0FF);
            border-radius: 22px; padding: 22px 20px;
            display: flex; flex-direction: column; gap: 10px;
            text-decoration: none; color: inherit; transition: .2s;
        }
        .coaching-action-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(200,162,240,.25); }
        .coaching-action-card i { font-size: 1.8rem; color: var(--lavender); }
        .coaching-action-card h4 { font-size: 1rem; font-weight: 700; color: #4A3B7A; margin: 0; }
        .coaching-action-card p { font-size: .85rem; color: #7A6A9A; margin: 0; }

        /* ── PROGRAMMES COACH (home) ── */
        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 14px; margin-top: 14px;
        }
        .program-card {
            background: linear-gradient(135deg, #F3EAFF, #E8F0FF);
            border-radius: 20px; padding: 18px;
            border-left: 4px solid var(--lavender);
        }
        .program-card h4 { color: #4A3B7A; font-size: 1rem; margin-bottom: 6px; }
        .program-card .program-meta { font-size: .82rem; color: #8A7AB0; margin-bottom: 10px; }
        .program-card .program-meta span { margin-right: 10px; }
        .difficulty-badge {
            display: inline-block; padding: 2px 10px; border-radius: 20px;
            font-size: .78rem; font-weight: 700;
        }
        .difficulty-easy   { background: #E8F5E9; color: #2E7D32; }
        .difficulty-medium { background: #FFF3E0; color: #E65100; }
        .difficulty-hard   { background: #FFEBEE; color: #C62828; }

        /* ── TOAST GREETING ── */
        #greetingToast {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%) translateY(-80px);
            background: linear-gradient(135deg, var(--mint), var(--sage));
            color: white; padding: 14px 28px; border-radius: 50px;
            font-weight: 700; font-size: 1rem; z-index: 9999;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
            transition: transform .4s ease, opacity .4s ease;
            opacity: 0; white-space: nowrap;
        }
        #greetingToast.show { transform: translateX(-50%) translateY(0); opacity: 1; }

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
            <a href="index.php">Accueil</a>
            <a href="#profilSec">Profil</a>
            <a href="#iaSec">Recettes</a>
            <a href="produit/front.html">Produit</a>
            <a href="evnemnet/index.php">Evénement</a>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="index.php?controller=dashboard&action=index"
                       style="color:var(--coral); font-weight:700;">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                <?php elseif ($user['role'] === 'coach'): ?>
                    <a href="index.php?controller=coaching&action=index"
                       style="color:var(--lavender); font-weight:700;">
                        <i class="fas fa-dumbbell"></i> Mes programmes
                    </a>
                <?php else: ?>
                    <a href="#coachingSec" style="color:var(--mint); font-weight:700;">
                        <i class="fas fa-running"></i> Coaching
                    </a>
                <?php endif; ?>
                <a href="index.php?controller=user&action=logout" class="btn-login" style="color:var(--coral); border-color:var(--coral);">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            <?php else: ?>
                <button class="btn-login" onclick="openLoginModal()">
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
            <a href="index.php?controller=user&action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>

    <?php else: ?>

        <div class="guest-banner">
            <p>👋 Rejoignez Nutrivert pour des recettes personnalisées !</p>
            <button class="btn-login" onclick="openLoginModal()">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </button>
            <a href="index.php?view=register" class="btn-signup">
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

        <?php else: ?>
            <!-- Profil COACH -->
            <div class="coach-card">
                <h3><i class="fas fa-dumbbell"></i> Espace Coach</h3>
                <p>
                    Bienvenue <strong><?= htmlspecialchars($user['nom']) ?></strong> !
                    <?php if ($user['specialite']): ?>
                        Spécialité : <strong><?= htmlspecialchars($user['specialite']) ?></strong>.
                    <?php endif; ?>
                </p>
                <a href="index.php?controller=coaching&action=index" class="coach-link">
                    <i class="fas fa-cog"></i> Gérer mes programmes
                </a>
            </div>

            <?php if (!empty($coachPrograms)): ?>
                <div style="margin-top:10px;">
                    <strong style="color:var(--deep); font-size:.95rem;">
                        <i class="fas fa-list-ul" style="color:var(--lavender);"></i>
                        Mes programmes (<?= count($coachPrograms) ?>)
                    </strong>
                    <div class="programs-grid">
                        <?php foreach ($coachPrograms as $prog): ?>
                            <div class="program-card">
                                <h4><?= htmlspecialchars($prog['title']) ?></h4>
                                <div class="program-meta">
                                    <span><i class="fas fa-calendar-alt"></i> <?= (int)$prog['duration_weeks'] ?> sem.</span>
                                    <span><i class="fas fa-running"></i> <?= (int)$prog['exercise_count'] ?> exercice(s)</span>
                                </div>
                                <span class="difficulty-badge difficulty-<?= htmlspecialchars($prog['difficulty_level']) ?>">
                                    <?= match($prog['difficulty_level']) {
                                        'easy'   => 'Facile',
                                        'medium' => 'Moyen',
                                        'hard'   => 'Difficile',
                                        default  => htmlspecialchars($prog['difficulty_level'])
                                    } ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <p style="color:#888; margin-top:10px; font-size:.9rem;">
                    <i class="fas fa-info-circle" style="color:var(--lavender);"></i>
                    Aucun programme créé.
                    <a href="index.php?controller=coaching&action=create" style="color:var(--lavender); font-weight:700;">Créer le premier</a>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- ── TABLEAU DE BORD COACHING (utilisateur connecté uniquement) ── -->
    <?php if ($user && $user['role'] === 'user'): ?>
    <div class="section-card" id="coachingSec">
        <div class="section-title">
            <i class="fas fa-dumbbell"></i>
            <span>Tableau de Bord Coaching</span>
        </div>
        <p style="color:#7A8B7A; margin-bottom:16px; font-size:.94rem;">
            Démarrez un programme ou discutez avec votre assistant IA nutrition.
        </p>
        <div class="coaching-dashboard">
            <a href="index.php?controller=user_dashboard&action=coaching_list" class="coaching-action-card">
                <i class="fas fa-play-circle"></i>
                <h4>Commencer un coaching</h4>
                <p>Parcourez les programmes disponibles et démarrez votre séance.</p>
            </a>
            <a href="index.php?controller=chat&action=index" class="coaching-action-card">
                <i class="fas fa-robot"></i>
                <h4>Chatbot IA NutriVert</h4>
                <p>Posez vos questions à l'assistant IA sur la nutrition et le coaching.</p>
            </a>
        </div>
    </div>
    <?php endif; ?>

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
<!-- ── TOAST GREETING ── -->
<div id="greetingToast"></div>

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
            <a href="index.php?view=forgot-password" style="color:#aaa; font-size:.85rem; display:block; margin-bottom:8px;">
                <i class="fas fa-key"></i> Mot de passe oublié ?
            </a>
            Pas encore de compte ?
            <a href="index.php?view=register">Créer un compte</a>
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

            const greeting = 'Bonjour ' + data.nom + ' ! 👋';
            showGreetingToast(greeting);

            setTimeout(function() {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            }, 1400);
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

    function showGreetingToast(msg) {
        const toast = document.getElementById('greetingToast');
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // ── Recette IA ────────────────────────────────────────────────
    function generateRecipe() {
        // Load user data from server (rendered once at page load)
        const userData = <?= json_encode([
            'isLoggedIn' => !empty($user),
            'nom' => $user['nom'] ?? '',
            'role' => $user['role'] ?? '',
            'objectif' => $user['objectif'] ?? 'maintien',
            'imc' => $user['imc'] ?? '',
            'objLabel' => $objLabel ?? 'Maintien',
            'imcInterpret' => $imcInterpret ?? ''
        ]) ?>;

        // Check login at runtime
        if (!userData.isLoggedIn) {
            openLoginModal();
            return;
        }

        const ingredients = document.getElementById('ingredientsField').value.trim();
        if (!ingredients) { alert('🍅 Entrez vos ingrédients !'); return; }

        const result = document.getElementById('recipeResult');
        let html = `✨ <strong>Recette personnalisée pour ${userData.nom}</strong><br>
        🥗 Ingrédients : ${ingredients}<br>`;

        if (userData.role === 'user' && userData.imc) {
            html += `📊 Profil : ${userData.objLabel} | IMC ${userData.imc} (${userData.imcInterpret})<br><br>`;
        } else {
            html += `<br>`;
        }

        html += `🧑‍🍳 <strong>Idée recette :</strong> Salade équilibrée — mélangez vos ingrédients avec une vinaigrette légère.<br>
        🎯 Astuce : `;

        if (userData.role === 'user') {
            if (userData.objectif === 'perte') html += 'Privilégiez les légumes verts et réduisez les féculents.';
            else if (userData.objectif === 'muscle') html += 'Ajoutez une source de protéines (œufs, pois chiches, tofu).';
            else html += 'Maintenez un bon équilibre glucides/lipides.';
        } else {
            html += 'Adaptez les portions à l\'objectif de votre client.';
        }

        result.innerHTML = html;
        result.style.display = 'block';
    }
</script>
<?php if (isset($_SESSION['user'])): ?>
<script>
(function() {
    // وظيفة للسيطرة على الزر الأصفر
    const fixYellowButton = function() {
        // البحث عن الزر الأصفر من خلال النص الظاهر فيه
        const yellowBtn = Array.from(document.querySelectorAll('button, .btn'))
                               .find(el => el.innerText.includes('Générer recette'));

        if (yellowBtn && !yellowBtn.dataset.fixedByMe) {
            
            // 1. تغيير لونه للأحمر فوراً لتعرف أنه أصبح ملكك
            yellowBtn.style.backgroundColor = "#ff4757";
            yellowBtn.style.color = "white";
            yellowBtn.innerHTML = "🚀 Ouvrir mon IA Recettes";

            // 2. إلغاء أي برمجة قديمة لأصدقائك (Clone)
            const newBtn = yellowBtn.cloneNode(true);
            yellowBtn.parentNode.replaceChild(newBtn, yellowBtn);

            // 3. إضافة وظيفة فتح مشروعك
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // البحث عن المدخلات (المكونات)
                const inputField = document.querySelector('input[type="text"], .form-control');
                const query = inputField ? encodeURIComponent(inputField.value) : "";
                
                const myUrl = "http://localhost/integration/recette/public/index.php?page=front_home&search=" + query;
                
                // فتح مشروعك في صفحة جديدة
                window.location.href = myUrl;
            });

            newBtn.dataset.fixedByMe = "true";
            console.log("Yellow button hijacked successfully!");
        }
    };

    // تشغيل الفحص كل ثانية لضمان السيطرة على الزر
    setInterval(fixYellowButton, 1000);
})();
</script>
<?php endif; ?>

</body>
</html>

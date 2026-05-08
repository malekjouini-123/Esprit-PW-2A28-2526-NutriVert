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
    <link rel="stylesheet" href="assets/css/style.css">
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
            <a href="evnment/index.php">Événements</a>
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
                <a href="evnment/index.php" class="user-stat" style="background:var(--lavender); color:white; text-decoration:none;">
                     📅 Événements
                 </a>
            </div>
            <a href="?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>

    <?php else: ?>

        <div class="guest-banner">
            <p>👋 Rejoignez Nutrivert pour des recettes personnalisées et des événements exclusifs !</p>
            <button class="btn-login" onclick="openLoginModal()">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </button>
            <a href="index.php?controller=user&action=register" class="btn-signup">
                <i class="fas fa-user-plus"></i> Inscription
            </a>
            <a href="evnment/index.php" class="btn-login" style="margin-left:10px; border-color:var(--lavender); color:#6A3B9A;">
                 <i class="fas fa-calendar-alt"></i> Événements
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

    <!-- ── SECTION ÉVÉNEMENTS ── -->
    <div class="section-card" id="eventSec">
        <div class="section-title">
            <i class="fas fa-calendar-alt"></i>
            <span>📅 Événements Nutrivert</span>
        </div>
        <p>Découvrez nos prochains ateliers, webinaires et séances de coaching collectif.</p>
        
        <div style="margin-top:20px; display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:20px;">
            <div style="background:#F0F7FF; padding:20px; border-radius:20px; border-left:5px solid #007bff;">
                <h4 style="color:#0056b3;">Atelier Cuisine Saine</h4>
                <p style="font-size:0.9rem; color:#555;">Apprenez à cuisiner sans gaspillage avec nos coachs nutrition.</p>
                <div style="margin-top:10px; font-weight:bold; color:#007bff;">🕒 Samedi 15 Mai - 10h00</div>
            </div>
            <div style="background:#FFF0F7; padding:20px; border-radius:20px; border-left:5px solid #d63384;">
                <h4 style="color:#a71d5d;">Webinaire : Gestion du Stress</h4>
                <p style="font-size:0.9rem; color:#555;">L'impact du stress sur votre nutrition et comment le gérer.</p>
                <div style="margin-top:10px; font-weight:bold; color:#d63384;">🕒 Mardi 18 Mai - 18h30</div>
            </div>
        </div>

        <div style="text-align:center; margin-top:30px;">
             <a href="evnment/index.php" class="btn-primary" style="text-decoration:none; display:inline-block;">
                 <i class="fas fa-plus-circle"></i> Accéder au portail événements
             </a>
         </div>
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

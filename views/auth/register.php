<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Nutrivert</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{
            font-family:'Quicksand',sans-serif;
            background:linear-gradient(135deg,#FFF9F0,#E8F5E9);
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            padding:30px 16px;
        }
        .card{
            background:#fff; border-radius:32px;
            padding:44px 40px; width:100%; max-width:520px;
            box-shadow:0 24px 48px -12px rgba(0,0,0,.12);
        }
        .logo{ text-align:center; margin-bottom:24px; }
        .logo i{ font-size:2.2rem; color:#FF7E67; }
        .logo span{
            display:block; font-size:1.7rem; font-weight:700;
            background:linear-gradient(135deg,#FF7E67,#7DCFB6);
            -webkit-background-clip:text; background-clip:text; color:transparent;
        }
        h2{ text-align:center; font-size:1.2rem; color:#4A6B4A; margin-bottom:20px; }

        .flash{
            background:#FFF0EE; border:1px solid #FF7E67;
            color:#c0392b; border-radius:16px;
            padding:10px 16px; font-size:.9rem;
            margin-bottom:18px; text-align:center;
        }

        /* Sélecteur de rôle */
        .role-tabs{
            display:flex; gap:10px; margin-bottom:26px;
        }
        .role-tab{
            flex:1; padding:12px;
            border:2px solid #E8F0E8; border-radius:40px;
            background:#fff; font-family:inherit; font-weight:700;
            font-size:.95rem; cursor:pointer; transition:.2s;
            color:#4A5B4A; text-align:center;
        }
        .role-tab.active-user  { border-color:#7DCFB6; background:#E8FAF6; color:#4A6B4A; }
        .role-tab.active-coach { border-color:#C8A2F0; background:#F3EAFF; color:#6A3B9A; }

        label{ display:block; font-weight:600; color:#4A5B4A; margin-bottom:5px; font-size:.88rem; }
        input, select, textarea{
            width:100%; padding:11px 18px;
            border:2px solid #E8F0E8; border-radius:40px;
            font-family:inherit; font-size:.97rem;
            outline:none; transition:.2s; margin-bottom:14px;
        }
        textarea{ border-radius:20px; resize:vertical; min-height:80px; }
        input:focus, select:focus, textarea:focus{ border-color:#7DCFB6; }

        .row2{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }

        /* Aperçu IMC/Calories */
        .preview-box{
            background:#FEF7E8; border-radius:20px;
            padding:14px 18px; font-size:.88rem;
            color:#4A5B4A; margin-bottom:14px;
            display:none;
        }
        .preview-box.visible{ display:block; }
        .preview-box strong{ color:#FF7E67; font-size:1rem; }

        /* Champs conditionnels */
        .fields-user, .fields-coach{ display:none; }
        .fields-user.show, .fields-coach.show{ display:block; }

        .btn{
            width:100%; padding:13px;
            border:none; border-radius:40px;
            background:linear-gradient(135deg,#FF7E67,#FFB347);
            color:#fff; font-family:inherit; font-size:1rem; font-weight:700;
            cursor:pointer; transition:.2s; margin-top:6px;
        }
        .btn:hover{ transform:translateY(-2px); box-shadow:0 8px 20px rgba(255,126,103,.35); }

        .link-login{ text-align:center; margin-top:18px; }
        .link-login a{ color:#7DCFB6; font-weight:600; text-decoration:none; }
        .link-login a:hover{ color:#FF7E67; }

        /* Section badge coach */
        .coach-badge{
            background:linear-gradient(135deg,#F3EAFF,#E8F0FF);
            border-radius:20px; padding:14px 18px;
            font-size:.88rem; color:#6A3B9A;
            margin-bottom:14px;
        }
        .coach-badge i{ margin-right:6px; }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">
        <i class="fas fa-leaf"></i>
        <span>Nutrivert</span>
    </div>
    <h2>Créer un compte</h2>

    <?php if (!empty($flashMessage)): ?>
        <div class="flash"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?controller=user&action=doRegister" id="registerForm">

        <!-- ── Sélecteur de rôle ── -->
        <div class="role-tabs">
            <button type="button" class="role-tab active-user" id="tabUser" onclick="selectRole('user')">
                <i class="fas fa-user"></i> Utilisateur
            </button>
            <button type="button" class="role-tab" id="tabCoach" onclick="selectRole('coach')">
                <i class="fas fa-dumbbell"></i> Coach
            </button>
        </div>
        <input type="hidden" name="role" id="roleInput" value="user">

        <!-- ── Champs communs ── -->
        <label for="nom"><i class="fas fa-id-card"></i> Nom complet</label>
        <input type="text" id="nom" name="nom" placeholder="Jean Dupont" required
               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">

        <label for="email"><i class="fas fa-envelope"></i> Email</label>
        <input type="email" id="email" name="email" placeholder="votre@email.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
        <input type="password" id="password" name="password" placeholder="Minimum 4 caractères" required>

        <!-- ── Champs USER ── -->
        <div class="fields-user show" id="fieldsUser">

            <div class="row2">
                <div>
                    <label><i class="fas fa-weight"></i> Poids (kg)</label>
                    <input type="number" name="poids" id="poids" min="20" max="500" step="0.1"
                           placeholder="70" value="<?= htmlspecialchars($_POST['poids'] ?? '') ?>"
                           oninput="previewHealth()">
                </div>
                <div>
                    <label><i class="fas fa-ruler-vertical"></i> Taille (cm)</label>
                    <input type="number" name="taille" id="taille" min="100" max="300"
                           placeholder="175" value="<?= htmlspecialchars($_POST['taille'] ?? '') ?>"
                           oninput="previewHealth()">
                </div>
            </div>

            <div class="row2">
                <div>
                    <label><i class="fas fa-birthday-cake"></i> Âge</label>
                    <input type="number" name="age" id="age" min="10" max="120"
                           placeholder="25" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>"
                           oninput="previewHealth()">
                </div>
                <div>
                    <label><i class="fas fa-venus-mars"></i> Sexe</label>
                    <select name="sexe" id="sexe" onchange="previewHealth()">
                        <option value="homme">Homme</option>
                        <option value="femme">Femme</option>
                    </select>
                </div>
            </div>

            <label><i class="fas fa-bullseye"></i> Objectif</label>
            <select name="objectif" id="objectif" onchange="previewHealth()">
                <option value="maintien">Maintien du poids</option>
                <option value="perte">Perte de poids</option>
                <option value="muscle">Prise de muscle</option>
            </select>

            <!-- Aperçu IMC / Calories -->
            <div class="preview-box" id="previewBox">
                <strong>📊 Aperçu de votre profil :</strong><br>
                <span id="previewText"></span>
            </div>
        </div>

        <!-- ── Champs COACH ── -->
        <div class="fields-coach" id="fieldsCoach">
            <div class="coach-badge">
                <i class="fas fa-info-circle"></i>
                En tant que <strong>coach</strong>, vous accéderez à vos programmes de coaching après l'inscription.
            </div>

            <label><i class="fas fa-star"></i> Spécialité</label>
            <input type="text" name="specialite" id="specialite"
                   placeholder="Ex : Perte de poids, Musculation, Cardio…"
                   value="<?= htmlspecialchars($_POST['specialite'] ?? '') ?>">

            <label><i class="fas fa-align-left"></i> Bio (courte présentation)</label>
            <textarea name="bio" id="bio" style="border-radius:20px;" placeholder="Quelques mots sur votre expérience…"><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn" id="submitBtn">
            <i class="fas fa-user-plus"></i> Créer mon compte
        </button>
    </form>

    <div class="link-login">
        Déjà un compte ? <a href="index.php?view=login">Se connecter</a>
    </div>
</div>

<script>
    function selectRole(role) {
        document.getElementById('roleInput').value = role;

        const tabUser  = document.getElementById('tabUser');
        const tabCoach = document.getElementById('tabCoach');
        const fUser    = document.getElementById('fieldsUser');
        const fCoach   = document.getElementById('fieldsCoach');
        const btn      = document.getElementById('submitBtn');

        if (role === 'user') {
            tabUser.className  = 'role-tab active-user';
            tabCoach.className = 'role-tab';
            fUser.classList.add('show');
            fCoach.classList.remove('show');
            btn.style.background = 'linear-gradient(135deg,#FF7E67,#FFB347)';
        } else {
            tabCoach.className = 'role-tab active-coach';
            tabUser.className  = 'role-tab';
            fCoach.classList.add('show');
            fUser.classList.remove('show');
            btn.style.background = 'linear-gradient(135deg,#C8A2F0,#7DCFB6)';
        }
    }

    function previewHealth() {
        const poids  = parseFloat(document.getElementById('poids').value);
        const taille = parseFloat(document.getElementById('taille').value);
        const age    = parseInt(document.getElementById('age').value);
        const sexe   = document.getElementById('sexe').value;
        const obj    = document.getElementById('objectif').value;
        const box    = document.getElementById('previewBox');
        const txt    = document.getElementById('previewText');

        if (!poids || !taille || !age || isNaN(poids) || isNaN(taille) || isNaN(age)) {
            box.classList.remove('visible');
            return;
        }

        const tailleM = taille / 100;
        const imc = (poids / (tailleM * tailleM)).toFixed(1);

        let interp = '';
        if      (imc < 18.5) interp = 'Insuffisance pondérale';
        else if (imc < 25)   interp = 'Poids normal ✅';
        else if (imc < 30)   interp = 'Surpoids';
        else                 interp = 'Obésité';

        let bmr = sexe === 'femme'
            ? 10*poids + 6.25*taille - 5*age - 161
            : 10*poids + 6.25*taille - 5*age + 5;
        let tdee = bmr * 1.55;
        let cal  = obj === 'perte' ? tdee - 500 : obj === 'muscle' ? tdee + 300 : tdee;

        txt.innerHTML = `IMC : <strong>${imc}</strong> (${interp})<br>🔥 Besoin calorique : <strong>${Math.round(cal)} kcal/jour</strong>`;
        box.classList.add('visible');
    }
</script>
</body>
</html>
<?php
declare(strict_types=1);
/**
 * Vue d'inscription à un événement (NutriVert).
 * @var Evenement $event
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire : <?= htmlspecialchars($event->titre) ?> | NutriVert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary: #2ecc71; --secondary: #27ae60; --dark: #2c3e50; --light: #f9fbf9; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--light); color: var(--dark); line-height: 1.6; }
        .container { max-width: 650px; margin: 2rem auto; padding: 2.5rem; background: #fff; border-radius: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        header { text-align: center; margin-bottom: 2rem; }
        .logo-title { color: var(--primary); font-size: 2.2rem; font-weight: 800; }
        h2 { font-size: 1.2rem; color: var(--dark); margin-top: 0.5rem; }
        
        .form-grid { display: grid; gap: 1.2rem; margin-top: 2rem; }
        .form-field { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-field label { font-size: 0.9rem; font-weight: 600; color: #7f8c8d; }
        .form-field input { padding: 0.9rem 1.2rem; border: 2px solid #f0f0f0; border-radius: 1rem; font-size: 1rem; outline: none; transition: all 0.3s; }
        .form-field input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1); }
        
        .two-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
        
        /* IMC Display */
        .imc-box { background: #f0fdf4; padding: 1.5rem; border-radius: 1.2rem; border: 2px dashed var(--primary); text-align: center; margin-top: 0.5rem; }
        .imc-value { font-size: 2rem; font-weight: 800; color: var(--secondary); display: block; }
        .imc-label { font-size: 0.85rem; font-weight: 600; color: var(--secondary); }

        .btn-submit { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: #fff; border: none; padding: 1.2rem; border-radius: 1rem; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: all 0.3s; margin-top: 1rem; }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3); }
        .btn-back { display: block; text-align: center; color: #bdc3c7; text-decoration: none; font-size: 0.9rem; margin-top: 1.5rem; font-weight: 600; }
        
        /* Error styles */
        .error-message { color: #e74c3c; font-size: 0.75rem; margin-top: 0.25rem; font-weight: 600; display: none; }
        .form-field.error input { border-color: #e74c3c; background-color: #fdf2f2; }
        .form-field.error .error-message { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-title">NutriVert</div>
            <h2>Inscription : <?= htmlspecialchars($event->titre) ?></h2>
        </header>

        <form action="index.php?action=save_inscription" method="post" class="form-grid" id="registerForm">
            <input type="hidden" name="evenement_id" value="<?= (int)$event->id ?>">
            
            <div class="two-cols">
                <div class="form-field">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Ex. Dupont">
                    <span class="error-message">Le nom est requis (min 2 caractères).</span>
                </div>
                <div class="form-field">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" placeholder="Ex. Jean">
                    <span class="error-message">Le prénom est requis (min 2 caractères).</span>
                </div>
            </div>

            <div class="two-cols">
                <div class="form-field">
                    <label for="email">Gmail (Email)</label>
                    <input type="text" id="email" name="email" placeholder="jean.dupont@gmail.com">
                    <span class="error-message">Veuillez entrer une adresse email valide.</span>
                </div>
                <div class="form-field">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="********">
                    <span class="error-message">Le mot de passe doit faire au moins 6 caractères.</span>
                </div>
            </div>

            <div class="two-cols">
                <div class="form-field">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" placeholder="06 00 00 00 00">
                    <span class="error-message">Le téléphone doit contenir 8 chiffres.</span>
                </div>
                <div class="form-field">
                    <label for="lieu">Lieu (ville / CP)</label>
                    <input type="text" id="lieu" name="lieu" placeholder="Ex. Paris 75001">
                    <span class="error-message">Le lieu est requis.</span>
                </div>
            </div>

            <div class="two-cols">
                <div class="form-field">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance">
                    <span class="error-message">Vous devez avoir au moins 13 ans.</span>
                </div>
                <div class="form-field">
                    <label for="categorie_preferee">Catégorie Préférée</label>
                    <select name="categorie_preferee" id="categorie_preferee" style="padding: 0.9rem 1.2rem; border: 2px solid #f0f0f0; border-radius: 1rem; font-size: 1rem; outline: none; transition: all 0.3s; background: white;">
                        <option value="">-- Votre préférence --</option>
                        <option value="Cuisine">Ateliers Cuisine</option>
                        <option value="Nutrition">Nutrition & Santé</option>
                        <option value="Sport">Sport & Vitalité</option>
                        <option value="Bien-être">Bien-être</option>
                    </select>
                    <span class="error-message">Veuillez choisir une catégorie.</span>
                </div>
            </div>

            <div class="two-cols">
                <div class="form-field">
                    <label for="poids">Poids (kg)</label>
                    <input type="text" id="poids" name="poids" placeholder="70" oninput="calculateIMC()">
                    <span class="error-message">Poids invalide (20-300 kg).</span>
                </div>
                <div class="form-field">
                    <label for="taille">Taille (cm)</label>
                    <input type="text" id="taille" name="taille" placeholder="175" oninput="calculateIMC()">
                    <span class="error-message">Taille invalide (50-250 cm).</span>
                </div>
            </div>

            <div class="imc-box">
                <input type="hidden" name="imc" id="imc_input" value="0">
                <span class="imc-label">Votre IMC estimé</span>
                <span class="imc-value" id="imc_display">--</span>
                <span id="imc_status" style="font-size: 0.8rem; font-weight: 700;"></span>
            </div>

            <button type="submit" class="btn-submit">Valider mon inscription</button>
            <a href="index.php?sub=participants" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
        </form>
    </div>

    <script>
        const form = document.getElementById('registerForm');

        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Reset errors
            document.querySelectorAll('.form-field').forEach(f => f.classList.remove('error'));

            // Nom & Prenom
            if (document.getElementById('nom').value.trim().length < 2) {
                showError('nom');
                isValid = false;
            }
            if (document.getElementById('prenom').value.trim().length < 2) {
                showError('prenom');
                isValid = false;
            }

            // Email
            const email = document.getElementById('email').value;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email');
                isValid = false;
            }

            // Password
            if (document.getElementById('mot_de_passe').value.length < 6) {
                showError('mot_de_passe');
                isValid = false;
            }

            // Phone (8 digits for TN)
            const tel = document.getElementById('telephone').value.replace(/\s/g, '');
            if (!/^\d{8}$/.test(tel)) {
                showError('telephone');
                isValid = false;
            }

            // Date de naissance (Min 13 years old)
            const dob = new Date(document.getElementById('date_naissance').value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            if (isNaN(dob.getTime()) || age < 13) {
                showError('date_naissance');
                isValid = false;
            }

            // Poids & Taille
            const poids = parseFloat(document.getElementById('poids').value);
            if (isNaN(poids) || poids < 20 || poids > 300) {
                showError('poids');
                isValid = false;
            }
            const taille = parseFloat(document.getElementById('taille').value);
            if (isNaN(taille) || taille < 50 || taille > 250) {
                showError('taille');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        function showError(id) {
            document.getElementById(id).closest('.form-field').classList.add('error');
        }

        function calculateIMC() {
            const poids = parseFloat(document.getElementById('poids').value);
            const taille = parseFloat(document.getElementById('taille').value);
            const display = document.getElementById('imc_display');
            const input = document.getElementById('imc_input');
            const status = document.getElementById('imc_status');

            if (poids > 0 && taille > 0) {
                const tailleM = taille / 100;
                const imc = (poids / (tailleM * tailleM)).toFixed(1);
                display.innerText = imc;
                input.value = imc;

                if (imc < 18.5) { status.innerText = "Insuffisance pondérale"; status.style.color = "#3498db"; }
                else if (imc < 25) { status.innerText = "Poids normal"; status.style.color = "#27ae60"; }
                else if (imc < 30) { status.innerText = "Surpoids"; status.style.color = "#f39c12"; }
                else { status.innerText = "Obésité"; status.style.color = "#e74c3c"; }
            } else {
                display.innerText = "--";
                input.value = "0";
                status.innerText = "";
            }
        }
    </script>
</body>
</html>

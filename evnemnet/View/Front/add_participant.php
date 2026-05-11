<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>Ajouter un Participant</h1>
    <p>Enregistrez un nouveau membre dans la base de données.</p>
</div>

<div class="section-card" style="max-width: 800px; margin-left: auto; margin-right: auto;">
    <div class="section-title">
        <i class="bi bi-person-plus-fill"></i>
        <span>Profil du participant</span>
    </div>
    
    <form action="index.php?action=add_participant" method="POST" class="ingredient-group">
        <!-- Identité -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Nom</label>
                <input type="text" name="nom" placeholder="Dupont" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Prénom</label>
                <input type="text" name="prenom" placeholder="Jean" required>
            </div>
        </div>

        <!-- Email et Mot de passe -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%; margin-top: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Email (Gmail)</label>
                <input type="email" name="email" placeholder="jean.dupont@gmail.com" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Mot de passe</label>
                <input type="password" name="mot_de_passe" placeholder="Votre mot de passe sécurisé" required>
            </div>
        </div>

        <!-- Contact -->
        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Téléphone</label>
            <input type="text" name="telephone" placeholder="06 01 02 03 04">
        </div>

        <!-- Informations Physiques -->
        <div style="margin-top: 30px; padding: 15px; background-color: #F5F9F5; border-radius: 10px;">
            <h4 style="color: var(--deep); margin-bottom: 15px;">Informations Physiques</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; width: 100%;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 600; color: var(--deep);">Poids (kg)</label>
                    <input type="number" name="poids" placeholder="70" step="0.1">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 600; color: var(--deep);">Taille (cm)</label>
                    <input type="number" name="taille" placeholder="180" step="0.1">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 600; color: var(--deep);">IMC</label>
                    <input type="number" name="imc" placeholder="Calculé automatiquement" step="0.1" readonly>
                </div>
            </div>
        </div>

        <!-- Localisation et Objectif -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%; margin-top: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Lieu</label>
                <input type="text" name="lieu" placeholder="Paris, France">
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Objectif</label>
            <textarea name="objectif" placeholder="Décrivez vos objectifs sportifs..." style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; resize: vertical; min-height: 80px;"></textarea>
        </div>

        <div style="display: flex; justify-content: center; width: 100%; margin-top: 40px;">
            <button type="submit" class="btn-primary" style="padding: 15px 60px; font-size: 1.1rem;">
                Enregistrer le participant
            </button>
        </div>
    </form>

    <script>
        // Calculer automatiquement l'IMC
        const poidsInput = document.querySelector('input[name="poids"]');
        const tailleInput = document.querySelector('input[name="taille"]');
        const imcInput = document.querySelector('input[name="imc"]');

        function calculateIMC() {
            const poids = parseFloat(poidsInput.value);
            const taille = parseFloat(tailleInput.value) / 100; // Convertir cm en m
            
            if (poids && taille) {
                const imc = (poids / (taille * taille)).toFixed(2);
                imcInput.value = imc;
            }
        }

        poidsInput.addEventListener('input', calculateIMC);
        tailleInput.addEventListener('input', calculateIMC);
        calculateIMC();
    </script>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

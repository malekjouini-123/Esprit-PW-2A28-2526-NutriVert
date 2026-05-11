<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>✏️ Créez Votre Recommandation</h1>
    <p>Écrivez ce que vous recherchez, et notre IA vous aide à trouver les meilleurs événements</p>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 40px;">
    <!-- Formulaire de création -->
    <div class="section-card" style="margin-top: 0;">
        <div class="section-title">
            <i class="bi bi-pencil-square"></i>
            <span>Ma recommandation</span>
        </div>

        <form method="POST" action="index.php?action=create_recommendation" class="ingredient-group" id="recForm">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Titre de votre recommandation *</label>
                <input type="text" name="titre" id="titre" placeholder="Ex: Un événement sportif pas cher" required value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 20px;">
                <label style="font-weight: 600; color: var(--deep);">Décrivez ce que vous recherchez *</label>
                <textarea name="description" id="description" placeholder="Écrivez ce que vous aimeriez faire... Par exemple: 'Je cherche un événement sportif dans Paris, pas trop cher, pour me remettre au tennis.' L'IA analysera votre demande pour vous suggérer les meilleurs événements." required style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; resize: vertical; min-height: 120px;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 600; color: var(--deep);">Catégorie (optionnel)</label>
                    <select name="categorie_preferee" id="categorie" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        <option value="">-- Sélectionnez une catégorie --</option>
                        <option value="Sport" <?= ($_POST['categorie_preferee'] ?? '') === 'Sport' ? 'selected' : '' ?>>Sport</option>
                        <option value="Culture" <?= ($_POST['categorie_preferee'] ?? '') === 'Culture' ? 'selected' : '' ?>>Culture</option>
                        <option value="Technologie" <?= ($_POST['categorie_preferee'] ?? '') === 'Technologie' ? 'selected' : '' ?>>Technologie</option>
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 600; color: var(--deep);">Budget max (€) (optionnel)</label>
                    <input type="number" name="budget_max" id="budget" placeholder="Ex: 100" step="0.01" value="<?= htmlspecialchars($_POST['budget_max'] ?? '') ?>">
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 20px;">
                <label style="font-weight: 600; color: var(--deep);">Localisation (optionnel)</label>
                <input type="text" name="localisation" id="localisation" placeholder="Ex: Paris, Lyon, Marseille" value="<?= htmlspecialchars($_POST['localisation'] ?? '') ?>">
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="button" class="btn-primary" onclick="analyzeForm()" style="padding: 12px 30px; background: var(--mint); font-weight: 700;">
                    <i class="bi bi-magic"></i> Analyser avec l'IA
                </button>
            </div>
        </form>
    </div>

    <!-- Panneau IA -->
    <div class="section-card" style="margin-top: 0; position: sticky; top: 20px;">
        <div class="section-title">
            <i class="bi bi-robot"></i>
            <span>Assistant IA</span>
        </div>

        <div id="aiPanel" style="display: none;">
            <div id="aiSuggestions" style="margin-bottom: 20px;"></div>
            
            <div id="aiEvents" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #E0E8E0;"></div>

            <button type="button" class="btn-primary" onclick="submitForm()" style="width: 100%; margin-top: 20px; padding: 12px; font-weight: 700;">
                ✓ Créer cette recommandation
            </button>
        </div>

        <div id="noPanel" style="background: #F5F9F5; padding: 15px; border-radius: 8px; text-align: center; color: #666;">
            <p><i class="bi bi-info-circle"></i> Remplissez le formulaire et cliquez sur "Analyser avec l'IA" pour obtenir des suggestions</p>
        </div>

        <div id="loadingPanel" style="display: none; text-align: center; padding: 20px;">
            <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #E0E8E0; border-top-color: var(--mint); border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 15px; color: #666;">L'IA analyse votre demande...</p>
        </div>

        <style>
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>
    </div>
</div>

<script>
    function analyzeForm() {
        const titre = document.getElementById('titre').value;
        const description = document.getElementById('description').value;
        const categorie = document.getElementById('categorie').value;
        const budget = document.getElementById('budget').value;
        const localisation = document.getElementById('localisation').value;

        if (!titre || !description) {
            alert('Veuillez remplir le titre et la description');
            return;
        }

        // Afficher le panneau de chargement
        document.getElementById('noPanel').style.display = 'none';
        document.getElementById('aiPanel').style.display = 'none';
        document.getElementById('loadingPanel').style.display = 'block';

        // Créer un formulaire pour envoyer les données
        const formData = new FormData();
        formData.append('titre', titre);
        formData.append('description', description);
        formData.append('categorie_preferee', categorie);
        formData.append('budget_max', budget);
        formData.append('localisation', localisation);
        formData.append('ajax_analyze', '1');

        fetch('index.php?action=create_recommendation', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            displayAISuggestions(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur s\'est produite');
            document.getElementById('loadingPanel').style.display = 'none';
            document.getElementById('noPanel').style.display = 'block';
        });
    }

    function displayAISuggestions(data) {
        document.getElementById('loadingPanel').style.display = 'none';
        document.getElementById('noPanel').style.display = 'none';
        document.getElementById('aiPanel').style.display = 'block';

        const suggestionsDiv = document.getElementById('aiSuggestions');
        const eventsDiv = document.getElementById('aiEvents');

        // Afficher les suggestions
        suggestionsDiv.innerHTML = '<h4 style="color: var(--deep); margin-bottom: 12px;">💡 Suggestions IA :</h4>';
        if (data.suggestions && Array.isArray(data.suggestions)) {
            data.suggestions.forEach(suggestion => {
                suggestionsDiv.innerHTML += '<p style="font-size: 0.9rem; color: #666; margin-bottom: 8px;">' + suggestion + '</p>';
            });
        }

        // Afficher les événements suggérés
        if (data.matching_events && data.matching_events.length > 0) {
            eventsDiv.innerHTML = '<h4 style="color: var(--deep); margin-bottom: 12px;">🎯 Événements suggérés :</h4>';
            data.matching_events.forEach(event => {
                const eventData = event.event || event;
                const titre = eventData.titre || 'Événement';
                const lieu = eventData.lieu || 'À définir';
                const prix = eventData.prix || 0;
                eventsDiv.innerHTML += `
                    <div style="padding: 10px; background: #E6F4EE; border-radius: 8px; margin-bottom: 8px; font-size: 0.85rem;">
                        <strong>${escapeHtml(titre)}</strong> • ${escapeHtml(lieu)}<br>
                        <span style="color: var(--coral);">${prix}€</span>
                    </div>
                `;
            });
        } else {
            eventsDiv.innerHTML = '<p style="color: #999; font-size: 0.9rem;">Aucun événement ne correspond à vos critères pour le moment.</p>';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function submitForm() {
        const form = document.getElementById('recForm');
        form.submit();
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

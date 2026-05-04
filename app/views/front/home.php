<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <section class="hero-panel">
        <h1 class="page-title">✨ Manger malin, zéro gaspillage ✨</h1>
        <p class="hero-text">Nutrivert — intelligence nutritionnelle & recettes durables dans le style de votre template test3.</p>
    </section>

    <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="page" value="front_home">

        <div>
            <label for="search">Recherche par titre de recette</label>
            <input
                type="text"
                id="search"
                name="search"
                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                placeholder="Rechercher une recette"
            >
        </div>

        <button type="submit" class="btn-green"><i class="fas fa-search"></i> Rechercher</button>
        <button type="button" class="btn-green alt" id="openGenerateModal"><i class="fas fa-wand-magic-sparkles"></i> Générer recette</button>
        <a href="index.php?page=front_home&mode_metier=jour" class="btn-ghost btn-feature"><i class="fas fa-calendar-day"></i> Recette du jour</a>
        <button type="button" class="btn-ghost btn-feature" id="openSuggestionModal"><i class="fas fa-lightbulb"></i> Suggestion intelligente</button>
        <a href="index.php?page=front_home&mode_metier=rapides" class="btn-ghost btn-feature"><i class="fas fa-bolt"></i> Recettes rapides</a>
        <button type="button" class="btn-ghost btn-feature" id="openObjectifModal"><i class="fas fa-bullseye"></i> Mode objectif</button>
        <a href="index.php?page=front_home" class="btn-ghost">Réinitialiser</a>
    </form>

    <div id="generateModal" class="generate-modal" style="display: none;">
        <div class="generate-modal-content">
            <div class="generate-modal-head">
                <h2>Générer recette</h2>
                <button type="button" id="closeGenerateModal" class="modal-close-btn">×</button>
            </div>

            <form method="GET" action="index.php" class="generate-form" id="generateRecipeForm">
                <input type="hidden" name="page" value="front_home">
                <input type="hidden" name="generer_recette" value="1">

                <label for="regime_generate">Choisir un régime (optionnel)</label>
                <select id="regime_generate" name="regime_generate">
                    <option value="">Tous les régimes</option>
                    <?php foreach (['Végétarien', 'Végan', 'Sans gluten', 'Protéiné', 'Faible en calories'] as $regimeOption): ?>
                        <option value="<?php echo htmlspecialchars($regimeOption); ?>" <?php echo (($selectedRegime ?? '') === $regimeOption) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($regimeOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Choisir les ingrédients disponibles dans la base</label>
                <?php if (!empty($availableIngredients)): ?>
                    <div class="ingredient-choice-grid">
                        <?php foreach ($availableIngredients as $ingredientName): ?>
                            <label class="ingredient-choice">
                                <input
                                    type="checkbox"
                                    name="ingredients_generate[]"
                                    value="<?php echo htmlspecialchars($ingredientName); ?>"
                                    <?php echo in_array($ingredientName, $selectedIngredients ?? [], true) ? 'checked' : ''; ?>
                                >
                                <span><?php echo htmlspecialchars($ingredientName); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="helper-text">Aucun ingrédient trouvé dans la base. Ajoutez d’abord des recettes avec ingrédients dans le BackOffice.</p>
                <?php endif; ?>

                <div class="error-message" id="generateRecipeError">
                    <?php if (!empty($generateMode) && empty($selectedIngredients)): ?>
                        Choisissez au moins un ingrédient avant de générer une recette. Le régime est optionnel.
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-green"><i class="fas fa-wand-magic-sparkles"></i> Générer recette</button>
                    <button type="button" class="btn-ghost" id="cancelGenerateModal">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <div id="suggestionModal" class="generate-modal" style="display: none;">
        <div class="generate-modal-content">
            <div class="generate-modal-head">
                <h2>Suggestion intelligente</h2>
                <button type="button" id="closeSuggestionModal" class="modal-close-btn">×</button>
            </div>

            <form method="GET" action="index.php" class="generate-form" id="suggestionForm">
                <input type="hidden" name="page" value="front_home">
                <input type="hidden" name="mode_metier" value="suggestion">

                <p class="helper-text">Choisis les ingrédients que tu as. Le système affiche les recettes compatibles même partiellement, avec un pourcentage.</p>

                <?php if (!empty($availableIngredients)): ?>
                    <div class="ingredient-choice-grid">
                        <?php foreach ($availableIngredients as $ingredientName): ?>
                            <label class="ingredient-choice">
                                <input
                                    type="checkbox"
                                    name="ingredients_suggestion[]"
                                    value="<?php echo htmlspecialchars($ingredientName); ?>"
                                    <?php echo in_array($ingredientName, $selectedSmartIngredients ?? [], true) ? 'checked' : ''; ?>
                                >
                                <span><?php echo htmlspecialchars($ingredientName); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="helper-text">Aucun ingrédient trouvé dans la base.</p>
                <?php endif; ?>

                <div class="error-message" id="suggestionError"></div>

                <div class="form-actions">
                    <button type="submit" class="btn-green"><i class="fas fa-lightbulb"></i> Afficher suggestions</button>
                    <button type="button" class="btn-ghost" id="cancelSuggestionModal">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <div id="objectifModal" class="generate-modal" style="display: none;">
        <div class="generate-modal-content small-modal">
            <div class="generate-modal-head">
                <h2>Mode objectif</h2>
                <button type="button" id="closeObjectifModal" class="modal-close-btn">×</button>
            </div>

            <form method="GET" action="index.php" class="generate-form" id="objectifForm">
                <input type="hidden" name="page" value="front_home">
                <input type="hidden" name="mode_metier" value="objectif">

                <label for="objectif_mode">Choisir un objectif</label>
                <select id="objectif_mode" name="objectif_mode">
                    <option value="">-- Choisir --</option>
                    <?php foreach (($availableObjectifs ?? []) as $objectifOption): ?>
                        <option value="<?php echo htmlspecialchars($objectifOption); ?>" <?php echo (($selectedObjectif ?? '') === $objectifOption) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($objectifOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="error-message" id="objectifError"></div>

                <div class="form-actions">
                    <button type="submit" class="btn-green"><i class="fas fa-bullseye"></i> Afficher recettes</button>
                    <button type="button" class="btn-ghost" id="cancelObjectifModal">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <?php $hideRecetteGrid = (!empty($generateMode) && empty($selectedIngredients)) || (($businessMode ?? '') === 'suggestion' && empty($selectedSmartIngredients)) || (($businessMode ?? '') === 'objectif' && empty($selectedObjectif)); ?>

    <?php if (!empty($resultTitle)): ?>
        <div class="generate-result-box">
            <strong><?php echo htmlspecialchars($resultTitle); ?></strong>
            <?php if (($businessMode ?? '') === 'rapides'): ?>
                <span> — recettes de 20 minutes ou moins.</span>
            <?php elseif (($businessMode ?? '') === 'objectif' && !empty($selectedObjectif)): ?>
                <span> — objectif choisi : <?php echo htmlspecialchars($selectedObjectif); ?></span>
            <?php elseif (($businessMode ?? '') === 'suggestion' && !empty($selectedSmartIngredients)): ?>
                <span> — ingrédients choisis : <?php echo htmlspecialchars(implode(', ', $selectedSmartIngredients)); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!$hideRecetteGrid): ?>
        <div class="recette-grid" id="recetteGrid">
            <?php if (!empty($recettes)): ?>
                <?php foreach ($recettes as $recette): ?>
                    <article class="recette-card">
                        <div class="card-badge">Recette</div>
                        <h3><?php echo htmlspecialchars($recette['titre']); ?></h3>
                        <div class="recette-meta"><strong>Objectif :</strong> <?php echo htmlspecialchars($recette['objectif']); ?></div>
                        <div class="recette-meta"><strong>Régime :</strong> <?php echo htmlspecialchars($recette['regime']); ?></div>
                        <div class="recette-meta"><strong>Durée :</strong> <?php echo (int) $recette['duree']; ?> min</div>
                        <?php if (isset($recette['match_score'])): ?>
                            <div class="smart-score">
                                Compatibilité : <?php echo (int) $recette['match_score']; ?>%
                                <span>(<?php echo (int) $recette['match_count']; ?>/<?php echo (int) $recette['match_total']; ?> ingrédients)</span>
                            </div>
                        <?php endif; ?>
                        <div class="recette-actions">
                            <a href="index.php?page=front_recette_detail&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-green"><i class="fas fa-utensils"></i> Voir détails</a>
                            <a href="index.php?page=front_save_recette&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-ghost"><i class="fas fa-bookmark"></i> Enregistrer</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="form-card">
                    <p>Aucune recette trouvée.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function setupModal(config) {
        const modal = document.getElementById(config.modalId);
        const openBtn = document.getElementById(config.openId);
        const closeBtn = document.getElementById(config.closeId);
        const cancelBtn = document.getElementById(config.cancelId);

        function openModal() {
            if (!modal) return;
            modal.style.display = 'flex';
            modal.classList.add('show');
            document.body.classList.add('generate-open');
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.classList.remove('generate-open');
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });
        }
    }

    setupModal({ modalId: 'generateModal', openId: 'openGenerateModal', closeId: 'closeGenerateModal', cancelId: 'cancelGenerateModal' });
    setupModal({ modalId: 'suggestionModal', openId: 'openSuggestionModal', closeId: 'closeSuggestionModal', cancelId: 'cancelSuggestionModal' });
    setupModal({ modalId: 'objectifModal', openId: 'openObjectifModal', closeId: 'closeObjectifModal', cancelId: 'cancelObjectifModal' });

    const generateForm = document.getElementById('generateRecipeForm');
    const generateError = document.getElementById('generateRecipeError');
    if (generateForm) {
        generateForm.addEventListener('submit', function (event) {
            const checkedIngredients = generateForm.querySelectorAll('input[name="ingredients_generate[]"]:checked');
            if (checkedIngredients.length === 0) {
                event.preventDefault();
                generateError.textContent = 'Choisissez au moins un ingrédient avant de générer une recette. Le régime est optionnel.';
            }
        });
    }

    const suggestionForm = document.getElementById('suggestionForm');
    const suggestionError = document.getElementById('suggestionError');
    if (suggestionForm) {
        suggestionForm.addEventListener('submit', function (event) {
            const checkedIngredients = suggestionForm.querySelectorAll('input[name="ingredients_suggestion[]"]:checked');
            if (checkedIngredients.length === 0) {
                event.preventDefault();
                suggestionError.textContent = 'Choisissez au moins un ingrédient pour la suggestion intelligente.';
            }
        });
    }

    const objectifForm = document.getElementById('objectifForm');
    const objectifError = document.getElementById('objectifError');
    if (objectifForm) {
        objectifForm.addEventListener('submit', function (event) {
            const objectif = document.getElementById('objectif_mode');
            if (!objectif || objectif.value.trim() === '') {
                event.preventDefault();
                objectifError.textContent = 'Choisissez un objectif avant d’afficher les recettes.';
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>

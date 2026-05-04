<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <section class="hero-panel">
        <h1 class="page-title"><?php echo t('hero_title'); ?></h1>
        <p class="hero-text"><?php echo t('hero_text'); ?></p>
    </section>

    <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="page" value="front_home">

        <div>
            <label for="search"><?php echo t('search_label'); ?></label>
            <input
                type="text"
                id="search"
                name="search"
                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                placeholder="<?php echo t('search_placeholder'); ?>"
            >
        </div>

        <button type="submit" class="btn-green"><i class="fas fa-search"></i> <?php echo t('search_btn'); ?></button>
        <button type="button" class="btn-green alt" id="openGenerateModal"><i class="fas fa-wand-magic-sparkles"></i> <?php echo t('generate_recipe'); ?></button>
        <a href="index.php?page=front_home&mode_metier=jour" class="btn-ghost btn-feature"><i class="fas fa-calendar-day"></i> <?php echo t('recipe_day'); ?></a>
        <button type="button" class="btn-ghost btn-feature" id="openSuggestionModal"><i class="fas fa-lightbulb"></i> <?php echo t('smart_suggestion'); ?></button>
        <a href="index.php?page=front_home&mode_metier=rapides" class="btn-ghost btn-feature"><i class="fas fa-bolt"></i> <?php echo t('quick_recipes'); ?></a>
        <button type="button" class="btn-ghost btn-feature" id="openObjectifModal"><i class="fas fa-bullseye"></i> <?php echo t('goal_mode'); ?></button>
        <a href="index.php?page=front_home" class="btn-ghost"><?php echo t('reset'); ?></a>
    </form>

    <div id="generateModal" class="generate-modal" style="display: none;">
        <div class="generate-modal-content">
            <div class="generate-modal-head">
                <h2><?php echo t('generate_recipe'); ?></h2>
                <button type="button" id="closeGenerateModal" class="modal-close-btn">×</button>
            </div>

            <form method="GET" action="index.php" class="generate-form" id="generateRecipeForm">
                <input type="hidden" name="page" value="front_home">
                <input type="hidden" name="generer_recette" value="1">

                <label for="regime_generate"><?php echo t('choose_regime'); ?></label>
                <select id="regime_generate" name="regime_generate">
                    <option value=""><?php echo t('all_regimes'); ?></option>
                    <?php foreach (['Végétarien', 'Végan', 'Sans gluten', 'Protéiné', 'Faible en calories'] as $regimeOption): ?>
                        <option value="<?php echo htmlspecialchars($regimeOption); ?>" <?php echo (($selectedRegime ?? '') === $regimeOption) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($regimeOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label><?php echo t('choose_ingredients'); ?></label>
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
                    <p class="helper-text"><?php echo t('no_ingredients'); ?></p>
                <?php endif; ?>

                <div class="error-message" id="generateRecipeError">
                    <?php if (!empty($generateMode) && empty($selectedIngredients)): ?>
                        <?php echo t('need_ingredient_generate'); ?>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-green"><i class="fas fa-wand-magic-sparkles"></i> <?php echo t('generate_recipe'); ?></button>
                    <button type="button" class="btn-ghost" id="cancelGenerateModal"><?php echo t('cancel'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div id="suggestionModal" class="generate-modal" style="display: none;">
        <div class="generate-modal-content">
            <div class="generate-modal-head">
                <h2><?php echo t('smart_suggestion'); ?></h2>
                <button type="button" id="closeSuggestionModal" class="modal-close-btn">×</button>
            </div>

            <form method="GET" action="index.php" class="generate-form" id="suggestionForm">
                <input type="hidden" name="page" value="front_home">
                <input type="hidden" name="mode_metier" value="suggestion">

                <p class="helper-text"><?php echo t('smart_help'); ?></p>

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
                    <p class="helper-text"><?php echo t('no_ingredients'); ?></p>
                <?php endif; ?>

                <div class="error-message" id="suggestionError"></div>

                <div class="form-actions">
                    <button type="submit" class="btn-green"><i class="fas fa-lightbulb"></i> <?php echo t('show_suggestions'); ?></button>
                    <button type="button" class="btn-ghost" id="cancelSuggestionModal"><?php echo t('cancel'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <div id="objectifModal" class="generate-modal" style="display: none;">
        <div class="generate-modal-content small-modal">
            <div class="generate-modal-head">
                <h2><?php echo t('goal_mode'); ?></h2>
                <button type="button" id="closeObjectifModal" class="modal-close-btn">×</button>
            </div>

            <form method="GET" action="index.php" class="generate-form" id="objectifForm">
                <input type="hidden" name="page" value="front_home">
                <input type="hidden" name="mode_metier" value="objectif">

                <label for="objectif_mode"><?php echo t('choose_goal'); ?></label>
                <select id="objectif_mode" name="objectif_mode">
                    <option value=""><?php echo t('choose'); ?></option>
                    <?php foreach (($availableObjectifs ?? []) as $objectifOption): ?>
                        <option value="<?php echo htmlspecialchars($objectifOption); ?>" <?php echo (($selectedObjectif ?? '') === $objectifOption) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($objectifOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="error-message" id="objectifError"></div>

                <div class="form-actions">
                    <button type="submit" class="btn-green"><i class="fas fa-bullseye"></i> <?php echo t('show_recipes'); ?></button>
                    <button type="button" class="btn-ghost" id="cancelObjectifModal"><?php echo t('cancel'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php $hideRecetteGrid = (!empty($generateMode) && empty($selectedIngredients)) || (($businessMode ?? '') === 'suggestion' && empty($selectedSmartIngredients)) || (($businessMode ?? '') === 'objectif' && empty($selectedObjectif)); ?>

    <?php if (!empty($resultTitle)): ?>
        <div class="generate-result-box">
            <strong><?php
                $resultKey = [
                    'Résultat Générer recette' => 'result_generate',
                    'Recette du jour' => 'recipe_day',
                    'Recettes rapides' => 'quick_recipes',
                    'Suggestion intelligente' => 'smart_suggestion',
                    'Mode objectif' => 'goal_mode',
                    'Recherche par titre' => 'result_search',
                ][$resultTitle] ?? '';
                echo htmlspecialchars($resultKey ? t($resultKey) : $resultTitle);
            ?></strong>
            <?php if (($businessMode ?? '') === 'rapides'): ?>
                <span> — <?php echo t('result_fast_suffix'); ?></span>
            <?php elseif (($businessMode ?? '') === 'objectif' && !empty($selectedObjectif)): ?>
                <span> — <?php echo t('goal_chosen'); ?> : <?php echo htmlspecialchars($selectedObjectif); ?></span>
            <?php elseif (($businessMode ?? '') === 'suggestion' && !empty($selectedSmartIngredients)): ?>
                <span> — <?php echo t('ingredients_chosen'); ?> : <?php echo htmlspecialchars(implode(', ', $selectedSmartIngredients)); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!$hideRecetteGrid): ?>
        <div class="recette-grid" id="recetteGrid">
            <?php if (!empty($recettes)): ?>
                <?php foreach ($recettes as $recette): ?>
                    <article class="recette-card">
                        <div class="card-badge"><?php echo t('recipe'); ?></div>
                        <h3><?php echo htmlspecialchars($recette['titre']); ?></h3>
                        <div class="recette-meta"><strong><?php echo t('objective'); ?> :</strong> <?php echo htmlspecialchars($recette['objectif']); ?></div>
                        <div class="recette-meta"><strong><?php echo t('regime'); ?> :</strong> <?php echo htmlspecialchars($recette['regime']); ?></div>
                        <div class="recette-meta"><strong><?php echo t('duration'); ?> :</strong> <?php echo (int) $recette['duree']; ?> min</div>
                        <?php if (isset($recette['match_score'])): ?>
                            <div class="smart-score">
                                <?php echo t('compatibility'); ?> : <?php echo (int) $recette['match_score']; ?>%
                                <span>(<?php echo (int) $recette['match_count']; ?>/<?php echo (int) $recette['match_total']; ?> <?php echo t('ingredients'); ?>)</span>
                            </div>
                        <?php endif; ?>
                        <div class="recette-actions">
                            <a href="index.php?page=front_recette_detail&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-green"><i class="fas fa-utensils"></i> <?php echo t('view_details'); ?></a>
                            <a href="index.php?page=front_save_recette&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-ghost"><i class="fas fa-bookmark"></i> <?php echo t('save'); ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="form-card">
                    <p><?php echo t('no_recipe_found'); ?></p>
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
                generateError.textContent = <?php echo json_encode(t('need_ingredient_generate'), JSON_UNESCAPED_UNICODE); ?>;
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
                suggestionError.textContent = <?php echo json_encode(t('js_need_suggestion'), JSON_UNESCAPED_UNICODE); ?>;
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
                objectifError.textContent = <?php echo json_encode(t('js_need_goal'), JSON_UNESCAPED_UNICODE); ?>;
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>

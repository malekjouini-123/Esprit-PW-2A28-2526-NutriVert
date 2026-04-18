<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container form-container">
    <div class="form-card wide-card">
        <h1 class="page-title">Ajouter une recette avec ses étapes</h1>

        <?php if (!empty($errors['global'])): ?>
            <div class="error-banner"><?php echo htmlspecialchars($errors['global']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=back_recette_store_full" id="recetteInstructionForm">
            <h2 class="section-subtitle">Informations recette</h2>

            <label for="titre">Titre</label>
            <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($old['titre'] ?? ''); ?>">
            <div class="error-message"><?php echo htmlspecialchars($errors['titre'] ?? ''); ?></div>

            <label for="objectif">Objectif</label>
            <input type="text" id="objectif" name="objectif" value="<?php echo htmlspecialchars($old['objectif'] ?? ''); ?>">
            <div class="error-message"><?php echo htmlspecialchars($errors['objectif'] ?? ''); ?></div>

            <label for="regime">Régime</label>
            <input type="text" id="regime" name="regime" value="<?php echo htmlspecialchars($old['regime'] ?? ''); ?>">
            <div class="error-message"><?php echo htmlspecialchars($errors['regime'] ?? ''); ?></div>

            <label for="duree">Durée</label>
            <input type="text" id="duree" name="duree" value="<?php echo htmlspecialchars($old['duree'] ?? ''); ?>">
            <div class="error-message"><?php echo htmlspecialchars($errors['duree'] ?? ''); ?></div>

            <h2 class="section-subtitle">Étapes de la recette</h2>
            <div id="steps-global-error" class="error-message"></div>

            <div id="steps-container">
                <?php
                $oldEtapes = $old['etape'] ?? [''];
                $oldDescriptions = $old['description'] ?? [''];
                $oldIngredients = $old['ingredient_produit'] ?? ['[]'];

                foreach ($oldEtapes as $i => $oldEtape):
                ?>
                    <div class="step-block">
                        <label>Étape</label>
                        <input type="text" name="etape[]" value="<?php echo htmlspecialchars((string)$oldEtape); ?>">
                        <div class="error-message"><?php echo htmlspecialchars($errors['etape'][$i] ?? ''); ?></div>

                        <label>Description</label>
                        <textarea name="description[]"><?php echo htmlspecialchars((string)($oldDescriptions[$i] ?? '')); ?></textarea>
                        <div class="error-message"><?php echo htmlspecialchars($errors['description'][$i] ?? ''); ?></div>

                        <label>Ajouter des ingrédients</label>

                        <div class="ingredient-form">
                            <input type="text" class="nom_produit" placeholder="Nom du produit">
                            <input type="text" class="quantite" placeholder="Quantité">
                            <input type="text" class="image" placeholder="Image URL (optionnel)">
                            <button type="button" class="add-ingredient-btn">Ajouter</button>
                        </div>

                        <div class="ingredient-inline-error error-message"></div>
                        <div class="error-message"><?php echo htmlspecialchars($errors['ingredient_produit'][$i] ?? ''); ?></div>

                        <table border="1" width="100%" class="ingredients-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <input
                            type="hidden"
                            name="ingredient_produit[]"
                            class="ingredients-json"
                            value="<?php echo htmlspecialchars((string)($oldIngredients[$i] ?? '[]')); ?>"
                        >

                        <?php if ($i > 0): ?>
                            <button type="button" class="btn-remove remove-step-btn">Supprimer cette étape</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-actions split-actions">
                <button type="button" id="add-step-btn" class="btn-green alt">+ Ajouter une étape</button>
                <div>
                    <button type="submit" class="btn-green">Enregistrer</button>
                    <a href="index.php?page=back_recettes" class="btn-ghost">Retour</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setupStep(stepBlock) {
        const addBtn = stepBlock.querySelector('.add-ingredient-btn');
        const nomInput = stepBlock.querySelector('.nom_produit');
        const quantiteInput = stepBlock.querySelector('.quantite');
        const imageInput = stepBlock.querySelector('.image');
        const tableBody = stepBlock.querySelector('tbody');
        const hiddenInput = stepBlock.querySelector('.ingredients-json');
        const inlineError = stepBlock.querySelector('.ingredient-inline-error');

        let ingredients = [];

        try {
            const initialValue = hiddenInput.value.trim();
            if (initialValue !== '') {
                const parsed = JSON.parse(initialValue);
                if (Array.isArray(parsed)) {
                    ingredients = parsed;
                }
            }
        } catch (e) {
            ingredients = [];
        }

        function renderIngredients() {
            tableBody.innerHTML = '';

            ingredients.forEach((ingredient) => {
                const nom = ingredient.nom_produit ? ingredient.nom_produit : '';
                const quantite = ingredient.quantite ? ingredient.quantite : '';
                const image = ingredient.image ? ingredient.image : '';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(nom)}</td>
                    <td>${escapeHtml(quantite)}</td>
                    <td>${image !== '' ? `<img src="${escapeHtml(image)}" width="50" alt="">` : ''}</td>
                `;
                tableBody.appendChild(row);
            });

            hiddenInput.value = JSON.stringify(ingredients);
        }

        addBtn.addEventListener('click', function () {
            inlineError.textContent = '';

            const nom = nomInput.value.trim();
            const quantite = quantiteInput.value.trim();
            const image = imageInput.value.trim();

            if (nom === '' || quantite === '') {
                inlineError.textContent = 'Nom du produit et quantité sont obligatoires.';
                return;
            }

            ingredients.push({
                nom_produit: nom,
                quantite: quantite,
                image: image
            });

            renderIngredients();

            nomInput.value = '';
            quantiteInput.value = '';
            imageInput.value = '';
        });

        renderIngredients();
    }

    document.querySelectorAll('.step-block').forEach(function (stepBlock) {
        setupStep(stepBlock);
    });

    document.getElementById('add-step-btn').addEventListener('click', function () {
        const container = document.getElementById('steps-container');
        const stepIndex = container.querySelectorAll('.step-block').length;

        const newStep = document.createElement('div');
        newStep.className = 'step-block';
        newStep.innerHTML = `
            <label>Étape</label>
            <input type="text" name="etape[]" value="">
            <div class="error-message"></div>

            <label>Description</label>
            <textarea name="description[]"></textarea>
            <div class="error-message"></div>

            <label>Ajouter des ingrédients</label>

            <div class="ingredient-form">
                <input type="text" class="nom_produit" placeholder="Nom du produit">
                <input type="text" class="quantite" placeholder="Quantité">
                <input type="text" class="image" placeholder="Image URL (optionnel)">
                <button type="button" class="add-ingredient-btn">Ajouter</button>
            </div>

            <div class="ingredient-inline-error error-message"></div>
            <div class="error-message"></div>

            <table border="1" width="100%" class="ingredients-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <input type="hidden" name="ingredient_produit[]" class="ingredients-json" value="[]">

            <button type="button" class="btn-remove remove-step-btn">Supprimer cette étape</button>
        `;

        container.appendChild(newStep);
        setupStep(newStep);

        newStep.querySelector('.remove-step-btn').addEventListener('click', function () {
            newStep.remove();
        });
    });

    document.querySelectorAll('.remove-step-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            this.closest('.step-block').remove();
        });
    });
});
</script>

<?php require __DIR__ . '/../../partials/footer.php'; ?>
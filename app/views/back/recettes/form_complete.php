<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container form-container">
    <div class="form-card wide-card">
        <h1 class="page-title"><?php echo htmlspecialchars($formTitle ?? 'Ajouter une recette avec ses étapes'); ?></h1>

        <?php if (!empty($errors['global'])): ?>
            <div class="error-banner"><?php echo htmlspecialchars($errors['global']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=<?php echo htmlspecialchars($action ?? 'back_recette_store_full'); ?>" id="recetteInstructionForm" novalidate>
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
                            <button type="button" class="add-ingredient-btn"><i class="fas fa-plus"></i> Ajouter</button>
                        </div>

                        <div class="ingredient-inline-error error-message"></div>
                        <div class="error-message"><?php echo htmlspecialchars($errors['ingredient_produit'][$i] ?? ''); ?></div>

                        <table border="1" width="100%" class="ingredients-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Image</th>
                                    <th>Actions</th>
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
                            <button type="button" class="btn-remove remove-step-btn"><i class="fas fa-trash"></i> Supprimer cette étape</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-actions split-actions">
                <button type="button" id="add-step-btn" class="btn-green alt"><i class="fas fa-layer-group"></i> Ajouter une étape</button>
                <div>
                    <button type="submit" class="btn-green"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="index.php?page=back_recettes_full_edit" class="btn-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
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
        let editingIndex = -1;

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

        function resetIngredientForm() {
            nomInput.value = '';
            quantiteInput.value = '';
            imageInput.value = '';
            editingIndex = -1;
            addBtn.textContent = 'Ajouter';
        }

        function renderIngredients() {
            tableBody.innerHTML = '';

            ingredients.forEach((ingredient, index) => {
                const nom = ingredient.nom_produit ? ingredient.nom_produit : '';
                const quantite = ingredient.quantite ? ingredient.quantite : '';
                const image = ingredient.image ? ingredient.image : '';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(nom)}</td>
                    <td>${escapeHtml(quantite)}</td>
                    <td>${image !== '' ? `<img src="${escapeHtml(image)}" width="50" alt="">` : ''}</td>
                    <td>
                        <button type="button" class="btn-green alt btn-edit-ingredient" data-index="${index}">Modifier</button>
                        <button type="button" class="btn-remove btn-delete-ingredient" data-index="${index}">Supprimer</button>
                    </td>
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

            const ingredientData = {
                nom_produit: nom,
                quantite: quantite,
                image: image
            };

            if (editingIndex >= 0) {
                ingredients[editingIndex] = ingredientData;
            } else {
                ingredients.push(ingredientData);
            }

            renderIngredients();
            resetIngredientForm();
        });

        tableBody.addEventListener('click', function (event) {
            const editBtn = event.target.closest('.btn-edit-ingredient');
            const deleteBtn = event.target.closest('.btn-delete-ingredient');

            if (editBtn) {
                const index = parseInt(editBtn.getAttribute('data-index'), 10);
                const ingredient = ingredients[index];

                if (!ingredient) {
                    return;
                }

                nomInput.value = ingredient.nom_produit ? ingredient.nom_produit : '';
                quantiteInput.value = ingredient.quantite ? ingredient.quantite : '';
                imageInput.value = ingredient.image ? ingredient.image : '';
                editingIndex = index;
                addBtn.textContent = 'Mettre à jour';
                inlineError.textContent = '';
                return;
            }

            if (deleteBtn) {
                const index = parseInt(deleteBtn.getAttribute('data-index'), 10);

                if (Number.isNaN(index)) {
                    return;
                }

                ingredients.splice(index, 1);

                if (editingIndex === index) {
                    resetIngredientForm();
                } else if (editingIndex > index) {
                    editingIndex -= 1;
                }

                renderIngredients();
            }
        });

        renderIngredients();
    }

    document.querySelectorAll('.step-block').forEach(function (stepBlock) {
        setupStep(stepBlock);
    });

    document.getElementById('add-step-btn').addEventListener('click', function () {
        const container = document.getElementById('steps-container');

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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <input type="hidden" name="ingredient_produit[]" class="ingredients-json" value="[]">

            <button type="button" class="btn-remove remove-step-btn"><i class="fas fa-trash"></i> Supprimer cette étape</button>
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
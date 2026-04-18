<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container form-container">
    <div class="form-card">
        <h1 class="page-title">
            <?php echo strpos($action, 'update') !== false ? 'Modifier instruction' : 'Ajouter instruction'; ?>
        </h1>

        <form method="POST" action="index.php?page=<?php echo htmlspecialchars($action); ?>">
            <label for="id_recette">Recette</label>
            <select id="id_recette" name="id_recette">
                <option value="">Choisir une recette</option>
                <?php foreach ($recettes as $recette): ?>
                    <option value="<?php echo (int) $recette['id_recette']; ?>"
                        <?php echo ((string) ($instruction['id_recette'] ?? '') === (string) $recette['id_recette']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($recette['titre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="error-message"><?php echo $errors['id_recette'] ?? ''; ?></div>

            <label for="etape">Étape</label>
            <input type="text" id="etape" name="etape" value="<?php echo htmlspecialchars($instruction['etape'] ?? ''); ?>">
            <div class="error-message"><?php echo $errors['etape'] ?? ''; ?></div>

            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($instruction['description'] ?? ''); ?></textarea>
            <div class="error-message"><?php echo $errors['description'] ?? ''; ?></div>

            <label>Ajouter des ingrédients</label>

            <div class="ingredient-form">
                <input type="text" id="nom_produit" placeholder="Nom du produit">
                <input type="text" id="quantite" placeholder="Quantité">
                <input type="text" id="image" placeholder="Image URL">
                <button type="button" id="addIngredientBtn">Ajouter</button>
            </div>

            <table border="1" width="100%" class="ingredients-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody id="ingredientsTableBody"></tbody>
            </table>

            <input
                type="hidden"
                name="ingredient_produit"
                id="ingredient_produit"
                value="<?php echo htmlspecialchars($instruction['ingredient_produit'] ?? '[]'); ?>"
            >
            <div class="error-message"><?php echo $errors['ingredient_produit'] ?? ''; ?></div>

            <div class="form-actions">
                <button type="submit" class="btn-green">Enregistrer</button>
                <a href="index.php?page=back_instructions" class="btn-ghost">Retour</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nomInput = document.getElementById('nom_produit');
    const quantiteInput = document.getElementById('quantite');
    const imageInput = document.getElementById('image');
    const addBtn = document.getElementById('addIngredientBtn');
    const tableBody = document.getElementById('ingredientsTableBody');
    const hiddenInput = document.getElementById('ingredient_produit');

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

        ingredients.forEach(function (ingredient) {
            const nom = ingredient.nom_produit ? ingredient.nom_produit : '';
            const quantite = ingredient.quantite ? ingredient.quantite : '';
            const image = ingredient.image ? ingredient.image : 'https://placehold.co/60x60?text=Produit';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${nom}</td>
                <td>${quantite}</td>
                <td><img src="${image}" width="50" alt=""></td>
            `;
            tableBody.appendChild(row);
        });

        hiddenInput.value = JSON.stringify(ingredients);
    }

    addBtn.addEventListener('click', function () {
        const nom = nomInput.value.trim();
        const quantite = quantiteInput.value.trim();
        const image = imageInput.value.trim();

        if (nom === '' || quantite === '') {
            alert('Nom du produit et quantité sont obligatoires.');
            return;
        }

        ingredients.push({
            nom_produit: nom,
            quantite: quantite,
            image: image !== '' ? image : 'https://placehold.co/60x60?text=Produit'
        });

        renderIngredients();

        nomInput.value = '';
        quantiteInput.value = '';
        imageInput.value = '';
    });

    renderIngredients();
});
</script>

<?php require __DIR__ . '/../../partials/footer.php'; ?>
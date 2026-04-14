<?php require __DIR__ . '/../../partials/header.php'; ?>
<div class="container form-container">
    <div class="form-card">
        <h1 class="page-title"><?php echo strpos($action, 'update') !== false ? 'Modifier instruction' : 'Ajouter instruction'; ?></h1>

        <form method="POST" action="index.php?page=<?php echo htmlspecialchars($action); ?>" class="validate-instruction-form">
            <label for="id_recette">Recette</label>
            <select id="id_recette" name="id_recette">
                <option value="">Choisir une recette</option>
                <?php foreach ($recettes as $recette): ?>
                    <option value="<?php echo (int)$recette['id_recette']; ?>" <?php echo ((string)($instruction['id_recette'] ?? '') === (string)$recette['id_recette']) ? 'selected' : ''; ?>>
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

            <label for="ingredient_produit">Ingrédients / Produits (JSON)</label>
            <textarea id="ingredient_produit" name="ingredient_produit"><?php echo htmlspecialchars($instruction['ingredient_produit'] ?? '[]'); ?></textarea>
            <div class="error-message"><?php echo $errors['ingredient_produit'] ?? ''; ?></div>

            <div class="form-actions">
                <button type="submit" class="btn-green">Enregistrer</button>
                <a href="index.php?page=back_instructions" class="btn-ghost">Retour</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../../partials/footer.php'; ?>

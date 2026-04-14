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
            <div class="error-message"><?php echo $errors['titre'] ?? ''; ?></div>

            <label for="objectif">Objectif</label>
            <input type="text" id="objectif" name="objectif" value="<?php echo htmlspecialchars($old['objectif'] ?? ''); ?>">
            <div class="error-message"><?php echo $errors['objectif'] ?? ''; ?></div>

            <label for="regime">Régime</label>
            <input type="text" id="regime" name="regime" value="<?php echo htmlspecialchars($old['regime'] ?? ''); ?>">
            <div class="error-message"><?php echo $errors['regime'] ?? ''; ?></div>

            <label for="duree">Durée</label>
            <input type="text" id="duree" name="duree" value="<?php echo htmlspecialchars($old['duree'] ?? ''); ?>">
            <div class="error-message"><?php echo $errors['duree'] ?? ''; ?></div>

            <h2 class="section-subtitle">Étapes de la recette</h2>
            <p class="helper-text">Astuce : pour chaque étape, utilise un JSON comme celui-ci pour les ingrédients : <br><code>[{"nom_produit":"Sucre","quantite":"100g","image":"https://placehold.co/60x60?text=Sucre"}]</code></p>

            <div id="steps-container">
                <?php
                $etapes = $old['etape'] ?? [''];
                $descriptions = $old['description'] ?? [''];
                $ingredients = $old['ingredient_produit'] ?? ['[]'];
                foreach ($etapes as $i => $etapeValue):
                ?>
                    <div class="step-block">
                        <label>Étape</label>
                        <input type="text" name="etape[]" value="<?php echo htmlspecialchars((string)$etapeValue); ?>">
                        <div class="error-message"><?php echo $errors['etape'][$i] ?? ''; ?></div>

                        <label>Description</label>
                        <textarea name="description[]"><?php echo htmlspecialchars((string)($descriptions[$i] ?? '')); ?></textarea>
                        <div class="error-message"><?php echo $errors['description'][$i] ?? ''; ?></div>

                        <label>Ingrédients / Produits (JSON)</label>
                        <textarea name="ingredient_produit[]"><?php echo htmlspecialchars((string)($ingredients[$i] ?? '[]')); ?></textarea>
                        <div class="error-message"><?php echo $errors['ingredient_produit'][$i] ?? ''; ?></div>

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
<?php require __DIR__ . '/../../partials/footer.php'; ?>

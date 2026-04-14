<?php require __DIR__ . '/../../partials/header.php'; ?>
<div class="container form-container">
    <div class="form-card">
        <h1 class="page-title"><?php echo strpos($action, 'update') !== false ? 'Modifier recette' : 'Ajouter recette'; ?></h1>

        <form method="POST" action="index.php?page=<?php echo htmlspecialchars($action); ?>" class="validate-recette-form">
            <label for="titre">Titre</label>
            <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($recette['titre'] ?? ''); ?>">
            <div class="error-message"><?php echo $errors['titre'] ?? ''; ?></div>

            <label for="objectif">Objectif</label>
            <input type="text" id="objectif" name="objectif" value="<?php echo htmlspecialchars($recette['objectif'] ?? ''); ?>">
            <div class="error-message"><?php echo $errors['objectif'] ?? ''; ?></div>

            <label for="regime">Régime</label>
            <input type="text" id="regime" name="regime" value="<?php echo htmlspecialchars($recette['regime'] ?? ''); ?>">
            <div class="error-message"><?php echo $errors['regime'] ?? ''; ?></div>

            <label for="duree">Durée</label>
            <input type="text" id="duree" name="duree" value="<?php echo htmlspecialchars((string)($recette['duree'] ?? '')); ?>">
            <div class="error-message"><?php echo $errors['duree'] ?? ''; ?></div>

            <div class="form-actions">
                <button type="submit" class="btn-green">Enregistrer</button>
                <a href="index.php?page=back_recettes" class="btn-ghost">Retour</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../../partials/footer.php'; ?>

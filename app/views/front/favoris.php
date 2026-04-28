<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <section class="hero-panel">
        <h1 class="page-title">Mes recettes enregistrées</h1>
        <p class="hero-text">Voici les recettes que vous avez enregistrées depuis le FrontOffice.</p>
    </section>

    <div class="recette-grid">
        <?php if (!empty($recettes)): ?>
            <?php foreach ($recettes as $recette): ?>
                <article class="recette-card saved-recette-card">
                    <div class="card-badge"><i class="fas fa-bookmark"></i> Enregistrée</div>
                    <h3><?php echo htmlspecialchars($recette['titre']); ?></h3>
                    <div class="recette-meta"><strong>Objectif :</strong> <?php echo htmlspecialchars($recette['objectif']); ?></div>
                    <div class="recette-meta"><strong>Régime :</strong> <?php echo htmlspecialchars($recette['regime']); ?></div>
                    <div class="recette-meta"><strong>Durée :</strong> <?php echo (int) $recette['duree']; ?> min</div>
                    <div class="recette-actions">
                        <a href="index.php?page=front_recette_detail&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-green"><i class="fas fa-utensils"></i> Voir détails</a>
                        <a href="index.php?page=front_remove_favori&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-remove"><i class="fas fa-trash"></i> Supprimer</a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="form-card empty-favoris">
                <p>Aucune recette enregistrée pour le moment.</p>
                <a href="index.php?page=front_home" class="btn-green"><i class="fas fa-arrow-left"></i> Voir les recettes</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>

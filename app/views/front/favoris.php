<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <section class="hero-panel">
        <h1 class="page-title"><?php echo t('saved_title'); ?></h1>
        <p class="hero-text"><?php echo t('saved_text'); ?></p>
    </section>

    <div class="recette-grid">
        <?php if (!empty($recettes)): ?>
            <?php foreach ($recettes as $recette): ?>
                <article class="recette-card saved-recette-card">
                    <div class="card-badge"><i class="fas fa-bookmark"></i> <?php echo t('saved_badge'); ?></div>
                    <h3><?php echo htmlspecialchars($recette['titre']); ?></h3>
                    <div class="recette-meta"><strong><?php echo t('objective'); ?> :</strong> <?php echo htmlspecialchars($recette['objectif']); ?></div>
                    <div class="recette-meta"><strong><?php echo t('regime'); ?> :</strong> <?php echo htmlspecialchars($recette['regime']); ?></div>
                    <div class="recette-meta"><strong><?php echo t('duration'); ?> :</strong> <?php echo (int) $recette['duree']; ?> min</div>
                    <div class="recette-actions">
                        <a href="index.php?page=front_recette_detail&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-green"><i class="fas fa-utensils"></i> <?php echo t('view_details'); ?></a>
                        <a href="index.php?page=front_remove_favori&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-remove"><i class="fas fa-trash"></i> <?php echo t('delete'); ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="form-card empty-favoris">
                <p><?php echo t('no_saved'); ?></p>
                <a href="index.php?page=front_home" class="btn-green"><i class="fas fa-arrow-left"></i> <?php echo t('see_recipes'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>

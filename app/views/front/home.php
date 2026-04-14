<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <section class="hero-panel">
        <h1 class="page-title">Nos recettes NutriVert</h1>
        <p class="hero-text">Découvrez des recettes saines, durables et agréables à consulter.</p>
    </section>

    <div class="recette-grid">
        <?php foreach ($recettes as $recette): ?>
            <article class="recette-card">
                <div class="card-badge">Recette</div>
                <h3><?php echo htmlspecialchars($recette['titre']); ?></h3>
                <div class="recette-meta"><strong>Objectif :</strong> <?php echo htmlspecialchars($recette['objectif']); ?></div>
                <div class="recette-meta"><strong>Régime :</strong> <?php echo htmlspecialchars($recette['regime']); ?></div>
                <div class="recette-meta"><strong>Durée :</strong> <?php echo (int)$recette['duree']; ?> min</div>
                <div class="recette-actions">
                    <a href="index.php?page=front_recette_detail&id=<?php echo (int)$recette['id_recette']; ?>" class="btn-green">Voir détails</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>

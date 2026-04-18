<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <section class="hero-panel">
        <h1 class="page-title">Nos recettes NutriVert</h1>
        <p class="hero-text">Découvrez des recettes saines, durables et agréables à consulter.</p>
    </section>

    <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="page" value="front_home">

        <label for="search">Recherche par titre de recette</label>
        <input
            type="text"
            id="search"
            name="search"
            value="<?php echo htmlspecialchars($search ?? ''); ?>"
            placeholder="Rechercher une recette"
        >

        <button type="submit" class="btn-green">Rechercher</button>
        <a href="index.php?page=front_home" class="btn-ghost">Réinitialiser</a>
    </form>

    <div class="recette-grid">
        <?php if (!empty($recettes)): ?>
            <?php foreach ($recettes as $recette): ?>
                <article class="recette-card">
                    <div class="card-badge">Recette</div>
                    <h3><?php echo htmlspecialchars($recette['titre']); ?></h3>
                    <div class="recette-meta"><strong>Objectif :</strong> <?php echo htmlspecialchars($recette['objectif']); ?></div>
                    <div class="recette-meta"><strong>Régime :</strong> <?php echo htmlspecialchars($recette['regime']); ?></div>
                    <div class="recette-meta"><strong>Durée :</strong> <?php echo (int) $recette['duree']; ?> min</div>
                    <div class="recette-actions">
                        <a href="index.php?page=front_recette_detail&id=<?php echo (int) $recette['id_recette']; ?>" class="btn-green">Voir détails</a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="form-card">
                <p>Aucune recette trouvée.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
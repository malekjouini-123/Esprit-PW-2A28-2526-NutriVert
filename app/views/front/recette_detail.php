<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <div class="detail-card">
        <h1 class="page-title"><?php echo htmlspecialchars($recette['titre']); ?></h1>
        <div class="detail-summary">
            <div class="summary-item"><strong>Objectif</strong><span><?php echo htmlspecialchars($recette['objectif']); ?></span></div>
            <div class="summary-item"><strong>Régime</strong><span><?php echo htmlspecialchars($recette['regime']); ?></span></div>
            <div class="summary-item"><strong>Durée</strong><span><?php echo (int)$recette['duree']; ?> min</span></div>
        </div>

        <h2 class="section-subtitle">Étapes et ingrédients</h2>

        <div class="instructions-list">
            <?php foreach ($instructions as $instruction): ?>
                <section class="instruction-card">
                    <h4><?php echo htmlspecialchars($instruction['etape']); ?></h4>
                    <p><?php echo nl2br(htmlspecialchars($instruction['description'])); ?></p>
                    <?php $ingredients = json_decode($instruction['ingredient_produit'], true); ?>
                    <?php if (is_array($ingredients) && !empty($ingredients)): ?>
                        <div class="ingredients-list">
                            <?php foreach ($ingredients as $ingredient): ?>
                                <?php
                                $nom = $ingredient['nom_produit'] ?? 'Produit';
                                $quantite = $ingredient['quantite'] ?? '-';
                                $image = $ingredient['image'] ?? 'https://placehold.co/60x60?text=Food';
                                ?>
                                <div class="ingredient-item">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($nom); ?>">
                                    <div class="ingredient-info">
                                        <span class="ingredient-name"><?php echo htmlspecialchars($nom); ?></span>
                                        <span class="ingredient-qty">Quantité : <?php echo htmlspecialchars($quantite); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>

        <p class="back-link-wrap">
            <a href="index.php?page=front_home" class="btn-green">Retour aux recettes</a>
        </p>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>

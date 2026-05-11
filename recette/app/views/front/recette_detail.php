<?php require __DIR__ . '/../partials/header.php'; ?>
<?php $aiT = function ($text) use ($aiTranslationMap) { return $aiTranslationMap[$text] ?? $text; }; ?>
<div class="container">
    <form method="GET" action="index.php" class="ai-translate-form">
        <input type="hidden" name="page" value="front_recette_detail">
        <input type="hidden" name="id" value="<?php echo (int) $recette['id_recette']; ?>">
        <label for="ai_translate"><i class="fas fa-language"></i> Traduction AI</label>
        <select id="ai_translate" name="ai_translate">
            <option value="fr" <?php echo (($aiTargetLang ?? '') === 'fr') ? 'selected' : ''; ?>>Français</option>
            <option value="en" <?php echo (($aiTargetLang ?? '') === 'en') ? 'selected' : ''; ?>>English</option>
            <option value="ar" <?php echo (($aiTargetLang ?? '') === 'ar') ? 'selected' : ''; ?>>العربية</option>
        </select>
        <button type="submit" class="btn-green"><i class="fas fa-wand-magic-sparkles"></i> Traduire la page</button>
        <?php if (!empty($aiTranslateNote)): ?><small><?php echo htmlspecialchars($aiTranslateNote); ?></small><?php endif; ?>
    </form>

    <div class="detail-card">
        <h1 class="page-title"><?php echo htmlspecialchars($aiT($recette['titre'])); ?></h1>
        <div class="detail-summary">
            <div class="summary-item"><strong><?php echo t('objective'); ?></strong><span><?php echo htmlspecialchars($aiT($recette['objectif'])); ?></span></div>
            <div class="summary-item"><strong><?php echo t('regime'); ?></strong><span><?php echo htmlspecialchars($aiT($recette['regime'])); ?></span></div>
            <div class="summary-item"><strong><?php echo t('duration'); ?></strong><span><?php echo (int)$recette['duree']; ?> min</span></div>
        </div>

        <h2 class="section-subtitle"><?php echo t('details_title'); ?></h2>

        <div class="instructions-list">
            <?php foreach ($instructions as $instruction): ?>
                <section class="instruction-card">
                    <h4><?php echo htmlspecialchars($aiT($instruction['etape'])); ?></h4>
                    <p><?php echo nl2br(htmlspecialchars($aiT($instruction['description']))); ?></p>
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
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($aiT($nom)); ?>">
                                    <div class="ingredient-info">
                                        <span class="ingredient-name"><?php echo htmlspecialchars($aiT($nom)); ?></span>
                                        <span class="ingredient-qty"><?php echo t('quantity'); ?> : <?php echo htmlspecialchars($aiT($quantite)); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>

        <p class="back-link-wrap">
            <a href="index.php?page=front_home" class="btn-green"><i class="fas fa-arrow-left"></i> <?php echo t('back_recipes'); ?></a>
        </p>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>

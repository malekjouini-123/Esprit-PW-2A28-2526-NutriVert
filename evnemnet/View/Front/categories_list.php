<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>Catégories d'Événements</h1>
    <p>Parcourez toutes nos catégories</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; margin-top: 40px;">
    <?php if (empty($categories)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px 0; color: #999;">
            <p>Aucune catégorie disponible pour le moment.</p>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $category): ?>
            <div class="section-card" style="margin-top: 0;">
                <div class="section-title">
                    <i class="bi bi-tag-fill"></i>
                    <span><?= htmlspecialchars($category['nom']) ?></span>
                </div>
                
                <p style="color: #4A5B4A; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">
                    <?= htmlspecialchars($category['description'] ?? 'Pas de description disponible') ?>
                </p>

                <div style="padding-top: 15px; border-top: 1px solid #E0E8E0;">
                    <a href="index.php?action=events&category=<?= $category['id'] ?>" class="btn-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 8px;">
                        Voir les événements
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

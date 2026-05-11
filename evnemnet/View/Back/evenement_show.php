<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="color: var(--deep); font-weight: 700; margin-bottom: 6px;"><?= htmlspecialchars($evenement['titre']) ?></h1>
        <p style="color: #8A9A86; margin: 0;">Détails de l’événement</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="admin.php?action=edit_event&id=<?= (int)$evenement['id'] ?>" class="btn-primary" style="text-decoration: none;">
            <i class="bi bi-pencil-square"></i> Modifier
        </a>
        <a href="admin.php?action=events" class="edit-btn" style="background: #eee; text-decoration: none;">Retour</a>
    </div>
</div>

<div class="section-card">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: start;">
        <div>
            <?php if (!empty($evenement['image_url'])): ?>
                <img src="<?= htmlspecialchars($evenement['image_url']) ?>" alt="<?= htmlspecialchars($evenement['titre']) ?>" style="width: 100%; max-height: 260px; object-fit: cover; border-radius: 18px;">
            <?php else: ?>
                <div style="width: 100%; height: 260px; background: #F5F9F5; border-radius: 18px; display:flex; align-items:center; justify-content:center; color:#999;">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="section-card" style="margin-top: 0; padding: 14px;">
                    <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Catégorie</div>
                    <div style="font-weight: 700; color: var(--deep);"><?= htmlspecialchars($evenement['categorie_nom'] ?? 'N/A') ?></div>
                </div>
                <div class="section-card" style="margin-top: 0; padding: 14px;">
                    <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Date</div>
                    <div style="font-weight: 700; color: var(--deep);"><?= date('d/m/Y H:i', strtotime($evenement['date_evenement'])) ?></div>
                </div>
                <div class="section-card" style="margin-top: 0; padding: 14px;">
                    <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Lieu</div>
                    <div style="font-weight: 700; color: var(--deep);"><?= htmlspecialchars($evenement['lieu']) ?></div>
                </div>
                <div class="section-card" style="margin-top: 0; padding: 14px;">
                    <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Prix</div>
                    <div style="font-weight: 700; color: var(--deep);"><?= number_format((float)$evenement['prix'], 2) ?> €</div>
                </div>
            </div>

            <div style="margin-top: 18px;">
                <div style="color: #8A9A86; font-size: 0.9rem; font-weight: 700; margin-bottom: 8px;">Description</div>
                <div style="color: #4A5B4A; line-height: 1.6;"><?= nl2br(htmlspecialchars($evenement['description'] ?? '')) ?></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>


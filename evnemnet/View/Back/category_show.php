<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="color: var(--deep); font-weight: 700; margin-bottom: 6px;"><?= htmlspecialchars($category['nom']) ?></h1>
        <p style="color: #8A9A86; margin: 0;">Détails de la catégorie</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="admin.php?action=edit_category&id=<?= (int)$category['id'] ?>" class="btn-primary" style="text-decoration: none;">
            <i class="bi bi-pencil-square"></i> Modifier
        </a>
        <a href="admin.php?action=categories" class="edit-btn" style="background: #eee; text-decoration: none;">Retour</a>
    </div>
</div>

<div class="section-card">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">ID</div>
            <div style="font-weight: 700; color: var(--deep);"><?= (int)$category['id'] ?></div>
        </div>
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Créée le</div>
            <div style="font-weight: 700; color: var(--deep);"><?= !empty($category['created_at']) ? date('d/m/Y', strtotime($category['created_at'])) : '-' ?></div>
        </div>
    </div>

    <div style="margin-top: 18px;">
        <div style="color: #8A9A86; font-size: 0.9rem; font-weight: 700; margin-bottom: 8px;">Description</div>
        <div style="color: #4A5B4A; line-height: 1.6;"><?= nl2br(htmlspecialchars($category['description'] ?? '')) ?></div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>


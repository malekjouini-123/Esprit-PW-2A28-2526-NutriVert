<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h1 style="color: var(--deep); font-weight: 700;">Gestion des Catégories</h1>
    <a href="admin.php?action=add_category" class="btn-primary">
        <i class="bi bi-plus-lg"></i> Nouvelle Catégorie
    </a>
</div>

<div class="section-card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Date de création</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td style="font-weight: 700; color: var(--coral);"><?= $c['id'] ?></td>
                <td style="font-weight: 600; color: var(--deep);"><?= htmlspecialchars($c['nom']) ?></td>
                <td><?= htmlspecialchars($c['description'] ?? '') ?></td>
                <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                <td style="text-align: right;">
                    <a href="admin.php?action=toggle_category_publish&id=<?= $c['id'] ?>" class="action-btn" title="<?= !empty($c['is_published']) ? 'Dépublier' : 'Publier' ?>">
                        <i class="bi <?= !empty($c['is_published']) ? 'bi-cloud-check-fill' : 'bi-cloud-arrow-up-fill' ?>"></i>
                    </a>
                    <a href="admin.php?action=show_category&id=<?= $c['id'] ?>" class="action-btn" title="Afficher">
                        <i class="bi bi-eye-fill"></i>
                    </a>
                    <a href="admin.php?action=edit_category&id=<?= $c['id'] ?>" class="action-btn btn-edit" title="Modifier">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="admin.php?action=delete_category&id=<?= $c['id'] ?>" class="action-btn btn-delete" title="Supprimer" onclick="return confirm('Supprimer cette catégorie ?')">
                        <i class="bi bi-trash3-fill"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>

<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h1 style="color: var(--deep); font-weight: 700;">Gestion des Événements</h1>
    <a href="admin.php?action=add_event" class="btn-primary">
        <i class="bi bi-plus-lg"></i> Nouvel Événement
    </a>
</div>

<div class="section-card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Date</th>
                <th>Lieu</th>
                <th>Prix</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($evenements as $e): ?>
            <tr>
                <td style="font-weight: 700; color: var(--coral);"><?= $e['id'] ?></td>
                <td>
                    <div style="font-weight: 600; color: var(--deep);"><?= htmlspecialchars($e['titre']) ?></div>
                    <small style="color: #8A9A86;"><?= substr(htmlspecialchars($e['description']), 0, 40) ?>...</small>
                </td>
                <td><span class="event-badge" style="margin: 0;"><?= htmlspecialchars($e['categorie_nom'] ?? 'N/A') ?></span></td>
                <td><?= date('d/m/Y H:i', strtotime($e['date_evenement'])) ?></td>
                <td><?= htmlspecialchars($e['lieu']) ?></td>
                <td style="font-weight: 700;"><?= number_format((float)$e['prix'], 2) ?> €</td>
                <td style="text-align: right;">
                    <a href="admin.php?action=toggle_event_publish&id=<?= $e['id'] ?>" class="action-btn" title="<?= !empty($e['is_published']) ? 'Dépublier' : 'Publier' ?>">
                        <i class="bi <?= !empty($e['is_published']) ? 'bi-cloud-check-fill' : 'bi-cloud-arrow-up-fill' ?>"></i>
                    </a>
                    <a href="admin.php?action=show_event&id=<?= $e['id'] ?>" class="action-btn" title="Afficher">
                        <i class="bi bi-eye-fill"></i>
                    </a>
                    <a href="admin.php?action=edit_event&id=<?= $e['id'] ?>" class="action-btn btn-edit" title="Modifier">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="admin.php?action=delete_event&id=<?= $e['id'] ?>" class="action-btn btn-delete" title="Supprimer" onclick="return confirm('Supprimer cet événement ?')">
                        <i class="bi bi-trash3-fill"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>

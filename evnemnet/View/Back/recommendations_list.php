<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="margin-bottom: 40px;">
    <h1 style="color: var(--deep); font-weight: 700;">Recommandations Personnalisées</h1>
    <p style="color: #8A9A86;">Liste des recommandations créées par les utilisateurs.</p>
</div>

<div class="section-card">
    <div class="section-title">
        <i class="bi bi-star-fill"></i>
        <span>Recommandations (<?= count($recommendations) ?>)</span>
    </div>

    <?php if (empty($recommendations)): ?>
        <p style="text-align: center; color: #8A9A86; padding: 40px;">Aucune recommandation personnalisée pour le moment.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Budget Max</th>
                        <th>Localisation</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recommendations as $rec): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$rec['id']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($rec['titre']) ?></strong>
                                <br>
                                <small style="color: #8A9A86;"><?= htmlspecialchars(substr($rec['description'], 0, 50)) ?>...</small>
                            </td>
                            <td><?= htmlspecialchars($rec['categorie_preferee'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($rec['budget_max']): ?>
                                    <?= number_format((float)$rec['budget_max'], 2) ?> €
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($rec['localisation'] ?? 'N/A') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($rec['created_at'])) ?></td>
                            <td>
                                <a href="admin.php?action=show_recommendation&id=<?= $rec['id'] ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.9rem;">
                                    <i class="bi bi-eye"></i> Voir
                                </a>
                                <a href="admin.php?action=delete_recommendation&id=<?= $rec['id'] ?>" class="btn-danger" style="padding: 6px 12px; font-size: 0.9rem; margin-left: 5px;"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette recommandation ?')">
                                    <i class="bi bi-trash"></i> Suppr
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>
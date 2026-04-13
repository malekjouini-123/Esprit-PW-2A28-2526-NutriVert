<?php
declare(strict_types=1);
/**
 * Vue admin pour la liste des événements.
 * @var Evenement[] $evenements
 */
?>
<style>
    .adm-section {
        background: #fff;
        border: 1px solid rgba(45, 79, 30, 0.1);
        border-radius: 0.75rem;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 2px 12px rgba(45, 79, 30, 0.04);
    }
    .adm-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .adm-section h2 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d4f1e;
        margin: 0;
    }
    .btn-adm {
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.2s;
    }
    .btn-adm-primary { background: #3d6b2e; color: #fff; }
    .btn-adm-primary:hover { background: #325c25; }
    .btn-adm-danger { background: #a53c32; color: #fff; }
    .btn-adm-danger:hover { background: #8e332a; }
    .btn-adm-muted { background: #eef3e8; color: #2d4f1e; border: 1px solid rgba(45, 79, 30, 0.15); }
    
    .adm-table-wrap { overflow-x: auto; }
    table.adm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    table.adm-table th {
        text-align: left;
        padding: 0.8rem;
        background: #f0f4eb;
        color: #2d4f1e;
        font-weight: 700;
        border-bottom: 2px solid rgba(45, 79, 30, 0.12);
    }
    table.adm-table td {
        padding: 0.8rem;
        border-bottom: 1px solid rgba(45, 79, 30, 0.08);
        vertical-align: middle;
    }
    .actions-cell {
        display: flex;
        gap: 0.75rem;
    }
    .actions-cell a {
        color: #3d6b2e;
        text-decoration: none;
        font-weight: 600;
    }
    .actions-cell a.delete-link { color: #a53c32; }
</style>

<div class="adm-header-flex">
    <h2><i class="fas fa-calendar-alt"></i> Gestion des Événements</h2>
    <a href="admin_evenements.php?action=add" class="btn-adm btn-adm-primary">
        <i class="fas fa-plus"></i> Nouvel événement
    </a>
</div>

<section class="adm-section">
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Date</th>
                    <th>Lieu</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($evenements)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 2rem; color: #666;">
                            Aucun événement trouvé.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($evenements as $ev): ?>
                        <tr>
                            <td><?= $ev->id ?></td>
                            <td><strong><?= e($ev->titre) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($ev->date_evenement)) ?></td>
                            <td><?= e($ev->lieu) ?></td>
                            <td class="actions-cell">
                                <a href="admin_evenements.php?action=edit&id=<?= $ev->id ?>"><i class="fas fa-edit"></i> Modifier</a>
                                <a href="admin_evenements.php?action=delete&id=<?= $ev->id ?>" class="delete-link" onclick="return confirm('Supprimer cet événement ?')"><i class="fas fa-trash"></i> Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

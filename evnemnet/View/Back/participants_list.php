<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="margin-bottom: 40px;">
    <h1 style="color: var(--deep); font-weight: 700;">Liste des Participants</h1>
    <p style="color: #8A9A86;">Retrouvez ici toutes les personnes inscrites à vos événements.</p>
</div>

<div class="section-card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom & Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Poids / Taille</th>
                <th>IMC</th>
                <th>Date d'inscription</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participants as $p): ?>
            <tr>
                <td style="font-weight: 700; color: var(--coral);"><?= $p['id'] ?></td>
                <td style="font-weight: 600; color: var(--deep);">
                    <?= htmlspecialchars($p['nom']) ?> <?= htmlspecialchars($p['prenom']) ?>
                </td>
                <td><a href="mailto:<?= htmlspecialchars($p['email']) ?>" style="color: var(--mint); text-decoration: none; font-weight: 500;"><?= htmlspecialchars($p['email']) ?></a></td>
                <td><?= htmlspecialchars($p['telephone'] ?? '-') ?></td>
                <td style="font-size: 0.9rem; color: #4A5B4A;">
                    <?php
                    $pk = (float)($p['poids'] ?? 0);
                    $tk = (float)($p['taille'] ?? 0);
                    echo $pk > 0 ? number_format($pk, 1) . ' kg' : '—';
                    echo ' / ';
                    echo $tk > 0 ? number_format($tk, 0) . ' cm' : '—';
                    ?>
                </td>
                <td style="font-weight: 700; color: var(--deep);">
                    <?= isset($p['imc_display']) && $p['imc_display'] !== null ? number_format((float)$p['imc_display'], 2, ',', ' ') : '—' ?>
                </td>
                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td style="text-align: right;">
                    <a href="admin.php?action=email_participant&id=<?= $p['id'] ?>" class="action-btn" title="Envoyer un Gmail" style="color: #D14836;">
                        <i class="bi bi-google"></i>
                    </a>
                    <a href="admin.php?action=show_participant&id=<?= $p['id'] ?>" class="action-btn" title="Afficher">
                        <i class="bi bi-eye-fill"></i>
                    </a>
                    <a href="admin.php?action=edit_participant&id=<?= $p['id'] ?>" class="action-btn btn-edit" title="Modifier">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="admin.php?action=delete_participant&id=<?= $p['id'] ?>" class="action-btn btn-delete" title="Supprimer" onclick="return confirm('Supprimer ce participant ?')">
                        <i class="bi bi-trash3-fill"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>

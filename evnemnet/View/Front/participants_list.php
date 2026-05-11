<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>Liste des Participants</h1>
    <p>Découvrez tous les participants enregistrés</p>
</div>

<div class="section-card" style="margin-top: 40px;">
    <div class="section-title">
        <i class="bi bi-people-fill"></i>
        <span>Tous les participants</span>
    </div>
    
    <?php if (empty($participants)): ?>
        <p style="text-align: center; color: #999; padding: 40px 0;">Aucun participant enregistré pour le moment.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #F5F9F5; border-bottom: 2px solid #E0E8E0;">
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--deep);">Nom</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--deep);">Prénom</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--deep);">Email</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--deep);">Téléphone</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--deep);">Lieu</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--deep);">Objectif</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600; color: var(--deep);">Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr style="border-bottom: 1px solid #E0E8E0;">
                            <td style="padding: 12px;"><?= htmlspecialchars($participant['nom']) ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars($participant['prenom']) ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars($participant['email']) ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars($participant['telephone'] ?? '-') ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars($participant['lieu'] ?? '-') ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars(substr($participant['objectif'] ?? '', 0, 40) . (strlen($participant['objectif'] ?? '') > 40 ? '...' : '')) ?></td>
                            <td style="padding: 12px; text-align: center;">
                                <?php if ($participant['poids'] && $participant['taille']): ?>
                                    <span style="background-color: #E6F4EE; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                        <?= $participant['poids'] ?>kg - <?= $participant['taille'] ?>cm - IMC: <?= $participant['imc'] ?? '-' ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

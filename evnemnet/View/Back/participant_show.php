<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="color: var(--deep); font-weight: 700; margin-bottom: 6px;">
            <?= htmlspecialchars($participant['nom']) ?> <?= htmlspecialchars($participant['prenom']) ?>
        </h1>
        <p style="color: #8A9A86; margin: 0;">Détails du participant</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="admin.php?action=email_participant&id=<?= (int)$participant['id'] ?>" class="btn-primary" style="text-decoration: none; background: #D14836;">
            <i class="bi bi-google"></i> Gmail
        </a>
        <a href="admin.php?action=edit_participant&id=<?= (int)$participant['id'] ?>" class="btn-primary" style="text-decoration: none;">
            <i class="bi bi-pencil-square"></i> Modifier
        </a>
        <a href="admin.php?action=participants" class="edit-btn" style="background: #eee; text-decoration: none;">Retour</a>
    </div>
</div>

<div class="section-card">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">ID</div>
            <div style="font-weight: 700; color: var(--deep);"><?= (int)$participant['id'] ?></div>
        </div>
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Inscrit le</div>
            <div style="font-weight: 700; color: var(--deep);"><?= !empty($participant['created_at']) ? date('d/m/Y', strtotime($participant['created_at'])) : '-' ?></div>
        </div>
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Email</div>
            <div style="font-weight: 700; color: var(--deep);"><?= htmlspecialchars($participant['email']) ?></div>
        </div>
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Téléphone</div>
            <div style="font-weight: 700; color: var(--deep);"><?= htmlspecialchars($participant['telephone'] ?? '-') ?></div>
        </div>
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Poids</div>
            <div style="font-weight: 700; color: var(--deep);"><?= htmlspecialchars((string)($participant['poids'] ?? '-')) ?></div>
        </div>
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Taille</div>
            <div style="font-weight: 700; color: var(--deep);"><?= htmlspecialchars((string)($participant['taille'] ?? '-')) ?></div>
        </div>
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">IMC</div>
            <div style="font-weight: 700; color: var(--deep);">
                <?= isset($participant['imc_display']) && $participant['imc_display'] !== null ? number_format((float)$participant['imc_display'], 2, ',', ' ') : '—' ?>
            </div>
            <?php if (isset($participant['imc_display']) && $participant['imc_display'] !== null): ?>
                <small style="color: #8A9A86;">
                    <?= !empty($participant['imc_is_computed']) ? '(calculé : poids kg ÷ (taille en m)²)' : '(valeur en base)' ?>
                </small>
            <?php endif; ?>
        </div>
        <div class="section-card" style="margin-top: 0; padding: 14px;">
            <div style="color: #8A9A86; font-size: 0.85rem; font-weight: 600;">Objectif</div>
            <div style="font-weight: 700; color: var(--deep);"><?= htmlspecialchars($participant['objectif'] ?? '-') ?></div>
        </div>
    </div>

    <div style="margin-top: 18px;">
        <div style="color: #8A9A86; font-size: 0.9rem; font-weight: 700; margin-bottom: 8px;">Lieu</div>
        <div style="color: #4A5B4A; line-height: 1.6;"><?= nl2br(htmlspecialchars($participant['lieu'] ?? '')) ?></div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>

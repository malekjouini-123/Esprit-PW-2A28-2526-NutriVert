<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="margin-bottom: 40px;">
    <h1 style="color: var(--deep); font-weight: 700;">Détails de la Recommandation</h1>
    <p style="color: #8A9A86;">
        <a href="admin.php?action=recommendations" style="color: var(--mint); text-decoration: none;">
            ← Retour à la liste
        </a>
    </p>
</div>

<div class="section-card">
    <div class="section-title">
        <i class="bi bi-star-fill"></i>
        <span><?= htmlspecialchars($recommendation['titre']) ?></span>
    </div>

    <div class="profil-grid" style="margin-top: 20px;">
        <div style="flex: 1;">
            <div class="profil-infos">
                <div class="info-badge">
                    <strong>Catégorie préférée:</strong> <?= htmlspecialchars($recommendation['categorie_preferee'] ?? 'Non spécifiée') ?>
                </div>
                <div class="info-badge">
                    <strong>Budget maximum:</strong>
                    <?php if ($recommendation['budget_max']): ?>
                        <?= number_format((float)$recommendation['budget_max'], 2) ?> €
                    <?php else: ?>
                        Non spécifié
                    <?php endif; ?>
                </div>
                <div class="info-badge">
                    <strong>Localisation:</strong> <?= htmlspecialchars($recommendation['localisation'] ?? 'Non spécifiée') ?>
                </div>
                <div class="info-badge">
                    <strong>Créé le:</strong> <?= date('d/m/Y H:i', strtotime($recommendation['created_at'])) ?>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <h3 style="color: var(--deep); margin-bottom: 15px;">Description</h3>
                <p style="line-height: 1.6; color: #4A5B4A;">
                    <?= nl2br(htmlspecialchars($recommendation['description'])) ?>
                </p>
            </div>

            <?php if ($recommendation['ai_suggestion']): ?>
                <div style="margin-top: 30px;">
                    <h3 style="color: var(--deep); margin-bottom: 15px;">Suggestions IA</h3>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid var(--mint);">
                        <?php
                        $suggestions = json_decode($recommendation['ai_suggestion'], true);
                        if (is_array($suggestions)):
                            foreach ($suggestions as $suggestion):
                        ?>
                            <p style="margin: 5px 0; color: #4A5B4A;">• <?= htmlspecialchars($suggestion) ?></p>
                        <?php
                            endforeach;
                        else:
                            echo '<p>' . htmlspecialchars($recommendation['ai_suggestion']) . '</p>';
                        endif;
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($recommendation['evenements_suggeres']): ?>
                <div style="margin-top: 30px;">
                    <h3 style="color: var(--deep); margin-bottom: 15px;">Événements Suggérés</h3>
                    <div style="background: #f0f8ff; padding: 20px; border-radius: 10px; border-left: 4px solid var(--blue);">
                        <?php
                        $events = json_decode($recommendation['evenements_suggeres'], true);
                        if (is_array($events)):
                            foreach ($events as $event):
                        ?>
                            <div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 5px;">
                                <strong><?= htmlspecialchars($event['titre'] ?? 'Événement') ?></strong>
                                <?php if (isset($event['score'])): ?>
                                    <span style="float: right; color: var(--mint); font-weight: bold;">
                                        Score: <?= htmlspecialchars((string)$event['score']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php
                            endforeach;
                        else:
                            echo '<p>' . htmlspecialchars($recommendation['evenements_suggeres']) . '</p>';
                        endif;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center;">
        <a href="admin.php?action=delete_recommendation&id=<?= $recommendation['id'] ?>" class="btn-danger"
           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette recommandation ?')">
            <i class="bi bi-trash"></i> Supprimer cette recommandation
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>
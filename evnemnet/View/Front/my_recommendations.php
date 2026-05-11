<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>Mes Recommandations</h1>
    <p>Gérez vos recommandations personnalisées d'événements</p>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="section-card" style="background: #E6F4EE; border-color: var(--mint); padding: 20px; margin-bottom: 20px;">
        <p style="color: #2D3E2B; font-weight: 700; text-align: center; margin: 0;">
            ✨ Votre recommandation a été créée avec succès !
        </p>
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="section-card" style="background: #FFE6E6; border-color: var(--coral); padding: 20px; margin-bottom: 20px;">
        <p style="color: #8B3E3E; font-weight: 700; text-align: center; margin: 0;">
            ✓ Votre recommandation a été supprimée.
        </p>
    </div>
<?php endif; ?>

<div style="margin-bottom: 30px;">
    <a href="index.php?action=create_recommendation" class="btn-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: 600;">
        <i class="bi bi-plus-circle"></i> Créer une nouvelle recommandation
    </a>
</div>

<?php if (empty($recommendations)): ?>
    <div class="section-card" style="text-align: center; padding: 60px 20px;">
        <i class="bi bi-inbox" style="font-size: 3rem; color: #999; margin-bottom: 20px; display: block;"></i>
        <h3 style="color: #999; margin-bottom: 10px;">Aucune recommandation</h3>
        <p style="color: #bbb; margin-bottom: 20px;">Vous n'avez pas encore créé de recommandation personnalisée.</p>
        <a href="index.php?action=create_recommendation" class="btn-primary" style="display: inline-block; padding: 10px 25px; text-decoration: none;">
            Créer ma première recommandation
        </a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px;">
        <?php foreach ($recommendations as $rec): ?>
            <div class="section-card" style="margin-top: 0; display: flex; flex-direction: column;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                    <h3 style="color: var(--deep); margin: 0; flex: 1;">
                        <?= htmlspecialchars($rec['titre']) ?>
                    </h3>
                    <a href="index.php?action=delete_recommendation&id=<?= $rec['id'] ?>" onclick="return confirm('Êtes-vous sûr ?')" style="color: var(--coral); cursor: pointer; text-decoration: none; font-size: 1.2rem;">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>

                <p style="color: #4A5B4A; font-size: 0.9rem; line-height: 1.6; margin-bottom: 15px; flex-grow: 1;">
                    <?= htmlspecialchars(substr($rec['description'], 0, 150)) ?>...
                </p>

                <!-- Critères -->
                <div style="background: #F5F9F5; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem;">
                    <?php if ($rec['categorie_preferee']): ?>
                        <div style="margin-bottom: 8px;">
                            <strong>📚 Catégorie :</strong> <?= htmlspecialchars($rec['categorie_preferee']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($rec['budget_max']): ?>
                        <div style="margin-bottom: 8px;">
                            <strong>💰 Budget :</strong> Jusqu'à <?= number_format((float)$rec['budget_max'], 2) ?> €
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($rec['localisation']): ?>
                        <div>
                            <strong>📍 Localisation :</strong> <?= htmlspecialchars($rec['localisation']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Suggestions IA -->
                <?php if ($rec['ai_suggestion']): ?>
                    <div style="background: #E6F4EE; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; border-left: 3px solid var(--mint);">
                        <strong style="color: var(--deep);">🤖 Analyse IA :</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 20px; color: #666;">
                            <?php
                            $suggestions = json_decode($rec['ai_suggestion'], true);
                            if (is_array($suggestions)) {
                                foreach (array_slice($suggestions, 0, 2) as $sugg) {
                                    echo '<li style="margin-bottom: 4px;">' . $sugg . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Événements suggérés -->
                <?php if ($rec['evenements_suggeres']): ?>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: var(--deep); font-size: 0.9rem;">🎯 Événements suggérés :</strong>
                        <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 8px;">
                            <?php
                            $events = json_decode($rec['evenements_suggeres'], true);
                            if (is_array($events)) {
                                foreach (array_slice($events, 0, 3) as $event) {
                                    echo '
                                        <div style="background: #E6F4EE; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                                            <a href="index.php?action=event&id=' . $event['id'] . '" style="color: var(--deep); text-decoration: none; font-weight: 600;">
                                                ' . htmlspecialchars($event['titre']) . '
                                            </a>
                                            <span style="color: #999; margin-left: 8px;">Score: ' . $event['score'] . '</span>
                                        </div>
                                    ';
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="text-align: center; color: #999; font-size: 0.8rem; padding-top: 12px; border-top: 1px solid #E0E8E0;">
                    Créée le <?= date('d/m/Y à H:i', strtotime($rec['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

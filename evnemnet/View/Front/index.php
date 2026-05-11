<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>Découvrez nos Événements</h1>
    <p>Inscrivez-vous aux prochains événements et activités pour vivre des moments uniques.</p>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="section-card" style="background: #E6F4EE; border-color: var(--mint); padding: 20px; margin-bottom: 20px;">
        <p style="color: #2D3E2B; font-weight: 700; text-align: center; margin: 0;">
            ✨ <?php 
                switch($_GET['success']) {
                    case 'event': echo "L'événement a été créé avec succès !"; break;
                    case 'category': echo "La catégorie a été créée avec succès !"; break;
                    case 'participant': echo "Le participant a été enregistré avec succès !"; break;
                }
            ?>
        </p>
    </div>
<?php endif; ?>

<div class="section-card">
    <div class="section-title">
        <i class="bi bi-star-fill"></i>
        <span>Événements à la une</span>
    </div>
    
    <div class="event-commu-grid">
        <?php foreach ($evenements as $e): ?>
        <div class="event-card">
            <?php if ($e['image_url']): ?>
                <img src="<?= htmlspecialchars($e['image_url']) ?>" alt="<?= htmlspecialchars($e['titre']) ?>">
            <?php else: ?>
                <div style="background: var(--sage); height: 200px; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                </div>
            <?php endif; ?>
            <div class="event-card-content">
                <span class="event-badge"><?= htmlspecialchars($e['categorie_nom'] ?? 'Général') ?></span>
                <h3 style="margin-bottom: 10px; color: var(--deep);"><?= htmlspecialchars($e['titre']) ?></h3>
                <p style="color: #6B7C68; font-size: 0.95rem; margin-bottom: 20px; line-height: 1.5;">
                    <?= substr(htmlspecialchars($e['description']), 0, 100) ?>...
                </p>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: var(--coral); font-size: 1.2rem;"><?= number_format((float)$e['prix'], 2) ?> €</span>
                    <a href="index.php?action=event&id=<?= $e['id'] ?>" class="btn-primary" style="padding: 8px 20px; font-size: 0.9rem;">Détails</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Section Recommandations Intelligentes -->
<div class="section-card" style="margin-top: 40px;">
    <div class="section-title">
        <i class="bi bi-lightbulb-fill"></i>
        <span>🎯 Recommandations Personnalisées</span>
    </div>
    
    <?php if (!empty($recommandations)): ?>
        <p style="color: #666; margin-bottom: 25px;">Basé sur nos critères intelligents : proximité de date, prix, disponibilité et popularité</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach (array_slice($recommandations, 0, 3) as $index => $rec): ?>
                <div style="border: 2px solid var(--mint); border-radius: 12px; padding: 15px; position: relative;">
                    <div style="position: absolute; top: -12px; left: 15px; background: var(--coral); color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 700;">
                        #<?= $index + 1 ?> - Score: <?= $rec['recommendation_score'] ?>%
                    </div>
                    
                    <h4 style="color: var(--deep); margin: 15px 0 8px 0; font-size: 0.95rem;">
                        <?= htmlspecialchars(substr($rec['titre'], 0, 40)) ?>
                    </h4>
                    
                    <p style="color: #666; font-size: 0.85rem; margin-bottom: 10px;">
                        <i class="bi bi-calendar2-event"></i> <?= date('d/m/Y', strtotime($rec['date_evenement'])) ?>
                        <br>
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars(substr($rec['lieu'], 0, 25)) ?>
                    </p>
                    
                    <div style="background: #E6F4EE; padding: 8px; border-radius: 8px; margin-bottom: 10px; font-size: 0.8rem;">
                        <strong style="color: var(--deep);">Prix:</strong> <?= number_format((float)$rec['prix'], 2) ?> €
                        <?php if ($rec['places_left'] > 0): ?>
                            <br><span style="color: var(--mint); font-weight: 600;">✓ <?= $rec['places_left'] ?> place(s) libre</span>
                        <?php endif; ?>
                    </div>
                    
                    <a href="index.php?action=event&id=<?= $rec['id'] ?>" style="display: inline-block; font-size: 0.85rem; padding: 8px 16px; background: var(--mint); color: white; border-radius: 6px; text-decoration: none; font-weight: 600;">
                        Découvrir →
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 25px;">
            <a href="index.php?action=recommendations" class="btn-primary" style="display: inline-block; padding: 12px 40px; text-decoration: none; border-radius: 8px;">
                Voir tous les recommandations
            </a>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: #999; padding: 30px 0;">Aucune recommandation disponible pour le moment.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>🎯 Recommandations Intelligentes</h1>
    <p>Découvrez les événements sélectionnés spécialement pour vous basé sur plusieurs critères</p>
</div>

<div class="section-card" style="margin-top: 40px; margin-bottom: 30px; background: linear-gradient(135deg, #E6F4EE 0%, #F0F8F5 100%); border-left: 5px solid var(--mint);">
    <div class="section-title">
        <i class="bi bi-lightbulb-fill"></i>
        <span>Comment fonctionne cette recommandation ?</span>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="padding: 15px; background: white; border-radius: 10px; border-left: 3px solid var(--coral);">
            <h4 style="color: var(--deep); margin-bottom: 8px;">📅 Proximité de la date</h4>
            <p style="color: #666; font-size: 0.9rem; margin: 0;">Les événements proches (dans les 30 jours) sont prioritaires</p>
        </div>
        
        <div style="padding: 15px; background: white; border-radius: 10px; border-left: 3px solid var(--coral);">
            <h4 style="color: var(--deep); margin-bottom: 8px;">💰 Prix avantageux</h4>
            <p style="color: #666; font-size: 0.9rem; margin: 0;">Les événements gratuits ou pas chers sont recommandés</p>
        </div>
        
        <div style="padding: 15px; background: white; border-radius: 10px; border-left: 3px solid var(--coral);">
            <h4 style="color: var(--deep); margin-bottom: 8px;">👥 Disponibilité</h4>
            <p style="color: #666; font-size: 0.9rem; margin: 0;">Priorité aux événements avec places disponibles</p>
        </div>
        
        <div style="padding: 15px; background: white; border-radius: 10px; border-left: 3px solid var(--coral);">
            <h4 style="color: var(--deep); margin-bottom: 8px;">⭐ Popularité</h4>
            <p style="color: #666; font-size: 0.9rem; margin: 0;">Les événements populaires avec beaucoup d'inscrits</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px; margin-top: 30px;">
    <?php if (empty($recommandations)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px 0; color: #999;">
            <p>Aucune recommandation disponible pour le moment. Revenez bientôt !</p>
        </div>
    <?php else: ?>
        <?php foreach ($recommandations as $index => $evenement): ?>
            <div class="section-card" style="margin-top: 0; display: flex; flex-direction: column; position: relative;">
                <!-- Badge de recommandation -->
                <div style="position: absolute; top: -10px; left: 15px; background: var(--coral); color: white; padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 0.9rem; z-index: 10;">
                    #<?= $index + 1 ?> - Score: <?= $evenement['recommendation_score'] ?>/100
                </div>
                
                <?php if ($evenement['image_url']): ?>
                    <img src="<?= htmlspecialchars($evenement['image_url']) ?>" alt="<?= htmlspecialchars($evenement['titre']) ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 15px 15px 0 0; margin: -15px -15px 15px -15px;">
                <?php else: ?>
                    <div style="width: 100%; height: 200px; background-color: #F5F9F5; border-radius: 15px 15px 0 0; margin: -15px -15px 15px -15px; display: flex; align-items: center; justify-content: center; color: #999;">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                    </div>
                <?php endif; ?>

                <h3 style="color: var(--deep); margin-bottom: 10px; margin-top: 10px;">
                    <?= htmlspecialchars($evenement['titre']) ?>
                </h3>

                <!-- Raison de la recommandation -->
                <div style="background-color: #E6F4EE; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; color: var(--deep);">
                    <strong>✨ Pourquoi c'est recommandé :</strong>
                    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                        <?php 
                        $raisons = [];
                        if ((float)$evenement['prix'] <= 50) {
                            if ((float)$evenement['prix'] == 0) {
                                $raisons[] = "Gratuit !";
                            } else {
                                $raisons[] = "Prix attractif";
                            }
                        }
                        if ($evenement['places_left'] > 0) {
                            $raisons[] = $evenement['places_left'] . " place(s) disponible(s)";
                        }
                        if ((int)$evenement['inscriptions_count'] > 0) {
                            $raisons[] = (int)$evenement['inscriptions_count'] . " personne(s) inscrite(s)";
                        }
                        $date = new DateTime($evenement['date_evenement']);
                        $now = new DateTime();
                        $days = $now->diff($date)->days;
                        if ($days <= 30) {
                            $raisons[] = "Événement proche dans " . $days . " jour(s)";
                        }
                        foreach ($raisons as $raison) {
                            echo "<li>" . htmlspecialchars($raison) . "</li>";
                        }
                        ?>
                    </ul>
                </div>

                <p style="color: #4A5B4A; font-size: 0.9rem; margin-bottom: 15px;">
                    <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($evenement['lieu']) ?> 
                    <br>
                    <i class="bi bi-calendar-check-fill"></i> <?= date('d/m/Y H:i', strtotime($evenement['date_evenement'])) ?>
                </p>

                <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                    <span style="background-color: #E6F4EE; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; color: var(--deep);">
                        <i class="bi bi-tag"></i> <?= htmlspecialchars($evenement['categorie_nom'] ?? 'N/A') ?>
                    </span>
                    <span style="background-color: #E6F4EE; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; color: var(--deep);">
                        <i class="bi bi-euro"></i> <?= number_format((float)$evenement['prix'], 2) ?> €
                    </span>
                    <span style="background-color: #E6F4EE; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; color: var(--deep);">
                        <i class="bi bi-people"></i> 
                        <?php if ($evenement['places_left'] > 0): ?>
                            <span style="color: var(--mint); font-weight: 600;">Disponible</span>
                        <?php else: ?>
                            <span style="color: var(--coral); font-weight: 600;">Complet</span>
                        <?php endif; ?>
                    </span>
                </div>

                <p style="color: #666; font-size: 0.9rem; line-height: 1.5; margin-bottom: 15px; flex-grow: 1;">
                    <strong style="color: var(--deep);">Description :</strong><br>
                    <?= htmlspecialchars(substr($evenement['description'], 0, 150)) ?>...
                </p>

                <a href="index.php?action=event&id=<?= $evenement['id'] ?>" class="btn-primary" style="display: block; text-align: center; padding: 12px 20px; text-decoration: none; border-radius: 8px; margin-top: auto; font-weight: 600;">
                    Voir les détails et s'inscrire →
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

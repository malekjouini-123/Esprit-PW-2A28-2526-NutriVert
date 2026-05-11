<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1><?= htmlspecialchars($evenement['titre']) ?></h1>
    <p><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($evenement['lieu']) ?> — <i class="bi bi-calendar-check-fill"></i> <?= date('d/m/Y H:i', strtotime($evenement['date_evenement'])) ?></p>
</div>

<div class="profil-grid" style="margin-top: 40px;">
    <div style="flex: 2;">
        <div class="section-card" style="margin-top: 0;">
            <?php if ($evenement['image_url']): ?>
                <img src="<?= htmlspecialchars($evenement['image_url']) ?>" alt="<?= htmlspecialchars($evenement['titre']) ?>" style="width: 100%; border-radius: 20px; margin-bottom: 25px; box-shadow: var(--shadow);">
            <?php endif; ?>
            
            <div class="section-title">
                <i class="bi bi-info-circle-fill"></i>
                <span>À propos de l'événement</span>
            </div>
            
            <p style="line-height: 1.8; color: #4A5B4A; font-size: 1.1rem; margin-bottom: 30px;">
                <?= nl2br(htmlspecialchars($evenement['description'])) ?>
            </p>
            
            <div class="profil-infos">
                <div class="info-badge">Prix: <?= number_format((float)$evenement['prix'], 2) ?> €</div>
                <div class="info-badge">Capacité: <?= (int)($evenement['capacite'] ?? 0) ?> places</div>
                <div class="info-badge">Catégorie: <?= htmlspecialchars($evenement['categorie_nom'] ?? 'N/A') ?></div>
            </div>
        </div>
    </div>
    
    <div style="flex: 1;">
        <div class="section-card ia-container" style="margin-top: 0; position: sticky; top: 20px;">
            <div class="section-title">
                <i class="bi bi-pencil-square"></i>
                <span>S'inscrire</span>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="recette-card" style="border-left-color: var(--mint); background: #E6F4EE; margin-bottom: 20px;">
                    <p style="color: #2D3E2B; font-weight: 600;">✨ Félicitations ! Votre inscription a été enregistrée.</p>
                    <?php if (isset($_GET['mail_sent'])): ?>
                        <p style="margin-top: 8px; color: #2D3E2B;">
                            <?= ((int)($_GET['mail_sent'] ?? 0) === 1)
                                ? 'Un email de confirmation vous a été envoyé.'
                                : 'L’email n’a pas pu être envoyé.'
                            ?>
                        </p>
                        <?php if ((int)($_GET['mail_sent'] ?? 0) !== 1 && !empty($_SESSION['flash_mail_error'])): ?>
                            <p style="margin-top: 6px; font-size: 0.9rem; color: #8A4A3B;">
                                <?= htmlspecialchars((string)$_SESSION['flash_mail_error']) ?>
                            </p>
                            <?php unset($_SESSION['flash_mail_error']); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form action="index.php?action=register" method="POST" class="ingredient-group">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="evenement_id" value="<?= $evenement['id'] ?>">
                
                <input type="text" name="nom" placeholder="Votre nom" required>
                <input type="text" name="prenom" placeholder="Votre prénom" required>
                <input type="email" name="email" placeholder="votre@email.com" required>
                <input type="text" name="telephone" placeholder="Téléphone (optionnel)">
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px; padding: 15px;">
                    Confirmer mon inscription
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

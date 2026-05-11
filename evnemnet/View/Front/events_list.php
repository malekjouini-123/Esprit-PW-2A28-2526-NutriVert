<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>Tous les Événements</h1>
    <p>Découvrez nos événements à venir</p>
</div>

<!-- Barre de recherche et filtres -->
<div class="section-card" style="margin-top: 40px; margin-bottom: 30px;">
    <div class="section-title">
        <i class="bi bi-search"></i>
        <span>Rechercher et trier</span>
    </div>

    <form method="GET" action="index.php" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto auto; gap: 15px; align-items: flex-end;">
        <input type="hidden" name="action" value="events">
        
        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-weight: 600; color: var(--deep); font-size: 0.9rem;">Rechercher par titre</label>
            <input type="text" name="search_title" placeholder="Marathon, Conférence..." value="<?= htmlspecialchars($_GET['search_title'] ?? '') ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
        </div>

        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-weight: 600; color: var(--deep); font-size: 0.9rem;">Rechercher par date</label>
            <input type="date" name="search_date" value="<?= htmlspecialchars($_GET['search_date'] ?? '') ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
        </div>

        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-weight: 600; color: var(--deep); font-size: 0.9rem;">Trier par prix</label>
            <select name="sort_price" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                <option value="">Aucun tri</option>
                <option value="asc" <?= ($_GET['sort_price'] ?? '') === 'asc' ? 'selected' : '' ?>>Croissant</option>
                <option value="desc" <?= ($_GET['sort_price'] ?? '') === 'desc' ? 'selected' : '' ?>>Décroissant</option>
            </select>
        </div>

        <button type="submit" class="btn-primary" style="padding: 10px 25px; height: fit-content;">
            <i class="bi bi-funnel"></i> Filtrer
        </button>

        <a href="index.php?action=export_pdf" class="btn-primary" style="padding: 10px 25px; height: fit-content; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
            <i class="bi bi-file-pdf"></i> Export PDF
        </a>
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px; margin-top: 0;">
    <?php if (empty($evenements)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px 0; color: #999;">
            <p>Aucun événement ne correspond à vos critères de recherche.</p>
        </div>
    <?php else: ?>
        <?php foreach ($evenements as $evenement): ?>
            <div class="section-card" style="margin-top: 0; display: flex; flex-direction: column;">
                <?php if ($evenement['image_url']): ?>
                    <img src="<?= htmlspecialchars($evenement['image_url']) ?>" alt="<?= htmlspecialchars($evenement['titre']) ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 15px 15px 0 0; margin: -15px -15px 15px -15px;">
                <?php else: ?>
                    <div style="width: 100%; height: 200px; background-color: #F5F9F5; border-radius: 15px 15px 0 0; margin: -15px -15px 15px -15px; display: flex; align-items: center; justify-content: center; color: #999;">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                    </div>
                <?php endif; ?>

                <h3 style="color: var(--deep); margin-bottom: 10px;">
                    <?= htmlspecialchars($evenement['titre']) ?>
                </h3>

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
                        <i class="bi bi-people"></i> <?= (int)($evenement['capacite'] ?? 0) ?> places
                    </span>
                </div>

                <p style="color: #666; font-size: 0.9rem; line-height: 1.5; margin-bottom: 15px; flex-grow: 1;">
                    <?= htmlspecialchars(substr($evenement['description'], 0, 100)) ?>...
                </p>

                <a href="index.php?action=event&id=<?= $evenement['id'] ?>" class="btn-primary" style="display: block; text-align: center; padding: 10px 20px; text-decoration: none; border-radius: 8px; margin-top: auto;">
                    Voir les détails
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

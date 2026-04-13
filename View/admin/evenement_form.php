<?php
declare(strict_types=1);
/**
 * Vue admin pour le formulaire d'événement.
 * @var Evenement $evenement
 */
?>
<style>
    .adm-section {
        background: #fff;
        border: 1px solid rgba(45, 79, 30, 0.1);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 2px 12px rgba(45, 79, 30, 0.04);
        max-width: 800px;
    }
    .adm-section h2 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d4f1e;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(45, 79, 30, 0.08);
    }
    .adm-form-grid {
        display: grid;
        gap: 1rem;
    }
    .adm-form-grid label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #2d4f1e;
        display: block;
        margin-bottom: 0.4rem;
    }
    .adm-form-grid input[type="text"],
    .adm-form-grid input[type="datetime-local"],
    .adm-form-grid textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid rgba(45, 79, 30, 0.2);
        border-radius: 0.5rem;
        font-family: inherit;
        font-size: 0.95rem;
        background: #fcfdf7;
        color: #2d4f1e;
    }
    .adm-form-grid textarea { min-height: 120px; resize: vertical; }
    .adm-actions-row { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem; }
    .btn-adm {
        padding: 0.65rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        border: none;
        font-family: inherit;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-adm-primary { background: #3d6b2e; color: #fff; }
    .btn-adm-muted { background: #eef3e8; color: #2d4f1e; border: 1px solid rgba(45, 79, 30, 0.15); }
</style>

<h2><i class="fas fa-calendar-alt"></i> <?= $evenement->id > 0 ? 'Modifier' : 'Ajouter' ?> un événement</h2>

<section class="adm-section">
    <form action="admin_evenements.php?action=save" method="post" class="adm-form-grid">
        <input type="hidden" name="id" value="<?= (int)$evenement->id ?>">
        
        <div>
            <label for="titre">Titre de l'événement</label>
            <input type="text" id="titre" name="titre" required maxlength="255" value="<?= e($evenement->titre) ?>" placeholder="Ex. Atelier cuisine saine">
        </div>
        
        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description" required placeholder="Détails de l'événement..."><?= e($evenement->description) ?></textarea>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label for="date_evenement">Date et heure</label>
                <input type="datetime-local" id="date_evenement" name="date_evenement" required value="<?= $evenement->date_evenement ? date('Y-m-d\TH:i', strtotime($evenement->date_evenement)) : '' ?>">
            </div>
            <div>
                <label for="lieu">Lieu / Ville</label>
                <input type="text" id="lieu" name="lieu" required maxlength="255" value="<?= e($evenement->lieu) ?>" placeholder="Ex. tunis  , En ligne...">
            </div>
        </div>
        
        <div>
            <label for="image_url">URL de l'image (optionnel)</label>
            <input type="text" id="image_url" name="image_url" maxlength="255" value="<?= e($evenement->image_url) ?>" placeholder="https://example.com/image.jpg">
        </div>

        <div class="adm-actions-row">
            <button type="submit" class="btn-adm btn-adm-primary">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <a href="admin_evenements.php" class="btn-adm btn-adm-muted">Annuler</a>
        </div>
    </form>
</section>

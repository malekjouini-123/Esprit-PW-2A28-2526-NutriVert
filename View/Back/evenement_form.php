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

    /* JS Validation Styles */
    .form-error { color: #d9534f; font-size: 0.8rem; font-weight: 600; margin-top: 0.25rem; display: none; }
    .adm-form-grid div.has-error input, 
    .adm-form-grid div.has-error textarea { border-color: #d9534f; background: #fff5f5; }
    .adm-form-grid div.has-error .form-error { display: block; }
</style>

<h2><i class="fas fa-calendar-alt"></i> <?= $evenement->id > 0 ? 'Modifier' : 'Ajouter' ?> un événement</h2>

<section class="adm-section">
    <form action="admin_evenements.php?action=save" method="post" class="adm-form-grid" id="eventForm">
        <input type="hidden" name="id" value="<?= (int)$evenement->id ?>">
        
        <div>
            <label for="titre">Titre de l'événement</label>
            <input type="text" id="titre" name="titre" value="<?= e($evenement->titre) ?>" placeholder="Ex. Atelier cuisine saine">
            <span class="form-error">Le titre doit contenir au moins 5 caractères.</span>
        </div>
        
        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Détails de l'événement..."><?= e($evenement->description) ?></textarea>
            <span class="form-error">La description doit contenir au moins 20 caractères.</span>
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label for="date_evenement">Date et heure</label>
                <input type="datetime-local" id="date_evenement" name="date_evenement" value="<?= $evenement->date_evenement ? date('Y-m-d\TH:i', strtotime($evenement->date_evenement)) : '' ?>">
                <span class="form-error">La date doit être dans le futur.</span>
            </div>
            <div>
                <label for="lieu">Lieu / Ville</label>
                <input type="text" id="lieu" name="lieu" value="<?= e($evenement->lieu) ?>" placeholder="Ex. tunis  , En ligne...">
                <span class="form-error">Le lieu est requis.</span>
            </div>
        </div>
        
        <div>
            <label for="image_url">URL de l'image (optionnel)</label>
            <input type="text" id="image_url" name="image_url" value="<?= e($evenement->image_url) ?>" placeholder="https://example.com/image.jpg">
            <span class="form-error">L'URL doit commencer par http:// ou https://.</span>
        </div>

        <div class="adm-actions-row">
            <button type="submit" class="btn-adm btn-adm-primary">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <a href="admin_evenements.php" class="btn-adm btn-adm-muted">Annuler</a>
        </div>
    </form>
</section>

<script>
document.getElementById('eventForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Reset errors
    this.querySelectorAll('div').forEach(div => div.classList.remove('has-error'));

    // Titre
    const titre = document.getElementById('titre').value.trim();
    if (titre.length < 5) {
        document.getElementById('titre').parentElement.classList.add('has-error');
        isValid = false;
    }

    // Description
    const desc = document.getElementById('description').value.trim();
    if (desc.length < 20) {
        document.getElementById('description').parentElement.classList.add('has-error');
        isValid = false;
    }

    // Date (must be future)
    const dateInput = document.getElementById('date_evenement').value;
    if (dateInput) {
        const selectedDate = new Date(dateInput);
        const now = new Date();
        if (selectedDate <= now) {
            document.getElementById('date_evenement').parentElement.classList.add('has-error');
            isValid = false;
        }
    } else {
        document.getElementById('date_evenement').parentElement.classList.add('has-error');
        isValid = false;
    }

    // Lieu
    if (document.getElementById('lieu').value.trim() === '') {
        document.getElementById('lieu').parentElement.classList.add('has-error');
        isValid = false;
    }

    // Image URL
    const imageUrl = document.getElementById('image_url').value.trim();
    if (imageUrl !== '' && !imageUrl.startsWith('http://') && !imageUrl.startsWith('https://')) {
        document.getElementById('image_url').parentElement.classList.add('has-error');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
});
</script>

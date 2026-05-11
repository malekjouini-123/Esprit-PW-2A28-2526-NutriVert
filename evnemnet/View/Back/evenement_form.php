<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>
<?php $isEdit = isset($evenement); ?>

<div style="margin-bottom: 40px;">
    <h1 style="color: var(--deep); font-weight: 700;">
        <?= $isEdit ? 'Modifier' : 'Ajouter' ?> un Événement
    </h1>
    <p style="color: #8A9A86;">Remplissez les informations ci-dessous pour l'événement.</p>
</div>

<div class="section-card">
    <form action="admin.php?action=<?= $isEdit ? 'edit_event&id='.$evenement['id'] : 'add_event' ?>" method="POST" class="ingredient-group" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Titre de l'événement</label>
                <input type="text" name="titre" value="<?= $isEdit ? htmlspecialchars($evenement['titre']) : '' ?>" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Catégorie</label>
                <select name="categorie_id" required style="padding: 15px; border-radius: 20px; border: 2px solid #FFD5C2; font-family: 'Quicksand';">
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($isEdit && $evenement['categorie_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Description</label>
            <textarea name="description" rows="5"><?= $isEdit ? htmlspecialchars($evenement['description']) : '' ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; width: 100%; margin-top: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Date et Heure</label>
                <input type="datetime-local" name="date_evenement" value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($evenement['date_evenement'])) : '' ?>" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Lieu</label>
                <input type="text" name="lieu" value="<?= $isEdit ? htmlspecialchars($evenement['lieu']) : '' ?>" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Prix (€)</label>
                <input type="number" step="0.01" name="prix" value="<?= $isEdit ? $evenement['prix'] : '0.00' ?>">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Capacité</label>
                <input type="number" name="capacite" value="<?= $isEdit ? $evenement['capacite'] : '0' ?>">
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">URL de l'image</label>
            <input type="url" name="image_url" value="<?= $isEdit ? htmlspecialchars($evenement['image_url'] ?? '') : '' ?>" placeholder="https://example.com/image.jpg">
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Ou choisir une image (JPG/PNG/WebP/GIF)</label>
            <input type="file" name="image_file" accept="image/*" style="padding: 12px; border-radius: 20px; border: 2px solid #FFD5C2; background: white;">
        </div>

        <div style="display: flex; align-items: center; gap: 10px; width: 100%; margin-top: 20px;">
            <?php $pub = $isEdit ? (int)($evenement['is_published'] ?? 1) : 1; ?>
            <input type="checkbox" id="is_published" name="is_published" value="1" <?= $pub === 1 ? 'checked' : '' ?>>
            <label for="is_published" style="font-weight: 700; color: var(--deep);">Publié</label>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px; width: 100%; margin-top: 30px;">
            <a href="admin.php?action=events" class="edit-btn" style="background: #eee;">Annuler</a>
            <button type="submit" class="btn-primary">
                <?= $isEdit ? 'Mettre à jour' : 'Enregistrer l\'événement' ?>
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>

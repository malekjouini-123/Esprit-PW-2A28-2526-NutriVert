<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>
<?php $isEdit = isset($category); ?>

<div style="margin-bottom: 40px;">
    <h1 style="color: var(--deep); font-weight: 700;"><?= $isEdit ? 'Modifier' : 'Ajouter' ?> une Catégorie</h1>
    <p style="color: #8A9A86;"><?= $isEdit ? 'Mettez à jour les informations de la catégorie.' : 'Créez une nouvelle catégorie pour organiser vos événements.' ?></p>
</div>

<div class="section-card">
    <form action="admin.php?action=<?= $isEdit ? 'edit_category&id='.$category['id'] : 'add_category' ?>" method="POST" class="ingredient-group" enctype="multipart/form-data">
        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
            <label style="font-weight: 600; color: var(--deep);">Nom de la catégorie</label>
            <input type="text" name="nom" placeholder="Ex: Conférence, Atelier, Sport..." value="<?= $isEdit ? htmlspecialchars($category['nom']) : '' ?>" required>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Description</label>
            <textarea name="description" rows="4" placeholder="Description optionnelle..."><?= $isEdit ? htmlspecialchars($category['description'] ?? '') : '' ?></textarea>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Image (optionnel)</label>
            <input type="file" name="image_file" accept="image/*" style="padding: 12px; border-radius: 20px; border: 2px solid #FFD5C2; background: white;">
            <?php if ($isEdit && !empty($category['image_url'])): ?>
                <small style="color: #8A9A86;">Image actuelle : <?= htmlspecialchars($category['image_url']) ?></small>
            <?php endif; ?>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; width: 100%; margin-top: 20px;">
            <?php $pub = $isEdit ? (int)($category['is_published'] ?? 1) : 1; ?>
            <input type="checkbox" id="is_published" name="is_published" value="1" <?= $pub === 1 ? 'checked' : '' ?>>
            <label for="is_published" style="font-weight: 700; color: var(--deep);">Publié</label>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px; width: 100%; margin-top: 30px;">
            <a href="admin.php?action=categories" class="edit-btn" style="background: #eee;">Annuler</a>
            <button type="submit" class="btn-primary"><?= $isEdit ? 'Mettre à jour' : 'Enregistrer la catégorie' ?></button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>

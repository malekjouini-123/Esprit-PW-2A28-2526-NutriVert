<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>Créer un Nouvel Événement</h1>
    <p>Organisez un moment inoubliable pour la communauté.</p>
</div>

<div class="section-card">
    <div class="section-title">
        <i class="bi bi-calendar-plus-fill"></i>
        <span>Informations de l'événement</span>
    </div>
    
    <form action="index.php?action=add_event" method="POST" class="ingredient-group" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Titre</label>
                <input type="text" name="titre" placeholder="Ex: Marathon de Printemps" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Catégorie</label>
                <select name="categorie_id" id="categorie_id" onchange="updateImageGallery()" required style="padding: 15px; border-radius: 20px; border: 2px solid #FFD5C2; font-family: 'Quicksand';">
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-nom="<?= htmlspecialchars($cat['nom']) ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Description</label>
            <textarea name="description" rows="5" placeholder="Décrivez votre événement en quelques lignes..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; width: 100%; margin-top: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Date et Heure</label>
                <input type="datetime-local" name="date_evenement" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Lieu</label>
                <input type="text" name="lieu" placeholder="Ex: Paris" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Prix (€)</label>
                <input type="number" step="0.01" name="prix" value="0.00">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Capacité</label>
                <input type="number" name="capacite" value="0">
            </div>
        </div>

        <!-- Section Image -->
        <div style="margin-top: 30px; padding: 20px; background: #F5F9F5; border-radius: 12px;">
            <h3 style="color: var(--deep); margin-bottom: 20px;">📸 Choisir une Image</h3>

            <!-- Galerie d'images réelles -->
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; color: var(--deep); display: block; margin-bottom: 15px;">
                    <i class="bi bi-images"></i> Sélectionnez une image réelle
                </label>
                <div id="imageGallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-bottom: 20px;">
                    <p style="color: #999; grid-column: 1 / -1;">Veuillez d'abord sélectionner une catégorie</p>
                </div>
            </div>

            <!-- Séparateur -->
            <div style="text-align: center; color: #999; margin: 20px 0; font-weight: 600;">OU</div>

            <!-- Upload fichier -->
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; color: var(--deep); display: block; margin-bottom: 10px;">
                    <i class="bi bi-cloud-upload"></i> Télécharger votre propre image
                </label>
                <input type="file" name="image_file" accept="image/*" id="imageFile" onchange="previewImage()" style="padding: 12px; border-radius: 20px; border: 2px solid #FFD5C2; background: white; width: 100%;">
                <div id="imagePreview" style="margin-top: 15px;"></div>
            </div>

            <!-- Saisie URL -->
            <div>
                <label style="font-weight: 600; color: var(--deep); display: block; margin-bottom: 10px;">
                    <i class="bi bi-link-45deg"></i> Ou saisir une URL d'image
                </label>
                <input type="url" name="image_url" id="imageUrl" placeholder="https://images.unsplash.com/photo-..." style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 100%;">
            </div>
        </div>

        <input type="hidden" id="selected_image_url" name="selected_image_url" value="">

        <div style="display: flex; align-items: center; gap: 10px; width: 100%; margin-top: 20px;">
            <input type="checkbox" id="is_published" name="is_published" value="1" checked>
            <label for="is_published" style="font-weight: 700; color: var(--deep);">Publier maintenant</label>
        </div>

        <div style="display: flex; justify-content: center; width: 100%; margin-top: 40px;">
            <button type="submit" class="btn-primary" style="padding: 15px 60px; font-size: 1.1rem;">
                Publier l'événement
            </button>
        </div>
    </form>
</div>

<script>
<?php
// Charger la galerie d'images
$images_gallery = include __DIR__ . '/../../assets/images_gallery.php';
?>

const imagesGallery = <?php echo json_encode($images_gallery); ?>;

function updateImageGallery() {
    const categorySelect = document.getElementById('categorie_id');
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const categoryName = selectedOption.dataset.nom || '';
    const gallery = document.getElementById('imageGallery');
    
    if (!categoryName) {
        gallery.innerHTML = '<p style="color: #999; grid-column: 1 / -1;">Veuillez d\'abord sélectionner une catégorie</p>';
        return;
    }

    const images = imagesGallery[categoryName] || imagesGallery['Autres'] || [];
    
    if (images.length === 0) {
        gallery.innerHTML = '<p style="color: #999; grid-column: 1 / -1;">Aucune image disponible</p>';
        return;
    }

    gallery.innerHTML = images.map((img, index) => `
        <div style="cursor: pointer; border-radius: 8px; overflow: hidden; border: 3px solid transparent; transition: all 0.3s;" 
             onclick="selectImage('${img.url}', this)" 
             title="${img.titre}">
            <img src="${img.url}" alt="${img.titre}" style="width: 100%; height: 100px; object-fit: cover; display: block;">
            <div style="padding: 4px; text-align: center; font-size: 0.75rem; background: #f5f5f5;">
                ${img.titre}
            </div>
        </div>
    `).join('');
}

function selectImage(url, element) {
    // Retirer la sélection précédente
    document.querySelectorAll('#imageGallery > div').forEach(el => {
        el.style.borderColor = 'transparent';
        el.style.backgroundColor = 'transparent';
    });
    
    // Ajouter la sélection au nouvel élément
    element.style.borderColor = 'var(--mint)';
    element.style.backgroundColor = '#E6F4EE';
    
    // Stocker l'URL sélectionnée
    document.getElementById('selected_image_url').value = url;
    
    // Afficher l'aperçu
    document.getElementById('imagePreview').innerHTML = `
        <div style="border: 2px solid var(--mint); border-radius: 8px; overflow: hidden;">
            <img src="${url}" alt="Aperçu" style="width: 100%; height: 200px; object-fit: cover;">
            <p style="text-align: center; padding: 10px; margin: 0; background: #E6F4EE; color: var(--deep); font-weight: 600;">Image sélectionnée ✓</p>
        </div>
    `;
    
    // Vider les autres champs
    document.getElementById('imageFile').value = '';
    document.getElementById('imageUrl').value = '';
}

function previewImage() {
    const fileInput = document.getElementById('imageFile');
    const preview = document.getElementById('imagePreview');
    const file = fileInput.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div style="border: 2px solid var(--mint); border-radius: 8px; overflow: hidden;">
                    <img src="${e.target.result}" alt="Aperçu" style="width: 100%; height: 200px; object-fit: cover;">
                    <p style="text-align: center; padding: 10px; margin: 0; background: #E6F4EE; color: var(--deep); font-weight: 600;">Image téléchargée ✓</p>
                </div>
            `;
            // Vider les autres sélections
            document.getElementById('selected_image_url').value = '';
            document.getElementById('imageUrl').value = '';
            document.querySelectorAll('#imageGallery > div').forEach(el => {
                el.style.borderColor = 'transparent';
                el.style.backgroundColor = 'transparent';
            });
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

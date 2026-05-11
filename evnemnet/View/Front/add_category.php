<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="hero">
    <h1>Créer une Nouvelle Catégorie</h1>
    <p>Organisez vos événements par thématiques.</p>
</div>

<div class="section-card" style="max-width: 800px; margin-left: auto; margin-right: auto;">
    <div class="section-title">
        <i class="bi bi-tag-fill"></i>
        <span>Détails de la catégorie</span>
    </div>
    
    <form action="index.php?action=add_category" method="POST" class="ingredient-group" enctype="multipart/form-data">
        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
            <label style="font-weight: 600; color: var(--deep);">Nom de la catégorie</label>
            <input type="text" name="nom" id="catName" placeholder="Ex: Sport, Musique, Technologie..." onchange="updateCategoryImages()" required>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Description</label>
            <textarea name="description" rows="4" placeholder="À quoi sert cette catégorie ?"></textarea>
        </div>

        <!-- Section Image -->
        <div style="margin-top: 30px; padding: 20px; background: #F5F9F5; border-radius: 12px;">
            <h3 style="color: var(--deep); margin-bottom: 20px;">📸 Choisir une Image</h3>

            <!-- Galerie d'images réelles -->
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; color: var(--deep); display: block; margin-bottom: 15px;">
                    <i class="bi bi-images"></i> Sélectionnez une image réelle
                </label>
                <div id="categoryImageGallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-bottom: 20px;">
                    <p style="color: #999; grid-column: 1 / -1;">Tapez le nom de la catégorie pour voir les images correspondantes</p>
                </div>
            </div>

            <!-- Séparateur -->
            <div style="text-align: center; color: #999; margin: 20px 0; font-weight: 600;">OU</div>

            <!-- Upload fichier -->
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; color: var(--deep); display: block; margin-bottom: 10px;">
                    <i class="bi bi-cloud-upload"></i> Télécharger votre propre image
                </label>
                <input type="file" name="image_file" accept="image/*" id="categoryImageFile" onchange="previewCategoryImage()" style="padding: 12px; border-radius: 20px; border: 2px solid #FFD5C2; background: white; width: 100%;">
                <div id="categoryImagePreview" style="margin-top: 15px;"></div>
            </div>
        </div>

        <input type="hidden" id="selected_category_image" name="selected_category_image" value="">

        <div style="display: flex; align-items: center; gap: 10px; width: 100%; margin-top: 20px;">
            <input type="checkbox" id="is_published" name="is_published" value="1" checked>
            <label for="is_published" style="font-weight: 700; color: var(--deep);">Publier maintenant</label>
        </div>

        <div style="display: flex; justify-content: center; width: 100%; margin-top: 40px;">
            <button type="submit" class="btn-primary" style="padding: 15px 60px; font-size: 1.1rem;">
                Créer la catégorie
            </button>
        </div>
    </form>
</div>

<script>
<?php
// Charger la galerie d'images
$images_gallery = include __DIR__ . '/../../assets/images_gallery.php';
?>

const categoryImagesGallery = <?php echo json_encode($images_gallery); ?>;

function updateCategoryImages() {
    const categoryName = document.getElementById('catName').value;
    const gallery = document.getElementById('categoryImageGallery');
    
    if (!categoryName) {
        gallery.innerHTML = '<p style="color: #999; grid-column: 1 / -1;">Tapez le nom de la catégorie pour voir les images</p>';
        return;
    }

    // Chercher une correspondance dans la galerie
    let images = categoryImagesGallery[categoryName] || null;
    
    // Si pas de correspondance exacte, chercher une correspondance partielle
    if (!images) {
        for (let key in categoryImagesGallery) {
            if (key.toLowerCase().includes(categoryName.toLowerCase())) {
                images = categoryImagesGallery[key];
                break;
            }
        }
    }
    
    // Sinon utiliser la catégorie "Autres"
    if (!images) {
        images = categoryImagesGallery['Autres'] || [];
    }

    if (images.length === 0) {
        gallery.innerHTML = '<p style="color: #999; grid-column: 1 / -1;">Aucune image disponible pour cette catégorie</p>';
        return;
    }

    gallery.innerHTML = images.map((img, index) => `
        <div style="cursor: pointer; border-radius: 8px; overflow: hidden; border: 3px solid transparent; transition: all 0.3s;" 
             onclick="selectCategoryImage('${img.url}', this)" 
             title="${img.titre}">
            <img src="${img.url}" alt="${img.titre}" style="width: 100%; height: 100px; object-fit: cover; display: block;">
            <div style="padding: 4px; text-align: center; font-size: 0.75rem; background: #f5f5f5;">
                ${img.titre}
            </div>
        </div>
    `).join('');
}

function selectCategoryImage(url, element) {
    // Retirer la sélection précédente
    document.querySelectorAll('#categoryImageGallery > div').forEach(el => {
        el.style.borderColor = 'transparent';
        el.style.backgroundColor = 'transparent';
    });
    
    // Ajouter la sélection au nouvel élément
    element.style.borderColor = 'var(--mint)';
    element.style.backgroundColor = '#E6F4EE';
    
    // Stocker l'URL sélectionnée
    document.getElementById('selected_category_image').value = url;
    
    // Afficher l'aperçu
    document.getElementById('categoryImagePreview').innerHTML = `
        <div style="border: 2px solid var(--mint); border-radius: 8px; overflow: hidden;">
            <img src="${url}" alt="Aperçu" style="width: 100%; height: 200px; object-fit: cover;">
            <p style="text-align: center; padding: 10px; margin: 0; background: #E6F4EE; color: var(--deep); font-weight: 600;">Image sélectionnée ✓</p>
        </div>
    `;
    
    // Vider le champ de fichier
    document.getElementById('categoryImageFile').value = '';
}

function previewCategoryImage() {
    const fileInput = document.getElementById('categoryImageFile');
    const preview = document.getElementById('categoryImagePreview');
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
            document.getElementById('selected_category_image').value = '';
            document.querySelectorAll('#categoryImageGallery > div').forEach(el => {
                el.style.borderColor = 'transparent';
                el.style.backgroundColor = 'transparent';
            });
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

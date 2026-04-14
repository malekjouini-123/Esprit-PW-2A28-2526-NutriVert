<?php
declare(strict_types=1);
/**
 * Vue Catégories - Category Builder & Showcase avec Design Glassmorphism.
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Builder | NutriVert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(145deg, #f0f9ea 0%, #e2f3db 50%, #d4ecce 100%); 
            background-attachment: fixed; 
            color: #1a3a1a; 
            scroll-behavior: smooth; 
        }

        /* Glassmorphism */
        .glass-card { 
            background: rgba(255, 255, 250, 0.65); 
            backdrop-filter: blur(14px); 
            border-radius: 2rem; 
            border: 1px solid rgba(100, 180, 70, 0.35); 
            box-shadow: 0 15px 35px -12px rgba(0, 0, 0, 0.08); 
            transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1); 
            overflow: hidden;
        }

        /* Header */
        header { 
            background: rgba(255, 255, 245, 0.92); 
            backdrop-filter: blur(20px); 
            padding: 0.9rem 2.5rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap; 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            border-bottom: 1px solid rgba(90, 160, 60, 0.3); 
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02); 
        }

        .logo-area { 
            display: flex; 
            align-items: center; 
            gap: 1rem; 
            background: rgba(230, 255, 220, 0.7); 
            padding: 0.4rem 1.2rem 0.4rem 1rem; 
            border-radius: 3rem; 
            transition: all 0.3s ease; 
            border: 1px solid rgba(100, 180, 70, 0.5); 
            text-decoration: none;
        }
        .logo-text { font-size: 1.7rem; font-weight: 800; background: linear-gradient(125deg, #1f5e1a, #4cae4c); -webkit-background-clip: text; background-clip: text; color: transparent; }

        nav { display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap; }
        nav a { color: #2a5522; text-decoration: none; font-weight: 600; transition: 0.2s; font-size: 0.95rem; padding: 0.4rem 0.2rem; border-bottom: 2px solid transparent; }
        nav a:hover, nav a.active { color: #3c9e2a; border-bottom-color: #6fbf4c; }

        .btn-primary-green { background: linear-gradient(105deg, #4cae4c, #2f8a2b); border: none; padding: 0.6rem 1.4rem; border-radius: 2rem; font-weight: 700; color: white; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }

        /* Main Container */
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }

        .section-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 1.8rem; display: flex; align-items: center; gap: 0.7rem; background: linear-gradient(120deg, #1f631a, #58b83a); -webkit-background-clip: text; background-clip: text; color: transparent; border-left: 7px solid #7ac85a; padding-left: 1rem; }

        /* Builder Layout */
        .builder-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 3rem; margin-bottom: 5rem; align-items: start; }
        .form-box { background: rgba(255, 255, 250, 0.8); padding: 2.5rem; border-radius: 2.5rem; border: 1px solid rgba(100, 180, 70, 0.3); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 0.5rem; color: #2c4d24; font-size: 0.85rem; text-transform: uppercase; }
        .form-control { width: 100%; padding: 1rem; border-radius: 1rem; border: 1px solid rgba(100, 180, 70, 0.2); background: rgba(255, 255, 255, 0.9); outline: none; transition: all 0.3s; font-family: inherit; }
        .form-control:focus { border-color: #4cae4c; box-shadow: 0 0 0 4px rgba(76, 174, 76, 0.1); }

        /* Preview Card */
        .preview-area { text-align: center; }
        .preview-title { font-size: 0.75rem; color: #7f8c8d; letter-spacing: 4px; margin-bottom: 2rem; display: block; text-transform: uppercase; font-weight: 700; }
        
        .cat-card { position: relative; border-radius: 3.5rem; overflow: hidden; height: 500px; border: 1px solid rgba(100, 180, 70, 0.35); background: #fff; }
        .cat-card-img-container { height: 100%; position: relative; }
        .cat-card-img-container img { width: 100%; height: 100%; object-fit: cover; transition: opacity 0.5s ease-in-out; }
        
        .cat-card-overlay { position: absolute; bottom: 0; left: 0; width: 100%; padding: 3rem; background: linear-gradient(transparent, rgba(26, 58, 26, 0.95)); text-align: left; }
        .cat-card-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -120%); font-size: 5rem; color: #7ac85a; opacity: 0.8; }
        .cat-card-h { font-size: 2.2rem; font-weight: 800; margin-bottom: 0.5rem; color: #fff; }
        .cat-card-p { font-size: 1rem; color: rgba(255,255,255,0.8); line-height: 1.6; margin-bottom: 1.5rem; }
        .cat-card-workshop { background: #4cae4c; color: #fff; padding: 0.5rem 1.5rem; border-radius: 2rem; font-weight: 700; font-size: 0.9rem; display: inline-block; }

        /* CRUD Buttons */
        .crud-btns { display: flex; gap: 0.5rem; margin-top: 1.5rem; }
        .btn-edit { background: rgba(241, 196, 15, 0.9); color: #fff; border: none; padding: 0.7rem; border-radius: 1rem; cursor: pointer; flex: 1; font-weight: 700; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-delete { background: rgba(231, 76, 60, 0.9); color: #fff; border: none; padding: 0.7rem; border-radius: 1rem; cursor: pointer; flex: 1; font-weight: 700; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-edit:hover { background: #d4ac0d; transform: translateY(-2px); }
        .btn-delete:hover { background: #c0392b; transform: translateY(-2px); }

        /* Multi-Image Selector in Form */
        .multi-img-selector { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 0.5rem; }
        .cat-img-opt { height: 60px; border-radius: 10px; cursor: pointer; border: 3px solid transparent; overflow: hidden; transition: 0.2s; position: relative; }
        .cat-img-opt.selected { border-color: #4cae4c; transform: scale(0.95); }
        .cat-img-opt img { width: 100%; height: 100%; object-fit: cover; }
        .cat-img-opt .check-badge { position: absolute; top: 2px; right: 2px; background: #4cae4c; color: white; border-radius: 50%; width: 18px; height: 18px; display: none; align-items: center; justify-content: center; font-size: 10px; }
        .cat-img-opt.selected .check-badge { display: flex; }

        /* Category Grid */
        .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 3rem; }
        
        /* Image Gallery inside card */
        .img-dots { position: absolute; top: 2rem; right: 2rem; display: flex; gap: 8px; z-index: 5; }
        .img-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; }
        .img-dot.active { background: #fff; transform: scale(1.2); }

        .btn-generate { width: 100%; padding: 1.2rem; font-size: 1rem; margin-top: 1rem; display: flex; align-items: center; justify-content: center; gap: 10px; }

        /* Validation Styles */
        .error-msg { color: #e74c3c; font-size: 0.75rem; font-weight: 700; margin-top: 0.3rem; display: none; }
        .form-group.has-error .form-control { border-color: #e74c3c; background: #fff5f5; }
        .form-group.has-error .error-msg { display: block; }

        footer { text-align: center; padding: 2rem; background: rgba(30, 55, 25, 0.9); color: #e0f0cf; margin-top: 4rem; font-size: 0.9rem; }

        @media (max-width: 1000px) { .builder-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo-area">
            <div style="height: 40px; width: 40px; background: #4cae4c; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;"><i class="fas fa-leaf"></i></div>
            <span class="logo-text">NutriVert</span>
        </a>
        <nav>
            <a href="index.php?sub=events">Événements & Suivi</a>
            <a href="index.php?sub=categories" class="active">Catégories</a>
            <a href="index.php?sub=participants">Participants</a>
            <a href="index.php?sub=participants" class="btn-primary-green">S'inscrire</a>
        </nav>
    </header>

    <div class="container">
        <div class="builder-grid">
            <!-- Formulaire Builder -->
            <div class="form-box">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h2 class="section-title" style="border:none; padding:0; font-size:1.5rem; margin:0;"><i class="fas fa-edit"></i> <span id="formTitle">Détails de la Catégorie</span></h2>
                    <button type="button" onclick="resetCatForm()" style="background:#95a5a6; color:white; border:none; padding:0.5rem 1rem; border-radius:0.8rem; cursor:pointer; font-weight:700; font-size:0.8rem;">
                        <i class="fas fa-sync-alt"></i> RÉINITIALISER
                    </button>
                </div>
                <form id="catForm" action="index.php?action=save_category" method="post">
                    <input type="hidden" id="categoryId" name="id" value="0">
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="catName">Nom de la catégorie</label>
                            <input type="text" id="catName" name="nom" class="form-control" placeholder="Ex: Nutrition Sportive...">
                            <span class="error-msg">Le nom est requis (min 3 car.).</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="catDesc">Description</label>
                        <textarea id="catDesc" name="description" class="form-control" style="min-height: 120px;" placeholder="Décrivez l'univers de cette catégorie..."></textarea>
                        <span class="error-msg">La description doit faire au moins 10 car.</span>
                    </div>
                    <div class="form-group">
                        <label for="catWorkshop">Atelier de cuisine</label>
                        <input type="text" id="catWorkshop" name="atelier" class="form-control" placeholder="Ex: Atelier Recettes Énergie...">
                        <span class="error-msg">L'atelier est requis.</span>
                    </div>

                    <div class="form-group" id="imgGroup">
                        <label>Choisissez les images pour cette catégorie</label>
                        <div class="multi-img-selector" id="catImgSelector">
                            <!-- Image Options -->
                            <?php 
                            $imgOpts = [
                                "https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800",
                                "https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800",
                                "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800",
                                "https://images.unsplash.com/photo-1506084868730-3423e9339e05?auto=format&fit=crop&w=800",
                                "https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=800",
                                "https://images.unsplash.com/photo-1466637574441-749b8f19452f?auto=format&fit=crop&w=800",
                                "https://images.unsplash.com/photo-1494390248081-4e521a5940db?auto=format&fit=crop&w=800",
                                "https://images.unsplash.com/photo-1505576399279-565b52d4ac71?auto=format&fit=crop&w=800"
                            ];
                            foreach($imgOpts as $url):
                            ?>
                            <div class="cat-img-opt" data-img="<?= $url ?>">
                                <img src="<?= $url ?>" style="width:100%; height:100%; object-fit:cover;">
                                <div class="check-badge"><i class="fas fa-check"></i></div>
                                <input type="checkbox" name="images[]" value="<?= $url ?>" style="display:none;">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <span class="error-msg">Sélectionnez au moins une image.</span>
                    </div>
                    <button type="submit" class="btn-primary-green btn-generate" id="submitBtn">
                        <i class="fas fa-magic"></i> GÉNÉRER
                    </button>
                </form>
            </div>

            <!-- Aperçu en direct -->
            <div class="preview-area">
                <span class="preview-title">Aperçu en direct</span>
                <div class="cat-card glass-card" id="previewCard">
                    <div class="cat-card-icon"><i class="fas fa-leaf"></i></div>
                    <div class="cat-card-img-container" id="previewImgContainer">
                        <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800" alt="Preview">
                    </div>
                    <div class="cat-card-overlay">
                        <h3 class="cat-card-h" id="previewName">Nouvelle Catégorie</h3>
                        <p class="cat-card-p" id="previewDesc">Remplissez le formulaire pour voir le rendu de votre catégorie en temps réel.</p>
                        <span class="cat-card-workshop" id="previewWorkshop">Nom de l'atelier</span>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="section-title"><i class="fas fa-th-large"></i> Nos Catégories</h2>
        <div class="category-grid" id="categoryGrid">
            <!-- Les catégories seront injectées ici -->
        </div>
    </div>

    <footer>
        <p>🍃 NutriVert — Mangez bien, Vivez mieux | &copy; 2026</p>
    </footer>

    <!-- Modal Détails Catégorie -->
    <div id="catModal" class="modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); align-items: center; justify-content: center;">
        <div class="modal-content" style="background: white; padding: 2.5rem; border-radius: 2rem; width: 90%; max-width: 600px; position: relative;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #f0f9ea; padding-bottom: 1rem;">
                <h3 id="modalCatTitle" style="font-size: 1.8rem; color: #1f5e1a; font-weight: 800;">Détails Catégorie</h3>
                <span class="close-modal" onclick="closeCatModal()" style="font-size: 1.5rem; color: #95a5a6; cursor: pointer;"><i class="fas fa-times"></i></span>
            </div>
            <div id="modalCatDetails" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Détails injectés ici -->
            </div>
        </div>
    </div>

    <script>
        let categoriesData = <?php echo json_encode($categories); ?>;

        function renderCategories() {
            const grid = document.getElementById('categoryGrid');
            grid.innerHTML = '';
            
            categoriesData.forEach((cat, index) => {
                const card = document.createElement('div');
                card.className = 'cat-card glass-card';
                card.innerHTML = `
                    <div class="cat-card-img-container" id="img-container-${index}">
                        <img src="${cat.images[0]}" alt="${cat.nom}" id="img-${index}">
                        <div class="img-dots">
                            ${cat.images.map((_, i) => `<div class="img-dot ${i === 0 ? 'active' : ''}" onclick="changeImg(${index}, ${i})"></div>`).join('')}
                        </div>
                    </div>
                    <div class="cat-card-overlay">
                        <h3 class="cat-card-h">${cat.nom}</h3>
                        <p class="cat-card-p">${cat.description}</p>
                        <span class="cat-card-workshop">${cat.atelier}</span>
                        <div class="crud-btns">
                            <button class="btn-edit" onclick="editCat(${cat.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" onclick="deleteCat(${cat.id})"><i class="fas fa-trash"></i></button>
                            <button class="btn-show" onclick="showCatDetails(${cat.id})" style="background: #3498db; color: white; border: none; padding: 0.5rem; border-radius: 8px; cursor: pointer; margin-left: 5px;"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        window.changeImg = (catIndex, imgIndex) => {
            const img = document.getElementById(`img-${catIndex}`);
            const dots = document.querySelectorAll(`#img-container-${catIndex} .img-dot`);
            
            img.style.opacity = '0';
            setTimeout(() => {
                img.src = categoriesData[catIndex].images[imgIndex];
                img.style.opacity = '1';
                dots.forEach((dot, i) => dot.classList.toggle('active', i === imgIndex));
            }, 300);
        };

        const catForm = document.getElementById('catForm');
        const categoryIdInput = document.getElementById('categoryId');
        const customCatIdInput = null; // Supprimé
        const formTitle = document.getElementById('formTitle');
        const submitBtn = document.getElementById('submitBtn');
        const imgOpts = document.querySelectorAll('.cat-img-opt');

        // Validation JS
        catForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Reset errors
            document.querySelectorAll('.form-group').forEach(g => g.classList.remove('has-error'));

            // Name
            if (document.getElementById('catName').value.trim().length < 3) {
                document.getElementById('catName').closest('.form-group').classList.add('has-error');
                isValid = false;
            }

            // Description
            if (document.getElementById('catDesc').value.trim().length < 10) {
                document.getElementById('catDesc').closest('.form-group').classList.add('has-error');
                isValid = false;
            }

            // Atelier
            if (document.getElementById('catWorkshop').value.trim() === '') {
                document.getElementById('catWorkshop').closest('.form-group').classList.add('has-error');
                isValid = false;
            }

            // Images
            const selectedImgs = document.querySelectorAll('.cat-img-opt.selected');
            if (selectedImgs.length === 0) {
                document.getElementById('imgGroup').classList.add('has-error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert("Veuillez corriger les erreurs dans le formulaire.");
            }
        });

        // Multi-image selector logic
        imgOpts.forEach(opt => {
            opt.addEventListener('click', () => {
                opt.classList.toggle('selected');
                const checkbox = opt.querySelector('input[type="checkbox"]');
                checkbox.checked = opt.classList.contains('selected');
                updatePreviewImages();
            });
        });

        function getSelectedImages() {
            const selected = [];
            document.querySelectorAll('.cat-img-opt.selected').forEach(opt => {
                selected.push(opt.getAttribute('data-img'));
            });
            return selected;
        }

        function updatePreviewImages() {
            const selected = getSelectedImages();
            if (selected.length > 0) {
                document.querySelector('#previewImgContainer img').src = selected[0];
            }
        }

        // Live Preview Logic
        catForm.nom.addEventListener('input', (e) => document.getElementById('previewName').textContent = e.target.value || "Nouvelle Catégorie");
        catForm.description.addEventListener('input', (e) => document.getElementById('previewDesc').textContent = e.target.value || "Description...");
        catForm.atelier.addEventListener('input', (e) => document.getElementById('previewWorkshop').textContent = e.target.value || "Nom de l'atelier");

        catForm.addEventListener('submit', (e) => {
            // Validation personnalisée (remplace HTML5 required)
            const fields = [
                { id: 'catName', label: 'Nom' },
                { id: 'catDesc', label: 'Description' },
                { id: 'catWorkshop', label: 'Atelier' }
            ];

            let errors = [];
            fields.forEach(f => {
                const val = document.getElementById(f.id).value.trim();
                if (!val) errors.push(f.label);
            });

            if (errors.length > 0) {
                e.preventDefault();
                alert("Veuillez remplir les champs suivants : " + errors.join(", "));
                return;
            }

            // Si on utilise le bouton principal (submitBtn), on force la création d'un nouveau
            if (e.submitter && e.submitter.id === "submitBtn") {
                categoryIdInput.value = "0";
            }
        });

        window.resetCatForm = () => {
            catForm.reset();
            categoryIdInput.value = "0";
            formTitle.textContent = "Détails de la Catégorie";
            submitBtn.innerHTML = '<i class="fas fa-magic"></i> GÉNÉRER';
            submitBtn.style.background = "#2ecc71";
            
            imgOpts.forEach(opt => {
                opt.classList.remove('selected');
                opt.querySelector('input[type="checkbox"]').checked = false;
            });
            
            document.getElementById('previewName').textContent = "Nouvelle Catégorie";
            document.getElementById('previewDesc').textContent = "Description...";
            document.getElementById('previewWorkshop').textContent = "Nom de l'atelier";
            document.querySelector('#previewImgContainer img').src = "https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800";
        };

        window.deleteCat = (id) => {
            if (confirm("Voulez-vous vraiment supprimer cette catégorie ?")) {
                window.location.href = `index.php?action=delete_category&id=${id}`;
            }
        };

        window.editCat = (id) => {
            const cat = categoriesData.find(c => c.id == id);
            if (!cat) return;

            catForm.nom.value = cat.nom;
            catForm.description.value = cat.description;
            catForm.atelier.value = cat.atelier;
            categoryIdInput.value = cat.id;
            
            formTitle.textContent = "Modifier la Catégorie";
            submitBtn.innerHTML = '<i class="fas fa-plus"></i> ENREGISTRER (NOUVEAU)';
            submitBtn.style.background = "#27ae60";
            
            // Bouton de confirmation de modification (optionnel si vous voulez un bouton séparé comme les autres)
            // Pour rester cohérent, on va s'assurer que le submit crée un nouveau si on utilise le bouton principal
            
            // Update image selector
            imgOpts.forEach(opt => {
                const imgUrl = opt.getAttribute('data-img');
                const isSelected = cat.images.includes(imgUrl);
                opt.classList.toggle('selected', isSelected);
                opt.querySelector('input[type="checkbox"]').checked = isSelected;
            });

            document.getElementById('previewName').textContent = cat.nom;
            document.getElementById('previewDesc').textContent = cat.description;
            document.getElementById('previewWorkshop').textContent = cat.atelier;
            updatePreviewImages();
            
            window.scrollTo({ top: catForm.offsetTop - 100, behavior: 'smooth' });
        };

        window.showCatDetails = (id) => {
            const cat = categoriesData.find(c => c.id == id);
            if (!cat) return;

            const modal = document.getElementById('catModal');
            const details = document.getElementById('modalCatDetails');
            
            details.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">ID Catégorie</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">#${cat.id}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Nom</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${cat.nom}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Atelier</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${cat.atelier}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Date Création</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${cat.created_at}</span>
                </div>
                <div style="grid-column:span 2; display:flex; flex-direction:column; gap:5px; background:#f8fcf6; padding:1rem; border-radius:1rem;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Description</span>
                    <span style="font-size:1rem; line-height:1.6; color:#2c3e50;">${cat.description}</span>
                </div>
            `;

            modal.style.display = 'flex';
        };

        window.closeCatModal = () => {
            document.getElementById('catModal').style.display = 'none';
        };

        window.onclick = (event) => {
            const catModal = document.getElementById('catModal');
            const eventModal = document.getElementById('eventModal');
            const participantModal = document.getElementById('participantModal');
            
            if (event.target == catModal) closeCatModal();
            if (event.target == eventModal) if(window.closeEventModal) window.closeEventModal();
            if (event.target == participantModal) if(window.closeModal) window.closeModal();
        };

        renderCategories();

        // Auto-show after update
        const urlParams = new URLSearchParams(window.location.search);
        const lastId = urlParams.get('id');
        if (lastId && urlParams.get('success') === 'saved') {
            setTimeout(() => showCatDetails(lastId), 500);
        }
    </script>
</body>
</html>

<?php
require_once __DIR__ . '/../../config.php';
$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM Post WHERE id_post = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
}

$users = $pdo->query("SELECT id_user, nom_utilisateur FROM Utilisateur ORDER BY nom_utilisateur ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $auteur_id = (int)($_POST['auteur_id'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    $type_post = $_POST['type_post'] ?? 'Article';

    if (empty($titre) || empty($contenu) || $auteur_id <= 0 || strlen($titre) < 5 || strlen($contenu) < 5) {
        // En cas d'échec (si le JS est contourné), on redirige simplement sans alerte intrusive
        header("Location: dashboard.php?section=posts");
        exit;
    }

    // Filtrage des mots inappropriés
    $titre = filterProfanity($titre);
    $contenu = filterProfanity($contenu);

    // Handle Media Upload (Image or Video)
    $image_url = $post ? $post['image_url'] : null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['media']['name']);
        $uploadFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['media']['tmp_name'], $uploadFile)) {
            $image_url = 'uploads/' . $filename;
        }
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE Post SET titre = ?, auteur_id = ?, contenu = ?, type_post = ?, image_url = ? WHERE id_post = ?");
        $stmt->execute([$titre, $auteur_id, $contenu, $type_post, $image_url, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO Post (titre, auteur_id, contenu, type_post, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titre, $auteur_id, $contenu, $type_post, $image_url]);
    }
    header("Location: dashboard.php?section=posts");
    exit;
}

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $id > 0 ? 'Modifier' : 'Ajouter' ?> une Publication</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f6f8f7; padding: 2rem; }
        .form-container { background: white; padding: 2rem; border-radius: 12px; max-width: 600px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h1 { color: #14532d; font-size: 1.5rem; margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500; }
        select, textarea, input[type="text"] { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 0.5rem; font-family: inherit; box-sizing: border-box; }
        select:focus, textarea:focus, input:focus { outline: none; border-color: #166534; }
        .error-msg { color: #ef4444; font-size: 0.8rem; margin-bottom: 1rem; display: block; height: 1rem; }
        button { background: #166534; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
        button:hover { background: #14532d; }
        .cancel { background: white; color: #166534; border: 1px solid #166534; text-decoration: none; padding: 0.7rem 1.5rem; border-radius: 8px; font-weight: 600; display: inline-block; margin-right: 0.5rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><?= $id > 0 ? 'Modifier la publication' : 'Nouvelle publication' ?></h1>
        <form method="POST" id="postForm" enctype="multipart/form-data">
            
            <label>Titre de la publication <span style="color:#ef4444;">*</span></label>
            <input type="text" name="titre" id="titre" value="<?= $post ? $e($post['titre']) : '' ?>" placeholder="Ex: Comment bien manger ?" required>
            <span class="error-msg" id="titre_error"></span>

            <input type="hidden" name="auteur_id" id="auteur_id" value="<?= $post ? (int)$post['auteur_id'] : 1 ?>">

            <label>Type de publication</label>
            <select name="type_post" required>
                <option value="Article" <?= ($post && $post['type_post'] == 'Article') ? 'selected' : '' ?>>Article</option>
                <option value="Question" <?= ($post && $post['type_post'] == 'Question') ? 'selected' : '' ?>>Question</option>
                <option value="Recette" <?= ($post && $post['type_post'] == 'Recette') ? 'selected' : '' ?>>Recette</option>
            </select>
            <span class="error-msg"></span>

            <label>Contenu de la publication <span style="color:#ef4444;">*</span></label>
            <textarea name="contenu" id="contenu" rows="8" placeholder="Écrivez le contenu ici..." required><?= $post ? $e($post['contenu']) : '' ?></textarea>
            <span class="error-msg" id="contenu_error"></span>

            <label>Média (Image ou Vidéo)</label>
            <?php if ($post && $post['image_url']): ?>
                <div style="margin-bottom: 0.5rem; font-size: 0.85rem; color: #6b7280;">Média actuel : <a href="../../<?= $e($post['image_url']) ?>" target="_blank" style="color: #166534; font-weight: 600;">Voir le média</a></div>
            <?php endif; ?>
            
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <input type="file" id="media_input" name="media" accept="image/*,video/*" style="display: none;" onchange="document.getElementById('media_name').textContent = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné'">
                <button type="button" class="cancel" onclick="document.getElementById('media_input').click()" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0;"><i class="fas fa-upload"></i> Sélectionner un fichier</button>
                <span id="media_name" style="font-size: 0.85rem; color: #6b7280;">Aucun fichier sélectionné</span>
            </div>
            <span class="error-msg"></span>

            <div style="margin-top: 1rem;">
                <a href="dashboard.php?section=posts" class="cancel">Annuler</a>
                <button type="submit">Enregistrer</button>
            </div>
        </form>
    </div>

    <script>
    const form = document.getElementById('postForm');
    
    function applyErrorStyle(input, errorSpan, message) {
        errorSpan.textContent = message;
        input.style.border = '1px solid #ef4444';
        input.style.backgroundColor = '#fef2f2';
        
        setTimeout(() => {
            input.style.border = '1px solid #d1d5db';
            input.style.backgroundColor = 'white';
            errorSpan.textContent = '';
        }, 4000);
    }

    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        const titreInput = document.getElementById('titre');
        const contenuInput = document.getElementById('contenu');

        const titreError = document.getElementById('titre_error');
        const contenuError = document.getElementById('contenu_error');

        // Reset errors
        titreError.textContent = '';
        contenuError.textContent = '';
        
        const titre = titreInput.value.trim();
        const contenu = contenuInput.value.trim();



        if (contenu === '') {
            applyErrorStyle(contenuInput, contenuError, 'Le contenu ne peut pas être vide.');
            isValid = false;
        } else if (contenu.length < 5) {
            applyErrorStyle(contenuInput, contenuError, 'Le contenu est trop court (min 5 caractères).');
            isValid = false;
        }

        if (titre === '') {
            applyErrorStyle(titreInput, titreError, 'Le titre est obligatoire.');
            isValid = false;
        } else if (titre.length < 5) {
            applyErrorStyle(titreInput, titreError, 'Le titre est trop court (min 5 caractères).');
            isValid = false;
        } else if (titre.length > 100) {
            applyErrorStyle(titreInput, titreError, 'Le titre est trop long (max 100 caractères).');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>

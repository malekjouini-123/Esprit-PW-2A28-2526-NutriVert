<?php
require_once __DIR__ . '/../../config.php';
$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$reply = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM Reply WHERE id_reply = ?");
    $stmt->execute([$id]);
    $reply = $stmt->fetch();
}

// Fetch posts with author ID
$posts = $pdo->query("SELECT id_post, titre, contenu, auteur_id FROM Post ORDER BY date_publication DESC")->fetchAll();
$users = $pdo->query("SELECT id_user, nom_utilisateur FROM Utilisateur ORDER BY nom_utilisateur ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = (int)$_POST['post_id'];
    $auteur_id = (int)$_POST['auteur_id'];
    $commentaire = trim($_POST['commentaire']);
    
    // Server-side validation
    if ($post_id <= 0 || $auteur_id <= 0 || (empty($commentaire) && empty($image_url)) || (!empty($commentaire) && strlen($commentaire) < 2)) {
        header("Location: dashboard.php?section=posts");
        exit;
    }

    // Filtrage des mots inappropriés
    $commentaire = filterProfanity($commentaire);

    // Handle Image Upload
    $image_url = $reply ? $reply['image_url'] : null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image_url = 'uploads/' . $filename;
        }
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE Reply SET post_id = ?, auteur_id = ?, commentaire = ?, image_url = ? WHERE id_reply = ?");
        $stmt->execute([$post_id, $auteur_id, $commentaire, $image_url, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO Reply (post_id, auteur_id, commentaire, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$post_id, $auteur_id, $commentaire, $image_url]);
    }
    header("Location: dashboard.php?section=comments");
    exit;
}

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

// Determine which author wrote the currently selected post (for edit mode)
$selectedPostAuthorId = null;
if ($reply) {
    foreach ($posts as $p) {
        if ($p['id_post'] == $reply['post_id']) {
            $selectedPostAuthorId = $p['auteur_id'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $id > 0 ? 'Modifier' : 'Ajouter' ?> un Commentaire</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f6f8f7; padding: 2rem; }
        .form-container { background: white; padding: 2rem; border-radius: 12px; max-width: 600px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h1 { color: #14532d; font-size: 1.5rem; margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500; }
        select, textarea, input[type="file"], input[type="text"] { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 0.5rem; font-family: inherit; box-sizing: border-box; }
        select:focus, textarea:focus, input:focus { outline: none; border-color: #166534; }
        .error-msg { color: #ef4444; font-size: 0.8rem; margin-bottom: 1rem; display: block; height: 1rem; }
        button { background: #166534; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
        button:hover { background: #14532d; }
        .cancel { background: white; color: #166534; border: 1px solid #166534; text-decoration: none; padding: 0.7rem 1.5rem; border-radius: 8px; font-weight: 600; display: inline-block; margin-right: 0.5rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><?= $id > 0 ? 'Modifier le commentaire' : 'Nouveau commentaire' ?></h1>
        
        <form method="POST" enctype="multipart/form-data" id="replyForm">
            
            <label>1. Filtrer par Auteur du Post cible (Optionnel)</label>
            <select id="post_author_filter">
                <option value="">Tous les auteurs</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id_user'] ?>" <?= ($selectedPostAuthorId == $u['id_user']) ? 'selected' : '' ?>><?= $e($u['nom_utilisateur']) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="error-msg"></span>

            <label>2. Article / Question rattaché(e) <span style="color:#ef4444;">*</span></label>
            <select name="post_id" id="post_id_select" required>
                <option value="">Sélectionnez un post...</option>
                <!-- Options populated via JS -->
            </select>
            <span class="error-msg" id="post_error"></span>

            <div id="post-preview" style="background: #edf7f0; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: #166534; font-size: 0.88rem; border: 1px solid #d7e5dc; white-space: pre-wrap; display: none;">
            </div>

            <input type="hidden" name="auteur_id" id="auteur_id" value="<?= $reply ? (int)$reply['auteur_id'] : 1 ?>">

            <label>Contenu du commentaire <span style="color:#ef4444;">*</span></label>
            <textarea name="commentaire" id="commentaire" rows="5" required><?= $reply ? $e($reply['commentaire']) : '' ?></textarea>
            <span class="error-msg" id="commentaire_error"></span>

            <label>Joindre une Image (Optionnel)</label>
            <?php if ($reply && $reply['image_url']): ?>
                <div style="margin-bottom: 0.5rem; font-size: 0.85rem; color: #6b7280;">Image actuelle : <a href="../../<?= $e($reply['image_url']) ?>" target="_blank" style="color: #166534; font-weight: 600;">Voir l'image</a></div>
            <?php endif; ?>
            
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <input type="file" id="media_input" name="image" accept="image/*,video/*" style="display: none;" onchange="document.getElementById('media_name').textContent = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné'">
                <button type="button" class="cancel" onclick="document.getElementById('media_input').click()" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0;"><i class="fas fa-upload"></i> Sélectionner une image</button>
                <span id="media_name" style="font-size: 0.85rem; color: #6b7280;">Aucun fichier sélectionné</span>
            </div>
            <span class="error-msg"></span>

            <div style="margin-top: 1rem;">
                <a href="dashboard.php?section=comments" class="cancel">Annuler</a>
                <button type="submit">Enregistrer</button>
            </div>
        </form>
    </div>

    <script>
    const postsData = <?= json_encode($posts, JSON_UNESCAPED_UNICODE) ?>;
    const authorFilter = document.getElementById('post_author_filter');
    const postSelect = document.getElementById('post_id_select');
    const previewElement = document.getElementById('post-preview');
    const replyPostId = <?= $reply ? (int)$reply['post_id'] : 'null' ?>;

    function renderPosts() {
        const filterId = authorFilter.value;
        postSelect.innerHTML = '<option value="">Sélectionnez un post...</option>';
        
        let filteredPosts = postsData;
        if (filterId) {
            filteredPosts = postsData.filter(p => p.auteur_id == filterId);
        }
        
        filteredPosts.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id_post;
            opt.textContent = p.titre;
            if (replyPostId && p.id_post == replyPostId) {
                opt.selected = true;
            }
            postSelect.appendChild(opt);
        });

        updatePreview();
    }

    function updatePreview() {
        const selectedId = postSelect.value;
        if (selectedId) {
            const post = postsData.find(p => p.id_post == selectedId);
            if (post) {
                const content = post.contenu.length > 250 ? post.contenu.substring(0, 250) + '...' : post.contenu;
                previewElement.textContent = "Aperçu : " + content;
                previewElement.style.display = 'block';
                return;
            }
        }
        previewElement.style.display = 'none';
        previewElement.textContent = '';
    }

    authorFilter.addEventListener('change', renderPosts);
    postSelect.addEventListener('change', updatePreview);

    // Initial render
    renderPosts();

    // Form Validation
    const form = document.getElementById('replyForm');
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
        
        const postSelect = document.getElementById('post_id_select');
        const commentaireInput = document.getElementById('commentaire');

        const postError = document.getElementById('post_error');
        const commentaireError = document.getElementById('commentaire_error');

        // Reset errors
        postError.textContent = '';
        commentaireError.textContent = '';
        
        const postId = postSelect.value;
        const commentaire = commentaireInput.value.trim();

        if (!postId) {
            applyErrorStyle(postSelect, postError, 'Veuillez sélectionner un post.');
            isValid = false;
        }



        if (commentaire === '') {
            applyErrorStyle(commentaireInput, commentaireError, 'Le contenu du commentaire ne peut pas être vide.');
            isValid = false;
        } else if (commentaire.length < 2) {
            applyErrorStyle(commentaireInput, commentaireError, 'Le commentaire est trop court (min 2 caractères).');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>

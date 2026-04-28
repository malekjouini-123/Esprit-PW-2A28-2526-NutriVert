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

    if (empty($titre) || empty($contenu) || $auteur_id <= 0) {
        echo "<script>alert('Veuillez remplir tous les champs obligatoires.'); history.back();</script>";
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE Post SET titre = ?, auteur_id = ?, contenu = ?, type_post = ? WHERE id_post = ?");
        $stmt->execute([$titre, $auteur_id, $contenu, $type_post, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO Post (titre, auteur_id, contenu, type_post) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titre, $auteur_id, $contenu, $type_post]);
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
        <form method="POST" id="postForm">
            
            <label>Titre de la publication <span style="color:#ef4444;">*</span></label>
            <input type="text" name="titre" id="titre" value="<?= $post ? $e($post['titre']) : '' ?>" placeholder="Ex: Comment bien manger ?" required>
            <span class="error-msg" id="titre_error"></span>

            <label>Auteur <span style="color:#ef4444;">*</span></label>
            <select name="auteur_id" id="auteur_id" required>
                <option value="">Sélectionnez un auteur...</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id_user'] ?>" <?= ($post && $post['auteur_id'] == $u['id_user']) ? 'selected' : '' ?>><?= $e($u['nom_utilisateur']) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="error-msg" id="auteur_error"></span>

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

            <div style="margin-top: 1rem;">
                <a href="dashboard.php?section=posts" class="cancel">Annuler</a>
                <button type="submit">Enregistrer</button>
            </div>
        </form>
    </div>

    <script>
    const form = document.getElementById('postForm');
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Reset errors
        document.getElementById('titre_error').textContent = '';
        document.getElementById('auteur_error').textContent = '';
        document.getElementById('contenu_error').textContent = '';
        
        const titre = document.getElementById('titre').value.trim();
        const auteurId = document.getElementById('auteur_id').value;
        const contenu = document.getElementById('contenu').value.trim();

        if (titre === '') {
            document.getElementById('titre_error').textContent = 'Le titre est obligatoire.';
            isValid = false;
        }

        if (!auteurId) {
            document.getElementById('auteur_error').textContent = 'Veuillez sélectionner un auteur.';
            isValid = false;
        }

        if (contenu === '') {
            document.getElementById('contenu_error').textContent = 'Le contenu ne peut pas être vide.';
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>

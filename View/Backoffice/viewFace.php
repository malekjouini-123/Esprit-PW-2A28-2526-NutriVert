<?php
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}
$hasFace = !empty($face['face_encoding']);
$preview = $hasFace ? substr($face['face_encoding'], 0, 180) . '...' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriVert | Detail Face ID</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f9ea; color: #1a3a1a; }
        .container { max-width: 760px; margin: 40px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .row { margin: 0.7rem 0; }
        .label { font-weight: bold; color: #2e7d32; }
        pre { white-space: pre-wrap; background: #f1f8e9; padding: 1rem; border-radius: 1rem; overflow-x: auto; }
        .btn { display: inline-block; border: 1px solid #4caf50; background: transparent; color: #2e7d32; padding: 0.45rem 0.9rem; border-radius: 1rem; text-decoration: none; cursor: pointer; font-weight: bold; }
        .btn-danger { border-color: #b00020; color: #b00020; }
    </style>
</head>
<body>
<div class="container">
    <h2>Detail Face ID</h2>
    <div class="row"><span class="label">Utilisateur :</span> <?= htmlspecialchars($face['prenom'] . ' ' . $face['nom']) ?></div>
    <div class="row"><span class="label">Email :</span> <?= htmlspecialchars($face['email']) ?></div>
    <div class="row"><span class="label">Role :</span> <?= htmlspecialchars($face['role']) ?></div>
    <div class="row"><span class="label">Statut :</span> <?= $hasFace ? 'Face ID actif' : 'Aucun Face ID' ?></div>
    <div class="row"><span class="label">Cree le :</span> <?= htmlspecialchars($face['created_at'] ?? '-') ?></div>
    <div class="row"><span class="label">Mis a jour le :</span> <?= htmlspecialchars($face['updated_at'] ?? '-') ?></div>

    <?php if ($hasFace): ?>
        <h3>Apercu encodage</h3>
        <pre><?= htmlspecialchars($preview) ?></pre>
        <form method="post" action="index.php?action=admin-delete-face&user_id=<?= (int)$face['id_utilisateur'] ?>" onsubmit="return confirm('Supprimer ce Face ID ?')">
            <button class="btn btn-danger" type="submit">Supprimer le Face ID</button>
        </form>
    <?php endif; ?>

    <p><a class="btn" href="index.php?action=admin-faces">Retour Face ID</a></p>
</div>
</body>
</html>

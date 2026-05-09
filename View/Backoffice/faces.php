<?php
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriVert | Face ID</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f9ea; color: #1a3a1a; }
        .container { max-width: 1000px; margin: 40px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.9rem; border-bottom: 1px solid #e8f5e9; text-align: left; }
        th { background: #f1f8e9; color: #2e5c1e; }
        .badge { display: inline-block; padding: 0.25rem 0.8rem; border-radius: 2rem; font-size: 0.8rem; font-weight: bold; }
        .badge-ok { background: #c8e6c9; color: #1b5e20; }
        .badge-empty { background: #f5d5d5; color: #8a1c1c; }
        .btn { display: inline-block; border: 1px solid #4caf50; background: transparent; color: #2e7d32; padding: 0.45rem 0.9rem; border-radius: 1rem; text-decoration: none; cursor: pointer; font-weight: bold; }
        .btn-danger { border-color: #b00020; color: #b00020; }
        .success { color: green; margin-bottom: 1rem; }
        .error { color: red; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="container">
    <h2>Gestion Face ID</h2>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <p><a class="btn" href="index.php?action=admin">Retour dashboard</a></p>
    <table>
        <thead>
        <tr>
            <th>Utilisateur</th>
            <th>Email</th>
            <th>Role</th>
            <th>Face ID</th>
            <th>Mis a jour</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td>
                    <?php if (!empty($u['has_face'])): ?>
                        <span class="badge badge-ok">Actif</span>
                    <?php else: ?>
                        <span class="badge badge-empty">Absent</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($u['face_updated_at'] ?? '-') ?></td>
                <td>
                    <a class="btn" href="index.php?action=admin-view-face&user_id=<?= (int)$u['id_utilisateur'] ?>">Voir</a>
                    <?php if (!empty($u['has_face'])): ?>
                        <form method="post" action="index.php?action=admin-delete-face&user_id=<?= (int)$u['id_utilisateur'] ?>" style="display:inline;" onsubmit="return confirm('Supprimer ce Face ID ?')">
                            <button class="btn btn-danger" type="submit">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

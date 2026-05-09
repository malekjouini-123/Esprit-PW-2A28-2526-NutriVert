<?php require_once __DIR__ . '/Verification.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Admin - Utilisateurs</title><style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:8px}</style></head>
<body>
<h2>Liste des utilisateurs</h2>
<a href="index.php?action=addUser">➕ Ajouter un utilisateur</a>
<a href="index.php">⬅ Retour accueil</a>
<br><br>
<table>
    <thead><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= $u['id_utilisateur'] ?></td>
        <td><?= htmlspecialchars($u['nom']) ?></td>
        <td><?= htmlspecialchars($u['prenom']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= $u['role'] ?></td>
        <td><a href="index.php?action=deleteUser&id=<?= $u['id_utilisateur'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
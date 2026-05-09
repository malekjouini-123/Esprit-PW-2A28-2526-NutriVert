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
    <title>NutriVert | Chats IA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f9ea; color: #1a3a1a; }
        .container { max-width: 1100px; margin: 40px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.9rem; border-bottom: 1px solid #e8f5e9; text-align: left; vertical-align: top; }
        th { background: #f1f8e9; color: #2e5c1e; }
        .text-cell { max-width: 360px; white-space: pre-wrap; }
        .btn { display: inline-block; border: 1px solid #4caf50; background: transparent; color: #2e7d32; padding: 0.45rem 0.9rem; border-radius: 1rem; text-decoration: none; font-weight: bold; }
        .muted { color: #666; font-size: 0.85rem; }
    </style>
</head>
<body>
<div class="container">
    <h2>Historique des chats IA</h2>
    <p><a class="btn" href="index.php?action=admin">Retour dashboard</a></p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Utilisateur</th>
                <th>Message</th>
                <th>Reponse IA</th>
                <th>Modele</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($chats)): ?>
            <tr><td colspan="5" class="muted">Aucun chat enregistre.</td></tr>
        <?php endif; ?>
        <?php foreach ($chats as $chat): ?>
            <tr>
                <td><?= htmlspecialchars($chat['created_at']) ?></td>
                <td>
                    <?= htmlspecialchars($chat['prenom'] . ' ' . $chat['nom']) ?><br>
                    <span class="muted"><?= htmlspecialchars($chat['email']) ?></span>
                </td>
                <td class="text-cell"><?= htmlspecialchars($chat['user_message']) ?></td>
                <td class="text-cell"><?= htmlspecialchars($chat['ai_response']) ?></td>
                <td><?= htmlspecialchars($chat['model']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

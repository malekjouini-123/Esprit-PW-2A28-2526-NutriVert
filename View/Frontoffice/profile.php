<?php if (!isset($user)) die("Accès interdit."); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mon profil - NutriVert</title>
    <style>
        body { background: #f0f9ea; font-family: Arial; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 1rem; border: 1px solid #ccc; }
        button { background: #2e7d32; color: white; font-weight: bold; cursor: pointer; border: none; }
        .success { color: green; }
        a { color: #2e7d32; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>👤 Mon profil</h2>
    <p>Bonjour <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)</p>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Poids (kg)</label>
        <input type="number" step="0.1" name="poids" value="<?= htmlspecialchars($user['poids']) ?>">
        <label>Taille (cm)</label>
        <input type="number" step="0.5" name="taille" value="<?= htmlspecialchars($user['taille']) ?>">
        <label>Objectif nutritionnel</label>
        <input type="text" name="objectif" value="<?= htmlspecialchars($user['objectif_nutritionnel']) ?>" placeholder="Ex: Perte de poids, prise de muscle...">
        <label>Régime alimentaire</label>
        <input type="text" name="regime" value="<?= htmlspecialchars($user['regime_alimentaire']) ?>" placeholder="Végétarien, sans gluten, vegan...">
        <button type="submit">Enregistrer les modifications</button>
    </form>
    <p><a href="index.php?action=logout">Se déconnecter</a> | <a href="index.php">Accueil</a></p>
</div>
</body>
</html>
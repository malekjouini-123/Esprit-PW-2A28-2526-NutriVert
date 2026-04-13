<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Connexion - NutriVert</title>
    <style>
        body { background: #f0f9ea; font-family: Arial; }
        .form { max-width: 400px; margin: 50px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 2rem; border: 1px solid #ccc; }
        button { background: #2e7d32; color: white; font-weight: bold; cursor: pointer; border: none; }
        .error { color: red; }
        .success { color: green; }
        a { color: #2e7d32; text-decoration: none; }
    </style>
</head>
<body>
<div class="form">
    <h2>🔐 Connexion</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
    <p><a href="index.php?action=register">Pas de compte ? Inscrivez-vous</a></p>
    <p><a href="index.php">← Retour à l'accueil</a></p>
</div>
</body>
</html>
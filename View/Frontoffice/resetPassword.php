<?php
session_start();

$jsonFile = __DIR__ . '/reset_tokens.json';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['error'] = "Lien invalide.";
    header('Location: login.php');
    exit;
}

// Charger les tokens
if (!file_exists($jsonFile)) {
    $_SESSION['error'] = "Lien invalide ou expiré.";
    header('Location: forgotPassword.php');
    exit;
}

$tokens = json_decode(file_get_contents($jsonFile), true) ?? [];

// Vérifier si le token existe et n'est pas expiré
if (!isset($tokens[$token]) || $tokens[$token]['expires'] < time()) {
    // Supprimer le token expiré
    unset($tokens[$token]);
    file_put_contents($jsonFile, json_encode($tokens, JSON_PRETTY_PRINT));
    $_SESSION['error'] = "Lien expiré ou invalide.";
    header('Location: forgotPassword.php');
    exit;
}

$email = $tokens[$token]['email'];

// Traitement du nouveau mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Le mot de passe doit comporter au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    } else {
        // Ici, mettez à jour le mot de passe de l'utilisateur (dans votre fichier JSON ou système d'authentification)
        // Exemple avec un fichier users.json (si vous utilisez JSON comme base)
        $usersFile = __DIR__ . '/users.json';
        if (file_exists($usersFile)) {
            $users = json_decode(file_get_contents($usersFile), true);
            foreach ($users as &$user) {
                if ($user['email'] === $email) {
                    $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                    break;
                }
            }
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
        }
        
        // Supprimer le token utilisé
        unset($tokens[$token]);
        file_put_contents($jsonFile, json_encode($tokens, JSON_PRETTY_PRINT));
        
        $_SESSION['success'] = "Mot de passe réinitialisé avec succès. Connectez-vous.";
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser mot de passe - NutriVert</title>
    <style>
        body { background: #f0f9ea; font-family: Arial; }
        .form { max-width: 400px; margin: 50px auto; background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 2rem; border: 1px solid #ccc; }
        button { background: #2e7d32; color: white; font-weight: bold; cursor: pointer; border: none; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
<div class="form">
    <h2>🔐 Réinitialiser le mot de passe</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="password" name="password" placeholder="Nouveau mot de passe" required>
        <input type="password" name="confirm_password" placeholder="Confirmer" required>
        <button type="submit">Réinitialiser</button>
    </form>
    <p><a href="login.php">← Retour à la connexion</a></p>
</div>
</body>
</html>
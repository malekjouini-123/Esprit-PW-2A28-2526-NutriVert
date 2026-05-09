<?php
session_start();

// Fichier JSON pour stocker les tokens
$jsonFile = __DIR__ . '/reset_tokens.json';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $_SESSION['error'] = "Veuillez saisir votre email.";
        header('Location: forgotPassword.php');
        exit;
    }
    
    // Charger les tokens existants
    $tokens = [];
    if (file_exists($jsonFile)) {
        $tokens = json_decode(file_get_contents($jsonFile), true) ?? [];
    }
    
    // Générer un token unique (alphanumérique, 64 caractères)
    $token = bin2hex(random_bytes(32));
    $expires = time() + 3600; // expire dans 1 heure
    
    // Stocker le token associé à l'email
    $tokens[$token] = [
        'email' => $email,
        'expires' => $expires
    ];
    
    // Nettoyer les tokens expirés
    foreach ($tokens as $key => $data) {
        if ($data['expires'] < time()) {
            unset($tokens[$key]);
        }
    }
    
    // Sauvegarder dans le fichier JSON
    file_put_contents($jsonFile, json_encode($tokens, JSON_PRETTY_PRINT));
    
    // Lien de réinitialisation (à adapter à votre domaine)
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/resetPassword.php?token=" . $token;
    
    // Envoi via EmailJS (API REST)
    $serviceId  = 'service_zwzm9wd';
    $templateId = 'template_j5artrm';
    $userId     = '6h8lpHtzmnU1t9j0QBCL-';
    
    $payload = [
        'service_id'      => $serviceId,
        'template_id'     => $templateId,
        'user_id'         => $userId,
        'template_params' => [
            'email' => $email,
            'link'  => $resetLink
        ]
    ];
    
    $ch = curl_init('https://api.emailjs.com/api/v1.0/email/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $_SESSION['success'] = "Un email de réinitialisation vous a été envoyé.";
        header('Location: email-confirmation.php');
    } else {
        $_SESSION['error'] = "Erreur d'envoi d'email. Réessayez.";
        header('Location: forgotPassword.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié - NutriVert</title>
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
    <h2>🔑 Mot de passe oublié</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Votre email" required>
        <button type="submit">Envoyer le lien de réinitialisation</button>
    </form>
    <p><a href="index.php?action=login">← Retour à la connexion</a></p>
</div>
</body>
</html>
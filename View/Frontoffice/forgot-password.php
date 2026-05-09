<?php
session_start();

// Mode debug : activer avec ?debug=1 dans l'URL
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

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
    
    // Générer un token unique
    $token = bin2hex(random_bytes(32));
    $expires = time() + 3600; // expire dans 1 heure
    
    // Stocker dans le fichier JSON
    $tokens = [];
    if (file_exists($jsonFile)) {
        $tokens = json_decode(file_get_contents($jsonFile), true) ?? [];
    }
    
    // Nettoyer les tokens expirés
    foreach ($tokens as $key => $data) {
        if ($data['expires'] < time()) {
            unset($tokens[$key]);
        }
    }
    
    $tokens[$token] = [
        'email' => $email,
        'expires' => $expires
    ];
    file_put_contents($jsonFile, json_encode($tokens, JSON_PRETTY_PRINT));
    
    // Lien de réinitialisation
    $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/resetPassword.php?token=" . $token;
    
    // ---------- Envoi via EmailJS ----------
    $serviceId  = 'service_zwzm9wd';
    $templateId = 'template_j5artrm';
    $userId     = 'ddAAvUXhqZqsHZA6S'; // ⚠️ À remplacer par votre User ID public
    
    $payload = [
        'service_id'      => $serviceId,
        'template_id'     => $templateId,
        'user_id'         => $userId,
        'template_params' => [
            'email' => $email,
            'link'  => $resetLink
        ]
    ];
    
    // Initialisation cURL
    $ch = curl_init('https://api.emailjs.com/api/v1.0/email/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // true en production avec certificat valide
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // ---------- Mode debug : affichage des informations ----------
    if ($debug) {
        echo "<h2>🔍 Mode DEBUG - EmailJS</h2>";
        echo "<h3>📤 Payload envoyé :</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . "</pre>";
        echo "<h3>📥 Réponse brute :</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        echo "<h3>📊 Code HTTP :</h3>";
        echo "<pre>$httpCode</pre>";
        if ($curlError) {
            echo "<h3>❌ Erreur cURL :</h3>";
            echo "<pre>$curlError</pre>";
        }
        echo "<hr><a href='forgotPassword.php'>← Revenir au formulaire</a>";
        exit;
    }
    
    // Si pas en debug, traitement normal
    if ($httpCode === 200) {
        $_SESSION['success'] = "Un email de réinitialisation vous a été envoyé.";
        header('Location: email-confirmation.php');
        exit;
    } else {
        // En cas d'erreur, on loggue mais on affiche un message générique
        error_log("EmailJS error: HTTP $httpCode - $response");
        $_SESSION['error'] = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
        header('Location: forgotPassword.php');
        exit;
    }
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
        .debug-info { background: #f4f4f4; border-left: 4px solid #ff9800; padding: 0.5rem 1rem; margin: 1rem 0; font-size: 0.9rem; }
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
    
    <?php if ($debug): ?>
        <div class="debug-info">
            ⚠️ Mode debug actif. Les détails de l'envoi EmailJS s'afficheront après soumission.
        </div>
    <?php else: ?>
        <div class="debug-info" style="background:#e7f3e7; border-left-color:#2e7d32;">
            💡 Pour tester l'envoi EmailJS, ajoutez <code>?debug=1</code> à l'URL du formulaire.
        </div>
    <?php endif; ?>
</div>
</body>
</html>
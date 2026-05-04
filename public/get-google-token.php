<?php
/**
 * Script pour générer le Google Refresh Token
 * Nécessaire pour l'authentification OAuth 2.0 avec Gmail
 * 
 * Usage: Accédez à http://localhost/NutriVertMVC/get-google-token.php
 */

require_once __DIR__ . './../config/env.php';

// Redirection vers Google pour l'authentification
if (!isset($_GET['code'])) {
    // Étape 1: Redirection vers Google
    $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    $params = [
        'client_id' => getenv_safe('GOOGLE_CLIENT_ID'),
        'redirect_uri' => getenv_safe('GOOGLE_REDIRECT_URI'),
        'scope' => 'https://www.googleapis.com/auth/gmail.send',
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    $redirectUrl = $authUrl . '?' . http_build_query($params);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Google OAuth - NutriVert</title>
        <style>
            body {
                background: #f0f9ea;
                font-family: Arial;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .container {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 500px;
            }
            h1 { color: #2e7d32; }
            p { color: #666; }
            .button {
                display: inline-block;
                background: #2e7d32;
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                font-weight: bold;
                margin-top: 1rem;
            }
            .button:hover {
                background: #1b5e20;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔐 Obtenir le Google Refresh Token</h1>
            <p>Cliquez le bouton ci-dessous pour autoriser NutriVert à accéder à votre email Gmail.</p>
            <p>Vous serez redirigé vers Google pour l'authentification.</p>
            <a href="<?php echo $redirectUrl; ?>" class="button">Se connecter avec Google</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Étape 2: Récupération du Refresh Token
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $params = [
        'client_id' => getenv_safe('GOOGLE_CLIENT_ID'),
        'client_secret' => getenv_safe('GOOGLE_CLIENT_SECRET'),
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => getenv_safe('GOOGLE_REDIRECT_URI')
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($response, true);
    
    if (isset($tokenData['error'])) {
        $error = $tokenData['error_description'] ?? $tokenData['error'];
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Erreur OAuth</title>
            <style>
                body { background: #f0f9ea; font-family: Arial; padding: 2rem; }
                .error { background: #ffebee; border-left: 4px solid #f44336; padding: 1rem; border-radius: 5px; color: #c62828; }
            </style>
        </head>
        <body>
            <div class="error">
                <h2>❌ Erreur d'authentification</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    $refreshToken = $tokenData['refresh_token'] ?? null;
    
    if ($refreshToken) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Token Généré</title>
            <style>
                body {
                    background: #f0f9ea;
                    font-family: Arial;
                    padding: 2rem;
                }
                .success {
                    background: #e8f5e9;
                    border-left: 4px solid #2e7d32;
                    padding: 1.5rem;
                    border-radius: 5px;
                    max-width: 600px;
                    margin: 0 auto;
                }
                .token-box {
                    background: #f5f5f5;
                    border: 1px solid #ddd;
                    padding: 1rem;
                    border-radius: 5px;
                    margin: 1rem 0;
                    word-break: break-all;
                    font-family: monospace;
                    font-size: 0.9rem;
                    user-select: all;
                }
                h2 { color: #2e7d32; }
                .warning {
                    background: #fff3e0;
                    border-left: 4px solid #ff9800;
                    padding: 1rem;
                    margin: 1rem 0;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="success">
                <h2>✅ Token généré avec succès!</h2>
                
                <p>Copiez le Refresh Token ci-dessous et ajoutez-le à votre fichier <code>.env</code>:</p>
                
                <div class="token-box">
                    <?php echo htmlspecialchars($refreshToken); ?>
                </div>
                
                <div class="warning">
                    <strong>⚠️ Important:</strong> 
                    <ul>
                        <li>Ne jamais partager ce token</li>
                        <li>Ne jamais le commit dans Git</li>
                        <li>Le garder secret comme un mot de passe</li>
                    </ul>
                </div>
                
                <h3>Prochaines étapes:</h3>
                <ol>
                    <li>Ouvrez le fichier <code>.env</code></li>
                    <li>Remplacez <code>GOOGLE_REFRESH_TOKEN=AJOUTER_VOTRE_REFRESH_TOKEN_ICI</code></li>
                    <li>Par: <code>GOOGLE_REFRESH_TOKEN=<?php echo htmlspecialchars($refreshToken); ?></code></li>
                    <li>Sauvegardez et testez vos emails</li>
                </ol>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo '<h2>❌ Impossible d\'obtenir le Refresh Token</h2>';
        echo '<pre>' . htmlspecialchars(json_encode($tokenData, JSON_PRETTY_PRINT)) . '</pre>';
    }
}

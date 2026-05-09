<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email envoyé - NutriVert</title>
    <style>
        body { 
            background: #f0f9ea; 
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 2rem;
            border-radius: 2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        h1 {
            color: #2e7d32;
            margin-bottom: 1rem;
        }
        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .email-display {
            background: #f0f9ea;
            border: 2px solid #2e7d32;
            padding: 1rem;
            border-radius: 1rem;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 1.5rem;
        }
        .dev-section {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .dev-section h3 {
            color: #d19c04;
            margin-top: 0;
            font-size: 0.95rem;
        }
        .reset-link {
            background: #f0f9ea;
            border: 1px solid #2e7d32;
            padding: 0.8rem;
            border-radius: 0.5rem;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        .reset-link a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
        }
        .reset-link a:hover {
            text-decoration: underline;
        }
        .actions {
            margin-top: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0.5rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-primary {
            background: #2e7d32;
            color: white;
        }
        .btn-primary:hover {
            background: #1b5e20;
        }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #bdbdbd;
        }
        .info-text {
            font-size: 0.9rem;
            color: #999;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="success-icon">✅</div>
    <h1>Email envoyé avec succès!</h1>
    
    <div class="message">
        <p>Un email de réinitialisation a été envoyé à:</p>
    </div>
    
    <div class="email-display">
        <?= htmlspecialchars($email ?? 'votre adresse email'); ?>
    </div>
    
    <div class="message">
        <p>Veuillez vérifier votre boîte de réception et cliquez sur le lien pour réinitialiser votre mot de passe.</p>
        <p><strong>Note:</strong> Le lien expire dans 1 heure.</p>
    </div>
    
    <!-- En production: ne pas afficher le lien de test pour des raisons de sécurité -->
    
    <div class="actions">
        <a href="index.php?action=login" class="btn btn-primary">← Retour à la connexion</a>
        <a href="index.php" class="btn btn-secondary">Accueil</a>
    </div>
    
    <div class="info-text">
        Vous n'avez pas reçu l'email? Vérifiez votre dossier "Spam" ou <a href="index.php?action=forgot-password">réessayez</a>.
    </div>
</div>
</body>
</html>

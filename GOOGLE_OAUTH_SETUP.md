# 📧 Configuration Google OAuth pour Envoi d'Emails

## ✅ Étapes de Configuration

### 1️⃣ Installer les Dépendances

```bash
cd C:\xampp\htdocs\NutriVertMVC
composer install
```

### 2️⃣ Obtenir votre Client Secret

1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Sélectionnez votre projet (ou créez-en un)
3. Allez à **APIs & Services** → **Credentials**
4. Trouvez votre application OAuth 2.0 (Desktop application)
5. Cliquez sur l'icône pour révéler le **Client Secret**
6. Copiez-le

### 3️⃣ Générer le Refresh Token

1. Ouvrez votre navigateur
2. Accédez à: `http://localhost/NutriVertMVC/public/get-google-token.php`
3. Cliquez sur "Se connecter avec Google"
4. Authentifiez-vous avec votre compte Gmail
5. Autorisez NutriVert à accéder à vos emails
6. Copiez le Refresh Token affichéé

### 4️⃣ Configurer le fichier `.env`

Éditez le fichier `.env` à la racine du projet:

```ini
# Configuration Google OAuth 2.0 pour Gmail
GOOGLE_CLIENT_ID=659040716385-245agcfion0fdr3l56io1b4vroj9jdot.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=VOTRE_CLIENT_SECRET_COPIE_ETAPE_2
GOOGLE_REFRESH_TOKEN=VOTRE_REFRESH_TOKEN_COPIE_ETAPE_3
GOOGLE_REDIRECT_URI=http://localhost/NutriVertMVC/public/get-google-token.php

# Configuration Email
MAIL_FROM=votre-email@gmail.com
MAIL_SMTP_HOST=smtp.gmail.com
MAIL_SMTP_PORT=587
MAIL_SMTP_ENCRYPTION=tls
```

### 5️⃣ Utiliser le Service d'Email

#### Exemple: Envoyer un email de réinitialisation de mot de passe

```php
<?php
require_once __DIR__ . '/Model/EmailService.php';

$emailService = new EmailService();

$resetLink = 'http://localhost/NutriVertMVC/reset-password.php?token=abc123';
$success = $emailService->sendPasswordReset(
    'user@example.com',
    $resetLink,
    'Jean Dupont'
);

if ($success) {
    echo "Email envoyé avec succès!";
} else {
    echo "Erreur lors de l'envoi de l'email";
}
?>
```

#### Exemple: Envoyer un email de confirmation

```php
<?php
$emailService = new EmailService();

$confirmationLink = 'http://localhost/NutriVertMVC/confirm-email.php?token=xyz789';
$success = $emailService->sendConfirmation(
    'newuser@example.com',
    $confirmationLink,
    'Marie Martin'
);
?>
```

## 🔒 Sécurité

⚠️ **IMPORTANT:**
- ✅ Le fichier `.env` est automatiquement ignoré par Git (voir `.gitignore`)
- ✅ Ne JAMAIS commit les secrets
- ✅ Ne JAMAIS partager le Refresh Token
- ✅ Utilisez des variables d'environnement en production

## 🧪 Test

Créez un fichier `test-google-email.php`:

```php
<?php
require_once __DIR__ . '/Model/EmailService.php';

try {
    $emailService = new EmailService();
    $success = $emailService->send(
        'votre-email@gmail.com',
        'Test NutriVert',
        '<h2>Test d\'envoi d\'email</h2><p>Si vous recevez cet email, la configuration Google OAuth fonctionne!</p>'
    );
    
    if ($success) {
        echo "✅ Email de test envoyé avec succès!";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
```

## 🚀 En Production

Pour utiliser en production:
1. Utilisez des variables d'environnement système
2. Configurez les variables dans votre serveur
3. Ne mettez jamais le fichier `.env` en production

## 📚 Ressources

- [Google Cloud Console](https://console.cloud.google.com/)
- [Gmail API Documentation](https://developers.google.com/gmail/api)
- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer)

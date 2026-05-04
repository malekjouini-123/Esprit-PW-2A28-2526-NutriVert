# 🚀 DÉMARRAGE RAPIDE - Configuration Gmail OAuth

## ✅ Votre identifiant a été intégré!

Votre **Client ID Google** a été sauvegardé:
```
659040716385-245agcfion0fdr3l56io1b4vroj9jdot.apps.googleusercontent.com
```

---

## 📋 3 Étapes Simples pour Terminer la Configuration

### 1️⃣ Accéder à la Configuration Automatique
Ouvrez dans votre navigateur:
```
http://localhost/NutriVertMVC/public/setup-email.php
```

### 2️⃣ Obtenir votre Client Secret
- Allez sur: https://console.cloud.google.com/
- Allez à **Credentials**
- Trouvez votre application Desktop OAuth
- Cliquez l'icône pour révéler le **Client Secret**

### 3️⃣ Générer le Refresh Token
- Depuis `setup-email.php`, allez à l'étape 2
- Cliquez le lien d'authentification
- Connectez-vous avec votre Gmail
- Autorisez NutriVert
- Copiez le token généré

---

## 📁 Fichiers Créés

| Fichier | Description |
|---------|-------------|
| `.env` | Configuration sécurisée (gardé secret) |
| `.env.example` | Template de configuration |
| `.gitignore` | `.env` est protégé du Git |
| `config/env.php` | Loader des variables d'environnement |
| `config/mail-config.php` | Configuration email |
| `Model/EmailService.php` | Service d'envoi d'emails |
| `public/setup-email.php` | Configuration visuelle (étapes 1-3) |
| `public/get-google-token.php` | Génération du Refresh Token |
| `public/test-email-oauth.php` | Test d'envoi d'emails |

---

## 💻 Utilisation dans le Code

### Envoyer un Email

```php
<?php
require_once __DIR__ . '/Model/EmailService.php';

$emailService = new EmailService();

// Email de réinitialisation de mot de passe
$success = $emailService->sendPasswordReset(
    'user@example.com',
    'http://localhost/reset.php?token=abc123',
    'Jean Dupont'
);

if ($success) {
    echo "✅ Email envoyé!";
}
?>
```

### Envoyer un Email Personnalisé

```php
<?php
$emailService = new EmailService();

$success = $emailService->send(
    'recipient@example.com',
    'Sujet de l\'email',
    '<h1>Bonjour</h1><p>Contenu HTML</p>'
);
?>
```

---

## 🧪 Tester Votre Configuration

Ouvrez:
```
http://localhost/NutriVertMVC/public/test-email-oauth.php
```

Entrez votre email et cliquez "Envoyer Email de Test"

---

## 🔒 Sécurité

✅ Le fichier `.env` est automatiquement:
- Exclu du Git grâce à `.gitignore`
- Jamais commité dans le repository
- Gardé secret sur votre serveur

⚠️ **NE JAMAIS:**
- Partager votre `.env`
- Commit le `.env` dans Git
- Partager votre Refresh Token

---

## 🆘 Dépannage

### "Erreur: PHPMailer n'est pas installé"
**Solution:** Le service utilise un fallback SMTP natif. Pas besoin d'installation.

### "L'email n'arrive pas"
**Vérifiez:**
1. L'adresse Gmail est correcte dans `.env`
2. Le Refresh Token est valide
3. Consultez les logs PHP pour plus de détails

### "Erreur d'authentification 535"
**Problèmes possibles:**
- Client Secret incorrect
- Refresh Token expiré (générer un nouveau)
- Double-vérification nécessaire sur le compte Google

---

## 📞 Support

Pour plus d'informations:
- [Google OAuth Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Gmail SMTP Setup](https://support.google.com/accounts/answer/185833)
- [PHPMailer GitHub](https://github.com/PHPMailer/PHPMailer)

---

**Configuration complète!** 🎉

Vous pouvez maintenant envoyer des emails depuis votre application NutriVert.

# 📧 Configuration des Emails - Guide XAMPP

## Étape 1: Activer l'envoi d'emails en XAMPP

### Windows (XAMPP avec Sendmail)

#### 1. Configurer Sendmail
1. Ouvrez: `C:\xampp\sendmail\sendmail.ini`
2. Trouvez la ligne commençant par `smtp_server=`
3. Remplacez par votre serveur SMTP:

**Option A: Gmail** (moins recommandé sans APP PASSWORD)
```ini
smtp_server=smtp.gmail.com
smtp_port=587
smtp_ssl=tls
auth_username=votre-email@gmail.com
auth_password=votre-mot-de-passe-application
```

**Option B: Mailtrap (Test gratuit - Recommandé)**
1. Allez sur [Mailtrap.io](https://mailtrap.io)
2. Créez un compte gratuit
3. Copiez les paramètres SMTP:
```ini
smtp_server=live.smtp.mailtrap.io
smtp_port=587
smtp_ssl=tls
auth_username=votre_username
auth_password=votre_mot_de_passe
```

**Option C: Serveur mail local**
```ini
smtp_server=localhost
smtp_port=25
```

#### 2. Configurer PHP
1. Ouvrez: `C:\xampp\php\php.ini`
2. Trouvez la section `[mail function]`
3. Modifiez:
```ini
[mail function]
SMTP=smtp.gmail.com
smtp_port=587
sendmail_from=votre-email@gmail.com
sendmail_path="\"C:\xampp\sendmail\sendmail.exe\" -t -i"
```

#### 3. Redémarrer XAMPP
- Ouvrez le Control Panel XAMPP
- Cliquez "Stop" puis "Start" sur Apache

## Configuration Détaillée par Fournisseur

### 📧 Gmail
```ini
smtp_server=smtp.gmail.com
smtp_port=587
smtp_ssl=tls
force_sender=votre-email@gmail.com
auth_username=votre-email@gmail.com
auth_password=votre-mot-de-passe-application
```
**Important:** Générez un [mot de passe d'application](https://support.google.com/accounts/answer/185833) sur votre compte Google

### 🔒 Mailtrap (Testé et Recommandé en Développement)
```ini
smtp_server=live.smtp.mailtrap.io
smtp_port=587
smtp_ssl=tls
auth_username=votre_nom_utilisateur
auth_password=votre_mot_de_passe
force_sender=reset-password@nutrivert.local
```

**Avantages:**
- ✅ Tous les emails de test sont capturés
- ✅ Interface web pour voir les emails
- ✅ Pas besoin de compte email réel
- ✅ Plan gratuit suffisant pour tests

### 📬 Hotmail/Outlook
```ini
smtp_server=smtp-mail.outlook.com
smtp_port=587
smtp_ssl=tls
auth_username=votre-email@outlook.com
auth_password=votre-mot-de-passe
```

## Étape 2: Tester la Configuration

### Créer un script de test
Créez `c:\xampp\htdocs\NutriVertMVC\test-email.php`:
```php
<?php
$to = 'test@example.com';
$subject = 'Test Email - NutriVert';
$message = 'Ceci est un email de test depuis votre configuration XAMPP.';
$headers = "From: noreply@nutrivert.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "✅ Email envoyé avec succès!";
} else {
    echo "❌ Erreur lors de l'envoi de l'email.";
}
?>
```

### Accéder au test
Ouvrez: `http://localhost/NutriVertMVC/test-email.php`

## Étape 3: Vérifier les Logs

Si les emails ne sont pas envoyés, vérifiez les logs:
- **PHP Logs:** `C:\xampp\php\logs\php_error_log`
- **Apache Logs:** `C:\xampp\apache\logs\error.log`

## Mode Production vs Développement

### En Développement (Actuellement)
- Les emails sont sauvegardés dans `storage/emails/*.log`
- Vous voyez un lien de test pour cliquer directement
- Consultez `emails-dev.php` pour voir tous les emails envoyés

### En Production
- Modifiez la méthode `saveEmailLog()` ou supprimez-la
- Vérifiez que `mail()` fonctionne avec votre serveur d'hébergement
- Considérez PHPMailer pour plus de robustesse

## Dépannage

| Problème | Solution |
|----------|----------|
| Sendmail.exe pas trouvé | Vérifiez le chemin dans php.ini |
| Port 587 non accessible | Essayez le port 25 ou 465 |
| Authentification échouée | Vérifiez le username/password |
| Erreur STARTTLS | Changez `smtp_ssl` de `tls` à `ssl` et port à 465 |
| Pas de fichier log | Vérifiez les permissions du dossier `storage/emails/` |

## Alternatives: PHPMailer (Plus Robuste)

Si vous avez des problèmes persistants:

```bash
composer require phpmailer/phpmailer
```

Modifiez `forgotPassword()` dans UserController.php pour utiliser PHPMailer.

## Voir les Emails Envoyés

Accédez à: `http://localhost/NutriVertMVC/emails-dev.php`

Cette page affiche tous les emails générés par votre système.

---
**Date de mise à jour:** 2026-05-03
**Version:** 1.0

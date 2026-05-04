# Système de Mot de Passe Oublié

## 📋 Résumé des changements

Un système complet de réinitialisation de mot de passe a été implémenté avec les fonctionnalités suivantes:

### 🔧 Modifications apportées:

#### 1. **Base de données** (`migrations/001_add_password_reset.sql`)
- Ajout de colonnes à la table `utilisateurs`:
  - `reset_token` (VARCHAR 64): Token unique pour la réinitialisation
  - `reset_token_expiry` (DATETIME): Date d'expiration du token

#### 2. **Modèle** (`Model/UserModel.php`)
- `generateResetToken($email)`: Génère un token sécurisé et le sauvegarde en base
- `findByResetToken($token)`: Récupère l'utilisateur associé au token (vérifie l'expiration)
- `resetPassword($token, $newPassword)`: Réinitialise le mot de passe avec le token

#### 3. **Contrôleur** (`Controller/UserController.php`)
- `forgotPassword()`: Traite la demande de réinitialisation et envoie un email
- `resetPassword()`: Valide le token et permet la réinitialisation du mot de passe

#### 4. **Vues**
- `View/Frontoffice/forgot-password.php`: Formulaire pour entrer l'email
- `View/Frontoffice/reset-password.php`: Formulaire pour entrer le nouveau mot de passe
- Modification de `login.php`: Ajout d'un lien "Mot de passe oublié?"

#### 5. **Routage** (`public/index.php`)
- Route `forgot-password`: Affiche le formulaire de récupération
- Route `reset-password`: Traite la réinitialisation avec token

## 🚀 Installation

### 1. Exécuter la migration SQL
Exécutez le script de migration sur votre base de données:

```bash
mysql -u root -p nutrivert < migrations/001_add_password_reset.sql
```

Ou dans phpMyAdmin:
1. Allez dans la base de données `nutrivert`
2. Allez à l'onglet "SQL"
3. Copiez et collez le contenu de `migrations/001_add_password_reset.sql`
4. Cliquez sur "Exécuter"

### 2. Configuration email (optionnel)
Par défaut, le système utilise la fonction `mail()` de PHP. Pour fonctionner correctement:

#### Sous Windows (XAMPP):
Éditez `php.ini`:
```ini
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = votre-email@gmail.com
```

#### Alternative: Configuration SMTP personnalisée
Modifiez la méthode `forgotPassword()` dans `UserController.php` pour utiliser PHPMailer ou SwiftMailer.

## 📝 Utilisation

### Flux utilisateur:

1. **Oublié le mot de passe**:
   - L'utilisateur clique sur "Mot de passe oublié?" sur la page de connexion
   - Entre son adresse email
   - Reçoit un email avec un lien de réinitialisation

2. **Réinitialiser le mot de passe**:
   - L'utilisateur clique sur le lien dans l'email
   - Entre son nouveau mot de passe
   - Le mot de passe est réinitialisé et le token est supprimé

### Token de sécurité:
- Généré aléatoirement avec `bin2hex(random_bytes(32))`
- Expire après 1 heure
- Utilisé une seule fois

## 🔐 Sécurité

✅ **Implémentations de sécurité:**
- Tokens aléatoires et sécurisés
- Expiration des tokens (1 heure)
- Tokens supprimés après utilisation
- Vérification de l'existence de l'utilisateur (ne révèle pas les emails existants)
- Mots de passe hashés avec `PASSWORD_DEFAULT`

## 🧪 Test rapide

1. Allez à `http://localhost/NutriVertMVC/public/index.php?action=login`
2. Cliquez sur "Mot de passe oublié?"
3. Entrez un email existant
4. (Vérifiez les logs PHP ou testez avec un email local)
5. Suivez le lien reçu
6. Entrez votre nouveau mot de passe

## 📧 Dépannage

### L'email n'est pas envoyé
- Vérifiez la configuration SMTP dans `php.ini`
- Vérifiez le fichier de log PHP
- Testez avec `var_dump(mail(...))` 

### Token expiré
- Les tokens expirent après 1 heure
- L'utilisateur peut faire une nouvelle demande

### Lien invalide
- Vérifiez que le token est correctement transmis en URL
- Vérifiez la base de données que le token est bien sauvegardé

## 📚 Fichiers modifiés

```
Controller/UserController.php        (2 nouvelles méthodes)
Model/UserModel.php                  (3 nouvelles méthodes)
View/Frontoffice/login.php           (lien ajouté)
View/Frontoffice/forgot-password.php (nouveau)
View/Frontoffice/reset-password.php  (nouveau)
public/index.php                      (2 nouvelles routes)
migrations/001_add_password_reset.sql (nouveau)
```

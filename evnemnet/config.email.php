<?php
/**
 * Configuration email pour Gmail SMTP.
 * 
 * ÉTAPES POUR ACTIVER GMAIL :
 * 1. Connectez-vous à votre compte Google.
 * 2. Activez la "Validation en 2 étapes".
 * 3. Allez dans "Mots de passe des applications" (recherchez-le dans la barre de recherche Google Account).
 * 4. Générez un mot de passe pour "Autre (Nom personnalisé)" : appelez-le "Nutrivert".
 * 5. Copiez le code de 16 caractères et collez-le ci-dessous dans GMAIL_SMTP_PASS.
 */

define('GMAIL_SMTP_USER', 'VOTRE_EMAIL@gmail.com');
define('GMAIL_SMTP_PASS', 'VOTRE_MOT_DE_PASSE_APPLICATION');
define('GMAIL_SMTP_FROM', 'VOTRE_EMAIL@gmail.com');
// MAIL_FROM_NAME est déjà défini dans config.php
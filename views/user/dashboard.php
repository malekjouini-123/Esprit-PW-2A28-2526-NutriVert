<?php
// views/user/dashboard.php - Dashboard utilisateur (accueil)
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - Nutrivert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="navbar-top">
    <div class="container">
        <div class="logo">
            <i class="fas fa-leaf"></i> Nutrivert
        </div>
        <div class="user-info">
            <span style="font-weight: 600;">👋 <?= htmlspecialchars($user['nom'] ?? 'Utilisateur') ?></span>
            <a href="evnment/index.php" class="btn-logout" style="background: var(--mint);">
                <i class="fas fa-calendar-alt"></i> Événements
            </a>
            <a href="index.php?controller=user&action=logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
</div>

<div class="container py-5">

    <?php if (!empty($flashMessage)): ?>
        <div class="flash-message">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flashMessage) ?>
        </div>
    <?php endif; ?>

    <div class="welcome-banner">
        <h1>🏋️ Bienvenue, <?= htmlspecialchars($user['nom'] ?? 'Utilisateur') ?>!</h1>
        <p>Prêt à commencer votre entraînement?</p>
    </div>

    <?php if (!empty($user['poids']) && !empty($user['taille'])): ?>
        <div>
            <h2 class="section-title">📊 Votre Profil</h2>
            <div class="profile-grid">
                <div class="profile-card">
                    <div class="label">Poids</div>
                    <div class="value"><?= htmlspecialchars((string)$user['poids']) ?> kg</div>
                </div>
                <div class="profile-card">
                    <div class="label">Taille</div>
                    <div class="value"><?= htmlspecialchars((string)$user['taille']) ?> cm</div>
                </div>
                <div class="profile-card">
                    <div class="label">IMC</div>
                    <div class="value"><?= htmlspecialchars((string)$user['imc']) ?></div>
                </div>
                <div class="profile-card">
                    <div class="label">Calories/jour</div>
                    <div class="value"><?= htmlspecialchars((string)$user['calories']) ?> kcal</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 50px;">
        <h2 class="section-title">🎯 Commencer votre Coaching</h2>
        <a href="index.php?controller=user_dashboard&action=coaching_list" class="btn-coaching">
            <i class="fas fa-dumbbell"></i> Voir tous les Coachings
        </a>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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
    <style>
        :root {
            --coral: #FF7E67;
            --mint: #7DCFB6;
            --sage: #4A6B4A;
            --light-bg: #f4f8f4;
        }
        body { 
            background: var(--light-bg); 
            color: var(--sage);
        }
        .navbar-top {
            background: white;
            padding: 15px 0;
            border-bottom: 2px solid var(--mint);
            margin-bottom: 30px;
        }
        .navbar-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--coral);
        }
        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .btn-logout {
            background: var(--coral);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-logout:hover {
            background: #e56a55;
            color: white;
        }
        .welcome-banner {
            background: linear-gradient(135deg, var(--mint), var(--sage));
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            text-align: center;
        }
        .welcome-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .welcome-banner p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .profile-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
        }
        .profile-card .label {
            font-size: 0.9rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .profile-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--coral);
        }
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--sage);
        }
        .btn-coaching {
            background: linear-gradient(135deg, var(--coral), #FFB347);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-coaching:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 126, 103, 0.4);
            color: white;
        }
        .flash-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>

<div class="navbar-top">
    <div class="container">
        <div class="logo">
            <i class="fas fa-leaf"></i> Nutrivert
        </div>
        <div class="user-info">
            <span style="font-weight: 600;">👋 <?= htmlspecialchars($user['nom'] ?? 'Utilisateur') ?></span>
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
        <br><br>
        <a href="index.php?controller=user_dashboard&action=chatbot" class="btn-coaching" style="background: linear-gradient(135deg, #4A6B4A, #7DCFB6);">
            <i class="fas fa-robot"></i> Coaching via Chatbot
        </a>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

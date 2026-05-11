<?php
// views/coaching/complete.php - Page de completion du coaching
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coaching Terminé - Nutrivert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --coral: #FF7E67;
            --mint: #7DCFB6;
            --sage: #4A6B4A;
            --light-bg: #f4f8f4;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, var(--mint), var(--sage));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-top {
            background: white;
            padding: 15px 0;
            border-bottom: 2px solid var(--mint);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .navbar-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.3rem;
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
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background: #e56a55;
            color: white;
        }

        .completion-card {
            background: white;
            border-radius: 25px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 600px;
            animation: zoomIn 0.6s ease-out;
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .celebration-emoji {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: bounce 1s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .completion-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--sage);
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .completion-message {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .stats-grid {
            background: linear-gradient(135deg, rgba(125, 207, 182, 0.1), rgba(74, 107, 74, 0.1));
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--coral);
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 12px 30px;
            border-radius: 25px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--mint), var(--sage));
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 107, 74, 0.3);
            color: white;
        }

        .btn-secondary-custom {
            background: #f0f0f0;
            color: var(--sage);
        }

        .btn-secondary-custom:hover {
            background: #e0e0e0;
            color: var(--sage);
            text-decoration: none;
        }

        .motivational-text {
            font-size: 0.95rem;
            color: #888;
            margin-top: 30px;
            font-style: italic;
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .completion-card { padding: 40px 25px; }
            .completion-title { font-size: 2rem; }
            .celebration-emoji { font-size: 3rem; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="navbar-top">
    <div class="container">
        <div class="logo">🏋️ Nutrivert</div>
        <div class="user-info">
            <span><?= htmlspecialchars($user['nom'] ?? '') ?></span>
            <a href="index.php?controller=user&action=logout" class="btn btn-logout">Déconnexion</a>
        </div>
    </div>
</div>

<div style="padding-top: 80px;">
    <div class="completion-card">

        <div class="celebration-emoji">🎉</div>

        <h1 class="completion-title">Coaching Terminé!</h1>

        <p class="completion-message">
            Vous avez complété avec succès tous les exercices de ce programme. Excellent travail!
        </p>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Durée totale</div>
                <div class="stat-value" id="total-duration">--:--</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Exercices</div>
                <div class="stat-value"><?= $_SESSION['coaching_session']['total_exercises'] ?? 0 ?></div>
            </div>
        </div>

        <!-- Motivational Message -->
        <p class="motivational-text">
            "La constance est la clé du succès. Bravo d'avoir terminé ce coaching! 💪"
        </p>

        <!-- Actions -->
        <div class="actions">
            <a href="index.php?controller=user_dashboard&action=coaching_list" class="btn-action btn-primary-custom">
                <i class="fas fa-redo"></i> Autre coaching
            </a>
            <a href="index.php?controller=user_dashboard&action=index" class="btn-action btn-secondary-custom">
                <i class="fas fa-home"></i> Retour au dashboard
            </a>
        </div>

    </div>
</div>

<script>
    // Calculer la durée totale
    const startedAt = <?= $_SESSION['coaching_session']['started_at'] ?? time() ?>;
    const completedAt = Math.floor(Date.now() / 1000);
    const durationSeconds = completedAt - startedAt;
    const minutes = Math.floor(durationSeconds / 60);
    const seconds = durationSeconds % 60;

    document.getElementById('total-duration').textContent =
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

    // Animation de confettis
    function createConfetti() {
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * window.innerWidth + 'px';
            confetti.style.top = '-10px';
            confetti.style.background = ['#FF7E67', '#7DCFB6', '#4A6B4A'][Math.floor(Math.random() * 3)];
            confetti.style.animation = `fall ${2 + Math.random() * 1}s linear`;

            document.body.appendChild(confetti);

            setTimeout(() => confetti.remove(), 3000);
        }
    }

    const style = document.createElement('style');
    style.textContent = `
        @keyframes fall {
            to {
                transform: translateY(${window.innerHeight + 20}px) rotate(360deg);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Lancer les confettis au chargement - seulement si coaching complètement terminé
    window.addEventListener('load', () => {
        const isFullCompletion = <?= json_encode($isFullCompletion === true) ?>;
        if (isFullCompletion) {
            createConfetti();
            setTimeout(createConfetti, 500);
        }
    });
</script>

</body>
</html>

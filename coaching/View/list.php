<?php
// views/coaching/list.php - Liste des coachings disponibles
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coachings Disponibles - Nutrivert</title>
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
        .btn-back {
            background: var(--mint);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-back:hover {
            background: #6ab89d;
            color: white;
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
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--sage);
        }
        .coaching-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .coaching-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }
        .coaching-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .coaching-card-header {
            background: linear-gradient(135deg, var(--mint), var(--sage));
            color: white;
            padding: 20px;
        }
        .coaching-card-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .coaching-card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .coaching-card-description {
            color: #666;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .coaching-card-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--sage);
        }
        .badge-difficulty {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 10px;
        }
        .badge-easy { background: #d4edda; color: #155724; }
        .badge-medium { background: #fff3cd; color: #856404; }
        .badge-hard { background: #f8d7da; color: #721c24; }
        .btn-start {
            background: linear-gradient(135deg, var(--coral), #FFB347);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: auto;
        }
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 126, 103, 0.4);
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
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 20px;
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
            <a href="index.php?controller=user_dashboard&action=index" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour
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

    <h1 class="section-title">
        <i class="fas fa-dumbbell"></i> Coachings Disponibles
    </h1>

    <?php if (empty($coachings)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h2>Aucun coaching disponible</h2>
            <p style="color: #888; margin-top: 10px;">Il n'y a pas encore de programmes de coaching disponibles.</p>
        </div>
    <?php else: ?>
        <div class="coaching-grid">
            <?php foreach ($coachings as $coaching): ?>
                <div class="coaching-card">
                    <div class="coaching-card-header">
                        <div class="coaching-card-title">
                            <?= htmlspecialchars($coaching['title']) ?>
                        </div>
                        <span class="badge-difficulty badge-<?= htmlspecialchars($coaching['difficulty_level']) ?>">
                            <?= strtoupper(htmlspecialchars($coaching['difficulty_level'])) ?>
                        </span>
                    </div>

                    <div class="coaching-card-body">
                        <p class="coaching-card-description">
                            <?= htmlspecialchars(mb_substr($coaching['description'] ?? '', 0, 150)) ?>
                            <?= mb_strlen($coaching['description'] ?? '') > 150 ? '...' : '' ?>
                        </p>

                        <div class="coaching-card-meta">
                            <span class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <?= htmlspecialchars($coaching['duration_weeks']) ?> semaines
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-running"></i>
                                <?= htmlspecialchars($coaching['exercise_count']) ?> exercices
                            </span>
                        </div>

                        <a href="index.php?controller=user_dashboard&action=start&id=<?= $coaching['id'] ?>"
                           class="btn-start">
                            <i class="fas fa-play"></i> Commencer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

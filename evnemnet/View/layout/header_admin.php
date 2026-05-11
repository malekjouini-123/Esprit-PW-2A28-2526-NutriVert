<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Événements</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: var(--deep);
            color: white;
            padding: 40px 20px;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .sidebar .logo span {
            background: white;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .admin-nav {
            margin-top: 50px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .admin-nav a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 15px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-nav a:hover, .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        .admin-nav a i {
            font-size: 1.2rem;
            color: var(--mint);
        }
        .main-content {
            padding: 40px;
            background: #FFF9F0;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        th {
            text-align: left;
            padding: 15px 20px;
            color: var(--deep);
            font-weight: 700;
        }
        td {
            padding: 20px;
            background: white;
        }
        tr td:first-child { border-radius: 20px 0 0 20px; }
        tr td:last-child { border-radius: 0 20px 20px 0; }
        .action-btn {
            padding: 8px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: 0.2s;
        }
        .btn-edit { color: var(--sunset); }
        .btn-delete { color: var(--coral); }
        .action-btn:hover { background: #f0f0f0; }

        @media (max-width: 992px) {
            .admin-layout { grid-template-columns: 1fr; }
            .sidebar { height: auto; position: relative; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <a href="index.php" class="logo">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Admin</span>
            </a>
            <nav class="admin-nav">
                <a href="admin.php?action=events" class="<?= ($_GET['action'] ?? '') === 'events' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-event"></i> Événements
                </a>
                <a href="admin.php?action=categories" class="<?= ($_GET['action'] ?? '') === 'categories' ? 'active' : '' ?>">
                    <i class="bi bi-tags"></i> Catégories
                </a>
                <a href="admin.php?action=participants" class="<?= ($_GET['action'] ?? '') === 'participants' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Participants
                </a>
                <a href="admin.php?action=recommendations" class="<?= ($_GET['action'] ?? '') === 'recommendations' ? 'active' : '' ?>">
                    <i class="bi bi-star"></i> Recommandations
                </a>
                <hr style="border: none; border-top: 1px dashed rgba(255,255,255,0.2); margin: 20px 0;">
                <a href="index.php">
                    <i class="bi bi-arrow-left-circle"></i> Retour au site
                </a>
            </nav>
        </aside>
        <main class="main-content">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 14px; background: #E6F4EE; color: #2D3E2B; border-left: 5px solid #53B38C;">
                    <?= htmlspecialchars((string)$_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 14px; background: #FFF1ED; color: #8A4A3B; border-left: 5px solid #E67E5F;">
                    <?= htmlspecialchars((string)$_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

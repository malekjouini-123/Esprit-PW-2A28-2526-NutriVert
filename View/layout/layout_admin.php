<?php
declare(strict_types=1);
/** @var string $adminContent */
if (!function_exists('e')) {
    function e(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Marketplace | NutriVert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #fcfdf7;
            color: #2d4f1e;
            min-height: 100vh;
            line-height: 1.5;
        }
        .admin-header {
            background: #fcfbf6;
            border-bottom: 1px solid rgba(45, 79, 30, 0.12);
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-brand {
            font-weight: 700;
            font-size: 1.15rem;
            color: #2d4f1e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .admin-brand i { color: #3a6b28; }
        .admin-nav {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
        }
        .admin-nav a {
            color: #2d4f1e;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.35rem 0;
            border-bottom: 2px solid transparent;
            transition: color 0.2s, border-color 0.2s;
        }
        .admin-nav a:hover { color: #3c7d2a; border-bottom-color: #6aab4e; }
        .admin-nav a.active {
            color: #2a6b1f;
            border-bottom-color: #5a9a3e;
        }
        .admin-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.75rem 1.5rem 3rem;
        }
        .admin-content { }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-brand">
            <i class="fas fa-leaf"></i> NutriVert — Partie admin
        </div>
        <nav class="admin-nav" aria-label="Navigation admin">
            <a href="index.php">Accueil</a>
            <a href="index.php#evenements">Événements (Public)</a>
            <a href="admin_evenements.php" class="active">Gestion Événements</a>
        </nav>
    </header>
    <main class="admin-main admin-content">
        <?= $adminContent ?>
    </main>
</body>
</html>

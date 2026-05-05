<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlers/userscontroller.php';

$usersController = new UsersController();
$users = $usersController->getAllUsers();
// Sort alphabetically A-Z
usort($users, fn($a, $b) => strcasecmp($a->getNomUtilisateur(), $b->getNomUtilisateur()));
$totalUsers = $usersController->getUserCount();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs | NutriVert</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/font-awesome.min.css">
    <style>
        :root {
            --green-dark: #14532d;
            --green-mid: #2e7d32;
            --green-light: #4ade80;
            --green-pale: #f0fdf4;
            --green-soft: #dcfce7;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-body: #f8faf9;
            --glass: rgba(255, 255, 255, 0.9);
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            --radius: 16px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.5);
            margin-bottom: 2rem;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            color: var(--green-dark);
            margin: 0;
            font-size: 2.5rem;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 500px;
        }

        .search-container input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 3rem;
            border-radius: 30px;
            border: 1px solid #e5e7eb;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .search-container input:focus {
            border-color: var(--green-mid);
            box-shadow: 0 0 0 4px var(--green-soft);
        }

        .search-container i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid #edf2f0;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: transform 0.3s;
        }

        .stat-card:hover { transform: translateY(-5px); }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .stat-info h3 { margin: 0; font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-info .value { font-size: 1.8rem; font-weight: 700; color: var(--green-dark); }

        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #f0f4f2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #f9fafb;
            padding: 1.2rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            border-bottom: 1px solid #f3f4f6;
        }

        td {
            padding: 1.2rem;
            border-bottom: 1px solid #f9fafb;
            vertical-align: middle;
        }

        tr:hover { background: #fcfdfc; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .avatar-placeholder {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--green-mid);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .badge-posts { background: #eff6ff; color: #2563eb; }
        .badge-replies { background: #f5f3ff; color: #7c3aed; }
        .badge-likes { background: #ecfdf5; color: #10b981; }
        .badge-dislikes { background: #fef2f2; color: #ef4444; }

        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-view { background: var(--green-pale); color: var(--green-mid); }
        .btn-view:hover { background: var(--green-mid); color: white; }
        .btn-delete { background: #fef2f2; color: #ef4444; }
        .btn-delete:hover { background: #ef4444; color: white; }

        .no-results {
            padding: 4rem;
            text-align: center;
            color: var(--text-muted);
        }

        .no-results i { font-size: 3rem; margin-bottom: 1rem; color: #e5e7eb; }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 48px;
            height: 48px;
            background: var(--green-mid);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(22, 101, 52, 0.3);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1000;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background: var(--green-dark);
            box-shadow: 0 6px 16px rgba(20, 83, 45, 0.4);
            transform: translateY(-4px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="header-flex">
                <div>
                    <h1>Membres</h1>
                    <p style="margin: 5px 0 0 0; color: var(--text-muted);">Gérez la communauté NutriVert</p>
                </div>
                
                
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <a href="users.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;"><i class="fas fa-sync"></i> Rafraîchir</a>
                    <a href="dashboard.php" style="color: var(--green-mid); text-decoration: none; font-weight: 600;"><i class="fas fa-arrow-left"></i> Dashboard</a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--green-soft); color: var(--green-dark);"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3>Total Membres</h3>
                        <div class="value"><?= $totalUsers ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0f2fe; color: #0369a1;"><i class="fas fa-user-check"></i></div>
                    <div class="stat-info">
                        <h3>Affichés</h3>
                        <div class="value"><?= count($users) ?></div>
                    </div>
                </div>
            </div>

            <?php if (count($users) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Activités</th>
                                <th>Réactions</th>
                                <th>Inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <?php if ($user->getPhotoProfil()): ?>
                                                <img src="../../<?= htmlspecialchars($user->getPhotoProfil()) ?>" class="avatar">
                                            <?php else: ?>
                                                <div class="avatar-placeholder">
                                                    <?= strtoupper(substr($user->getNomUtilisateur(), 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight: 700; color: var(--green-dark);"><?= htmlspecialchars($user->getNomUtilisateur()) ?></div>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">ID: #<?= $user->getIdUser() ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($user->getEmail()) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                            <span class="badge badge-posts"><i class="fas fa-file-alt"></i> <?= $user->getPostCount() ?></span>
                                            <span class="badge badge-replies"><i class="fas fa-comment"></i> <?= $user->getReplyCount() ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <span class="badge badge-likes"><i class="fas fa-thumbs-up"></i> <?= $user->getLikeCount() ?></span>
                                            <span class="badge badge-dislikes"><i class="fas fa-thumbs-down"></i> <?= $user->getDislikeCount() ?></span>
                                        </div>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-muted);"><?= $user->getDateInscription() ? date('d M Y', strtotime($user->getDateInscription())) : '—' ?></td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="user_detail.php?id=<?= $user->getIdUser() ?>" class="btn-action btn-view" title="Voir détails"><i class="fas fa-eye"></i></a>
                                            <button onclick="confirmDelete(<?= $user->getIdUser() ?>)" class="btn-action btn-delete" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-users-slash"></i>
                    <h2>Aucun membre trouvé</h2>
                    <p>La communauté NutriVert ne possède pas encore de membres inscrits.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <button id="back-to-top" class="back-to-top" title="Retour en haut">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
        function confirmDelete(id) {
            if (confirm('Voulez-vous vraiment bannir cet utilisateur ? Cette action est irréversible.')) {
                window.location.href = 'delete_user.php?id=' + id;
            }
        }

        // Back to Top Logic
        const backToTopBtn = document.getElementById('back-to-top');
        if (backToTopBtn) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            });

            backToTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    </script>
</body>
</html>

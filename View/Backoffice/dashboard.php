<?php
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../../Model/UserModel.php';
$userModel = new UserModel();

// Gestion ajout utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client';

    if ($nom && $prenom && $email && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $hashed, $role]);
        $_SESSION['success'] = "Utilisateur ajouté avec succès.";
    } else {
        $_SESSION['error'] = "Tous les champs sont requis.";
    }
    header('Location: index.php?action=admin');
    exit;
}

// Gestion suppression utilisateur
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    $userModel->delete($id);
    $_SESSION['success'] = "Utilisateur supprimé.";
    header('Location: index.php?action=admin');
    exit;
}

// Récupération des utilisateurs réels
$users = $userModel->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriVert | Dashboard Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ===== TOUS LES STYLES DE back.html (copiés intégralement) ===== */
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; background: linear-gradient(145deg, #eef7ea 0%, #d9e8d4 100%); min-height: 100vh; overflow-x: hidden; }
.sidebar { width: 280px; height: 100vh; background: linear-gradient(180deg, #0a3d0a 0%, #1a6e1a 100%); position: fixed; color: white; padding: 2rem 1.5rem; box-shadow: 8px 0 30px rgba(0,0,0,0.15); transition: all 0.3s ease; z-index: 100; overflow-y: auto; }
.logo-area { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.12); backdrop-filter: blur(8px); padding: 0.8rem 1.2rem; border-radius: 2rem; margin-bottom: 2.5rem; transition: all 0.3s; border: 1px solid rgba(255,255,255,0.2); }
.logo-area:hover { background: rgba(255,255,255,0.2); transform: scale(1.02); }
.logo-area img { width: 42px; height: 42px; object-fit: contain; background: white; border-radius: 1rem; padding: 6px; }
.logo-area h2 { font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #ffffff, #c8e6c9); -webkit-background-clip: text; background-clip: text; color: transparent; letter-spacing: -0.5px; }
.menu-item { display: flex; align-items: center; gap: 14px; padding: 0.9rem 1rem; margin: 0.5rem 0; color: #e8f5e9; text-decoration: none; border-radius: 1.2rem; transition: all 0.25s ease; font-weight: 500; cursor: pointer; font-size: 0.95rem; }
.menu-item i { width: 24px; font-size: 1.2rem; }
.menu-item:hover { background: rgba(255,255,255,0.18); transform: translateX(8px); }
.menu-item.active { background: linear-gradient(95deg, #4caf50, #2e7d32); box-shadow: 0 6px 14px rgba(76,175,80,0.3); }
.main { margin-left: 280px; padding: 1.8rem 2.2rem; min-height: 100vh; }
.top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: rgba(255,255,255,0.75); backdrop-filter: blur(12px); padding: 0.8rem 2rem; border-radius: 2rem; border: 1px solid rgba(100,180,70,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
.top-bar h1 { font-size: 1.5rem; background: linear-gradient(135deg, #1b5e1a, #4caf50); -webkit-background-clip: text; background-clip: text; color: transparent; display: flex; align-items: center; gap: 10px; }
.admin-badge { background: #2e7d32; padding: 0.4rem 1.2rem; border-radius: 2rem; color: white; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; }
.section { display: none; animation: fadeInUp 0.4s ease-out; }
.section.active-section { display: block; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px);} to { opacity: 1; transform: translateY(0);} }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: rgba(255,255,255,0.88); backdrop-filter: blur(12px); padding: 1.3rem; border-radius: 1.8rem; border: 1px solid rgba(76,175,80,0.25); transition: all 0.3s; text-align: center; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
.stat-card:hover { transform: translateY(-6px); background: white; box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
.stat-card i { font-size: 2.2rem; color: #2e7d32; margin-bottom: 0.5rem; }
.stat-card h3 { font-size: 2rem; font-weight: 800; color: #1b5e1a; }
.stat-card p { color: #555; font-weight: 500; }
.table-container { background: white; border-radius: 1.5rem; padding: 1.2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.05); overflow-x: auto; margin-top: 1rem; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e8f5e9; }
th { background: #f1f8e9; color: #2e5c1e; font-weight: 600; }
tr:hover { background: #f9fff7; }
.badge { background: #c8e6c9; padding: 0.2rem 0.8rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; }
.form-card { background: white; border-radius: 1.5rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.4rem; font-weight: 600; color: #2d3e2a; font-size: 0.85rem; }
input, select { width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid #c8e6c9; background: #fefef7; transition: all 0.2s; font-family: inherit; }
input:focus, select:focus { outline: none; border-color: #4caf50; box-shadow: 0 0 0 3px rgba(76,175,80,0.15); }
.btn-primary { background: linear-gradient(105deg, #2e7d32, #1b5e20); border: none; padding: 0.7rem 1.5rem; border-radius: 2rem; font-weight: 600; color: white; cursor: pointer; transition: 0.2s; margin-right: 0.8rem; }
.btn-primary:hover { transform: scale(1.02); background: linear-gradient(105deg, #3c8c40, #2a6e2a); }
.btn-outline { background: transparent; border: 1.5px solid #4caf50; padding: 0.7rem 1.5rem; border-radius: 2rem; font-weight: 600; color: #2e7d32; cursor: pointer; transition: 0.2s; }
.btn-outline:hover { background: #4caf5010; }
.btn-sm { padding: 0.3rem 0.9rem; font-size: 0.75rem; }
.grid-2col { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; }
.chart-container { background: white; border-radius: 1.5rem; padding: 1rem; margin-top: 1rem; }
@media (max-width: 900px) { .sidebar { width: 80px; padding: 1rem 0.5rem; } .sidebar .menu-item span, .logo-area h2 { display: none; } .logo-area img { margin: 0 auto; } .main { margin-left: 80px; } }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo-area">
        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%234caf50'/%3E%3Cpath fill='white' d='M50 30 L65 45 L55 45 L55 65 L45 65 L45 45 L35 45 Z'/%3E%3C/svg%3E" alt="logo">
        <h2>NutriVert</h2>
    </div>
    <div class="menu-item active" data-section="dashboard"><i class="fas fa-chart-line"></i><span> Dashboard</span></div>
    <div class="menu-item" data-section="users"><i class="fas fa-users"></i><span> Utilisateurs</span></div>
    <div class="menu-item" data-section="coachs"><i class="fas fa-chalkboard-user"></i><span> Coachs</span></div>
    <div class="menu-item" data-section="fournisseurs"><i class="fas fa-store"></i><span> Fournisseurs</span></div>
    <div class="menu-item" data-section="recettes"><i class="fas fa-robot"></i><span> Recettes IA</span></div>
    <div class="menu-item" data-section="events"><i class="fas fa-calendar-alt"></i><span> Événements</span></div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="top-bar">
        <h1><i class="fas fa-seedling"></i> Tableau de bord</h1>
        <div class="admin-badge"><i class="fas fa-shield-alt"></i> <?= htmlspecialchars($_SESSION['user_email']) ?></div>
    </div>

    <!-- SECTION DASHBOARD (statique) -->
    <div id="dashboard-section" class="section active-section">
        <div class="stats-grid" id="statsGrid"></div>
        <div class="grid-2col">
            <div class="chart-container"><canvas id="activityChart" height="200"></canvas></div>
            <div class="form-card"><h3><i class="fas fa-bell"></i> Alertes & activité</h3><div id="recentActivity"></div></div>
        </div>
    </div>

    <!-- SECTION UTILISATEURS (FONCTIONNELLE) -->
    <div id="users-section" class="section">
        <div class="form-card">
            <h2><i class="fas fa-user-plus"></i> Gestion des utilisateurs</h2>
            <?php if (isset($_SESSION['success'])): ?>
                <div style="color:green; margin-bottom:1rem;"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div style="color:red; margin-bottom:1rem;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <div class="grid-2col">
                <div>
                    <form method="post">
                        <div class="form-group"><label>Nom</label><input type="text" name="nom" required></div>
                        <div class="form-group"><label>Prénom</label><input type="text" name="prenom" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                        <div class="form-group"><label>Mot de passe</label><input type="password" name="password" required></div>
                        <div class="form-group"><label>Rôle</label><select name="role"><option value="client">Client</option><option value="admin">Admin</option></select></div>
                        <button type="submit" name="add_user" class="btn-primary"><i class="fas fa-save"></i> Ajouter</button>
                    </form>
                </div>
                <div class="table-container">
                    <table>
                        <thead><tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['nom']) ?></td>
                                <td><?= htmlspecialchars($u['prenom']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge"><?= $u['role'] ?></span></td>
                                <td><a href="index.php?action=admin&delete_user=<?= $u['id_utilisateur'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')" class="btn-outline btn-sm"><i class="fas fa-trash"></i> Supprimer</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION COACHS (statique, design uniquement) -->
    <div id="coachs-section" class="section">
        <div class="form-card"><h2><i class="fas fa-chalkboard-user"></i> Coachs certifiés</h2><p style="color:#777;">Fonctionnalité à venir</p></div>
    </div>

    <!-- SECTION FOURNISSEURS (statique) -->
    <div id="fournisseurs-section" class="section">
        <div class="form-card"><h2><i class="fas fa-store"></i> Fournisseurs partenaires</h2><p style="color:#777;">Fonctionnalité à venir</p></div>
    </div>

    <!-- SECTION RECETTES (statique) -->
    <div id="recettes-section" class="section">
        <div class="form-card"><h2><i class="fas fa-robot"></i> Recettes IA</h2><p style="color:#777;">Fonctionnalité à venir</p></div>
    </div>

    <!-- SECTION ÉVÉNEMENTS (statique) -->
    <div id="events-section" class="section">
        <div class="form-card"><h2><i class="fas fa-calendar-alt"></i> Événements green</h2><p style="color:#777;">Fonctionnalité à venir</p></div>
    </div>
</div>

<script>
    // Données simulées pour le dashboard (statistiques et graphique)
    const stats = {
        users: <?= count($users) ?>,
        coachs: 0,
        fournisseurs: 0,
        recettes: 0,
        events: 0
    };
    document.getElementById('statsGrid').innerHTML = `
        <div class="stat-card"><i class="fas fa-users"></i><h3>${stats.users}</h3><p>Utilisateurs</p></div>
        <div class="stat-card"><i class="fas fa-chalkboard-user"></i><h3>${stats.coachs}</h3><p>Coachs</p></div>
        <div class="stat-card"><i class="fas fa-store"></i><h3>${stats.fournisseurs}</h3><p>Fournisseurs</p></div>
        <div class="stat-card"><i class="fas fa-robot"></i><h3>${stats.recettes}</h3><p>Recettes IA</p></div>
        <div class="stat-card"><i class="fas fa-calendar"></i><h3>${stats.events}</h3><p>Événements</p></div>
    `;
    document.getElementById('recentActivity').innerHTML = `<p><i class="fas fa-user-plus"></i> Dernier utilisateur ajouté : ${<?= json_encode($users[0]['email'] ?? 'aucun') ?>}</p>`;

    const ctx = document.getElementById('activityChart')?.getContext('2d');
    if(ctx) {
        new Chart(ctx, {
            type: 'line',
            data: { labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai'], datasets: [{ label: 'Nouveaux utilisateurs', data: [8, 15, 27, 42, stats.users], borderColor: '#2e7d32', backgroundColor: 'rgba(46,125,50,0.05)', tension: 0.3, fill: true }] },
            options: { responsive: true }
        });
    }

    // Navigation entre sections
    const menuItems = document.querySelectorAll('.menu-item');
    const sections = document.querySelectorAll('.section');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            menuItems.forEach(m => m.classList.remove('active'));
            item.classList.add('active');
            sections.forEach(s => s.classList.remove('active-section'));
            const sectionId = item.getAttribute('data-section') + '-section';
            document.getElementById(sectionId).classList.add('active-section');
        });
    });
</script>
</body>
</html>
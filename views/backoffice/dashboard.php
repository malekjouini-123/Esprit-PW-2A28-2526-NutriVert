<?php
require_once __DIR__ . '/../../config.php';

$pdo = getDB();

$flashMessage = '';
if (isset($_GET['action']) && $_GET['action'] === 'delete_post' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM Post WHERE id_post = ?");
    $stmt->execute([(int)$_GET['id']]);
    $flashMessage = "La publication a été supprimée avec succès.";
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_user' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE id_user = ?");
    $stmt->execute([(int)$_GET['id']]);
    $flashMessage = "L'utilisateur a été supprimé avec succès.";
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_reply' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM Reply WHERE id_reply = ?");
    $stmt->execute([(int)$_GET['id']]);
    $flashMessage = "Le commentaire a été supprimé avec succès.";
}

$totalUsers = $pdo->query("SELECT COUNT(*) FROM Utilisateur")->fetchColumn();
$totalPosts = $pdo->query("SELECT COUNT(*) FROM Post")->fetchColumn();
$totalReplies = $pdo->query("SELECT COUNT(*) FROM Reply")->fetchColumn();
$totalReactions = $pdo->query("SELECT COUNT(*) FROM Reaction")->fetchColumn();

$types = ['Article', 'Question', 'Recette'];
$chartData = [];
$chartLabels = $types;

foreach ($types as $type) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Post WHERE type_post = ?");
    $stmt->execute([$type]);
    $chartData[] = (int)$stmt->fetchColumn();
}

$posts = $pdo->query("
    SELECT p.*, u.nom_utilisateur 
    FROM Post p 
    LEFT JOIN Utilisateur u ON p.auteur_id = u.id_user 
    ORDER BY p.date_publication DESC
")->fetchAll();

$replies = $pdo->query("
    SELECT r.*, u.nom_utilisateur, p.titre as post_titre
    FROM Reply r 
    LEFT JOIN Utilisateur u ON r.auteur_id = u.id_user 
    LEFT JOIN Post p ON r.post_id = p.id_post
    ORDER BY r.date_reply DESC
")->fetchAll();

$users = $pdo->query("SELECT * FROM Utilisateur ORDER BY id_user DESC")->fetchAll();

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriVert | Dashboard Communauté</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
    --green-deep:  #14532d;
    --green-mid:   #166534;
    --green-light: #22c55e;
    --green-pale:  #edf7f0;
    --green-soft:  #d7e5dc;
    --text:        #1f2937;
    --text-muted:  #6b7280;
    --bg:          #f6f8f7;
    --radius:      12px;
    --shadow:      none;
    --sidebar-w:   260px;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    color: var(--text);
}

.sidebar {
    width: var(--sidebar-w);
    height: 100vh;
    background: #ffffff;
    position: fixed;
    top: 0; left: 0;
    padding: 1.5rem 1rem;
    overflow-y: auto;
    z-index: 100;
    border-right: 1px solid #d9e2dd;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.7rem 1rem;
    background: #edf7f0;
    border-radius: 1rem;
    margin-bottom: 2rem;
    border: 1px solid #d7e5dc;
}

.sidebar-logo .icon {
    width: 36px; height: 36px;
    background: var(--green-mid);
    border-radius: 0.8rem;
    display: grid; place-items: center;
    font-size: 1rem; color: white; flex-shrink: 0;
}

.sidebar-logo span {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    color: var(--green-deep);
    white-space: nowrap;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.75rem 1rem;
    margin: 0.3rem 0;
    border-radius: 0.9rem;
    color: var(--text);
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
    font-size: 0.92rem;
}

.menu-item i { width: 20px; font-size: 1rem; flex-shrink: 0; }
.menu-item:hover { background: #edf2ef; }
.menu-item.active { background: var(--green-mid); color: white; }

.main {
    margin-left: var(--sidebar-w);
    padding: 1.5rem 2rem;
    min-height: 100vh;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    padding: 0.9rem 1.8rem;
    border-radius: var(--radius);
    margin-bottom: 1.8rem;
    border: 1px solid #d9e2dd;
    gap: 1rem;
    flex-wrap: wrap;
}

.topbar h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    color: var(--green-deep);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-badge {
    background: #edf7f0;
    padding: 0.4rem 1rem;
    border-radius: 2rem;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--green-deep);
}

.switch-link {
    text-decoration: none;
    border: 1px solid var(--green-mid);
    color: var(--green-mid);
    border-radius: 999px;
    padding: 0.38rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.switch-link:hover { background: var(--green-pale); }

.section { display: none; animation: fadeUp 0.35s ease; }
.section.active-section { display: block; }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 1.2rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: #ffffff;
    border-radius: var(--radius);
    padding: 1.2rem;
    text-align: center;
    border: 1px solid #d9e2dd;
}

.stat-card i { font-size: 1.8rem; color: var(--green-mid); margin-bottom: 0.5rem; display: block; }
.stat-card h3 { font-size: 1.8rem; font-weight: 700; color: var(--green-deep); }
.stat-card p  { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem; }

.form-card {
    background: white;
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #d9e2dd;
}

.form-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    color: var(--green-deep);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.grid-2col {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
}

.btn-primary {
    background: var(--green-mid);
    border: none;
    padding: 0.65rem 1.2rem;
    border-radius: 2rem;
    font-family: inherit;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: 0.2s;
    font-size: 0.84rem;
    margin-right: 0.4rem;
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover { background: var(--green-deep); }

.btn-outline {
    background: transparent;
    border: 1px solid var(--green-light);
    padding: 0.65rem 1.2rem;
    border-radius: 2rem;
    font-family: inherit;
    font-weight: 600;
    color: var(--green-mid);
    cursor: pointer;
    transition: 0.2s;
    font-size: 0.84rem;
    text-decoration: none;
    display: inline-block;
}

.btn-outline:hover { background: var(--green-pale); }
.btn-sm { padding: 0.28rem 0.75rem; font-size: 0.75rem; }

.btn-danger {
    color: #ef4444;
    border-color: #ef4444;
}
.btn-danger:hover {
    background: #fef2f2;
}

.table-container {
    background: white;
    border-radius: var(--radius);
    padding: 1rem;
    overflow-x: auto;
    border: 1px solid #d9e2dd;
}

table { width: 100%; border-collapse: collapse; }

th, td {
    padding: 0.85rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--green-pale);
    font-size: 0.88rem;
    vertical-align: top;
}

th {
    background: var(--green-pale);
    color: var(--green-deep);
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.badge {
    background: var(--green-pale);
    color: var(--green-mid);
    padding: 0.2rem 0.7rem;
    border-radius: 2rem;
    font-size: 0.72rem;
    font-weight: 600;
}

.badge-article { background: #dbeafe; color: #1e40af; }
.badge-question { background: #fef08a; color: #854d0e; }
.badge-recette { background: #fce7f3; color: #9d174d; }

.chart-container {
    background: white;
    border-radius: var(--radius);
    padding: 1.2rem;
    border: 1px solid #d9e2dd;
}

.activity-list p {
    padding: 0.6rem 0;
    border-bottom: 1px solid var(--green-pale);
    font-size: 0.88rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.activity-list p i { color: var(--green-mid); }

.toast {
    position: fixed;
    bottom: 1.5rem; right: 1.5rem;
    background: var(--green-mid);
    color: white;
    padding: 0.9rem 1.4rem;
    border-radius: 1rem;
    font-weight: 600;
    font-size: 0.9rem;
    transform: translateY(200%);
    transition: transform 0.35s ease;
    z-index: 9999;
    max-width: 320px;
}

.toast.show { transform: translateY(0); }

.table-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.hint {
    color: var(--text-muted);
    font-size: 0.82rem;
    margin-bottom: 1rem;
}

@media (max-width: 900px) {
    :root { --sidebar-w: 70px; }
    .sidebar-logo span, .menu-item span { display: none; }
    .sidebar-logo { justify-content: center; }
    .menu-item { justify-content: center; }
    .main { margin-left: 70px; padding: 1rem; }
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="icon"><i class="fas fa-leaf"></i></div>
        <span>NutriVert</span>
    </div>
    <div class="menu-item active" data-section="dashboard"><i class="fas fa-chart-line"></i><span> Dashboard</span></div>
    <div class="menu-item" data-section="posts"><i class="fas fa-file-alt"></i><span> Publications</span></div>
    <div class="menu-item" data-section="comments"><i class="fas fa-comment-dots"></i><span> Commentaires</span></div>
    <div class="menu-item" data-section="users"><i class="fas fa-users"></i><span> Utilisateurs</span></div>
</div>

<div class="main">
    <div class="topbar">
        <h1><i class="fas fa-seedling" style="color:var(--green-light)"></i> Communauté Admin</h1>
        <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
            <a class="switch-link" href="../../index.php">Retour à la Communauté</a>
            <div class="admin-badge"><i class="fas fa-shield-alt"></i> Admin NutriVert</div>
        </div>
    </div>

    <div id="dashboard-section" class="section active-section">
        <div class="stats-grid">
            <div class="stat-card"><i class="fas fa-users"></i><h3><?= $totalUsers ?></h3><p>Membres</p></div>
            <div class="stat-card"><i class="fas fa-file-alt"></i><h3><?= $totalPosts ?></h3><p>Publications</p></div>
            <div class="stat-card"><i class="fas fa-comment-dots"></i><h3><?= $totalReplies ?></h3><p>Commentaires</p></div>
            <div class="stat-card"><i class="fas fa-heart"></i><h3><?= $totalReactions ?></h3><p>Réactions</p></div>
        </div>
        <div class="grid-2col">
            <div class="chart-container">
                <canvas id="activityChart" height="220"></canvas>
            </div>
            <div class="form-card">
                <h2><i class="fas fa-bell"></i> Dernières publications</h2>
                <div class="activity-list">
                    <?php if ($posts === []): ?>
                        <p><i class="fas fa-info-circle"></i> Aucune publication pour le moment.</p>
                    <?php endif; ?>
                    <?php foreach (array_slice($posts, 0, 5) as $post): ?>
                        <p><i class="fas fa-file-alt"></i> <?= $e($post['nom_utilisateur']) ?> a publié: <?= $e(substr($post['titre'], 0, 30)) ?>...</p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="posts-section" class="section">
        <div class="form-card">
            <h2><i class="fas fa-comments"></i> Gestion des publications</h2>
            <p class="hint">Aperçu et modération des articles, questions et recettes partagés par la communauté.</p>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Auteur</th><th>Titre / Contenu</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($posts === []): ?>
                        <tr><td colspan="6">Aucune publication trouvée.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= (int)$post['id_post'] ?></td>
                            <td><?= $e($post['nom_utilisateur']) ?></td>
                            <td>
                                <strong><?= $e($post['titre']) ?></strong><br>
                                <span style="color:#6b7280; font-size:0.8rem;"><?= $e(substr($post['contenu'], 0, 50)) ?>...</span>
                            </td>
                            <td>
                                <?php 
                                    $badgeClass = 'badge';
                                    if ($post['type_post'] === 'Article') $badgeClass .= ' badge-article';
                                    elseif ($post['type_post'] === 'Question') $badgeClass .= ' badge-question';
                                    elseif ($post['type_post'] === 'Recette') $badgeClass .= ' badge-recette';
                                ?>
                                <span class="<?= $badgeClass ?>"><?= $e($post['type_post']) ?></span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($post['date_publication'])) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-outline btn-sm" href="post_form.php?id=<?= (int)$post['id_post'] ?>">Modifier</a>
                                    <a class="btn-outline btn-sm btn-danger" data-confirm href="dashboard.php?action=delete_post&id=<?= (int)$post['id_post'] ?>">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="comments-section" class="section">
        <div class="form-card">
            <h2><i class="fas fa-comment-dots"></i> Gestion des commentaires</h2>
            <div style="margin-bottom:1rem;">
                <a class="btn-primary" href="reply_form.php">Ajouter un Commentaire</a>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Auteur</th><th>Post</th><th>Contenu</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($replies === []): ?>
                        <tr><td colspan="6">Aucun commentaire trouvé.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($replies as $reply): ?>
                        <tr>
                            <td><?= (int)$reply['id_reply'] ?></td>
                            <td><?= $e($reply['nom_utilisateur']) ?></td>
                            <td>
                                <span style="font-size: 0.8rem; color: #6b7280;"><?= $e(substr($reply['post_titre'], 0, 30)) ?>...</span><br>
                                <a href="../../index.php#post-<?= $reply['post_id'] ?>" target="_blank" style="font-size: 0.7rem; color: #166534; text-decoration: none;"><i class="fas fa-external-link-alt"></i> Voir le post</a>
                            </td>
                            <td><?= $e(substr($reply['commentaire'], 0, 50)) ?>...</td>
                            <td><?= date('d/m/Y H:i', strtotime($reply['date_reply'])) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-outline btn-sm" href="reply_form.php?id=<?= (int)$reply['id_reply'] ?>">Modifier</a>
                                    <a class="btn-outline btn-sm btn-danger" data-confirm href="dashboard.php?action=delete_reply&id=<?= (int)$reply['id_reply'] ?>">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="users-section" class="section">
        <div class="form-card">
            <h2><i class="fas fa-users"></i> Gestion des utilisateurs</h2>
            <p class="hint">Liste des membres de la communauté.</p>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($users === []): ?>
                        <tr><td colspan="4">Aucun utilisateur trouvé.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= (int)$user['id_user'] ?></td>
                            <td><?= $e($user['nom_utilisateur']) ?></td>
                            <td><?= $e($user['email']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-outline btn-sm btn-danger" data-confirm href="dashboard.php?action=delete_user&id=<?= (int)$user['id_user'] ?>">Bannir</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const chartLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
const chartData = <?= json_encode($chartData) ?>;
const flashMessage = <?= json_encode($flashMessage, JSON_UNESCAPED_UNICODE) ?>;

function showToast(msg) {
    if (!msg) return;
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._to);
    t._to = setTimeout(() => t.classList.remove('show'), 3200);
}

document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active-section'));
        item.classList.add('active');
        document.getElementById(item.dataset.section + '-section').classList.add('active-section');
    });
});

document.querySelectorAll('[data-confirm]').forEach(link => {
    link.addEventListener('click', event => {
        if (!confirm('Confirmer la suppression de cet element ?')) {
            event.preventDefault();
        }
    });
});

const ctx = document.getElementById('activityChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels.length ? chartLabels : ['Aucun'],
            datasets: [{
                label: 'Publications par type',
                data: chartData.length ? chartData : [0],
                borderColor: '#2e7d32',
                backgroundColor: ['#dbeafe', '#fef08a', '#fce7f3'],
                borderWidth: 1.5,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
}

showToast(flashMessage);
</script>
</body>
</html>

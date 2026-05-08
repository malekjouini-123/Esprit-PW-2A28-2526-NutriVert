<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';

$pdo = getDB();

$flashMessage = '';
if (isset($_SESSION['flash'])) {
    $flashMessage = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete_post' && isset($_GET['id'])) {
        require_once __DIR__ . '/../../controlers/postcontroller.php';
        $postController = new PostController();
        $postController->deletePost((int)$_GET['id']);
        $_SESSION['flash'] = "La publication a été supprimée avec succès.";
        header("Location: dashboard.php?section=posts");
        exit;
    }

    if ($_GET['action'] === 'delete_user' && isset($_GET['id'])) {
        $id_user = (int)$_GET['id'];
        $pdo->prepare("SET FOREIGN_KEY_CHECKS=0")->execute();
        try {
            // Delete user's reactions
            $pdo->prepare("DELETE FROM Reaction WHERE user_id = ?")->execute([$id_user]);
            $pdo->prepare("DELETE FROM ReactionReply WHERE user_id = ?")->execute([$id_user]);
            
            $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE id_user = ?");
            $stmt->execute([$id_user]);
            $_SESSION['flash'] = "L'utilisateur a été banni avec succès.";
        } catch (Exception $e) {
            $_SESSION['flash'] = "Erreur: Impossible de supprimer cet utilisateur car il possède encore des publications ou commentaires.";
        }
        $pdo->prepare("SET FOREIGN_KEY_CHECKS=1")->execute();
        header("Location: dashboard.php?section=users");
        exit;
    }

    if ($_GET['action'] === 'delete_reply' && isset($_GET['id'])) {
        $id_reply = (int)$_GET['id'];
        $pdo->prepare("SET FOREIGN_KEY_CHECKS=0")->execute();
        
        $stmt = $pdo->prepare("SELECT id_reply FROM Reply WHERE parent_reply_id = ?");
        $stmt->execute([$id_reply]);
        $subReplies = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $allRepliesToDelete = array_merge([$id_reply], $subReplies);

        if (!empty($allRepliesToDelete)) {
            $in = str_repeat('?,', count($allRepliesToDelete) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM ReactionReply WHERE reply_id IN ($in)");
            $stmt->execute($allRepliesToDelete);
        }

        if (!empty($subReplies)) {
            $inSub = str_repeat('?,', count($subReplies) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM Reply WHERE id_reply IN ($inSub)");
            $stmt->execute($subReplies);
        }

        $stmt = $pdo->prepare("DELETE FROM Reply WHERE id_reply = ?");
        $stmt->execute([$id_reply]);
        
        $pdo->prepare("SET FOREIGN_KEY_CHECKS=1")->execute();
        $_SESSION['flash'] = "Le commentaire a été supprimé avec succès.";
        header("Location: dashboard.php?section=posts");
        exit;
    }

    if ($_GET['action'] === 'repost_post' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("INSERT INTO Post (titre, contenu, media_url, type_post, auteur_id) 
                               SELECT CONCAT('reposted: ', titre), contenu, media_url, type_post, auteur_id 
                               FROM Post WHERE id_post = ?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = "La publication a été republiée avec succès.";
        header("Location: dashboard.php?section=posts");
        exit;
    }
}

$totalUsers = $pdo->query("SELECT COUNT(*) FROM Utilisateur")->fetchColumn();
$totalPosts = $pdo->query("SELECT COUNT(*) FROM Post")->fetchColumn();
$totalReplies = $pdo->query("SELECT COUNT(*) FROM Reply")->fetchColumn();
$totalReactions = $pdo->query("SELECT (SELECT COUNT(*) FROM Reaction) + (SELECT COUNT(*) FROM ReactionReply)")->fetchColumn();

$types = ['Article', 'Question', 'Recette'];
$chartData = [];
$chartLabels = $types;

foreach ($types as $type) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Post WHERE type_post = ?");
    $stmt->execute([$type]);
    $chartData[] = (int)$stmt->fetchColumn();
}

// Stats for Reactions Chart (Sum of Posts reactions and Replies reactions)
$totalLikes = $pdo->query("SELECT (SELECT COUNT(*) FROM Reaction WHERE type_reaction = 'Like') + (SELECT COUNT(*) FROM ReactionReply WHERE type_reaction = 'Like')")->fetchColumn();
$totalDislikes = $pdo->query("SELECT (SELECT COUNT(*) FROM Reaction WHERE type_reaction = 'Dislike') + (SELECT COUNT(*) FROM ReactionReply WHERE type_reaction = 'Dislike')")->fetchColumn();
$reactionChartData = [(int)$totalLikes, (int)$totalDislikes];

$sort = $_GET['sort'] ?? 'newest';
$orderBy = "p.date_publication DESC";
if ($sort === 'most_liked') $orderBy = "likes_count DESC";
elseif ($sort === 'most_disliked') $orderBy = "dislikes_count DESC";

$posts = $pdo->query("
    SELECT p.*, u.nom_utilisateur,
        (SELECT COUNT(*) FROM Reaction WHERE post_id = p.id_post AND type_reaction = 'Like') as likes_count,
        (SELECT COUNT(*) FROM Reaction WHERE post_id = p.id_post AND type_reaction = 'Dislike') as dislikes_count,
        (SELECT COUNT(*) FROM Reply WHERE post_id = p.id_post) as replies_count
    FROM Post p 
    LEFT JOIN Utilisateur u ON p.auteur_id = u.id_user 
    ORDER BY $orderBy
")->fetchAll();

// Fetch all replies with their own reaction counts, grouped by post_id
$repliesByPost = [];
$allRepliesRaw = $pdo->query("
    SELECT r.*, u.nom_utilisateur,
        (SELECT COUNT(*) FROM ReactionReply WHERE reply_id = r.id_reply AND type_reaction = 'Like') as likes_count,
        (SELECT COUNT(*) FROM ReactionReply WHERE reply_id = r.id_reply AND type_reaction = 'Dislike') as dislikes_count
    FROM Reply r 
    LEFT JOIN Utilisateur u ON r.auteur_id = u.id_user 
    ORDER BY r.date_reply ASC
")->fetchAll();

foreach ($allRepliesRaw as $r) {
    $repliesByPost[$r['post_id']][] = $r;
}

// Fetch minimal user list for dropdowns
$users = $pdo->query("SELECT id_user, nom_utilisateur FROM Utilisateur ORDER BY nom_utilisateur ASC")->fetchAll();

// Determine active section server-side
$activeSection = $_GET['section'] ?? 'dashboard';
$validSections = ['dashboard', 'posts', 'mentions'];
if (!in_array($activeSection, $validSections)) $activeSection = 'dashboard';

// --- MENTIONS LOGIC ---
if ($activeSection === 'mentions') {
    $pdo->query("UPDATE Utilisateur SET date_lecture_mentions = NOW() WHERE id_user IN (1, 102)");
}
$adminData = $pdo->query("SELECT MAX(date_lecture_mentions) as last_read FROM Utilisateur WHERE id_user IN (1, 102)")->fetch();
$lastReadTime = strtotime($adminData['last_read'] ?? '2000-01-01 00:00:00');

// L'administrateur peut être "Marc Robert" (id=1) ou le compte spécifique "Admin NutriVert"
$adminUser = $pdo->query("SELECT nom_utilisateur FROM Utilisateur WHERE id_user = 1")->fetch();
$adminName1 = $adminUser ? $adminUser['nom_utilisateur'] : 'Marc Robert';
$adminName2 = 'Admin NutriVert';

$adminTag1 = '@' . $adminName1;
$adminTag2 = '@' . $adminName2;

$likeTag1 = '%' . $adminTag1 . '%';
$likeTag2 = '%' . $adminTag2 . '%';

// Mentions in Posts (title or content)
$stmt = $pdo->prepare("
    SELECT 'Publication' as type, p.id_post as id, p.titre, p.contenu, p.date_publication as date_creation, u.nom_utilisateur as auteur
    FROM Post p
    LEFT JOIN Utilisateur u ON p.auteur_id = u.id_user
    WHERE p.contenu LIKE ? OR p.titre LIKE ? OR p.contenu LIKE ? OR p.titre LIKE ?
");
$stmt->execute([$likeTag1, $likeTag1, $likeTag2, $likeTag2]);
$postMentions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mentions in Replies
$stmt = $pdo->prepare("
    SELECT 'Commentaire' as type, r.id_reply as id, r.post_id, r.commentaire as contenu, r.date_reply as date_creation, u.nom_utilisateur as auteur
    FROM Reply r
    LEFT JOIN Utilisateur u ON r.auteur_id = u.id_user
    WHERE r.commentaire LIKE ? OR r.commentaire LIKE ?
");
$stmt->execute([$likeTag1, $likeTag2]);
$replyMentions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$allMentions = array_merge($postMentions, $replyMentions);
usort($allMentions, function($a, $b) {
    return strtotime($b['date_creation']) - strtotime($a['date_creation']);
});

$mentionsCount = 0;
foreach ($allMentions as $m) {
    if (strtotime($m['date_creation']) > $lastReadTime) {
        $mentionsCount++;
    }
}

$adminTagDisplay = $adminTag1 . " ou " . $adminTag2;
// ----------------------

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

.search-box {
    position: relative;
    flex: 1;
    max-width: 450px;
    margin: 0 1.5rem;
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.8rem;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    font-family: inherit;
    font-size: 0.9rem;
    outline: none;
    transition: all 0.2s;
}

.search-box input:focus {
    background: white;
    border-color: var(--green-mid);
    box-shadow: 0 0 0 4px var(--green-soft);
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
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
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
    border-color: var(--green-mid);
    background: var(--green-pale);
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
}

/* Animation de surbrillance pour les lignes ciblées par une ancre (#post-row-id) */
tr:target {
    animation: highlight-row 3s ease-out;
    background-color: #d1fae5 !important;
}

@keyframes highlight-row {
    0% { background-color: #d1fae5; box-shadow: 0 0 0 4px #d1fae5 inset; }
    100% { background-color: transparent; box-shadow: 0 0 0 0 transparent inset; }
}
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
/* Custom Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.show {
    display: flex;
    opacity: 1;
}

.modal-box {
    background: white;
    padding: 2rem;
    border-radius: 1.5rem;
    width: 90%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: scale(0.9);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-overlay.show .modal-box {
    transform: scale(1);
}

.modal-icon {
    width: 60px;
    height: 60px;
    background: #fef2f2;
    color: #ef4444;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1.5rem;
}

.modal-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: var(--green-deep);
    margin-bottom: 0.5rem;
}

.modal-text {
    color: var(--text-muted);
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 2rem;
}

.modal-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.modal-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.modal-btn-cancel {
    background: #f3f4f6;
    color: #4b5563;
}

.modal-btn-cancel:hover {
    background: #e5e7eb;
}

.modal-btn-confirm {
    background: #ef4444;
    color: white;
}

.modal-btn-confirm:hover {
    background: #dc2626;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

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
    background: var(--green-deep);
    box-shadow: 0 6px 16px rgba(20, 83, 45, 0.4);
    transform: translateY(-4px);
}

.back-to-top:active {
    transform: translateY(0);
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="icon"><i class="fas fa-leaf"></i></div>
        <span>NutriVert</span>
    </div>
    <a class="menu-item <?= $activeSection === 'dashboard' ? 'active' : '' ?>" href="dashboard.php" data-section="dashboard"><i class="fas fa-chart-line"></i><span> Dashboard</span></a>
    <a class="menu-item <?= $activeSection === 'posts' ? 'active' : '' ?>" href="dashboard.php?section=posts" data-section="posts"><i class="fas fa-file-alt"></i><span> Publications</span></a>
    <a class="menu-item <?= $activeSection === 'mentions' ? 'active' : '' ?>" href="dashboard.php?section=mentions" data-section="mentions">
        <i class="fas fa-bell"></i><span style="display:flex; align-items:center; width:100%;"> Mentions 
        <?php if ($mentionsCount > 0): ?>
            <span style="background:#ef4444; color:white; border-radius:999px; padding:0.1rem 0.4rem; font-size:0.7rem; margin-left:auto;"><?= $mentionsCount ?></span>
        <?php endif; ?>
        </span>
    </a>
</div>

<div class="main">
    <div class="topbar">
        <h1 style="margin:0; font-size: 1.4rem;"><i class="fas fa-seedling" style="color:var(--green-light)"></i> Admin</h1>
        <!-- Barre de recherche déplacée -->
        <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
            <a class="switch-link" href="../../index.php">Retour</a>
            <div class="admin-badge"><i class="fas fa-shield-alt"></i> Admin NutriVert</div>
        </div>
    </div>

    <div id="dashboard-section" class="section <?= $activeSection === 'dashboard' ? 'active-section' : '' ?>">
        <div class="stats-grid">
            <div class="stat-card" onclick="location.href='dashboard.php?section=posts'"><i class="fas fa-file-alt"></i><h3><?= $totalPosts ?></h3><p>Publications</p></div>
            <div class="stat-card" onclick="location.href='dashboard.php?section=posts'"><i class="fas fa-comment-dots"></i><h3><?= $totalReplies ?></h3><p>Commentaires</p></div>
            <div class="stat-card" onclick="location.href='dashboard.php?section=posts'"><i class="fas fa-heart"></i><h3><?= $totalReactions ?></h3><p>Réactions</p></div>
        </div>
        <div class="grid-2col">
            <div class="chart-container">
                <canvas id="activityChart" height="220"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="reactionChart" height="220"></canvas>
            </div>
        </div>
        <div class="form-card" style="margin-top:2rem;">
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

    <div id="posts-section" class="section <?= $activeSection === 'posts' ? 'active-section' : '' ?>">
        <div class="form-card" style="margin-bottom: 2rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
                <h2><i class="fas fa-pen"></i> Créer une publication</h2>
            </div>
            
            <div style="margin-top:1.5rem;">
                <form id="quickPostForm" action="post_form.php" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:1rem; margin-top: 1rem;">
                    <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <input type="text" id="quick_titre" name="titre" placeholder="Titre de la publication (min 5 caractères)" required style="padding: 0.8rem; border-radius: 8px; border: 1px solid #d1d5db; font-family: inherit; outline: none; transition: all 0.2s; width: 100%;">
                        <span id="quick_titre_error" style="color: #ef4444; font-size: 0.75rem; font-weight: 500; margin-left: 0.5rem; display: none;"></span>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <textarea id="quick_contenu" name="contenu" rows="3" placeholder="Que voulez-vous partager avec la communauté ?" required style="padding: 0.8rem; border-radius: 8px; border: 1px solid #d1d5db; font-family: inherit; resize: vertical; outline: none; transition: all 0.2s; width: 100%;"></textarea>
                        <span id="quick_contenu_error" style="color: #ef4444; font-size: 0.75rem; font-weight: 500; margin-left: 0.5rem; display: none;"></span>
                    </div>

                    <div style="display:flex; gap: 1rem; align-items:center; flex-wrap: wrap;">
                        <select name="type_post" style="padding: 0.6rem; border-radius: 8px; border: 1px solid #d1d5db; font-family: inherit; outline: none; cursor: pointer;">
                            <option value="Article">📄 Article</option>
                            <option value="Question">❓ Question</option>
                            <option value="Recette">🍳 Recette</option>
                        </select>
                        <input type="file" id="quick_media_input" name="media" accept="image/*,video/*" style="display: none;" onchange="document.getElementById('quick_media_name').textContent = this.files[0] ? this.files[0].name : ''">
                        <button type="button" class="btn-outline btn-sm" onclick="document.getElementById('quick_media_input').click()" style="display: flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1rem; border-radius: 8px; cursor: pointer;"><i class="fas fa-image"></i> Ajouter un Média</button>
                        <span id="quick_media_name" style="font-size: 0.8rem; color: #6b7280; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                        <button type="submit" class="btn-primary" style="margin-left:auto;"><i class="fas fa-paper-plane"></i> Publier</button>
                    </div>
                </form>
            </div>
            
            <script>
            document.getElementById('quickPostForm').addEventListener('submit', function(e) {
                let isValid = true;
                const titre = document.getElementById('quick_titre');
                const contenu = document.getElementById('quick_contenu');
                
                const tErr = document.getElementById('quick_titre_error');
                const cErr = document.getElementById('quick_contenu_error');
                
                function showError(input, span, msg) {
                    span.textContent = msg;
                    span.style.display = 'block';
                    input.style.borderColor = '#ef4444';
                    input.style.backgroundColor = '#fef2f2';
                    setTimeout(() => {
                        span.style.display = 'none';
                        input.style.borderColor = '#d1d5db';
                        input.style.backgroundColor = 'white';
                    }, 4000);
                }

                if (titre.value.trim().length < 5) {
                    showError(titre, tErr, 'Le titre doit faire au moins 5 caractères.');
                    isValid = false;
                }
                if (contenu.value.trim().length < 5) {
                    showError(contenu, cErr, 'Le contenu doit faire au moins 5 caractères.');
                    isValid = false;
                }
                
                
                if (!isValid) e.preventDefault();
            });
            </script>
        </div>

        <div class="form-card">
            <div style="display:flex; flex-direction:column; gap:1rem; margin-bottom: 1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem;"><i class="fas fa-comments"></i> Gestion des publications</h2>
                        <p class="hint">Aperçu et modération des articles, questions et recettes partagés par la communauté.</p>
                    </div>
                    <div class="table-actions" style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Trier :</span>
                        <a href="dashboard.php?section=posts&sort=newest" class="btn-outline btn-sm <?= ($sort === 'newest') ? 'active' : '' ?>" style="border-radius: 4px;">Récents</a>
                        <a href="dashboard.php?section=posts&sort=most_liked" class="btn-outline btn-sm <?= ($sort === 'most_liked') ? 'active' : '' ?>" style="border-radius: 4px; color: #10b981; border-color: #10b981;">+ Likes</a>
                        <a href="dashboard.php?section=posts&sort=most_disliked" class="btn-outline btn-sm <?= ($sort === 'most_disliked') ? 'active' : '' ?>" style="border-radius: 4px; color: #ef4444; border-color: #ef4444;">+ Dislikes</a>
                    </div>
                </div>
                <div class="search-box" style="margin: 0; max-width: 100%;">
                    <i class="fas fa-search" style="left: 1.2rem;"></i>
                    <input type="text" id="globalSearch" placeholder="Rechercher une publication par auteur, titre, type ou contenu..." style="width: 100%; padding: 0.8rem 1rem 0.8rem 2.8rem; border-radius: 8px; border: 1px solid #d1d5db; background: white; font-size: 0.95rem;">
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Auteur</th><th>Titre / Contenu</th><th>Type</th><th>Réactions</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($posts === []): ?>
                        <tr><td colspan="6">Aucune publication trouvée.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($posts as $post): ?>
                        <tr id="post-row-<?= (int)$post['id_post'] ?>">
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
                            <td>
                                <div style="display:flex; flex-direction:column; gap:4px;">
                                    <div style="display:flex; gap:0.8rem; align-items:center;">
                                        <span title="Likes" style="color:#10b981; font-weight:600;"><i class="fas fa-thumbs-up"></i> <?= (int)$post['likes_count'] ?> <span style="display:none">Likes</span></span>
                                        <span title="Dislikes" style="color:#ef4444; font-weight:600;"><i class="fas fa-thumbs-down"></i> <?= (int)$post['dislikes_count'] ?> <span style="display:none">Dislikes</span></span>
                                    </div>
                                    <span style="font-size: 0.75rem; color: var(--green-mid); font-weight: 600;"><i class="fas fa-comment"></i> <?= (int)$post['replies_count'] ?> commentaires</span>
                                </div>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($post['date_publication'])) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-outline btn-sm" href="dashboard.php?action=repost_post&id=<?= (int)$post['id_post'] ?>" title="Republier cette publication" style="color: #6366f1; border-color: #6366f1;"><i class="fas fa-retweet"></i> Reposter</a>
                                    <a class="btn-outline btn-sm" href="post_form.php?id=<?= (int)$post['id_post'] ?>">Modifier</a>
                                    <a class="btn-outline btn-sm btn-danger" data-confirm href="dashboard.php?action=delete_post&id=<?= (int)$post['id_post'] ?>">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                        <?php if (isset($repliesByPost[$post['id_post']])): ?>
                            <tr style="background: #f9fafb;">
                                <td colspan="6" style="padding: 0 1rem 1rem 3rem; border-bottom: 2px solid #edf7f0;">
                                    <div style="border-left: 3px solid var(--green-soft); padding-left: 1rem; margin-top: 0.5rem;">
                                        <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Commentaires associés</h4>
                                        <?php foreach ($repliesByPost[$post['id_post']] as $reply): ?>
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6;">
                                                <div style="font-size: 0.85rem;">
                                                    <strong><?= $e($reply['nom_utilisateur']) ?>:</strong> <?= $e($reply['commentaire']) ?>
                                                    <br>
                                                    <span style="font-size: 0.75rem; color: #10b981; margin-right: 0.5rem;"><i class="fas fa-thumbs-up"></i> <?= (int)$reply['likes_count'] ?></span>
                                                    <span style="font-size: 0.75rem; color: #ef4444; margin-right: 0.5rem;"><i class="fas fa-thumbs-down"></i> <?= (int)$reply['dislikes_count'] ?></span>
                                                    <span style="font-size: 0.7rem; color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($reply['date_reply'])) ?></span>
                                                </div>
                                                <div class="table-actions">
                                                    <a href="reply_form.php?id=<?= (int)$reply['id_reply'] ?>" style="color: var(--green-mid); font-size: 0.75rem; text-decoration: none; margin-right: 0.5rem;"><i class="fas fa-edit"></i></a>
                                                    <a data-confirm href="dashboard.php?action=delete_reply&id=<?= (int)$reply['id_reply'] ?>" style="color: #ef4444; font-size: 0.75rem; text-decoration: none;"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<div id="mentions-section" class="section <?= $activeSection === 'mentions' ? 'active-section' : '' ?>">
    <div class="form-card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom: 1.5rem;">
            <div>
                <h2 style="margin-bottom: 0.5rem;"><i class="fas fa-bell"></i> Mentions administrateur</h2>
                <p class="hint">Liste de toutes les publications et commentaires où l'on vous a mentionné (<?= $e($adminTagDisplay) ?>).</p>
            </div>
        </div>
        
        <div class="notifications-feed" style="display:flex; flex-direction:column; gap:1rem;">
            <?php if ($allMentions === []): ?>
                <div style="padding:3rem 2rem; text-align:center; color:var(--text-muted); background:#f9fafb; border-radius:12px; border:2px dashed #e5e7eb;">
                    <i class="fas fa-bell-slash" style="font-size:2.5rem; margin-bottom:1rem; color:#d1d5db;"></i>
                    <p style="font-size:1.05rem; font-weight:500;">Vous n'avez aucune mention pour le moment.</p>
                </div>
            <?php endif; ?>
            
            <?php foreach ($allMentions as $mention): ?>
                <div class="notification-card" style="background:white; border:1px solid #e5e7eb; border-radius:12px; padding:1.2rem; display:flex; gap:1rem; transition:transform 0.2s, box-shadow 0.2s; position:relative; overflow:hidden;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <!-- Indicateur visuel à gauche -->
                    <div style="position:absolute; left:0; top:0; bottom:0; width:4px; background:var(--green-mid);"></div>
                    
                    <!-- Icône de type -->
                    <div style="width:48px; height:48px; border-radius:50%; background:#edf7f0; color:var(--green-mid); display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0;">
                        <i class="fas <?= $mention['type'] === 'Publication' ? 'fa-file-alt' : 'fa-comment-dots' ?>"></i>
                    </div>
                    
                    <!-- Contenu de la notification -->
                    <div style="flex:1;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.8rem; flex-wrap:wrap; gap:0.5rem;">
                            <div style="font-size:0.95rem;">
                                <strong style="color:var(--green-deep); font-weight:700;"><i class="fas fa-user-circle" style="color:#9ca3af; margin-right:0.3rem;"></i> <?= $e($mention['auteur']) ?></strong> 
                                vous a mentionné dans 
                                <span style="font-weight:600; color:#6b7280;"><?= $mention['type'] === 'Publication' ? 'une publication' : 'un commentaire' ?></span>
                            </div>
                            <div style="font-size:0.8rem; color:#9ca3af; display:flex; align-items:center; gap:0.4rem; font-weight:500;">
                                <i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($mention['date_creation'])) ?>
                            </div>
                        </div>
                        
                        <!-- Extrait du texte -->
                        <div style="background:#f9fafb; padding:1rem 1.2rem; border-radius:8px; border-left:3px solid #d1d5db; color:var(--text); font-size:0.9rem; line-height:1.6;">
                            <?php 
                            $snippet = $mention['contenu'];
                            if ($mention['type'] === 'Publication' && !empty($mention['titre']) && (stripos($mention['titre'], $adminTag1) !== false || stripos($mention['titre'], $adminTag2) !== false)) {
                                $snippet = "<strong style='color:var(--text); font-size:1rem; display:block; margin-bottom:0.3rem;'>" . $mention['titre'] . "</strong>" . $snippet;
                            }
                            // Highlight both tags
                            $regex = '/(?<=^|\s)('.preg_quote($adminTag1, '/').'|'.preg_quote($adminTag2, '/').')(?=[^\w]|$)/i';
                            $highlighted = preg_replace($regex, '<strong style="color:#059669; background:#d1fae5; padding:0.15rem 0.4rem; border-radius:6px; box-shadow:0 1px 2px rgba(0,0,0,0.05);">$1</strong>', $e($snippet));
                            echo nl2br($highlighted);
                            ?>
                        </div>
                        
                        <!-- Actions -->
                        <div style="margin-top:1rem; display:flex; gap:0.5rem;">
                            <a href="dashboard.php?section=posts#post-row-<?= $mention['type'] === 'Publication' ? $mention['id'] : $mention['post_id'] ?>" class="btn-outline btn-sm" style="background:white; display:flex; align-items:center; gap:0.4rem; border-color:#d1d5db; color:#4b5563;" onmouseover="this.style.borderColor='var(--green-mid)'; this.style.color='var(--green-mid)';" onmouseout="this.style.borderColor='#d1d5db'; this.style.color='#4b5563';">
                                <i class="fas fa-list"></i> Voir la mention
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div> <!-- Fin de .main -->

<div class="toast" id="toast"></div>

<button id="back-to-top" class="back-to-top" title="Retour en haut">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Custom Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="modal-title">Confirmation</h2>
        <p class="modal-text">Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-cancel" id="modalCancel">Annuler</button>
            <button class="modal-btn modal-btn-confirm" id="modalConfirm">Supprimer</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="modal-box">
        <div class="modal-icon" style="background:#f0fdf4; color:#16a34a;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 class="modal-title">Succès</h2>
        <p class="modal-text" id="successModalText">L'opération a été effectuée avec succès.</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-confirm" style="background:#16a34a;" onclick="document.getElementById('successModal').classList.remove('show')">OK</button>
        </div>
    </div>
</div>


<script>
const chartLabels = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
const chartData = <?= json_encode($chartData) ?>;
const reactionChartData = <?= json_encode($reactionChartData) ?>;
const flashMessage = <?= json_encode($flashMessage, JSON_UNESCAPED_UNICODE) ?>;

function showToast(msg) {
    if (!msg) return;
    const t = document.getElementById('toast');
    if (t) {
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(t._to);
        t._to = setTimeout(() => t.classList.remove('show'), 3200);
    }
}

function showSuccessModal(msg) {
    const sm = document.getElementById('successModal');
    const smt = document.getElementById('successModalText');
    if (sm && smt) {
        smt.textContent = msg;
        sm.classList.add('show');
    }
}

document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('click', () => {
        document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active-section'));
        item.classList.add('active');
        const target = document.getElementById(item.dataset.section + '-section');
        if (target) {
            target.classList.add('active-section');
        }
    });
});

// The active section is now handled server-side via PHP for maximum stability.
// No need to trigger clicks via JS on page load anymore.

const confirmModal = document.getElementById('confirmModal');
const modalConfirmBtn = document.getElementById('modalConfirm');
const modalCancelBtn = document.getElementById('modalCancel');
let deleteUrl = '';

document.querySelectorAll('[data-confirm]').forEach(link => {
    link.addEventListener('click', event => {
        event.preventDefault();
        deleteUrl = link.href;
        confirmModal.classList.add('show');
    });
});

modalCancelBtn.addEventListener('click', () => {
    confirmModal.classList.remove('show');
    deleteUrl = '';
});

modalConfirmBtn.addEventListener('click', () => {
    if (deleteUrl) {
        window.location.href = deleteUrl;
    }
});

// Close modal when clicking outside the box
confirmModal.addEventListener('click', (e) => {
    if (e.target === confirmModal) {
        confirmModal.classList.remove('show');
        deleteUrl = '';
    }
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
            plugins: { 
                legend: { position: 'top' },
                title: { display: true, text: 'Répartition des publications' }
            },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
}

const ctx2 = document.getElementById('reactionChart')?.getContext('2d');
if (ctx2) {
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['J\'aime', 'Je n\'aime pas'],
            datasets: [{
                data: reactionChartData,
                backgroundColor: ['#10b981', '#ef4444'],
                hoverOffset: 12,
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 13, weight: '600', family: "'DM Sans', sans-serif" },
                        generateLabels: (chart) => {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const dataset = data.datasets[0];
                                const total = dataset.data.reduce((a, b) => a + b, 0);
                                return data.labels.map((label, i) => {
                                    const value = dataset.data[i];
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return {
                                        text: `${label} (${percentage}%)`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.backgroundColor[i],
                                        pointStyle: 'circle',
                                        hidden: !chart.isDatasetVisible(0) || chart.getDataVisibility(i) === false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    },
                    onClick: (e, legendItem, legend) => {
                        const index = legendItem.index;
                        legend.chart.toggleDataVisibility(index);
                        legend.chart.update();
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: (context) => {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return ` ${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                title: { 
                    display: true, 
                    text: 'Engagement (Likes vs Dislikes)',
                    font: { size: 16, weight: '700', family: "'Playfair Display', serif" },
                    padding: { bottom: 20 },
                    color: '#14532d'
                }
            },
            cutout: '70%'
        }
    });
}

if (flashMessage) {
    showSuccessModal(flashMessage);
}

// Global Search Logic
const globalSearch = document.getElementById('globalSearch');
if (globalSearch) {
    globalSearch.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        
        // Find all visible table rows in active sections
        const activeSection = document.querySelector('.section.active-section');
        if (!activeSection) return;

        const rows = activeSection.querySelectorAll('tbody tr');
        rows.forEach(row => {
            // Skip "no results" placeholder rows
            if (row.cells.length < 2) return;
            
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });

        // Specific handling for "Dernières publications" list on dashboard
        if (activeSection.id === 'dashboard-section') {
            const listItems = activeSection.querySelectorAll('.activity-list p');
            listItems.forEach(item => {
                item.style.display = item.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        }
    });
}

// Back to Top Logic
const backToTopBtn = document.getElementById('back-to-top');
if (backToTopBtn) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
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

<?php
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

$totalPrograms = count($coachingPrograms);
$totalExercises = count($exercises);
$difficultyCounts = ['easy' => 0, 'medium' => 0, 'hard' => 0];
$exerciseCountsByProgram = [];

foreach ($coachingPrograms as $program) {
    $difficulty = $program['difficulty_level'] ?? 'easy';
    if (isset($difficultyCounts[$difficulty])) {
        $difficultyCounts[$difficulty]++;
    }
    $exerciseCountsByProgram[(int)$program['id']] = 0;
}

foreach ($exercises as $exercise) {
    $coachingId = (int)$exercise['coaching_id'];
    if (isset($exerciseCountsByProgram[$coachingId])) {
        $exerciseCountsByProgram[$coachingId]++;
    }
}

$chartLabels = [];
$chartData = [];
foreach ($coachingPrograms as $program) {
    $chartLabels[] = $program['title'];
    $chartData[] = $exerciseCountsByProgram[(int)$program['id']] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NutriVert | Dashboard Admin</title>
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
    <div class="menu-item" data-section="coaching"><i class="fas fa-chalkboard-user"></i><span> Coaching</span></div>
    <div class="menu-item" data-section="exercises"><i class="fas fa-dumbbell"></i><span> Exercises</span></div>
</div>

<div class="main">
    <div class="topbar">
        <h1><i class="fas fa-seedling" style="color:var(--green-light)"></i> Tableau de bord Admin</h1>
        <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;">
            <a class="switch-link" href="index.php?controller=coaching&action=index">Back to User View</a>
            <div class="admin-badge"><i class="fas fa-shield-alt"></i> Admin NutriVert</div>
        </div>
    </div>

    <div id="dashboard-section" class="section active-section">
        <div class="stats-grid">
            <div class="stat-card"><i class="fas fa-chalkboard-user"></i><h3><?= $totalPrograms ?></h3><p>Programmes</p></div>
            <div class="stat-card"><i class="fas fa-dumbbell"></i><h3><?= $totalExercises ?></h3><p>Exercices</p></div>
            <div class="stat-card"><i class="fas fa-seedling"></i><h3><?= $difficultyCounts['easy'] ?></h3><p>Facile</p></div>
            <div class="stat-card"><i class="fas fa-layer-group"></i><h3><?= $difficultyCounts['medium'] ?></h3><p>Moyen</p></div>
            <div class="stat-card"><i class="fas fa-fire"></i><h3><?= $difficultyCounts['hard'] ?></h3><p>Difficile</p></div>
        </div>
        <div class="grid-2col">
            <div class="chart-container">
                <canvas id="activityChart" height="220"></canvas>
            </div>
            <div class="form-card">
                <h2><i class="fas fa-bell"></i> Activité récente</h2>
                <div class="activity-list">
                    <?php if ($coachingPrograms === [] && $exercises === []): ?>
                        <p><i class="fas fa-info-circle"></i> Aucune activité pour le moment.</p>
                    <?php endif; ?>
                    <?php foreach (array_slice($coachingPrograms, 0, 3) as $program): ?>
                        <p><i class="fas fa-chalkboard-user"></i> Programme ajouté : <?= $e($program['title']) ?></p>
                    <?php endforeach; ?>
                    <?php foreach (array_slice($exercises, 0, 3) as $exercise): ?>
                        <p><i class="fas fa-dumbbell"></i> Exercice ajouté : <?= $e($exercise['name']) ?> (<?= $e($exercise['coaching_title'] ?? '') ?>)</p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="coaching-section" class="section">
        <div class="form-card">
            <h2><i class="fas fa-chalkboard-user"></i> Gestion des programmes</h2>
            <p class="hint">Dashboard en lecture seule: utilisez les boutons pour ouvrir les pages CRUD séparées.</p>
            <div style="margin-bottom:1rem;">
                <a class="btn-primary" href="index.php?controller=dashboard&action=index&view=coaching_create">Add Coaching Program</a>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Titre</th><th>Durée</th><th>Difficulté</th><th>Créé le</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($coachingPrograms === []): ?>
                        <tr><td colspan="6">Aucun programme trouvé.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($coachingPrograms as $program): ?>
                        <tr>
                            <td><?= (int)$program['id'] ?></td>
                            <td><?= $e($program['title']) ?></td>
                            <td><?= (int)$program['duration_weeks'] ?> semaines</td>
                            <td><span class="badge"><?= $e($program['difficulty_level']) ?></span></td>
                            <td><?= $e($program['created_at']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-outline btn-sm" href="index.php?controller=dashboard&action=index&view=exercises&coaching_id=<?= (int)$program['id'] ?>">View Exercises</a>
                                    <a class="btn-outline btn-sm" href="index.php?controller=dashboard&action=index&view=coaching_edit&id=<?= (int)$program['id'] ?>">Edit</a>
                                    <a class="btn-outline btn-sm" data-confirm href="index.php?controller=coaching&action=delete&id=<?= (int)$program['id'] ?>&redirect=dashboard">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="exercises-section" class="section">
        <div class="form-card">
            <h2><i class="fas fa-dumbbell"></i> Aperçu des exercices</h2>
            <p class="hint">Utilisez "View Exercises" dans un programme pour ouvrir la liste filtrée par coaching.</p>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Programme</th><th>Nom</th><th>Sets/Reps</th><th>Rest</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($exercises === []): ?>
                        <tr><td colspan="6">Aucun exercice trouvé.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($exercises as $exercise): ?>
                        <tr>
                            <td><?= (int)$exercise['id'] ?></td>
                            <td><?= $e($exercise['coaching_title'] ?? '') ?></td>
                            <td><?= $e($exercise['name']) ?></td>
                            <td><?= (int)$exercise['sets'] ?>/<?= (int)$exercise['reps'] ?></td>
                            <td><?= $e($exercise['rest_time']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-outline btn-sm" href="index.php?controller=dashboard&action=index&view=exercises_edit&id=<?= (int)$exercise['id'] ?>">Edit</a>
                                    <a class="btn-outline btn-sm" data-confirm href="index.php?controller=exercise&action=delete&id=<?= (int)$exercise['id'] ?>&redirect=dashboard&coaching_id=<?= (int)$exercise['coaching_id'] ?>">Delete</a>
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
            labels: chartLabels.length ? chartLabels : ['Aucun programme'],
            datasets: [{
                label: 'Exercices par programme',
                data: chartData.length ? chartData : [0],
                borderColor: '#2e7d32',
                backgroundColor: 'rgba(76,175,80,0.25)',
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

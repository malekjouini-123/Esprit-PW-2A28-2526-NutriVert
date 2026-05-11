<?php
// =============================================================================
//  Vue BACK — Tableau de bord Admin
//  Variables injectées par DashboardController::index() :
//    $coachingPrograms : Coaching[]  (objets — on utilise les getters)
//    $exercises        : array[]     (tableaux bruts PDO)
//    $flashMessage     : ?string
// =============================================================================

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

$totalPrograms = count($coachingPrograms);
$totalExercises = count($exercises);

// Données pour le graphique ligne (programmes par mois)
$programsByMonth = [];
foreach ($coachingPrograms as $p) {
    $dateStr = $p->getCreatedAt() ?? date('Y-m-d H:i:s');
    $month   = date('M Y', strtotime($dateStr));
    $programsByMonth[$month] = ($programsByMonth[$month] ?? 0) + 1;
}
$timeLabels = array_keys($programsByMonth);
$timeData   = array_values($programsByMonth);

// Données pour le graphique barres (exercices par programme)
$exerciseCountsByProgram = [];
foreach ($coachingPrograms as $p) {
    $exerciseCountsByProgram[(int)$p->getId()] = 0;
}
foreach ($exercises as $ex) {
    $cid = (int)$ex['coaching_id'];
    if (isset($exerciseCountsByProgram[$cid])) {
        $exerciseCountsByProgram[$cid]++;
    }
}
$barLabels = [];
$barData   = [];
foreach ($coachingPrograms as $p) {
    $barLabels[] = mb_substr($p->getTitle(), 0, 14);
    $barData[]   = $exerciseCountsByProgram[(int)$p->getId()];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de Bord Admin — Nutrivert</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background: var(--bg-warm);">

<div class="d-flex">

  <!-- ===================== SIDEBAR ===================== -->
  <aside class="bg-white border-end shadow" style="width: 280px; min-height: 100vh;">

    <div class="sidebar-header" style="background:none; border-bottom:none; gap:12px; padding:25px;">
      <i class="fas fa-seedling" style="font-size:2.4rem; color:var(--coral); filter:drop-shadow(0 2px 5px rgba(255,126,103,0.3));"></i>
      <span style="font-size:1.8rem; font-weight:700; background:linear-gradient(135deg,#FF7E67,#7DCFB6); -webkit-background-clip:text; background-clip:text; color:transparent;">Nutrivert</span>
    </div>

    <div class="sidebar-menu">
      <a href="index.php?controller=dashboard&action=index" class="sidebar-link active" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-chart-pie"></i> Tableau de bord
      </a>
      <a href="index.php?controller=dashboard&action=index#table-programs" class="sidebar-link" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-folder-open"></i> Programmes
      </a>
      <a href="index.php?controller=user&action=adminUsers" class="sidebar-link" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-users"></i> Utilisateurs
      </a>
      <a href="index.php?controller=admin&action=faces" class="sidebar-link" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-camera"></i> Face ID
      </a>
      <a href="index.php?controller=chat&action=adminChats" class="sidebar-link" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-comments"></i> Chats IA
      </a>
    </div>

    <div class="sidebar-footer" style="border-top:none; padding:25px;">
      <a href="index.php?controller=coaching&action=index" class="btn btn-outline w-full" style="justify-content:center; border-radius:60px;">
        <i class="fas fa-sign-out-alt"></i> Retour au site
      </a>
    </div>

  </aside>

  <!-- ===================== MAIN ===================== -->
  <main class="flex-grow-1" style="background: var(--bg-warm);">
    <div class="container-fluid py-4">

      <!-- FLASH MESSAGE -->
      <?php if (!empty($flashMessage)): ?>
        <div class="flash-message" id="flash-auto">
          <i class="fas fa-check-circle" style="color: var(--c-primary)"></i>
          <?= $e($flashMessage) ?>
        </div>
      <?php endif; ?>

      <!-- TITRE -->
      <div style="margin-bottom:40px; text-align:center;">
        <h1 style="font-size:3rem; background:linear-gradient(120deg,var(--coral),var(--lavender),var(--mint));
                   background-size:200% auto; -webkit-background-clip:text; background-clip:text;
                   color:transparent; animation:shimmer 5s linear infinite; line-height:1.2;">
          Tableau de bord
        </h1>
        <p style="font-size:1.2rem; color:#6B7C68; margin-top:12px;">Bienvenue. Voici un aperçu de l'activité.</p>
      </div>

      <!-- STATISTIQUES -->
      <div class="grid grid-2" style="margin-bottom:20px;">

        <div class="section-card" style="margin:0; background:linear-gradient(135deg,var(--coral),var(--lavender)); color:white; border:none;">
          <div style="display:flex; align-items:center; gap:1.5rem;">
            <div style="width:64px; height:64px; background:rgba(255,255,255,0.2); border-radius:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fas fa-layer-group" style="font-size:1.8rem;"></i>
            </div>
            <div>
              <p style="font-size:0.85rem; font-weight:700; opacity:0.85; text-transform:uppercase; letter-spacing:0.1em;">Total Programmes</p>
              <p class="counter-value" data-target="<?= $totalPrograms ?>"
                 style="font-size:3rem; font-weight:800; line-height:1;">0</p>
            </div>
          </div>
        </div>

        <div class="section-card" style="margin:0; background:linear-gradient(135deg,var(--mint),#4DBFA5); color:white; border:none;">
          <div style="display:flex; align-items:center; gap:1.5rem;">
            <div style="width:64px; height:64px; background:rgba(255,255,255,0.2); border-radius:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fas fa-dumbbell" style="font-size:1.8rem;"></i>
            </div>
            <div>
              <p style="font-size:0.85rem; font-weight:700; opacity:0.85; text-transform:uppercase; letter-spacing:0.1em;">Total Exercices</p>
              <p class="counter-value" data-target="<?= $totalExercises ?>"
                 style="font-size:3rem; font-weight:800; line-height:1;">0</p>
            </div>
          </div>
        </div>

      </div>

      <!-- STATS SUPPLÉMENTAIRES -->
      <div class="grid grid-2" style="margin-bottom:40px;">

        <div class="section-card" style="margin:0; background:linear-gradient(135deg,#6c63ff,#a78bfa); color:white; border:none;">
          <div style="display:flex; align-items:center; gap:1.5rem;">
            <div style="width:64px; height:64px; background:rgba(255,255,255,0.2); border-radius:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fas fa-users" style="font-size:1.8rem;"></i>
            </div>
            <div>
              <p style="font-size:0.85rem; font-weight:700; opacity:0.85; text-transform:uppercase; letter-spacing:0.1em;">Utilisateurs</p>
              <p class="counter-value" data-target="<?= $totalUsers ?? 0 ?>"
                 style="font-size:3rem; font-weight:800; line-height:1;">0</p>
            </div>
          </div>
        </div>

        <div class="section-card" style="margin:0; background:linear-gradient(135deg,#f59e0b,#f97316); color:white; border:none;">
          <div style="display:flex; align-items:center; gap:1.5rem;">
            <div style="width:64px; height:64px; background:rgba(255,255,255,0.2); border-radius:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fas fa-comments" style="font-size:1.8rem;"></i>
            </div>
            <div>
              <p style="font-size:0.85rem; font-weight:700; opacity:0.85; text-transform:uppercase; letter-spacing:0.1em;">Chats IA</p>
              <p class="counter-value" data-target="<?= $totalChats ?? 0 ?>"
                 style="font-size:3rem; font-weight:800; line-height:1;">0</p>
              <p style="font-size:0.75rem; opacity:0.8;"><?= $totalFaces ?? 0 ?> Face ID enregistrés</p>
            </div>
          </div>
        </div>

      </div>

      <!-- GRAPHIQUES -->
      <div class="row g-4 mb-5">

        <div class="col-lg-6">
          <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
              <div class="section-title mb-3">
                <i class="fas fa-chart-line"></i> Programmes par mois
              </div>
              <div style="height:280px;"><canvas id="timeChart"></canvas></div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
              <div class="section-title mb-3">
                <i class="fas fa-chart-bar" style="color:var(--lavender);"></i> Exercices par programme
              </div>
              <div style="height:280px;"><canvas id="barChart"></canvas></div>
            </div>
          </div>
        </div>

      </div>

      <!-- TABLEAU DES PROGRAMMES -->
      <div class="card shadow-sm border-0 rounded-4" id="table-programs">
        <div class="card-body p-4">

          <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <div class="section-title mb-0">
              <i class="fas fa-folder-open"></i> Programmes Récents
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
              <input type="text" id="tableSearch" placeholder="Rechercher..."
                     class="form-control rounded-pill border-success">
              <select id="tableSort" class="form-select rounded-pill border-success">
                <option value="none">Trier par</option>
                <option value="title">Titre</option>
                <option value="duration">Durée</option>
                <option value="difficulty">Difficulté</option>
                <option value="date">Date</option>
              </select>
              <a href="index.php?controller=dashboard&action=index&view=coaching_create"
                 class="btn btn-success rounded-pill">
                <i class="fas fa-plus"></i> Nouveau Programme
              </a>
            </div>

          </div>

        <div class="table-responsive">
          <table id="programsTable" class="table table-hover">
            <thead class="table-light">
              <tr>
                <th class="rounded-start">Titre</th>
                <th>Durée</th>
                <th>Difficulté</th>
                <th>Créé le</th>
                <th class="text-end rounded-end">Actions</th>
              </tr>
            </thead>
            <tbody>

              <?php if (empty($coachingPrograms)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    Aucun programme enregistré.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($coachingPrograms as $p): ?>
                  <tr class="program-row"
                      data-title="<?= strtolower($e($p->getTitle())) ?>"
                      data-duration="<?= (int)$p->getDurationWeeks() ?>"
                      data-difficulty="<?= strtolower($e($p->getDifficultyLevel())) ?>"
                      data-date="<?= strtotime((string)$p->getCreatedAt()) ?>">

                    <td class="fw-bold"><?= $e($p->getTitle()) ?></td>
                    <td class="text-muted"><?= (int)$p->getDurationWeeks() ?> sem</td>
                    <td>
                      <span class="badge bg-light text-dark border">
                        <?= ucfirst($e($p->getDifficultyLevel())) ?>
                      </span>
                    </td>
                    <td class="text-muted small">
                      <?= date('d/m/Y', strtotime((string)$p->getCreatedAt())) ?>
                    </td>
                    <td class="text-end">
                      <div class="d-flex gap-2 justify-content-end">
                      <div class="d-flex gap-2 justify-content-end">
                        <a href="index.php?controller=dashboard&action=index&view=exercises&coaching_id=<?= (int)$p->getId() ?>"
                           class="btn btn-outline-success btn-sm rounded-pill">
                          Détails
                        </a>
                        <a href="index.php?controller=coaching&action=export&id=<?= (int)$p->getId() ?>"
                           class="btn btn-outline-primary btn-sm" title="Exporter PDF" target="_blank">
                          <i class="fas fa-download"></i>
                        </a>
                        <a href="index.php?controller=dashboard&action=index&view=coaching_edit&id=<?= (int)$p->getId() ?>"
                           class="btn btn-outline-warning btn-sm" title="Modifier">
                          <i class="fas fa-edit"></i>
                        </a>
                        <a href="index.php?controller=coaching&action=delete&id=<?= (int)$p->getId() ?>&redirect=dashboard"
                           data-confirm class="btn btn-outline-danger btn-sm" title="Supprimer">
                          <i class="fas fa-trash"></i>
                        </a>
                      </div>
                    </td>

                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>

            </tbody>
          </table>
        </div>

      </div>
      <!-- /table-programs -->

    </div>
  </main>

</div>

<!-- ===================== SCRIPTS ===================== -->
<script>
// --- Compteurs animés ---
document.querySelectorAll('.counter-value').forEach(el => {
    const target   = parseInt(el.dataset.target);
    const duration = 800;
    const start    = performance.now();
    function tick(now) {
        const progress = Math.min((now - start) / duration, 1);
        const ease     = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(ease * target);
        if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
});

// --- Recherche + tri du tableau ---
const searchInput = document.getElementById('tableSearch');
const sortSelect  = document.getElementById('tableSort');
const tbody       = document.querySelector('#programsTable tbody');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const val = this.value.toLowerCase();
        document.querySelectorAll('.program-row').forEach(row => {
            row.style.display = row.dataset.title.includes(val) ? '' : 'none';
        });
    });
}

if (sortSelect) {
    sortSelect.addEventListener('change', function () {
        const type = this.value;
        if (type === 'none') return;
        let rows = Array.from(document.querySelectorAll('.program-row'));
        rows.sort((a, b) => {
            if (type === 'title')      return a.dataset.title.localeCompare(b.dataset.title);
            if (type === 'duration')   return parseInt(b.dataset.duration)  - parseInt(a.dataset.duration);
            if (type === 'date')       return parseInt(b.dataset.date)      - parseInt(a.dataset.date);
            if (type === 'difficulty') {
                const val = x => x === 'easy' ? 1 : (x === 'medium' || x === 'moyen' ? 2 : 3);
                return val(a.dataset.difficulty) - val(b.dataset.difficulty);
            }
            return 0;
        });
        rows.forEach(r => tbody.appendChild(r));
    });
}

// --- Chart.js ---
Chart.defaults.font.family = "'Quicksand', sans-serif";
Chart.defaults.color = '#6B7C68';

// Graphique ligne
const ctxTime = document.getElementById('timeChart').getContext('2d');
const grad1   = ctxTime.createLinearGradient(0, 0, 0, 280);
grad1.addColorStop(0, 'rgba(255,126,103,0.3)');
grad1.addColorStop(1, 'rgba(255,126,103,0)');

new Chart(ctxTime, {
    type: 'line',
    data: {
        labels: <?= json_encode($timeLabels) ?>,
        datasets: [{
            label: 'Programmes',
            data: <?= json_encode($timeData) ?>,
            borderColor: '#FF7E67',
            backgroundColor: grad1,
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#FF7E67',
            pointBorderColor: '#fff',
            pointBorderWidth: 3,
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.03)' } },
            x: { grid: { display: false } }
        }
    }
});

// Graphique barres
const ctxBar = document.getElementById('barChart').getContext('2d');
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: <?= json_encode($barLabels) ?>,
        datasets: [{
            label: 'Exercices',
            data: <?= json_encode($barData) ?>,
            backgroundColor: 'rgba(200,162,240,0.5)',
            borderColor: '#C8A2F0',
            borderWidth: 2,
            borderRadius: 12,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.03)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<script src="assets/js/app.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

$totalPrograms = count($coachingPrograms);
$totalExercises = count($exercises);

$programsByMonth = [];
foreach ($coachingPrograms as $p) {
    $dateStr = $p['created_at'] ?? date('Y-m-d H:i:s');
    $month = date('M Y', strtotime($dateStr));
    $programsByMonth[$month] = ($programsByMonth[$month] ?? 0) + 1;
}
$timeLabels = array_keys($programsByMonth);
$timeData = array_values($programsByMonth);

$exerciseCountsByProgram = [];
foreach ($coachingPrograms as $p) { $exerciseCountsByProgram[(int)$p['id']] = 0; }
foreach ($exercises as $ex) { $cid = (int)$ex['coaching_id']; if (isset($exerciseCountsByProgram[$cid])) $exerciseCountsByProgram[$cid]++; }
$barLabels = []; $barData = [];
foreach ($coachingPrograms as $p) { $barLabels[] = mb_substr($p['title'], 0, 14); $barData[] = $exerciseCountsByProgram[(int)$p['id']]; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de Bord Admin - Nutrivert</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background: var(--bg-warm);">

<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar-rich" style="background: white; border-right: 1px solid var(--c-border); box-shadow: var(--shadow);">
    <div class="sidebar-header" style="background: none; border-bottom: none; gap: 12px; padding: 25px;">
      <i class="fas fa-seedling" style="font-size: 2.4rem; color: var(--coral); filter: drop-shadow(0 2px 5px rgba(255,126,103,0.3));"></i>
      <span style="font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #FF7E67, #7DCFB6); -webkit-background-clip: text; background-clip: text; color: transparent;">Nutrivert</span>
    </div>
    <div class="sidebar-menu">
      <a href="index.php?controller=dashboard&action=index" class="sidebar-link active" style="margin: 0 15px; border-radius: 60px;">
        <i class="fas fa-chart-pie"></i> Tableau de bord
      </a>
      <a href="index.php?controller=dashboard&action=index#table-programs" class="sidebar-link" style="margin: 0 15px; border-radius: 60px;">
        <i class="fas fa-folder-open"></i> Programmes
      </a>
    </div>
    <div class="sidebar-footer" style="border-top: none; padding: 25px;">
      <a href="index.php?controller=coaching&action=index" class="btn btn-outline w-full" style="justify-content:center; border-radius: 60px;">
        <i class="fas fa-sign-out-alt"></i> Retour au site
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="dashboard-main" style="background: var(--bg-warm);">
    <div class="container" style="padding-top: 40px;">
      <?php if (!empty($flashMessage)): ?>
        <div class="flash-message" id="flash-auto">
          <i class="fas fa-check-circle" style="color: var(--c-primary)"></i>
          <?= $e($flashMessage) ?>
        </div>
      <?php endif; ?>

      <div style="margin-bottom: 40px; text-align: center;">
        <h1 style="font-size: 3rem; background: linear-gradient(120deg, var(--coral), var(--lavender), var(--mint)); background-size: 200% auto; -webkit-background-clip: text; background-clip: text; color: transparent; animation: shimmer 5s linear infinite; line-height: 1.2;">Tableau de bord</h1>
        <p style="font-size: 1.2rem; color: #6B7C68; margin-top: 12px;">Bienvenue. Voici un aperçu de l'activité.</p>
      </div>

      <!-- SECTION 1: Vivid Stats -->
      <div class="grid grid-2" style="margin-bottom: 40px;">
        <div class="section-card" style="margin:0; background: linear-gradient(135deg, var(--coral), var(--lavender)); color: white; border: none;">
          <div style="display:flex; align-items:center; gap: 1.5rem;">
            <div style="width:64px; height:64px; background:rgba(255,255,255,0.2); border-radius:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
              <i class="fas fa-layer-group" style="font-size:1.8rem;"></i>
            </div>
            <div>
              <p style="font-size: 0.85rem; font-weight: 700; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.1em;">Total Programmes</p>
              <p class="counter-value" data-target="<?= $totalPrograms ?>" style="font-size: 3rem; font-weight: 800; line-height: 1;">0</p>
            </div>
          </div>
        </div>
        <div class="section-card" style="margin:0; background: linear-gradient(135deg, var(--mint), #4DBFA5); color: white; border: none;">
          <div style="display:flex; align-items:center; justify-content:space-between; gap: 1.5rem;">
            <div style="display:flex; align-items:center; gap: 1.5rem;">
               <div style="width:64px; height:64px; background:rgba(255,255,255,0.2); border-radius:16px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-dumbbell" style="font-size:1.8rem;"></i>
              </div>
              <div>
                <p style="font-size: 0.85rem; font-weight: 700; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.1em;">Total Exercices</p>
                <p class="counter-value" data-target="<?= $totalExercises ?>" style="font-size: 3rem; font-weight: 800; line-height: 1;">0</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SECTION 2: Charts -->
      <div class="grid grid-2" style="margin-bottom: 40px;">
        <div class="section-card" style="margin:0;">
          <div class="section-title">
            <i class="fas fa-chart-line"></i> Programmes par mois
          </div>
          <div style="height: 280px;"><canvas id="timeChart"></canvas></div>
        </div>
        <div class="section-card" style="margin:0;">
          <div class="section-title">
            <i class="fas fa-chart-bar" style="color:var(--lavender);"></i> Exercices par programme
          </div>
          <div style="height: 280px;"><canvas id="barChart"></canvas></div>
        </div>
      </div>

      <!-- SECTION 3: Programs Table -->
      <div class="section-card" id="table-programs" style="margin-bottom: 40px;">
        <div class="flex justify-between items-center" style="margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
          <div class="section-title" style="margin-bottom: 0;">
            <i class="fas fa-folder-open"></i> Programmes Récents
          </div>
          
          <div class="flex items-center gap-2" style="flex-wrap: wrap;">
            <input type="text" id="tableSearch" placeholder="Rechercher..." style="padding: 10px 15px; border-radius: 60px; border: 1px solid rgba(125,207,182,0.5); font-family: 'Quicksand', sans-serif;">
            <select id="tableSort" style="padding: 10px 15px; border-radius: 60px; border: 1px solid rgba(125,207,182,0.5); font-family: 'Quicksand', sans-serif; background: white;">
              <option value="none">Trier par</option>
              <option value="title">Titre</option>
              <option value="duration">Durée</option>
              <option value="difficulty">Difficulté</option>
              <option value="date">Date</option>
            </select>
            <a href="index.php?controller=dashboard&action=index&view=coaching_create" class="btn btn-gradient" style="white-space: nowrap;">
              <i class="fas fa-plus"></i> Nouveau Programme
            </a>
          </div>
        </div>
        <div style="overflow-x: auto;">
          <table id="programsTable" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead><tr>
              <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15); border-top-left-radius: 20px;">Titre</th>
              <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15);">Durée</th>
              <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15);">Difficulté</th>
              <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15);">Créé le</th>
              <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15); border-top-right-radius: 20px; text-align:right;">Actions</th>
            </tr></thead>
            <tbody>
              <?php if ($coachingPrograms === []): ?>
                <tr><td colspan="5" class="text-center text-muted" style="padding:3rem;">Aucun programme enregistré.</td></tr>
              <?php else: ?>
                <?php foreach ($coachingPrograms as $p): ?>
                  <tr class="program-row" style="border-bottom: 1px solid rgba(125,207,182,0.12); transition: background 0.2s;" data-title="<?= strtolower($e($p['title'])) ?>" data-duration="<?= (int)$p['duration_weeks'] ?>" data-difficulty="<?= strtolower($e($p['difficulty_level'] ?? 'medium')) ?>" data-date="<?= strtotime($p['created_at']) ?>">
                    <td style="padding: 1.3rem 1.5rem; font-weight:700;"><?= $e($p['title']) ?></td>
                    <td style="padding: 1.3rem 1.5rem; color: #6B7C68;"><?= (int)$p['duration_weeks'] ?> sem</td>
                    <td style="padding: 1.3rem 1.5rem;"><span class="info-badge" style="font-size: 0.8rem; padding: 4px 12px; margin: 0; box-shadow: none; border: 1px solid rgba(0,0,0,0.05); background: white; border-radius: 60px;"><?= ucfirst($e($p['difficulty_level'] ?? 'moyen')) ?></span></td>
                    <td style="padding: 1.3rem 1.5rem; color: #6B7C68; font-size:0.9rem;"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                    <td style="padding: 1.3rem 1.5rem; text-align:right;">
                      <div class="flex gap-2" style="justify-content:flex-end;">
                        <a href="index.php?controller=dashboard&action=index&view=exercises&coaching_id=<?= (int)$p['id'] ?>" class="btn-soft" style="padding:0.4rem 1rem; font-size:0.85rem; border-radius: 60px; text-decoration: none; border: 1.5px solid rgba(125,207,182,0.3); color: var(--deep); display: inline-block;">Détails</a>
                        <a href="index.php?controller=dashboard&action=index&view=coaching_edit&id=<?= (int)$p['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="index.php?controller=coaching&action=delete&id=<?= (int)$p['id'] ?>&redirect=dashboard" data-confirm class="btn-icon danger" title="Delete"><i class="fas fa-trash"></i></a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>


    </div>
  </main>
</div>

<script>
// Counter Animation
document.querySelectorAll('.counter-value').forEach(el => {
  const target = parseInt(el.dataset.target);
  const duration = 800;
  const start = performance.now();
  function tick(now) {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.floor(ease * target);
    if (progress < 1) requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);
});

// Table filtering and sorting
const searchInput = document.getElementById('tableSearch');
const sortSelect = document.getElementById('tableSort');
const tbody = document.querySelector('#programsTable tbody');

if (searchInput) {
  searchInput.addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.program-row').forEach(row => {
      row.style.display = row.dataset.title.includes(val) ? '' : 'none';
    });
  });
}

if (sortSelect) {
  sortSelect.addEventListener('change', function() {
    const type = this.value;
    if (type === 'none') return;
    
    let rows = Array.from(document.querySelectorAll('.program-row'));
    rows.sort((a, b) => {
      if (type === 'title') return a.dataset.title.localeCompare(b.dataset.title);
      if (type === 'duration') return parseInt(b.dataset.duration) - parseInt(a.dataset.duration);
      if (type === 'date') return parseInt(b.dataset.date) - parseInt(a.dataset.date);
      if (type === 'difficulty') {
        const val = x => x==='easy'?1 : (x==='medium'||x==='moyen'?2 : 3);
        return val(a.dataset.difficulty) - val(b.dataset.difficulty);
      }
      return 0;
    });
    
    rows.forEach(r => tbody.appendChild(r));
  });
}

// Charts
Chart.defaults.font.family = "'Quicksand', sans-serif";
Chart.defaults.color = '#6B7C68';

const ctxTime = document.getElementById('timeChart').getContext('2d');
const grad1 = ctxTime.createLinearGradient(0, 0, 0, 280);
grad1.addColorStop(0, 'rgba(255,126,103,0.3)');
grad1.addColorStop(1, 'rgba(255,126,103,0)');

new Chart(ctxTime, {
  type: 'line',
  data: {
    labels: <?= json_encode($timeLabels) ?>,
    datasets: [{
      label: 'Programs', data: <?= json_encode($timeData) ?>,
      borderColor: '#FF7E67', backgroundColor: grad1, borderWidth: 3,
      tension: 0.4, fill: true, pointBackgroundColor: '#FF7E67',
      pointBorderColor: '#fff', pointBorderWidth: 3, pointRadius: 5,
    }]
  },
  options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.03)' } }, x: { grid: { display: false } } } }
});

const ctxBar = document.getElementById('barChart').getContext('2d');
new Chart(ctxBar, {
  type: 'bar',
  data: {
    labels: <?= json_encode($barLabels) ?>,
    datasets: [{
      label: 'Exercises', data: <?= json_encode($barData) ?>,
      backgroundColor: 'rgba(200,162,240,0.5)', borderColor: '#C8A2F0',
      borderWidth: 2, borderRadius: 12, borderSkipped: false,
    }]
  },
  options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.03)' } }, x: { grid: { display: false } } } }
});
</script>
<script src="assets/js/app.js" defer></script>
</body>
</html>

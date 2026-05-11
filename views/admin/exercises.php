<?php
// =============================================================================
//  Vue BACK — Liste des exercices d'un programme (admin dashboard)
//  Variables injectées par DashboardController :
//    $exercises  : array[]  (tableaux bruts PDO)
//    $coachingId : int
//    $flashMessage : ?string
// =============================================================================

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$coachingId = (int)($coachingId ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Exercices du Programme #<?= $coachingId ?></title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body style="background: var(--bg-warm);">

<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar-rich" style="background:white; border-right:1px solid var(--c-border); box-shadow:var(--shadow);">
    <div class="sidebar-header" style="background:none; border-bottom:none; gap:12px; padding:25px;">
      <i class="fas fa-seedling" style="font-size:2.4rem; color:var(--coral); filter:drop-shadow(0 2px 5px rgba(255,126,103,0.3));"></i>
      <span style="font-size:1.8rem; font-weight:700; background:linear-gradient(135deg,#FF7E67,#7DCFB6); -webkit-background-clip:text; background-clip:text; color:transparent;">Nutrivert</span>
    </div>
    <div class="sidebar-menu">
      <a href="index.php?controller=dashboard&action=index" class="sidebar-link" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-chart-pie"></i> Dashboard
      </a>
      <a href="index.php?controller=dashboard&action=index#table-programs" class="sidebar-link" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-folder-open"></i> Programmes
      </a>
      <a href="#" class="sidebar-link active" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-dumbbell"></i> Exercices
      </a>
    </div>
    <div class="sidebar-footer" style="border-top:none; padding:25px;">
      <a href="index.php?controller=coaching&action=index" class="btn btn-outline w-full" style="justify-content:center; border-radius:60px;">
        <i class="fas fa-sign-out-alt"></i> Retour au site
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="dashboard-main" style="background: var(--bg-warm);">
    <div class="container" style="padding-top:40px;">

      <?php if (!empty($flashMessage)): ?>
        <div class="flash-message" id="flash-auto">
          <i class="fas fa-check-circle" style="color:var(--c-primary)"></i>
          <?= $e($flashMessage) ?>
        </div>
      <?php endif; ?>

      <div class="section-card" style="margin-bottom:40px;">

        <div class="section-title">
          <i class="fas fa-dumbbell"></i> Exercices — Programme #<?= $coachingId ?>
        </div>

        <div class="flex justify-between items-center" style="margin-bottom:2rem;">
          <h2 style="font-size:1.5rem; font-weight:700; color:var(--deep); letter-spacing:-0.02em;">
            Gérer les Exercices
          </h2>
          <div class="flex gap-2">
            <a class="btn btn-gradient"
               href="index.php?controller=dashboard&action=index&view=exercises_create&coaching_id=<?= $coachingId ?>"
               style="border-radius:60px;">
              <i class="fas fa-plus"></i> Ajouter un Exercice
            </a>
            <a class="btn btn-soft"
               href="index.php?controller=dashboard&action=index"
               style="border-radius:60px;">
              <i class="fas fa-arrow-left"></i> Retour
            </a>
          </div>
        </div>

        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
              <tr>
                <th style="background:#FEF7E8; padding:1.3rem 1.5rem; color:#6B7C68; font-weight:700; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.08em; border-bottom:2px dashed rgba(255,126,103,0.15); border-top-left-radius:20px;">ID</th>
                <th style="background:#FEF7E8; padding:1.3rem 1.5rem; color:#6B7C68; font-weight:700; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.08em; border-bottom:2px dashed rgba(255,126,103,0.15);">Photo</th>
                <th style="background:#FEF7E8; padding:1.3rem 1.5rem; color:#6B7C68; font-weight:700; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.08em; border-bottom:2px dashed rgba(255,126,103,0.15);">Nom</th>
                <th style="background:#FEF7E8; padding:1.3rem 1.5rem; color:#6B7C68; font-weight:700; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.08em; border-bottom:2px dashed rgba(255,126,103,0.15);">Séries/Reps</th>
                <th style="background:#FEF7E8; padding:1.3rem 1.5rem; color:#6B7C68; font-weight:700; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.08em; border-bottom:2px dashed rgba(255,126,103,0.15);">Repos</th>
                <th style="background:#FEF7E8; padding:1.3rem 1.5rem; color:#6B7C68; font-weight:700; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.08em; border-bottom:2px dashed rgba(255,126,103,0.15);">Vidéo</th>
                <th style="background:#FEF7E8; padding:1.3rem 1.5rem; color:#6B7C68; font-weight:700; text-transform:uppercase; font-size:0.75rem; letter-spacing:0.08em; border-bottom:2px dashed rgba(255,126,103,0.15); border-top-right-radius:20px; text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>

              <?php if (empty($exercises)): ?>
                <tr>
                  <td colspan="7" class="text-center text-muted" style="padding:3rem;">
                    Aucun exercice pour ce programme.
                  </td>
                </tr>
              <?php endif; ?>

              <?php foreach ($exercises as $exercise): ?>
                <tr style="border-bottom:1px solid rgba(125,207,182,0.12); transition:background 0.2s;"
                    onmouseover="this.style.background='#fafffe'" onmouseout="this.style.background=''">

                  <td style="padding:1.3rem 1.5rem; color:#6B7C68; font-family:monospace; opacity:0.7;">
                    #<?= (int)$exercise['id'] ?>
                  </td>

                  <td style="padding:1rem 1.5rem;">
                    <?php if (!empty($exercise['image'])): ?>
                      <img src="<?= $e($exercise['image']) ?>"
                           alt="<?= $e($exercise['name']) ?>"
                           style="width:60px; height:60px; object-fit:cover; border-radius:12px;
                                  border:2px solid rgba(255,126,103,0.2); box-shadow:0 2px 8px rgba(0,0,0,0.08);
                                  cursor:pointer; transition:transform 0.2s;"
                           onclick="this.style.transform = this.style.transform ? '' : 'scale(3) translateX(-40%)';"
                           title="Cliquer pour zoomer">
                    <?php else: ?>
                      <div style="width:60px; height:60px; border-radius:12px; background:#f5f5f5;
                                   border:2px dashed #ddd; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-image" style="color:#ccc; font-size:1.2rem;"></i>
                      </div>
                    <?php endif; ?>
                  </td>

                  <td style="padding:1.3rem 1.5rem; font-weight:700; color:var(--c-text);">
                    <?= $e($exercise['name']) ?>
                  </td>

                  <td style="padding:1.3rem 1.5rem;">
                    <strong class="info-badge"
                            style="background:white; border:1px solid rgba(0,0,0,0.05); border-radius:60px; padding:4px 12px;">
                      <?= (int)$exercise['sets'] ?>×<?= (int)$exercise['reps'] ?>
                    </strong>
                  </td>

                  <td style="padding:1.3rem 1.5rem; color:#6B7C68;">
                    <?= $e($exercise['rest_time']) ?>
                  </td>

                  <td style="padding:1.3rem 1.5rem;">
                    <?php if (!empty($exercise['video_url'])): ?>
                      <a href="<?= $e($exercise['video_url']) ?>" target="_blank" rel="noopener"
                         style="display:inline-flex; align-items:center; gap:5px; color:var(--coral);
                                text-decoration:none; font-size:0.85rem; font-weight:600;
                                background:rgba(255,126,103,0.1); padding:4px 10px; border-radius:60px;">
                        <i class="fas fa-play-circle"></i> Watch
                      </a>
                    <?php else: ?>
                      <span style="color:#ccc; font-size:0.8rem;">—</span>
                    <?php endif; ?>
                  </td>

                  <td style="padding:1.3rem 1.5rem; text-align:right;">
                    <div class="flex gap-2" style="justify-content:flex-end;">
                      <a href="index.php?controller=dashboard&action=index&view=exercises_edit&id=<?= (int)$exercise['id'] ?>&coaching_id=<?= $coachingId ?>"
                         class="btn-icon" title="Modifier">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a href="index.php?controller=exercise&action=delete&id=<?= (int)$exercise['id'] ?>&redirect=dashboard&coaching_id=<?= $coachingId ?>"
                         data-confirm class="btn-icon danger" title="Supprimer">
                        <i class="fas fa-trash"></i>
                      </a>
                    </div>
                  </td>

                </tr>
              <?php endforeach; ?>

            </tbody>
          </table>
        </div>

      </div>
    </div>
  </main>

</div>

<script src="assets/js/app.js" defer></script>

</body>
</html>
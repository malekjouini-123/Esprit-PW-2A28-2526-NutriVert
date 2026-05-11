<?php
// =============================================================================
//  Vue BACK — Éditer un exercice (admin dashboard)
//  Variables injectées par DashboardController :
//    $editingExercise : array  (tableau brut PDO)
//    $coachingId      : int
//    $flashMessage    : ?string
// =============================================================================

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$coachingId = (int)($coachingId ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Éditer l'Exercice</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
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
      <a href="index.php" class="btn btn-outline w-full" style="justify-content:center; border-radius:60px;">
        <i class="fas fa-sign-out-alt"></i> Retour au site
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="dashboard-main" style="background: var(--bg-warm);">
    <div class="container" style="padding-top:40px; padding-bottom:60px;">

      <?php if (!empty($flashMessage)): ?>
        <div class="flash-message" id="flash-auto">
          <i class="fas fa-exclamation-circle"></i>
          <?= $e($flashMessage) ?>
        </div>
      <?php endif; ?>

      <div class="section-card" style="max-width:650px; margin:0 auto;">

        <div class="section-title" style="justify-content:center; margin-bottom:2rem;">
          <i class="fas fa-edit" style="font-size:2.5rem;"></i>
          <span style="font-size:2rem;">Éditer l'Exercice</span>
        </div>

        <form method="post" enctype="multipart/form-data"
              action="index.php?controller=exercise&action=update&id=<?= (int)$editingExercise['id'] ?>&redirect=dashboard">

          <input type="hidden" name="coaching_id" value="<?= $coachingId ?>">

          <!-- Nom -->
          <div class="form-group">
            <label class="form-label">Nom de l'exercice</label>
            <input type="text" name="name" class="form-input"
                   value="<?= $e($editingExercise['name'] ?? '') ?>" required>
          </div>

          <!-- Instructions -->
          <div class="form-group">
            <label class="form-label">Instructions / Description</label>
            <textarea name="description" class="form-textarea" rows="3" required><?= $e($editingExercise['description'] ?? '') ?></textarea>
          </div>

          <!-- Séries + Reps -->
          <div class="grid grid-2" style="gap:1rem;">
            <div class="form-group">
              <label class="form-label">Séries (Sets)</label>
              <input type="number" name="sets" class="form-input" min="1"
                     value="<?= $e($editingExercise['sets'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Répétitions (Reps)</label>
              <input type="number" name="reps" class="form-input" min="1"
                     value="<?= $e($editingExercise['reps'] ?? '') ?>" required>
            </div>
          </div>

          <!-- Repos -->
          <div class="form-group">
            <label class="form-label">Temps de repos</label>
            <input type="text" name="rest_time" class="form-input"
                   value="<?= $e($editingExercise['rest_time'] ?? '') ?>" required>
          </div>

          <!-- Ordre + Durée -->
          <div class="grid grid-2" style="gap:1rem;">
            <div class="form-group">
              <label class="form-label">Ordre d'exécution ⭐</label>
              <input type="number" name="ordre" class="form-input" min="1"
                     value="<?= $e($editingExercise['ordre'] ?? 1) ?>" required
                     title="L'ordre de progression des exercices (1 = premier)">
              <small style="color: #888;">Important pour la progression</small>
            </div>
            <div class="form-group">
              <label class="form-label">Durée (secondes) ⏱️</label>
              <input type="number" name="duree_sec" class="form-input" min="5" step="5"
                     value="<?= $e($editingExercise['duree_sec'] ?? 30) ?>" required
                     title="Durée du timer pour cet exercice">
              <small style="color: #888;">30 sec par défaut</small>
            </div>
          </div>

          <!-- Image -->
          <div class="form-group">
            <label class="form-label">
              Image de Démonstration
              <span class="text-muted" style="font-size:0.8rem; font-weight:normal;">(laisser vide pour conserver l'actuelle)</span>
            </label>
            <input type="file" name="image" class="form-input" accept=".jpg,.jpeg,.png">
          </div>

          <!-- Image actuelle -->
          <?php if (!empty($editingExercise['image'])): ?>
            <div class="form-group">
              <label class="form-label text-muted" style="font-size:0.85rem;">Image actuelle</label>
              <img src="<?= $e($editingExercise['image']) ?>"
                   alt="<?= $e($editingExercise['name'] ?? '') ?>"
                   style="max-width:150px; border-radius:16px; border:2px solid var(--c-border); display:block; margin-top:0.5rem;">
            </div>
          <?php endif; ?>

          <!-- Vidéo -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-video" style="color:var(--coral); margin-right:6px;"></i>
              Vidéo de Démonstration
              <span class="text-muted" style="font-size:0.8rem; font-weight:normal;">(optionnel — URL YouTube / Vimeo)</span>
            </label>
            <input type="url" name="video_url" class="form-input"
                   placeholder="https://www.youtube.com/watch?v=..."
                   value="<?= $e($editingExercise['video_url'] ?? '') ?>">
            <?php if (!empty($editingExercise['video_url'])): ?>
              <a href="<?= $e($editingExercise['video_url']) ?>" target="_blank" rel="noopener"
                 style="display:inline-flex; align-items:center; gap:6px; margin-top:8px;
                        font-size:0.85rem; color:var(--coral); text-decoration:none; font-weight:600;">
                <i class="fas fa-external-link-alt"></i> Voir la vidéo actuelle
              </a>
            <?php endif; ?>
          </div>

          <div class="flex gap-4" style="margin-top:2rem;">
            <button type="submit" class="btn btn-gradient w-full" style="border-radius:60px;">
              <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
            <a href="index.php?controller=dashboard&action=index"
               class="btn btn-soft w-full text-center" style="border-radius:60px;">
              <i class="fas fa-times"></i> Annuler
            </a>
          </div>

        </form>

      </div>
    </div>
  </main>

</div>

<script src="assets/js/app.js" defer></script>
<script src="assets/js/validation.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>
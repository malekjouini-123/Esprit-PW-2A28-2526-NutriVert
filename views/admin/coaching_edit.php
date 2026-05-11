<?php
// =============================================================================
//  Vue BACK — Éditer un programme (admin dashboard)
//  Variables injectées par DashboardController :
//    $editingProgram : Coaching  (objet — on utilise les getters)
//    $flashMessage   : ?string
// =============================================================================

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$selectedDifficulty = $editingProgram->getDifficultyLevel();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Éditer le Programme</title>
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
      <a href="index.php?controller=dashboard&action=index#table-programs" class="sidebar-link active" style="margin:0 15px; border-radius:60px;">
        <i class="fas fa-folder-open"></i> Programmes
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
          <span style="font-size:2rem;">Éditer le Programme</span>
        </div>

        <form method="post"
              action="index.php?controller=coaching&action=update&id=<?= (int)$editingProgram->getId() ?>&redirect=dashboard">

          <div class="form-group">
            <label class="form-label">Titre du Programme</label>
            <input type="text" name="title" class="form-input"
                   value="<?= $e($editingProgram->getTitle()) ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-textarea" rows="4" required><?= $e($editingProgram->getDescription()) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Image (optionnel)</label>
            <input type="text" name="image" class="form-input"
                   value="<?= $e($editingProgram->getImage() ?? '') ?>">
            <small style="color: #888;">Chemin relatif ou URL</small>
          </div>

          <div class="grid grid-2" style="gap:1rem;">
            <div class="form-group">
              <label class="form-label">Durée (semaines)</label>
              <input type="number" name="duration_weeks" class="form-input"
                     min="1" max="52"
                     value="<?= $editingProgram->getDurationWeeks() ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Difficulté</label>
              <select name="difficulty_level" class="form-select" required>
                <option value="">Sélectionner</option>
                <option value="easy"   <?= $selectedDifficulty === 'easy'   ? 'selected' : '' ?>>🟢 Facile</option>
                <option value="medium" <?= $selectedDifficulty === 'medium' ? 'selected' : '' ?>>🟡 Moyen</option>
                <option value="hard"   <?= $selectedDifficulty === 'hard'   ? 'selected' : '' ?>>🔴 Difficile</option>
              </select>
            </div>
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
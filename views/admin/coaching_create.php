<?php
// =============================================================================
//  Vue BACK — Créer un programme (admin dashboard)
//  Variables injectées par DashboardController :
//    $flashMessage : ?string
// =============================================================================

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Créer un Programme</title>
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

      <div class="card shadow-sm border-0 rounded-4 mx-auto" style="max-width:650px;">

        <div class="card-body p-5">
          <div class="text-center mb-4">
            <i class="fas fa-pen-fancy text-success" style="font-size:2.5rem;"></i>
            <h2 class="mt-3">Créer un Programme</h2>
          </div>

          <form method="post" action="index.php?controller=coaching&action=store&redirect=dashboard">

            <div class="mb-3">
              <label class="form-label fw-bold">Titre du Programme</label>
              <input type="text" name="title" class="form-control rounded-pill"
                     value="<?= $e($_POST['title'] ?? '') ?>"
                     placeholder="Ex : Programme Bien-être 8 semaines"
                     required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Description</label>
              <textarea name="description" class="form-control" rows="4"
                        placeholder="Décrivez les objectifs et le contenu du programme..." required><?= $e($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Image (optionnel)</label>
              <input type="text" name="image" class="form-control rounded-pill"
                     value="<?= $e($_POST['image'] ?? '') ?>"
                     placeholder="Ex: uploads/coaching-image.jpg">
              <small class="text-muted">Chemin relatif ou URL</small>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Durée (semaines)</label>
                <input type="number" name="duration_weeks" class="form-control rounded-pill"
                       min="1" max="52"
                       value="<?= $e($_POST['duration_weeks'] ?? '') ?>"
                       placeholder="Ex : 8" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Difficulté</label>
                <select name="difficulty_level" class="form-select rounded-pill" required>
                  <option value="">Sélectionner</option>
                  <option value="easy"   <?= (($_POST['difficulty_level'] ?? '') === 'easy')   ? 'selected' : '' ?>>🟢 Facile</option>
                  <option value="medium" <?= (($_POST['difficulty_level'] ?? '') === 'medium') ? 'selected' : '' ?>>🟡 Moyen</option>
                <option value="hard"   <?= (($_POST['difficulty_level'] ?? '') === 'hard')   ? 'selected' : '' ?>>🔴 Difficile</option>
              </select>
            </div>
          </div>

          <div class="d-flex gap-3 mt-4">
            <button type="submit" class="btn btn-success rounded-pill flex-fill">
              <i class="fas fa-check"></i> Créer le Programme
            </button>
            <a href="index.php?controller=dashboard&action=index"
               class="btn btn-outline-secondary rounded-pill flex-fill text-center">
              <i class="fas fa-times"></i> Annuler
            </a>
          </div>

        </form>
        </div>
      </div>
    </div>
  </main>

</div>

<script src="assets/js/app.js" defer></script>
<script src="assets/js/validation.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>
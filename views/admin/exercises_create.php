<?php
// =============================================================================
//  Vue BACK — Créer un exercice (admin dashboard)
//  Variables injectées par DashboardController :
//    $coachingPrograms : array[]  (id, title)
//    $filterCoachingId : int
//    $flashMessage     : ?string
// =============================================================================

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$currentCoachingId = (int)($filterCoachingId ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Ajouter un Exercice</title>
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
          <i class="fas fa-dumbbell" style="font-size:2.5rem;"></i>
          <span style="font-size:2rem;">Ajouter un Exercice</span>
        </div>

        <form method="post" enctype="multipart/form-data"
              action="index.php?controller=exercise&action=store&redirect=dashboard">

          <!-- Programme associé -->
          <div class="form-group">
            <label class="form-label">Programme Associé</label>
            <select name="coaching_id" class="form-select" required>
              <option value="">Sélectionner un programme</option>
              <?php foreach ($coachingPrograms as $program): ?>
                <option value="<?= (int)$program['id'] ?>"
                  <?= $currentCoachingId === (int)$program['id'] ? 'selected' : '' ?>>
                  <?= $e($program['title']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Nom -->
          <div class="form-group">
            <label class="form-label">Nom de l'exercice</label>
            <input type="text" name="name" class="form-input"
                   placeholder="Ex : Squat, Pompes, Gainage..." required>
          </div>

          <!-- Instructions -->
          <div class="form-group">
            <label class="form-label">Instructions / Description</label>
            <textarea name="description" class="form-textarea" rows="3"
                      placeholder="Décrivez comment réaliser l'exercice..." required></textarea>
          </div>

          <!-- Séries + Reps -->
          <div class="grid grid-2" style="gap:1rem;">
            <div class="form-group">
              <label class="form-label">Séries (Sets)</label>
              <input type="number" name="sets" class="form-input" min="1" placeholder="Ex : 3" required>
            </div>
            <div class="form-group">
              <label class="form-label">Répétitions (Reps)</label>
              <input type="number" name="reps" class="form-input" min="1" placeholder="Ex : 12" required>
            </div>
          </div>

          <!-- Repos -->
          <div class="form-group">
            <label class="form-label">Temps de repos</label>
            <input type="text" name="rest_time" class="form-input"
                   placeholder="Ex : 60 sec" required>
          </div>

          <!-- Ordre + Durée -->
          <div class="grid grid-2" style="gap:1rem;">
            <div class="form-group">
              <label class="form-label">Ordre d'exécution ⭐</label>
              <input type="number" name="ordre" class="form-input" min="1" 
                     placeholder="Ex : 1, 2, 3..." required
                     title="L'ordre de progression des exercices (1 = premier)">
              <small style="color: #888;">Important pour la progression</small>
            </div>
            <div class="form-group">
              <label class="form-label">Durée (secondes) ⏱️</label>
              <input type="number" name="duree_sec" class="form-input" min="5" step="5"
                     placeholder="Ex : 30, 45, 60..." value="30" required
                     title="Durée du timer pour cet exercice">
              <small style="color: #888;">30 sec par défaut</small>
            </div>
          </div>

          <!-- Image -->
          <div class="form-group">
            <label class="form-label">
              Image de Démonstration
              <span class="text-muted" style="font-size:0.8rem; font-weight:normal;">(optionnel — JPG/PNG, max 2 Mo)</span>
            </label>
            <input type="file" name="image" class="form-input" accept=".jpg,.jpeg,.png">
          </div>

          <!-- Vidéo -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-video" style="color:var(--coral); margin-right:6px;"></i>
              Vidéo de Démonstration
              <span class="text-muted" style="font-size:0.8rem; font-weight:normal;">(optionnel — YouTube / Vimeo)</span>
            </label>
            <input type="url" name="video_url" class="form-input"
                   placeholder="https://www.youtube.com/watch?v=...">
          </div>

          <div class="flex gap-4" style="margin-top:2rem;">
            <button type="submit" class="btn btn-gradient w-full" style="border-radius:60px;">
              <i class="fas fa-check"></i> Créer l'exercice
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
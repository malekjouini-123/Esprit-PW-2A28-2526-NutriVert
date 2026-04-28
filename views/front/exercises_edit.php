<?php
// =============================================================================
//  Vue FRONT — Éditer un exercice
//  Variables injectées par ExerciseController::edit() :
//    $editingExercise  : array   (tableau brut PDO)
//    $coachingPrograms : array[] (id, title)
//    $coachingId       : int
//    $filterCoachingId : int
//    $redirectTarget   : string
//    $flashMessage     : ?string
// =============================================================================

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$coachingId = (int)($coachingId ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nutrivert — Éditer l'Exercice</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<nav class="navbar">
  <div class="logo">
    <i class="fas fa-seedling"></i>
    <span>Nutrivert</span>
  </div>
  <div class="nav-links">
    <a href="#" class="nav-link">Accueil</a>
    <a href="index.php?controller=coaching&action=index" class="nav-link active">Programmes</a>
    <a href="#" class="nav-link">Nutrition</a>
    <a href="#" class="nav-link">Communauté</a>
    <a href="#" class="nav-link">À propos</a>
  </div>
  <div class="nav-right">
    <a href="index.php?controller=dashboard&action=index" class="nav-hidden-btn" title="Admin">
      <i class="fas fa-user-shield"></i>
    </a>
  </div>
</nav>

<div class="container" style="padding-top: 40px; padding-bottom: 60px;">

  <?php if (!empty($flashMessage)): ?>
    <div class="flash-message" id="flash-auto">
      <i class="fas fa-exclamation-circle"></i>
      <?= e($flashMessage) ?>
    </div>
  <?php endif; ?>

  <div class="section-card" style="max-width: 650px; margin: 0 auto;">

    <div class="section-title" style="justify-content: center; margin-bottom: 2rem;">
      <i class="fas fa-dumbbell" style="font-size: 2.5rem; color: var(--mint);"></i>
      <span style="font-size: 2rem;">Éditer l'Exercice</span>
    </div>

    <form method="post" enctype="multipart/form-data"
          action="index.php?controller=exercise&action=update&id=<?= (int)$editingExercise['id'] ?>&redirect=<?= e($redirectTarget ?: 'exercises') ?>&coaching_id=<?= $coachingId ?>">

      <input type="hidden" name="coaching_id" value="<?= $coachingId ?>">

      <!-- Nom -->
      <div class="form-group">
        <label class="form-label">Nom de l'exercice</label>
        <input type="text" name="name" class="form-input"
               value="<?= e($editingExercise['name'] ?? '') ?>" required>
      </div>

      <!-- Instructions -->
      <div class="form-group">
        <label class="form-label">Instructions / Description</label>
        <textarea name="description" class="form-textarea" rows="3" required><?= e($editingExercise['description'] ?? '') ?></textarea>
      </div>

      <!-- Séries + Répétitions -->
      <div class="grid grid-2" style="gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Séries (Sets)</label>
          <input type="number" name="sets" class="form-input" min="1"
                 value="<?= e($editingExercise['sets'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Répétitions (Reps)</label>
          <input type="number" name="reps" class="form-input" min="1"
                 value="<?= e($editingExercise['reps'] ?? '') ?>" required>
        </div>
      </div>

      <!-- Temps de repos -->
      <div class="form-group">
        <label class="form-label">Temps de repos</label>
        <input type="text" name="rest_time" class="form-input"
               value="<?= e($editingExercise['rest_time'] ?? '') ?>" required>
      </div>

      <!-- URL Vidéo -->
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-video" style="color:var(--coral); margin-right:6px;"></i>
          URL de la Vidéo
          <span class="text-muted" style="font-size:0.8rem; font-weight:normal;">(optionnel)</span>
        </label>
        <div style="position: relative;">
          <i class="fas fa-video" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); pointer-events:none; color:var(--coral);"></i>
          <input type="url" name="video_url" class="form-input"
                 placeholder="https://youtube.com/watch?v=..."
                 style="padding-left: 2.8rem;"
                 value="<?= e($editingExercise['video_url'] ?? '') ?>">
        </div>
        <?php if (!empty($editingExercise['video_url'])): ?>
          <a href="<?= e($editingExercise['video_url']) ?>" target="_blank" rel="noopener"
             style="display:inline-flex; align-items:center; gap:6px; margin-top:8px;
                    font-size:0.85rem; color:var(--coral); text-decoration:none; font-weight:600;">
            <i class="fas fa-external-link-alt"></i> Voir la vidéo actuelle
          </a>
        <?php endif; ?>
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
          <img src="<?= e($editingExercise['image']) ?>"
               alt="<?= e($editingExercise['name'] ?? '') ?>"
               style="max-width:150px; border-radius:16px; border:2px solid var(--c-border); display:block; margin-top:0.5rem;">
        </div>
      <?php endif; ?>

      <!-- Boutons -->
      <div class="flex gap-4" style="margin-top: 2rem;">
        <button type="submit" class="btn btn-gradient w-full" style="border-radius: 60px;">
          <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
        <a href="index.php?controller=exercise&action=index<?= $coachingId > 0 ? '&coaching_id=' . $coachingId : '' ?>"
           class="btn btn-soft w-full text-center" style="border-radius: 60px;">
          <i class="fas fa-times"></i> Annuler
        </a>
      </div>

    </form>

  </div>
</div>

<footer>
  <i class="fas fa-leaf"></i> Nutrivert – Nutrition intelligente, durable &amp; solidaire | Marketplace d'ingrédients frais
</footer>

<script src="assets/js/app.js" defer></script>
<script src="assets/js/validation.js" defer></script>

</body>
</html>
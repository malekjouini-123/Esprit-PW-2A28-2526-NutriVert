<?php
// =============================================================================
//  Vue FRONT — Créer un exercice
//  Variables injectées par ExerciseController::renderCreate() :
//    $coachingPrograms : array[]  (id, title)
//    $filterCoachingId : int
//    $redirectTarget   : string
//    $flashMessage     : ?string
// =============================================================================

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$currentCoachingId = (int)($filterCoachingId ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nutrivert — Ajouter un Exercice</title>
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
      <span style="font-size: 2rem;">Ajouter un Exercice</span>
    </div>

    <form method="post" enctype="multipart/form-data"
          action="index.php?controller=exercise&action=store&redirect=<?= e($redirectTarget ?: 'exercises') ?>&coaching_id=<?= $currentCoachingId ?>">

      <!-- Programme associé -->
      <div class="form-group">
        <label class="form-label">Programme Associé</label>
        <select name="coaching_id" class="form-select" required>
          <option value="">Sélectionner un programme</option>
          <?php foreach ($coachingPrograms as $program): ?>
            <option value="<?= (int)$program['id'] ?>"
              <?= $currentCoachingId === (int)$program['id'] ? 'selected' : '' ?>>
              <?= e($program['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Nom -->
      <div class="form-group">
        <label class="form-label">Nom de l'exercice</label>
        <input type="text" name="name" class="form-input"
               placeholder="Ex : Squat, Pompes, Gainage..."
               required>
      </div>

      <!-- Instructions -->
      <div class="form-group">
        <label class="form-label">Instructions / Description</label>
        <textarea name="description" class="form-textarea" rows="3"
                  placeholder="Décrivez comment réaliser l'exercice correctement..." required></textarea>
      </div>

      <!-- Séries + Répétitions -->
      <div class="grid grid-2" style="gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Séries (Sets)</label>
          <input type="number" name="sets" class="form-input" min="1" placeholder="Ex : 3" required>
        </div>
        <div class="form-group">
          <label class="form-label">Répétitions (Reps)</label>
          <input type="number" name="reps" class="form-input" min="1" placeholder="Ex : 12" required>
        </div>
      </div>

      <!-- Temps de repos -->
      <div class="form-group">
        <label class="form-label">Temps de repos</label>
        <input type="text" name="rest_time" class="form-input"
               placeholder="Ex : 60 sec, 1 min 30..." required>
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
                 style="padding-left: 2.8rem;">
        </div>
      </div>

      <!-- Image -->
      <div class="form-group">
        <label class="form-label">
          Image de Démonstration
          <span class="text-muted" style="font-size:0.8rem; font-weight:normal;">(optionnel — JPG ou PNG, max 2 Mo)</span>
        </label>
        <input type="file" name="image" class="form-input" accept=".jpg,.jpeg,.png">
      </div>

      <!-- Boutons -->
      <div class="flex gap-4" style="margin-top: 2rem;">
        <button type="submit" class="btn btn-gradient w-full" style="border-radius: 60px;">
          <i class="fas fa-check"></i> Créer l'exercice
        </button>
        <a href="index.php?controller=exercise&action=index<?= $currentCoachingId > 0 ? '&coaching_id=' . $currentCoachingId : '' ?>"
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
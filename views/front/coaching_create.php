<?php
// =============================================================================
//  Vue FRONT — Créer un programme de coaching
//  Variables injectées par CoachingController::renderCreate() :
//    $flashMessage : ?string
// =============================================================================

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nutrivert — Créer un Programme</title>
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
      <i class="fas fa-pen-fancy" style="font-size: 2.5rem; color: var(--coral);"></i>
      <span style="font-size: 2rem;">Créer un Programme</span>
    </div>

    <form method="post" action="index.php?controller=coaching&action=store&redirect=coaching">

      <!-- Titre -->
      <div class="form-group">
        <label class="form-label">Titre du Programme</label>
        <input type="text" name="title" class="form-input"
               value="<?= e($_POST['title'] ?? '') ?>"
               placeholder="Ex : Programme Bien-être 8 semaines"
               required>
      </div>

      <!-- Description -->
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-textarea" rows="4"
                  placeholder="Décrivez les objectifs et le contenu du programme..." required><?= e($_POST['description'] ?? '') ?></textarea>
      </div>

      <!-- Durée + Difficulté -->
      <div class="grid grid-2" style="gap: 1rem;">

        <div class="form-group">
          <label class="form-label">Durée (semaines)</label>
          <input type="number" name="duration_weeks" class="form-input"
                 min="1" max="52"
                 value="<?= e($_POST['duration_weeks'] ?? '') ?>"
                 placeholder="Ex : 8"
                 required>
        </div>

        <div class="form-group">
          <label class="form-label">Difficulté</label>
          <select name="difficulty_level" class="form-select" required>
            <option value="">Sélectionner</option>
            <option value="easy"   <?= (($_POST['difficulty_level'] ?? '') === 'easy')   ? 'selected' : '' ?>>🟢 Facile</option>
            <option value="medium" <?= (($_POST['difficulty_level'] ?? '') === 'medium') ? 'selected' : '' ?>>🟡 Moyen</option>
            <option value="hard"   <?= (($_POST['difficulty_level'] ?? '') === 'hard')   ? 'selected' : '' ?>>🔴 Difficile</option>
          </select>
        </div>

      </div>

      <!-- Boutons -->
      <div class="flex gap-4" style="margin-top: 2rem;">
        <button type="submit" class="btn btn-gradient w-full" style="border-radius: 60px;">
          <i class="fas fa-check"></i> Créer le Programme
        </button>
        <a href="index.php?controller=coaching&action=index"
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
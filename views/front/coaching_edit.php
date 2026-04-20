<?php
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$selectedDifficulty = $editingProgram['difficulty_level'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nutrivert - Edit Program</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<nav class="navbar">
  <div class="logo">
    <i class="fas fa-seedling"></i>
    <i class="fas fa-brain"></i>
    <span>Nutrivert</span>
  </div>
  <div class="nav-links">
    <a href="#">Home</a>
    <a href="index.php?controller=coaching&action=index" class="active">Programs</a>
    <a href="#">Nutrition</a>
    <a href="#">Community</a>
    <a href="#">About</a>
  </div>
  <div class="nav-right" style="margin-left: 20px;">
    <a href="index.php?controller=dashboard&action=index" class="nav-hidden-btn" title="Admin">
      <i class="fas fa-user-shield"></i>
    </a>
  </div>
</nav>
<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
  <div class="section-card" style="max-width: 650px; margin: 0 auto;">
    <div class="section-title" style="justify-content: center; margin-bottom: 2rem;">
        <i class="fas fa-edit" style="font-size: 2.5rem;"></i>
        <span style="font-size: 2rem;">Éditer le Programme</span>
    </div>
    
    <form method="post" action="index.php?controller=coaching&action=update&id=<?= (int)$editingProgram['id'] ?>&redirect=coaching">
      <div class="form-group"><label class="form-label">Titre du Programme</label><input type="text" name="title" class="form-input" required value="<?= $e($editingProgram['title'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" required rows="4"><?= $e($editingProgram['description'] ?? '') ?></textarea></div>
      <div class="grid grid-2" style="gap:1rem;">
        <div class="form-group"><label class="form-label">Durée (semaines)</label><input type="number" name="duration_weeks" class="form-input" min="1" required value="<?= $e($editingProgram['duration_weeks'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label">Difficulté</label><select name="difficulty_level" class="form-select" required><option value="">Sélectionner</option><option value="easy" <?= $selectedDifficulty === 'easy' ? 'selected' : '' ?>>Facile</option><option value="medium" <?= $selectedDifficulty === 'medium' ? 'selected' : '' ?>>Moyen</option><option value="hard" <?= $selectedDifficulty === 'hard' ? 'selected' : '' ?>>Difficile</option></select></div>
      </div>
      <div class="flex gap-4" style="margin-top: 2rem;"><button type="submit" class="btn btn-gradient w-full" style="border-radius: 60px;">Enregistrer les modifications</button><a href="index.php?controller=coaching&action=index" class="btn btn-soft w-full text-center" style="border-radius: 60px;">Annuler</a></div>
    </form>
  </div>
</div>
<footer>
  <i class="fas fa-leaf"></i> Nutrivert – Nutrition intelligente, durable & solidaire | Marketplace d'ingrédients frais
</footer>
<script src="assets/js/app.js" defer></script>
<script src="assets/js/validation.js" defer></script>
</body>
</html>

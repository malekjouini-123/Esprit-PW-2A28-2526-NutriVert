<?php
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nutrivert - Coaching Programs</title>
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
    <a href="#" class="nav-link">Home</a>
    <a href="index.php?controller=coaching&action=index" class="nav-link active">Programs</a>
    <a href="#" class="nav-link">Nutrition</a>
    <a href="#" class="nav-link">Community</a>
    <a href="#" class="nav-link">About</a>
  </div>
  <div class="nav-right" style="margin-left: 20px;">
    <a href="index.php?controller=dashboard&action=index" class="nav-hidden-btn" title="Admin">
      <i class="fas fa-user-shield"></i>
    </a>
  </div>
  <div class="logo">
  <img src="assets/logo web.png" alt="Nutrivert Logo" style="height:40px; width:auto; margin-right:10px;">
  <span>Nutrivert</span>
</div>
</nav>

<div class="hero">
  <h1>✨ Manger malin, zéro gaspillage ✨</h1>
  <p>Nutrivert — intelligence nutritionnelle & marketplace durable</p>
</div>

<div class="container">
  <?php if (!empty($flashMessage)): ?>
    <div class="flash-message" id="flash-auto">
      <i class="fas fa-check-circle" style="color: var(--c-primary)"></i>
      <?= $e($flashMessage) ?>
    </div>
  <?php endif; ?>

  <div id="programs-section" class="section-card">
    <div class="section-title">
        <i class="fas fa-chalkboard-user"></i>
        <span>🎯 Coaching personnalisé — choisis ton objectif</span>
    </div>
    <div class="flex justify-between items-center mb-8" style="background: white; padding: 2rem; border-radius: 28px; box-shadow: var(--shadow-sm); border: 1.5px solid var(--c-border);">
      <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--deep); letter-spacing: -0.02em;">Vos Programmes</h2>
        <p class="text-muted" style="margin-top: 0.25rem;">Gérez vos programmes de nutrition et d'entraînement.</p>
      </div>
      <a href="index.php?controller=coaching&action=create" class="btn btn-gradient">
        <i class="fas fa-plus"></i>
        Créer un Programme
      </a>
    </div>

    <!-- Search & Sort Bar -->
    <form method="GET" action="index.php" class="flex gap-4 mb-8 items-center" style="background: #FEF7E8; padding: 1.5rem; border-radius: 28px; border: 1.5px dashed rgba(255,126,103,0.3);">
      <input type="hidden" name="controller" value="coaching">
      <input type="hidden" name="action" value="index">
      
      <div style="flex: 2; position: relative;">
        <i class="fas fa-search" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--coral);"></i>
        <input type="text" name="search" placeholder="Rechercher (titre ou description)..." 
               value="<?= $e($_GET['search'] ?? '') ?>" 
               class="form-input" style="padding-left: 50px; background: white; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
      </div>

      <div style="flex: 1;">
        <select name="sort" class="form-select" style="background: white; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03);" onchange="this.form.submit()">
          <option value="">Trier par défaut</option>
          <option value="title_asc" <?= ($_GET['sort'] ?? '') === 'title_asc' ? 'selected' : '' ?>>Titre (A-Z)</option>
          <option value="duration_weeks_asc" <?= ($_GET['sort'] ?? '') === 'duration_weeks_asc' ? 'selected' : '' ?>>Durée croissante</option>
          <option value="duration_weeks_desc" <?= ($_GET['sort'] ?? '') === 'duration_weeks_desc' ? 'selected' : '' ?>>Durée décroissante</option>
        </select>
      </div>
      
      <button type="submit" class="btn btn-gradient" style="box-shadow: none; padding: 12px 20px;"><i class="fas fa-filter"></i></button>
      <?php if (!empty($_GET['search']) || !empty($_GET['sort'])): ?>
        <a href="index.php?controller=coaching&action=index" class="btn btn-soft" style="box-shadow: none; padding: 12px 20px;" title="Reset"><i class="fas fa-times"></i></a>
      <?php endif; ?>
    </form>

    <?php if ($coachingPrograms === []): ?>
      <div class="coach-wrapper text-center text-muted" style="padding: 6rem 2rem; border: 2px dashed rgba(125,207,182,0.3); border-radius: 28px; display: block;">
        <i class="fas fa-folder-open mb-8" style="font-size: 3rem; color: var(--mint); opacity: 0.5;"></i>
        <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--c-text);">Aucun programme disponible</h3>
        <p style="margin-top: 0.5rem;">Votre bibliothèque est vide. Commençons par créer votre premier programme.</p>
      </div>
    <?php else: ?>
      <div class="coach-wrapper">
      <?php
        $faIcons = ['fa-heart-pulse', 'fa-bolt', 'fa-dumbbell', 'fa-leaf', 'fa-carrot', 'fa-fire'];
      ?>
      <?php foreach ($coachingPrograms as $i => $program): ?>
        <?php
          $programExercises = $exercisesByCoaching[(int)$program['id']] ?? [];
          $dif = strtolower($program['difficulty_level'] ?? '');
          if ($dif === 'hard') $badgeHtml = '<span class="badge-rich" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2);">Difficile</span>';
          elseif ($dif === 'medium') $badgeHtml = '<span class="badge-rich" style="background:rgba(245,158,11,0.1); color:#f59e0b; border:1px solid rgba(245,158,11,0.2);">Moyen</span>';
          else $badgeHtml = '<span class="badge-rich" style="background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.2);">Facile</span>';
        ?>
        <div class="coach-option" style="flex: 1 1 calc(33.333% - 24px); min-width: 300px; text-align: left; position: relative;">
          <div class="flex justify-between items-center" style="margin-bottom: 1.5rem;">
            <i class="fas <?= $faIcons[$i % count($faIcons)] ?>" style="font-size: 2rem; color: var(--coral);"></i>
          </div>

          <h3 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--c-text);"><?= $e($program['title']) ?></h3>
          <p class="text-muted" style="font-size: 0.95rem; margin-bottom: 1rem; min-height: 40px;"><?= nl2br($e($program['description'])) ?></p>

          <div class="flex gap-2" style="margin-bottom: 1.5rem;">
            <span class="info-badge" style="font-size: 0.8rem; padding: 4px 12px; margin: 0; box-shadow: none; border: 1px solid rgba(0,0,0,0.05);"><?= (int)$program['duration_weeks'] ?> sem</span>
            <?= $badgeHtml ?>
          </div>

          <div style="margin-bottom: 1.5rem;">
            <a href="index.php?controller=exercise&action=index&coaching_id=<?= (int)$program['id'] ?>" class="btn btn-soft w-full" style="justify-content: center; gap: 0.6rem; border-radius: 40px;">
              <i class="fas fa-list-check"></i>
              Voir les exercices
              <span class="info-badge" style="margin-left: auto; font-size: 0.7rem; padding: 2px 8px; box-shadow:none; border:1px solid #eee;"><?= count($programExercises) ?></span>
            </a>
          </div>

          <div class="flex gap-2 mt-auto" style="padding-top: 1.5rem; border-top: 2px dashed rgba(255,126,103,0.15);">
            <a href="index.php?controller=coaching&action=export&id=<?= (int)$program['id'] ?>" class="btn-primary w-full" style="text-align: center; text-decoration: none; padding: 10px 15px; display: inline-flex; justify-content: center; align-items: center; gap: 6px;">
              <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="index.php?controller=coaching&action=edit&id=<?= (int)$program['id'] ?>" class="btn-icon" title="Edit Program" style="background: white; border: 1px solid var(--c-border); flex-shrink:0;">
              <i class="fas fa-edit"></i>
            </a>
            <a href="index.php?controller=coaching&action=delete&id=<?= (int)$program['id'] ?>" data-confirm class="btn-icon danger" title="Delete Program" style="flex-shrink:0;">
              <i class="fas fa-trash"></i>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<footer>
  <i class="fas fa-leaf"></i> Nutrivert – Nutrition intelligente, durable & solidaire | Marketplace d'ingrédients frais
</footer>

<script src="assets/js/app.js" defer></script>
</body>
</html>

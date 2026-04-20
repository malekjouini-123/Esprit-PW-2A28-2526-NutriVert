<?php
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$currentCoachingId = (int)($filterCoachingId ?? 0);

// Find the current program name
$currentProgram = null;
foreach ($coachingPrograms as $cp) {
    if ((int)$cp['id'] === $currentCoachingId) {
        $currentProgram = $cp;
        break;
    }
}
$programTitle = $currentProgram ? $currentProgram['title'] : 'All Programs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nutrivert - <?= $e($programTitle) ?> Exercises</title>
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

<div class="hero">
  <h1>✨ Exercises - <?= $e($programTitle) ?> ✨</h1>
</div>

<div class="container" style="padding-top: 20px;">
  <?php if (!empty($flashMessage)): ?>
    <div class="flash-message" id="flash-auto">
      <i class="fas fa-check-circle" style="color: var(--c-primary)"></i>
      <?= $e($flashMessage) ?>
    </div>
  <?php endif; ?>

  <div class="section-card">
    <div class="section-title">
        <i class="fas fa-dumbbell"></i>
        <span>🏋️ Liste des exercices</span>
    </div>
    
    <div class="flex justify-between items-center mb-8" style="background: white; padding: 2rem; border-radius: 28px; box-shadow: var(--shadow-sm); border: 1.5px solid var(--c-border);">
      <div>
        <?php if ($currentProgram): ?>
          <p style="font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--coral); margin-bottom:0.4rem;"><?= $e($programTitle) ?></p>
        <?php endif; ?>
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--deep); letter-spacing: -0.02em;">Gérer les Exercices</h2>
        <p class="text-muted" style="margin-top: 0.25rem;"><?= count($exercises) ?> exercice<?= count($exercises) !== 1 ? 's' : '' ?> dans ce programme</p>
      </div>
      <div class="flex gap-2">
        <a class="btn btn-gradient" href="index.php?controller=exercise&action=create&coaching_id=<?= $currentCoachingId ?>&redirect=exercises">
          <i class="fas fa-plus"></i> Ajouter un Exercice
        </a>
        <a class="btn btn-soft" href="index.php?controller=coaching&action=index" style="border-radius: 60px;">
          <i class="fas fa-arrow-left"></i> Retour
        </a>
      </div>
    </div>

    <!-- Search & Sort Bar -->
    <form method="GET" action="index.php" class="flex gap-4 mb-8 items-center" style="background: #FEF7E8; padding: 1.5rem; border-radius: 28px; border: 1.5px dashed rgba(255,126,103,0.3);">
      <input type="hidden" name="controller" value="exercise">
      <input type="hidden" name="action" value="index">
      <?php if ($currentCoachingId > 0): ?>
        <input type="hidden" name="coaching_id" value="<?= $currentCoachingId ?>">
      <?php endif; ?>
      
      <div style="flex: 2; position: relative;">
        <i class="fas fa-search" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--coral);"></i>
        <input type="text" name="search" placeholder="Rechercher par nom..." 
               value="<?= $e($_GET['search'] ?? '') ?>" 
               class="form-input" style="padding-left: 50px; background: white; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
      </div>

      <div style="flex: 1;">
        <select name="sort" class="form-select" style="background: white; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03);" onchange="this.form.submit()">
          <option value="">Trier par défaut</option>
          <option value="name_asc" <?= ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Nom (A-Z)</option>
          <option value="sets_asc" <?= ($_GET['sort'] ?? '') === 'sets_asc' ? 'selected' : '' ?>>Séries croissantes</option>
          <option value="reps_desc" <?= ($_GET['sort'] ?? '') === 'reps_desc' ? 'selected' : '' ?>>Répétitions décroissantes</option>
        </select>
      </div>
      
      <button type="submit" class="btn btn-gradient" style="box-shadow: none; padding: 12px 20px;"><i class="fas fa-filter"></i></button>
      <?php if (!empty($_GET['search']) || !empty($_GET['sort'])): ?>
        <a href="index.php?controller=exercise&action=index<?= $currentCoachingId > 0 ? '&coaching_id='.$currentCoachingId : '' ?>" class="btn btn-soft" style="box-shadow: none; padding: 12px 20px;" title="Reset"><i class="fas fa-times"></i></a>
      <?php endif; ?>
    </form>

    <div style="overflow-x: auto;">
    <table>
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead><tr>
          <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15); border-top-left-radius: 20px;">Photo</th>
          <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15);">Nom</th>
          <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15);">Séries / Rép</th>
          <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15);">Repos</th>
          <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15);">Vidéo</th>
          <th style="background: #FEF7E8; padding: 1.3rem 1.5rem; color: #6B7C68; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; border-bottom: 2px dashed rgba(255,126,103,0.15); border-top-right-radius: 20px; text-align:right;">Actions</th>
        </tr></thead>
        <tbody>
          <?php if ($exercises === []): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:3rem;">Aucun exercice pour ce programme.</td></tr>
          <?php endif; ?>
          <?php foreach ($exercises as $exercise): ?>
            <tr style="border-bottom: 1px solid rgba(125,207,182,0.12); transition: background 0.2s;">
              <td style="padding: 1rem 1.5rem;">
                <?php if (!empty($exercise['image'])): ?>
                  <img src="<?= $e($exercise['image']) ?>" alt="<?= $e($exercise['name']) ?>" style="width:60px; height:60px; object-fit:cover; border-radius:12px; border:2px solid rgba(255,126,103,0.2); box-shadow:0 2px 8px rgba(0,0,0,0.08); cursor:pointer; transition:transform 0.2s;" onclick="this.style.transform=this.style.transform?'':'scale(3) translateX(-40%)';" title="Cliquer pour zoomer">
                <?php else: ?>
                  <div style="width:60px; height:60px; border-radius:12px; background:#f5f5f5; border:2px dashed #ddd; display:flex; align-items:center; justify-content:center;"><i class="fas fa-image" style="color:#ccc; font-size:1.2rem;"></i></div>
                <?php endif; ?>
              </td>
              <td style="padding: 1.3rem 1.5rem; font-weight:700; color:var(--c-text);"><?= $e($exercise['name']) ?></td>
              <td style="padding: 1.3rem 1.5rem;"><strong class="info-badge" style="background: white; border: 1px solid rgba(0,0,0,0.05); border-radius: 60px; padding: 4px 12px;"><?= (int)$exercise['sets'] ?>x<?= (int)$exercise['reps'] ?></strong></td>
              <td style="padding: 1.3rem 1.5rem; color: #6B7C68;"><?= $e($exercise['rest_time']) ?></td>
              <td style="padding: 1.3rem 1.5rem;">
                <?php if (!empty($exercise['video_url'])): ?>
                  <a href="<?= $e($exercise['video_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-icon" title="Watch Video" style="color: var(--coral); background: white; border: 1px solid rgba(255,126,103,0.2);">
                    <i class="fas fa-video"></i>
                  </a>
                <?php else: ?>
                  <span class="text-muted" style="font-size:0.85rem;">—</span>
                <?php endif; ?>
              </td>
              <td style="padding: 1.3rem 1.5rem; text-align:right;"><div class="flex gap-2" style="justify-content:flex-end;">
                <a href="index.php?controller=exercise&action=edit&id=<?= (int)$exercise['id'] ?>&coaching_id=<?= $currentCoachingId ?>&redirect=exercises" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="index.php?controller=exercise&action=delete&id=<?= (int)$exercise['id'] ?>&redirect=exercises&coaching_id=<?= $currentCoachingId ?>" data-confirm class="btn-icon danger" title="Delete"><i class="fas fa-trash"></i></a>
              </div></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<footer>
  <i class="fas fa-leaf"></i> Nutrivert – Nutrition intelligente, durable & solidaire | Marketplace d'ingrédients frais
</footer>
<script src="assets/js/app.js" defer></script>
</body>
</html>

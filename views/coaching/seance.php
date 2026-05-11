<?php
// Variables disponibles ici (injectées par genererSeance()) :
//   $program  → objet Coaching (titre, niveau...)
//   $seance   → tableau d'exercices générés
//   $niveau   → 'easy' / 'medium' / 'hard'

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$badgeLabel = match($niveau) {
    'hard'   => 'Difficile',
    'medium' => 'Moyen',
    default  => 'Facile',
};
$badgeColor = match($niveau) {
    'hard'   => '#FF7E67',
    'medium' => '#C8A2F0',
    default  => '#7DCFB6',
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Séance générée — Nutrivert</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="logo">
    <i class="fas fa-seedling"></i>
    <span>Nutrivert</span>
  </div>
  <div class="nav-links">
    <a href="index.php?controller=coaching&action=index" class="nav-link active">Programmes</a>
  </div>
</nav>

<div class="container">
  <div class="section-card" style="margin-top:2rem;">

    <!-- TITRE SECTION -->
    <div class="section-title">
      <i class="fas fa-bolt"></i>
      <span>⚡ Séance générée automatiquement</span>
    </div>

    <!-- EN-TÊTE -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h2><?= e($program->getTitle()) ?></h2>
        <p class="text-muted">
          Niveau :
          <span style="background:<?= $badgeColor ?>22; color:<?= $badgeColor ?>;
                       border:1px solid <?= $badgeColor ?>55;
                       font-size:0.8rem; font-weight:700;
                       padding:3px 12px; border-radius:60px;">
            <?= $badgeLabel ?>
          </span>
          &nbsp;—&nbsp; <?= count($seance) ?> exercices sélectionnés
        </p>
      </div>

      <!-- Bouton retour -->
      <a href="index.php?controller=coaching&action=index" class="btn btn-soft">
        <i class="fas fa-arrow-left"></i> Retour aux programmes
      </a>
    </div>

    <!-- LISTE DES EXERCICES GÉNÉRÉS -->
    <div class="coach-wrapper">

      <?php foreach ($seance as $ex): ?>

        <?php
          // Couleur selon le type d'exercice
          $couleur = match($ex['type']) {
              'cardio'    => ['border' => '#F59E0B', 'bg' => '#FEF3C722',
                              'text'   => '#92400E', 'label' => '🏃 Cardio'],
              'endurance' => ['border' => '#10B981', 'bg' => '#D1FAE522',
                              'text'   => '#065F46', 'label' => '💪 Endurance'],
              'force'     => ['border' => '#3B82F6', 'bg' => '#DBEAFE22',
                              'text'   => '#1E40AF', 'label' => '🏋️ Force'],
              default     => ['border' => '#ccc',    'bg' => '#eee',
                              'text'   => '#333',    'label' => $ex['type']],
          };
        ?>

        <div class="coach-option"
             style="border-left: 4px solid <?= $couleur['border'] ?>;">

          <!-- Nom + badge type -->
          <div style="display:flex; justify-content:space-between;
                      align-items:center; margin-bottom:0.5rem;">
            <h3 style="margin:0;"><?= e($ex['nom']) ?></h3>
            <span style="background:<?= $couleur['bg'] ?>;
                         color:<?= $couleur['text'] ?>;
                         border:1px solid <?= $couleur['border'] ?>55;
                         font-size:0.75rem; font-weight:700;
                         padding:3px 10px; border-radius:60px;">
              <?= $couleur['label'] ?>
            </span>
          </div>

          <!-- Détails séries / reps / repos -->
          <p style="font-size:0.85rem; color:#8A9B88; margin:0;">
            <i class="fas fa-layer-group" style="color:var(--coral);"></i>
            <?= $ex['series'] ?> séries
            &nbsp;|&nbsp;
            <i class="fas fa-redo" style="color:var(--mint);"></i>
            <?= $ex['reps'] ?> répétitions
            &nbsp;|&nbsp;
            <i class="fas fa-clock" style="color:#C8A2F0;"></i>
            <?= $ex['repos'] ?>s de repos
          </p>

        </div>

      <?php endforeach; ?>

    </div>

  </div>
</div>

<!-- FOOTER -->
<footer>
  <i class="fas fa-leaf"></i> Nutrivert – Nutrition intelligente, durable &amp; solidaire
</footer>

<script src="assets/js/app.js" defer></script>
</body>
</html>
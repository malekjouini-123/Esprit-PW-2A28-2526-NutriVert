<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programmes de Coaching</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f4f8f4; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform .2s; }
        .card:hover { transform: translateY(-4px); }
        .badge-easy   { background: #7DCFB6; color: #fff; }
        .badge-medium { background: #FFA07A; color: #fff; }
        .badge-hard   { background: #e74c3c; color: #fff; }
        .btn-primary  { background: #FF7E67; border: none; }
        .btn-primary:hover { background: #e56a55; }
        .page-title   { color: #2D3E2B; font-weight: 700; }
        .navbar-top   { background: white; padding: 15px 0; border-bottom: 1px solid #e0e0e0; margin-bottom: 30px; }
        .navbar-top .container { display: flex; justify-content: space-between; align-items: center; }
        .user-info { display: flex; align-items: center; gap: 15px; font-weight: 600; color: #2D3E2B; }
        .btn-logout { background: #FF7E67; color: white; border: none; padding: 8px 20px; border-radius: 20px; }
        .btn-logout:hover { background: #e56a55; color: white; text-decoration: none; }
        .btn-admin { background: #7DCFB6; color: white; border: none; padding: 8px 20px; border-radius: 20px; }
        .btn-admin:hover { background: #6ab89d; color: white; text-decoration: none; }
    </style>
</head>
<body>
<div class="navbar-top">
    <div class="container">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="javascript:history.length>1?history.back():window.location='index.php'"
               style="background:#7DCFB6; color:white; border:none; padding:8px 18px; border-radius:20px; text-decoration:none; font-weight:600; font-size:.9rem;">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <h4 class="mb-0">🏋️ Coaching</h4>
        </div>
        <div class="user-info">
            <?php if (!empty($_SESSION['user'])): ?>
                <span><?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="index.php?controller=dashboard&action=index" class="btn btn-admin">
                        <i class="fas fa-cog"></i> Back-office
                    </a>
                <?php endif; ?>
                <a href="index.php?controller=user&action=logout" class="btn btn-logout">Déconnexion</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-5">

    <!-- Titre + bouton ajouter -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">🏋️ Programmes de Coaching</h1>
        <a href="index.php?controller=coaching&action=create" class="btn btn-primary px-4">
            + Nouveau programme
        </a>
    </div>

    <!-- Message flash -->
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flashMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Barre recherche + tri -->
    <div class="row g-2 mb-4">
        <div class="col-md-6">
            <form method="GET" action="index.php">
                <input type="hidden" name="controller" value="coaching">
                <input type="hidden" name="action" value="search">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher un programme..."
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit">🔍</button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <form method="GET" action="index.php">
                <input type="hidden" name="controller" value="coaching">
                <input type="hidden" name="action" value="sort">
                <?php if (!empty($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                <?php endif; ?>
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Trier par --</option>
                    <option value="title_asc"           <?= ($_GET['sort'] ?? '') === 'title_asc'           ? 'selected' : '' ?>>Titre A→Z</option>
                    <option value="duration_weeks_asc"  <?= ($_GET['sort'] ?? '') === 'duration_weeks_asc'  ? 'selected' : '' ?>>Durée croissante</option>
                    <option value="duration_weeks_desc" <?= ($_GET['sort'] ?? '') === 'duration_weeks_desc' ? 'selected' : '' ?>>Durée décroissante</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Liste des programmes -->
    <?php if (empty($coachingPrograms)): ?>
        <div class="text-center py-5 text-muted">
            <p class="fs-5">Aucun programme trouvé.</p>
            <a href="index.php?controller=coaching&action=create" class="btn btn-primary">Créer un programme</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($coachingPrograms as $program): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold text-dark mb-0">
                                    <?= htmlspecialchars($program->getTitle()) ?>
                                </h5>
                                <span class="badge bg-<?= match($program->getDifficultyLevel()) {
                                    'easy' => 'success',
                                    'medium' => 'warning',
                                    'hard' => 'danger'
                                } ?> ms-2">
                                    <?= ucfirst($program->getDifficultyLevel()) ?>
                                </span>
                            </div>

                            <p class="text-muted small mb-3">
                                <?= htmlspecialchars(mb_substr($program->getDescription(), 0, 100)) ?>
                                <?= mb_strlen($program->getDescription()) > 100 ? '...' : '' ?>
                            </p>

                            <div class="mb-3">
                                <span class="badge bg-light text-dark border">
                                    ⏱ <?= $program->getDurationWeeks() ?> semaines
                                </span>
                                <?php $exCount = count($exercisesByCoaching[$program->getId()] ?? []); ?>
                                <span class="badge bg-light text-dark border ms-1">
                                    💪 <?= $exCount ?> exercice<?= $exCount > 1 ? 's' : '' ?>
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-wrap gap-1 mt-auto">
                                <a href="index.php?controller=coaching&action=edit&id=<?= $program->getId() ?>"
                                   class="btn btn-sm btn-outline-secondary">✏️ Modifier</a>

                                <a href="index.php?controller=exercise&action=index&coaching_id=<?= $program->getId() ?>"
                                   class="btn btn-sm btn-outline-primary">🏃 Exercices</a>

                                <a href="index.php?controller=coaching&action=generer_seance&id=<?= $program->getId() ?>"
                                   class="btn btn-sm btn-outline-success">⚡ Générer séance</a>

                                <a href="index.php?controller=coaching&action=export&id=<?= $program->getId() ?>"
                                   class="btn btn-sm btn-outline-dark">📄 PDF</a>

                                <a href="index.php?controller=coaching&action=export_csv&id=<?= $program->getId() ?>"
                                   class="btn btn-sm btn-outline-info">📊 CSV</a>

                                <a href="index.php?controller=coaching&action=delete&id=<?= $program->getId() ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Supprimer ce programme et tous ses exercices ?')">🗑️</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
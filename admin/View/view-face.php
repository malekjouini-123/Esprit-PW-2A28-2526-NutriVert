<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Face ID — Admin Nutrivert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root{ --sage:#4A6B4A; --mint:#7DCFB6; --coral:#FF7E67; }
        body{ background:#f4f8f4; }
        pre{ background:#f1f3f4; padding:15px; border-radius:8px; font-size:.8rem; max-height:300px; overflow:auto; }
    </style>
</head>
<body>
<div class="container py-5" style="max-width:700px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-camera"></i> Détail Face ID</h4>
        <a href="index.php?controller=admin&action=faces" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Utilisateur</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($face['nom']) ?></dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($face['email']) ?></dd>
                <dt class="col-sm-3">Rôle</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($face['role']) ?></dd>
                <dt class="col-sm-3">Enregistré le</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($face['created_at'] ?? '—') ?></dd>
                <dt class="col-sm-3">Mis à jour le</dt>
                <dd class="col-sm-9"><?= htmlspecialchars($face['updated_at'] ?? '—') ?></dd>
            </dl>
        </div>
    </div>

    <?php if (!empty($face['face_encoding'])): ?>
        <div class="card shadow-sm">
            <div class="card-header"><i class="fas fa-code"></i> Encodage stocké (JSON)</div>
            <div class="card-body">
                <pre><?= htmlspecialchars(
                    json_encode(json_decode($face['face_encoding'], true), JSON_PRETTY_PRINT)
                ) ?></pre>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Aucun encodage Face ID trouvé pour cet utilisateur.</div>
    <?php endif; ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

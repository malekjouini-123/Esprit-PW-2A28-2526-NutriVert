<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Face ID — Admin Nutrivert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root{ --coral:#FF7E67; --mint:#7DCFB6; --sage:#4A6B4A; }
        body{ background:#f4f8f4; }
        .sidebar{ background:var(--sage); min-height:100vh; padding:20px 0; }
        .sidebar .nav-link{ color:rgba(255,255,255,.8); padding:12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active{ color:#fff; background:rgba(255,255,255,.1); }
        .sidebar .brand{ color:#fff; font-size:1.3rem; font-weight:700; padding:20px; margin-bottom:10px; }
        .top-bar{ background:#fff; border-bottom:2px solid var(--mint); padding:15px 25px; }
    </style>
</head>
<body>
<div class="container-fluid">
<div class="row">

    <div class="col-md-2 sidebar">
        <div class="brand"><i class="fas fa-leaf"></i> Nutrivert</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="index.php?controller=dashboard&action=index"><i class="fas fa-chart-bar me-2"></i> Coaching</a>
            <a class="nav-link" href="index.php?controller=user&action=adminUsers"><i class="fas fa-users me-2"></i> Utilisateurs</a>
            <a class="nav-link active" href="index.php?controller=admin&action=faces"><i class="fas fa-camera me-2"></i> Face ID</a>
            <a class="nav-link" href="index.php?controller=chat&action=adminChats"><i class="fas fa-comments me-2"></i> Chats IA</a>
            <a class="nav-link text-danger" href="index.php?controller=user&action=logout"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a>
        </nav>
    </div>

    <div class="col-md-10 p-0">
        <div class="top-bar">
            <h4 class="mb-0"><i class="fas fa-camera"></i> Gestion des Face ID</h4>
        </div>

        <div class="p-4">
            <?php if (!empty($flashMessage)): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <?= htmlspecialchars($flashMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Face ID</th>
                                <th>Enregistré le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int)$u['id'] ?></td>
                                <td><?= htmlspecialchars($u['nom']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'coach' ? 'warning' : 'primary') ?>">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($u['has_face']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Enregistré</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Aucun</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $u['face_created_at'] ? htmlspecialchars($u['face_created_at']) : '—' ?></td>
                                <td>
                                    <?php if ($u['has_face']): ?>
                                        <a href="index.php?controller=admin&action=viewFace&user_id=<?= (int)$u['id'] ?>"
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form method="POST"
                                              action="index.php?controller=admin&action=deleteFace&user_id=<?= (int)$u['id'] ?>"
                                              style="display:inline;"
                                              onsubmit="return confirm('Supprimer le Face ID de cet utilisateur ?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

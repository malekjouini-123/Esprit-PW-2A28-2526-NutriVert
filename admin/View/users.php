<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs — Admin Nutrivert</title>
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
        .badge-face-ok{ background:#d4edda; color:#155724; }
        .badge-face-none{ background:#fff3cd; color:#856404; }
    </style>
</head>
<body>
<div class="container-fluid">
<div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 sidebar">
        <div class="brand"><i class="fas fa-leaf"></i> Nutrivert</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="index.php?controller=dashboard&action=index">
                <i class="fas fa-chart-bar me-2"></i> Coaching
            </a>
            <a class="nav-link active" href="index.php?controller=user&action=adminUsers">
                <i class="fas fa-users me-2"></i> Utilisateurs
            </a>
            <a class="nav-link" href="index.php?controller=admin&action=faces">
                <i class="fas fa-camera me-2"></i> Face ID
            </a>
            <a class="nav-link" href="index.php?controller=chat&action=adminChats">
                <i class="fas fa-comments me-2"></i> Chats IA
            </a>
            <a class="nav-link text-danger" href="index.php?controller=user&action=logout">
                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
            </a>
        </nav>
    </div>

    <!-- Main content -->
    <div class="col-md-10 p-0">
        <div class="top-bar d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-users"></i> Gestion des Utilisateurs</h4>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-user-plus"></i> Ajouter
            </button>
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
                                        <span class="badge badge-face-ok"><i class="fas fa-check-circle"></i> Oui</span>
                                    <?php else: ?>
                                        <span class="badge badge-face-none"><i class="fas fa-times-circle"></i> Non</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="<?= (int)$u['id'] ?>"
                                            data-nom="<?= htmlspecialchars($u['nom']) ?>"
                                            data-email="<?= htmlspecialchars($u['email']) ?>"
                                            data-role="<?= htmlspecialchars($u['role']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="index.php?controller=user&action=adminUsers&delete_user=<?= (int)$u['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Supprimer cet utilisateur ?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
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

<!-- Add modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?controller=user&action=adminUsers">
                <div class="modal-body">
                    <input type="hidden" name="add_user" value="1">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-select">
                            <option value="user">Utilisateur</option>
                            <option value="coach">Coach</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?controller=user&action=adminUsers">
                <div class="modal-body">
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" id="editNom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe <small class="text-muted">(laisser vide = inchangé)</small></label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="role" id="editRole" class="form-select">
                            <option value="user">Utilisateur</option>
                            <option value="coach">Coach</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editUserId').value = btn.dataset.id;
    document.getElementById('editNom').value    = btn.dataset.nom;
    document.getElementById('editEmail').value  = btn.dataset.email;
    document.getElementById('editRole').value   = btn.dataset.role;
});
</script>
</body>
</html>

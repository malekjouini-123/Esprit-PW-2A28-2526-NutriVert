<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlers/userscontroller.php';

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$userId = intval($_GET['id']);
$usersController = new UsersController();
$user = $usersController->getUserById($userId);

if (!$user) {
    header('Location: users.php?error=User not found');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'utilisateur - <?php echo htmlspecialchars($user->getNomUtilisateur()); ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .detail-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .user-profile {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            background: #e9ecef;
        }

        .profile-info h1 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .profile-info p {
            margin: 5px 0;
            color: #666;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-section h3 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
        }

        .info-value {
            color: #333;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-box.posts {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-box.replies {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-box.likes {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-box.dislikes {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 5px;
        }

        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .bio {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            font-style: italic;
            color: #666;
        }

        .bio-empty {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="detail-container">
        <a href="users.php" class="back-link">← Retour à la liste des utilisateurs</a>

        <div class="user-profile">
            <div class="profile-header">
                <?php if ($user->getPhotoProfil()): ?>
                    <img src="../../<?php echo htmlspecialchars($user->getPhotoProfil()); ?>" alt="Avatar" class="profile-avatar">
                <?php else: ?>
                    <div class="profile-avatar" style="display: flex; align-items: center; justify-content: center; background: #007bff; color: white; font-size: 48px; font-weight: bold;">
                        <?php echo strtoupper(substr($user->getNomUtilisateur(), 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user->getNomUtilisateur()); ?></h1>
                    <p><?php echo htmlspecialchars($user->getEmail()); ?></p>
                    <p style="font-size: 12px; color: #999;">ID Utilisateur: #<?php echo $user->getIdUser(); ?></p>
                </div>
            </div>

            <?php if ($user->getBio()): ?>
                <div class="info-section">
                    <h3>Biographie</h3>
                    <div class="bio"><?php echo htmlspecialchars($user->getBio()); ?></div>
                </div>
            <?php endif; ?>

            <div class="info-section">
                <h3>Statistiques</h3>
                <div class="stats">
                    <div class="stat-box posts">
                        <div class="stat-number"><?php echo $user->getPostCount(); ?></div>
                        <div class="stat-label">Publication<?php echo $user->getPostCount() > 1 ? 's' : ''; ?></div>
                    </div>
                    <div class="stat-box replies">
                        <div class="stat-number"><?php echo $user->getReplyCount(); ?></div>
                        <div class="stat-label">Réponse<?php echo $user->getReplyCount() > 1 ? 's' : ''; ?></div>
                    </div>
                    <div class="stat-box likes">
                        <div class="stat-number"><?php echo $user->getLikeCount(); ?></div>
                        <div class="stat-label">J'aime</div>
                    </div>
                    <div class="stat-box dislikes">
                        <div class="stat-number"><?php echo $user->getDislikeCount(); ?></div>
                        <div class="stat-label">Je n'aime pas</div>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <h3>Informations Générales</h3>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($user->getEmail()); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date d'inscription</span>
                    <span class="info-value"><?php echo date('d/m/Y à H:i', strtotime($user->getDateInscription())); ?></span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="users.php" class="btn btn-secondary">Retour à la liste</a>
                <button class="btn btn-danger" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) { window.location='delete_user.php?id=<?php echo $user->getIdUser(); ?>'; }">Supprimer l'utilisateur</button>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// views/community.php

global $pdo;
$allUsersList = [];
if (isset($pdo)) {
    $allUsersList = $pdo->query("SELECT id_user, nom_utilisateur FROM Utilisateur ORDER BY LENGTH(nom_utilisateur) DESC")->fetchAll(PDO::FETCH_ASSOC);
}

function renderTags($text, $users) {
    $safeText = htmlspecialchars($text);
    foreach ($users as $u) {
        $name = $u['nom_utilisateur'];
        $pattern = '/(?<=^|\s)@(' . preg_quote($name, '/') . ')(?=[^\w]|$)/i';
        $replacement = '<span class="user-tag" data-id="'.$u['id_user'].'">@$1</span>';
        $safeText = preg_replace($pattern, $replacement, $safeText);
    }
    return nl2br($safeText);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriVert | Communauté</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>
<header>
    <div class="logo"><i class="fas fa-leaf" style="color:#10b981;"></i> NutriVert</div>
    <nav>
        <a href="#">Accueil</a>
        <a href="#" class="active">Communauté</a>
        <a href="#">Coaching</a>
        <a href="#">Recettes</a>
        <a class="nav-admin" href="#"><i class="fas fa-user-circle"></i> Profil</a>
    </nav>
</header>

<div class="wrapper">
    <!-- Create Post Section -->
    <section class="create-post">
        <div class="create-post-header" style="flex-direction: column; gap: 0.8rem; align-items: stretch;">
            <div style="display: flex; gap: 1rem; align-items: center; width: 100%;">
                <div class="avatar">MR</div>
                <div style="flex: 1; display: flex; flex-direction: column; gap: 0.2rem;">
                    <input type="text" id="new-post-title" placeholder="Titre de votre publication (ex: Mon nouveau régime)" style="width: 100%; border: none; background: #f3f4f6; border-radius: 8px; padding: 0.8rem 1.2rem; font-size: 1rem; font-weight: 600; font-family: inherit; outline: none; transition: box-shadow 0.2s;">
                    <span id="new-post-title-error" class="error-msg-front" style="color: #ef4444; font-size: 0.75rem; font-weight: 500; margin-left: 0.5rem; display: none;"></span>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                <textarea id="new-post-content" placeholder="Que voulez-vous partager avec la communauté ?" style="margin-top: 0.2rem;"></textarea>
                <span id="new-post-content-error" class="error-msg-front" style="color: #ef4444; font-size: 0.75rem; font-weight: 500; margin-left: 0.5rem; display: none;"></span>
            </div>
        </div>
        <div class="create-post-actions">
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <select id="new-post-type" class="custom-select">
                    <option value="Article">📄 Article</option>
                    <option value="Question">❓ Question</option>
                    <option value="Recette">🍳 Recette</option>
                </select>
                <input type="file" id="post-image-input" accept="image/*" style="display: none;">
                <button class="action-btn" id="btn-post-image" onclick="document.getElementById('post-image-input').click()"><i class="fas fa-image"></i> Photo/Vidéo</button>
                <span id="post-image-name" style="font-size: 0.8rem; color: #6b7280;"></span>
            </div>
            <button id="btn-submit-post" class="btn-submit">Publier</button>
        </div>
    </section>

    <!-- Post Feed -->
    <?php foreach ($posts as $post): 
        $author = getUserInfo($post->getAuteurId());
        $authorName = $author ? $author['nom_utilisateur'] : 'Utilisateur inconnu';
        $initials = getInitials($authorName);
        $isAuthor = ($post->getAuteurId() == $currentUserId);
        $isMarcRobert = strtolower(trim($authorName)) === 'marc robert';
        $canManagePost = $isAuthor || $isMarcRobert;
        $colors = [
            ['bg' => '#fef08a', 'text' => '#854d0e'],
            ['bg' => '#bfdbfe', 'text' => '#1e3a8a'],
            ['bg' => '#fbcfe8', 'text' => '#9d174d'],
            ['bg' => '#bbf7d0', 'text' => '#166534'],
            ['bg' => '#e5e7eb', 'text' => '#4b5563']
        ];
        $cIdx = $post->getAuteurId() % count($colors);
        $color = $colors[$cIdx];
        
        $replies = $replyController->getRepliesByPost($post->getIdPost());
        $reactionCounts = $reactionController->getReactionCounts($post->getIdPost());
        $likes = $reactionCounts['Like'] ?? 0;
    ?>
    <article class="post-card" id="post-<?= $post->getIdPost() ?>" data-post-id="<?= $post->getIdPost() ?>">
        <div class="post-header">
            <div class="post-author-wrap">
                <div class="avatar" style="background:<?= $color['bg'] ?>; color:<?= $color['text'] ?>;"><?= $initials ?></div>
                <div class="post-author-info">
                    <span class="post-author-name"><?= htmlspecialchars($authorName) ?></span>
                    <span class="post-time">
                        <i class="fas fa-globe-americas"></i> <?= timeAgo($post->getDatePublication()) ?>
                        <span style="margin: 0 0.4rem; color: #d1d5db;">•</span>
                        <span style="background: #f3f4f6; padding: 0.1rem 0.6rem; border-radius: 999px; font-size: 0.75rem; color: #4b5563; font-weight: 500; border: 1px solid #e5e7eb;"><?= htmlspecialchars($post->getTypePost()) ?></span>
                    </span>
                </div>
            </div>
            <div class="post-options-container" style="position: relative;">
                <button class="post-options" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';"><i class="fas fa-ellipsis-h"></i></button>
                <div class="post-dropdown" style="display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid #d1d5db; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10; width: max-content; overflow: hidden;">
                    <button class="dropdown-item btn-repost" style="display: block; width: 100%; padding: 0.5rem 1rem; text-align: left; background: none; border: none; cursor: pointer; font-family: inherit; font-size: 0.9rem; border-bottom: 1px solid #f3f4f6;"><i class="fas fa-retweet" style="margin-right: 0.5rem; color: #10b981;"></i> Reposter le post</button>
                    <button class="dropdown-item btn-copy-link" style="display: block; width: 100%; padding: 0.5rem 1rem; text-align: left; background: none; border: none; cursor: pointer; font-family: inherit; font-size: 0.9rem;"><i class="fas fa-link" style="margin-right: 0.5rem; color: #3b82f6;"></i> Copier le lien</button>
                </div>
            </div>
        </div>
        <div class="post-content" data-raw-content="<?= htmlspecialchars($post->getContenu()) ?>">
            <?php if ($post->getTitre()): ?>
                <h3 class="post-title" style="margin-bottom: 0.5rem; font-size: 1.1rem; color: #111827;"><?= renderTags($post->getTitre(), $allUsersList) ?></h3>
            <?php endif; ?>
            <p class="post-text" style="margin: 0;"><?= renderTags($post->getContenu(), $allUsersList) ?></p>
        </div>
        
        <?php if ($post->getMediaUrl()): ?>
        <img src="<?= htmlspecialchars($post->getMediaUrl()) ?>" alt="Post image" class="post-image">
        <?php endif; ?>
        
        <div class="post-stats" style="display: flex; gap: 1rem;">
            <div class="stat-item">
                <i class="fas fa-heart stat-icon" style="color:#ef4444;"></i>
                <span class="likes-count"><?= $likes ?></span>
            </div>
            <div class="stat-item">
                <i class="fas fa-thumbs-down stat-icon" style="color:#6b7280;"></i>
                <span class="dislikes-count"><?= $reactionCounts['Dislike'] ?? 0 ?></span>
            </div>
            <div class="stat-item" style="margin-left: auto;">
                <span><?= count($replies) ?> réponse<?= count($replies) > 1 ? 's' : '' ?></span>
            </div>
        </div>

        <div class="post-actions-bar">
            <div class="reaction-wrapper">
                <button class="post-action btn-react-main" data-type="Like"><i class="far fa-thumbs-up"></i> J'aime</button>
                <div class="reaction-popover">
                    <button class="react-choice btn-react" data-type="Like" title="J'aime"><i class="fas fa-thumbs-up" style="color: #10b981;"></i></button>
                    <button class="react-choice btn-react" data-type="Dislike" title="Je n'aime pas"><i class="fas fa-thumbs-down" style="color: #ef4444;"></i></button>
                </div>
            </div>
            <button class="post-action btn-reply-post"><i class="far fa-comment-alt"></i> Répondre</button>
            <?php if ($canManagePost): ?>
            <button class="post-action btn-edit-post" style="margin-left: auto; color: #6b7280;" title="Modifier ce post"><i class="fas fa-edit"></i> Modifier</button>
            <button class="post-action btn-delete-post" style="color: #ef4444;" title="Supprimer ce post"><i class="fas fa-trash-alt"></i> Supprimer</button>
            <?php endif; ?>
        </div>

        <div class="replies-section">
            <div class="replies-list">
            <?php foreach ($replies as $reply): 
                $rAuthor = getUserInfo($reply->getAuteurId());
                $rAuthorName = $rAuthor ? $rAuthor['nom_utilisateur'] : 'Utilisateur';
                $rInitials = getInitials($rAuthorName);
                $rCIdx = $reply->getAuteurId() % count($colors);
                $rColor = $colors[$rCIdx];
                $rrCounts = $reactionReplyController->getReactionCounts($reply->getIdReply());
                $rLikes = $rrCounts['Like'] ?? 0;
                $isMarcRobertReply = strtolower(trim($rAuthorName)) === 'marc robert';
            ?>
            <div class="reply" data-reply-id="<?= $reply->getIdReply() ?>" <?= $reply->getParentReplyId() ? 'style="margin-left: 2.5rem; border-left: 2px solid #e5e7eb; padding-left: 1rem;"' : '' ?>>
                <div class="avatar reply-avatar" style="background:<?= $rColor['bg'] ?>; color:<?= $rColor['text'] ?>;"><?= $rInitials ?></div>
                <div style="flex:1;">
                    <div class="reply-content-box" data-raw-content="<?= htmlspecialchars($reply->getCommentaire()) ?>">
                        <div class="reply-author"><?= htmlspecialchars($rAuthorName) ?></div>
                        <div class="reply-text"><?= renderTags($reply->getCommentaire(), $allUsersList) ?></div>
                        <?php if ($reply->getImageUrl()): ?>
                        <img src="<?= htmlspecialchars($reply->getImageUrl()) ?>" style="max-width: 200px; border-radius: 8px; margin-top: 0.5rem;">
                        <?php endif; ?>
                    </div>
                    <div class="reply-actions">
                        <div class="reaction-wrapper-reply">
                            <button class="reply-action btn-react-main-reply" data-type="Like" style="font-weight:600; color:#4b5563;">J'aime <span class="likes-count" style="margin-left:0.2rem; color:#10b981;"><?= $rLikes > 0 ? $rLikes : '' ?></span></button>
                            <div class="reaction-popover-reply">
                                <button class="react-choice-reply btn-react-reply" data-type="Like" title="J'aime"><i class="fas fa-thumbs-up" style="color: #10b981;"></i></button>
                                <button class="react-choice-reply btn-react-reply" data-type="Dislike" title="Je n'aime pas"><i class="fas fa-thumbs-down" style="color: #ef4444;"></i></button>
                            </div>
                        </div>
                        <button class="reply-action btn-sub-reply">Répondre</button>
                        <span style="margin-left: auto; display:flex; gap:0.5rem; align-items:center;">
                            <?php if ($reply->getAuteurId() == $currentUserId || $isMarcRobertReply || $isMarcRobert): ?>
                                <button class="reply-action btn-edit-reply" style="color: #6b7280;">Modifier</button>
                                <button class="reply-action btn-delete-reply" style="color: #ef4444;">Supprimer</button>
                            <?php endif; ?>
                            <span style="font-size:0.75rem; color:#9ca3af; margin-left:0.5rem;"><?= timeAgo($reply->getDateReply()) ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>

            <div class="reply-input-area" data-parent-id="0">
                <div class="avatar reply-avatar" style="background:#e5e7eb; color:#4b5563;">Toi</div>
                <div class="reply-input-wrapper">
                    <input type="text" class="reply-input" placeholder="Écrire une réponse...">
                    <input type="file" class="reply-image-input" accept="image/*" style="display: none;">
                    <button class="icon-btn" onclick="this.previousElementSibling.click()"><i class="fas fa-camera"></i></button>
                    <button class="icon-btn btn-send-reply" style="color:#166534;"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </article>
    <?php endforeach; ?>

</div>
<!-- Custom Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="modal-title">Confirmation</h2>
        <p class="modal-text">Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-cancel" id="modalCancel">Annuler</button>
            <button class="modal-btn modal-btn-confirm" id="modalConfirm">Supprimer</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="modal-box">
        <div class="modal-icon" style="background:#f0fdf4; color:#16a34a;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 class="modal-title">Succès</h2>
        <p class="modal-text" id="successModalText">L'opération a été effectuée avec succès.</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-confirm" style="background:#16a34a;" onclick="document.getElementById('successModal').classList.remove('show')">OK</button>
        </div>
    </div>
</div>

    <button id="back-to-top" class="back-to-top" title="Retour en haut">
        <i class="fas fa-arrow-up"></i>
    </button>

</div>

<script>
    const allUsers = <?= json_encode($allUsersList, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="assets/js/community.js?v=<?= time() ?>"></script>
</body>
</html>

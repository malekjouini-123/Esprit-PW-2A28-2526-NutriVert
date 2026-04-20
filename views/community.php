<?php
// views/community.php
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
    <link rel="stylesheet" href="assets/css/style.css">
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
        <div class="create-post-header">
            <div class="avatar">MR</div>
            <textarea id="new-post-content" placeholder="Partagez un article, posez une question sur votre régime..."></textarea>
        </div>
        <div class="create-post-actions">
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <input type="file" id="post-image-input" accept="image/*" style="display: none;">
                <button class="action-btn" id="btn-post-image" onclick="document.getElementById('post-image-input').click()"><i class="fas fa-image"></i> Photo/Vidéo</button>
                <span id="post-image-name" style="font-size: 0.8rem; color: #6b7280;"></span>
                <button class="action-btn"><i class="fas fa-question-circle" style="color:#3b82f6;"></i> Question</button>
            </div>
            <button id="btn-submit-post" class="btn-submit">Publier</button>
        </div>
    </section>

    <!-- Post Feed -->
    <?php foreach ($posts as $post): 
        $author = getUserInfo($post->getAuteurId());
        $authorName = $author ? $author['nom_utilisateur'] : 'Utilisateur inconnu';
        $initials = getInitials($authorName);
        $isMarcRobert = strtolower(trim($authorName)) === 'marc robert';
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
                    <span class="post-time"><i class="fas fa-globe-americas"></i> <?= timeAgo($post->getDatePublication()) ?></span>
                </div>
            </div>
            <button class="post-options"><i class="fas fa-ellipsis-h"></i></button>
        </div>
        <div class="post-content" data-raw-content="<?= htmlspecialchars($post->getContenu()) ?>">
            <p class="post-text" style="margin: 0;"><?= nl2br(htmlspecialchars($post->getContenu())) ?></p>
        </div>
        
        <?php if ($post->getMediaUrl()): ?>
        <img src="<?= htmlspecialchars($post->getMediaUrl()) ?>" alt="Post image" class="post-image">
        <?php endif; ?>
        
        <div class="post-stats" style="display: flex; gap: 1rem;">
            <div class="stat-item">
                <i class="fas fa-thumbs-up stat-icon like"></i>
                <i class="fas fa-heart stat-icon" style="color:#ef4444;"></i>
                <span class="likes-count"><?= $likes ?></span>
            </div>
            <div class="stat-item">
                <i class="fas fa-thumbs-down stat-icon dislike" style="color:#6b7280;"></i>
                <span class="dislikes-count"><?= $reactionCounts['Dislike'] ?? 0 ?></span>
            </div>
            <div class="stat-item" style="margin-left: auto;">
                <span><?= count($replies) ?> réponse<?= count($replies) > 1 ? 's' : '' ?></span>
            </div>
        </div>

        <div class="post-actions-bar">
            <button class="post-action btn-like" data-type="Like"><i class="far fa-thumbs-up"></i> J'aime</button>
            <button class="post-action btn-dislike" data-type="Dislike"><i class="far fa-thumbs-down"></i> Je n'aime pas</button>
            <button class="post-action"><i class="far fa-comment-alt"></i> Répondre</button>
            <?php if ($isMarcRobert): ?>
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
            ?>
            <div class="reply" data-reply-id="<?= $reply->getIdReply() ?>" <?= $reply->getParentReplyId() ? 'style="margin-left: 2.5rem; border-left: 2px solid #e5e7eb; padding-left: 1rem;"' : '' ?>>
                <div class="avatar reply-avatar" style="background:<?= $rColor['bg'] ?>; color:<?= $rColor['text'] ?>;"><?= $rInitials ?></div>
                <div style="flex:1;">
                    <div class="reply-content-box" data-raw-content="<?= htmlspecialchars($reply->getCommentaire()) ?>">
                        <div class="reply-author"><?= htmlspecialchars($rAuthorName) ?></div>
                        <div class="reply-text"><?= nl2br(htmlspecialchars($reply->getCommentaire())) ?></div>
                        <?php if ($reply->getImageUrl()): ?>
                        <img src="<?= htmlspecialchars($reply->getImageUrl()) ?>" style="max-width: 200px; border-radius: 8px; margin-top: 0.5rem;">
                        <?php endif; ?>
                    </div>
                    <div class="reply-actions">
                        <button class="reply-action btn-like-reply" data-type="Like">J'aime (<span class="reply-likes-count"><?= $rLikes ?></span>)</button>
                        <button class="reply-action btn-dislike-reply" data-type="Dislike">Je n'aime pas (<span class="reply-dislikes-count"><?= $rrCounts['Dislike'] ?? 0 ?></span>)</button>
                        <button class="reply-action btn-sub-reply">Répondre</button>
                        <span style="margin-left: auto; display:flex; gap:0.5rem; align-items:center;">
                            <button class="reply-action btn-edit-reply" style="color: #6b7280;">Modifier</button>
                            <button class="reply-action btn-delete-reply" style="color: #ef4444;">Supprimer</button>
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
<script src="assets/js/community.js"></script>
</body>
</html>

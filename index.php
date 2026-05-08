<?php
// index.php
require_once __DIR__ . '/controlers/postcontroller.php';
require_once __DIR__ . '/controlers/replycontroller.php';
require_once __DIR__ . '/controlers/ractionscontroller.php';
require_once __DIR__ . '/controlers/reactionreplycontroller.php';

$postController = new PostController();
$replyController = new ReplyController();
$reactionController = new ReactionController();
$reactionReplyController = new ReactionReplyController();

$pdo = getDB();

// Simulated current user session
$currentUserId = 1; 

$posts = $postController->getAllPosts();

// Load all available icons
$allIcons = $postController->getAllIcons();
$iconMap = [];
foreach ($allIcons as $icon) {
    $iconMap[$icon['id_icon']] = $icon;
}

// Helper to get user
function getUserInfo($id_user) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_user = ?");
    $stmt->execute([$id_user]);
    return $stmt->fetch();
}

// Helper to get initials
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $w) {
        $initials .= strtoupper(substr($w, 0, 1));
    }
    return substr($initials, 0, 2);
}

// Helper to format time ago
function timeAgo($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'an',
        'm' => 'mois',
        'w' => 'semaine',
        'd' => 'jour',
        'h' => 'heure',
        'i' => 'minute',
        's' => 'seconde',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 && $k != 'm' ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ' : 'à l\'instant';
}

require_once __DIR__ . '/views/community.php';
?>

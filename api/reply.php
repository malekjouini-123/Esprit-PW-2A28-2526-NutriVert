<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../model/reply.php';
require_once '../controlers/replycontroller.php';

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$parent_reply_id = isset($_POST['parent_reply_id']) ? (int)$_POST['parent_reply_id'] : null;
$commentaire = $_POST['commentaire'] ?? '';

if ($post_id === 0 || trim($commentaire) === '') {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants ou vides']);
    exit;
}

$image_url = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $filename = time() . '_' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        $image_url = 'uploads/' . $filename;
    }
}

$user_id = 1; // Simulated user ID

// Handle empty parent_reply_id specifically as null
if ($parent_reply_id === 0) {
    $parent_reply_id = null;
}

$reply = new Reply($commentaire, $image_url, $post_id, $user_id, $parent_reply_id);
$replyController = new ReplyController();
$newReply = $replyController->createReply($reply);

// Get the author details
$pdo = getDB();
$stmt = $pdo->prepare("SELECT nom_utilisateur FROM Utilisateur WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$authorName = $user ? $user['nom_utilisateur'] : 'Utilisateur';

function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $w) {
        $initials .= strtoupper(substr($w, 0, 1));
    }
    return substr($initials, 0, 2);
}

$colors = [
    ['bg' => '#fef08a', 'text' => '#854d0e'],
    ['bg' => '#bfdbfe', 'text' => '#1e3a8a'],
    ['bg' => '#fbcfe8', 'text' => '#9d174d'],
    ['bg' => '#bbf7d0', 'text' => '#166534'],
    ['bg' => '#e5e7eb', 'text' => '#4b5563']
];
$rColor = $colors[$user_id % count($colors)];

echo json_encode([
    'success' => true,
    'id_reply' => $newReply->getIdReply(),
    'commentaire' => nl2br(htmlspecialchars($newReply->getCommentaire())),
    'image_url' => $image_url,
    'author_name' => htmlspecialchars($authorName),
    'initials' => getInitials($authorName),
    'color_bg' => $rColor['bg'],
    'color_text' => $rColor['text'],
    'date' => 'à l\'instant',
    'parent_reply_id' => $parent_reply_id
]);

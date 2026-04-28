<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../model/reaction.php';
require_once '../controlers/ractionscontroller.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['post_id']) || !isset($data['type'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$post_id = (int)$data['post_id'];
$type = $data['type']; // 'Like' or 'Dislike'
$user_id = 1; // Simulated user ID

$reactionController = new ReactionController();
$reactionController->toggleReaction($user_id, $post_id, $type);

// Return new counts
$counts = $reactionController->getReactionCounts($post_id);

// Return user's current reaction
$pdo = getDB();
$stmt = $pdo->prepare("SELECT type_reaction FROM Reaction WHERE user_id = ? AND post_id = ?");
$stmt->execute([$user_id, $post_id]);
$userReaction = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'likes' => $counts['Like'] ?? 0,
    'dislikes' => $counts['Dislike'] ?? 0,
    'user_reaction' => $userReaction ?: null
]);

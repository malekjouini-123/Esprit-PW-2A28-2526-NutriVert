<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../model/reactionreply.php';
require_once '../controlers/reactionreplycontroller.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['reply_id']) || !isset($data['type'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$reply_id = (int)$data['reply_id'];
$type = $data['type']; // 'Like' or 'Dislike'
$user_id = 1; // Simulated user ID

$reactionController = new ReactionReplyController();
$reactionController->toggleReaction($user_id, $reply_id, $type);

// Return new counts
$counts = $reactionController->getReactionCounts($reply_id);

// Return user's current reaction
$pdo = getDB();
$stmt = $pdo->prepare("SELECT type_reaction FROM ReactionReply WHERE user_id = ? AND reply_id = ?");
$stmt->execute([$user_id, $reply_id]);
$userReaction = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'likes' => $counts['Like'] ?? 0,
    'dislikes' => $counts['Dislike'] ?? 0,
    'user_reaction' => $userReaction ?: null
]);

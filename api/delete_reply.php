<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id_reply'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$id_reply = (int)$data['id_reply'];

try {
    $pdo = getDB();
    $pdo->prepare("SET FOREIGN_KEY_CHECKS=0")->execute();

    // Find all sub-replies
    $stmt = $pdo->prepare("SELECT id_reply FROM Reply WHERE parent_reply_id = ?");
    $stmt->execute([$id_reply]);
    $subReplies = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allRepliesToDelete = array_merge([$id_reply], $subReplies);

    // Delete reactions for the reply and all its sub-replies
    $in = str_repeat('?,', count($allRepliesToDelete) - 1) . '?';
    $stmt = $pdo->prepare("DELETE FROM ReactionReply WHERE reply_id IN ($in)");
    $stmt->execute($allRepliesToDelete);

    // Delete sub-replies
    if (!empty($subReplies)) {
        $inSub = str_repeat('?,', count($subReplies) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM Reply WHERE id_reply IN ($inSub)");
        $stmt->execute($subReplies);
    }

    // Delete the reply itself
    $stmt = $pdo->prepare("DELETE FROM Reply WHERE id_reply = ?");
    $stmt->execute([$id_reply]);
    
    $success = $stmt->rowCount() > 0;
    
    $pdo->prepare("SET FOREIGN_KEY_CHECKS=1")->execute();

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Commentaire non trouvé']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

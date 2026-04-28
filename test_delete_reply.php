<?php
require_once 'config.php';
$pdo = getDB();

$reply_id = 1; // Existing reply from my check_tables output

try {
    // Simulate api/delete_reply.php logic
    $id_reply = $reply_id;

    // Find all sub-replies
    $stmt = $pdo->prepare("SELECT id_reply FROM reply WHERE parent_reply_id = ?");
    $stmt->execute([$id_reply]);
    $subReplies = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allRepliesToDelete = array_merge([$id_reply], $subReplies);

    echo "Found " . count($allRepliesToDelete) . " replies to delete reactions for.\n";

    // Delete reactions for the reply and all its sub-replies
    $in = str_repeat('?,', count($allRepliesToDelete) - 1) . '?';
    $stmt = $pdo->prepare("DELETE FROM reactionreply WHERE reply_id IN ($in)");
    $stmt->execute($allRepliesToDelete);
    echo "Reactions deleted.\n";

    // Delete sub-replies
    if (!empty($subReplies)) {
        $inSub = str_repeat('?,', count($subReplies) - 1) . '?';
        $stmt = $pdo->prepare("DELETE FROM reply WHERE id_reply IN ($inSub)");
        $stmt->execute($subReplies);
        echo "Sub-replies deleted.\n";
    }

    // Delete the reply itself
    $stmt = $pdo->prepare("DELETE FROM reply WHERE id_reply = ?");
    $stmt->execute([$id_reply]);
    echo "Reply deleted.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

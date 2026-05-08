<?php
require_once 'config.php';
$pdo = getDB();

try {
    echo "Testing query...\n";
    $userOrderBy = "u.id_user DESC";
    $query = "
        SELECT u.*, 
            (SELECT COUNT(*) FROM Post WHERE auteur_id = u.id_user) as post_count,
            (SELECT COUNT(*) FROM Reply WHERE auteur_id = u.id_user) as reply_count,
            (SELECT (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Like') + (SELECT COUNT(*) FROM ReactionReply WHERE user_id = u.id_user AND type_reaction = 'Like')) as like_count,
            (SELECT (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Dislike') + (SELECT COUNT(*) FROM ReactionReply WHERE user_id = u.id_user AND type_reaction = 'Dislike')) as dislike_count
        FROM Utilisateur u 
        ORDER BY $userOrderBy
    ";
    $stmt = $pdo->query($query);
    $users = $stmt->fetchAll();
    echo "Query successful! Found " . count($users) . " users.\n";
    foreach($users as $user) {
        echo "- " . $user['nom_utilisateur'] . " (Posts: " . $user['post_count'] . ", Replies: " . $user['reply_count'] . ")\n";
    }
} catch (Exception $e) {
    echo "QUERY FAILED: " . $e->getMessage() . "\n";
}

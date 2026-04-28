<?php
require_once 'config.php';
$pdo = getDB();
try {
    $stmt = $pdo->query("
        SELECT u.id_user, u.nom_utilisateur, u.email,
            (SELECT COUNT(*) FROM Post WHERE auteur_id = u.id_user) as post_count,
            (SELECT COUNT(*) FROM Reply WHERE auteur_id = u.id_user) as reply_count,
            (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Like') as like_count,
            (SELECT COUNT(*) FROM Reaction WHERE user_id = u.id_user AND type_reaction = 'Dislike') as dislike_count
        FROM Utilisateur u 
        ORDER BY u.nom_utilisateur ASC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($users) . "\n";
    foreach ($users as $u) {
        echo "- " . $u['nom_utilisateur'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

<?php
header('Content-Type: application/json');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de post invalide']);
    exit;
}

$pdo = getDB();

try {
    // Duplicate the post
    $stmt = $pdo->prepare("INSERT INTO Post (titre, contenu, media_url, type_post, auteur_id) 
                           SELECT CONCAT('reposted: ', titre), contenu, media_url, type_post, auteur_id 
                           FROM Post WHERE id_post = ?");
    $stmt->execute([$post_id]);
    
    echo json_encode(['success' => true, 'message' => 'Publication republiée avec succès']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la republication : ' . $e->getMessage()]);
}

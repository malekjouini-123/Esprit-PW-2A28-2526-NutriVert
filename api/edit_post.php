<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../controlers/postcontroller.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id_post']) || !isset($data['contenu'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$id_post = (int)$data['id_post'];
$contenu = trim($data['contenu']);

if ($contenu === '') {
    echo json_encode(['success' => false, 'message' => 'Contenu vide']);
    exit;
}

try {
    $postController = new PostController();
    
    // Get the post to verify it belongs to Marc Robert
    $post = $postController->getPostById($id_post);
    
    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post non trouvé']);
        exit;
    }
    
    // Get user info to verify it's Marc Robert
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT nom_utilisateur FROM Utilisateur WHERE id_user = ?");
    $stmt->execute([$post->getAuteurId()]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        exit;
    }
    
    // Check if user is Marc Robert (case-insensitive trim)
    $isMarcRobert = strtolower(trim($user['nom_utilisateur'])) === 'marc robert';
    
    if (!$isMarcRobert) {
        echo json_encode(['success' => false, 'message' => 'Seuls les posts de Marc Robert peuvent être modifiés']);
        exit;
    }
    
    // Update the post
    $post->setContenu($contenu);
    $postController->updatePost($post);
    
    echo json_encode(['success' => true, 'contenu' => nl2br(htmlspecialchars($contenu))]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

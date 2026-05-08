<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../model/post.php';
require_once '../controlers/postcontroller.php';

$contenu = $_POST['contenu'] ?? '';

if (trim($contenu) === '') {
    echo json_encode(['success' => false, 'message' => 'Contenu vide']);
    exit;
}

$media_url = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $filename = time() . '_' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        $media_url = 'uploads/' . $filename; // Relative to the document root
    }
}

$titre = trim($_POST['titre'] ?? '');
$type_post = $_POST['type_post'] ?? 'Article';

if (empty($titre) || mb_strlen($titre) < 5) {
    echo json_encode(['success' => false, 'message' => 'Le titre est obligatoire et doit faire au moins 5 caractères']);
    exit;
}

if (empty($contenu) || mb_strlen($contenu) < 5) {
    echo json_encode(['success' => false, 'message' => 'Le contenu doit faire au moins 5 caractères']);
    exit;
}
$user_id = 1; // Simulated user ID
$icon_ids = isset($_POST['icon_id']) ? (array)$_POST['icon_id'] : [];

$post = new Post($titre, $contenu, $media_url, $type_post, $user_id, $icon_ids);
$postController = new PostController();
$newPost = $postController->createPost($post);

echo json_encode([
    'success' => true
]);

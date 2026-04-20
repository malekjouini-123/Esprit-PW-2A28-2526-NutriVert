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
    $stmt = $pdo->prepare("DELETE FROM Reply WHERE id_reply = ?");
    $stmt->execute([$id_reply]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Commentaire non trouvé']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

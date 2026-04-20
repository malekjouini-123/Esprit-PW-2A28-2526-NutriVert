<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id_reply']) || !isset($data['commentaire'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$id_reply = (int)$data['id_reply'];
$commentaire = trim($data['commentaire']);

if ($commentaire === '') {
    echo json_encode(['success' => false, 'message' => 'Contenu vide']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE Reply SET commentaire = ? WHERE id_reply = ?");
    $stmt->execute([$commentaire, $id_reply]);
    echo json_encode(['success' => true, 'commentaire' => nl2br(htmlspecialchars($commentaire))]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Model/FaceRecognitionService.php';
require_once __DIR__ . '/../Database.php';

class AuthController {
    private $userModel;
    private $faceRecognition;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->faceRecognition = new FaceRecognitionService();
    }

    /** Authenticate a user with a webcam Face ID capture. */
    public function faceLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'match' => false, 'error' => 'Methode non autorisee.'], 405);
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $email = trim($payload['email'] ?? '');
        $capture = $payload['face_image'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'match' => false, 'error' => 'Email invalide.'], 422);
        }

        if (!$capture) {
            $this->jsonResponse(['success' => false, 'match' => false, 'error' => 'Capture Face ID manquante.'], 422);
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $this->jsonResponse(['success' => false, 'match' => false, 'error' => 'Aucun compte ne correspond a cet email.'], 404);
        }

        $storedEncoding = $this->userModel->getFaceEncoding($user['id_utilisateur']);
        $result = $this->faceRecognition->compareCapturedImage($capture, $storedEncoding, $user['id_utilisateur']);
        $data = $result['data'] ?? [];

        if (!$result['success'] || empty($data['match'])) {
            $this->jsonResponse([
                'success' => false,
                'match' => false,
                'confidence' => $data['confidence'] ?? 0,
                'error' => $data['error'] ?? 'Visage non reconnu.'
            ], 401);
        }

        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $this->userModel->logAuth($user['id_utilisateur'], 'face_id');

        $this->jsonResponse([
            'success' => true,
            'match' => true,
            'confidence' => $data['confidence'] ?? null,
            'user_id' => (int)$user['id_utilisateur'],
            'redirect' => $user['role'] === 'admin' ? 'index.php?action=admin' : 'index.php?action=profile'
        ]);
    }

    /** Return a JSON payload and stop request handling. */
    private function jsonResponse(array $payload, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}

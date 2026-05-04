<?php
require_once __DIR__ . '/../Model/ChatModel.php';
require_once __DIR__ . '/../Model/GeminiService.php';
require_once __DIR__ . '/../Database.php';

class ChatController {
    private $chatModel;
    private $geminiService;

    public function __construct() {
        $this->chatModel = new ChatModel();
        $this->geminiService = new GeminiService();
    }

    /** Show the user's AI assistant page. */
    public function index() {
        if (!isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }

        $chats = $this->chatModel->getUserChats($_SESSION['user_id']);
        include __DIR__ . '/../View/Frontoffice/chat.php';
    }

    /** Handle one chat message through Gemini and persist the exchange. */
    public function ask() {
        if (!isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'error' => 'Connexion requise.'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Methode non autorisee.'], 405);
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $message = trim($payload['message'] ?? '');
        if ($message === '' || strlen($message) > 2000) {
            $this->jsonResponse(['success' => false, 'error' => 'Message invalide.'], 422);
        }

        $history = $this->chatModel->getUserChats($_SESSION['user_id'], 8);
        $result = $this->geminiService->generateReply($message, $history);
        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 502);
        }

        $model = $result['model'] ?? $this->geminiService->getModel();
        $this->chatModel->saveChat($_SESSION['user_id'], $message, $result['reply'], $model);

        $this->jsonResponse([
            'success' => true,
            'reply' => $result['reply'],
            'model' => $model
        ]);
    }

    /** Show all AI chats to admins. */
    public function adminChats() {
        if (!isAdmin()) {
            header('Location: index.php');
            exit;
        }

        $chats = $this->chatModel->getAllChats();
        include __DIR__ . '/../View/Backoffice/chats.php';
    }

    /** Return a JSON response and stop request handling. */
    private function jsonResponse(array $payload, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}

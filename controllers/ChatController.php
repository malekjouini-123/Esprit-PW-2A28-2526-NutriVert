<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/ChatModel.php';
require_once __DIR__ . '/../models/GeminiService.php';

if (class_exists('ChatController')) return;

class ChatController
{
    private ChatModel     $chatModel;
    private GeminiService $geminiService;

    public function __construct()
    {
        $this->chatModel     = new ChatModel();
        $this->geminiService = new GeminiService();
    }

    public function handle(string $action): void
    {
        match ($action) {
            'index'      => $this->index(),
            'ask'        => $this->ask(),
            'adminChats' => $this->adminChats(),
            default      => $this->index(),
        };
    }

    private function index(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: index.php?view=login');
            exit;
        }

        $user  = $_SESSION['user'];
        $chats = $this->chatModel->getUserChats((int)$user['id']);
        include __DIR__ . '/../views/user/chatbot.php';
        exit;
    }

    private function ask(): void
    {
        if (empty($_SESSION['user'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Connexion requise.'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Méthode non autorisée.'], 405);
        }

        $payload = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $message = trim((string)($payload['message'] ?? ''));
        if ($message === '' || strlen($message) > 2000) {
            $this->jsonResponse(['success' => false, 'error' => 'Message invalide.'], 422);
        }

        $userId  = (int)$_SESSION['user']['id'];
        $history = $this->chatModel->getUserChats($userId, 8);
        $result  = $this->geminiService->generateReply($message, $history);

        if (!$result['success']) {
            $this->jsonResponse(['success' => false, 'error' => $result['error']], 502);
        }

        $model = $result['model'] ?? $this->geminiService->getModel();
        $this->chatModel->saveChat($userId, $message, $result['reply'], $model);

        $this->jsonResponse([
            'success' => true,
            'reply'   => $result['reply'],
            'model'   => $model,
        ]);
    }

    private function adminChats(): void
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }

        $chats = $this->chatModel->getAllChats();
        include __DIR__ . '/../views/admin/chats.php';
        exit;
    }

    private function jsonResponse(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}

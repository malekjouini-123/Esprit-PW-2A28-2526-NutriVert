<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/ChatModel.php';
require_once __DIR__ . '/../Model/AIChatService.php';
require_once __DIR__ . '/../Model/ChatbotService.php';

if (class_exists('ChatController')) return;

class ChatController
{
    private ChatModel      $chatModel;
    private AIChatService  $aiService;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $aiConfig        = is_file(__DIR__ . '/../../config/ai.php')
            ? require __DIR__ . '/../../config/ai.php'
            : ['provider' => 'local'];
        $this->aiService = new AIChatService($aiConfig);
    }

    public function handle(string $action): void
    {
        match ($action) {
            'index'      => $this->index(),
            'message'    => $this->message(),
            'stream'     => $this->stream(),
            'reset'      => $this->reset(),
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

        $user     = $_SESSION['user'];
        $chats    = $this->chatModel->getUserChats((int)$user['id']);
        $provider = $this->aiService->getProvider();

        if (empty($_SESSION['chatbot'])) {
            $_SESSION['chatbot'] = [
                'phase'      => 'conversation',
                'history'    => [],
                'context'    => [],
                'created_at' => time(),
            ];
            $initialBotMessage = "Bonjour 👋 Je suis votre assistant coaching NutriVert. Comment puis-je vous aider aujourd'hui ?";
        } else {
            $initialBotMessage = null;
        }

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        include __DIR__ . '/../View/chatbot.php';
        exit;
    }

    private function message(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'Connexion requise.']);
            exit;
        }

        $message = trim((string)($_POST['message'] ?? ''));
        if ($message === '' || strlen($message) > 2000) {
            echo json_encode(['success' => false, 'bot' => 'Message invalide.']);
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];
        $state  = $_SESSION['chatbot'] ?? ['history' => [], 'phase' => 'conversation'];

        $result        = $this->aiService->generateReply($state, $message);
        $assistantText = trim($result['reply'] ?? "Je n'ai pas pu répondre pour le moment.");

        $state['history'][] = ['role' => 'user',     'content' => $message];
        $state['history'][] = ['role' => 'assistant', 'content' => $assistantText];
        if (count($state['history']) > 20) {
            $state['history'] = array_slice($state['history'], -20);
        }
        $_SESSION['chatbot'] = $state;

        $this->chatModel->saveChat($userId, $message, $assistantText, $result['provider'] ?? $this->aiService->getProvider());

        echo json_encode([
            'success'  => true,
            'bot'      => $assistantText,
            'provider' => $result['provider'] ?? $this->aiService->getProvider(),
        ]);
        exit;
    }

    private function stream(): void
    {
        if (empty($_SESSION['user'])) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $message = trim((string)($_GET['message'] ?? ''));
        if ($message === '') {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        $state  = $_SESSION['chatbot'] ?? ['history' => []];
        $prompt = $this->aiService->buildOllamaPromptPublic($state, $message);
        $url    = $this->aiService->getOllamaApiUrl();
        $model  = $this->aiService->getOllamaModel();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        $payload = json_encode([
            'model'       => $model,
            'prompt'      => $prompt,
            'temperature' => 0.7,
            'stream'      => true,
        ], JSON_UNESCAPED_UNICODE);

        $buffer      = '';
        $partialLine = '';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$buffer, &$partialLine) {
            $data        = $partialLine . $data;
            $partialLine = '';
            $lines       = explode("\n", $data);
            $lastLine    = array_pop($lines);
            if ($lastLine !== '') {
                $partialLine = $lastLine;
            }
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $decoded = json_decode($line, true);
                if (!is_array($decoded)) continue;
                $chunk = $decoded['response'] ?? '';
                if ($chunk === '') continue;
                $safe = str_replace("\n", "\\n", $chunk);
                echo "data: $safe\n\n";
                @ob_flush();
                @flush();
                $buffer .= $chunk;
            }
            return strlen($data);
        });

        curl_exec($ch);
        curl_close($ch);

        $assistantText = trim($buffer);
        if ($assistantText !== '') {
            $state['history'][] = ['role' => 'assistant', 'content' => $assistantText];
            if (count($state['history']) > 20) {
                $state['history'] = array_slice($state['history'], -20);
            }
            $_SESSION['chatbot'] = $state;
            $this->chatModel->saveChat((int)$_SESSION['user']['id'], $message, $assistantText, 'ollama_api');
        }

        echo "event: done\ndata: " . json_encode(['provider' => 'ollama_api']) . "\n\n";
        exit;
    }

    private function reset(): void
    {
        unset($_SESSION['chatbot']);
        header('Location: index.php?controller=chat&action=index');
        exit;
    }

    private function adminChats(): void
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }

        $chats = $this->chatModel->getAllChats();
        include __DIR__ . '/../../admin/View/chats.php';
        exit;
    }
}

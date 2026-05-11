<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/env.php';

if (class_exists('GeminiService')) return;

class GeminiService {
    private string $apiKey;
    private string $model;
    private string $examplePrompt;

    public function __construct() {
        $this->apiKey        = getenv_safe('GEMINI_API', '');
        $this->model         = getenv_safe('AI_MODEL', 'gemini-2.5-flash-lite');
        $this->examplePrompt = $this->loadExamplePrompt();
    }

    public function generateReply(string $message, array $history = []): array {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'Clé API Gemini manquante dans config/.env.'];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => "L'extension PHP cURL est requise."];
        }

        $endpoint     = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($this->model) . ':generateContent';
        $systemPrompt = "Tu es l'assistant IA de NutriVert. Réponds toujours en français, avec un ton clair, utile et bienveillant. "
            . "Tu DOIS répondre uniquement aux questions directement liées à l'application NutriVert (fonctionnalités, utilisation, "
            . "comptes, coaching, exercices, nutrition, chat, reconnaissance faciale, configuration, emails, paramètres, dépannage). "
            . "Si la question n'est PAS liée à NutriVert, réponds uniquement : "
            . "'Désolé, je ne peux répondre qu'aux questions concernant l'application NutriVert.' "
            . "Ne fournis pas d'avis médical ni de diagnostic ; pour les sujets de santé sensibles, indique de consulter un professionnel. "
            . "Inspire-toi de cet exemple : " . $this->examplePrompt;

        $contents = [
            ['role' => 'user',  'parts' => [['text' => $systemPrompt]]],
            ['role' => 'model', 'parts' => [['text' => 'Compris. Je répondrai en français comme assistant NutriVert.']]],
        ];

        foreach ($history as $chat) {
            $contents[] = ['role' => 'user',  'parts' => [['text' => $chat['user_message']]]];
            $contents[] = ['role' => 'model', 'parts' => [['text' => $chat['ai_response']]]];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $payload = (string)json_encode([
            'contents'        => $contents,
            'generationConfig' => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 700,
            ],
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT    => 30,
        ]);

        $raw       = (string)curl_exec($ch);
        $curlError = curl_error($ch);
        $status    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$raw) {
            return ['success' => false, 'error' => 'Erreur réseau Gemini: ' . $curlError];
        }

        $data = json_decode($raw, true);
        if ($status >= 400) {
            $errMsg = is_array($data) ? ($data['error']['message'] ?? 'Erreur Gemini inconnue.') : 'Erreur Gemini inconnue.';
            return ['success' => false, 'error' => $errMsg];
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (!$text) {
            return ['success' => false, 'error' => "Gemini n'a pas retourné de réponse exploitable."];
        }

        return ['success' => true, 'reply' => trim($text), 'model' => $this->model];
    }

    public function getModel(): string {
        return $this->model;
    }

    private function loadExamplePrompt(): string {
        $path = __DIR__ . '/../../config/example_ai.txt';
        if (!is_file($path)) {
            return '';
        }
        return trim((string)file_get_contents($path));
    }
}

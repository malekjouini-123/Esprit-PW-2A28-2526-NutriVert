<?php
require_once __DIR__ . '/../config/env.php';

class GeminiService {
    private $apiKey;
    private $model;
    private $examplePrompt;

    public function __construct() {
        $this->apiKey = getenv_safe('GEMINI_API', '');
        $this->model = getenv_safe('AI_MODEL', 'gemini-2.5-flash-lite');
        $this->examplePrompt = $this->loadExamplePrompt();
    }

    /** Ask Gemini to answer as the NutriVert assistant in French. */
    public function generateReply($message, array $history = []) {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'Cle API Gemini manquante dans .env.'];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => "L'extension PHP cURL est requise pour appeler Gemini."];
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($this->model) . ':generateContent';
        $systemPrompt = "Tu es l'assistant IA de NutriVert. Reponds toujours en francais, avec un ton clair, utile et bienveillant. Aide l'utilisateur sur la nutrition, les recettes, le mode de vie durable et l'utilisation de l'application. Ne donne pas de diagnostic medical et conseille de consulter un professionnel pour les sujets de sante sensibles. Inspire-toi de cet exemple d'appel API si utile: " . $this->examplePrompt;

        $contents = [
            [
                'role' => 'user',
                'parts' => [['text' => $systemPrompt]]
            ],
            [
                'role' => 'model',
                'parts' => [['text' => "Compris. Je repondrai en francais comme assistant NutriVert."]]
            ]
        ];

        foreach ($history as $chat) {
            $contents[] = ['role' => 'user', 'parts' => [['text' => $chat['user_message']]]];
            $contents[] = ['role' => 'model', 'parts' => [['text' => $chat['ai_response']]]];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $payload = json_encode([
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 700
            ]
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30
        ]);

        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return ['success' => false, 'error' => 'Erreur reseau Gemini: ' . $curlError];
        }

        $data = json_decode($raw, true);
        if ($status >= 400) {
            $message = $data['error']['message'] ?? 'Erreur Gemini inconnue.';
            return ['success' => false, 'error' => $message];
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (!$text) {
            return ['success' => false, 'error' => 'Gemini n/a pas retourne de reponse exploitable.'];
        }

        return ['success' => true, 'reply' => trim($text), 'model' => $this->model];
    }

    /** Return the configured Gemini model name. */
    public function getModel() {
        return $this->model;
    }

    /** Load the local API example requested by the project. */
    private function loadExamplePrompt() {
        $path = __DIR__ . '/../example_ai.txt';
        if (!is_file($path)) {
            return '';
        }

        return trim(file_get_contents($path));
    }
}

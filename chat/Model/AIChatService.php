<?php
declare(strict_types=1);

class AIChatService
{
    private string $provider;
    private string $apiKey;
    private string $model;
    private string $ollamaPath;
    private string $ollamaApiUrl;
    private string $ollamaModel;
    private float $temperature;
    private int $maxTokens;

    public function __construct(array $config)
    {
        $this->provider = trim((string)($config['provider'] ?? 'openai'));
        $this->apiKey = trim((string)($config['openai_api_key'] ?? ''));
        $this->ollamaPath = trim((string)($config['ollama_path'] ?? 'ollama'));
        $this->ollamaApiUrl = trim((string)($config['ollama_api_url'] ?? 'http://localhost:11434/api/generate'));
        $this->ollamaModel = trim((string)($config['ollama_model'] ?? 'llama2:latest'));
        $this->model = trim((string)($config['model'] ?? 'gpt-3.5-turbo'));
        $this->temperature = isset($config['temperature']) ? (float)$config['temperature'] : 0.7;
        $this->maxTokens = isset($config['max_tokens']) ? (int)$config['max_tokens'] : 800;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getOllamaApiUrl(): string
    {
        return $this->ollamaApiUrl;
    }

    public function getOllamaModel(): string
    {
        return $this->ollamaModel;
    }

    public function buildOllamaPromptPublic(array $state, string $message): string
    {
        return $this->buildOllamaPrompt($state, $message);
    }

    public function generateReply(array $state, string $message): array
    {
        // PRIORITY: Try AI provider (Ollama or OpenAI). Only fallback to local rules if AI fails.
        if ($this->provider === 'ollama_api') {
            $res = $this->generateOllamaAPIReply($state, $message);
            if (!empty($res['reply']) && $res['reply'] !== '') {
                $res['provider'] = 'ollama_api';
                return $res;
            }
        } elseif ($this->provider === 'ollama') {
            $res = $this->generateOllamaReply($state, $message);
            if (!empty($res['reply']) && $res['reply'] !== '') {
                $res['provider'] = 'ollama';
                return $res;
            }
        } elseif ($this->provider === 'openai') {
            $res = $this->generateOpenAIReply($state, $message);
            if (!empty($res['reply']) && $res['reply'] !== '') {
                $res['provider'] = 'openai';
                return $res;
            }
        }

        // FALLBACK: Only if AI failed or provider is 'local'
        $res = $this->generateLocalReply($state, $message);
        $res['provider'] = 'local';
        return $res;
    }

    /**
     * Simple fallback response for when AI is unavailable.
     * This is minimal intentionally - AI should be the primary responder.
     */
    private function generateSimpleFallback(string $message): string
    {
        $isEnglish = preg_match('/\b(i|you|what|how|when|where|why|help|question|can)\b/i', $message);
        $isSpanish = preg_match('/\b(yo|tÃº|quÃ©|cÃ³mo|cuÃ¡ndo|dÃ³nde|por quÃ©|ayuda|pregunta|puede)\b/i', $message);

        if ($isEnglish) {
            return "I'm temporarily offline. Please try again in a moment.";
        } elseif ($isSpanish) {
            return "Estoy temporalmente sin conexiÃ³n. Intenta de nuevo en un momento.";
        }

        return "Je suis temporairement hors ligne. RÃ©essayez dans un instant.";
    }

    /**
     * Local fallback reply - used ONLY when AI is unavailable.
     * Kept minimal intentionally to encourage AI usage.
     */
    private function generateLocalReply(array $state, string $message): array
    {
        $reply = $this->generateSimpleFallback($message);
        return ['reply' => $reply];
    }

    private function extractPersonalInfo(string $message): array
    {
        $info = [
            'name' => null,
            'age' => null,
            'height' => null,
            'weight' => null,
        ];

        // Extraction du nom (aprÃ¨s "je suis", "m'appelle", "nom", etc.)
        if (preg_match('/(je suis|m\'appelle|c\'est|nom:?|appel[Ã©e])\s+([a-zÃ Ã¢Ã¤Ã©Ã¨ÃªÃ«Ã¯Ã®Ã´Ã¶Ã¹Ã»Ã¼Å“Ã¦Ã§A-ZÃ€Ã‚Ã„Ã‰ÃˆÃŠÃ‹ÃÃŽÃ”Ã–Ã™Ã›ÃœÅ’Ã†Ã‡]+)/i', $message, $matches)) {
            $info['name'] = ucfirst(strtolower($matches[2]));
        }

        // Extraction de l'Ã¢ge (nombre entre 10 et 100, souvent aprÃ¨s "ans", "age", "j'ai")
        if (preg_match('/(?:j\'ai|age|Ã¢ge|ans:?)\s*(\d{1,3})(?:\s*ans)?/i', $message, $matches)) {
            $age = (int)$matches[1];
            if ($age >= 10 && $age <= 120) {
                $info['age'] = $age;
            }
        }

        // Extraction de la taille en cm (nombre suivi de cm ou aprÃ¨s "taille")
        if (preg_match('/(\d{2,3})\s*cm(?:\s|$|,)/i', $message, $matches)) {
            $height = (int)$matches[1];
            if ($height >= 140 && $height <= 250) {
                $info['height'] = $height;
            }
        }

        // Extraction du poids en kg (nombre suivi de kg ou aprÃ¨s "poids")
        if (preg_match('/(\d{1,3})\s*kg(?:\s|$|,)/i', $message, $matches)) {
            $weight = (int)$matches[1];
            if ($weight >= 30 && $weight <= 250) {
                $info['weight'] = $weight;
            }
        }

        return $info;
    }

    private function buildPersonalInfoResponse(array $info): string
    {
        $parts = [];
        
        if (!empty($info['name'])) {
            $parts[] = "EnchantÃ©, {$info['name']}! ðŸ‘‹";
        }

        if (!empty($info['age']) || !empty($info['height']) || !empty($info['weight'])) {
            $parts[] = "Merci pour ces informations!";
            
            $stats = [];
            if (!empty($info['age'])) {
                $stats[] = "Ã‚ge: {$info['age']} ans";
            }
            if (!empty($info['height'])) {
                $stats[] = "Taille: {$info['height']} cm";
            }
            if (!empty($info['weight'])) {
                $stats[] = "Poids: {$info['weight']} kg";
            }
            
            if (!empty($stats)) {
                $parts[] = "ðŸ“Š Tes infos: " . implode(" â€¢ ", $stats);
            }
            
            // Calcul de l'IMC si on a poids et taille
            if (!empty($info['height']) && !empty($info['weight'])) {
                $heightM = $info['height'] / 100;
                $imc = round($info['weight'] / ($heightM * $heightM), 1);
                $imcCategory = $this->getIMCCategory($imc);
                $parts[] = "ðŸ’ª IMC: {$imc} ({$imcCategory})";
            }
        }

        $parts[] = "\nDonne-moi ton besoin exact et je te rÃ©pondrai directement.";

        return implode("\n", $parts) . "\n\nâœ¨ *Assistant alimentÃ© par Ollama*";
    }

    private function getIMCCategory(float $imc): string
    {
        if ($imc < 18.5) {
            return "Insuffisant (< 18.5)";
        } elseif ($imc < 25) {
            return "Normal (18.5-25)";
        } elseif ($imc < 30) {
            return "Surpoids (25-30)";
        } else {
            return "ObÃ©sitÃ© (> 30)";
        }
    }

    private function selectExercise(array $history): string
    {
        $historyText = implode(' ', array_column($history, 'content'));
        
        if (preg_match('/(perte|perdre|poids|cardio)/i', $historyText)) {
            return 'Marche rapide';
        } elseif (preg_match('/(muscle|musculaire|force|gain)/i', $historyText)) {
            return 'Pompes';
        } elseif (preg_match('/(abdos|ventre|core)/i', $historyText)) {
            return 'Crunch';
        } elseif (preg_match('/(jambes|legs)/i', $historyText)) {
            return 'Squats';
        }
        
        return 'Ã‰chauffement lÃ©ger';
    }

    private function getExerciseDescription(string $exerciseName): string
    {
        $descriptions = [
            'Marche rapide' => "**Marche rapide**\nTechnique: Marche Ã  rythme soutenu (150+ pas/min)\nDurÃ©e: 20 minutes\nIntensitÃ©: ModÃ©rÃ©e (tu dois pouvoir parler)\nBÃ©nÃ©fices: Cardio, endurance, bien-Ãªtre",
            'Pompes' => "**Pompes classiques**\nTechnique: Corps tendu, mains Ã  largeur Ã©paules\nRÃ©pÃ©titions: 3 x 10-15\nRepos: 60 secondes\nBÃ©nÃ©fices: Pectoraux, Ã©paules, triceps",
            'Crunch' => "**Crunch (abdominaux)**\nTechnique: AllongÃ©, genoux flÃ©chis, relÃ¨ve le buste\nRÃ©pÃ©titions: 3 x 15-20\nRepos: 30 secondes\nBÃ©nÃ©fices: Abdominaux, core",
            'Squats' => "**Squats (jambes)**\nTechnique: Pieds Ã©cartÃ©s, descend en pliant genoux\nRÃ©pÃ©titions: 3 x 15\nRepos: 45 secondes\nBÃ©nÃ©fices: Jambes, fessiers, Ã©quilibre",
            'Ã‰chauffement lÃ©ger' => "**Ã‰chauffement**\nTechnique: LÃ©ger cardio et Ã©tirements\nDurÃ©e: 5-10 minutes\nIntensitÃ©: Faible\nBÃ©nÃ©fices: PrÃ©pare le corps, rÃ©duit les risques",
        ];
        
        return $descriptions[$exerciseName] ?? "Exercice prÃªt!";
    }

    private function generateOllamaReply(array $state, string $message): array
    {
        $prompt = $this->buildOllamaPrompt($state, $message);
        $response = $this->runOllama($prompt);

        // Return as-is; if null, let generateReply() handle fallback
        if ($response === null || $response === '') {
            return ['reply' => ''];
        }

        return ['reply' => $response];
    }

    private function generateOllamaAPIReply(array $state, string $message): array
    {
        $prompt = $this->buildOllamaPrompt($state, $message);
        $response = $this->callOllamaApi($prompt);

        // Return response as-is; if null/empty, let generateReply() handle fallback
        if ($response === null || $response === '') {
            return ['reply' => ''];
        }

        return ['reply' => $response];
    }

    private function callOllamaApi(string $prompt): ?string
    {
        $payload = [
            'model' => $this->ollamaModel,
            'prompt' => $prompt,
            'temperature' => $this->temperature,
            'stream' => false,
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($jsonPayload === false) {
            return null;
        }

        $raw = null;

        if (function_exists('curl_version')) {
            $raw = $this->callOllamaApiWithCurl($jsonPayload);
        }

        if ($raw === null || $raw === '') {
            $raw = $this->callOllamaApiWithHttp($jsonPayload);
        }

        if ($raw === null || $raw === '') {
            return null;
        }

        $responseData = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $parsed = $this->parseOllamaApiResponse($responseData);
            if ($parsed !== null && $parsed !== '') {
                return trim($parsed);
            }
        }

        $parsedStream = $this->parseOllamaNdjsonResponse($raw);
        if ($parsedStream !== null && $parsedStream !== '') {
            return trim($parsedStream);
        }

        return trim($raw);
    }

    private function callOllamaApiWithCurl(string $jsonPayload): ?string
    {
        $ch = curl_init($this->ollamaApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        if (defined('CURLOPT_IPRESOLVE')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $httpCode >= 400) {
            return null;
        }

        return trim($raw);
    }

    private function callOllamaApiWithHttp(string $jsonPayload): ?string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Accept: application/json\r\nContent-Type: application/json",
                'content' => $jsonPayload,
                'timeout' => 60,
            ],
        ]);

        $raw = @file_get_contents($this->ollamaApiUrl, false, $context);
        if ($raw === false) {
            return null;
        }

        return trim($raw);
    }

    private function parseOllamaApiResponse(array $responseData): ?string
    {
        if (isset($responseData['choices'][0]['text'])) {
            return $responseData['choices'][0]['text'];
        }

        if (isset($responseData['choices'][0]['message']['content'])) {
            return $responseData['choices'][0]['message']['content'];
        }

        if (isset($responseData['results'][0]['content'])) {
            return $responseData['results'][0]['content'];
        }

        if (isset($responseData['response'])) {
            return $responseData['response'];
        }

        if (isset($responseData['text'])) {
            return $responseData['text'];
        }

        if (isset($responseData['output']) && is_string($responseData['output'])) {
            return $responseData['output'];
        }

        if (isset($responseData['results']) && is_string($responseData['results'])) {
            return $responseData['results'];
        }

        return null;
    }

    private function parseOllamaNdjsonResponse(string $raw): ?string
    {
        $lines = preg_split('/\r?\n/', trim($raw));
        if ($lines === false || count($lines) === 0) {
            return null;
        }

        $content = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $item = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($item)) {
                continue;
            }

            $chunk = $this->parseOllamaApiResponse($item);
            if ($chunk !== null) {
                $content .= $chunk;
            }
        }

        return $content === '' ? null : $content;
    }

    private function buildOllamaPrompt(array $state, string $message): string
    {
        $prompt = $this->systemPrompt();
        
        // Add conversation history (last 5 exchanges to stay concise)
        $history = $state['history'] ?? [];
        $history = array_slice($history, -10); // Max 5 exchanges = 10 messages
        
        if (!empty($history)) {
            $prompt .= "\n\nRecent conversation:\n";
            foreach ($history as $item) {
                if (!isset($item['role'], $item['content'])) {
                    continue;
                }
                $role = $item['role'] === 'assistant' ? 'Assistant' : 'User';
                $content = trim($item['content']);
                // Truncate long messages to ~100 chars for context
                if (strlen($content) > 100) {
                    $content = substr($content, 0, 100) . '...';
                }
                $prompt .= $role . ": $content\n";
            }
        }
        
        $prompt .= "\nUser: " . trim($message) . "\nAssistant:";
        return $prompt;
    }

    private function runOllama(string $prompt): ?string
    {
        $binary = $this->ollamaPath;
        $model = $this->ollamaModel;
        $command = [$binary, 'run', $model, '--nowordwrap', '--hidethinking'];

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorspec, $pipes);
        if (!is_resource($process)) {
            return null;
        }

        fwrite($pipes[0], $prompt);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $status = proc_close($process);
        if ($output === false || $output === '' || $status !== 0) {
            return null;
        }

        return trim($this->cleanOllamaOutput($output));
    }

    private function cleanOllamaOutput(string $output): string
    {
        $output = preg_replace('/\x1B\[[0-9;]*[A-Za-z]/', '', $output);
        $output = str_replace(["\r", "\x00"], '', $output);
        return trim($output);
    }

    private function buildMessages(array $state, string $message): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt(),
            ],
        ];

        foreach ($state['history'] ?? [] as $item) {
            if (!isset($item['role'], $item['content'])) {
                continue;
            }

            $messages[] = [
                'role' => $item['role'],
                'content' => $item['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        return $messages;
    }

    private function generateOpenAIReply(array $state, string $message): array
    {
        $messages = $this->buildMessages($state, $message);
        $response = $this->callOpenAI($messages);

        // Return as-is; if null, let generateReply() handle fallback
        if ($response === null) {
            return ['reply' => ''];
        }

        return $this->parseAssistantResponse($response);
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Tu es un assistant intelligent spÃ©cialisÃ© UNIQUEMENT en coaching sportif, plans d'exercices, et nutrition de performance.

ðŸŽ¯ TON DOMAINE EXACT :
âœ… AUTORISÃ‰ :
- Plans d'exercices (cardio, musculation, Ã©tirements, yoga, HIIT, etc.)
- Conseils nutritionnels LIÃ‰S AU COACHING (calories pour perte/prise de poids, macros, alimentation de performance)
- Programmes d'entraÃ®nement progressifs
- Motivation et suivi de progression
- Conseils de santÃ©/bien-Ãªtre liÃ©s Ã  l'activitÃ© physique

âŒ INTERDIT :
- Recettes de cuisine gÃ©nÃ©rale (pizza, gÃ¢teaux, plats, etc.)
- Consultations mÃ©dicales sÃ©rieuses
- Nutrition sans lien au coaching
- Sujets hors sport/bien-Ãªtre (politique, finance, divertissement, etc.)

ðŸ“Œ COMPORTEMENT OBLIGATOIRE :
- Ton motivant, clair, professionnel ET amical
- Emojis pertinents pour amÃ©liorer l'expÃ©rience ðŸ˜ŠðŸ’ªðŸ”¥ðŸŽ¯
- Propose TOUJOURS des actions concrÃ¨tes (exercices, plans, conseils pratiques)
- RÃ©ponds en FRANÃ‡AIS principalement

ðŸš« REFUS STRICT - TRÃˆS IMPORTANT :
Si la question N'EST PAS du coaching/exercices/nutrition de performance, rÃ©ponds EXACTEMENT :
"ðŸ‘‰ DÃ©solÃ©, je suis spÃ©cialisÃ© uniquement en coaching sportif et bien-Ãªtre ðŸ˜Š"

AUCUNE exception. Pas de "petite rÃ©ponse rapide". Refus total.

âš¡ FONCTIONNALITÃ‰S :
- Programme complet demandÃ© â†’ plan exercices + nutrition + timing
- Coaching commence â†’ guide Ã©tape par Ã©tape
- Progression partielle â†’ encourage et propose suite
- Sois coach intelligent, interactif et bienveillant

ðŸ’¬ EXEMPLES :
"ðŸ’ª Super ! Voici ton programme pour aujourd'hui..."
"ðŸ”¥ Continue, tu progresses trÃ¨s bien !"
"ðŸŽ¯ Objectif : 10 pompes, puis repos"
"âœ… Excellent ! Passons Ã  l'Ã©tape suivante..."

â±ï¸ CLARTÃ‰ :
- RÃ©ponses claires et utiles (max 150 mots sauf programmes)
- Pas de bavardage
PROMPT;
    }

    private function callOpenAI(array $messages): ?string
    {
        $payload = json_encode([
            'model' => $this->model,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'messages' => $messages,
        ]);

        if ($payload === false) {
            return null;
        }

        $curl = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($response === false || $err !== '') {
            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['choices'][0]['message']['content'])) {
            return null;
        }

        return trim((string)$data['choices'][0]['message']['content']);
    }

    private function parseAssistantResponse(string $content): array
    {
        $reply = $content;
        $jsonPayload = $this->extractJsonBlock($content);

        if ($jsonPayload !== null) {
            $decoded = json_decode($jsonPayload, true);
            if (is_array($decoded)) {
                $reply = trim(preg_replace('/###AI_OUTPUT###.*?###END_AI_OUTPUT###/s', '', $content));
                if ($reply === '') {
                    $reply = trim((string)($decoded['reply'] ?? ''));
                }

                $result = ['reply' => $reply];
                foreach (['status', 'exercise', 'plan', 'coaching_id', 'progress'] as $key) {
                    if (array_key_exists($key, $decoded)) {
                        $result[$key] = $decoded[$key];
                    }
                }

                return $result;
            }
        }

        return ['reply' => $reply];
    }

    private function extractJsonBlock(string $content): ?string
    {
        $start = strpos($content, '###AI_OUTPUT###');
        $end = strpos($content, '###END_AI_OUTPUT###');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $jsonText = substr($content, $start + strlen('###AI_OUTPUT###'), $end - $start - strlen('###AI_OUTPUT###'));
        return trim($jsonText);
    }
}

<?php

class AiRecipeService
{
    private string $apiKey;
    private string $model;

    public function __construct(array $config)
    {
        $this->apiKey = trim((string) ($config['openai_api_key'] ?? ''));
        $this->model = trim((string) ($config['openai_model'] ?? 'gpt-4o-mini'));
    }

    public function generate(string $regime, array $ingredients): array
    {
        if ($this->apiKey !== '' && function_exists('curl_init')) {
            try {
                return $this->generateWithOpenAi($regime, $ingredients);
            } catch (Throwable $e) {
                return $this->generateFallback($regime, $ingredients, 'IA locale utilisée car API indisponible.');
            }
        }

        return $this->generateFallback($regime, $ingredients, 'IA locale utilisée. Ajoute OPENAI_API_KEY pour une vraie génération API.');
    }

    private function generateWithOpenAi(string $regime, array $ingredients): array
    {
        $prompt = [
            'regime' => $regime !== '' ? $regime : 'Libre',
            'ingredients' => array_values($ingredients),
            'format_obligatoire' => [
                'titre' => 'string, max 50 chars',
                'objectif' => 'string, max 100 chars',
                'regime' => 'one of: Végétarien, Végan, Sans gluten, Protéiné, Faible en calories',
                'duree' => 'integer minutes between 5 and 120',
                'instructions' => [
                    [
                        'etape' => 'string',
                        'description' => 'string',
                        'ingredients' => [
                            ['nom_produit' => 'string', 'quantite' => 'string', 'image' => 'string or empty']
                        ]
                    ]
                ]
            ]
        ];

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un assistant cuisine pour NutriVert. Réponds uniquement avec un JSON valide, sans markdown. La recette doit utiliser les ingrédients donnés.'
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($prompt, JSON_UNESCAPED_UNICODE)
                ]
            ],
            'temperature' => 0.8,
            'response_format' => ['type' => 'json_object']
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 35,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            throw new RuntimeException($error !== '' ? $error : 'OpenAI HTTP ' . $status);
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? '';
        $recipe = json_decode($content, true);

        if (!is_array($recipe)) {
            throw new RuntimeException('Réponse IA invalide.');
        }

        return $this->normalizeRecipe($recipe, $regime, $ingredients, 'Recette générée par IA.');
    }

    private function generateFallback(string $regime, array $ingredients, string $note): array
    {
        $main = $ingredients[0] ?? 'légumes';
        $second = $ingredients[1] ?? 'herbes';
        $selectedRegime = $this->normalizeRegime($regime);

        $recipe = [
            'titre' => mb_substr('Bol NutriVert ' . ucfirst($main), 0, 50),
            'objectif' => 'Recette saine anti-gaspillage',
            'regime' => $selectedRegime,
            'duree' => 25,
            'instructions' => [
                [
                    'etape' => 'Préparer les ingrédients',
                    'description' => 'Laver et couper : ' . implode(', ', $ingredients) . '.',
                    'ingredients' => $this->mapIngredients($ingredients, '1 portion')
                ],
                [
                    'etape' => 'Cuisson rapide',
                    'description' => 'Faire cuire ' . $main . ' avec un filet d’huile et assaisonner selon le goût.',
                    'ingredients' => $this->mapIngredients([$main, $second], 'selon besoin')
                ],
                [
                    'etape' => 'Dressage',
                    'description' => 'Servir chaud ou tiède avec une présentation simple et équilibrée.',
                    'ingredients' => $this->mapIngredients($ingredients, 'selon goût')
                ],
            ],
            'note' => $note,
        ];

        return $this->normalizeRecipe($recipe, $regime, $ingredients, $note);
    }

    private function normalizeRecipe(array $recipe, string $regime, array $ingredients, string $note): array
    {
        $titre = trim((string) ($recipe['titre'] ?? 'Recette NutriVert IA'));
        $objectif = trim((string) ($recipe['objectif'] ?? 'Recette saine anti-gaspillage'));
        $duree = (int) ($recipe['duree'] ?? 25);
        $instructions = $recipe['instructions'] ?? [];

        if ($titre === '') {
            $titre = 'Recette NutriVert IA';
        }
        if ($objectif === '') {
            $objectif = 'Recette saine anti-gaspillage';
        }
        if ($duree <= 0) {
            $duree = 25;
        }
        if ($duree > 120) {
            $duree = 120;
        }
        if (!is_array($instructions) || count($instructions) === 0) {
            $instructions = $this->generateFallback($regime, $ingredients, $note)['instructions'];
        }

        $normalizedInstructions = [];
        foreach ($instructions as $index => $instruction) {
            if (!is_array($instruction)) {
                continue;
            }

            $stepIngredients = $instruction['ingredients'] ?? $instruction['ingredient_produit'] ?? [];
            if (!is_array($stepIngredients) || count($stepIngredients) === 0) {
                $stepIngredients = $this->mapIngredients($ingredients, 'selon besoin');
            }

            $normalizedInstructions[] = [
                'etape' => trim((string) ($instruction['etape'] ?? 'Étape ' . ($index + 1))),
                'description' => trim((string) ($instruction['description'] ?? 'Préparer cette étape.')),
                'ingredients' => $this->normalizeIngredients($stepIngredients),
            ];
        }

        return [
            'titre' => mb_substr($titre, 0, 50),
            'objectif' => mb_substr($objectif, 0, 100),
            'regime' => $this->normalizeRegime((string) ($recipe['regime'] ?? $regime)),
            'duree' => $duree,
            'instructions' => $normalizedInstructions,
            'note' => $note,
        ];
    }

    private function normalizeRegime(string $regime): string
    {
        $allowed = ['Végétarien', 'Végan', 'Sans gluten', 'Protéiné', 'Faible en calories'];
        return in_array($regime, $allowed, true) ? $regime : 'Végétarien';
    }

    private function mapIngredients(array $ingredients, string $quantity): array
    {
        return array_map(function ($name) use ($quantity) {
            return [
                'nom_produit' => trim((string) $name),
                'quantite' => $quantity,
                'image' => 'https://placehold.co/60x60?text=' . urlencode(trim((string) $name)),
            ];
        }, array_values(array_filter($ingredients)));
    }

    private function normalizeIngredients(array $ingredients): array
    {
        $result = [];
        foreach ($ingredients as $ingredient) {
            if (is_string($ingredient)) {
                $name = trim($ingredient);
                $quantity = 'selon besoin';
                $image = 'https://placehold.co/60x60?text=' . urlencode($name);
            } else {
                $name = trim((string) ($ingredient['nom_produit'] ?? $ingredient['nom'] ?? 'Produit'));
                $quantity = trim((string) ($ingredient['quantite'] ?? 'selon besoin'));
                $image = trim((string) ($ingredient['image'] ?? ''));
            }

            if ($name === '') {
                continue;
            }
            if ($quantity === '') {
                $quantity = 'selon besoin';
            }
            if ($image === '') {
                $image = 'https://placehold.co/60x60?text=' . urlencode($name);
            }

            $result[] = [
                'nom_produit' => $name,
                'quantite' => $quantity,
                'image' => $image,
            ];
        }

        return $result;
    }

    public function translateTexts(string $targetLang, array $texts): array
    {
        $targetLang = $this->normalizeTargetLang($targetLang);
        $texts = array_values(array_filter(array_map('strval', $texts), function ($text) {
            return trim($text) !== '';
        }));
        $texts = array_values(array_unique($texts));

        if (empty($texts)) {
            return ['translations' => [], 'note' => ''];
        }

        if ($this->apiKey !== '' && function_exists('curl_init')) {
            try {
                return $this->translateWithOpenAi($targetLang, $texts);
            } catch (Throwable $e) {
                return $this->translateFallback($targetLang, $texts, 'Traduction locale utilisée car API indisponible.');
            }
        }

        return $this->translateFallback($targetLang, $texts, 'Traduction locale utilisée. Ajoute OPENAI_API_KEY pour une vraie traduction IA.');
    }

    private function translateWithOpenAi(string $targetLang, array $texts): array
    {
        $languageNames = [
            'fr' => 'French',
            'en' => 'English',
            'ar' => 'Arabic',
        ];

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un traducteur professionnel pour une application de recettes. Réponds uniquement avec un JSON valide: {"translations":{"texte original":"traduction"}}. Garde les nombres, les unités, les URLs et les noms NutriVert.'
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'target_language' => $languageNames[$targetLang] ?? 'French',
                        'texts' => $texts,
                    ], JSON_UNESCAPED_UNICODE)
                ],
            ],
            'temperature' => 0.2,
            'response_format' => ['type' => 'json_object'],
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 35,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            throw new RuntimeException($error !== '' ? $error : 'OpenAI HTTP ' . $status);
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? '';
        $json = json_decode($content, true);
        $translations = $json['translations'] ?? [];

        if (!is_array($translations)) {
            throw new RuntimeException('Réponse traduction IA invalide.');
        }

        return ['translations' => $translations, 'note' => 'Page traduite par IA.'];
    }

    private function translateFallback(string $targetLang, array $texts, string $note): array
    {
        $dictionary = [
            'en' => [
                'Végétarien' => 'Vegetarian', 'Végan' => 'Vegan', 'Sans gluten' => 'Gluten-free', 'Protéiné' => 'High protein', 'Faible en calories' => 'Low calorie',
                'Recette saine anti-gaspillage' => 'Healthy zero-waste recipe', 'Préparer les ingrédients' => 'Prepare the ingredients', 'Cuisson rapide' => 'Quick cooking', 'Dressage' => 'Plating',
                'selon besoin' => 'as needed', 'selon goût' => 'to taste', '1 portion' => '1 serving',
            ],
            'ar' => [
                'Végétarien' => 'نباتي', 'Végan' => 'نباتي صارم', 'Sans gluten' => 'بدون غلوتين', 'Protéiné' => 'غني بالبروتين', 'Faible en calories' => 'قليل السعرات',
                'Recette saine anti-gaspillage' => 'وصفة صحية بدون تبذير', 'Préparer les ingrédients' => 'تحضير المكونات', 'Cuisson rapide' => 'طبخ سريع', 'Dressage' => 'التقديم',
                'selon besoin' => 'حسب الحاجة', 'selon goût' => 'حسب الذوق', '1 portion' => 'حصة واحدة',
            ],
            'fr' => [],
        ];

        $translations = [];
        foreach ($texts as $text) {
            $translations[$text] = $dictionary[$targetLang][$text] ?? $this->simpleFallbackSentence($targetLang, $text);
        }

        return ['translations' => $translations, 'note' => $note];
    }

    private function simpleFallbackSentence(string $targetLang, string $text): string
    {
        if ($targetLang === 'fr') {
            return $text;
        }

        $replace = [
            'en' => [
                'Bol NutriVert' => 'NutriVert bowl', 'Laver et couper' => 'Wash and cut', 'Faire cuire' => 'Cook', 'Servir chaud ou tiède' => 'Serve hot or warm', 'avec' => 'with', 'et' => 'and', 'huile' => 'oil', 'tomate' => 'tomato', 'poulet' => 'chicken', 'quinoa' => 'quinoa',
            ],
            'ar' => [
                'Bol NutriVert' => 'طبق NutriVert', 'Laver et couper' => 'اغسل وقطّع', 'Faire cuire' => 'اطبخ', 'Servir chaud ou tiède' => 'قدّمها ساخنة أو دافئة', 'avec' => 'مع', 'et' => 'و', 'huile' => 'زيت', 'tomate' => 'طماطم', 'poulet' => 'دجاج', 'quinoa' => 'كينوا',
            ],
        ];

        return strtr($text, $replace[$targetLang] ?? []);
    }

    private function normalizeTargetLang(string $targetLang): string
    {
        return in_array($targetLang, ['fr', 'en', 'ar'], true) ? $targetLang : 'fr';
    }

}

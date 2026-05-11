<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/AIChatService.php';

if (class_exists('RecipeController')) return;

class RecipeController
{
    private AIChatService $aiService;

    public function __construct()
    {
        $aiConfig = is_file(__DIR__ . '/../../config/ai.php')
            ? require __DIR__ . '/../../config/ai.php'
            : ['provider' => 'local'];
        $this->aiService = new AIChatService($aiConfig);
    }

    public function handle(string $action): void
    {
        match ($action) {
            'generate' => $this->generateRecipe(),
            default    => $this->generateRecipe(),
        };
    }

    private function generateRecipe(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'Connexion requise.']);
            exit;
        }

        $ingredients = trim((string)($_POST['ingredients'] ?? ''));
        if ($ingredients === '' || strlen($ingredients) > 500) {
            echo json_encode(['success' => false, 'error' => 'Ingrédients invalides.']);
            exit;
        }

        $user = $_SESSION['user'];
        $objectif = $user['objectif'] ?? 'maintien';
        $imc = (float)($user['imc'] ?? 0);

        // Build context for AI
        $imcInterpret = $imc < 18.5 ? 'Insuffisance pondérale'
            : ($imc < 25 ? 'Poids normal'
            : ($imc < 30 ? 'Surpoids' : 'Obésité'));

        $prompt = $this->buildRecipePrompt(
            $user['nom'] ?? 'Utilisateur',
            $ingredients,
            $objectif,
            $imc > 0 ? $imcInterpret : null
        );

        // Call AI service
        $state = ['history' => [], 'context' => ['type' => 'recipe']];
        $result = $this->aiService->generateReply($state, $prompt);

        if (!empty($result['reply'])) {
            echo json_encode([
                'success' => true,
                'recipe' => $result['reply'],
                'provider' => $result['provider'] ?? 'local',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Impossible de générer la recette. Réessayez.',
            ]);
        }
        exit;
    }

    private function buildRecipePrompt(
        string $userName,
        string $ingredients,
        string $objectif,
        ?string $imcStatus
    ): string {
        $objectifText = match($objectif) {
            'perte' => 'une perte de poids',
            'muscle' => 'une prise de muscle',
            default => 'un maintien du poids',
        };

        $imcLine = $imcStatus ? " Profil santé : $imcStatus." : '';

        return "Tu es un chef cuisinier expert en nutrition. Génère une recette personnalisée COURTE (5-7 lignes max) pour $userName.

Ingrédients disponibles: $ingredients$imcLine

Objectif: $objectifText

Format de réponse:
🍽️ [Nom de la recette]
📝 Préparation: [étapes courtes]
⚡ Astuce nutrition: [conseil adapté à l'objectif]";
    }
}

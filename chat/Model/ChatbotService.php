<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

if (class_exists('ChatbotService')) return;

class ChatbotService
{
    private PDO $pdo;
    private array $config;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function buildPersonalizedPlan(string $objective, string $level, string $constraints, int $limit = 5): array
    {
        $difficulty = $this->mapLevelToDifficulty($level);
        $exercises = $this->selectExercisesByDifficulty($difficulty, $limit);

        if (empty($exercises)) {
            $exercises = $this->selectExercises($limit);
        }

        if (empty($exercises)) {
            $exercises = [$this->fallbackExercise()];
        }

        $selectedCount = count($exercises);
        $summary = "J'ai prÃ©parÃ© un plan personnalisÃ© de {$selectedCount} exercices pour votre objectif '" . htmlspecialchars($objective, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "'.\n";
        $summary .= "Niveau sÃ©lectionnÃ© : " . ucfirst($difficulty) . ".\n";
        $summary .= "Contraintes prises en compte : " . htmlspecialchars($constraints, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ".\n";
        $summary .= "Voici le premier exercice :";

        return [
            'plan' => $exercises,
            'summary' => $summary,
            'coaching_id' => (int)($exercises[0]['coaching_id'] ?? 0),
        ];
    }

    private function fallbackExercise(): array
    {
        return [
            'id' => 0,
            'coaching_id' => 0,
            'name' => 'Respiration consciente',
            'description' => 'Asseyez-vous confortablement, inspirez profondÃ©ment pendant 4 secondes, retenez pendant 4 secondes, puis expirez pendant 4 secondes. RÃ©pÃ©tez 5 fois.',
            'sets' => 1,
            'reps' => 1,
            'rest_time' => '30s',
            'video_url' => '',
            'image' => '',
            'enseigne' => '',
            'ordre' => 0,
            'duree_sec' => 30,
            'difficulty_level' => 'easy',
        ];
    }

    private function selectExercisesByDifficulty(string $difficulty, int $limit): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM exercises WHERE difficulty_level = :difficulty ORDER BY RAND() LIMIT :limit'
        );
        $stmt->bindValue(':difficulty', $difficulty, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function selectExercises(int $limit): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM exercises ORDER BY RAND() LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function mapLevelToDifficulty(string $level): string
    {
        $text = mb_strtolower(trim($level), 'UTF-8');

        if (str_contains($text, 'dÃ©butant') || str_contains($text, 'easy') || str_contains($text, 'facile')) {
            return 'easy';
        }

        if (str_contains($text, 'intermÃ©diaire') || str_contains($text, 'moyen') || str_contains($text, 'medium')) {
            return 'medium';
        }

        if (str_contains($text, 'avancÃ©') || str_contains($text, 'difficile') || str_contains($text, 'hard')) {
            return 'hard';
        }

        return 'medium';
    }
}


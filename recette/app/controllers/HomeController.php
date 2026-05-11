<?php
class HomeController extends BaseController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        $aiRecipeError = $_SESSION['ai_recipe_error'] ?? '';
        $aiRecipeSuccess = $_SESSION['ai_recipe_success'] ?? '';
        $aiRecipeNote = $_SESSION['ai_recipe_note'] ?? '';
        unset($_SESSION['ai_recipe_error'], $_SESSION['ai_recipe_success'], $_SESSION['ai_recipe_note']);

        $aiTargetLang = $this->normalizeAiLang($_GET['ai_translate'] ?? '');
        $aiTranslationMap = [];
        $aiTranslateNote = '';
        if ($aiTargetLang !== '') {
            $_SESSION['lang'] = $aiTargetLang;
        }

        $search = trim($_GET['search'] ?? '');
        $generateMode = isset($_GET['generer_recette']);
        $businessMode = trim($_GET['mode_metier'] ?? '');

        $selectedRegime = trim($_GET['regime_generate'] ?? '');
        $selectedIngredients = $this->cleanArray($_GET['ingredients_generate'] ?? []);
        $selectedSmartIngredients = $this->cleanArray($_GET['ingredients_suggestion'] ?? []);
        $selectedObjectif = trim($_GET['objectif_mode'] ?? '');

        $regimesAutorises = ['Végétarien', 'Végan', 'Sans gluten', 'Protéiné', 'Faible en calories'];
        if ($selectedRegime !== '' && !in_array($selectedRegime, $regimesAutorises, true)) {
            $selectedRegime = '';
        }

        $availableIngredients = $this->getAvailableIngredients();
        $availableObjectifs = $this->getAvailableObjectifs();

        $selectedIngredients = $this->keepOnlyExistingIngredients($selectedIngredients, $availableIngredients);
        $selectedSmartIngredients = $this->keepOnlyExistingIngredients($selectedSmartIngredients, $availableIngredients);

        if ($selectedObjectif !== '' && !in_array($selectedObjectif, $availableObjectifs, true)) {
            $selectedObjectif = '';
        }

        $resultTitle = '';

        if ($generateMode) {
            $recettes = !empty($selectedIngredients)
                ? $this->findRecettesByRegimeAndIngredients($selectedRegime, $selectedIngredients)
                : [];
            $resultTitle = 'Résultat Générer recette';
        } elseif ($businessMode === 'jour') {
            $recettes = $this->getRecetteDuJour();
            $resultTitle = 'Recette du jour';
        } elseif ($businessMode === 'rapides') {
            $recettes = $this->getRecettesRapides();
            $resultTitle = 'Recettes rapides';
        } elseif ($businessMode === 'suggestion') {
            $recettes = !empty($selectedSmartIngredients)
                ? $this->findRecettesByAnyIngredients($selectedSmartIngredients)
                : [];
            $resultTitle = 'Suggestion intelligente';
        } elseif ($businessMode === 'objectif') {
            $recettes = $selectedObjectif !== ''
                ? $this->findRecettesByObjectif($selectedObjectif)
                : [];
            $resultTitle = 'Mode objectif';
        } elseif ($search !== '') {
            $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE titre LIKE :titre ORDER BY id_recette DESC');
            $stmt->execute([':titre' => '%' . $search . '%']);
            $recettes = $stmt->fetchAll();
            $resultTitle = 'Recherche par titre';
        } else {
            $stmt = $this->pdo->query('SELECT * FROM recette ORDER BY id_recette DESC');
            $recettes = $stmt->fetchAll();
        }

        if ($aiTargetLang !== '') {
            $translationData = $this->translateHomeTexts($aiTargetLang, $recettes, $selectedObjectif, array_merge($selectedIngredients, $selectedSmartIngredients));
            $aiTranslationMap = $translationData['translations'];
            $aiTranslateNote = $translationData['note'];
        }

        $this->render('front/home', [
            'pageTitle' => 'NutriVert | FrontOffice',
            'recettes' => $recettes,
            'search' => $search,
            'availableIngredients' => $availableIngredients,
            'availableObjectifs' => $availableObjectifs,
            'selectedRegime' => $selectedRegime,
            'selectedIngredients' => $selectedIngredients,
            'selectedSmartIngredients' => $selectedSmartIngredients,
            'selectedObjectif' => $selectedObjectif,
            'generateMode' => $generateMode,
            'businessMode' => $businessMode,
            'resultTitle' => $resultTitle,
            'aiRecipeError' => $aiRecipeError,
            'aiRecipeSuccess' => $aiRecipeSuccess,
            'aiRecipeNote' => $aiRecipeNote,
            'aiTargetLang' => $aiTargetLang,
            'aiTranslationMap' => $aiTranslationMap,
            'aiTranslateNote' => $aiTranslateNote,
        ]);
    }

    private function normalizeAiLang(string $lang): string
    {
        $lang = trim($lang);
        return in_array($lang, ['fr', 'en', 'ar'], true) ? $lang : '';
    }

    private function translateHomeTexts(string $targetLang, array $recettes, string $selectedObjectif, array $selectedIngredients): array
    {
        $texts = [];
        foreach ($recettes as $recette) {
            $texts[] = $recette['titre'] ?? '';
            $texts[] = $recette['objectif'] ?? '';
            $texts[] = $recette['regime'] ?? '';
        }
        $texts[] = $selectedObjectif;
        foreach ($selectedIngredients as $ingredient) {
            $texts[] = $ingredient;
        }

        return $this->translateTextsWithAi($targetLang, $texts);
    }

    private function translateDetailTexts(string $targetLang, array $recette, array $instructions): array
    {
        $texts = [
            $recette['titre'] ?? '',
            $recette['objectif'] ?? '',
            $recette['regime'] ?? '',
        ];

        foreach ($instructions as $instruction) {
            $texts[] = $instruction['etape'] ?? '';
            $texts[] = $instruction['description'] ?? '';
            $ingredients = json_decode($instruction['ingredient_produit'] ?? '[]', true);
            if (is_array($ingredients)) {
                foreach ($ingredients as $ingredient) {
                    $texts[] = $ingredient['nom_produit'] ?? '';
                    $texts[] = $ingredient['quantite'] ?? '';
                }
            }
        }

        return $this->translateTextsWithAi($targetLang, $texts);
    }

    private function translateTextsWithAi(string $targetLang, array $texts): array
    {
        try {
            $config = require __DIR__ . '/../../config/config.php';
            $aiService = new AiRecipeService($config);
            return $aiService->translateTexts($targetLang, $texts);
        } catch (Throwable $e) {
            return ['translations' => [], 'note' => 'Traduction indisponible : ' . $e->getMessage()];
        }
    }

    private function cleanArray($values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('trim', $values))));
    }

    private function keepOnlyExistingIngredients(array $selected, array $available): array
    {
        return array_values(array_filter($selected, function ($ingredient) use ($available) {
            return in_array($ingredient, $available, true);
        }));
    }

    private function getAvailableObjectifs(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT objectif FROM recette WHERE objectif IS NOT NULL AND objectif <> "" ORDER BY objectif ASC');
        $objectifs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return array_values(array_filter(array_map('trim', $objectifs)));
    }

    private function getRecetteDuJour(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM recette ORDER BY RAND() LIMIT 1');
        $recette = $stmt->fetch();

        return $recette ? [$recette] : [];
    }

    private function getRecettesRapides(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE duree <= :duree ORDER BY duree ASC, id_recette DESC');
        $stmt->execute([':duree' => 20]);

        return $stmt->fetchAll();
    }

    private function findRecettesByObjectif(string $objectif): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE objectif = :objectif ORDER BY id_recette DESC');
        $stmt->execute([':objectif' => $objectif]);

        return $stmt->fetchAll();
    }

    private function getAvailableIngredients(): array
    {
        $stmt = $this->pdo->query('SELECT ingredient_produit FROM instruction');
        $rows = $stmt->fetchAll();
        $ingredients = [];

        foreach ($rows as $row) {
            $decoded = json_decode($row['ingredient_produit'] ?? '[]', true);
            if (!is_array($decoded)) {
                continue;
            }

            foreach ($decoded as $ingredient) {
                $nom = trim((string) ($ingredient['nom_produit'] ?? ''));
                if ($nom !== '') {
                    $ingredients[] = $nom;
                }
            }
        }

        $ingredients = array_values(array_unique($ingredients));
        natcasesort($ingredients);

        return array_values($ingredients);
    }

    private function findRecettesByRegimeAndIngredients(string $regime, array $selectedIngredients): array
    {
        if ($regime !== '') {
            $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE regime = :regime ORDER BY id_recette DESC');
            $stmt->execute([':regime' => $regime]);
        } else {
            $stmt = $this->pdo->query('SELECT * FROM recette ORDER BY id_recette DESC');
        }
        $recettes = $stmt->fetchAll();

        if (empty($recettes)) {
            return [];
        }

        $ingredientsByRecette = $this->getIngredientsForRecettes(array_column($recettes, 'id_recette'));

        return array_values(array_filter($recettes, function ($recette) use ($selectedIngredients, $ingredientsByRecette) {
            $recetteId = (int) $recette['id_recette'];
            $recetteIngredients = array_unique($ingredientsByRecette[$recetteId] ?? []);

            foreach ($selectedIngredients as $selectedIngredient) {
                if (!in_array(mb_strtolower($selectedIngredient), $recetteIngredients, true)) {
                    return false;
                }
            }

            return true;
        }));
    }

    private function findRecettesByAnyIngredients(array $selectedIngredients): array
    {
        $stmt = $this->pdo->query('SELECT * FROM recette ORDER BY id_recette DESC');
        $recettes = $stmt->fetchAll();

        if (empty($recettes)) {
            return [];
        }

        $ingredientsByRecette = $this->getIngredientsForRecettes(array_column($recettes, 'id_recette'));
        $selectedLower = array_map('mb_strtolower', $selectedIngredients);
        $totalSelected = count($selectedLower);
        $results = [];

        foreach ($recettes as $recette) {
            $recetteId = (int) $recette['id_recette'];
            $recetteIngredients = array_unique($ingredientsByRecette[$recetteId] ?? []);
            $matches = array_intersect($selectedLower, $recetteIngredients);
            $matchCount = count($matches);

            if ($matchCount > 0) {
                $recette['match_count'] = $matchCount;
                $recette['match_total'] = $totalSelected;
                $recette['match_score'] = (int) round(($matchCount / max(1, $totalSelected)) * 100);
                $results[] = $recette;
            }
        }

        usort($results, function ($a, $b) {
            if ($a['match_score'] === $b['match_score']) {
                return $b['match_count'] <=> $a['match_count'];
            }

            return $b['match_score'] <=> $a['match_score'];
        });

        return $results;
    }

    private function getIngredientsForRecettes(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        }));

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmtInstructions = $this->pdo->prepare('SELECT id_recette, ingredient_produit FROM instruction WHERE id_recette IN (' . $placeholders . ')');
        $stmtInstructions->execute($ids);
        $instructions = $stmtInstructions->fetchAll();

        $ingredientsByRecette = [];
        foreach ($instructions as $instruction) {
            $recetteId = (int) $instruction['id_recette'];
            if (!isset($ingredientsByRecette[$recetteId])) {
                $ingredientsByRecette[$recetteId] = [];
            }

            $decoded = json_decode($instruction['ingredient_produit'] ?? '[]', true);
            if (!is_array($decoded)) {
                continue;
            }

            foreach ($decoded as $ingredient) {
                $nom = trim((string) ($ingredient['nom_produit'] ?? ''));
                if ($nom !== '') {
                    $ingredientsByRecette[$recetteId][] = mb_strtolower($nom);
                }
            }
        }

        return $ingredientsByRecette;
    }


    public function generateAiRecipeAndSave(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?page=front_home');
            return;
        }

        $regime = trim($_POST['regime_generate'] ?? '');
        $selectedIngredients = $this->cleanArray($_POST['ingredients_generate'] ?? []);
        $typedIngredients = $this->parseIngredientsText($_POST['ingredients_ai_text'] ?? '');
        $ingredients = array_values(array_unique(array_merge($selectedIngredients, $typedIngredients)));

        $regimesAutorises = ['Végétarien', 'Végan', 'Sans gluten', 'Protéiné', 'Faible en calories'];
        if ($regime !== '' && !in_array($regime, $regimesAutorises, true)) {
            $regime = '';
        }

        if (count($ingredients) === 0) {
            $_SESSION['ai_recipe_error'] = 'Choisissez ou écrivez au moins un ingrédient.';
            $this->redirect('index.php?page=front_home');
            return;
        }

        try {
            $config = require __DIR__ . '/../../config/config.php';
            $aiService = new AiRecipeService($config);
            $recipe = $aiService->generate($regime, $ingredients);
            $newId = $this->saveGeneratedRecipe($recipe);

            $_SESSION['ai_recipe_success'] = 'Recette IA générée et enregistrée dans la base.';
            if (!empty($recipe['note'])) {
                $_SESSION['ai_recipe_note'] = $recipe['note'];
            }

            $this->redirect('index.php?page=front_recette_detail&id=' . $newId);
        } catch (Throwable $e) {
            $_SESSION['ai_recipe_error'] = 'Erreur IA : ' . $e->getMessage();
            $this->redirect('index.php?page=front_home');
        }
    }

    private function parseIngredientsText(string $text): array
    {
        $parts = preg_split('/[,;\n\r]+/', $text);
        if (!is_array($parts)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('trim', $parts))));
    }

    private function saveGeneratedRecipe(array $recipe): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmtRecette = $this->pdo->prepare('INSERT INTO recette (titre, objectif, regime, duree) VALUES (:titre, :objectif, :regime, :duree)');
            $stmtRecette->execute([
                ':titre' => $recipe['titre'],
                ':objectif' => $recipe['objectif'],
                ':regime' => $recipe['regime'],
                ':duree' => (int) $recipe['duree'],
            ]);

            $recetteId = (int) $this->pdo->lastInsertId();
            $stmtInstruction = $this->pdo->prepare('INSERT INTO instruction (id_recette, etape, description, ingredient_produit) VALUES (:id_recette, :etape, :description, :ingredient_produit)');

            foreach (($recipe['instructions'] ?? []) as $instruction) {
                $ingredientsJson = json_encode($instruction['ingredients'] ?? [], JSON_UNESCAPED_UNICODE);
                $stmtInstruction->execute([
                    ':id_recette' => $recetteId,
                    ':etape' => trim((string) ($instruction['etape'] ?? 'Étape')),
                    ':description' => trim((string) ($instruction['description'] ?? '')),
                    ':ingredient_produit' => $ingredientsJson ?: '[]',
                ]);
            }

            $this->pdo->commit();
            return $recetteId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function recetteDetail(int $id): void
    {
        $stmtRecette = $this->pdo->prepare('SELECT * FROM recette WHERE id_recette = :id');
        $stmtRecette->execute([':id' => $id]);
        $recette = $stmtRecette->fetch();

        if (!$recette) {
            $this->redirect('index.php?page=front_home');
            return;
        }

        $stmtInstructions = $this->pdo->prepare('SELECT * FROM instruction WHERE id_recette = :id_recette ORDER BY id_instruction ASC');
        $stmtInstructions->execute([':id_recette' => $id]);
        $instructions = $stmtInstructions->fetchAll();

        $aiTargetLang = $this->normalizeAiLang($_GET['ai_translate'] ?? '');
        $aiTranslationMap = [];
        $aiTranslateNote = '';
        if ($aiTargetLang !== '') {
            $_SESSION['lang'] = $aiTargetLang;
            $translationData = $this->translateDetailTexts($aiTargetLang, $recette, $instructions);
            $aiTranslationMap = $translationData['translations'];
            $aiTranslateNote = $translationData['note'];
        }

        $this->render('front/recette_detail', [
            'pageTitle' => 'Détail recette',
            'recette' => $recette,
            'instructions' => $instructions,
            'aiTargetLang' => $aiTargetLang,
            'aiTranslationMap' => $aiTranslationMap,
            'aiTranslateNote' => $aiTranslateNote,
        ]);
    }

    public function saveRecette(int $id): void
    {
        if ($id <= 0) {
            $this->redirect('index.php?page=front_home');
            return;
        }

        $stmt = $this->pdo->prepare('SELECT id_recette FROM recette WHERE id_recette = :id');
        $stmt->execute([':id' => $id]);
        $recette = $stmt->fetch();

        if (!$recette) {
            $this->redirect('index.php?page=front_home');
            return;
        }

        if (!isset($_SESSION['recettes_enregistrees']) || !is_array($_SESSION['recettes_enregistrees'])) {
            $_SESSION['recettes_enregistrees'] = [];
        }

        if (!in_array($id, $_SESSION['recettes_enregistrees'], true)) {
            $_SESSION['recettes_enregistrees'][] = $id;
        }

        $this->redirect('index.php?page=front_favoris');
    }

    public function favoris(): void
    {
        $ids = $_SESSION['recettes_enregistrees'] ?? [];
        $ids = array_values(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        }));

        $recettes = [];

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE id_recette IN (' . $placeholders . ') ORDER BY titre ASC');
            $stmt->execute($ids);
            $recettes = $stmt->fetchAll();
        }

        $this->render('front/favoris', [
            'pageTitle' => 'Mes recettes enregistrées | NutriVert',
            'recettes' => $recettes,
        ]);
    }

    public function removeFavori(int $id): void
    {
        if (isset($_SESSION['recettes_enregistrees']) && is_array($_SESSION['recettes_enregistrees'])) {
            $_SESSION['recettes_enregistrees'] = array_values(array_filter($_SESSION['recettes_enregistrees'], function ($savedId) use ($id) {
                return (int) $savedId !== $id;
            }));
        }

        $this->redirect('index.php?page=front_favoris');
    }
}

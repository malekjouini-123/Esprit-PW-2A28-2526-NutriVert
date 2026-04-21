<?php
class InstructionController extends BaseController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function validateInstruction(array $data): array
    {
        $errors = [];

        if (($data['id_recette'] ?? '') === '' || !ctype_digit((string) $data['id_recette'])) {
            $errors['id_recette'] = 'La recette est obligatoire.';
        }

        if (($data['etape'] ?? '') === '' || mb_strlen(trim($data['etape'])) < 2) {
            $errors['etape'] = 'L\'étape est obligatoire.';
        }

        if (($data['description'] ?? '') === '' || mb_strlen(trim($data['description'])) < 5) {
            $errors['description'] = 'La description doit contenir au moins 5 caractères.';
        }

        if (($data['ingredient_produit'] ?? '') === '') {
            $errors['ingredient_produit'] = 'Les ingrédients sont obligatoires.';
        } else {
            json_decode($data['ingredient_produit'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['ingredient_produit'] = 'Le JSON des ingrédients est invalide.';
            }
        }

        return $errors;
    }

    private function getAllRecettes(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM recette ORDER BY id_recette DESC');
        return $stmt->fetchAll();
    }

    public function index(): void
    {
        $searchIdRecette = trim($_GET['id_recette'] ?? '');
        $errorSearch = '';

        if ($searchIdRecette !== '') {
            if (ctype_digit($searchIdRecette)) {
                $stmt = $this->pdo->prepare('SELECT i.*, r.titre AS recette_titre FROM instruction i INNER JOIN recette r ON i.id_recette = r.id_recette WHERE i.id_recette = :id_recette ORDER BY i.id_instruction DESC');
                $stmt->execute([':id_recette' => (int) $searchIdRecette]);
                $instructions = $stmt->fetchAll();
            } else {
                $instructions = [];
                $errorSearch = 'ID recette invalide.';
            }
        } else {
            $stmt = $this->pdo->query('SELECT i.*, r.titre AS recette_titre FROM instruction i INNER JOIN recette r ON i.id_recette = r.id_recette ORDER BY i.id_instruction DESC');
            $instructions = $stmt->fetchAll();
        }

        $this->render('back/instructions/index', [
            'pageTitle' => 'BackOffice | Instructions',
            'instructions' => $instructions,
            'searchIdRecette' => $searchIdRecette,
            'errorSearch' => $errorSearch,
        ]);
    }

    public function create(): void
    {
        $this->render('back/instructions/form', [
            'pageTitle' => 'Ajouter instruction',
            'action' => 'back_instruction_store',
            'instruction' => [
                'id_recette' => '',
                'etape' => '',
                'description' => '',
                'ingredient_produit' => '[]',
            ],
            'errors' => [],
            'recettes' => $this->getAllRecettes(),
        ]);
    }

    public function store(): void
    {
        $data = [
            'id_recette' => trim($_POST['id_recette'] ?? ''),
            'etape' => trim($_POST['etape'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'ingredient_produit' => trim($_POST['ingredient_produit'] ?? ''),
        ];

        $errors = $this->validateInstruction($data);
        if (!empty($errors)) {
            $this->render('back/instructions/form', [
                'pageTitle' => 'Ajouter instruction',
                'action' => 'back_instruction_store',
                'instruction' => $data,
                'errors' => $errors,
                'recettes' => $this->getAllRecettes(),
            ]);
            return;
        }

        $stmt = $this->pdo->prepare('INSERT INTO instruction (id_recette, etape, description, ingredient_produit) VALUES (:id_recette, :etape, :description, :ingredient_produit)');
        $stmt->execute([
            ':id_recette' => (int) $data['id_recette'],
            ':etape' => $data['etape'],
            ':description' => $data['description'],
            ':ingredient_produit' => $data['ingredient_produit'],
        ]);

        $this->redirect('index.php?page=back_instructions');
    }

    public function edit(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT * FROM instruction WHERE id_instruction = :id');
        $stmt->execute([':id' => $id]);
        $instruction = $stmt->fetch();

        if (!$instruction) {
            $this->redirect('index.php?page=back_instructions');
            return;
        }

        $this->render('back/instructions/form', [
            'pageTitle' => 'Modifier instruction',
            'action' => 'back_instruction_update&id=' . $id,
            'instruction' => $instruction,
            'errors' => [],
            'recettes' => $this->getAllRecettes(),
        ]);
    }

    public function update(int $id): void
    {
        $data = [
            'id_recette' => trim($_POST['id_recette'] ?? ''),
            'etape' => trim($_POST['etape'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'ingredient_produit' => trim($_POST['ingredient_produit'] ?? ''),
        ];

        $errors = $this->validateInstruction($data);
        if (!empty($errors)) {
            $data['id_instruction'] = $id;
            $this->render('back/instructions/form', [
                'pageTitle' => 'Modifier instruction',
                'action' => 'back_instruction_update&id=' . $id,
                'instruction' => $data,
                'errors' => $errors,
                'recettes' => $this->getAllRecettes(),
            ]);
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE instruction SET id_recette = :id_recette, etape = :etape, description = :description, ingredient_produit = :ingredient_produit WHERE id_instruction = :id');
        $stmt->execute([
            ':id_recette' => (int) $data['id_recette'],
            ':etape' => $data['etape'],
            ':description' => $data['description'],
            ':ingredient_produit' => $data['ingredient_produit'],
            ':id' => $id,
        ]);

        $this->redirect('index.php?page=back_instructions');
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM instruction WHERE id_instruction = :id');
        $stmt->execute([':id' => $id]);
        $this->redirect('index.php?page=back_instructions');
    }
}

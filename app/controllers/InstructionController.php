<?php
class InstructionController extends BaseController
{
    private Instruction $instructionModel;
    private Recette $recetteModel;

    public function __construct(Instruction $instructionModel, Recette $recetteModel)
    {
        $this->instructionModel = $instructionModel;
        $this->recetteModel = $recetteModel;
    }

public function index(): void
{
    $searchIdRecette = trim($_GET['id_recette'] ?? '');
    $errorSearch = '';

    if ($searchIdRecette !== '') {
        if (ctype_digit($searchIdRecette)) {
            $instructions = $this->instructionModel->searchByRecetteId((int) $searchIdRecette);
        } else {
            $instructions = [];
            $errorSearch = 'ID recette invalide.';
        }
    } else {
        $instructions = $this->instructionModel->all();
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
                'ingredient_produit' => "[]",
            ],
            'errors' => [],
            'recettes' => $this->recetteModel->all(),
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

        $errors = $this->instructionModel->validate($data);
        if (!empty($errors)) {
            $this->render('back/instructions/form', [
                'pageTitle' => 'Ajouter instruction',
                'action' => 'back_instruction_store',
                'instruction' => $data,
                'errors' => $errors,
                'recettes' => $this->recetteModel->all(),
            ]);
            return;
        }

        $this->instructionModel->create($data);
        $this->redirect('index.php?page=back_instructions');
    }

    public function edit(int $id): void
    {
        $instruction = $this->instructionModel->find($id);
        if (!$instruction) {
            $this->redirect('index.php?page=back_instructions');
        }

        $this->render('back/instructions/form', [
            'pageTitle' => 'Modifier instruction',
            'action' => 'back_instruction_update&id=' . $id,
            'instruction' => $instruction,
            'errors' => [],
            'recettes' => $this->recetteModel->all(),
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

        $errors = $this->instructionModel->validate($data);
        if (!empty($errors)) {
            $data['id_instruction'] = $id;
            $this->render('back/instructions/form', [
                'pageTitle' => 'Modifier instruction',
                'action' => 'back_instruction_update&id=' . $id,
                'instruction' => $data,
                'errors' => $errors,
                'recettes' => $this->recetteModel->all(),
            ]);
            return;
        }

        $this->instructionModel->update($id, $data);
        $this->redirect('index.php?page=back_instructions');
    }

    public function delete(int $id): void
    {
        $this->instructionModel->delete($id);
        $this->redirect('index.php?page=back_instructions');
    }
}

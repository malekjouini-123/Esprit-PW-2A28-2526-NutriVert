<?php
class RecetteController extends BaseController
{
    private Recette $recetteModel;
    private Instruction $instructionModel;
    private PDO $pdo;

    public function __construct(Recette $recetteModel, Instruction $instructionModel, PDO $pdo)
    {
        $this->recetteModel = $recetteModel;
        $this->instructionModel = $instructionModel;
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        $this->render('back/recettes/index', [
            'pageTitle' => 'BackOffice | Recettes',
            'recettes' => $this->recetteModel->all(),
        ]);
    }

    public function create(): void
    {
        $this->render('back/recettes/form', [
            'pageTitle' => 'Ajouter recette',
            'action' => 'back_recette_store',
            'recette' => ['titre' => '', 'objectif' => '', 'regime' => '', 'duree' => ''],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $data = [
            'titre' => trim($_POST['titre'] ?? ''),
            'objectif' => trim($_POST['objectif'] ?? ''),
            'regime' => trim($_POST['regime'] ?? ''),
            'duree' => trim($_POST['duree'] ?? ''),
        ];

        $errors = $this->recetteModel->validate($data);
        if (!empty($errors)) {
            $this->render('back/recettes/form', [
                'pageTitle' => 'Ajouter recette',
                'action' => 'back_recette_store',
                'recette' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $this->recetteModel->create([
            ...$data,
            'duree' => (int)$data['duree'],
        ]);
        $this->redirect('index.php?page=back_recettes');
    }

    public function edit(int $id): void
    {
        $recette = $this->recetteModel->find($id);
        if (!$recette) {
            $this->redirect('index.php?page=back_recettes');
        }

        $this->render('back/recettes/form', [
            'pageTitle' => 'Modifier recette',
            'action' => 'back_recette_update&id=' . $id,
            'recette' => $recette,
            'errors' => [],
        ]);
    }

    public function update(int $id): void
    {
        $data = [
            'titre' => trim($_POST['titre'] ?? ''),
            'objectif' => trim($_POST['objectif'] ?? ''),
            'regime' => trim($_POST['regime'] ?? ''),
            'duree' => trim($_POST['duree'] ?? ''),
        ];

        $errors = $this->recetteModel->validate($data);
        if (!empty($errors)) {
            $data['id_recette'] = $id;
            $this->render('back/recettes/form', [
                'pageTitle' => 'Modifier recette',
                'action' => 'back_recette_update&id=' . $id,
                'recette' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $this->recetteModel->update($id, [
            ...$data,
            'duree' => (int)$data['duree'],
        ]);
        $this->redirect('index.php?page=back_recettes');
    }

    public function delete(int $id): void
    {
        $this->recetteModel->delete($id);
        $this->redirect('index.php?page=back_recettes');
    }

    public function createFull(): void
    {
        $this->render('back/recettes/form_complete', [
            'pageTitle' => 'Ajouter recette complète',
            'errors' => [],
            'old' => [
                'titre' => '',
                'objectif' => '',
                'regime' => '',
                'duree' => '',
                'etape' => [''],
                'description' => [''],
                'ingredient_produit' => ['[]'],
            ],
        ]);
    }

    public function storeFull(): void
    {
        $old = [
            'titre' => trim($_POST['titre'] ?? ''),
            'objectif' => trim($_POST['objectif'] ?? ''),
            'regime' => trim($_POST['regime'] ?? ''),
            'duree' => trim($_POST['duree'] ?? ''),
            'etape' => $_POST['etape'] ?? [''],
            'description' => $_POST['description'] ?? [''],
            'ingredient_produit' => $_POST['ingredient_produit'] ?? ['[]'],
        ];

        $errors = $this->recetteModel->validate($old);

        $etapes = $old['etape'];
        $descriptions = $old['description'];
        $ingredients = $old['ingredient_produit'];

        if (count($etapes) === 0) {
            $errors['etape'][0] = 'Vous devez ajouter au moins une étape.';
        }

        foreach ($etapes as $index => $etape) {
            $etape = trim((string)$etape);
            $description = trim((string)($descriptions[$index] ?? ''));
            $ingredient = trim((string)($ingredients[$index] ?? ''));

            if ($etape === '') {
                $errors['etape'][$index] = 'Le champ étape est obligatoire.';
            }
            if ($description === '') {
                $errors['description'][$index] = 'La description est obligatoire.';
            }
            if ($ingredient === '') {
                $errors['ingredient_produit'][$index] = 'Les ingrédients sont obligatoires.';
            } else {
                $decoded = json_decode($ingredient, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    $errors['ingredient_produit'][$index] = 'Le JSON des ingrédients est invalide.';
                }
            }
        }

        if (!empty($errors)) {
            $this->render('back/recettes/form_complete', [
                'pageTitle' => 'Ajouter recette complète',
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        try {
            $this->pdo->beginTransaction();

            $recetteId = $this->recetteModel->createAndReturnId([
                'titre' => $old['titre'],
                'objectif' => $old['objectif'],
                'regime' => $old['regime'],
                'duree' => (int)$old['duree'],
            ]);

            foreach ($etapes as $index => $etape) {
                $this->instructionModel->create([
                    'id_recette' => $recetteId,
                    'etape' => trim((string)$etape),
                    'description' => trim((string)$descriptions[$index]),
                    'ingredient_produit' => trim((string)$ingredients[$index]),
                ]);
            }

            $this->pdo->commit();
            $this->redirect('index.php?page=back_recettes');
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $errors['global'] = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
            $this->render('back/recettes/form_complete', [
                'pageTitle' => 'Ajouter recette complète',
                'errors' => $errors,
                'old' => $old,
            ]);
        }
    }
}

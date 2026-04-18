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
    $search = trim($_GET['search'] ?? '');

    if ($search !== '') {
        $recettes = $this->recetteModel->searchByTitre($search);
    } else {
        $recettes = $this->recetteModel->all();
    }

    $this->render('back/recettes/index', [
        'pageTitle' => 'BackOffice | Recettes',
        'recettes' => $recettes,
        'search' => $search,
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
            'duree' => (int) $data['duree'],
        ]);

        $this->redirect('index.php?page=back_recettes');
    }

    public function edit(int $id): void
    {
        $recette = $this->recetteModel->find($id);

        if (!$recette) {
            $this->redirect('index.php?page=back_recettes');
            return;
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
            'duree' => (int) $data['duree'],
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
        $ingredientsList = $old['ingredient_produit'];

        if (count($etapes) === 0) {
            $errors['global'] = 'Vous devez ajouter au moins une étape.';
        }

        foreach ($etapes as $i => $etape) {
            if (trim((string) $etape) === '') {
                $errors['etape'][$i] = 'Le nom de l’étape est obligatoire.';
            }

            if (empty(trim((string) ($descriptions[$i] ?? '')))) {
                $errors['description'][$i] = 'La description est obligatoire.';
            }

            $ingredients = json_decode($ingredientsList[$i] ?? '[]', true);

            if (!is_array($ingredients) || count($ingredients) === 0) {
                $errors['ingredient_produit'][$i] = 'Chaque étape doit contenir au moins un ingrédient.';
            } else {
                foreach ($ingredients as $ingredient) {
                    $nomProduit = trim((string) ($ingredient['nom_produit'] ?? ''));
                    $quantite = trim((string) ($ingredient['quantite'] ?? ''));

                    if ($nomProduit === '' || $quantite === '') {
                        $errors['ingredient_produit'][$i] = 'Chaque ingrédient doit avoir un nom et une quantité.';
                        break;
                    }
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
                'duree' => (int) $old['duree'],
            ]);

            foreach ($etapes as $index => $etape) {
                $this->instructionModel->create([
                    'id_recette' => $recetteId,
                    'etape' => trim((string) $etape),
                    'description' => trim((string) ($descriptions[$index] ?? '')),
                    'ingredient_produit' => trim((string) ($ingredientsList[$index] ?? '[]')),
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
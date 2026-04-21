<?php

class RecetteController extends BaseController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function validateRecette(array $data): array
    {
        $errors = [];

        if (($data['titre'] ?? '') === '' || mb_strlen(trim($data['titre'])) < 3) {
            $errors['titre'] = 'Le titre doit contenir au moins 3 caractères.';
        }

        if (($data['objectif'] ?? '') === '' || mb_strlen(trim($data['objectif'])) < 5) {
            $errors['objectif'] = 'L\'objectif doit contenir au moins 5 caractères.';
        }

        if (($data['regime'] ?? '') === '') {
            $errors['regime'] = 'Le régime est obligatoire.';
        }

        if (($data['duree'] ?? '') === '' || !is_numeric($data['duree']) || (int) $data['duree'] <= 0) {
            $errors['duree'] = 'La durée doit être un nombre positif.';
        }

        return $errors;
    }

    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');

        if ($search !== '') {
            $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE titre LIKE :titre ORDER BY id_recette DESC');
            $stmt->execute([':titre' => '%' . $search . '%']);
            $recettes = $stmt->fetchAll();
        } else {
            $stmt = $this->pdo->query('SELECT * FROM recette ORDER BY id_recette DESC');
            $recettes = $stmt->fetchAll();
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

        $errors = $this->validateRecette($data);

        if (!empty($errors)) {
            $this->render('back/recettes/form', [
                'pageTitle' => 'Ajouter recette',
                'action' => 'back_recette_store',
                'recette' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $stmt = $this->pdo->prepare('INSERT INTO recette (titre, objectif, regime, duree) VALUES (:titre, :objectif, :regime, :duree)');
        $stmt->execute([
            ':titre' => $data['titre'],
            ':objectif' => $data['objectif'],
            ':regime' => $data['regime'],
            ':duree' => (int) $data['duree'],
        ]);

        $this->redirect('index.php?page=back_recettes');
    }

    public function edit(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE id_recette = :id');
        $stmt->execute([':id' => $id]);
        $recette = $stmt->fetch();

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

        $errors = $this->validateRecette($data);

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

        $stmt = $this->pdo->prepare('UPDATE recette SET titre = :titre, objectif = :objectif, regime = :regime, duree = :duree WHERE id_recette = :id');
        $stmt->execute([
            ':titre' => $data['titre'],
            ':objectif' => $data['objectif'],
            ':regime' => $data['regime'],
            ':duree' => (int) $data['duree'],
            ':id' => $id,
        ]);

        $this->redirect('index.php?page=back_recettes');
    }

    public function delete(int $id): void
    {
        $stmtInstructions = $this->pdo->prepare('DELETE FROM instruction WHERE id_recette = :id_recette');
        $stmtInstructions->execute([':id_recette' => $id]);

        $stmtRecette = $this->pdo->prepare('DELETE FROM recette WHERE id_recette = :id');
        $stmtRecette->execute([':id' => $id]);

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

        $errors = $this->validateRecette($old);

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

            $stmtRecette = $this->pdo->prepare('INSERT INTO recette (titre, objectif, regime, duree) VALUES (:titre, :objectif, :regime, :duree)');
            $stmtRecette->execute([
                ':titre' => $old['titre'],
                ':objectif' => $old['objectif'],
                ':regime' => $old['regime'],
                ':duree' => (int) $old['duree'],
            ]);

            $recetteId = (int) $this->pdo->lastInsertId();

            $stmtInstruction = $this->pdo->prepare('INSERT INTO instruction (id_recette, etape, description, ingredient_produit) VALUES (:id_recette, :etape, :description, :ingredient_produit)');

            foreach ($etapes as $index => $etape) {
                $stmtInstruction->execute([
                    ':id_recette' => $recetteId,
                    ':etape' => trim((string) $etape),
                    ':description' => trim((string) ($descriptions[$index] ?? '')),
                    ':ingredient_produit' => trim((string) ($ingredientsList[$index] ?? '[]')),
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

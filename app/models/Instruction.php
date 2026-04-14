<?php
class Instruction
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        $sql = 'SELECT i.*, r.titre AS recette_titre FROM instruction i INNER JOIN recette r ON i.id_recette = r.id_recette ORDER BY i.id_instruction DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM instruction WHERE id_instruction = :id');
        $stmt->execute([':id' => $id]);
        $instruction = $stmt->fetch();
        return $instruction ?: null;
    }

    public function byRecette(int $idRecette): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM instruction WHERE id_recette = :id_recette ORDER BY id_instruction ASC');
        $stmt->execute([':id_recette' => $idRecette]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO instruction (id_recette, etape, description, ingredient_produit) VALUES (:id_recette, :etape, :description, :ingredient_produit)');
        return $stmt->execute([
            ':id_recette' => $data['id_recette'],
            ':etape' => $data['etape'],
            ':description' => $data['description'],
            ':ingredient_produit' => $data['ingredient_produit'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE instruction SET id_recette = :id_recette, etape = :etape, description = :description, ingredient_produit = :ingredient_produit WHERE id_instruction = :id');
        return $stmt->execute([
            ':id_recette' => $data['id_recette'],
            ':etape' => $data['etape'],
            ':description' => $data['description'],
            ':ingredient_produit' => $data['ingredient_produit'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM instruction WHERE id_instruction = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function deleteByRecette(int $idRecette): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM instruction WHERE id_recette = :id_recette');
        return $stmt->execute([':id_recette' => $idRecette]);
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (($data['id_recette'] ?? '') === '' || !is_numeric($data['id_recette'])) {
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
}

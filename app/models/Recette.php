<?php
class Recette
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM recette ORDER BY id_recette DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE id_recette = :id');
        $stmt->execute([':id' => $id]);
        $recette = $stmt->fetch();
        return $recette ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO recette (titre, objectif, regime, duree) VALUES (:titre, :objectif, :regime, :duree)');
        return $stmt->execute([
            ':titre' => $data['titre'],
            ':objectif' => $data['objectif'],
            ':regime' => $data['regime'],
            ':duree' => $data['duree'],
        ]);
    }

    public function createAndReturnId(array $data): int
    {
        $this->create($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE recette SET titre = :titre, objectif = :objectif, regime = :regime, duree = :duree WHERE id_recette = :id');
        return $stmt->execute([
            ':titre' => $data['titre'],
            ':objectif' => $data['objectif'],
            ':regime' => $data['regime'],
            ':duree' => $data['duree'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM recette WHERE id_recette = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function validate(array $data): array
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

        if (($data['duree'] ?? '') === '' || !is_numeric($data['duree']) || (int)$data['duree'] <= 0) {
            $errors['duree'] = 'La durée doit être un nombre positif.';
        }

        return $errors;
    }
public function searchByTitre(string $titre): array
{
    $sql = "SELECT * FROM recette WHERE titre LIKE :titre ORDER BY id_recette DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        ':titre' => '%' . $titre . '%'
    ]);
    return $stmt->fetchAll();
}

}


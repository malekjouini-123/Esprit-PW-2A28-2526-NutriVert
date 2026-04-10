<?php
declare(strict_types=1);

/**
 * Modèle : accès à la table `categorie`.
 */
class Categorie
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, nom, description FROM categorie ORDER BY nom ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nom, description FROM categorie WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public function create(string $nom, ?string $description): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO categorie (nom, description) VALUES (?, ?)');
        $stmt->execute([$nom, $description]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $nom, ?string $description): int
    {
        $stmt = $this->pdo->prepare('UPDATE categorie SET nom = ?, description = ? WHERE id = ?');
        $stmt->execute([$nom, $description, $id]);
        return $stmt->rowCount();
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM categorie WHERE id = ?');
        $stmt->execute([$id]);
    }
}

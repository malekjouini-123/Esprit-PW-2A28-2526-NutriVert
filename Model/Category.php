<?php
declare(strict_types=1);

/**
 * Modèle Category — Gère les catégories dans la base de données.
 */
class Category
{
    public int $id;
    public string $cat_id;
    public string $nom;
    public string $description;
    public string $atelier;
    public array $images;
    public string $created_at;

    public function __construct(array $data = [])
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->cat_id = (string)($data['cat_id'] ?? '');
        $this->nom = (string)($data['nom'] ?? '');
        $this->description = (string)($data['description'] ?? '');
        $this->atelier = (string)($data['atelier'] ?? '');
        
        $imagesData = $data['images'] ?? '';
        if (is_array($imagesData)) {
            $this->images = $imagesData;
        } else {
            $this->images = $imagesData ? explode(',', $imagesData) : [];
        }
        
        $this->created_at = (string)($data['created_at'] ?? '');
    }

    /**
     * Récupère toutes les catégories.
     */
    public static function findAll(): array
    {
        $pdo = nv_pdo();
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC");
        $results = $stmt->fetchAll();
        
        $categories = [];
        foreach ($results as $row) {
            $categories[] = new self($row);
        }
        return $categories;
    }

    /**
     * Enregistre ou met à jour une catégorie.
     */
    public function save(): bool
    {
        $pdo = nv_pdo();
        $imagesStr = implode(',', $this->images);

        if ($this->id > 0) {
            $stmt = $pdo->prepare("UPDATE categories SET cat_id = ?, nom = ?, description = ?, atelier = ?, images = ? WHERE id = ?");
            return $stmt->execute([
                $this->cat_id,
                $this->nom,
                $this->description,
                $this->atelier,
                $imagesStr,
                $this->id
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (cat_id, nom, description, atelier, images) VALUES (?, ?, ?, ?, ?)");
            $res = $stmt->execute([
                $this->cat_id,
                $this->nom,
                $this->description,
                $this->atelier,
                $imagesStr
            ]);
            if ($res) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $res;
        }
    }

    /**
     * Supprime une catégorie.
     */
    public static function delete(int $id): bool
    {
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

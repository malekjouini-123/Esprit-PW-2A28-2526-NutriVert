<?php
declare(strict_types=1);

/**
 * Modèle : accès à la table `produit` (jointure catégorie pour l’affichage).
 */
class Produit
{
    private const SQL_LIST = 'SELECT p.id, p.categorie_id, p.nom, p.label, p.producteur, p.prix, p.empreinte_co2, p.icone,
        c.nom AS categorie_nom
        FROM produit p
        INNER JOIN categorie c ON c.id = p.categorie_id';

    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(?int $categorieId = null): array
    {
        if ($categorieId !== null && $categorieId > 0) {
            $stmt = $this->pdo->prepare(self::SQL_LIST . ' WHERE p.categorie_id = ? ORDER BY p.nom ASC');
            $stmt->execute([$categorieId]);
            return $stmt->fetchAll();
        }
        $stmt = $this->pdo->query(self::SQL_LIST . ' ORDER BY c.nom ASC, p.nom ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(self::SQL_LIST . ' WHERE p.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public function create(
        int $categorieId,
        string $nom,
        ?string $label,
        ?string $producteur,
        float $prix,
        ?float $empreinteCo2,
        string $icone
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO produit (categorie_id, nom, label, producteur, prix, empreinte_co2, icone) VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([$categorieId, $nom, $label, $producteur, $prix, $empreinteCo2, $icone]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(
        int $id,
        int $categorieId,
        string $nom,
        ?string $label,
        ?string $producteur,
        float $prix,
        ?float $empreinteCo2,
        string $icone
    ): int {
        $stmt = $this->pdo->prepare(
            'UPDATE produit SET categorie_id=?, nom=?, label=?, producteur=?, prix=?, empreinte_co2=?, icone=? WHERE id=?'
        );
        $stmt->execute([$categorieId, $nom, $label, $producteur, $prix, $empreinteCo2, $icone, $id]);
        return $stmt->rowCount();
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM produit WHERE id = ?');
        $stmt->execute([$id]);
    }
}

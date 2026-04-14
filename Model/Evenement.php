<?php
declare(strict_types=1);

/**
 * Modèle Evenement — Gère les événements dans la base de données.
 */
class Evenement
{
    public int $id;
    public string $titre;
    public ?string $categorie;
    public string $description;
    public string $date_evenement;
    public string $lieu;
    public float $prix_participation;
    public int $capacite_max;
    public string $statut;
    public ?string $image_url;
    public string $created_at;

    public function __construct(array $data = [])
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->titre = (string)($data['titre'] ?? '');
        $this->categorie = (string)($data['categorie'] ?? '');
        $this->description = (string)($data['description'] ?? '');
        $this->date_evenement = (string)($data['date_evenement'] ?? '');
        $this->lieu = (string)($data['lieu'] ?? '');
        $this->prix_participation = (float)($data['prix_participation'] ?? 0);
        $this->capacite_max = (int)($data['capacite_max'] ?? 0);
        $this->statut = (string)($data['statut'] ?? 'Actif');
        $this->image_url = $data['image_url'] ?? null;
        $this->created_at = (string)($data['created_at'] ?? '');
    }

    /**
     * Récupère tous les événements.
     */
    public static function findAll(): array
    {
        $pdo = nv_pdo();
        $stmt = $pdo->query("SELECT * FROM evenements ORDER BY date_evenement ASC");
        $results = $stmt->fetchAll();
        
        $evenements = [];
        foreach ($results as $row) {
            $evenements[] = new self($row);
        }
        return $evenements;
    }

    /**
     * Trouve un événement par son ID.
     */
    public static function findById(int $id): ?self
    {
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        return $row ? new self($row) : null;
    }

    public function save(): bool
    {
        $pdo = nv_pdo();
        if ($this->id > 0) {
            $stmt = $pdo->prepare("UPDATE evenements SET titre = ?, categorie = ?, description = ?, date_evenement = ?, lieu = ?, prix_participation = ?, capacite_max = ?, statut = ?, image_url = ? WHERE id = ?");
            return $stmt->execute([
                $this->titre,
                $this->categorie,
                $this->description,
                $this->date_evenement,
                $this->lieu,
                $this->prix_participation,
                $this->capacite_max,
                $this->statut,
                $this->image_url,
                $this->id
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO evenements (titre, categorie, description, date_evenement, lieu, prix_participation, capacite_max, statut, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $res = $stmt->execute([
                $this->titre,
                $this->categorie,
                $this->description,
                $this->date_evenement,
                $this->lieu,
                $this->prix_participation,
                $this->capacite_max,
                $this->statut,
                $this->image_url
            ]);
            if ($res) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $res;
        }
    }

    /**
     * Supprime un événement.
     */
    public static function delete(int $id): bool
    {
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("DELETE FROM evenements WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

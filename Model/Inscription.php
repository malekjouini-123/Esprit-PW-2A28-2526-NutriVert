<?php
declare(strict_types=1);

/**
 * Modèle Inscription — Gère les inscriptions des utilisateurs aux événements.
 */
class Inscription
{
    public int $id;
    public string $participant_custom_id;
    public int $evenement_id;
    public string $nom;
    public string $prenom;
    public string $email;
    public string $mot_de_passe;
    public string $telephone;
    public string $lieu;
    public string $date_naissance;
    public float $poids;
    public float $taille;
    public float $imc;
    public string $categorie_preferee;
    public string $created_at;

    public function __construct(array $data = [])
    {
        $this->id = (int)($data['id'] ?? 0);
        $this->participant_custom_id = (string)($data['participant_custom_id'] ?? '');
        $this->evenement_id = (int)($data['evenement_id'] ?? 0);
        $this->nom = (string)($data['nom'] ?? '');
        $this->prenom = (string)($data['prenom'] ?? '');
        $this->email = (string)($data['email'] ?? '');
        $this->mot_de_passe = (string)($data['mot_de_passe'] ?? '');
        $this->telephone = (string)($data['telephone'] ?? '');
        $this->lieu = (string)($data['lieu'] ?? '');
        $this->date_naissance = (string)($data['date_naissance'] ?? '');
        $this->poids = (float)($data['poids'] ?? 0);
        $this->taille = (float)($data['taille'] ?? 0);
        $this->imc = (float)($data['imc'] ?? 0);
        $this->categorie_preferee = (string)($data['categorie_preferee'] ?? '');
        $this->created_at = (string)($data['created_at'] ?? '');
    }

    /**
     * Calcule l'IMC : poids (kg) / [taille (m)]²
     */
    public function calculateIMC(): void
    {
        if ($this->taille > 0) {
            $tailleMetres = $this->taille / 100;
            $this->imc = round($this->poids / ($tailleMetres * $tailleMetres), 1);
        }
    }

    /**
     * Enregistre une nouvelle inscription.
     */
    public function save(): bool
    {
        $this->calculateIMC();
        
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("
            INSERT INTO inscriptions 
            (participant_custom_id, evenement_id, nom, prenom, email, mot_de_passe, telephone, lieu, date_naissance, poids, taille, imc, categorie_preferee) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $res = $stmt->execute([
            $this->participant_custom_id,
            $this->evenement_id,
            $this->nom,
            $this->prenom,
            $this->email,
            $this->mot_de_passe, // Note: En production, hachez le mot de passe !
            $this->telephone,
            $this->lieu,
            $this->date_naissance,
            $this->poids,
            $this->taille,
            $this->imc,
            $this->categorie_preferee
        ]);

        if ($res) {
            $this->id = (int)$pdo->lastInsertId();
        }
        return $res;
    }

    /**
     * Récupère tous les participants.
     */
    public static function findAll(): array
    {
        $pdo = nv_pdo();
        $stmt = $pdo->query("SELECT * FROM inscriptions ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve un participant par son ID.
     */
    public static function findById(int $id): ?self
    {
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    /**
     * Met à jour une inscription existante.
     */
    public function update(): bool
    {
        $this->calculateIMC();
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("
            UPDATE inscriptions 
            SET participant_custom_id = ?, evenement_id = ?, nom = ?, prenom = ?, email = ?, mot_de_passe = ?, telephone = ?, lieu = ?, date_naissance = ?, poids = ?, taille = ?, imc = ?, categorie_preferee = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $this->participant_custom_id,
            $this->evenement_id,
            $this->nom,
            $this->prenom,
            $this->email,
            $this->mot_de_passe,
            $this->telephone,
            $this->lieu,
            $this->date_naissance,
            $this->poids,
            $this->taille,
            $this->imc,
            $this->categorie_preferee,
            $this->id
        ]);
    }

    /**
     * Supprime un participant.
     */
    public static function delete(int $id): bool
    {
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Récupère un participant par son email.
     */
    public static function findOneByEmail(string $email): ?self
    {
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    /**
     * Récupère les inscriptions pour un email donné (pour le suivi).
     */
    public static function findByEmail(string $email): array
    {
        $pdo = nv_pdo();
        $stmt = $pdo->prepare("
            SELECT i.*, e.titre as evenement_titre, e.date_evenement 
            FROM inscriptions i
            JOIN evenements e ON i.evenement_id = e.id
            WHERE i.email = ?
            ORDER BY e.date_evenement DESC
        ");
        $stmt->execute([$email]);
        return $stmt->fetchAll();
    }
}

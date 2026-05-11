<?php
declare(strict_types=1);

// =============================================================================
//  ENTITÉ  User
//  Objet métier pur : attributs, constructeur, destructeur, getters, setters.
//  Aucune logique BDD ici — tout accès PDO est dans UserController.
// =============================================================================

class User
{
    // -------------------------------------------------------------------------
    // Attributs privés
    // -------------------------------------------------------------------------

    private ?int    $id;
    private string  $nom;
    private string  $email;
    private string  $password;      // hash bcrypt stocké en BDD
    private string  $role;          // 'user' ou 'coach' ou 'admin'

    // Champs spécifiques aux utilisateurs (role = 'user')
    private ?float  $poids;         // kg
    private ?float  $taille;        // cm
    private ?int    $age;           // ans
    private ?string $sexe;          // 'homme' ou 'femme'
    private ?string $objectif;      // 'perte', 'maintien', 'muscle'
    private ?float  $imc;           // calculé
    private ?int    $calories;      // kcal/jour calculées

    // Champs spécifiques aux coachs (role = 'coach')
    private ?string $specialite;
    private ?string $bio;

    private ?string $createdAt;

    public const ALLOWED_ROLES = ['user', 'coach', 'admin'];

    // -------------------------------------------------------------------------
    // Constructeur
    // -------------------------------------------------------------------------

    /**
     * @throws InvalidArgumentException si les données obligatoires sont manquantes.
     */
    public function __construct(array $data)
    {
        $this->id         = isset($data['id'])          ? (int)$data['id']              : null;
        $this->nom        = trim((string)($data['nom']       ?? ''));
        $this->email      = strtolower(trim((string)($data['email']    ?? '')));
        $this->password   = (string)($data['password']  ?? '');
        $this->role       = strtolower(trim((string)($data['role']     ?? 'user')));
        $this->poids      = isset($data['poids']) && $data['poids'] !== '' && $data['poids'] !== null ? (float)$data['poids']         : null;
        $this->taille     = isset($data['taille']) && $data['taille'] !== '' && $data['taille'] !== null ? (float)$data['taille']        : null;
        $this->age        = isset($data['age']) && $data['age'] !== '' && $data['age'] !== null ? (int)$data['age']            : null;
        $this->sexe       = isset($data['sexe']) && $data['sexe'] !== '' ? strtolower(trim((string)$data['sexe'])) : null;
        $this->objectif   = isset($data['objectif']) && $data['objectif'] !== '' ? strtolower(trim((string)$data['objectif'])) : null;
        $this->imc        = isset($data['imc']) && $data['imc'] !== '' && $data['imc'] !== null ? (float)$data['imc']           : null;
        $this->calories   = isset($data['calories']) && $data['calories'] !== '' && $data['calories'] !== null ? (int)$data['calories']        : null;
        $this->specialite = isset($data['specialite'])   ? trim((string)$data['specialite']) : null;
        $this->bio        = isset($data['bio'])          ? trim((string)$data['bio'])    : null;
        $this->createdAt  = isset($data['created_at'])   ? (string)$data['created_at']  : null;

        $this->validate();

        // Calcul automatique IMC et calories si user avec poids/taille
        if ($this->role === 'user' && $this->poids && $this->taille && !$this->imc) {
            $this->imc      = $this->calculerImc();
            $this->calories = $this->calculerCalories($this->age ?? 25, $this->sexe ?? 'homme', $this->objectif ?? 'maintien');
        }
    }


    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function getId(): ?int           { return $this->id; }
    public function getNom(): string        { return $this->nom; }
    public function getEmail(): string      { return $this->email; }
    public function getPassword(): string   { return $this->password; }
    public function getRole(): string       { return $this->role; }
    public function getPoids(): ?float      { return $this->poids; }
    public function getTaille(): ?float     { return $this->taille; }
    public function getAge(): ?int          { return $this->age; }
    public function getSexe(): ?string      { return $this->sexe; }
    public function getObjectif(): ?string  { return $this->objectif; }
    public function getImc(): ?float        { return $this->imc; }
    public function getCalories(): ?int     { return $this->calories; }
    public function getSpecialite(): ?string{ return $this->specialite; }
    public function getBio(): ?string       { return $this->bio; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function isCoach(): bool         { return $this->role === 'coach'; }
    public function isUser(): bool          { return $this->role === 'user'; }
    public function isAdmin(): bool         { return $this->role === 'admin'; }

    // -------------------------------------------------------------------------
    // Setters
    // -------------------------------------------------------------------------

    public function setNom(string $nom): void
    {
        $nom = trim($nom);
        if ($nom === '') throw new InvalidArgumentException('Le nom ne peut pas être vide.');
        $this->nom = $nom;
    }

    public function setEmail(string $email): void
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email invalide.');
        }
        $this->email = $email;
    }

    public function setPassword(string $plainPassword): void
    {
        if (strlen($plainPassword) < 4) {
            throw new InvalidArgumentException('Le mot de passe doit contenir au moins 4 caractères.');
        }
        $this->password = password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function setRole(string $role): void
    {
        $role = strtolower(trim($role));
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException('Rôle invalide. Valeurs : ' . implode(', ', self::ALLOWED_ROLES));
        }
        $this->role = $role;
    }

    public function setPoids(float $poids): void
    {
        if ($poids <= 0 || $poids > 500) throw new InvalidArgumentException('Poids invalide.');
        $this->poids = $poids;
    }

    public function setTaille(float $taille): void
    {
        if ($taille <= 0 || $taille > 300) throw new InvalidArgumentException('Taille invalide.');
        $this->taille = $taille;
    }

    public function setAge(?int $age): void
    {
        if ($age !== null && ($age <= 0 || $age > 120)) {
            throw new InvalidArgumentException('Âge invalide.');
        }
        $this->age = $age;
    }

    public function setSexe(?string $sexe): void
    {
        $sexe = $sexe ? strtolower(trim($sexe)) : null;
        if ($sexe !== null && !in_array($sexe, ['homme', 'femme'], true)) {
            throw new InvalidArgumentException('Sexe invalide. Valeurs : homme, femme');
        }
        $this->sexe = $sexe;
    }

    public function setObjectif(?string $objectif): void
    {
        $objectif = $objectif ? strtolower(trim($objectif)) : null;
        if ($objectif !== null && !in_array($objectif, ['perte', 'maintien', 'muscle'], true)) {
            throw new InvalidArgumentException('Objectif invalide. Valeurs : perte, maintien, muscle');
        }
        $this->objectif = $objectif;
    }

    public function setImc(float $imc): void        { $this->imc = round($imc, 1); }
    public function setCalories(int $cal): void     { $this->calories = $cal; }
    public function setSpecialite(?string $s): void { $this->specialite = $s ? trim($s) : null; }
    public function setBio(?string $bio): void      { $this->bio = $bio ? trim($bio) : null; }

    // -------------------------------------------------------------------------
    // Vérification du mot de passe en clair
    // -------------------------------------------------------------------------

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password);
    }

    // -------------------------------------------------------------------------
    // Utilitaires
    // -------------------------------------------------------------------------

    public function toArray(): array
    {
        return [
            'nom'          => $this->nom,
            'email'        => $this->email,
            'mot_de_passe' => $this->password,
            'role'         => $this->role,
            'poids'      => $this->poids,
            'taille'     => $this->taille,
            'age'        => $this->age,
            'sexe'       => $this->sexe,
            'objectif'   => $this->objectif,
            'imc'        => $this->imc,
            'calories'   => $this->calories,
            'specialite' => $this->specialite,
            'bio'        => $this->bio,
        ];
    }

    public function __toString(): string
    {
        return sprintf('User[id=%s, nom="%s", role=%s, email=%s]',
            $this->id ?? 'new', $this->nom, $this->role, $this->email);
    }

    // -------------------------------------------------------------------------
    // Calculs santé
    // -------------------------------------------------------------------------

    public function calculerImc(): float
    {
        if (!$this->poids || !$this->taille) return 0.0;
        $tailleM = $this->taille / 100;
        return round($this->poids / ($tailleM * $tailleM), 1);
    }

    public function getImcInterpretation(): string
    {
        $imc = $this->imc ?? $this->calculerImc();
        if ($imc < 18.5) return 'Insuffisance pondérale';
        if ($imc < 25.0) return 'Poids normal';
        if ($imc < 30.0) return 'Surpoids';
        return 'Obésité';
    }

    /**
     * Calcule le besoin calorique journalier (formule Mifflin-St Jeor).
     */
    public function calculerCalories(int $age = 25, string $sexe = 'homme', string $objectif = 'maintien'): int
    {
        if (!$this->poids || !$this->taille) return 0;
        if ($sexe === 'femme') {
            $bmr = 10 * $this->poids + 6.25 * $this->taille - 5 * $age - 161;
        } else {
            $bmr = 10 * $this->poids + 6.25 * $this->taille - 5 * $age + 5;
        }
        $tdee = $bmr * 1.55; // activité modérée
        return (int)round(match ($objectif) {
            'perte'   => $tdee - 500,
            'muscle'  => $tdee + 300,
            default   => $tdee,
        });
    }

    // -------------------------------------------------------------------------
    // Validation interne
    // -------------------------------------------------------------------------

    private function validate(): void
    {
        if ($this->nom === '') {
            throw new InvalidArgumentException('Le nom ne peut pas être vide.');
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email invalide : ' . $this->email);
        }
        if ($this->password === '') {
            throw new InvalidArgumentException('Le mot de passe ne peut pas être vide.');
        }
        if (!in_array($this->role, self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException('Rôle invalide.');
        }
        if ($this->role === 'user' && ($this->poids !== null && $this->poids <= 0)) {
            throw new InvalidArgumentException('Poids invalide.');
        }
    }
}
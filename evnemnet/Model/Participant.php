<?php
declare(strict_types=1);

class Participant
{
    private ?int $id = null;
    private string $nom = '';
    private string $prenom = '';
    private string $email = '';
    private string $mot_de_passe = '';
    private ?string $telephone = null;
    private float $poids = 0.0;
    private float $taille = 0.0;
    private float $imc = 0.0;
    private string $lieu = '';
    private string $objectif = 'maintien';
    private ?string $created_at = null;

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getMotDePasse(): string { return $this->mot_de_passe; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getPoids(): float { return $this->poids; }
    public function getTaille(): float { return $this->taille; }
    public function getImc(): float { return $this->imc; }
    public function getLieu(): string { return $this->lieu; }
    public function getObjectif(): string { return $this->objectif; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    public function setId(?int $id): self { $this->id = $id; return $this; }
    public function setNom(string $nom): self { $this->nom = trim($nom); return $this; }
    public function setPrenom(string $prenom): self { $this->prenom = trim($prenom); return $this; }
    public function setEmail(string $email): self { $this->email = trim($email); return $this; }
    public function setMotDePasse(string $motDePasse): self { $this->mot_de_passe = $motDePasse; return $this; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone !== null ? trim($telephone) : null; return $this; }
    public function setPoids(float $poids): self { $this->poids = $poids; return $this; }
    public function setTaille(float $taille): self { $this->taille = $taille; return $this; }
    public function setImc(float $imc): self { $this->imc = $imc; return $this; }
    public function setLieu(string $lieu): self { $this->lieu = trim($lieu); return $this; }
    public function setObjectif(string $objectif): self { $this->objectif = trim($objectif); return $this; }
    public function setCreatedAt(?string $createdAt): self { $this->created_at = $createdAt !== null ? trim($createdAt) : null; return $this; }
}

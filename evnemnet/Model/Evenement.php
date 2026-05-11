<?php
declare(strict_types=1);

class Evenement
{
    private ?int $id = null;
    private string $titre = '';
    private string $description = '';
    private string $date_evenement = '';
    private string $lieu = '';
    private float $prix = 0.0;
    private int $capacite = 0;
    private ?int $categorie_id = null;
    private ?string $image_url = null;
    private int $is_published = 1;
    private ?string $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDateEvenement(): string
    {
        return $this->date_evenement;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function getCapacite(): int
    {
        return $this->capacite;
    }

    public function getCategorieId(): ?int
    {
        return $this->categorie_id;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function getIsPublished(): int
    {
        return $this->is_published;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = trim($titre);
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = trim($description);
        return $this;
    }

    public function setDateEvenement(string $dateEvenement): self
    {
        $this->date_evenement = trim($dateEvenement);
        return $this;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = trim($lieu);
        return $this;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function setCapacite(int $capacite): self
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function setCategorieId(?int $categorieId): self
    {
        $this->categorie_id = $categorieId;
        return $this;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->image_url = $imageUrl !== null ? trim($imageUrl) : null;
        return $this;
    }

    public function setIsPublished(int $isPublished): self
    {
        $this->is_published = $isPublished ? 1 : 0;
        return $this;
    }

    public function setCreatedAt(?string $createdAt): self
    {
        $this->created_at = $createdAt !== null ? trim($createdAt) : null;
        return $this;
    }
}

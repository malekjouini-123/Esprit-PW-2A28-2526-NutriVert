<?php
declare(strict_types=1);

class Category
{
    private ?int $id = null;
    private string $nom = '';
    private string $description = '';
    private ?string $image_url = null;
    private int $is_published = 1;
    private ?string $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): string
    {
        return $this->description;
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

    public function setNom(string $nom): self
    {
        $this->nom = trim($nom);
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = trim($description);
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

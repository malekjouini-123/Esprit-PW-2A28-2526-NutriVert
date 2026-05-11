<?php
declare(strict_types=1);

class PersonalRecommendation
{
    private ?int $id = null;
    private string $titre = '';
    private string $description = '';
    private ?string $categorie_preferee = null;
    private ?float $budget_max = null;
    private ?string $localisation = null;
    private ?string $ai_suggestion = null;
    private ?string $evenements_suggeres = null;
    private ?string $created_at = null;

    public function getId(): ?int { return $this->id; }
    public function getTitre(): string { return $this->titre; }
    public function getDescription(): string { return $this->description; }
    public function getCategoriePreferee(): ?string { return $this->categorie_preferee; }
    public function getBudgetMax(): ?float { return $this->budget_max; }
    public function getLocalisation(): ?string { return $this->localisation; }
    public function getAiSuggestion(): ?string { return $this->ai_suggestion; }
    public function getEvenementsSuggeres(): ?string { return $this->evenements_suggeres; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    public function setId(?int $id): self { $this->id = $id; return $this; }
    public function setTitre(string $titre): self { $this->titre = trim($titre); return $this; }
    public function setDescription(string $description): self { $this->description = trim($description); return $this; }
    public function setCategoriePreferee(?string $categoriePreferee): self { $this->categorie_preferee = $categoriePreferee !== null ? trim($categoriePreferee) : null; return $this; }
    public function setBudgetMax(?float $budgetMax): self { $this->budget_max = $budgetMax; return $this; }
    public function setLocalisation(?string $localisation): self { $this->localisation = $localisation !== null ? trim($localisation) : null; return $this; }
    public function setAiSuggestion(?string $aiSuggestion): self { $this->ai_suggestion = $aiSuggestion !== null ? trim($aiSuggestion) : null; return $this; }
    public function setEvenementsSuggeres(?string $evenementsSuggeres): self { $this->evenements_suggeres = $evenementsSuggeres !== null ? trim($evenementsSuggeres) : null; return $this; }
    public function setCreatedAt(?string $createdAt): self { $this->created_at = $createdAt !== null ? trim($createdAt) : null; return $this; }
}

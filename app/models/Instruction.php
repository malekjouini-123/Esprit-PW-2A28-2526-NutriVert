<?php
class Instruction
{
    private ?int $id_instruction;
    private int $id_recette;
    private string $etape;
    private string $description;
    private string $ingredient_produit;

    public function __construct(?int $id_instruction = null, int $id_recette = 0, string $etape = '', string $description = '', string $ingredient_produit = '[]')
    {
        $this->id_instruction = $id_instruction;
        $this->id_recette = $id_recette;
        $this->etape = $etape;
        $this->description = $description;
        $this->ingredient_produit = $ingredient_produit;
    }

    public function getIdInstruction(): ?int
    {
        return $this->id_instruction;
    }

    public function setIdInstruction(?int $id_instruction): void
    {
        $this->id_instruction = $id_instruction;
    }

    public function getIdRecette(): int
    {
        return $this->id_recette;
    }

    public function setIdRecette(int $id_recette): void
    {
        $this->id_recette = $id_recette;
    }

    public function getEtape(): string
    {
        return $this->etape;
    }

    public function setEtape(string $etape): void
    {
        $this->etape = $etape;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getIngredientProduit(): string
    {
        return $this->ingredient_produit;
    }

    public function setIngredientProduit(string $ingredient_produit): void
    {
        $this->ingredient_produit = $ingredient_produit;
    }
}

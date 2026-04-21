<?php
class Recette
{
    private ?int $id_recette;
    private string $titre;
    private string $objectif;
    private string $regime;
    private int $duree;

    public function __construct(?int $id_recette = null, string $titre = '', string $objectif = '', string $regime = '', int $duree = 0)
    {
        $this->id_recette = $id_recette;
        $this->titre = $titre;
        $this->objectif = $objectif;
        $this->regime = $regime;
        $this->duree = $duree;
    }

    public function getIdRecette(): ?int
    {
        return $this->id_recette;
    }

    public function setIdRecette(?int $id_recette): void
    {
        $this->id_recette = $id_recette;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    public function getObjectif(): string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): void
    {
        $this->objectif = $objectif;
    }

    public function getRegime(): string
    {
        return $this->regime;
    }

    public function setRegime(string $regime): void
    {
        $this->regime = $regime;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): void
    {
        $this->duree = $duree;
    }
}

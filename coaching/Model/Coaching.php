<?php
declare(strict_types=1);

// =============================================================================
//  ENTITÉ  Coaching
//  Objet métier pur : attributs, constructeur, destructeur, getters, setters.
//  Aucune logique BDD ici — tout accès PDO est dans CoachingController.
// =============================================================================

class Coaching
{
    // -------------------------------------------------------------------------
    // Attributs privés
    // -------------------------------------------------------------------------

    private ?int    $id;
    private string  $title;
    private string  $description;
    private ?string $image;
    private int     $durationWeeks;
    private string  $difficultyLevel;
    private ?string $createdAt;

    public const ALLOWED_DIFFICULTIES = ['easy', 'medium', 'hard'];

    // -------------------------------------------------------------------------
    // Constructeur
    // -------------------------------------------------------------------------

    /**
     * Initialise l'entité depuis un tableau associatif (résultat PDO ou $_POST).
     *
     * @throws InvalidArgumentException si les données sont invalides.
     */
    public function __construct(array $data)
    {
        $this->id              = isset($data['id'])             ? (int)$data['id']                        : null;
        $this->title           = trim((string)($data['title']           ?? ''));
        $this->description     = trim((string)($data['description']     ?? ''));
        $this->image           = isset($data['image']) && !empty($data['image']) ? trim((string)$data['image']) : null;
        $this->durationWeeks   = isset($data['duration_weeks']) ? (int)$data['duration_weeks']            : 0;
        $this->difficultyLevel = strtolower(trim((string)($data['difficulty_level'] ?? '')));
        $this->createdAt       = isset($data['created_at'])     ? (string)$data['created_at']             : null;

        $this->validate();
    }



    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function getId(): ?int             { return $this->id; }
    public function getTitle(): string        { return $this->title; }
    public function getDescription(): string  { return $this->description; }
    public function getImage(): ?string       { return $this->image; }
    public function getDurationWeeks(): int   { return $this->durationWeeks; }
    public function getDifficultyLevel(): string { return $this->difficultyLevel; }
    public function getCreatedAt(): ?string   { return $this->createdAt; }

    // -------------------------------------------------------------------------
    // Setters (avec validation intégrée)
    // -------------------------------------------------------------------------

    public function setTitle(string $title): void
    {
        $title = trim($title);
        if ($title === '') {
            throw new InvalidArgumentException('Le titre ne peut pas être vide.');
        }
        $this->title = $title;
    }

    public function setDescription(string $description): void
    {
        $this->description = trim($description);
    }

    public function setImage(?string $image): void
    {
        $this->image = $image ? trim($image) : null;
    }

    public function setDurationWeeks(int $durationWeeks): void
    {
        if ($durationWeeks <= 0) {
            throw new InvalidArgumentException('La durée doit être un entier positif.');
        }
        $this->durationWeeks = $durationWeeks;
    }

    public function setDifficultyLevel(string $difficultyLevel): void
    {
        $level = strtolower(trim($difficultyLevel));
        if (!in_array($level, self::ALLOWED_DIFFICULTIES, true)) {
            throw new InvalidArgumentException(
                'Niveau invalide. Valeurs acceptées : ' . implode(', ', self::ALLOWED_DIFFICULTIES)
            );
        }
        $this->difficultyLevel = $level;
    }

    // -------------------------------------------------------------------------
    // Utilitaires
    // -------------------------------------------------------------------------

    /**
     * Retourne un tableau compatible avec les requêtes PDO INSERT / UPDATE.
     */
    public function toArray(): array
    {
        return [
            'title'            => $this->title,
            'description'      => $this->description,
            'image'            => $this->image,
            'duration_weeks'   => $this->durationWeeks,
            'difficulty_level' => $this->difficultyLevel,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            'Coaching[id=%s, title="%s", difficulty=%s, duration=%d weeks]',
            $this->id ?? 'new',
            $this->title,
            $this->difficultyLevel,
            $this->durationWeeks
        );
    }

    // -------------------------------------------------------------------------
    // Validation interne — appelée uniquement par __construct
    // -------------------------------------------------------------------------

    private function validate(): void
    {
        if ($this->title === '') {
            throw new InvalidArgumentException('Le titre ne peut pas être vide.');
        }
        if ($this->durationWeeks <= 0) {
            throw new InvalidArgumentException('La durée doit être un entier positif.');
        }
        if (!in_array($this->difficultyLevel, self::ALLOWED_DIFFICULTIES, true)) {
            throw new InvalidArgumentException(
                'Niveau invalide. Valeurs acceptées : ' . implode(', ', self::ALLOWED_DIFFICULTIES)
            );
        }
    }
}
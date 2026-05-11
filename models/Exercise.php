<?php
declare(strict_types=1);

// =============================================================================
//  ENTITÉ  Exercise
//  Objet métier pur : attributs, constructeur, destructeur, getters, setters.
//  Aucune logique BDD ici — tout accès PDO est dans ExerciseController.
// =============================================================================

class Exercise
{
    // -------------------------------------------------------------------------
    // Attributs privés
    // -------------------------------------------------------------------------

    private ?int    $id;
    private int     $coachingId;
    private string  $name;
    private string  $description;
    private int     $sets;
    private int     $reps;
    private string  $restTime;
    private string  $videoUrl;
    private string  $image;
    private int     $ordre;          // Ordre d'exécution
    private int     $dureeSec;       // Durée en secondes
    private ?string $createdAt;
    private ?string $coachingTitle;  // champ joint (lecture seule)

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
        $this->id            = isset($data['id'])         ? (int)$data['id']              : null;
        $this->coachingId    = isset($data['coaching_id']) ? (int)$data['coaching_id']    : 0;
        $this->name          = trim((string)($data['name']          ?? ''));
        $this->description   = trim((string)($data['description']   ?? ''));
        $this->sets          = isset($data['sets'])       ? (int)$data['sets']            : 0;
        $this->reps          = isset($data['reps'])       ? (int)$data['reps']            : 0;
        $this->restTime      = trim((string)($data['rest_time']     ?? ''));
        $this->videoUrl      = trim((string)($data['video_url']     ?? ''));
        $this->image         = trim((string)($data['image']         ?? ''));
        $this->ordre         = isset($data['ordre'])      ? (int)$data['ordre']           : 1;
        $this->dureeSec      = isset($data['duree_sec'])  ? (int)$data['duree_sec']       : 30;
        $this->createdAt     = isset($data['created_at']) ? (string)$data['created_at']   : null;
        $this->coachingTitle = isset($data['coaching_title']) ? (string)$data['coaching_title'] : null;

        $this->validate();
    }



    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function getId(): ?int             { return $this->id; }
    public function getCoachingId(): int      { return $this->coachingId; }
    public function getName(): string         { return $this->name; }
    public function getDescription(): string  { return $this->description; }
    public function getSets(): int            { return $this->sets; }
    public function getReps(): int            { return $this->reps; }
    public function getRestTime(): string     { return $this->restTime; }
    public function getVideoUrl(): string     { return $this->videoUrl; }
    public function getImage(): string        { return $this->image; }
    public function getOrdre(): int           { return $this->ordre; }
    public function getDureeSec(): int        { return $this->dureeSec; }
    public function getCreatedAt(): ?string   { return $this->createdAt; }
    public function getCoachingTitle(): ?string { return $this->coachingTitle; }

    // -------------------------------------------------------------------------
    // Setters (avec validation intégrée)
    // -------------------------------------------------------------------------

    public function setCoachingId(int $coachingId): void
    {
        if ($coachingId <= 0) {
            throw new InvalidArgumentException('L\'identifiant du programme doit être un entier positif.');
        }
        $this->coachingId = $coachingId;
    }

    public function setName(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Le nom de l\'exercice ne peut pas être vide.');
        }
        $this->name = $name;
    }

    public function setDescription(string $description): void
    {
        $this->description = trim($description);
    }

    public function setSets(int $sets): void
    {
        if ($sets <= 0) {
            throw new InvalidArgumentException('Le nombre de séries doit être un entier positif.');
        }
        $this->sets = $sets;
    }

    public function setReps(int $reps): void
    {
        if ($reps <= 0) {
            throw new InvalidArgumentException('Le nombre de répétitions doit être un entier positif.');
        }
        $this->reps = $reps;
    }

    public function setRestTime(string $restTime): void
    {
        $restTime = trim($restTime);
        if ($restTime === '') {
            throw new InvalidArgumentException('Le temps de repos ne peut pas être vide.');
        }
        $this->restTime = $restTime;
    }

    public function setVideoUrl(string $videoUrl): void
    {
        $videoUrl = trim($videoUrl);
        if ($videoUrl !== '' && !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('L\'URL de la vidéo n\'est pas valide.');
        }
        $this->videoUrl = $videoUrl;
    }

    public function setImage(string $image): void
    {
        $this->image = trim($image);
    }

    public function setOrdre(int $ordre): void
    {
        if ($ordre <= 0) {
            throw new InvalidArgumentException('L\'ordre doit être un entier positif.');
        }
        $this->ordre = $ordre;
    }

    public function setDureeSec(int $dureeSec): void
    {
        if ($dureeSec <= 0) {
            throw new InvalidArgumentException('La durée doit être un entier positif (en secondes).');
        }
        $this->dureeSec = $dureeSec;
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
            'coaching_id' => $this->coachingId,
            'name'        => $this->name,
            'description' => $this->description,
            'sets'        => $this->sets,
            'reps'        => $this->reps,
            'rest_time'   => $this->restTime,
            'video_url'   => $this->videoUrl,
            'image'       => $this->image,
            'ordre'       => $this->ordre,
            'duree_sec'   => $this->dureeSec,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            'Exercise[id=%s, name="%s", sets=%d, reps=%d, coaching_id=%d]',
            $this->id ?? 'new',
            $this->name,
            $this->sets,
            $this->reps,
            $this->coachingId
        );
    }

    // -------------------------------------------------------------------------
    // Validation interne — appelée uniquement par __construct
    // -------------------------------------------------------------------------

    private function validate(): void
    {
        if ($this->coachingId <= 0) {
            throw new InvalidArgumentException('L\'identifiant du programme doit être un entier positif.');
        }
        if ($this->name === '') {
            throw new InvalidArgumentException('Le nom de l\'exercice ne peut pas être vide.');
        }
        if ($this->sets <= 0) {
            throw new InvalidArgumentException('Le nombre de séries doit être un entier positif.');
        }
        if ($this->reps <= 0) {
            throw new InvalidArgumentException('Le nombre de répétitions doit être un entier positif.');
        }
        if ($this->restTime === '') {
            throw new InvalidArgumentException('Le temps de repos ne peut pas être vide.');
        }
        if ($this->videoUrl !== '' && !filter_var($this->videoUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('L\'URL de la vidéo n\'est pas valide.');
        }
        if ($this->ordre <= 0) {
            throw new InvalidArgumentException('L\'ordre doit être un entier positif.');
        }
        if ($this->dureeSec <= 0) {
            throw new InvalidArgumentException('La durée doit être un entier positif (en secondes).');
        }
    }
}
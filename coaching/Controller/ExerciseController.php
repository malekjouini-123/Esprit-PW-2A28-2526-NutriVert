<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Model/Exercise.php';
require_once __DIR__ . '/../Model/Coaching.php';

if (class_exists('ExerciseController')) return;

class ExerciseController
{
    private PDO $pdo;
    private array $allowedSortColumns = ['sets', 'reps', 'created_at', 'name'];

    public function __construct() { $this->pdo = getDB(); }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'index': case 'search': case 'sort': $this->renderIndex(); break;
            case 'create': $this->renderCreate(); break;
            case 'store':  $this->store();        break;
            case 'edit':   $this->edit();         break;
            case 'update': $this->update();       break;
            case 'delete': $this->delete();       break;
            default:       $this->renderIndex();  break;
        }
    }

    public function getAllForDashboard(): array          { return $this->dbGetAllRaw(); }
    public function getByIdForDashboard(int $id): ?array { return $this->dbGetByIdRaw($id); }
    public function getByCoachingForController(int $coachingId): array { return $this->dbGetByCoachingRaw($coachingId); }
    public function deleteById(int $id): bool           { return $this->dbDelete($id); }

    private function renderIndex(): void
    {
        $coachingPrograms = $this->dbGetAllCoachingRaw();
        $filterCoachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
        $filterCoachingId = $filterCoachingId !== false && $filterCoachingId !== null ? (int)$filterCoachingId : 0;
        $search = trim((string)($_GET['search'] ?? ''));
        $sort   = trim((string)($_GET['sort']   ?? ''));
        $sortMap = ['name_asc' => ['name','asc'], 'sets_asc' => ['sets','asc'], 'reps_desc' => ['reps','desc']];
        [$sortColumn, $sortOrder] = $sortMap[$sort] ?? ['', 'desc'];

        if ($search !== '' && $sortColumn !== '') {
            $exercises = $this->dbSearch($search);
            if ($filterCoachingId > 0) $exercises = array_values(array_filter($exercises, static fn(array $ex): bool => (int)$ex['coaching_id'] === $filterCoachingId));
            $exercises = $this->sortRows($exercises, $sortColumn, $sortOrder);
        } elseif ($search !== '') {
            $exercises = $this->dbSearch($search);
            if ($filterCoachingId > 0) $exercises = array_values(array_filter($exercises, static fn(array $ex): bool => (int)$ex['coaching_id'] === $filterCoachingId));
        } elseif ($sortColumn !== '') {
            $exercises = $this->dbSort($sortColumn, $sortOrder);
            if ($filterCoachingId > 0) $exercises = array_values(array_filter($exercises, static fn(array $ex): bool => (int)$ex['coaching_id'] === $filterCoachingId));
        } elseif ($filterCoachingId > 0) {
            $exercises = $this->dbGetByCoachingRaw($filterCoachingId);
        } else {
            $exercises = $this->dbGetAllRaw();
        }

        $flashMessage = $this->consumeFlash();
        include __DIR__ . '/../View/exercises.php';
    }

    private function renderCreate(): void
    {
        $coachingPrograms = $this->dbGetAllCoachingRaw();
        $filterCoachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
        $filterCoachingId = $filterCoachingId !== false && $filterCoachingId !== null ? (int)$filterCoachingId : 0;
        $redirectTarget   = trim((string)($_GET['redirect'] ?? ''));
        $flashMessage     = $this->consumeFlash();
        include __DIR__ . '/../View/exercises_create.php';
    }

    private function edit(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) { $this->setFlash('Identifiant invalide.'); $this->redirect('index.php?controller=exercise&action=index'); }
        $editingExercise = $this->dbGetByIdRaw($id);
        if (!$editingExercise) { $this->setFlash('Exercice introuvable.'); $this->redirect('index.php?controller=exercise&action=index'); }
        $coachingPrograms = $this->dbGetAllCoachingRaw();
        $filterCoachingId = (int)$editingExercise['coaching_id'];
        $coachingId       = $filterCoachingId;
        $redirectTarget   = trim((string)($_GET['redirect'] ?? ''));
        $flashMessage     = $this->consumeFlash();
        include __DIR__ . '/../View/exercises_edit.php';
    }

    private function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('index.php?controller=exercise&action=index'); }
        [$data, $errors] = $this->validateExercise($_POST);
        [$imagePath, $imageError] = $this->handleImageUpload(true);
        if ($imageError !== null) $errors[] = $imageError;
        if ($errors !== []) { if ($imagePath) $this->deleteUploadedImage($imagePath); $this->setFlash(implode(' ', $errors)); $this->redirectBackToExercises(null, $data['coaching_id']); }
        $data['image'] = $imagePath ?? '';
        $this->dbCreate($data);
        $this->setFlash('Exercice créé avec succès.');
        $this->redirectBackToExercises(null, $data['coaching_id']);
    }

    private function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('index.php?controller=exercise&action=index'); }
        $id = $this->getIdFromGet();
        if ($id === null) {
            $this->setFlash('Identifiant invalide.');
            $fallbackCoachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
            $fallbackCoachingId = $fallbackCoachingId !== false && $fallbackCoachingId !== null ? (int)$fallbackCoachingId : 0;
            $this->redirectBackToExercises(null, $fallbackCoachingId);
        }
        $existing = $this->dbGetByIdRaw($id);
        if (!$existing) { $this->setFlash('Exercice introuvable.'); $this->redirectBackToExercises(); }
        [$data, $errors] = $this->validateExercise($_POST);
        [$imagePath, $imageError] = $this->handleImageUpload(false, $existing['image']);
        if ($imageError !== null) $errors[] = $imageError;
        if ($errors !== []) { if ($imagePath && $imagePath !== $existing['image']) $this->deleteUploadedImage($imagePath); $this->setFlash(implode(' ', $errors)); $this->redirectBackToExercises($id, $data['coaching_id']); }
        $data['image'] = $imagePath ?? '';
        $this->dbUpdate($id, $data);
        $this->setFlash('Exercice mis à jour avec succès.');
        $this->redirectBackToExercises(null, $data['coaching_id']);
    }

    private function delete(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) { $this->setFlash('Identifiant invalide.'); $this->redirectBackToExercises(); }
        $exercise = $this->dbGetByIdRaw($id);
        if ($exercise && !empty($exercise['image'])) { $absolutePath = __DIR__ . '/../../' . $exercise['image']; if (is_file($absolutePath)) @unlink($absolutePath); }
        $coachingId = (int)($exercise['coaching_id'] ?? 0);
        $this->dbDelete($id);
        $this->setFlash('Exercice supprimé avec succès.');
        $this->redirectBackToExercises(null, $coachingId);
    }

    private function dbGetAllRaw(): array
    {
        $stmt = $this->pdo->query('SELECT e.*, c.title AS coaching_title FROM exercises e LEFT JOIN coaching_programs c ON c.id = e.coaching_id ORDER BY e.created_at DESC');
        return $stmt->fetchAll();
    }

    private function dbGetByIdRaw(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT e.*, c.title AS coaching_title FROM exercises e LEFT JOIN coaching_programs c ON c.id = e.coaching_id WHERE e.id = :id');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    private function dbGetByCoachingRaw(int $coachingId): array
    {
        $stmt = $this->pdo->prepare('SELECT e.*, c.title AS coaching_title FROM exercises e LEFT JOIN coaching_programs c ON c.id = e.coaching_id WHERE e.coaching_id = :coaching_id ORDER BY e.created_at DESC');
        $stmt->execute(['coaching_id' => $coachingId]);
        return $stmt->fetchAll();
    }

    private function dbCreate(array $data): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO exercises (coaching_id, name, description, sets, reps, rest_time, video_url, image) VALUES (:coaching_id, :name, :description, :sets, :reps, :rest_time, :video_url, :image)');
        return $stmt->execute(['coaching_id'=>$data['coaching_id'],'name'=>$data['name'],'description'=>$data['description'],'sets'=>$data['sets'],'reps'=>$data['reps'],'rest_time'=>$data['rest_time'],'video_url'=>$data['video_url']??'','image'=>$data['image']??'']);
    }

    private function dbUpdate(int $id, array $data): bool
    {
        if (!empty($data['image'])) {
            $stmt = $this->pdo->prepare('UPDATE exercises SET coaching_id=:coaching_id,name=:name,description=:description,sets=:sets,reps=:reps,rest_time=:rest_time,video_url=:video_url,image=:image WHERE id=:id');
            return $stmt->execute(['id'=>$id,'coaching_id'=>$data['coaching_id'],'name'=>$data['name'],'description'=>$data['description'],'sets'=>$data['sets'],'reps'=>$data['reps'],'rest_time'=>$data['rest_time'],'video_url'=>$data['video_url']??'','image'=>$data['image']]);
        }
        $stmt = $this->pdo->prepare('UPDATE exercises SET coaching_id=:coaching_id,name=:name,description=:description,sets=:sets,reps=:reps,rest_time=:rest_time,video_url=:video_url WHERE id=:id');
        return $stmt->execute(['id'=>$id,'coaching_id'=>$data['coaching_id'],'name'=>$data['name'],'description'=>$data['description'],'sets'=>$data['sets'],'reps'=>$data['reps'],'rest_time'=>$data['rest_time'],'video_url'=>$data['video_url']??'']);
    }

    private function dbDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM exercises WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    private function dbSearch(string $keyword): array
    {
        $stmt = $this->pdo->prepare('SELECT e.*, c.title AS coaching_title FROM exercises e LEFT JOIN coaching_programs c ON c.id = e.coaching_id WHERE e.name LIKE :keyword ORDER BY e.created_at DESC');
        $stmt->execute(['keyword' => '%'.$keyword.'%']);
        return $stmt->fetchAll();
    }

    private function dbSort(string $column, string $order): array
    {
        $safeColumn = in_array($column, $this->allowedSortColumns, true) ? $column : 'created_at';
        $safeOrder  = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
        $sql = "SELECT e.*, c.title AS coaching_title FROM exercises e LEFT JOIN coaching_programs c ON c.id = e.coaching_id ORDER BY e.{$safeColumn} {$safeOrder}";
        return $this->pdo->query($sql)->fetchAll();
    }

    private function dbGetAllCoachingRaw(): array
    {
        $stmt = $this->pdo->query('SELECT id, title FROM coaching_programs ORDER BY title ASC');
        return $stmt->fetchAll();
    }

    private function dbCoachingExists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM coaching_programs WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (bool)$stmt->fetch();
    }

    private function validateExercise(array $input): array
    {
        $errors = [];
        $coachingId  = filter_var($input['coaching_id'] ?? null, FILTER_VALIDATE_INT);
        $name        = trim(strip_tags((string)($input['name']        ?? '')));
        $description = trim(strip_tags((string)($input['description'] ?? '')));
        $sets        = filter_var($input['sets'] ?? null, FILTER_VALIDATE_INT);
        $reps        = filter_var($input['reps'] ?? null, FILTER_VALIDATE_INT);
        $restTime    = trim(strip_tags((string)($input['rest_time']   ?? '')));
        $videoUrl    = trim((string)($input['video_url'] ?? ''));
        if ($coachingId === false || $coachingId <= 0 || !$this->dbCoachingExists((int)$coachingId)) $errors[] = 'Veuillez sélectionner un programme valide.';
        if ($name === '')                              $errors[] = 'Le nom de l\'exercice est requis.';
        if ($sets === false || $sets <= 0)             $errors[] = 'Le nombre de séries doit être un entier positif.';
        if ($reps === false || $reps <= 0)             $errors[] = 'Le nombre de répétitions doit être un entier positif.';
        if ($restTime === '')                          $errors[] = 'Le temps de repos est requis.';
        if ($videoUrl !== '' && !filter_var($videoUrl, FILTER_VALIDATE_URL)) $errors[] = 'L\'URL de la vidéo doit être une URL valide.';
        return [['coaching_id'=>(int)($coachingId?:0),'name'=>$name,'description'=>$description,'sets'=>(int)($sets?:0),'reps'=>(int)($reps?:0),'rest_time'=>$restTime,'video_url'=>$videoUrl], $errors];
    }

    private function handleImageUpload(bool $required, ?string $oldImage = null): array
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return $required ? [null, 'L\'image est requise.'] : [$oldImage, null];
        }
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) return [null, 'Échec de l\'upload de l\'image.'];
        $maxFileSize = 2 * 1024 * 1024;
        if ((int)($_FILES['image']['size'] ?? 0) <= 0 || (int)$_FILES['image']['size'] > $maxFileSize) return [null, 'L\'image ne doit pas dépasser 2 Mo.'];
        $tmpName   = $_FILES['image']['tmp_name'];
        $extension = strtolower(pathinfo((string)($_FILES['image']['name'] ?? ''), PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = $finfo ? finfo_file($finfo, $tmpName) : null;
        if ($finfo) finfo_close($finfo);
        $allowedExtensions = ['jpg'=>'jpg','jpeg'=>'jpg','png'=>'png'];
        $allowedMimes      = ['image/jpeg'=>'jpg','image/png'=>'png'];
        if (!isset($allowedMimes[$mime]) || !isset($allowedExtensions[$extension]) || $allowedExtensions[$extension] !== $allowedMimes[$mime]) return [null, 'Seuls les fichiers JPG et PNG sont autorisés.'];
        $uploadDir = __DIR__ . '/../../uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName    = 'exercise_' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];
        $destination = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $destination)) return [null, 'Impossible de sauvegarder l\'image uploadée.'];
        if ($oldImage) { $oldPath = __DIR__ . '/../../' . $oldImage; if (is_file($oldPath)) @unlink($oldPath); }
        return ['uploads/' . $fileName, null];
    }

    private function sortRows(array $rows, string $column, string $order): array
    {
        $safeOrder  = strtolower($order) === 'asc' ? 1 : -1;
        $safeColumn = in_array($column, ['sets','reps','created_at','name'], true) ? $column : 'created_at';
        usort($rows, static function (array $a, array $b) use ($safeColumn, $safeOrder): int { return (($a[$safeColumn] <=> $b[$safeColumn]) * $safeOrder); });
        return $rows;
    }

    private function getIdFromGet(): ?int
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        return ($id !== false && $id !== null) ? $id : null;
    }

    private function setFlash(string $message): void    { $_SESSION['flash_message'] = $message; }

    private function consumeFlash(): ?string
    {
        if (!isset($_SESSION['flash_message'])) return null;
        $msg = (string)$_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }

    private function redirect(string $url): void { header('Location: ' . $url); exit; }

    private function deleteUploadedImage(string $relativePath): void
    {
        $absolutePath = __DIR__ . '/../../' . ltrim($relativePath, '/');
        if (is_file($absolutePath)) @unlink($absolutePath);
    }

    private function redirectBackToExercises(?int $editId = null, int $coachingId = 0): void
    {
        $redirect = trim((string)($_GET['redirect'] ?? ''));
        if ($redirect === 'dashboard') {
            $url = 'index.php?controller=dashboard&action=index';
            $url .= $editId !== null ? '&view=exercises_edit&id='.$editId : '&view=exercises';
            if ($coachingId > 0) $url .= '&coaching_id='.$coachingId;
            $this->redirect($url);
        }
        if ($redirect === 'exercises') {
            $url = 'index.php?controller=exercise&action=index';
            if ($coachingId > 0) $url .= '&coaching_id='.$coachingId;
            $this->redirect($url);
        }
        if ($redirect === 'coaching') {
            if ($editId !== null) { $url = 'index.php?controller=exercise&action=edit&id='.$editId; if ($coachingId > 0) $url .= '&coaching_id='.$coachingId; $url .= '&redirect=coaching'; $this->redirect($url); }
            $this->redirect('index.php?controller=coaching&action=index');
        }
        if ($editId !== null) { $url = 'index.php?controller=exercise&action=edit&id='.$editId; if ($coachingId > 0) $url .= '&coaching_id='.$coachingId; $this->redirect($url); }
        $url = 'index.php?controller=exercise&action=index';
        if ($coachingId > 0) $url .= '&coaching_id='.$coachingId;
        $this->redirect($url);
    }
}

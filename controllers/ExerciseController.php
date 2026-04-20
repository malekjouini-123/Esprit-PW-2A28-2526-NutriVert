<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Exercise.php';
require_once __DIR__ . '/../models/Coaching.php';

class ExerciseController
{
    private Exercise $exerciseModel;
    private Coaching $coachingModel;

    public function __construct()
    {
        $this->exerciseModel = new Exercise();
        $this->coachingModel = new Coaching();
    }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'index':
            case 'search':
            case 'sort':
                $this->renderIndex();
                break;
            case 'create':
                $this->renderCreate();
                break;
            case 'store':
                $this->store();
                break;
            case 'edit':
                $this->edit();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                $this->renderIndex();
                break;
        }
    }

    public function getAllForDashboard(): array
    {
        return $this->exerciseModel->getAll();
    }

    public function getByIdForDashboard(int $id): ?array
    {
        return $this->exerciseModel->getById($id);
    }

    private function renderIndex(): void
    {
        $coachingPrograms = $this->coachingModel->getAll();

        $filterCoachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
        $filterCoachingId = $filterCoachingId !== false && $filterCoachingId !== null ? $filterCoachingId : 0;

        $search = trim((string)($_GET['search'] ?? ''));
        $sort   = trim((string)($_GET['sort'] ?? ''));

        // Map UI sort keys to [column, order] — avoids explode() breaking on multi-word columns.
        $sortMap = [
            'name_asc'   => ['name',        'asc'],
            'sets_asc'   => ['sets',         'asc'],
            'reps_desc'  => ['reps',         'desc'],
        ];
        [$sortColumn, $sortOrder] = $sortMap[$sort] ?? ['', 'desc'];

        if ($search !== '' && $sortColumn !== '') {
            // Search first, then filter by program if needed, then sort in-memory.
            $exercises = $this->exerciseModel->search($search);
            if ($filterCoachingId > 0) {
                $exercises = array_values(array_filter(
                    $exercises,
                    static fn(array $ex): bool => (int)$ex['coaching_id'] === $filterCoachingId
                ));
            }
            $exercises = $this->sortRows($exercises, $sortColumn, $sortOrder);
        } elseif ($search !== '') {
            $exercises = $this->exerciseModel->search($search);
            if ($filterCoachingId > 0) {
                $exercises = array_values(array_filter(
                    $exercises,
                    static fn(array $ex): bool => (int)$ex['coaching_id'] === $filterCoachingId
                ));
            }
        } elseif ($sortColumn !== '') {
            $exercises = $this->exerciseModel->sort($sortColumn, $sortOrder);
            if ($filterCoachingId > 0) {
                $exercises = array_values(array_filter(
                    $exercises,
                    static fn(array $ex): bool => (int)$ex['coaching_id'] === $filterCoachingId
                ));
            }
        } elseif ($filterCoachingId > 0) {
            $exercises = $this->exerciseModel->getByCoaching($filterCoachingId);
        } else {
            $exercises = $this->exerciseModel->getAll();
        }

        $flashMessage = $this->consumeFlash();

        include __DIR__ . '/../views/front/exercises.php';
    }

    private function renderCreate(): void
    {
        $coachingPrograms = $this->coachingModel->getAll();
        $filterCoachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
        $filterCoachingId = $filterCoachingId !== false && $filterCoachingId !== null ? $filterCoachingId : 0;
        $redirectTarget = trim((string)($_GET['redirect'] ?? ''));
        $flashMessage = $this->consumeFlash();

        include __DIR__ . '/../views/front/exercises_create.php';
    }

    private function edit(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) {
            $this->setFlash('Invalid exercise ID.');
            $this->redirect('index.php?controller=exercise&action=index');
        }

        $editingExercise = $this->exerciseModel->getById($id);
        if (!$editingExercise) {
            $this->setFlash('Exercise not found.');
            $this->redirect('index.php?controller=exercise&action=index');
        }

        $coachingPrograms = $this->coachingModel->getAll();
        $filterCoachingId = (int)$editingExercise['coaching_id'];
        $redirectTarget = trim((string)($_GET['redirect'] ?? ''));
        $flashMessage = $this->consumeFlash();
        include __DIR__ . '/../views/front/exercises_edit.php';
    }

    private function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=exercise&action=index');
        }

        [$data, $errors] = $this->validateExercise($_POST);
        [$imagePath, $imageError] = $this->handleImageUpload(true);

        if ($imageError !== null) {
            $errors[] = $imageError;
        }

        if ($errors !== []) {
            if ($imagePath) {
                $this->deleteUploadedImage($imagePath);
            }
            $this->setFlash(implode(' ', $errors));
            $this->redirectBackToExercises(null, $data['coaching_id']);
        }

        $data['image'] = $imagePath ?? '';
        $this->exerciseModel->create($data);

        $this->setFlash('Exercise created successfully.');
        $this->redirectBackToExercises(null, $data['coaching_id']);
    }

    private function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=exercise&action=index');
        }

        $id = $this->getIdFromGet();
        if ($id === null) {
            $this->setFlash('Invalid exercise ID.');
            $fallbackCoachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
            $fallbackCoachingId = $fallbackCoachingId !== false && $fallbackCoachingId !== null ? (int)$fallbackCoachingId : 0;
            $this->redirectBackToExercises(null, $fallbackCoachingId);
        }

        $existing = $this->exerciseModel->getById($id);
        if (!$existing) {
            $this->setFlash('Exercise not found.');
            $this->redirectBackToExercises();
        }

        [$data, $errors] = $this->validateExercise($_POST);
        [$imagePath, $imageError] = $this->handleImageUpload(false, $existing['image']);

        if ($imageError !== null) {
            $errors[] = $imageError;
        }

        if ($errors !== []) {
            if ($imagePath && $imagePath !== $existing['image']) {
                $this->deleteUploadedImage($imagePath);
            }
            $this->setFlash(implode(' ', $errors));
            $this->redirectBackToExercises($id, $data['coaching_id']);
        }

        $data['image'] = $imagePath ?? '';
        $this->exerciseModel->update($id, $data);

        $this->setFlash('Exercise updated successfully.');
        $this->redirectBackToExercises(null, $data['coaching_id']);
    }

    private function delete(): void
    {
        $id = $this->getIdFromGet();
        if ($id === null) {
            $this->setFlash('Invalid exercise ID.');
            $this->redirectBackToExercises();
        }

        $exercise = $this->exerciseModel->getById($id);
        if ($exercise && !empty($exercise['image'])) {
            $absolutePath = __DIR__ . '/../' . $exercise['image'];
            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }

        $coachingId = (int)($exercise['coaching_id'] ?? 0);
        $this->exerciseModel->delete($id);
        $this->setFlash('Exercise deleted successfully.');
        $this->redirectBackToExercises(null, $coachingId);
    }

    private function validateExercise(array $input): array
    {
        $errors = [];

        $coachingId = filter_var($input['coaching_id'] ?? null, FILTER_VALIDATE_INT);
        $name = trim(strip_tags((string)($input['name'] ?? '')));
        $description = trim(strip_tags((string)($input['description'] ?? '')));
        $sets = filter_var($input['sets'] ?? null, FILTER_VALIDATE_INT);
        $reps = filter_var($input['reps'] ?? null, FILTER_VALIDATE_INT);
        $restTime = trim(strip_tags((string)($input['rest_time'] ?? '')));
        $videoUrl = trim((string)($input['video_url'] ?? ''));

        if ($coachingId === false || $coachingId <= 0 || !$this->coachingModel->getById((int)$coachingId)) {
            $errors[] = 'Please select a valid coaching program.';
        }

        if ($name === '') {
            $errors[] = 'Exercise name is required.';
        }

        if ($sets === false || $sets <= 0) {
            $errors[] = 'Sets must be a positive number.';
        }

        if ($reps === false || $reps <= 0) {
            $errors[] = 'Reps must be a positive number.';
        }

        if ($restTime === '') {
            $errors[] = 'Rest time is required.';
        }

        if ($videoUrl !== '' && !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Video URL must be a valid URL (e.g. https://youtube.com/...).';
        }

        return [[
            'coaching_id' => (int)($coachingId ?: 0),
            'name'        => $name,
            'description' => $description,
            'sets'        => (int)($sets ?: 0),
            'reps'        => (int)($reps ?: 0),
            'rest_time'   => $restTime,
            'video_url'   => $videoUrl,
        ], $errors];
    }

    private function handleImageUpload(bool $required, ?string $oldImage = null): array
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return $required ? [null, 'Image is required.'] : [$oldImage, null];
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Failed to upload image.'];
        }

        $maxFileSize = 2 * 1024 * 1024;
        if ((int)($_FILES['image']['size'] ?? 0) <= 0 || (int)$_FILES['image']['size'] > $maxFileSize) {
            return [null, 'Image size must be 2MB or less.'];
        }

        $tmpName = $_FILES['image']['tmp_name'];
        $extension = strtolower(pathinfo((string)($_FILES['image']['name'] ?? ''), PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $tmpName) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedExtensions = [
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'png' => 'png',
        ];
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        if (
            !isset($allowedMimes[$mime]) ||
            !isset($allowedExtensions[$extension]) ||
            $allowedExtensions[$extension] !== $allowedMimes[$mime]
        ) {
            return [null, 'Only JPG and PNG files are allowed.'];
        }

        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'exercise_' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];
        $destination = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($tmpName, $destination)) {
            return [null, 'Unable to save uploaded image.'];
        }

        if ($oldImage) {
            $oldPath = __DIR__ . '/../' . $oldImage;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return ['uploads/' . $fileName, null];
    }

    private function sortRows(array $rows, string $column, string $order): array
    {
        $safeOrder = strtolower($order) === 'asc' ? 1 : -1;
        $safeColumn = in_array($column, ['sets', 'reps', 'created_at', 'name'], true) ? $column : 'created_at';

        usort($rows, static function (array $a, array $b) use ($safeColumn, $safeOrder): int {
            return (($a[$safeColumn] <=> $b[$safeColumn]) * $safeOrder);
        });

        return $rows;
    }

    private function getIdFromGet(): ?int
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        return $id !== false && $id !== null ? $id : null;
    }

    private function setFlash(string $message): void
    {
        $_SESSION['flash_message'] = $message;
    }

    private function consumeFlash(): ?string
    {
        if (!isset($_SESSION['flash_message'])) {
            return null;
        }

        $message = (string)$_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    private function deleteUploadedImage(string $relativePath): void
    {
        $absolutePath = __DIR__ . '/../' . ltrim($relativePath, '/');
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function redirectBackToExercises(?int $editId = null, int $coachingId = 0): void
    {
        $redirect = trim((string)($_GET['redirect'] ?? ''));

        if ($redirect === 'dashboard') {
            $url = 'index.php?controller=dashboard&action=index';
            if ($editId !== null) {
                $url .= '&view=exercises_edit&id=' . $editId;
            } else {
                $url .= '&view=exercises';
            }
            if ($coachingId > 0) {
                $url .= '&coaching_id=' . $coachingId;
            }
            $this->redirect($url);
        }

        if ($redirect === 'exercises') {
            $url = 'index.php?controller=exercise&action=index';
            if ($coachingId > 0) {
                $url .= '&coaching_id=' . $coachingId;
            }
            $this->redirect($url);
        }

        if ($redirect === 'coaching') {
            if ($editId !== null) {
                $url = 'index.php?controller=exercise&action=edit&id=' . $editId;
                if ($coachingId > 0) {
                    $url .= '&coaching_id=' . $coachingId;
                }
                $url .= '&redirect=coaching';
                $this->redirect($url);
            }

            $this->redirect('index.php?controller=coaching&action=index');
        }

        if ($editId !== null) {
            $url = 'index.php?controller=exercise&action=edit&id=' . $editId;
            if ($coachingId > 0) {
                $url .= '&coaching_id=' . $coachingId;
            }
            $this->redirect($url);
        }

        $url = 'index.php?controller=exercise&action=index';
        if ($coachingId > 0) {
            $url .= '&coaching_id=' . $coachingId;
        }
        $this->redirect($url);
    }
}

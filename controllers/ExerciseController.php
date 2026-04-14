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

        $keyword = trim((string)($_GET['keyword'] ?? ''));
        $sortColumn = trim((string)($_GET['sort_column'] ?? ''));
        $sortOrder = trim((string)($_GET['sort_order'] ?? 'desc'));

        if ($keyword !== '') {
            $exercises = $this->exerciseModel->search($keyword);
        } elseif ($sortColumn !== '') {
            $exercises = $this->exerciseModel->sort($sortColumn, $sortOrder);
        } elseif ($filterCoachingId > 0) {
            $exercises = $this->exerciseModel->getByCoaching($filterCoachingId);
        } else {
            $exercises = $this->exerciseModel->getAll();
        }

        if ($filterCoachingId > 0 && ($keyword !== '' || $sortColumn !== '')) {
            $exercises = array_values(array_filter(
                $exercises,
                static fn(array $exercise): bool => (int)$exercise['coaching_id'] === $filterCoachingId
            ));
        }

        if ($keyword !== '' && $sortColumn !== '') {
            $exercises = $this->sortRows($exercises, $sortColumn, $sortOrder);
        }

        $flashMessage = $this->consumeFlash();

        include __DIR__ . '/../views/front/exercises.php';
    }

    private function renderCreate(): void
    {
        $coachingPrograms = $this->coachingModel->getAll();
        $filterCoachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
        $filterCoachingId = $filterCoachingId !== false && $filterCoachingId !== null ? $filterCoachingId : 0;
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
            $this->redirectBackToExercises(null, $data['coaching_id']);
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

        return [[
            'coaching_id' => (int)($coachingId ?: 0),
            'name' => $name,
            'description' => $description,
            'sets' => (int)($sets ?: 0),
            'reps' => (int)($reps ?: 0),
            'rest_time' => $restTime,
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
        $safeColumn = in_array($column, ['sets', 'reps', 'created_at'], true) ? $column : 'created_at';

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
        if (($_GET['redirect'] ?? '') === 'dashboard') {
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

        $url = 'index.php?controller=exercise&action=index';
        if ($coachingId > 0) {
            $url .= '&coaching_id=' . $coachingId;
        }
        if ($editId !== null) {
            $url .= '&action=edit&id=' . $editId;
        }
        $this->redirect($url);
    }
}

<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/controllers/CoachingController.php';
require_once __DIR__ . '/controllers/ExerciseController.php';

// Single-entry procedural router.
$page = $_GET['controller'] ?? 'coaching';
$action = $_GET['action'] ?? 'index';

switch ($page) {
    case 'coaching':
        (new CoachingController())->handle($action);
        break;

    case 'exercise':
        (new ExerciseController())->handle($action);
        break;

    case 'dashboard':
        $coachingController = new CoachingController();
        $exerciseController = new ExerciseController();
        $coachingPrograms = $coachingController->getAllForDashboard();
        $exercises = $exerciseController->getAllForDashboard();
        $view = $_GET['view'] ?? 'coaching_list';

        $coachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
        $coachingId = $coachingId !== false && $coachingId !== null ? (int)$coachingId : 0;

        $editingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $editingId = $editingId !== false && $editingId !== null ? (int)$editingId : 0;

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        switch ($view) {
            case 'coaching_create':
                include __DIR__ . '/views/back/coaching_create.php';
                break;

            case 'coaching_edit':
                $editingProgram = $editingId > 0 ? $coachingController->getByIdForDashboard($editingId) : null;
                if (!$editingProgram) {
                    $_SESSION['flash_message'] = 'Coaching program not found.';
                    header('Location: index.php?controller=dashboard&action=index');
                    exit;
                }
                include __DIR__ . '/views/back/coaching_edit.php';
                break;

            case 'exercises':
                if ($coachingId <= 0) {
                    $_SESSION['flash_message'] = 'Please select a coaching program.';
                    header('Location: index.php?controller=dashboard&action=index');
                    exit;
                }
                $selectedProgram = $coachingController->getByIdForDashboard($coachingId);
                if (!$selectedProgram) {
                    $_SESSION['flash_message'] = 'Coaching program not found.';
                    header('Location: index.php?controller=dashboard&action=index');
                    exit;
                }
                $exercises = array_values(array_filter(
                    $exercises,
                    static fn(array $exercise): bool => (int)$exercise['coaching_id'] === $coachingId
                ));
                include __DIR__ . '/views/back/exercises.php';
                break;

            case 'exercises_create':
                if ($coachingId <= 0) {
                    $_SESSION['flash_message'] = 'Please select a coaching program.';
                    header('Location: index.php?controller=dashboard&action=index');
                    exit;
                }
                $selectedProgram = $coachingController->getByIdForDashboard($coachingId);
                if (!$selectedProgram) {
                    $_SESSION['flash_message'] = 'Coaching program not found.';
                    header('Location: index.php?controller=dashboard&action=index');
                    exit;
                }
                include __DIR__ . '/views/back/exercises_create.php';
                break;

            case 'exercises_edit':
                $editingExercise = $editingId > 0 ? $exerciseController->getByIdForDashboard($editingId) : null;
                if (!$editingExercise) {
                    $_SESSION['flash_message'] = 'Exercise not found.';
                    header('Location: index.php?controller=dashboard&action=index');
                    exit;
                }
                $coachingId = (int)$editingExercise['coaching_id'];
                $selectedProgram = $coachingController->getByIdForDashboard($coachingId);
                include __DIR__ . '/views/back/exercises_edit.php';
                break;

            case 'coaching_list':
            default:
                include __DIR__ . '/views/back/dashboard.php';
                break;
        }
        break;

    default:
        header('Location: index.php?controller=coaching&action=index');
        exit;
}

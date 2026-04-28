<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ExerciseController.php';
require_once __DIR__ . '/controllers/CoachingController.php';
require_once __DIR__ . '/controllers/UserDashboardController.php';

// ✅ Vérifier si l'utilisateur essaie d'accéder au dashboard sans être admin
function requireAdmin(): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        $_SESSION['flash_message'] = 'Accès non autorisé. Vous devez être admin.';
        header('Location: index.php');
        exit;
    }
}

// ✅ Vérifier si l'utilisateur essaie d'accéder à coaching sans être coach
function requireCoach(): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'coach') {
        $_SESSION['flash_message'] = 'Accès non autorisé. Vous devez être coach.';
        header('Location: index.php');
        exit;
    }
}

// ✅ Vérifier si l'utilisateur est connecté
function requireLogin(): void {
    if (empty($_SESSION['user'])) {
        $_SESSION['flash_message'] = 'Veuillez vous connecter.';
        header('Location: index.php?view=login');
        exit;
    }
}

// ✅ Déterminer la page et l'action
$page   = $_GET['controller'] ?? '';
$action = $_GET['action']     ?? 'index';
$view   = $_GET['view']       ?? '';

// ✅ Gestion des vues simples (sans contrôleur)
if (!$page && $view) {
    $flashMessage = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    
    match ($view) {
        'register'  => include __DIR__ . '/views/front/register.php',
        'login'     => include __DIR__ . '/views/front/login.php',
        'interface' => include __DIR__ . '/views/front/interface.php',
        default     => header('Location: index.php'),
    };
    exit;
}

// ✅ Si aucune page, afficher l'interface (page d'accueil)
if (!$page) {
    $flashMessage = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    include __DIR__ . '/views/front/interface.php';
    exit;
}

switch ($page) {
    case 'user':
        (new UserController())->handle($action);
        break;

    case 'coaching':
        if (empty($_SESSION['user']) || (!in_array($_SESSION['user']['role'], ['coach', 'admin'], true))) {
            $_SESSION['flash_message'] = 'Accès non autorisé. Vous devez être coach ou admin.';
            header('Location: index.php');
            exit;
        }
        (new CoachingController())->handle($action);
        break;

    case 'exercise':
        if (empty($_SESSION['user']) || (!in_array($_SESSION['user']['role'], ['coach', 'admin'], true))) {
            $_SESSION['flash_message'] = 'Accès non autorisé. Vous devez être coach ou admin.';
            header('Location: index.php');
            exit;
        }
        (new ExerciseController())->handle($action);
        break;

    case 'user_dashboard':
        requireLogin();
        (new UserDashboardController())->handle($action);
        break;

    case 'dashboard':
        requireAdmin();
        $coachingController = new CoachingController();
        $exerciseController = new ExerciseController();
        $coachingPrograms   = $coachingController->getAllForDashboard();
        $exercises          = $exerciseController->getAllForDashboard();
        $view               = $_GET['view'] ?? 'coaching_list';

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
                $editingProgram = $editingId > 0
                    ? $coachingController->getByIdForDashboard($editingId)
                    : null;
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
                $editingExercise = $editingId > 0
                    ? $exerciseController->getByIdForDashboard($editingId)
                    : null;
                if (!$editingExercise) {
                    $_SESSION['flash_message'] = 'Exercise not found.';
                    header('Location: index.php?controller=dashboard&action=index');
                    exit;
                }
                $coachingId      = (int)$editingExercise['coaching_id'];
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
        // Afficher la page d'accueil
        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        include __DIR__ . '/views/front/interface.php';
        exit;
}
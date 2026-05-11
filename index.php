<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/user/Controller/UserController.php';
require_once __DIR__ . '/coaching/Controller/ExerciseController.php';
require_once __DIR__ . '/coaching/Controller/CoachingController.php';
require_once __DIR__ . '/user/Controller/UserDashboardController.php';
require_once __DIR__ . '/chat/Controller/ChatController.php';
require_once __DIR__ . '/admin/Controller/AdminController.php';

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

    // reset-password needs the token available to the view
    $token = (string)($_GET['token'] ?? '');

    match ($view) {
        'register'       => include __DIR__ . '/auth/View/register.php',
        'login'          => include __DIR__ . '/auth/View/login.php',
        'interface'      => include __DIR__ . '/views/home.php',
        'forgot-password'=> (new UserController())->handle('forgotPassword'),
        'reset-password' => (new UserController())->handle('resetPassword'),
        default          => header('Location: index.php'),
    };
    exit;
}

// ✅ Si aucune page, afficher l'interface (page d'accueil)
if (!$page) {
    $flashMessage = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    include __DIR__ . '/views/home.php';
    exit;
}

switch ($page) {
    case 'user':
        (new UserController())->handle($action);
        break;

    case 'face-login':
        (new UserController())->faceLogin();
        break;

    case 'chat':
    case 'chatbot':
        requireLogin();
        (new ChatController())->handle($action);
        break;

    case 'admin':
        requireAdmin();
        (new AdminController())->handle($action);
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

        // Extra stats for backoffice overview
        $db = getDB();
        $totalUsers  = (int)$db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
        $totalChats  = (int)$db->query("SELECT COUNT(*) FROM ai_chats")->fetchColumn();
        $totalFaces  = (int)$db->query("SELECT COUNT(*) FROM visages_utilisateurs")->fetchColumn();
        $view               = $_GET['view'] ?? 'coaching_list';

        $coachingId = filter_input(INPUT_GET, 'coaching_id', FILTER_VALIDATE_INT);
        $coachingId = $coachingId !== false && $coachingId !== null ? (int)$coachingId : 0;

        $editingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $editingId = $editingId !== false && $editingId !== null ? (int)$editingId : 0;

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        switch ($view) {
            case 'coaching_create':
                include __DIR__ . '/admin/View/coaching_create.php';
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
                include __DIR__ . '/admin/View/coaching_edit.php';
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
                include __DIR__ . '/admin/View/exercises.php';
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
                $filterCoachingId = $coachingId;
                $coachingPrograms = array_map(
                    static fn($p): array => ['id' => $p->getId(), 'title' => $p->getTitle()],
                    $coachingPrograms
                );
                include __DIR__ . '/admin/View/exercises_create.php';
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
                include __DIR__ . '/admin/View/exercises_edit.php';
                break;

            case 'coaching_list':
            default:
                include __DIR__ . '/admin/View/dashboard.php';
                break;
        }
        break;

    default:
        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        include __DIR__ . '/views/home.php';
        exit;
}

// ✅ تم دمج السكريبت هنا بشكل صحيح داخل شرط الـ Admin
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const forceMatchStyle = function() {
        // 1. البحث عن زر "Face ID" لاستخدامه كنموذج للتصميم
        const allLinks = Array.from(document.querySelectorAll('a'));
        const faceIdLink = allLinks.find(el => el.innerText.includes('Face ID'));
        const sidebar = document.querySelector('.sidebar-menu') || (faceIdLink ? faceIdLink.closest('nav') || faceIdLink.parentElement.parentElement : null);

        if (faceIdLink && sidebar && !document.querySelector('#tab-recette-final')) {
            // 2. إنشاء عنصر القائمة الجديد
            const navItem = document.createElement('div');
            navItem.id = 'tab-recette-final';
            
            // نسخ الكلاسات لضمان نفس التنسيق
            const originalClasses = faceIdLink.className;
            const originalParentClasses = faceIdLink.parentElement.className;
            navItem.className = originalParentClasses;

            // ✅ الرابط الصحيح والمؤكد لفتح الـ Back Office بناءً على كود الـ header الخاص بك
            const targetUrl = "/integration/recette/public/index.php?page=back_dashboard";

            navItem.innerHTML = `
                <a href="${targetUrl}" class="${originalClasses}" style="display: flex; align-items: center; gap: 12px;">
                   <span style="font-size: 20px;">🍲</span>
                   <span>Recettes</span>
                </a>
            `;

            // 3. وضعه قبل زر "Retour au site"
            const returnBtn = allLinks.find(el => el.innerText.includes('Retour au site'));
            if (returnBtn) {
                returnBtn.parentElement.parentElement.insertBefore(navItem, returnBtn.parentElement);
            } else {
                sidebar.appendChild(navItem);
            }
        }
    };

    forceMatchStyle();
    setInterval(forceMatchStyle, 1000);
});
</script>
<?php endif; ?>
<?php endif; ?>
<?php endif; // نهاية شرط الأدمن ?>
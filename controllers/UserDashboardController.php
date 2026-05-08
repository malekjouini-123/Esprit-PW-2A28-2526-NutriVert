<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (class_exists('UserDashboardController')) return;

class UserDashboardController
{
    private PDO $pdo;

    public function __construct() 
    { 
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
            header('Location: index.php?view=login');
            exit;
        }
        $this->pdo = getDB(); 
    }

    public function __destruct() {}

    public function handle(string $action): void
    {
        match ($action) {
            'index'        => $this->dashboard(),
            'coaching_list'=> $this->coachingList(),
            'start'        => $this->startCoaching(),
            'next'         => $this->nextExercise(),
            'complete'     => $this->completeCoaching(),
            default        => $this->dashboard(),
        };
    }

    // =========================================================================
    //  DASHBOARD UTILISATEUR (accueil)
    // =========================================================================

    private function dashboard(): void
    {
        $user = $_SESSION['user'];
        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        include __DIR__ . '/../views/user/dashboard.php';
    }

    // =========================================================================
    //  LISTE DES COACHINGS
    // =========================================================================

    private function coachingList(): void
    {
        $user = $_SESSION['user'];
        
        // Récupérer tous les coachings avec le nombre d'exercices
        $stmt = $this->pdo->query(
            'SELECT cp.*, COUNT(e.id) as exercise_count 
             FROM coaching_programs cp 
             LEFT JOIN exercises e ON e.coaching_id = cp.id 
             GROUP BY cp.id 
             ORDER BY cp.created_at DESC'
        );
        $coachings = $stmt->fetchAll();

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        include __DIR__ . '/../views/user/coaching_list.php';
    }

    // =========================================================================
    //  DÉMARRER UN COACHING
    // =========================================================================

    private function startCoaching(): void
    {
        $coachingId = (int)($_GET['id'] ?? 0);
        
        if ($coachingId <= 0) {
            $_SESSION['flash_message'] = 'Coaching invalide.';
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        // Récupérer le coaching
        $stmt = $this->pdo->prepare(
            'SELECT * FROM coaching_programs WHERE id = :id'
        );
        $stmt->execute(['id' => $coachingId]);
        $coaching = $stmt->fetch();

        if (!$coaching) {
            $_SESSION['flash_message'] = 'Coaching non trouvé.';
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        // Récupérer tous les exercices du coaching (ordonnés)
        $stmt = $this->pdo->prepare(
            'SELECT * FROM exercises WHERE coaching_id = :coaching_id ORDER BY ordre ASC, id ASC'
        );
        $stmt->execute(['coaching_id' => $coachingId]);
        $exercises = $stmt->fetchAll();

        if (empty($exercises)) {
            $_SESSION['flash_message'] = 'Ce coaching n\'a pas d\'exercices.';
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        // Initialiser la session de coaching
        $_SESSION['coaching_session'] = [
            'coaching_id' => $coachingId,
            'current_exercise_index' => 0,
            'started_at' => time(),
            'total_exercises' => count($exercises),
        ];

        // Inclure la vue de la session
        $user = $_SESSION['user'];
        $currentExerciseIndex = 0;
        $currentExercise = $exercises[$currentExerciseIndex];
        $totalExercises = count($exercises);
        
        include __DIR__ . '/../views/user/coaching_session.php';
    }

    // =========================================================================
    //  EXERCICE SUIVANT
    // =========================================================================

    private function nextExercise(): void
    {
        if (!isset($_SESSION['coaching_session'])) {
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        $coachingId = $_SESSION['coaching_session']['coaching_id'];
        $currentIndex = $_SESSION['coaching_session']['current_exercise_index'];

        // Récupérer tous les exercices
        $stmt = $this->pdo->prepare(
            'SELECT * FROM exercises WHERE coaching_id = :coaching_id ORDER BY ordre ASC, id ASC'
        );
        $stmt->execute(['coaching_id' => $coachingId]);
        $exercises = $stmt->fetchAll();

        $nextIndex = $currentIndex + 1;

        // Vérifier s'il y a d'autres exercices
        if ($nextIndex >= count($exercises)) {
            // C'était le dernier exercice
            $this->completeCoaching();
            return;
        }

        // Passer à l'exercice suivant
        $_SESSION['coaching_session']['current_exercise_index'] = $nextIndex;

        $user = $_SESSION['user'];
        $currentExerciseIndex = $nextIndex;
        $currentExercise = $exercises[$nextIndex];
        $totalExercises = count($exercises);

        include __DIR__ . '/../views/user/coaching_session.php';
    }

    // =========================================================================
    //  COMPLÉTER LE COACHING
    // =========================================================================

    private function completeCoaching(): void
    {
        $user = $_SESSION['user'];
        
        if (!isset($_SESSION['coaching_session'])) {
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        $coachingId = $_SESSION['coaching_session']['coaching_id'];
        $startedAt = $_SESSION['coaching_session']['started_at'];
        $completedAt = time();
        $duration = $completedAt - $startedAt;

        // Nettoyer la session
        unset($_SESSION['coaching_session']);

        $_SESSION['flash_message'] = 'Coaching complété avec succès! 🎉';

        include __DIR__ . '/../views/user/coaching_complete.php';
    }

    // =========================================================================
    //  UTILITAIRES
    // =========================================================================

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}

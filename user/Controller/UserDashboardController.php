<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Model/ReminderService.php';
require_once __DIR__ . '/../Model/ReminderEmailService.php';

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

    public function handle(string $action): void
    {
        match ($action) {
            'index'        => $this->dashboard(),
            'coaching_list'=> $this->coachingList(),
            'chatbot'      => $this->chatbot(),
            'start'        => $this->startCoaching(),
            'next'         => $this->nextExercise(),
            'complete'     => $this->completeCoaching(),
            'feedback'     => $this->saveFeedback(),
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

        include __DIR__ . '/../View/dashboard.php';
    }

    // =========================================================================
    //  LISTE DES COACHINGS
    // =========================================================================

    private function coachingList(): void
    {
        $user = $_SESSION['user'];

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

        include __DIR__ . '/../../coaching/View/list.php';
    }

    // =========================================================================
    //  CHATBOT COACHING
    // =========================================================================

    private function chatbot(): void
    {
        require_once __DIR__ . '/../../chat/Controller/ChatController.php';
        (new ChatController())->handle('index');
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

        $stmt = $this->pdo->prepare('SELECT * FROM coaching_programs WHERE id = :id');
        $stmt->execute(['id' => $coachingId]);
        $coaching = $stmt->fetch();

        if (!$coaching) {
            $_SESSION['flash_message'] = 'Coaching non trouvé.';
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        $stmt = $this->pdo->prepare(
            'SELECT * FROM exercises WHERE coaching_id = :coaching_id ORDER BY ordre ASC, id ASC'
        );
        $stmt->execute(['coaching_id' => $coachingId]);
        $exercises = $stmt->fetchAll();

        if (empty($exercises)) {
            $_SESSION['flash_message'] = 'Ce coaching n\'a pas d\'exercices.';
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        $_SESSION['coaching_session'] = [
            'coaching_id'            => $coachingId,
            'current_exercise_index' => 0,
            'started_at'             => time(),
            'total_exercises'        => count($exercises),
            'completed_exercises'    => 0,
        ];

        $userId = (int)$_SESSION['user']['id'];
        try {
            $this->getReminderService()->updateCoachingProgress($userId, $coachingId, 0, count($exercises));
        } catch (\Exception $e) {
            error_log('updateCoachingProgress error: ' . $e->getMessage());
        }

        $user                 = $_SESSION['user'];
        $currentExerciseIndex = 0;
        $currentExercise      = $exercises[$currentExerciseIndex];
        $totalExercises       = count($exercises);

        include __DIR__ . '/../../coaching/View/session.php';
    }

    // =========================================================================
    //  EXERCICE SUIVANT
    // =========================================================================

    private function nextExercise(): void
    {
        if (!isset($_SESSION['coaching_session'])) {
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        $coachingId   = $_SESSION['coaching_session']['coaching_id'];
        $currentIndex = $_SESSION['coaching_session']['current_exercise_index'];

        $stmt = $this->pdo->prepare(
            'SELECT * FROM exercises WHERE coaching_id = :coaching_id ORDER BY ordre ASC, id ASC'
        );
        $stmt->execute(['coaching_id' => $coachingId]);
        $exercises = $stmt->fetchAll();

        $nextIndex = $currentIndex + 1;

        $userId = (int)$_SESSION['user']['id'];
        try {
            $this->getReminderService()->updateCoachingProgress($userId, $coachingId, $nextIndex, count($exercises));
        } catch (\Exception $e) {
            error_log('updateCoachingProgress error: ' . $e->getMessage());
        }

        if ($nextIndex >= count($exercises)) {
            $_SESSION['coaching_session']['completed_exercises'] = $nextIndex;
            $this->completeCoaching();
            return;
        }

        $_SESSION['coaching_session']['current_exercise_index'] = $nextIndex;
        $_SESSION['coaching_session']['completed_exercises']    = $nextIndex;

        $user                 = $_SESSION['user'];
        $currentExerciseIndex = $nextIndex;
        $currentExercise      = $exercises[$nextIndex];
        $totalExercises       = count($exercises);

        include __DIR__ . '/../../coaching/View/session.php';
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

        $coachingId     = (int)$_SESSION['coaching_session']['coaching_id'];
        $startedAt      = (int)$_SESSION['coaching_session']['started_at'];
        $totalExercises = (int)$_SESSION['coaching_session']['total_exercises'];
        $userId         = (int)$_SESSION['user']['id'];

        // Detect how the session ended
        $isQuit          = isset($_GET['quit']) && $_GET['quit'] === '1';
        $timerCompleted  = isset($_GET['completed']) && $_GET['completed'] === '1';

        // If the user clicked "Arrêter" mid-exercise (quit=1, completed=0),
        // the current exercise was NOT finished, so don't count it.
        // In every other case (timer expired, or "Exercice terminé" button), count it.
        if ($isQuit && !$timerCompleted) {
            $completedExercises = (int)$_SESSION['coaching_session']['current_exercise_index'];
        } else {
            $completedExercises = (int)$_SESSION['coaching_session']['current_exercise_index'] + 1;
        }

        // Never let the number exceed what was already tracked in the session
        $completedExercises = max(
            $completedExercises,
            (int)($_SESSION['coaching_session']['completed_exercises'] ?? 0)
        );

        // Clamp to total — can't have completed more than exist
        $completedExercises = min($completedExercises, $totalExercises);

        // Full completion only when every exercise is done AND the user didn't manually quit
        $isFullCompletion = ($completedExercises >= $totalExercises) && !$isQuit;

        try {
            $this->getReminderService()->updateCoachingProgress(
                $userId, $coachingId, $completedExercises, $totalExercises
            );
        } catch (\Exception $e) {
            error_log('updateCoachingProgress error: ' . $e->getMessage());
        }

        // Send partial-progress email whenever the session ended before full completion
        $hasQuitEarly = !$isFullCompletion;

        error_log(sprintf(
            'DEBUG completeCoaching: isQuit=%s, timerCompleted=%s, completedExercises=%d/%d, isFullCompletion=%s, hasQuitEarly=%s',
            $isQuit ? 'true' : 'false',
            $timerCompleted ? 'true' : 'false',
            $completedExercises,
            $totalExercises,
            $isFullCompletion ? 'true' : 'false',
            $hasQuitEarly ? 'true' : 'false'
        ));

        if ($hasQuitEarly) {
            try {
                error_log("DEBUG: Attempting to send exercise failure reminder for user $userId on coaching $coachingId");

                $allExercises = $this->getExercisesForCoaching($coachingId);

                $completedList = array_map(
                    static fn(array $ex): string => (string)$ex['name'],
                    array_slice($allExercises, 0, $completedExercises)
                );
                $pendingList = array_map(
                    static fn(array $ex): string => (string)$ex['name'],
                    array_slice($allExercises, $completedExercises)
                );
                $progressPercentage = $totalExercises > 0
                    ? (int)round(($completedExercises / $totalExercises) * 100)
                    : 0;

                error_log(sprintf(
                    'DEBUG: Sending email — completed: %d, pending: %d, progress: %d%%',
                    count($completedList),
                    count($pendingList),
                    $progressPercentage
                ));

                $result = $this->getReminderService()->sendExerciseFailureReminder(
                    $userId, $coachingId, 'quit',
                    $completedList, $pendingList, $progressPercentage
                );

                error_log('DEBUG: Email send result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            } catch (\Exception $e) {
                error_log('Partial progress email error: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
            }
        }

        $completionTitle = $isFullCompletion
            ? 'Bravo, tu as terminé la session!'
            : 'Session arrêtée';
        $completionMessage = $isFullCompletion
            ? 'Vous avez complété avec succès tous les exercices de ce programme. Excellent travail!'
            : 'Vous avez arrêté la session avant la fin. Votre progression partielle a bien été enregistrée.';
        $completedCount        = $completedExercises;
        $totalCount            = $totalExercises;
        $showFeedbackForm      = $isFullCompletion;
        $coachingIdForFeedback = $coachingId;

        unset($_SESSION['coaching_session']);

        include __DIR__ . '/../../coaching/View/complete.php';
    }

    // =========================================================================
    //  SAUVEGARDER LE FEEDBACK
    // =========================================================================

    private function saveFeedback(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        $userId     = (int)$_SESSION['user']['id'];
        $coachingId = (int)($_POST['coaching_id'] ?? 0);
        $emoji      = trim((string)($_POST['emoji'] ?? ''));
        $comment    = trim((string)($_POST['comment'] ?? ''));

        $allowedEmojis = ['👍', '🔥', '😐', '❤️', '😴'];

        if ($userId <= 0 || $coachingId <= 0) {
            $_SESSION['flash_message'] = 'Données de feedback invalides.';
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        if (!in_array($emoji, $allowedEmojis, true)) {
            $emoji = '👍';
        }

        if ($comment === '') {
            $_SESSION['flash_message'] = 'Merci d\'ajouter un commentaire.';
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        if (mb_strlen($comment) > 1000) {
            $comment = mb_substr($comment, 0, 1000);
        }

        if (!$this->hasCompletedCoaching($userId, $coachingId)) {
            $_SESSION['flash_message'] = 'Vous devez terminer le coaching avant de laisser un avis.';
            $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
        }

        try {
            $this->ensureFeedbackTable();

            $stmt = $this->pdo->prepare(
                'INSERT INTO coaching_feedback (user_id, coaching_id, emoji, comment, created_at, updated_at)
                 VALUES (:user_id, :coaching_id, :emoji, :comment, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE emoji = VALUES(emoji), comment = VALUES(comment), updated_at = NOW()'
            );

            $saved = $stmt->execute([
                'user_id'     => $userId,
                'coaching_id' => $coachingId,
                'emoji'       => $emoji,
                'comment'     => $comment,
            ]);
        } catch (PDOException $e) {
            error_log('Feedback save error: ' . $e->getMessage());
            $saved = false;
        }

        $_SESSION['flash_message'] = $saved
            ? 'Merci, votre avis a bien été enregistré.'
            : 'Impossible d\'enregistrer votre avis pour le moment.';

        $this->redirect('index.php?controller=user_dashboard&action=coaching_list');
    }

    // =========================================================================
    //  UTILITAIRES
    // =========================================================================

    private function getReminderService(): ReminderService
    {
        static $reminderService = null;
        if ($reminderService === null) {
            $config = require __DIR__ . '/../../config/reminders.php';
            $reminderService = new ReminderService($this->pdo, $config);
        }
        return $reminderService;
    }

    private function getExercisesForCoaching(int $coachingId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name FROM exercises WHERE coaching_id = :coaching_id ORDER BY ordre ASC, id ASC'
        );
        $stmt->execute(['coaching_id' => $coachingId]);
        return $stmt->fetchAll();
    }

    private function hasCompletedCoaching(int $userId, int $coachingId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT progress_percentage FROM coaching_progress
                 WHERE user_id = :user_id AND coaching_id = :coaching_id LIMIT 1'
            );
            $stmt->execute(['user_id' => $userId, 'coaching_id' => $coachingId]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('hasCompletedCoaching error: ' . $e->getMessage());
            return false;
        }
        return $row && (int)($row['progress_percentage'] ?? 0) >= 100;
    }

    private function ensureFeedbackTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS coaching_feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                coaching_id INT NOT NULL,
                emoji VARCHAR(16) NOT NULL,
                comment TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_user_coaching_feedback (user_id, coaching_id),
                INDEX idx_feedback_coaching (coaching_id),
                CONSTRAINT fk_feedback_user FOREIGN KEY (user_id)
                    REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
                CONSTRAINT fk_feedback_coaching FOREIGN KEY (coaching_id)
                    REFERENCES coaching_programs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log('Feedback table creation error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

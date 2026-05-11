<?php
declare(strict_types=1);

require_once __DIR__ . '/ReminderEmailService.php';

/**
 * Service de gestion des rappels d'email
 * GÃ¨re la logique d'envoi des rappels en fonction de l'inactivitÃ©
 */

class ReminderService
{
    private PDO $pdo;
    private ReminderEmailService $emailService;
    private array $config;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
        $this->emailService = new ReminderEmailService($config['email']);
    }

    /**
     * Traite tous les rappels d'inactivitÃ©
     * Ã€ appeler pÃ©riodiquement via cron
     * 
     * @return array Statistiques des rappels envoyÃ©s
     */
    public function processAllReminders(): array
    {
        $this->logDebug("=== DÃ©but du traitement des rappels ===");
        
        if (!$this->config['reminders']['enabled']) {
            $this->logDebug("âŒ SystÃ¨me de rappels DÃ‰SACTIVÃ‰");
            return ['error' => 'Reminder system is disabled'];
        }

        $stats = [
            'processed' => 0,
            'reminders_sent' => 0,
            'errors' => 0,
            'details' => [],
            'skipped' => 0,
        ];

        // RÃ©cupÃ©rer tous les utilisateurs avec des coachings actifs
        $inactiveUsers = $this->getInactiveUsersWithIncompleteCoaching();
        $this->logDebug("âœ… TrouvÃ© " . count($inactiveUsers) . " utilisateurs inactifs");

        foreach ($inactiveUsers as $userCoaching) {
            try {
                $stats['processed']++;
                
                if ($this->shouldSendReminder($userCoaching)) {
                    $this->logDebug("ðŸ“§ Envoi rappel Ã  user #{$userCoaching['user_id']} (coaching #{$userCoaching['coaching_id']}, progression: {$userCoaching['progress_percentage']}%)");
                    $result = $this->sendReminder($userCoaching);
                    if ($result) {
                        $stats['reminders_sent']++;
                        $stats['details'][] = [
                            'status' => 'sent',
                            'user_id' => $userCoaching['user_id'],
                            'email' => $userCoaching['email'],
                            'coaching_id' => $userCoaching['coaching_id'],
                            'reminder_type' => $userCoaching['reminder_type'],
                        ];
                    } else {
                        $this->logDebug("âŒ Ã‰chec envoi email user #{$userCoaching['user_id']}");
                        $stats['errors']++;
                    }
                } else {
                    $stats['skipped']++;
                    $this->logDebug("â­ï¸ Rappel ignorÃ© pour user #{$userCoaching['user_id']} (rappel rÃ©cent ou limite atteinte)");
                }
            } catch (Exception $e) {
                $stats['errors']++;
                $this->logDebug("âš ï¸ Erreur pour user #{$userCoaching['user_id']}: " . $e->getMessage());
                error_log("ReminderService Error: " . $e->getMessage());
                $stats['details'][] = [
                    'status' => 'error',
                    'user_id' => $userCoaching['user_id'],
                    'coaching_id' => $userCoaching['coaching_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->logProcessing($stats);
        return $stats;
    }

    /**
     * RÃ©cupÃ¨re les utilisateurs inactifs avec des coachings incomplets
     */
    private function getInactiveUsersWithIncompleteCoaching(): array
    {
        $earlyHours = (int)$this->config['inactivity']['early_reminder_hours'];
        $standardHours = (int)$this->config['inactivity']['standard_reminder_hours'];
        $urgentHours = (int)$this->config['inactivity']['urgent_reminder_hours'];

        $this->logDebug("Recherche coachings incomplets (inactivitÃ© > {$earlyHours}h)");

        $sql = "
            SELECT
                cp.id AS coaching_id,
                c.title AS coaching_name,
                u.id_utilisateur AS user_id,
                u.email,
                u.nom,
                cp.progress_percentage,
                cp.last_activity,
                cp.exercises_completed,
                cp.total_exercises,
                CASE
                    WHEN TIMESTAMPDIFF(HOUR, cp.last_activity, NOW()) > :urgent_hours_case THEN 'urgent'
                    WHEN TIMESTAMPDIFF(HOUR, cp.last_activity, NOW()) > :standard_hours_case THEN 'standard'
                    WHEN TIMESTAMPDIFF(HOUR, cp.last_activity, NOW()) > :early_hours_case THEN 'early'
                    ELSE NULL
                END AS reminder_type
            FROM coaching_progress cp
            JOIN utilisateurs u ON u.id_utilisateur = cp.user_id
            JOIN coaching_programs c ON c.id = cp.coaching_id
            WHERE cp.completed_at IS NULL
            AND cp.progress_percentage < 100
            AND TIMESTAMPDIFF(HOUR, cp.last_activity, NOW()) > :early_hours_where
            ORDER BY cp.last_activity ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':early_hours_case' => $earlyHours,
                ':standard_hours_case' => $standardHours,
                ':urgent_hours_case' => $urgentHours,
                ':early_hours_where' => $earlyHours,
            ]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logDebug("âŒ Erreur requÃªte coachings incomplets: " . $e->getMessage());
            return [];
        }
    }

    /**
     * VÃ©rifie si un rappel devrait Ãªtre envoyÃ© pour ce user/coaching
     */
    private function shouldSendReminder(array $userCoaching): bool
    {
        $minInterval = (int)$this->config['reminders']['min_interval_between_reminders'];
        $maxReminders = (int)$this->config['reminders']['max_reminders_per_coaching'];

        // VÃ©rifier si un rappel a dÃ©jÃ  Ã©tÃ© envoyÃ© rÃ©cemment
        $sql = "
            SELECT COUNT(*) as reminder_count, MAX(sent_at) as last_sent
            FROM coaching_reminders
            WHERE user_id = :user_id 
            AND coaching_id = :coaching_id
            AND sent_at > DATE_SUB(NOW(), INTERVAL {$minInterval} SECOND)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userCoaching['user_id'],
            ':coaching_id' => $userCoaching['coaching_id'],
        ]);

        $result = $stmt->fetch();
        
        // Ne pas envoyer si dÃ©jÃ  un rappel rÃ©cent
        if ($result['reminder_count'] > 0) {
            $this->logDebug("  â¸ï¸ Rappel rÃ©cent trouvÃ© ({$result['reminder_count']} rappel(s) en {$minInterval}s)");
            return false;
        }

        // VÃ©rifier si on n'a pas dÃ©passÃ© le nombre max de rappels
        $sql = "
            SELECT COUNT(*) as total_reminders
            FROM coaching_reminders
            WHERE user_id = :user_id 
            AND coaching_id = :coaching_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userCoaching['user_id'],
            ':coaching_id' => $userCoaching['coaching_id'],
        ]);

        $result = $stmt->fetch();
        $totalReminders = $result['total_reminders'] ?? 0;
        
        if ($totalReminders >= $maxReminders) {
            $this->logDebug("  âš ï¸ Limite de rappels atteinte ({$totalReminders}/{$maxReminders})");
            return false;
        }

        $this->logDebug("  âœ… Rappel devrait Ãªtre envoyÃ© ({$totalReminders}/{$maxReminders})");
        return true;
    }

    /**
     * Envoie un rappel pour un utilisateur
     */
    private function sendReminder(array $userCoaching): bool
    {
        $reminderType = $userCoaching['reminder_type'] ?? 'standard';
        $email = $userCoaching['email'];
        $nom = $userCoaching['nom'];
        $coaching = $userCoaching['coaching_name'];
        $progress = (int)$userCoaching['progress_percentage'];
        
        // Valider l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->logDebug("  âŒ Email invalide: $email");
            return false;
        }

        // Envoyer l'email
        $this->logDebug("  ðŸ“§ Envoi email Ã  $email (type: $reminderType, progression: $progress%)");
        $emailSent = $this->emailService->sendReminderEmail(
            $email,
            $nom,
            $coaching,
            $progress,
            $reminderType
        );

        if (!$emailSent) {
            $this->logDebug("  âŒ Ã‰chec envoi email via EmailService");
            return false;
        }

        $this->logDebug("  âœ… Email envoyÃ© avec succÃ¨s");

        // Enregistrer le log interne
        try {
            $this->emailService->logEmailSent(
                $email,
                $nom,
                $coaching,
                $progress,
                $reminderType
            );
            $this->logDebug("  ðŸ“ Log email enregistrÃ©");
        } catch (Exception $e) {
            $this->logDebug("  âš ï¸ Erreur enregistrement log: " . $e->getMessage());
        }

        // Enregistrer en base de donnÃ©es
        $recorded = $this->recordReminderSent(
            $userCoaching['user_id'],
            $userCoaching['coaching_id'],
            $progress,
            $reminderType
        );

        if (!$recorded) {
            $this->logDebug("  âš ï¸ Email envoyÃ©, mais Ã©chec enregistrement du rappel en BDD. VÃ©rifiez la table coaching_reminders.");
            return true;
        }

        $this->logDebug("  âœ… Rappel enregistrÃ© en BDD");
        return true;
    }

    /**
     * Envoie un email de suivi d'exercice en cas d'Ã©chec ou de timeout
     */
    public function sendExerciseFailureReminder(
        int $userId,
        int $coachingId,
        string $eventType,
        array $completedExercises,
        array $pendingExercises,
        int $progressPercentage
    ): bool
    {
        // Debug logging to ensure correct user
        $this->logDebug("ðŸ“§ sendExerciseFailureReminder: userId=$userId, coachingId=$coachingId, type=$eventType");
        
        $user = $this->getUserById($userId);
        $coaching = $this->getCoachingById($coachingId);

        if (!$user) {
            $this->logDebug("  âŒ User not found with ID: $userId");
            return false;
        }

        if (!$coaching) {
            $this->logDebug("  âŒ Coaching not found with ID: $coachingId");
            return false;
        }

        $this->logDebug("  âœ… Sending email to: " . $user['email'] . " (User: " . $user['nom'] . ")");

        $emailSent = $this->emailService->sendExerciseStatusEmail(
            $user['email'],
            $user['nom'],
            $coaching['title'],
            $progressPercentage,
            $completedExercises,
            $pendingExercises,
            $eventType
        );

        if (!$emailSent) {
            $this->logDebug("  âŒ Failed to send email via EmailService");
            return false;
        }

        $this->logDebug("  âœ… Email sent successfully");

        try {
            $this->emailService->logEmailSent(
                $user['email'],
                $user['nom'],
                $coaching['title'],
                $progressPercentage,
                'urgent'
            );
            $this->logDebug("  ðŸ“ Email log recorded");
        } catch (Exception $e) {
            $this->logDebug("  âš ï¸ Error recording email log: " . $e->getMessage());
        }

        // Record reminder sent
        $this->recordReminderSent(
            $userId,
            $coachingId,
            $progressPercentage,
            'urgent'
        );

        return true;
    }

    /**
     * RÃ©cupÃ¨re un utilisateur par identifiant
     */
    private function getUserById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id_utilisateur AS id, nom, email FROM utilisateurs WHERE id_utilisateur = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * RÃ©cupÃ¨re un coaching par identifiant
     */
    private function getCoachingById(int $coachingId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, title FROM coaching_programs WHERE id = :id');
        $stmt->execute(['id' => $coachingId]);
        $coaching = $stmt->fetch();
        return $coaching ?: null;
    }

    /**
     * Enregistre un rappel comme envoyÃ© en base de donnÃ©es
     */
    private function recordReminderSent(
        int $userId,
        int $coachingId,
        int $progressPercentage,
        string $reminderType
    ): bool
    {
        $sql = "
            INSERT INTO coaching_reminders (user_id, coaching_id, progress_percentage, reminder_type)
            VALUES (:user_id, :coaching_id, :progress_percentage, :reminder_type)
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':user_id' => $userId,
                ':coaching_id' => $coachingId,
                ':progress_percentage' => $progressPercentage,
                ':reminder_type' => $reminderType,
            ]);
        } catch (PDOException $e) {
            $this->logDebug("  âš ï¸ Erreur SQL recordReminderSent: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met Ã  jour la progression d'un utilisateur pour un coaching
     */
    public function updateCoachingProgress(
        int $userId,
        int $coachingId,
        int $exercisesCompleted,
        int $totalExercises
    ): bool
    {
        $progressPercentage = $totalExercises > 0 
            ? (int)(($exercisesCompleted / $totalExercises) * 100)
            : 0;

        $isCompleted = $progressPercentage >= 100;
        $completedAt = $isCompleted ? date('Y-m-d H:i:s') : null;

        $sql = "
            INSERT INTO coaching_progress 
            (user_id, coaching_id, exercises_completed, total_exercises, progress_percentage, last_activity, completed_at)
            VALUES 
            (:user_id, :coaching_id, :insert_exercises_completed, :insert_total_exercises, :insert_progress_percentage, NOW(), :insert_completed_at)
            ON DUPLICATE KEY UPDATE 
                exercises_completed = :update_exercises_completed,
                progress_percentage = :update_progress_percentage,
                last_activity = NOW(),
                completed_at = COALESCE(:update_completed_at, completed_at)
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':coaching_id' => $coachingId,
            ':insert_exercises_completed' => $exercisesCompleted,
            ':insert_total_exercises' => $totalExercises,
            ':insert_progress_percentage' => $progressPercentage,
            ':insert_completed_at' => $completedAt,
            ':update_exercises_completed' => $exercisesCompleted,
            ':update_progress_percentage' => $progressPercentage,
            ':update_completed_at' => $completedAt,
        ]);
    }

    /**
     * RÃ©cupÃ¨re le statut actuel d'un utilisateur pour un coaching
     */
    public function getCoachingProgress(int $userId, int $coachingId): ?array
    {
        $sql = "
            SELECT * FROM coaching_progress
            WHERE user_id = :user_id AND coaching_id = :coaching_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':coaching_id' => $coachingId,
        ]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Retourne l'historique des rappels pour un utilisateur/coaching
     */
    public function getReminderHistory(int $userId, int $coachingId): array
    {
        $sql = "
            SELECT * FROM coaching_reminders
            WHERE user_id = :user_id AND coaching_id = :coaching_id
            ORDER BY sent_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':coaching_id' => $coachingId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Enregistre les statistiques de traitement
     */
    private function logProcessing(array $stats): void
    {
        $logDir = __DIR__ . '/../../logs/';
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'reminders_processing.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $logEntry = sprintf(
            "[%s] âœ… RÃ‰SUMÃ‰: Processed=%d, Sent=%d, Errors=%d, Skipped=%d\n",
            $timestamp,
            $stats['processed'],
            $stats['reminders_sent'],
            $stats['errors'],
            $stats['skipped'] ?? 0
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Enregistre un message de debug/info
     */
    private function logDebug(string $message): void
    {
        $logDir = __DIR__ . '/../../logs/';
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'reminders_debug_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message\n";
        
        file_put_contents($logFile, $entry, FILE_APPEND);
    }
}


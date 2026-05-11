<?php
declare(strict_types=1);

/**
 * Service d'envoi d'emails pour le systÃ¨me de rappels
 * GÃ¨re l'envoi d'emails motivants personnalisÃ©s
 */

class ReminderEmailService
{
    private string $fromEmail;
    private string $fromName;
    private string $transport;
    private array $config;

    public function __construct(array $emailConfig)
    {
        $this->config = $emailConfig;
        $this->fromEmail = $emailConfig['from'] ?? 'noreply@nutrivert.local';
        $this->fromName = $emailConfig['from_name'] ?? 'Nutrivert';
        $this->transport = $emailConfig['transport'] ?? 'php_mail';
    }

    /**
     * Envoie un email de rappel personnalisÃ©
     * 
     * @param string $toEmail Email du destinataire
     * @param string $userName Nom de l'utilisateur
     * @param string $coachingName Nom du coaching
     * @param int $progressPercentage Pourcentage de progression
     * @param string $reminderType Type de rappel (early, standard, urgent)
     * @return bool True si l'email a Ã©tÃ© envoyÃ© avec succÃ¨s
     */
    public function sendReminderEmail(
        string $toEmail,
        string $userName,
        string $coachingName,
        int $progressPercentage,
        string $reminderType = 'standard'
    ): bool
    {
        // Valider l'email
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("EmailService: Email invalide: $toEmail");
            return false;
        }

        // GÃ©nÃ©rer le contenu de l'email
        $emailContent = $this->generateEmailContent(
            $toEmail,
            $userName,
            $coachingName,
            $progressPercentage,
            $reminderType
        );

        // Envoyer l'email
        return $this->send(
            $toEmail,
            $emailContent['subject'],
            $emailContent['body'],
            $emailContent['headers']
        );
    }

    /**
     * Envoie un email de suivi de progression / Ã©chec d'exercice
     */
    public function sendExerciseStatusEmail(
        string $toEmail,
        string $userName,
        string $coachingName,
        int $progressPercentage,
        array $completedExercises,
        array $pendingExercises,
        string $eventType = 'timeout'
    ): bool
    {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("EmailService: Email invalide: $toEmail");
            return false;
        }

        $emailContent = $this->generateExerciseStatusContent(
            $toEmail,
            $userName,
            $coachingName,
            $progressPercentage,
            $completedExercises,
            $pendingExercises,
            $eventType
        );

        return $this->send(
            $toEmail,
            $emailContent['subject'],
            $emailContent['body'],
            $emailContent['headers']
        );
    }

    /**
     * GÃ©nÃ¨re le contenu personnalisÃ© de l'email
     */
    private function generateEmailContent(
        string $toEmail,
        string $userName,
        string $coachingName,
        int $progressPercentage,
        string $reminderType
    ): array
    {
        // DÃ©terminer le message motivant basÃ© sur la progression
        $motivationalMessage = $this->getMotivationalMessage($progressPercentage, $reminderType);
        $actionableGoal = $this->getActionableGoal($progressPercentage);
        $subject = $this->getSubject($reminderType, $progressPercentage);

        // Construire l'email HTML
        $body = $this->buildEmailBody(
            $toEmail,
            $userName,
            $coachingName,
            $progressPercentage,
            $motivationalMessage,
            $actionableGoal,
            $reminderType
        );

        $headers = $this->buildEmailHeaders();

        return [
            'subject' => $subject,
            'body' => $body,
            'headers' => $headers,
        ];
    }

    /**
     * GÃ©nÃ¨re le contenu personnalisÃ© de l'email de suivi d'exercice
     */
    private function generateExerciseStatusContent(
        string $toEmail,
        string $userName,
        string $coachingName,
        int $progressPercentage,
        array $completedExercises,
        array $pendingExercises,
        string $eventType
    ): array
    {
        $subject = $this->getExerciseStatusSubject($eventType, $progressPercentage);
        $motivationalMessage = $this->getMotivationalMessage($progressPercentage, $eventType === 'timeout' ? 'urgent' : 'standard');
        $actionableGoal = $this->getActionableGoal($progressPercentage);

        $body = $this->buildExerciseStatusBody(
            $toEmail,
            $userName,
            $coachingName,
            $progressPercentage,
            $motivationalMessage,
            $actionableGoal,
            $eventType,
            $completedExercises,
            $pendingExercises
        );

        $headers = $this->buildEmailHeaders();

        return [
            'subject' => $subject,
            'body' => $body,
            'headers' => $headers,
        ];
    }

    private function getExerciseStatusSubject(string $eventType, int $progressPercentage): string
    {
        return match ($eventType) {
            'quit' => "âš ï¸ Progression partielle enregistrÃ©e ({$progressPercentage}%)",
            'timeout' => "â±ï¸ Temps Ã©coulÃ© sur un exercice ({$progressPercentage}%)",
            default => "ðŸŽ¯ Votre progression de coaching ({$progressPercentage}%)",
        };
    }

    private function buildExerciseStatusBody(
        string $toEmail,
        string $userName,
        string $coachingName,
        int $progressPercentage,
        string $motivationalMessage,
        string $actionableGoal,
        string $eventType,
        array $completedExercises,
        array $pendingExercises
    ): string
    {
        $eventText = $eventType === 'timeout'
            ? 'Le temps imparti pour lâ€™exercice actuel est Ã©coulÃ©. Voici votre progression et les prochaines Ã©tapes pour repartir sur de bonnes bases.'
            : 'Vous avez arrÃªtÃ© votre session avant la fin du coaching. Voici oÃ¹ vous en Ãªtes et comment reprendre de maniÃ¨re efficace.';

        $completedListHtml = $this->buildExerciseListHtml($completedExercises, 'Exercices complÃ©tÃ©s');
        $pendingListHtml = $this->buildExerciseListHtml($pendingExercises, 'Exercices non complÃ©tÃ©s');
        $progressColor = $this->getProgressColor($progressPercentage);

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrivert - Votre progression</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f8f4; color: #333; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 18px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
        .header { text-align: center; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #2f5d4f; }
        .section { margin-bottom: 24px; }
        .section-title { font-size: 16px; font-weight: 700; margin-bottom: 12px; color: #4a6b4a; }
        .box { background: #f7f9f6; border-radius: 14px; padding: 18px; }
        .progress { font-size: 28px; font-weight: 700; color: {$progressColor}; margin-bottom: 10px; }
        .message { line-height: 1.7; font-size: 15px; color: #4c5350; }
        .list { margin: 12px 0 0 0; padding-left: 18px; }
        .list li { margin-bottom: 8px; }
        .goal { background: #eaf8ef; border-left: 4px solid #7dcfb6; padding: 14px; border-radius: 10px; color: #3c6f54; }
        .cta { display: inline-block; margin-top: 18px; padding: 12px 24px; background: #ff7e67; color: white; border-radius: 12px; text-decoration: none; }
        .footer { font-size: 12px; color: #7a7a7a; text-align: center; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bonjour {$userName},</h1>
            <p>{$eventText}</p>
        </div>

        <div class="section box">
            <div class="section-title">Votre progression actuelle</div>
            <div class="progress">{$progressPercentage}%</div>
            <p class="message">{$motivationalMessage}</p>
        </div>

        <div class="section">
            <div class="section-title">RÃ©sumÃ© des exercices</div>
            {$completedListHtml}
            {$pendingListHtml}
        </div>

        <div class="section box goal">
            <div class="section-title">Recommandation</div>
            <p>{$actionableGoal}</p>
        </div>

        <div class="section" style="text-align:center;">
            <a class="cta" href="{$this->generateCoachingLink()}">Reprendre mon coaching</a>
        </div>

        <div class="footer">
            <p>Vous recevez cet email car vous avez un coaching en cours chez Nutrivert.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function buildExerciseListHtml(array $exerciseNames, string $title): string
    {
        if (empty($exerciseNames)) {
            return "<div class=\"section\"><strong>{$title}:</strong> Aucun exercice pour le moment.</div>";
        }

        $items = '';
        foreach ($exerciseNames as $exercise) {
            // Handle both string names and array/object exercise data
            $name = is_array($exercise) || is_object($exercise)
                ? ($exercise['name'] ?? $exercise->name ?? '')
                : (string)$exercise;
            
            if (!empty($name)) {
                $items .= "<li>" . htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</li>";
            }
        }

        return "<div class=\"section\"><div class=\"section-title\">{$title}</div><ul class=\"list\">{$items}</ul></div>";
    }

    /**
     * Retourne un message motivant basÃ© sur la progression
     */
    private function getMotivationalMessage(int $progressPercentage, string $reminderType): string
    {
        if ($progressPercentage < 30) {
            return "Vous avez un coaching en cours! Commencez lentement et progressez Ã  votre rythme. Chaque exercice vous rapproche de vos objectifs. ðŸ’ª";
        }

        if ($progressPercentage < 80) {
            return "Vous progressez bien! La constance est la clÃ© du succÃ¨s. Continuez cet excellent travail! ðŸŽ¯";
        }

        return "Vous Ãªtes Ã  {$progressPercentage}% de completion! Vous Ãªtes si proche de la finish line. Un dernier effort! ðŸ”¥";
    }

    /**
     * Retourne un objectif actionable basÃ© sur la progression
     */
    private function getActionableGoal(int $progressPercentage): string
    {
        if ($progressPercentage < 30) {
            return "Objectif: ComplÃ©tez au moins 1 exercice aujourd'hui";
        }

        if ($progressPercentage < 60) {
            return "Objectif: ComplÃ©tez 2-3 exercices aujourd'hui";
        }

        if ($progressPercentage < 90) {
            return "Objectif: ComplÃ©tez 3-4 exercices aujourd'hui";
        }

        return "Objectif: Terminez le coaching aujourd'hui! ðŸŽ‰";
    }

    /**
     * Retourne le sujet de l'email
     */
    private function getSubject(string $reminderType, int $progressPercentage): string
    {
        $subjects = [
            'early' => "ðŸ’ª Vous n'avez pas oubliÃ© votre coaching?",
            'standard' => "ðŸŽ¯ Continuez votre progression ({$progressPercentage}%)",
            'urgent' => "ðŸ”¥ Presque fini! N'abandonnez pas ({$progressPercentage}%)",
        ];

        return $subjects[$reminderType] ?? $subjects['standard'];
    }

    /**
     * Construit le corps de l'email en HTML
     */
    private function buildEmailBody(
        string $toEmail,
        string $userName,
        string $coachingName,
        int $progressPercentage,
        string $motivationalMessage,
        string $actionableGoal,
        string $reminderType
    ): string
    {
        $progressColor = $this->getProgressColor($progressPercentage);
        
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrivert - Coaching Reminder</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f4f8f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #FF7E67, #7DCFB6); padding: 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
        .content { padding: 40px; color: #333; }
        .greeting { font-size: 18px; margin-bottom: 20px; }
        .greeting strong { color: #FF7E67; }
        .coaching-info { background: #f9f9f9; padding: 20px; border-radius: 8px; border-left: 4px solid #FF7E67; margin: 20px 0; }
        .coaching-info p { margin: 8px 0; font-size: 15px; }
        .progress-section { margin: 30px 0; }
        .progress-bar { background: #e0e0e0; height: 12px; border-radius: 6px; overflow: hidden; margin: 15px 0; }
        .progress-fill { background: $progressColor; height: 100%; width: {$progressPercentage}%; transition: width 0.3s; }
        .progress-text { text-align: center; font-weight: 700; color: $progressColor; font-size: 24px; }
        .message-box { background: #fffbf0; border-left: 4px solid #FF7E67; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .message-box p { margin: 0; font-size: 14px; line-height: 1.6; color: #555; }
        .goal-box { background: #f0f5ff; border-left: 4px solid #7DCFB6; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .goal-box strong { color: #7DCFB6; }
        .cta-button { display: inline-block; background: #FF7E67; color: white; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 20px 0; }
        .footer { background: #f4f8f4; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #e0e0e0; }
        .footer a { color: #FF7E67; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ðŸŽ¯ Nutrivert Coaching</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Votre parcours fitness personnalisÃ©</p>
        </div>

        <div class="content">
            <div class="greeting">
                Bonjour <strong>{$userName}</strong>! ðŸ‘‹
            </div>

            <div class="coaching-info">
                <p><strong>Programme:</strong> {$coachingName}</p>
                <p><strong>Statut:</strong> En cours depuis quelques jours</p>
            </div>

            <div class="progress-section">
                <p><strong>Votre Progression:</strong></p>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text">{$progressPercentage}%</div>
            </div>

            <div class="message-box">
                <p>âœ¨ {$motivationalMessage}</p>
            </div>

            <div class="goal-box">
                <p><strong>ðŸ’¡ {$actionableGoal}</strong></p>
            </div>

            <p style="margin: 30px 0; line-height: 1.8; color: #666; font-size: 14px;">
                Vous Ãªtes en bonne voie! Nous croyons en vous et nous savons que vous pouvez le faire. 
                Chaque session vous rapproche de votre objectif. N'abandonnez pas maintenant!
            </p>

            <a href="{$this->generateCoachingLink()}" class="cta-button">Continuer mon coaching â†’</a>

            <p style="margin-top: 30px; font-size: 13px; color: #999;">
                Vous recevez cet email parce que vous avez commencÃ© un programme de coaching et n'avez pas d'activitÃ© rÃ©cente.
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0;">Â© 2026 Nutrivert - Coaching System</p>
            <p style="margin: 5px 0 0 0;">Ce message a Ã©tÃ© envoyÃ© Ã  <strong>{$toEmail}</strong></p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Retourne la couleur basÃ©e sur la progression
     */
    private function getProgressColor(int $progressPercentage): string
    {
        if ($progressPercentage < 30) {
            return '#FF7E67'; // Coral (early stage)
        }
        if ($progressPercentage < 70) {
            return '#FFB84D'; // Orange (middle)
        }
        return '#7DCFB6'; // Mint (almost done)
    }

    /**
     * GÃ©nÃ¨re le lien vers le coaching
     */
    private function generateCoachingLink(): string
    {
        // Retourner un lien gÃ©nÃ©rique vers le dashboard
        // En production, cela devrait Ãªtre un lien avec token pour accÃ¨s directe
        $baseUrl = 'http://localhost/Integration/malek_coach'; // Ã€ adapter selon votre environnement
        return $baseUrl . '/index.php?controller=user_dashboard&action=coaching_list';
    }

    /**
     * Construit les headers de l'email
     */
    private function buildEmailHeaders(): string
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "X-Mailer: Nutrivert Reminder System\r\n";
        return $headers;
    }

    /**
     * Envoie l'email via le transport configurÃ©
     */
    private function send(string $to, string $subject, string $body, string $headers): bool
    {
        try {
            if ($this->transport === 'smtp') {
                $this->logEmailDebug("Tentative envoi via SMTP Ã  {$to}");
                return $this->sendViaSMTP($to, $subject, $body, $headers);
            }

            // Utiliser mail() de PHP (php_mail)
            $this->logEmailDebug("Tentative envoi via PHP mail() Ã  {$to}");
            $result = mail($to, $subject, $body, $headers);
            
            if ($result) {
                $this->logEmailDebug("Email envoyÃ© avec succÃ¨s Ã  {$to}");
                return true;
            }

            $sendmail = ini_get('sendmail_path') ?: 'non configurÃ©';
            $smtphost = ini_get('SMTP') ?: 'non configurÃ©';
            $smtpport = ini_get('smtp_port') ?: 'non configurÃ©';
            $this->logEmailDebug("Ã‰chec envoi email Ã  {$to} - mail() a retournÃ© false. Configuration: sendmail_path={$sendmail}, SMTP={$smtphost}, smtp_port={$smtpport}");

            if (!empty($this->config['smtp']['host'])) {
                $this->logEmailDebug('Tentative de fallback SMTP aprÃ¨s Ã©chec de mail()');
                return $this->sendViaSMTP($to, $subject, $body, $headers);
            }

            return false;
        } catch (Exception $e) {
            error_log("EmailService: Exception lors de l'envoi d'email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie l'email via SMTP
     */
    private function sendViaSMTP(string $to, string $subject, string $body, string $headers): bool
    {
        $smtpConfig = $this->config['smtp'] ?? [];
        $host = trim($smtpConfig['host'] ?? '');
        $port = (int)($smtpConfig['port'] ?? 25);
        $username = $smtpConfig['username'] ?? '';
        $password = $smtpConfig['password'] ?? '';
        $encryption = $smtpConfig['encryption'] ?? null;
        $timeout = 20;

        if ($host === '') {
            $this->logEmailDebug('SMTP configuration incomplÃ¨te (host vide).');
            return false;
        }

        $remoteSocket = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $socket = stream_socket_client($remoteSocket, $errno, $errstr, $timeout);

        if (!$socket) {
            $this->logEmailDebug("Ã‰chec de connexion SMTP Ã  {$remoteSocket} ({$errno}: {$errstr})");
            return false;
        }

        stream_set_timeout($socket, $timeout);

        if (!$this->smtpRead($socket, 220)) {
            fclose($socket);
            return false;
        }

        $hostname = gethostname() ?: 'localhost';
        if (!$this->smtpWrite($socket, "EHLO $hostname", 250)) {
            fclose($socket);
            return false;
        }

        if ($encryption === 'tls') {
            if (!$this->smtpWrite($socket, 'STARTTLS', 220)) {
                fclose($socket);
                return false;
            }

            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log('EmailService SMTP: STARTTLS Ã©chouÃ©');
                fclose($socket);
                return false;
            }

            if (!$this->smtpWrite($socket, "EHLO $hostname", 250)) {
                fclose($socket);
                return false;
            }
        }

        if ($username !== '' && $password !== '') {
            if (!$this->smtpWrite($socket, 'AUTH LOGIN', 334)) {
                fclose($socket);
                return false;
            }

            if (!$this->smtpWrite($socket, base64_encode($username), 334)) {
                fclose($socket);
                return false;
            }

            if (!$this->smtpWrite($socket, base64_encode($password), 235)) {
                fclose($socket);
                return false;
            }
        }

        $from = $this->fromEmail;
        if (!$this->smtpWrite($socket, "MAIL FROM:<$from>", 250)) {
            fclose($socket);
            return false;
        }

        if (!$this->smtpWrite($socket, "RCPT TO:<$to>", [250, 251])) {
            fclose($socket);
            return false;
        }

        if (!$this->smtpWrite($socket, 'DATA', 354)) {
            fclose($socket);
            return false;
        }

        $date = date('r');
        $fullHeaders = "Date: $date\r\n" .
            "From: {$this->fromName} <{$this->fromEmail}>\r\n" .
            "To: $to\r\n" .
            "Subject: $subject\r\n" .
            $headers .
            "\r\n";

        $message = $fullHeaders . $body;
        $message = str_replace(["\r\n.", "\n."], ["\r\n..", "\n.."], $message);

        fwrite($socket, $message . "\r\n.\r\n");
        if (!$this->smtpRead($socket, 250)) {
            error_log('EmailService SMTP: Ã©chec de DATA, le serveur nâ€™a pas acceptÃ© le message.');
            fclose($socket);
            return false;
        }

        $this->smtpWrite($socket, 'QUIT', 221);
        fclose($socket);
        return true;
    }

    private function smtpRead($socket, $expectedCode): bool
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        if ($response === '') {
            $this->logEmailDebug('SMTP: aucune rÃ©ponse du serveur');
            return false;
        }

        $code = (int)substr($response, 0, 3);
        if (is_array($expectedCode)) {
            $ok = in_array($code, $expectedCode, true);
        } else {
            $ok = $code === $expectedCode;
        }

        if (!$ok) {
            $this->logEmailDebug("SMTP rÃ©ponse inattendue: {$response}");
        }

        return $ok;
    }

    private function smtpWrite($socket, string $command, $expectedCode): bool
    {
        fwrite($socket, $command . "\r\n");
        return $this->smtpRead($socket, $expectedCode);
    }

    /**
     * Enregistre un log debug pour le service email
     */
    private function logEmailDebug(string $message): void
    {
        $logDir = __DIR__ . '/../../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'email_service.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[{$timestamp}] {$message}\n";
        file_put_contents($logFile, $entry, FILE_APPEND);
        error_log("EmailService: {$message}");
    }

    /**
     * Enregistre un log d'envoi d'email
     */
    public function logEmailSent(
        string $toEmail,
        string $userName,
        string $coachingName,
        int $progressPercentage,
        string $reminderType
    ): bool
    {
        $logDir = __DIR__ . '/../../logs/';
        
        // CrÃ©er le dossier s'il n'existe pas
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'reminders_' . date('Y-m-d') . '.log';
        $logEntry = sprintf(
            "[%s] Email sent to %s (User: %s, Coaching: %s, Progress: %d%%, Type: %s)\n",
            date('Y-m-d H:i:s'),
            $toEmail,
            $userName,
            $coachingName,
            $progressPercentage,
            $reminderType
        );

        return (bool)file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}


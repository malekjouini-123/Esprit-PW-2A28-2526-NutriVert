<?php
declare(strict_types=1);

// Cron Job pour traiter les rappels d'email
//
// Tous les jours à minuit :
// 0 0 * * * /usr/bin/php /path/to/cron/process_reminders.php
//
// Toutes les heures :
// 0 * * * * /usr/bin/php /path/to/cron/process_reminders.php
//
// Chaque 30 minutes :
// */30 * * * * /usr/bin/php /path/to/cron/process_reminders.php

$rootDir = realpath(__DIR__ . '/../');

require_once $rootDir . '/config/database.php';
require_once $rootDir . '/user/Model/ReminderEmailService.php';
require_once $rootDir . '/user/Model/ReminderService.php';

$remindersConfig = require $rootDir . '/config/reminders.php';

$logDir = $rootDir . '/logs/';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . 'cron_reminders_' . date('Y-m-d') . '.log';

function log_execution(string $message): void
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
    echo $entry;
}

try {
    log_execution("=== Starting reminder processing ===");

    $pdo = getDB();

    $reminderService = new ReminderService($pdo, $remindersConfig);

    $stats = $reminderService->processAllReminders();

    log_execution("Reminders processed successfully");
    log_execution("  - Total processed: " . $stats['processed']);
    log_execution("  - Reminders sent: " . $stats['reminders_sent']);
    log_execution("  - Errors: " . $stats['errors']);

    if (($remindersConfig['logging']['level'] ?? 1) >= 2) {
        foreach ($stats['details'] as $detail) {
            log_execution("  - [" . $detail['status'] . "] User #" . $detail['user_id'] . " Coaching #" . $detail['coaching_id']);
        }
    }

    log_execution("=== Reminders processing completed ===\n");
    exit(0);

} catch (Throwable $e) {
    log_execution("ERROR: " . $e->getMessage());
    log_execution("Stack trace: " . $e->getTraceAsString());
    log_execution("=== Reminders processing failed ===\n");
    exit(1);
}

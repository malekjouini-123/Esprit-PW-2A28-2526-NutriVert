<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

$mailFrom = getenv_safe('MAIL_FROM') ?: 'noreply@nutrivert.local';

return [
    'reminders' => [
        'enabled'                        => true,
        'min_interval_between_reminders' => 3600,
        'max_reminders_per_coaching'     => 5,
    ],
    'inactivity' => [
        'early_reminder_hours'    => 24,
        'standard_reminder_hours' => 48,
        'urgent_reminder_hours'   => 72,
    ],
    'logging' => [
        'level' => 2,
    ],
    'email' => [
        'from'       => $mailFrom,
        'from_name'  => 'NutriVert',
        'transport'  => 'php_mail',
        'smtp'       => [
            'host'     => getenv_safe('MAIL_SMTP_HOST', 'smtp.gmail.com'),
            'port'     => (int)getenv_safe('MAIL_SMTP_PORT', '587'),
            'username' => $mailFrom,
            'password' => getenv_safe('GOOGLE_REFRESH_TOKEN'),
        ],
    ],
];

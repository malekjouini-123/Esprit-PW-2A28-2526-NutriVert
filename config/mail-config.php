<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

return [
    'driver' => 'gmail',

    'google' => [
        'client_id'     => getenv_safe('GOOGLE_CLIENT_ID'),
        'client_secret' => getenv_safe('GOOGLE_CLIENT_SECRET'),
        'refresh_token' => getenv_safe('GOOGLE_REFRESH_TOKEN'),
        'redirect_uri'  => getenv_safe('GOOGLE_REDIRECT_URI'),
    ],

    'smtp' => [
        'host'       => getenv_safe('MAIL_SMTP_HOST', 'smtp.gmail.com'),
        'port'       => getenv_safe('MAIL_SMTP_PORT', '587'),
        'encryption' => getenv_safe('MAIL_SMTP_ENCRYPTION', 'tls'),
        'username'   => getenv_safe('MAIL_FROM'),
        'password'   => getenv_safe('GOOGLE_REFRESH_TOKEN'),
    ],

    'from' => getenv_safe('MAIL_FROM'),
];

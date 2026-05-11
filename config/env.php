<?php
declare(strict_types=1);

function loadEnv(string $filePath = ''): void {
    if ($filePath === '') {
        $filePath = __DIR__ . '/.env';
    }
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, '\'"');
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

loadEnv();

function getenv_safe(string $key, $default = null): string {
    $v = $_ENV[$key] ?? getenv($key);
    return ($v !== false && $v !== null && $v !== '') ? (string)$v : (string)($default ?? '');
}

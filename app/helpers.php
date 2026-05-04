<?php
function app_current_lang(): string
{
    $allowed = ['fr', 'en', 'ar'];
    if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? 'fr';
}

function app_translations(): array
{
    static $translations = null;
    if ($translations === null) {
        $translations = require __DIR__ . '/lang.php';
    }
    return $translations;
}

function t(string $key): string
{
    $lang = app_current_lang();
    $translations = app_translations();
    return $translations[$lang][$key] ?? $translations['fr'][$key] ?? $key;
}

function lang_url(string $lang): string
{
    $query = $_GET;
    $query['lang'] = $lang;
    return 'index.php?' . http_build_query($query);
}

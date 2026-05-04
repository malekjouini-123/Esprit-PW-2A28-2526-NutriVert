<?php
/**
 * Charge les variables d'environnement depuis le fichier .env
 */

function loadEnv($filePath = __DIR__ . '/../.env') {
    if (!file_exists($filePath)) {
        throw new Exception("Le fichier .env n'existe pas: $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parser les variables
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '\'"');

            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Charger les variables d'environnement
loadEnv();

/**
 * Récupère une variable d'environnement
 */
function getenv_safe($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

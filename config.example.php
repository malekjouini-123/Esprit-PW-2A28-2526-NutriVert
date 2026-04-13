<?php
/**
 * Configuration globale NutriVert (MVC)
 * Paramètres MySQL par défaut pour XAMPP/WAMP
 */
declare(strict_types=1);

// Paramètres de connexion
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'NutriVert');
define('DB_USER', 'root');
define('DB_PASS', '');

function nv_bootstrap(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        nv_send_cors();
        http_response_code(204);
        exit;
    }
}

function nv_send_cors(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

function nv_json($data, int $code = 200): void
{
    nv_send_cors();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function nv_json_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $j = json_decode($raw, true);
    return is_array($j) ? $j : [];
}

function nv_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        // En production, ne pas afficher l'erreur brute
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
    
    return $pdo;
}

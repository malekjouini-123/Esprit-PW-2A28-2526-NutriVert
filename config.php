<?php
/**
 * Configuration globale NutriVert (MVC) — MySQL XAMPP par défaut : root sans mot de passe.
 */
declare(strict_types=1);

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
    $dsn = 'mysql:host=127.0.0.1;dbname=nutrivert;charset=utf8mb4';
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

/** Pourcentages de promotion vitrine autorisés (0 = aucune réduction). */
function nv_promo_pourcent_normalize(int $pct): int
{
    return in_array($pct, [15, 20, 25, 50, 75], true) ? $pct : 0;
}

/** Prix catalogue après application d’une promo (arrondi 2 déc.). */
function nv_prix_apres_promo(float $prixCatalogue, int $promoPourcent): float
{
    $p = nv_promo_pourcent_normalize($promoPourcent);
    if ($p <= 0) {
        return round($prixCatalogue, 2);
    }
    return round($prixCatalogue * (100 - $p) / 100, 2);
}

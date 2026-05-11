<?php
declare(strict_types=1);

// Paramètres de connexion
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'nutrivert');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Retourne une instance de PDO pour la base de données.
 */
function get_pdo(): PDO
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
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
    
    return $pdo;
}

/**
 * Envoie une réponse JSON et arrête l'exécution.
 */
function send_json($data, int $code = 200): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Email (simple) ---
// On XAMPP/Windows, PHP mail() may require SMTP configuration.
define('MAIL_FROM', 'no-reply@localhost');
define('MAIL_FROM_NAME', 'Gestion des Événements');

// --- Gmail SMTP (optionnel) ---
// 1) Copiez config.email.example.php → config.email.php et renseignez vos identifiants.
// 2) composer install (PHPMailer dans vendor/).
if (is_readable(__DIR__ . '/config.email.php')) {
    require_once __DIR__ . '/config.email.php';
}
if (!defined('GMAIL_SMTP_USER')) {
    define('GMAIL_SMTP_USER', '');
}
if (!defined('GMAIL_SMTP_PASS')) {
    define('GMAIL_SMTP_PASS', '');
}
if (!defined('GMAIL_SMTP_FROM')) {
    define('GMAIL_SMTP_FROM', '');
}

/**
 * Crée le répertoire des logs s'il n'existe pas.
 */
function ensure_log_directory(): string
{
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    return $logDir;
}

/**
 * Enregistre un message dans le fichier de log.
 */
function log_message(string $type, string $message): void
{
    $logDir = ensure_log_directory();
    $logFile = $logDir . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Envoie un email (HTML). Retourne [bool success, string message].
 */
function send_email(string $to, string $subject, string $htmlBody): array
{
    $to = trim($to);
    if ($to === '') {
        $error = "Email destination vide";
        log_message('ERROR', "send_email(): $error");
        return [false, $error];
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide: $to";
        log_message('ERROR', "send_email(): $error");
        return [false, $error];
    }

    // Prefer Gmail SMTP via PHPMailer if available & configured
    if (GMAIL_SMTP_USER !== '' && GMAIL_SMTP_PASS !== '') {
        $autoload = dirname(__DIR__) . '/vendor/autoload.php';
        if (!file_exists($autoload)) {
            $error = 'PHPMailer absent : exécutez composer install à la racine.';
            log_message('ERROR', "send_email(): $error");
            return [false, $error];
        }
        require_once $autoload;
        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            $error = 'PHPMailer non chargé (vendor/autoload.php).';
            log_message('ERROR', "send_email(): $error");
            return [false, $error];
        }
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = GMAIL_SMTP_USER;
            $mail->Password = GMAIL_SMTP_PASS;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPOptions = [
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
            ];

            $fromEmail = (GMAIL_SMTP_FROM !== '') ? GMAIL_SMTP_FROM : GMAIL_SMTP_USER;
            $mail->setFrom($fromEmail, MAIL_FROM_NAME);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();
            log_message('INFO', "Email envoyé à $to via Gmail SMTP");
            return [true, 'Email envoyé avec succès'];
        } catch (Throwable $e) {
            $error = 'PHPMailer : ' . $e->getMessage();
            log_message('ERROR', "send_email() Gmail → $to : $error");
            return [false, $error];
        }
    }

    // Fallback to native mail() function
    log_message('INFO', 'Gmail non configuré (remplissez config.email.php ou GMAIL_SMTP_*) — essai avec mail()');
    
    $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8');
    $fromName = mb_encode_mimeheader(MAIL_FROM_NAME, 'UTF-8');

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . $fromName . ' <' . MAIL_FROM . '>';
    $headers[] = 'Reply-To: ' . MAIL_FROM;

    try {
        $result = mail($to, $encodedSubject, $htmlBody, implode("\r\n", $headers));
        if ($result) {
            log_message('INFO', "Email envoyé à $to via mail()");
            return [true, "Email envoyé avec succès"];
        } else {
            $error = "mail() function failed - SMTP may not be configured on this server";
            log_message('WARNING', "send_email() to $to: $error");
            return [false, $error];
        }
    } catch (Throwable $e) {
        $error = "mail() exception: " . $e->getMessage();
        log_message('ERROR', "send_email() to $to: $error");
        return [false, $error];
    }
}

/**
 * Génère un token CSRF et le stocke en session.
 */
function generate_csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie la validité du token CSRF.
 */
function verify_csrf_token(?string $token): bool
{
    if (!isset($_SESSION['csrf_token']) || $token === null) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Hash un mot de passe avec password_hash() (Bcrypt).
 */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Vérifie un mot de passe contre son hash.
 */
function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Initialise la session (à appeler en début de chaque request).
 */
function init_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Obtient l'ID de l'utilisateur en session, sinon retourne null.
 */
function get_user_id(): ?int
{
    init_session();
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Obtient le rôle de l'utilisateur en session ('admin' ou 'user'), sinon retourne null.
 */
function get_user_role(): ?string
{
    init_session();
    return $_SESSION['user_role'] ?? null;
}

/**
 * Définit la session utilisateur.
 */
function set_user_session(int $userId, string $role = 'user'): void
{
    init_session();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $role;
}

/**
 * Détruit la session utilisateur (logout).
 */
function destroy_user_session(): void
{
    init_session();
    unset($_SESSION['user_id']);
    unset($_SESSION['user_role']);
    session_destroy();
}

/**
 * Vérifie que l'utilisateur est connecté, sinon redirige vers la page de login.
 */
function require_login(): void
{
    if (get_user_id() === null) {
        header('Location: index.php?action=login');
        exit;
    }
}

/**
 * Vérifie que l'utilisateur est admin, sinon affiche une erreur.
 */
function require_admin(): void
{
    if (get_user_role() !== 'admin') {
        http_response_code(403);
        die("Accès refusé. Seuls les administrateurs peuvent accéder à cette page.");
    }
}

/**
 * Sauvegarde un fichier uploadé dans /assets/uploads/<subdir> et retourne l'URL relative (ex: assets/uploads/events/xxx.jpg)
 * Retourne null si aucun fichier valide.
 */
function save_uploaded_image(array $file, string $subdir): ?string
{
    if (empty($file) || !isset($file['error']) || (int)$file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $mime = (string)($file['type'] ?? '');
    if (!isset($allowed[$mime])) {
        return null;
    }

    $uploadRoot = __DIR__ . '/assets/uploads';
    $dir = $uploadRoot . '/' . trim($subdir, '/');
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $ext = $allowed[$mime];
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!@move_uploaded_file($tmp, $dest)) {
        return null;
    }

    return 'assets/uploads/' . trim($subdir, '/') . '/' . $name;
}

<?php
declare(strict_types=1);

/**
 * Envoi d'e-mails HTML via SMTP (Gmail : smtp.gmail.com:587 + STARTTLS).
 * Configurez les constantes dans config.php (voir config.example.php).
 */

function nv_mail_smtp_configured(): bool
{
    return defined('NV_SMTP_USER') && NV_SMTP_USER !== ''
        && defined('NV_SMTP_PASSWORD') && NV_SMTP_PASSWORD !== '';
}

function nv_mail_smtp_read($fp): string
{
    $data = '';
    while (($line = fgets($fp, 8192)) !== false) {
        $data .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }
    return $data;
}

function nv_mail_smtp_expect(string $response, array $okPrefixes): bool
{
    foreach ($okPrefixes as $p) {
        if (str_starts_with($response, $p)) {
            return true;
        }
    }
    return false;
}

function nv_mail_smtp_cmd($fp, string $line, array $okPrefixes): ?string
{
    fwrite($fp, $line . "\r\n");
    $resp = nv_mail_smtp_read($fp);
    return nv_mail_smtp_expect($resp, $okPrefixes) ? $resp : null;
}

/**
 * Envoie un message HTML. Retourne false en cas d'échec ; $error contient une courte explication.
 */
function nv_send_html_mail(string $to, string $subject, string $htmlBody, ?string &$error = null): bool
{
    $error = null;
    if (!nv_mail_smtp_configured()) {
        $error = 'SMTP non configuré (NV_SMTP_USER / NV_SMTP_PASSWORD dans config.php).';
        return false;
    }
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse destinataire invalide.';
        return false;
    }

    $host = defined('NV_SMTP_HOST') ? NV_SMTP_HOST : 'smtp.gmail.com';
    $port = defined('NV_SMTP_PORT') ? (int)NV_SMTP_PORT : 587;
    $user = NV_SMTP_USER;
    $pass = NV_SMTP_PASSWORD;
    $fromEmail = (defined('NV_MAIL_FROM_EMAIL') && NV_MAIL_FROM_EMAIL !== '') ? NV_MAIL_FROM_EMAIL : $user;
    $fromName = defined('NV_MAIL_FROM_NAME') ? NV_MAIL_FROM_NAME : 'NutriVert';

    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $fp = @stream_socket_client(
        "tcp://{$host}:{$port}",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $ctx
    );
    if (!$fp) {
        $error = "Connexion SMTP impossible : {$errstr} ({$errno})";
        return false;
    }
    stream_set_timeout($fp, 30);

    $greet = nv_mail_smtp_read($fp);
    if (!nv_mail_smtp_expect($greet, ['220'])) {
        $error = 'Réponse serveur inattendue : ' . trim($greet);
        fclose($fp);
        return false;
    }

    if (nv_mail_smtp_cmd($fp, 'EHLO localhost', ['250']) === null) {
        $error = 'EHLO refusé';
        fclose($fp);
        return false;
    }

    if (nv_mail_smtp_cmd($fp, 'STARTTLS', ['220']) === null) {
        $error = 'STARTTLS refusé';
        fclose($fp);
        return false;
    }

    if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        $error = 'Échec négociation TLS';
        fclose($fp);
        return false;
    }

    if (nv_mail_smtp_cmd($fp, 'EHLO localhost', ['250']) === null) {
        $error = 'EHLO après TLS refusé';
        fclose($fp);
        return false;
    }

    if (nv_mail_smtp_cmd($fp, 'AUTH LOGIN', ['334']) === null) {
        $error = 'AUTH LOGIN non supporté';
        fclose($fp);
        return false;
    }
    if (nv_mail_smtp_cmd($fp, base64_encode($user), ['334']) === null) {
        $error = 'Identifiant refusé';
        fclose($fp);
        return false;
    }
    if (nv_mail_smtp_cmd($fp, base64_encode($pass), ['235']) === null) {
        $error = 'Mot de passe SMTP refusé (vérifiez le mot de passe d\'application Gmail).';
        fclose($fp);
        return false;
    }

    if (nv_mail_smtp_cmd($fp, 'MAIL FROM:<' . $fromEmail . '>', ['250']) === null) {
        $error = 'MAIL FROM refusé';
        fclose($fp);
        return false;
    }
    if (nv_mail_smtp_cmd($fp, 'RCPT TO:<' . $to . '>', ['250', '251']) === null) {
        $error = 'RCPT TO refusé';
        fclose($fp);
        return false;
    }
    if (nv_mail_smtp_cmd($fp, 'DATA', ['354']) === null) {
        $error = 'DATA refusé';
        fclose($fp);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $fromHeader = sprintf('"%s" <%s>', addcslashes($fromName, '"\\'), $fromEmail);

    $body = str_replace(["\r\n", "\r"], "\n", $htmlBody);
    $body = str_replace("\n", "\r\n", $body);
    $body = preg_replace('/^\./m', '..', $body) ?? $body;

    $headers = "From: {$fromHeader}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "To: <{$to}>\r\n";
    $headers .= "Subject: {$encodedSubject}\r\n";

    fwrite($fp, $headers . "\r\n" . $body . "\r\n.\r\n");
    $dataResp = nv_mail_smtp_read($fp);
    if (!nv_mail_smtp_expect($dataResp, ['250'])) {
        $error = 'Message refusé : ' . trim($dataResp);
        fclose($fp);
        return false;
    }

    nv_mail_smtp_cmd($fp, 'QUIT', ['221', '250']);
    fclose($fp);
    return true;
}

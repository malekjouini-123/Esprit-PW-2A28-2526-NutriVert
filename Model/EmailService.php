<?php
/**
 * Service d'envoi d'emails avec Gmail OAuth 2.0
 * 
 * Supporte deux modes:
 * 1. Avec PHPMailer (recommandé) - composer require phpmailer/phpmailer
 * 2. Avec stream_context (solution alternative)
 */

require_once __DIR__ . '/../config/mail-config.php';

class EmailService {
    private $config;
    private $mailer;
    private $usePHPMailer = false;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/mail-config.php';
        $this->checkPHPMailer();
    }

    private function checkPHPMailer() {
        // Vérifier si PHPMailer est disponible
        $autoloadPaths = [
            __DIR__ . '/../vendor/autoload.php',
            'vendor/autoload.php',
        ];
        
        foreach ($autoloadPaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                    $this->usePHPMailer = true;
                    $this->initializeWithPHPMailer();
                    return;
                }
            }
        }
        
        // Fallback si PHPMailer n'est pas disponible
        error_log('PHPMailer n\'est pas installé. Utilisation du fallback SMTP simple.');
    }

    private function initializeWithPHPMailer() {
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Configuration SMTP pour Gmail
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp']['host'];
            $this->mailer->Port = $this->config['smtp']['port'];
            $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->SMTPAuth = true;
            
            $this->mailer->Username = $this->config['from'];
            $this->mailer->Password = $this->config['smtp']['password'];
            
            $this->mailer->setFrom($this->config['from']);
        } catch (Exception $e) {
            throw new Exception('Erreur de configuration email: ' . $e->getMessage());
        }
    }

    /**
     * Envoie un email de réinitialisation de mot de passe
     */
    public function sendPasswordReset($email, $resetLink, $userName) {
        $subject = '[NutriVert] Réinitialisation de votre mot de passe';
        
        $body = "
        <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Réinitialisation de mot de passe</h2>
                <p>Bonjour {$userName},</p>
                <p>Nous avons reçu une demande de réinitialisation de mot de passe.</p>
                <p>
                    <a href='{$resetLink}' style='background-color: #2e7d32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                        Réinitialiser mon mot de passe
                    </a>
                </p>
                <p>Ce lien expire dans 24 heures.</p>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            </body>
        </html>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Envoie un email de confirmation
     */
    public function sendConfirmation($email, $confirmationLink, $userName) {
        $subject = '[NutriVert] Confirmez votre adresse email';
        
        $body = "
        <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Confirmation d'email</h2>
                <p>Bienvenue {$userName} sur NutriVert!</p>
                <p>Veuillez confirmer votre adresse email en cliquant le lien ci-dessous:</p>
                <p>
                    <a href='{$confirmationLink}' style='background-color: #2e7d32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                        Confirmer mon email
                    </a>
                </p>
                <p>Ce lien expire dans 48 heures.</p>
            </body>
        </html>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Envoie un email générique
     */
    public function send($to, $subject, $body) {
        if ($this->usePHPMailer) {
            return $this->sendWithPHPMailer($to, $subject, $body);
        } else {
            return $this->sendWithSMTPFallback($to, $subject, $body);
        }
    }

    private function sendWithPHPMailer($to, $subject, $body) {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log('Erreur d\'envoi email (PHPMailer): ' . $e->getMessage());
            return false;
        }
    }

    private function sendWithSMTPFallback($to, $subject, $body) {
        try {
            // Configuration pour envoyer via Gmail SMTP sans PHPMailer
            $host = $this->config['smtp']['host'];
            $port = $this->config['smtp']['port'];
            $username = $this->config['from'];
            $password = $this->config['smtp']['password'];
            // Try to get an OAuth2 access token from refresh token if available
            $accessToken = null;
            if (!empty($this->config['google']['refresh_token']) && !empty($this->config['google']['client_id']) && !empty($this->config['google']['client_secret'])) {
                $accessToken = $this->getAccessTokenFromRefreshToken(
                    $this->config['google']['client_id'],
                    $this->config['google']['client_secret'],
                    $this->config['google']['refresh_token']
                );
            }
            
            // Créer la connexion SMTP
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
            
            // Connect plain TCP and then STARTTLS (preferred) or fallback to implicit SSL on 465
            if ((int)$port === 465) {
                $smtp = fsockopen('ssl://' . $host, $port, $errno, $errstr, 10);
            } else {
                $smtp = fsockopen($host, $port, $errno, $errstr, 10);
            }
            
            if (!$smtp) {
                throw new Exception('Connexion SMTP échouée: ' . $errstr);
            }
            
            // Lire la réponse du serveur
            $response = fgets($smtp, 1024);

            // Send EHLO
            fwrite($smtp, "EHLO " . gethostname() . "\r\n");
            $ehlo = '';
            while ($line = fgets($smtp, 1024)) {
                $ehlo .= $line;
                if (substr($line, 3, 1) !== '-') break;
            }

            // If not implicit SSL on 465, request STARTTLS
            if ((int)$port !== 465) {
                fwrite($smtp, "STARTTLS\r\n");
                $starttlsResp = fgets($smtp, 1024);
                if (strpos($starttlsResp, '220') !== 0) {
                    throw new Exception('STARTTLS failed: ' . $starttlsResp);
                }

                // Enable crypto (TLS)
                $cryptoEnabled = false;
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                }
                $cryptoEnabled = stream_socket_enable_crypto($smtp, true, $cryptoMethod);
                if ($cryptoEnabled !== true) {
                    throw new Exception('Failed to enable crypto for SMTP connection');
                }

                // Repeat EHLO after STARTTLS
                fwrite($smtp, "EHLO " . gethostname() . "\r\n");
                while ($line = fgets($smtp, 1024)) {
                    if (substr($line, 3, 1) !== '-') break;
                }
            }

            // Authenticate: prefer XOAUTH2 if access token available
            if ($accessToken) {
                $authString = base64_encode("user={$username}\x01auth=Bearer {$accessToken}\x01\x01");
                fwrite($smtp, "AUTH XOAUTH2 " . $authString . "\r\n");
                $authResp = fgets($smtp, 1024);
                if (strpos($authResp, '235') !== 0) {
                    throw new Exception('SMTP XOAUTH2 authentication failed: ' . $authResp);
                }
            } else {
                // Fallback to AUTH LOGIN with username/password
                fwrite($smtp, "AUTH LOGIN\r\n");
                $resp = fgets($smtp, 1024);
                fwrite($smtp, base64_encode($username) . "\r\n");
                $resp = fgets($smtp, 1024);
                fwrite($smtp, base64_encode($password) . "\r\n");
                $resp = fgets($smtp, 1024);
                if (strpos($resp, '235') !== 0) {
                    throw new Exception('SMTP AUTH LOGIN failed: ' . $resp);
                }
            }

            // MAIL FROM / RCPT TO / DATA
            fwrite($smtp, "MAIL FROM: <" . $username . ">\r\n");
            $resp = fgets($smtp, 1024);
            fwrite($smtp, "RCPT TO: <" . $to . ">\r\n");
            $resp = fgets($smtp, 1024);
            fwrite($smtp, "DATA\r\n");
            $resp = fgets($smtp, 1024);

            // Headers + body
            $headers = "From: " . $username . "\r\n";
            $headers .= "To: " . $to . "\r\n";
            $headers .= "Subject: " . $subject . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n\r\n";

            fwrite($smtp, $headers);
            fwrite($smtp, $body . "\r\n");
            fwrite($smtp, ".\r\n");
            $resp = fgets($smtp, 1024);
            if (strpos($resp, '250') !== 0 && strpos($resp, '354') !== 0) {
                // Some servers respond 250 after DATA end, others 250 after '.'
                // We'll check for common failure codes
                if (strpos($resp, '550') === 0 || strpos($resp, '554') === 0) {
                    throw new Exception('Erreur SMTP: ' . $resp);
                }
            }
            
            fwrite($smtp, "QUIT\r\n");
            fclose($smtp);
            
            return true;
        } catch (Exception $e) {
            error_log('Erreur d\'envoi email (SMTP): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un access token OAuth2 depuis un refresh token
     */
    private function getAccessTokenFromRefreshToken($clientId, $clientSecret, $refreshToken) {
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $post = http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ]);

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            error_log('Curl error getting access token: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['access_token'])) {
            error_log('Invalid token response: ' . $response);
            return null;
        }

        return $data['access_token'];
    }
}

<?php
declare(strict_types=1);

if (class_exists('EmailService')) return;

require_once __DIR__ . '/../config/mail-config.php';

class EmailService {
    private array $config;
    private $mailer = null;
    private bool $usePHPMailer = false;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/mail-config.php';
        $this->checkPHPMailer();
    }

    private function checkPHPMailer(): void {
        $autoloadPaths = [
            __DIR__ . '/../vendor/autoload.php',
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

        error_log('PHPMailer non installé. Utilisation du fallback SMTP.');
    }

    private function initializeWithPHPMailer(): void {
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['smtp']['host'];
            $this->mailer->Port       = (int)$this->config['smtp']['port'];
            $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config['from'];
            $this->mailer->Password   = $this->config['smtp']['password'];
            $this->mailer->setFrom($this->config['from']);
        } catch (\Exception $e) {
            throw new \Exception('Erreur de configuration email: ' . $e->getMessage());
        }
    }

    public function send(string $to, string $subject, string $body): bool {
        if ($this->usePHPMailer) {
            return $this->sendWithPHPMailer($to, $subject, $body);
        }
        return $this->sendWithSMTPFallback($to, $subject, $body);
    }

    private function sendWithPHPMailer(string $to, string $subject, string $body): bool {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (\Exception $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
            return false;
        }
    }

    private function sendWithSMTPFallback(string $to, string $subject, string $body): bool {
        try {
            $host     = $this->config['smtp']['host'];
            $port     = (int)$this->config['smtp']['port'];
            $username = $this->config['from'];
            $password = $this->config['smtp']['password'];

            $accessToken = null;
            if (!empty($this->config['google']['refresh_token'])
                && !empty($this->config['google']['client_id'])
                && !empty($this->config['google']['client_secret'])) {
                $accessToken = $this->getAccessTokenFromRefreshToken(
                    $this->config['google']['client_id'],
                    $this->config['google']['client_secret'],
                    $this->config['google']['refresh_token']
                );
            }

            if ($port === 465) {
                $smtp = fsockopen('ssl://' . $host, $port, $errno, $errstr, 10);
            } else {
                $smtp = fsockopen($host, $port, $errno, $errstr, 10);
            }

            if (!$smtp) {
                throw new \Exception('Connexion SMTP échouée: ' . $errstr);
            }

            fgets($smtp, 1024);

            fwrite($smtp, "EHLO " . gethostname() . "\r\n");
            while ($line = fgets($smtp, 1024)) {
                if (substr($line, 3, 1) !== '-') break;
            }

            if ($port !== 465) {
                fwrite($smtp, "STARTTLS\r\n");
                $resp = fgets($smtp, 1024);
                if (strpos($resp, '220') !== 0) {
                    throw new \Exception('STARTTLS failed: ' . $resp);
                }

                $cryptoMethod = defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')
                    ? STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                    : STREAM_CRYPTO_METHOD_TLS_CLIENT;

                if (stream_socket_enable_crypto($smtp, true, $cryptoMethod) !== true) {
                    throw new \Exception('Failed to enable crypto for SMTP connection');
                }

                fwrite($smtp, "EHLO " . gethostname() . "\r\n");
                while ($line = fgets($smtp, 1024)) {
                    if (substr($line, 3, 1) !== '-') break;
                }
            }

            if ($accessToken) {
                $authString = base64_encode("user={$username}\x01auth=Bearer {$accessToken}\x01\x01");
                fwrite($smtp, "AUTH XOAUTH2 " . $authString . "\r\n");
                $resp = fgets($smtp, 1024);
                if (strpos($resp, '235') !== 0) {
                    throw new \Exception('SMTP XOAUTH2 authentication failed: ' . $resp);
                }
            } else {
                fwrite($smtp, "AUTH LOGIN\r\n");
                fgets($smtp, 1024);
                fwrite($smtp, base64_encode($username) . "\r\n");
                fgets($smtp, 1024);
                fwrite($smtp, base64_encode($password) . "\r\n");
                $resp = fgets($smtp, 1024);
                if (strpos($resp, '235') !== 0) {
                    throw new \Exception('SMTP AUTH LOGIN failed: ' . $resp);
                }
            }

            fwrite($smtp, "MAIL FROM: <{$username}>\r\n");
            fgets($smtp, 1024);
            fwrite($smtp, "RCPT TO: <{$to}>\r\n");
            fgets($smtp, 1024);
            fwrite($smtp, "DATA\r\n");
            fgets($smtp, 1024);

            $headers  = "From: {$username}\r\n";
            $headers .= "To: {$to}\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n\r\n";

            fwrite($smtp, $headers);
            fwrite($smtp, $body . "\r\n");
            fwrite($smtp, ".\r\n");
            fgets($smtp, 1024);
            fwrite($smtp, "QUIT\r\n");
            fclose($smtp);

            return true;
        } catch (\Exception $e) {
            error_log('SMTP fallback error: ' . $e->getMessage());
            return false;
        }
    }

    private function getAccessTokenFromRefreshToken(string $clientId, string $clientSecret, string $refreshToken): ?string {
        $post = http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST          => true,
            CURLOPT_POSTFIELDS    => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER    => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            error_log('Curl error getting access token: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if (!is_array($data) || empty($data['access_token'])) {
            error_log('Invalid token response: ' . $response);
            return null;
        }

        return $data['access_token'];
    }
}

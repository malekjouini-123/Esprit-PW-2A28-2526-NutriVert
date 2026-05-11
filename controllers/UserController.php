<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/FaceRecognitionService.php';
require_once __DIR__ . '/../models/EmailService.php';

if (class_exists('UserController')) return;

class UserController
{
    private PDO $pdo;
    private FaceRecognitionService $faceRecognition;

    public function __construct()
    {
        $this->pdo = getDB();
        $this->faceRecognition = new FaceRecognitionService();
    }

    public function handle(string $action): void
    {
        switch ($action) {
            case 'doRegister':        $this->register();           break;
            case 'doLogin':           $this->login();              break;
            case 'logout':            $this->logout();             break;
            case 'forgotPassword':    $this->forgotPassword();     break;
            case 'resetPassword':     $this->resetPassword();      break;
            case 'adminUsers':        $this->adminUsers();         break;
            default:                  $this->redirect('index.php');
        }
    }

    // =========================================================================
    //  INSCRIPTION
    // =========================================================================

    private function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?view=register');
        }

        $nom        = trim((string)($_POST['nom'] ?? ''));
        $email      = strtolower(trim((string)($_POST['email'] ?? '')));
        $password   = (string)($_POST['password'] ?? '');
        $role       = strtolower(trim((string)($_POST['role'] ?? 'user')));
        $poids      = isset($_POST['poids']) && $_POST['poids'] !== '' ? (float)$_POST['poids'] : null;
        $taille     = isset($_POST['taille']) && $_POST['taille'] !== '' ? (float)$_POST['taille'] : null;
        $age        = isset($_POST['age']) && $_POST['age'] !== '' ? (int)$_POST['age'] : null;
        $sexe       = (string)($_POST['sexe'] ?? 'homme');
        $objectif   = (string)($_POST['objectif'] ?? 'maintien');
        $specialite = trim((string)($_POST['specialite'] ?? ''));
        $bio        = trim((string)($_POST['bio'] ?? ''));

        $errors = [];

        if ($nom === '')     $errors[] = 'Le nom est requis.';
        if ($email === '')   $errors[] = "L'email est requis.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        if ($password === '' || strlen($password) < 4) $errors[] = 'Le mot de passe doit faire au moins 4 caractères.';
        if (!in_array($role, User::ALLOWED_ROLES, true)) $errors[] = 'Rôle invalide.';

        if (empty($errors)) {
            $stmt = $this->pdo->prepare('SELECT id_utilisateur FROM utilisateurs WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Cet email est déjà enregistré.';
            }
        }

        if ($role === 'user') {
            if ($poids === null || $poids <= 0)   $errors[] = 'Le poids est requis pour les utilisateurs.';
            if ($taille === null || $taille <= 0) $errors[] = 'La taille est requise pour les utilisateurs.';
            if ($age === null || $age <= 0)       $errors[] = "L'âge est requis pour les utilisateurs.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_message'] = implode(' ', $errors);
            $this->redirect('index.php?view=register');
        }

        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $imc      = null;
            $calories = null;

            if ($role === 'user' && $poids && $taille) {
                $imc      = $this->calculerImc($poids, $taille);
                $calories = $this->calculerCalories($age ?? 25, $sexe, $objectif);
            }

            $stmt = $this->pdo->prepare('
                INSERT INTO utilisateurs (nom, email, mot_de_passe, role, poids, taille, age, sexe, objectif, imc, calories, specialite, bio)
                VALUES (:nom, :email, :password, :role, :poids, :taille, :age, :sexe, :objectif, :imc, :calories, :specialite, :bio)
            ');

            $stmt->execute([
                ':nom'        => $nom,
                ':email'      => $email,
                ':password'   => $passwordHash,
                ':role'       => $role,
                ':poids'      => $poids,
                ':taille'     => $taille,
                ':age'        => $age,
                ':sexe'       => $sexe,
                ':objectif'   => $objectif,
                ':imc'        => $imc,
                ':calories'   => $calories,
                ':specialite' => $specialite ?: null,
                ':bio'        => $bio ?: null,
            ]);

            $_SESSION['flash_message'] = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            $this->redirect('index.php?view=login');
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors de l'inscription : " . $e->getMessage();
            $this->redirect('index.php?view=register');
        }
    }

    // =========================================================================
    //  CONNEXION
    // =========================================================================

    private function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?view=login');
        }

        $email    = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        $errors = [];
        if ($email === '')    $errors[] = "L'email est requis.";
        if ($password === '') $errors[] = 'Le mot de passe est requis.';

        if (!empty($errors)) {
            $_SESSION['flash_message'] = implode(' ', $errors);
            $this->redirect('index.php?view=login');
        }

        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($password, $row['mot_de_passe'])) {
            $_SESSION['flash_message'] = 'Email ou mot de passe incorrect.';
            $this->redirect('index.php?view=login');
        }

        $this->setUserSession($row);
        $this->logAuth((int)$row['id_utilisateur']);
        $this->redirectByRole($row['role']);
    }

    // =========================================================================
    //  FACE ID LOGIN (JSON endpoint)
    // =========================================================================

    public function faceLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Méthode non autorisée.'], 405);
        }

        $payload = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $email   = trim((string)($payload['email'] ?? ''));
        $capture = (string)($payload['face_image'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'error' => 'Email invalide.'], 422);
        }
        if (!$capture) {
            $this->jsonResponse(['success' => false, 'error' => 'Capture Face ID manquante.'], 422);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->jsonResponse(['success' => false, 'error' => 'Aucun compte ne correspond à cet email.'], 404);
        }

        $storedEncoding = $this->getFaceEncoding((int)$user['id_utilisateur']);
        $result = $this->faceRecognition->compareCapturedImage($capture, $storedEncoding, (int)$user['id_utilisateur']);
        $data   = $result['data'] ?? [];

        if (!$result['success'] || empty($data['match'])) {
            $this->jsonResponse([
                'success'    => false,
                'match'      => false,
                'confidence' => $data['confidence'] ?? 0,
                'error'      => $data['error'] ?? 'Visage non reconnu.',
            ], 401);
        }

        $this->setUserSession($user);
        $this->logAuth((int)$user['id_utilisateur'], 'face_id');

        $redirect = $user['role'] === 'admin'
            ? 'index.php?controller=dashboard&action=index'
            : ($user['role'] === 'coach'
                ? 'index.php?controller=coaching&action=index'
                : 'index.php?controller=user_dashboard&action=index');

        $this->jsonResponse([
            'success'    => true,
            'match'      => true,
            'confidence' => $data['confidence'] ?? null,
            'user_id'    => (int)$user['id_utilisateur'],
            'redirect'   => $redirect,
        ]);
    }

    // =========================================================================
    //  DÉCONNEXION
    // =========================================================================

    private function logout(): void
    {
        session_destroy();
        $this->redirect('index.php');
    }

    // =========================================================================
    //  MOT DE PASSE OUBLIÉ
    // =========================================================================

    private function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = strtolower(trim((string)($_POST['email'] ?? '')));

            if (!$email) {
                $_SESSION['flash_message'] = 'Veuillez entrer votre email.';
                $this->redirect('index.php?view=forgot-password');
            }

            $token = $this->generateResetToken($email);

            if ($token) {
                $resetLink = 'http://' . $_SERVER['HTTP_HOST']
                    . '/Integration/final/index.php?view=reset-password&token=' . $token;

                $stmt = $this->pdo->prepare('SELECT nom FROM utilisateurs WHERE email = :email LIMIT 1');
                $stmt->execute(['email' => $email]);
                $userRow  = $stmt->fetch(PDO::FETCH_ASSOC);
                $userName = $userRow ? htmlspecialchars($userRow['nom']) : 'Utilisateur';

                $subject = 'Réinitialiser votre mot de passe — NutriVert';
                $body    = "
                <html><body style='font-family:Arial,sans-serif;color:#333;'>
                    <div style='max-width:600px;margin:0 auto;'>
                        <h2 style='color:#2e7d32;'>Réinitialisation de Mot de Passe</h2>
                        <p>Bonjour {$userName},</p>
                        <p>Vous avez demandé la réinitialisation de votre mot de passe NutriVert.</p>
                        <p style='text-align:center;margin:2rem 0;'>
                            <a href='" . htmlspecialchars($resetLink) . "'
                               style='background-color:#2e7d32;color:white;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block;'>
                                Réinitialiser Mot de Passe
                            </a>
                        </p>
                        <p style='color:#666;'><strong>Ou copiez ce lien :</strong><br>
                            <small>" . htmlspecialchars($resetLink) . "</small></p>
                        <hr style='border:none;border-top:1px solid #ddd;margin:2rem 0;'>
                        <p style='color:#999;font-size:.9em;'>
                            <strong>Important :</strong> Ce lien expire dans 1 heure.<br>
                            Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                        </p>
                        <p style='color:#999;font-size:.85em;'>© NutriVert — " . date('Y') . "</p>
                    </div>
                </body></html>";

                $this->saveEmailLog($email, $subject, strip_tags($body), $resetLink);

                $emailSent = false;
                try {
                    $emailSent = (new EmailService())->send($email, $subject, $body);
                } catch (\Exception $e) {
                    error_log('EmailService error: ' . $e->getMessage());
                }

                $flashMessage = $emailSent
                    ? "Un lien de réinitialisation a été envoyé à {$email}."
                    : "Lien généré (email non envoyé). Consultez le log ou vérifiez config/.env.";

                $flashMessage = htmlspecialchars($flashMessage);
                include __DIR__ . '/../views/auth/email-confirmation.php';
                exit;
            } else {
                $_SESSION['flash_message'] = "Cet email n'existe pas dans notre système.";
                $this->redirect('index.php?view=forgot-password');
            }
        }

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        include __DIR__ . '/../views/auth/forgot-password.php';
        exit;
    }

    // =========================================================================
    //  RÉINITIALISATION DU MOT DE PASSE
    // =========================================================================

    private function resetPassword(): void
    {
        $token = (string)($_GET['token'] ?? '');

        if (!$token) {
            $_SESSION['flash_message'] = 'Lien de réinitialisation invalide.';
            $this->redirect('index.php?view=login');
        }

        $stmt = $this->pdo->prepare(
            'SELECT * FROM utilisateurs WHERE reset_token = :token AND reset_token_expiry > NOW() LIMIT 1'
        );
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['flash_message'] = 'Lien de réinitialisation expiré ou invalide.';
            $this->redirect('index.php?view=login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = (string)($_POST['password'] ?? '');
            $confirm  = (string)($_POST['confirm_password'] ?? '');

            if (!$password || !$confirm) {
                $_SESSION['flash_message'] = 'Veuillez remplir tous les champs.';
                $this->redirect('index.php?view=reset-password&token=' . urlencode($token));
            }

            if ($password !== $confirm) {
                $_SESSION['flash_message'] = 'Les mots de passe ne correspondent pas.';
                $this->redirect('index.php?view=reset-password&token=' . urlencode($token));
            }

            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt   = $this->pdo->prepare(
                'UPDATE utilisateurs SET mot_de_passe = :pwd, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = :token'
            );
            $stmt->execute([':pwd' => $hashed, ':token' => $token]);

            $_SESSION['flash_message'] = 'Mot de passe réinitialisé. Vous pouvez vous connecter.';
            $this->redirect('index.php?view=login');
        }

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        include __DIR__ . '/../views/auth/reset-password.php';
        exit;
    }

    // =========================================================================
    //  PROFIL UTILISATEUR
    // =========================================================================

    // =========================================================================
    //  ADMIN — GESTION UTILISATEURS
    // =========================================================================

    private function adminUsers(): void
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $this->redirect('index.php');
        }

        // ── POST actions ────────────────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_user'])) {
                $nom      = trim((string)($_POST['nom'] ?? ''));
                $email    = strtolower(trim((string)($_POST['email'] ?? '')));
                $password = (string)($_POST['password'] ?? '');
                $role     = (string)($_POST['role'] ?? 'user');

                if ($nom && $email && $password) {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt   = $this->pdo->prepare(
                        'INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (:nom, :email, :pwd, :role)'
                    );
                    if ($stmt->execute([':nom' => $nom, ':email' => $email, ':pwd' => $hashed, ':role' => $role])) {
                        $_SESSION['flash_message'] = 'Utilisateur ajouté.';
                    } else {
                        $_SESSION['flash_message'] = "Erreur lors de l'ajout.";
                    }
                } else {
                    $_SESSION['flash_message'] = 'Tous les champs sont requis.';
                }
                $this->redirect('index.php?controller=user&action=adminUsers');
            }

            if (isset($_POST['edit_user'])) {
                $id       = (int)($_POST['user_id'] ?? 0);
                $nom      = trim((string)($_POST['nom'] ?? ''));
                $email    = strtolower(trim((string)($_POST['email'] ?? '')));
                $role     = (string)($_POST['role'] ?? 'user');
                $password = (string)($_POST['password'] ?? '');

                if ($nom && $email && $role) {
                    if ($password) {
                        $hashed = password_hash($password, PASSWORD_BCRYPT);
                        $stmt   = $this->pdo->prepare(
                            'UPDATE utilisateurs SET nom=:nom, email=:email, role=:role, mot_de_passe=:pwd WHERE id_utilisateur=:id'
                        );
                        $stmt->execute([':nom' => $nom, ':email' => $email, ':role' => $role, ':pwd' => $hashed, ':id' => $id]);
                    } else {
                        $stmt = $this->pdo->prepare(
                            'UPDATE utilisateurs SET nom=:nom, email=:email, role=:role WHERE id_utilisateur=:id'
                        );
                        $stmt->execute([':nom' => $nom, ':email' => $email, ':role' => $role, ':id' => $id]);
                    }
                    $_SESSION['flash_message'] = 'Utilisateur modifié.';
                } else {
                    $_SESSION['flash_message'] = 'Champs requis manquants.';
                }
                $this->redirect('index.php?controller=user&action=adminUsers');
            }
        }

        // ── GET delete ───────────────────────────────────────────────────────
        if (isset($_GET['delete_user'])) {
            $id   = (int)$_GET['delete_user'];
            $stmt = $this->pdo->prepare('DELETE FROM utilisateurs WHERE id_utilisateur = :id');
            $stmt->execute([':id' => $id]);
            $_SESSION['flash_message'] = 'Utilisateur supprimé.';
            $this->redirect('index.php?controller=user&action=adminUsers');
        }

        $stmt  = $this->pdo->query("
            SELECT u.id_utilisateur AS id, u.nom, u.email, u.role,
                   v.id_visage,
                   v.created_at AS face_created_at,
                   CASE WHEN v.id_visage IS NULL THEN 0 ELSE 1 END AS has_face
            FROM utilisateurs u
            LEFT JOIN visages_utilisateurs v ON v.id_utilisateur = u.id_utilisateur
            ORDER BY u.id_utilisateur DESC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        include __DIR__ . '/../views/admin/users.php';
        exit;
    }

    // =========================================================================
    //  HELPERS — DB
    // =========================================================================

    private function getFaceEncoding(int $userId): string|false
    {
        $stmt = $this->pdo->prepare('SELECT face_encoding FROM visages_utilisateurs WHERE id_utilisateur = :uid LIMIT 1');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string)$row['face_encoding'] : false;
    }

    private function generateResetToken(string $email): string|false
    {
        $stmt = $this->pdo->prepare('SELECT id_utilisateur FROM utilisateurs WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        if (!$stmt->fetch()) {
            return false;
        }

        $token  = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email'
        );
        $stmt->execute([':token' => $token, ':expiry' => $expiry, ':email' => $email]);

        return $token;
    }

    private function logAuth(int $userId, string $type = 'email'): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO authentifications (id_utilisateur, type_connexion, derniere_connexion) VALUES (:uid, :type, NOW())'
            );
            $stmt->execute([':uid' => $userId, ':type' => $type]);
        } catch (\Exception $e) {
            // Auth log is non-critical; swallow errors silently.
        }
    }

    private function saveEmailLog(string $email, string $subject, string $message, string $resetLink): void
    {
        $dir = __DIR__ . '/../storage/emails';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename   = $dir . '/' . date('Y-m-d_H-i-s') . '_' . str_replace('@', '_', $email) . '.log';
        $logContent = "TO: {$email}\nSUBJECT: {$subject}\nDATE: " . date('Y-m-d H:i:s')
            . "\nRESET_LINK: {$resetLink}\n\n--- MESSAGE ---\n{$message}\n--- END ---\n";

        file_put_contents($filename, $logContent);
    }

    // =========================================================================
    //  HELPERS — CALCULS
    // =========================================================================

    private function calculerImc(float $poids, float $taille): float
    {
        $tailleM = $taille / 100;
        return round($poids / ($tailleM * $tailleM), 2);
    }

    private function calculerCalories(int $age, string $sexe, string $objectif): int
    {
        $base = $sexe === 'femme' ? 1400 : 1600;

        $multiplicateur = match ($objectif) {
            'perte'  => 0.8,
            'muscle' => 1.2,
            default  => 1.0,
        };

        return (int)($base * $multiplicateur);
    }

    // =========================================================================
    //  HELPERS — SESSION / REDIRECT
    // =========================================================================

    private function setUserSession(array $row): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'         => (int)$row['id_utilisateur'],
            'nom'        => $row['nom'],
            'prenom'     => $row['prenom'] ?? null,
            'email'      => $row['email'],
            'role'       => $row['role'],
            'poids'      => $row['poids'] ?? null,
            'taille'     => $row['taille'] ?? null,
            'imc'        => $row['imc'] ?? null,
            'calories'   => $row['calories'] ?? null,
            'age'        => $row['age'] ?? null,
            'sexe'       => $row['sexe'] ?? null,
            'objectif'   => $row['objectif'] ?? null,
            'specialite' => $row['specialite'] ?? null,
            'bio'        => $row['bio'] ?? null,
        ];
    }

    private function redirectByRole(string $role): void
    {
        $this->redirect('index.php');
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    private function jsonResponse(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}

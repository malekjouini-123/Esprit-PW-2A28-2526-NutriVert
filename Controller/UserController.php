<?php
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Model/EmailService.php';
require_once __DIR__ . '/../Model/FaceRecognitionService.php';
require_once __DIR__ . '/../Database.php';

class UserController {
    private $userModel;
    private $faceRecognition;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->faceRecognition = new FaceRecognitionService();
    }

    public function index() {
        include __DIR__ . '/../View/FrontOffice/index.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = $this->userModel->findByEmail($email);
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id_utilisateur'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $this->userModel->logAuth($user['id_utilisateur']);
                header('Location: index.php?action=profile');
                exit;
            } else {
                $_SESSION['error'] = "Email ou mot de passe incorrect.";
                header('Location: index.php?action=login');
                exit;
            }
        }
        include __DIR__ . '/../View/FrontOffice/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($password !== $confirm) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
                header('Location: index.php?action=register');
                exit;
            }
            if ($this->userModel->findByEmail($email)) {
                $_SESSION['error'] = "Cet email est déjà utilisé.";
                header('Location: index.php?action=register');
                exit;
            }
            if ($this->userModel->create($nom, $prenom, $email, $password)) {
                $_SESSION['success'] = "Inscription réussie. Connectez-vous.";
                header('Location: index.php?action=login');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription.";
                header('Location: index.php?action=register');
                exit;
            }
        }
        include __DIR__ . '/../View/FrontOffice/register.php';
    }

    public function profile() {
        if (!isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }
        $userId = $_SESSION['user_id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $poids = $_POST['poids'] ?? null;
            $taille = $_POST['taille'] ?? null;
            $objectif = $_POST['objectif'] ?? '';
            $regime = $_POST['regime'] ?? '';
            $this->userModel->updateProfile($userId, $poids, $taille, $objectif, $regime);
            $_SESSION['success'] = "Profil mis à jour !";
            header('Location: index.php?action=profile');
            exit;
        }
        $user = $this->userModel->findById($userId);
        $hasFace = $this->userModel->getFaceEncoding($userId) !== false;
        include __DIR__ . '/../View/FrontOffice/profile.php';
    }

    /** Register the connected user's Face ID from an uploaded image. */
    public function addFace() {
        $this->saveFaceFromCapture("Face ID ajoute avec succes.");
    }

    /** Replace the connected user's Face ID from an uploaded image. */
    public function updateFace() {
        $this->saveFaceFromCapture("Face ID mis a jour avec succes.");
    }

    /** Delete the connected user's Face ID encoding. */
    public function deleteFace() {
        if (!isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=profile');
            exit;
        }

        $this->userModel->deleteFaceEncoding($_SESSION['user_id']);
        $_SESSION['success'] = "Face ID supprime.";
        header('Location: index.php?action=profile');
        exit;
    }

    /** Validate a webcam capture, call Python, and persist the face encoding. */
    private function saveFaceFromCapture($successMessage) {
        if (!isLoggedIn()) {
            $this->faceJsonResponse(['success' => false, 'error' => 'Connexion requise.'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->faceJsonResponse(['success' => false, 'error' => 'Methode non autorisee.'], 405);
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $capture = $payload['face_image'] ?? '';
        if (!$capture) {
            $this->faceJsonResponse(['success' => false, 'error' => 'Capture webcam manquante.'], 422);
        }

        $result = $this->faceRecognition->encodeCapturedImage($capture, $_SESSION['user_id']);

        if (!$result['success'] || empty($result['data']['encoding'])) {
            $this->faceJsonResponse([
                'success' => false,
                'error' => $result['data']['error'] ?? "Impossible d'analyser cette image."
            ], 422);
        }

        $encoding = json_encode($result['data']['encoding']);
        if (!$this->userModel->saveFaceEncoding($_SESSION['user_id'], $encoding)) {
            $this->faceJsonResponse(['success' => false, 'error' => "Erreur lors de l'enregistrement du Face ID."], 500);
        }

        $this->faceJsonResponse(['success' => true, 'message' => $successMessage]);
    }

    /** Return a JSON response for webcam Face ID profile actions. */
    private function faceJsonResponse(array $payload, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            if (!$email) {
                $_SESSION['error'] = "Veuillez entrer votre email.";
                header('Location: index.php?action=forgot-password');
                exit;
            }
            
            $token = $this->userModel->generateResetToken($email);
            
            if ($token) {
                // Créer le lien de réinitialisation
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/NutriVertMVC/public/index.php?action=reset-password&token=" . $token;
                
                // Find user for personalization
                $user = $this->userModel->findByEmail($email);
                $userName = $user ? $user['prenom'] . ' ' . $user['nom'] : 'Utilisateur';
                
                // Email content HTML
                $subject = "Réinitialiser votre mot de passe - NutriVert";
                $message = "
                <html>
                    <body style='font-family: Arial, sans-serif; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #2e7d32;'>Réinitialisation de Mot de Passe</h2>
                            <p>Bonjour " . htmlspecialchars($userName) . ",</p>
                            <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte NutriVert.</p>
                            <p>Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe:</p>
                            <p style='text-align: center; margin: 2rem 0;'>
                                <a href='" . htmlspecialchars($resetLink) . "' 
                                   style='background-color: #2e7d32; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                    Réinitialiser Mot de Passe
                                </a>
                            </p>
                            <p style='color: #666;'>
                                <strong>Ou copiez ce lien:</strong><br>
                                <small>" . htmlspecialchars($resetLink) . "</small>
                            </p>
                            <hr style='border: none; border-top: 1px solid #ddd; margin: 2rem 0;'>
                            <p style='color: #999; font-size: 0.9em;'>
                                <strong>Important:</strong> Ce lien expire dans 1 heure.<br>
                                Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                            </p>
                            <p style='color: #999; font-size: 0.85em;'>
                                © NutriVert - " . date('Y') . "
                            </p>
                        </div>
                    </body>
                </html>";
                
                // Save email to file (development mode/logging)
                $this->saveEmailLog($email, $subject, strip_tags($message), $resetLink);
                
                // Send email using EmailService
                try {
                    $emailService = new EmailService();
                    $emailSent = $emailService->send($email, $subject, $message);
                } catch (Exception $e) {
                    error_log('Email Service Error: ' . $e->getMessage());
                    $emailSent = false;
                }
                
                // Prepare data for confirmation page (ne pas exposer le lien de reset en production)
                $confirmationData = [
                    'email' => $email,
                    'emailSent' => $emailSent
                ];
                
                // Include confirmation view with data
                extract($confirmationData);
                include __DIR__ . '/../View/FrontOffice/email-confirmation.php';
                exit;
            } else {
                $_SESSION['error'] = "Cet email n'existe pas dans notre système.";
                header('Location: index.php?action=forgot-password');
                exit;
            }
        }
        
        include __DIR__ . '/../View/FrontOffice/forgot-password.php';
    }

    private function saveEmailLog($email, $subject, $message, $resetLink) {
        $storageDir = __DIR__ . '/../storage/emails';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        $filename = $storageDir . '/' . date('Y-m-d_H-i-s') . '_' . str_replace('@', '_', $email) . '.log';
        
        $logContent = "TO: $email\n";
        $logContent .= "SUBJECT: $subject\n";
        $logContent .= "DATE: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "RESET_LINK: $resetLink\n";
        $logContent .= "\n--- MESSAGE ---\n";
        $logContent .= $message . "\n";
        $logContent .= "\n--- END ---\n";
        
        file_put_contents($filename, $logContent);
    }

    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (!$token) {
            $_SESSION['error'] = "Lien de réinitialisation invalide.";
            header('Location: index.php?action=login');
            exit;
        }
        
        $user = $this->userModel->findByResetToken($token);
        
        if (!$user) {
            $_SESSION['error'] = "Lien de réinitialisation expiré ou invalide.";
            header('Location: index.php?action=login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            
            if (!$password || !$confirm) {
                $_SESSION['error'] = "Veuillez remplir tous les champs.";
                header('Location: index.php?action=reset-password&token=' . $token);
                exit;
            }
            
            if ($password !== $confirm) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
                header('Location: index.php?action=reset-password&token=' . $token);
                exit;
            }
            
            if ($this->userModel->resetPassword($token, $password)) {
                $_SESSION['success'] = "Votre mot de passe a été réinitialisé. Connectez-vous.";
                header('Location: index.php?action=login');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la réinitialisation.";
                header('Location: index.php?action=reset-password&token=' . $token);
                exit;
            }
        }
        
        include __DIR__ . '/../View/FrontOffice/reset-password.php';
    }

    public function adminDashboard() {
        if (!isAdmin()) {
            header('Location: index.php');
            exit;
        }

        // Gestion des actions POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ajout d'utilisateur
            if (isset($_POST['add_user'])) {
                $nom = trim($_POST['nom'] ?? '');
                $prenom = trim($_POST['prenom'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? 'client';

                if ($nom && $prenom && $email && $password) {
                    if ($this->userModel->create($nom, $prenom, $email, $password, $role)) {
                        $_SESSION['success'] = "Utilisateur ajouté.";
                    } else {
                        $_SESSION['error'] = "Erreur lors de l'ajout.";
                    }
                } else {
                    $_SESSION['error'] = "Tous les champs sont requis.";
                }
                header('Location: index.php?action=admin');
                exit;
            }

            // Modification d'utilisateur
            if (isset($_POST['edit_user'])) {
                $id = (int)$_POST['user_id'];
                $nom = trim($_POST['nom'] ?? '');
                $prenom = trim($_POST['prenom'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $role = $_POST['role'] ?? 'client';
                $password = $_POST['password'] ?? '';

                if ($nom && $prenom && $email && $role) {
                    if ($this->userModel->updateUser($id, $nom, $prenom, $email, $role, $password ?: null)) {
                        $_SESSION['success'] = "Utilisateur modifié.";
                    } else {
                        $_SESSION['error'] = "Erreur lors de la modification.";
                    }
                } else {
                    $_SESSION['error'] = "Tous les champs sont requis.";
                }
                header('Location: index.php?action=admin');
                exit;
            }
        }

        // Gestion des actions GET
        if (isset($_GET['delete_user'])) {
            $id = (int)$_GET['delete_user'];
            if ($this->userModel->delete($id)) {
                $_SESSION['success'] = "Utilisateur supprimé.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression.";
            }
            header('Location: index.php?action=admin');
            exit;
        }

        $users = $this->userModel->getAllWithFaceStatus();
        include __DIR__ . '/../View/Backoffice/dashboard.php';
    }
}

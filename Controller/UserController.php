<?php
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Database.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
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
        include __DIR__ . '/../View/FrontOffice/profile.php';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    public function adminDashboard() {
        if (!isAdmin()) {
            header('Location: index.php');
            exit;
        }
        include __DIR__ . '/../View/Backoffice/dashboard.php';
    }
}
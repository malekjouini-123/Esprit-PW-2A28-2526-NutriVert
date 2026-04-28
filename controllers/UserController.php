<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if (class_exists('UserController')) return;

class UserController
{
    private PDO $pdo;

    public function __construct() { $this->pdo = getDB(); }
    public function __destruct() {}

    public function handle(string $action): void
    {
        switch ($action) {
            case 'doRegister': $this->register();     break;
            case 'doLogin':    $this->login();        break;
            case 'logout':     $this->logout();       break;
            default:           $this->redirect('index.php');
        }
    }

    // =========================================================================
    //  INSCRIPTION
    // =========================================================================

    private function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('views/front/register.php');
        }

        $nom       = trim((string)($_POST['nom'] ?? ''));
        $email     = strtolower(trim((string)($_POST['email'] ?? '')));
        $password  = (string)($_POST['password'] ?? '');
        $role      = strtolower(trim((string)($_POST['role'] ?? 'user')));
        $poids     = isset($_POST['poids']) && $_POST['poids'] !== '' ? (float)$_POST['poids'] : null;
        $taille    = isset($_POST['taille']) && $_POST['taille'] !== '' ? (float)$_POST['taille'] : null;
        $age       = isset($_POST['age']) && $_POST['age'] !== '' ? (int)$_POST['age'] : null;
        $sexe      = (string)($_POST['sexe'] ?? 'homme');
        $objectif  = (string)($_POST['objectif'] ?? 'maintien');
        $specialite= trim((string)($_POST['specialite'] ?? ''));
        $bio       = trim((string)($_POST['bio'] ?? ''));

        $errors = [];

        // Validation basique
        if ($nom === '')              $errors[] = 'Le nom est requis.';
        if ($email === '')            $errors[] = 'L\'email est requis.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        if ($password === '' || strlen($password) < 4) $errors[] = 'Le mot de passe doit faire au moins 4 caractères.';
        if (!in_array($role, User::ALLOWED_ROLES, true)) $errors[] = 'Rôle invalide.';

        // Vérifier que l'email n'existe pas
        if (empty($errors)) {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Cet email est déjà enregistré.';
            }
        }

        // Validations spécifiques au rôle
        if ($role === 'user') {
            if ($poids === null || $poids <= 0) $errors[] = 'Le poids est requis pour les utilisateurs.';
            if ($taille === null || $taille <= 0) $errors[] = 'La taille est requise pour les utilisateurs.';
            if ($age === null || $age <= 0) $errors[] = 'L\'âge est requis pour les utilisateurs.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_message'] = implode(' ', $errors);
            $this->redirect('index.php?view=register');
        }

        // Créer l'utilisateur
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $this->pdo->prepare('
                INSERT INTO users (nom, email, password, role, poids, taille, age, sexe, objectif, imc, calories, specialite, bio)
                VALUES (:nom, :email, :password, :role, :poids, :taille, :age, :sexe, :objectif, :imc, :calories, :specialite, :bio)
            ');

            $imc = null;
            $calories = null;
            
            if ($role === 'user' && $poids && $taille) {
                $imc = $this->calculerImc($poids, $taille);
                $calories = $this->calculerCalories($age ?? 25, $sexe, $objectif);
            }

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
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Erreur lors de l\'inscription : ' . $e->getMessage();
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
        if ($email === '')    $errors[] = 'L\'email est requis.';
        if ($password === '') $errors[] = 'Le mot de passe est requis.';

        if (!empty($errors)) {
            $_SESSION['flash_message'] = implode(' ', $errors);
            $this->redirect('index.php?view=login');
        }

        // Chercher l'utilisateur
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($password, $row['password'])) {
            $_SESSION['flash_message'] = 'Email ou mot de passe incorrect.';
            $this->redirect('index.php?view=login');
        }

        // Connexion réussie
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'        => (int)$row['id'],
            'nom'       => $row['nom'],
            'email'     => $row['email'],
            'role'      => $row['role'],
            'poids'     => $row['poids'],
            'taille'    => $row['taille'],
            'imc'       => $row['imc'],
            'calories'  => $row['calories'],
            'age'       => $row['age'],
            'sexe'      => $row['sexe'],
            'objectif'  => $row['objectif'],
            'specialite'=> $row['specialite'],
            'bio'       => $row['bio'],
        ];

        // Redirection selon le rôle
        if ($row['role'] === 'coach') {
            $this->redirect('index.php?controller=coaching&action=index');
        } elseif ($row['role'] === 'admin') {
            $this->redirect('index.php?controller=dashboard&action=index');
        } else {
            // User → Dashboard utilisateur
            $this->redirect('index.php?controller=user_dashboard&action=index');
        }
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
    //  UTILITAIRES
    // =========================================================================

    private function calculerImc(float $poids, float $taille): float
    {
        // taille en cm, donc convertir en m
        $tailleM = $taille / 100;
        return round($poids / ($tailleM * $tailleM), 2);
    }

    private function calculerCalories(int $age, string $sexe, string $objectif): int
    {
        // Formule de Harris-Benedict (simplifiée)
        // Utilisateur moyen 70kg, 175cm
        $base = $sexe === 'femme' ? 1400 : 1600;

        // Ajustement objectif
        $multiplicateur = match ($objectif) {
            'perte'  => 0.8,
            'muscle' => 1.2,
            default  => 1.0,  // maintien
        };

        return (int)($base * $multiplicateur);
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}

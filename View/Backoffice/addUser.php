<?php
// Vérification admin
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Inclure les modèles
require_once __DIR__ . '/../../Model/UserModel.php';
require_once __DIR__ . '/../../Model/CoachModel.php';
require_once __DIR__ . '/../../Model/FournisseurModel.php';
require_once __DIR__ . '/../../Model/RecetteModel.php';
require_once __DIR__ . '/../../Model/EventModel.php';

$userModel = new UserModel();
$coachModel = new CoachModel();
$fournisseurModel = new FournisseurModel();
$recetteModel = new RecetteModel();
$eventModel = new EventModel();

// Gestion des actions POST
// Utilisateurs : ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client';
    if ($nom && $prenom && $email && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $hashed, $role]);
        $_SESSION['success'] = "Utilisateur ajouté.";
    } else {
        $_SESSION['error'] = "Tous les champs sont requis.";
    }
    header('Location: index.php?action=admin');
    exit;
}

// Utilisateurs : suppression
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    $userModel->delete($id);
    $_SESSION['success'] = "Utilisateur supprimé.";
    header('Location: index.php?action=admin');
    exit;
}

// Coachs : ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coach'])) {
    $nom = $_POST['coach_nom'] ?? '';
    $specialite = $_POST['coach_specialite'] ?? '';
    if ($nom) {
        $coachModel->create($nom, $specialite);
        $_SESSION['success'] = "Coach ajouté.";
    }
    header('Location: index.php?action=admin');
    exit;
}

// Coachs : suppression
if (isset($_GET['delete_coach'])) {
    $id = (int)$_GET['delete_coach'];
    $coachModel->delete($id);
    $_SESSION['success'] = "Coach supprimé.";
    header('Location: index.php?action=admin');
    exit;
}

// Fournisseurs : ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_fournisseur'])) {
    $nom = $_POST['fournisseur_nom'] ?? '';
    $email = $_POST['fournisseur_email'] ?? '';
    $type = $_POST['fournisseur_type'] ?? '';
    if ($nom) {
        $fournisseurModel->create($nom, $email, $type);
        $_SESSION['success'] = "Fournisseur ajouté.";
    }
    header('Location: index.php?action=admin');
    exit;
}

// Fournisseurs : suppression
if (isset($_GET['delete_fournisseur'])) {
    $id = (int)$_GET['delete_fournisseur'];
    $fournisseurModel->delete($id);
    $_SESSION['success'] = "Fournisseur supprimé.";
    header('Location: index.php?action=admin');
    exit;
}

// Recettes : ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recette'])) {
    $titre = $_POST['recette_titre'] ?? '';
    $calories = $_POST['recette_calories'] ?? 0;
    $ingredients =
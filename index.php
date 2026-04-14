<?php
/**
 * Point d’entrée Front Office NutriVert (MVC).
 */
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Controller/FrontController.php';

$controller = new FrontController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login_suivi':
        $controller->login();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'save_inscription':
        $controller->saveInscription();
        break;
    case 'update_participant':
        $controller->saveInscription(); // Utilise la même logique
        break;
    case 'delete_participant':
        $id = (int)($_GET['id'] ?? 0);
        Inscription::delete($id);
        header('Location: index.php?sub=participants&success=deleted');
        exit;
    case 'save_event':
        // Logique temporaire pour le front si besoin
        header('Location: index.php');
        exit;
    case 'save_category':
        // Logique temporaire pour le front si besoin
        header('Location: index.php');
        exit;
    case 'delete_event':
        $id = (int)($_GET['id'] ?? 0);
        Evenement::delete($id);
        header('Location: index.php?sub=events&success=deleted');
        exit;
    case 'delete_category':
        $id = (int)($_GET['id'] ?? 0);
        Category::delete($id);
        header('Location: index.php?sub=categories&success=deleted');
        exit;
    default:
        $controller->render();
        break;
}

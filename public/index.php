<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../app/controllers/BaseController.php';
require_once __DIR__ . '/../app/models/Recette.php';
require_once __DIR__ . '/../app/models/Instruction.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/RecetteController.php';

$pdo = Database::getConnection();
$homeController = new HomeController($pdo);
$recetteController = new RecetteController($pdo);

$page = $_GET['page'] ?? 'front_home';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

switch ($page) {
    case 'front_home':
        $homeController->index();
        break;
    case 'front_recette_detail':
        $homeController->recetteDetail($id);
        break;

    case 'back_dashboard':
        $viewPageTitle = 'BackOffice | Dashboard';
        $pageTitle = $viewPageTitle;
        require __DIR__ . '/../app/views/back/dashboard.php';
        break;

    case 'back_recettes_full_edit':
        $recetteController->indexFullEdit();
        break;
    case 'back_recette_create_full':
        $recetteController->createFull();
        break;
    case 'back_recette_store_full':
        $recetteController->storeFull();
        break;
    case 'back_recette_edit_full':
        $recetteController->editFull($id);
        break;
    case 'back_recette_update_full':
        $recetteController->updateFull($id);
        break;
    case 'back_recette_delete':
        $recetteController->delete($id);
        break;

    default:
        $homeController->index();
        break;
}

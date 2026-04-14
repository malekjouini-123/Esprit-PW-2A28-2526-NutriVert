<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../app/controllers/BaseController.php';
require_once __DIR__ . '/../app/models/Recette.php';
require_once __DIR__ . '/../app/models/Instruction.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/RecetteController.php';
require_once __DIR__ . '/../app/controllers/InstructionController.php';

$pdo = Database::getConnection();
$recetteModel = new Recette($pdo);
$instructionModel = new Instruction($pdo);

$homeController = new HomeController($recetteModel, $instructionModel);
$recetteController = new RecetteController($recetteModel, $instructionModel, $pdo);
$instructionController = new InstructionController($instructionModel, $recetteModel);

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

    case 'back_recettes':
        $recetteController->index();
        break;
    case 'back_recette_create':
        $recetteController->create();
        break;
    case 'back_recette_store':
        $recetteController->store();
        break;
    case 'back_recette_edit':
        $recetteController->edit($id);
        break;
    case 'back_recette_update':
        $recetteController->update($id);
        break;
    case 'back_recette_delete':
        $recetteController->delete($id);
        break;
    case 'back_recette_create_full':
        $recetteController->createFull();
        break;
    case 'back_recette_store_full':
        $recetteController->storeFull();
        break;

    case 'back_instructions':
        $instructionController->index();
        break;
    case 'back_instruction_create':
        $instructionController->create();
        break;
    case 'back_instruction_store':
        $instructionController->store();
        break;
    case 'back_instruction_edit':
        $instructionController->edit($id);
        break;
    case 'back_instruction_update':
        $instructionController->update($id);
        break;
    case 'back_instruction_delete':
        $instructionController->delete($id);
        break;

    default:
        $homeController->index();
        break;
}

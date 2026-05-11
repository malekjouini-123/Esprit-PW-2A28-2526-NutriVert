<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
init_session();

require_once __DIR__ . '/Controller/FrontController.php';

$action = $_GET['action'] ?? 'index';
$controller = new FrontController();

switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'event':
        $id = (int)($_GET['id'] ?? 0);
        $controller->showEvent($id);
        break;
    case 'register':
        $controller->register();
        break;
    case 'add_event':
        $controller->addEvent();
        break;
    case 'add_category':
        $controller->addCategory();
        break;
    case 'add_participant':
        $controller->addParticipant();
        break;
    case 'participants':
        $controller->listParticipants();
        break;
    case 'categories':
        $controller->listCategories();
        break;
    case 'events':
        $controller->listEvents();
        break;
    case 'export_pdf':
        $controller->exportPDF();
        break;
    case 'recommendations':
        $controller->recommendations();
        break;
    case 'create_recommendation':
        $controller->createPersonalRecommendation();
        break;
    case 'my_recommendations':
        $controller->myRecommendations();
        break;
    case 'delete_recommendation':
        $id = (int)($_GET['id'] ?? 0);
        $controller->deleteRecommendation($id);
        break;
    default:
        $controller->index();
        break;
}

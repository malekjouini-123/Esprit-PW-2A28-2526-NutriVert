<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
init_session();

require_once __DIR__ . '/Controller/BackController.php';

$action = $_GET['action'] ?? 'events';
$controller = new BackController();

switch ($action) {
    case 'events':
        $controller->listEvents();
        break;
    case 'show_event':
        $id = (int)($_GET['id'] ?? 0);
        $controller->showEvent($id);
        break;
    case 'add_event':
        $controller->addEvent();
        break;
    case 'edit_event':
        $id = (int)($_GET['id'] ?? 0);
        $controller->editEvent($id);
        break;
    case 'toggle_event_publish':
        $id = (int)($_GET['id'] ?? 0);
        $controller->toggleEventPublish($id);
        break;
    case 'delete_event':
        $id = (int)($_GET['id'] ?? 0);
        $controller->deleteEvent($id);
        break;
    case 'categories':
        $controller->listCategories();
        break;
    case 'add_category':
        $controller->addCategory();
        break;
    case 'show_category':
        $id = (int)($_GET['id'] ?? 0);
        $controller->showCategory($id);
        break;
    case 'edit_category':
        $id = (int)($_GET['id'] ?? 0);
        $controller->editCategory($id);
        break;
    case 'toggle_category_publish':
        $id = (int)($_GET['id'] ?? 0);
        $controller->toggleCategoryPublish($id);
        break;
    case 'delete_category':
        $id = (int)($_GET['id'] ?? 0);
        $controller->deleteCategory($id);
        break;
    case 'participants':
        $controller->listParticipants();
        break;
    case 'show_participant':
        $id = (int)($_GET['id'] ?? 0);
        $controller->showParticipant($id);
        break;
    case 'edit_participant':
        $id = (int)($_GET['id'] ?? 0);
        $controller->editParticipant($id);
        break;
    case 'delete_participant':
        $id = (int)($_GET['id'] ?? 0);
        $controller->deleteParticipant($id);
        break;
    case 'email_participant':
        $id = (int)($_GET['id'] ?? 0);
        $controller->emailParticipant($id);
        break;
    case 'recommendations':
        $controller->listRecommendations();
        break;
    case 'show_recommendation':
        $id = (int)($_GET['id'] ?? 0);
        $controller->showRecommendation($id);
        break;
    case 'delete_recommendation':
        $id = (int)($_GET['id'] ?? 0);
        $controller->deleteRecommendation($id);
        break;
    default:
        $controller->listEvents();
        break;
}

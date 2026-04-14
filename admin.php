<?php
/**
 * Point d’entrée Back Office NutriVert (MVC).
 */
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Controller/BackController.php';

$controller = new BackController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'save_event':
        $controller->saveEvent();
        break;
    case 'delete_event':
        $controller->deleteEvent();
        break;
    case 'save_category':
        $controller->saveCategory();
        break;
    case 'delete_category':
        $controller->deleteCategory();
        break;
    case 'save_inscription': // Gestion admin des inscriptions
        $controller->saveParticipant();
        break;
    case 'update_participant':
        $controller->saveParticipant();
        break;
    case 'delete_participant':
        $controller->deleteParticipant();
        break;
    default:
        $controller->render();
        break;
}

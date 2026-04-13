<?php
declare(strict_types=1);

require_once __DIR__ . '/controller/AdminEvenementController.php';

$controller = new AdminEvenementController();
$action = $_GET['action'] ?? 'index';
$id = (int)($_GET['id'] ?? 0);

switch ($action) {
    case 'add':
        $controller->form();
        break;
    case 'edit':
        $controller->form($id);
        break;
    case 'save':
        $controller->save();
        break;
    case 'delete':
        $controller->delete($id);
        break;
    default:
        $controller->index();
        break;
}

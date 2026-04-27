<?php
session_start();
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Controller/UserController.php';

$controller = new UserController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'register':
        $controller->register();
        break;
    case 'profile':
        $controller->profile();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'admin':
        $controller->adminDashboard();
        break;
    default:
        $controller->index();
}
?>
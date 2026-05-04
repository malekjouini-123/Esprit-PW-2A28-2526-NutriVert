<?php
session_start();
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Controller/UserController.php';
require_once __DIR__ . '/../Controller/AuthController.php';
require_once __DIR__ . '/../Controller/AdminController.php';
require_once __DIR__ . '/../Controller/ChatController.php';

$controller = new UserController();
$authController = new AuthController();
$adminController = new AdminController();
$chatController = new ChatController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'face-login':
        $authController->faceLogin();
        break;
    case 'register':
        $controller->register();
        break;
    case 'forgot-password':
        $controller->forgotPassword();
        break;
    case 'reset-password':
        $controller->resetPassword();
        break;
    case 'profile':
        $controller->profile();
        break;
    case 'add-face':
        $controller->addFace();
        break;
    case 'update-face':
        $controller->updateFace();
        break;
    case 'delete-face':
        $controller->deleteFace();
        break;
    case 'chat':
        $chatController->index();
        break;
    case 'chat-ask':
        $chatController->ask();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'admin':
        $controller->adminDashboard();
        break;
    case 'admin-faces':
        $adminController->listUsersWithFaces();
        break;
    case 'admin-delete-face':
        $adminController->deleteUserFace($_GET['user_id'] ?? 0);
        break;
    case 'admin-view-face':
        $adminController->viewUserFace($_GET['user_id'] ?? 0);
        break;
    case 'admin-chats':
        $chatController->adminChats();
        break;
    default:
        $controller->index();
}
?>

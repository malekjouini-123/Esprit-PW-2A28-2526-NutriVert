<?php
/**
 * Point d’entrée front NutriVert (MVC).
 * URL : http://localhost/projet_web/index.php
 */
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Controller/NurtvieController.php';
$controller = new NurtvieController();

$action = $_GET['action'] ?? '';

if ($action === 'save_inscription') {
    $controller->handleInscription();
} elseif ($action === 'update_participant') {
    $controller->handleParticipantUpdate();
} elseif ($action === 'delete_participant') {
    $controller->handleParticipantDelete();
} elseif ($action === 'save_event') {
    $controller->handleEventSave();
} elseif ($action === 'delete_event') {
    $controller->handleEventDelete();
} elseif ($action === 'save_category') {
    $controller->handleCategorySave();
} elseif ($action === 'delete_category') {
    $controller->handleCategoryDelete();
} elseif ($action === 'login_suivi') {
    $controller->handleLogin();
} elseif ($action === 'logout') {
    $controller->handleLogout();
} else {
    $controller->renderEvenements();
}

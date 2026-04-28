<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlers/userscontroller.php';

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$userId = intval($_GET['id']);
$usersController = new UsersController();

try {
    $usersController->deleteUser($userId);
    header('Location: users.php?success=User deleted successfully');
} catch (Exception $e) {
    header('Location: users.php?error=' . urlencode($e->getMessage()));
}
?>

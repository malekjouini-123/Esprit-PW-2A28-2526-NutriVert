<?php
require_once 'config.php';
require_once 'controlers/userscontroller.php';

$ctrl = new UsersController();
try {
    $users = $ctrl->getAllUsers();
    echo "Found " . count($users) . " users\n";
    foreach ($users as $u) {
        echo "- " . $u->getNomUtilisateur() . " (ID:" . $u->getIdUser() . ")\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

<?php
// Vérification admin – inclus dans chaque page backoffice
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}
?>
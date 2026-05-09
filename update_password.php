<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nutrivert;charset=utf8', 'root', '');
    $hash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->execute([$hash, 'jean@nutrivert.fr']);
    echo 'Mot de passe mis à jour pour jean@nutrivert.fr' . PHP_EOL;
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . PHP_EOL;
}
?>
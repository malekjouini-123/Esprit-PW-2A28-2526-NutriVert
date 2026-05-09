<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nutrivert;charset=utf8', 'root', '');
    $stmt = $pdo->query('SELECT email, password FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo $user['email'] . ' - ' . $user['password'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . PHP_EOL;
}
?>
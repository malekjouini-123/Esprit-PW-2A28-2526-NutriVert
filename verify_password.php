<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nutrivert;charset=utf8', 'root', '');
    $stmt = $pdo->prepare('SELECT password FROM users WHERE email = ?');
    $stmt->execute(['jean@nutrivert.fr']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo 'Hash actuel: ' . $row['password'] . PHP_EOL;
        echo 'Vérification password123: ' . (password_verify('password123', $row['password']) ? 'OK' : 'ÉCHEC') . PHP_EOL;
    } else {
        echo 'Utilisateur non trouvé' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . PHP_EOL;
}
?>
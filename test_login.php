<?php
session_start();

try {
    $pdo = new PDO('mysql:host=localhost;dbname=nutrivert;charset=utf8', 'root', '');

    // Simuler la connexion
    $email = 'jean@nutrivert.fr';
    $password = 'password123';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($password, $row['password'])) {
        echo 'Échec de connexion' . PHP_EOL;
        exit;
    }

    // Connexion réussie
    $_SESSION['user'] = [
        'id'        => (int)$row['id'],
        'nom'       => $row['nom'],
        'email'     => $row['email'],
        'role'      => $row['role'],
        'poids'     => $row['poids'],
        'taille'    => $row['taille'],
        'imc'       => $row['imc'],
        'calories'  => $row['calories'],
        'age'       => $row['age'],
        'sexe'      => $row['sexe'],
        'objectif'  => $row['objectif'],
        'specialite'=> $row['specialite'],
        'bio'       => $row['bio'],
    ];

    echo 'Connexion réussie pour ' . $_SESSION['user']['nom'] . PHP_EOL;
    echo 'Redirection vers le dashboard...' . PHP_EOL;

    // Rediriger vers le dashboard
    header('Location: index.php?controller=user_dashboard&action=index');
    exit;

} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . PHP_EOL;
}
?>
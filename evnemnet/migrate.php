<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function migrate() {
    try {
        // Connexion sans base de données pour la créer si nécessaire
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sql = file_get_contents(__DIR__ . '/database.sql');
        
        // Exécution du script SQL
        $pdo->exec($sql);
        
        echo "Base de données initialisée avec succès !\n";
    } catch (PDOException $e) {
        die("Erreur lors de la migration : " . $e->getMessage() . "\n");
    }
}

migrate();

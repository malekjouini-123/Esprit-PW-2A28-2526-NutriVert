<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Model/Participant.php';
require_once __DIR__ . '/Model/Evenement.php';
require_once __DIR__ . '/Model/Category.php';
require_once __DIR__ . '/Model/PersonalRecommendation.php';
require_once __DIR__ . '/Controller/FrontController.php';
require_once __DIR__ . '/Controller/BackController.php';

echo "=== Test de chargement des classes ===\n";

// Test des modèles
try {
    $participant = new Participant();
    echo "✓ Modèle Participant chargé\n";
} catch (Exception $e) {
    echo "✗ Erreur Participant: " . $e->getMessage() . "\n";
}

try {
    $evenement = new Evenement();
    echo "✓ Modèle Evenement chargé\n";
} catch (Exception $e) {
    echo "✗ Erreur Evenement: " . $e->getMessage() . "\n";
}

try {
    $category = new Category();
    echo "✓ Modèle Category chargé\n";
} catch (Exception $e) {
    echo "✗ Erreur Category: " . $e->getMessage() . "\n";
}

try {
    $recommendation = new PersonalRecommendation();
    echo "✓ Modèle PersonalRecommendation chargé\n";
} catch (Exception $e) {
    echo "✗ Erreur PersonalRecommendation: " . $e->getMessage() . "\n";
}

// Test des contrôleurs
try {
    $frontController = new FrontController();
    echo "✓ FrontController chargé\n";
} catch (Exception $e) {
    echo "✗ Erreur FrontController: " . $e->getMessage() . "\n";
}

try {
    $backController = new BackController();
    echo "✓ BackController chargé\n";
} catch (Exception $e) {
    echo "✗ Erreur BackController: " . $e->getMessage() . "\n";
}

// Test connexion base de données
try {
    $pdo = get_pdo();
    echo "✓ Connexion base de données OK\n";
} catch (Exception $e) {
    echo "✗ Erreur base de données: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
echo "Si tous les ✓ sont affichés, le code est prêt à être exécuté.\n";
echo "Lancez XAMPP et allez sur http://localhost/projet pour voir le front-end.\n";
echo "Allez sur http://localhost/projet/admin.php pour voir le back-end.\n";
?>
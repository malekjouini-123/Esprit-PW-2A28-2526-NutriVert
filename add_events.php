<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = nv_pdo();
    
    // Check if they already exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM evenements WHERE titre LIKE '%Dégustation%' OR titre LIKE '%Sport%'");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $sql = "INSERT INTO evenements (titre, description, date_evenement, lieu, image_url) VALUES 
        ('Dégustation de Fruits Exotiques', 'Une explosion de saveurs tropicales.', '2026-07-05 16:00:00', 'Marseille', 'https://images.unsplash.com/photo-1514986888952-8cd320577b68?auto=format&fit=crop&w=800'),
        ('Session Sport & Yoga', 'Renforcez votre corps et votre esprit.', '2026-08-12 09:00:00', 'Bordeaux', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800')";
        
        $pdo->exec($sql);
        echo "Événements ajoutés avec succès !";
    } else {
        echo "Les événements existent déjà.";
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}

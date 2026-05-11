<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function migrate_update_participants() {
    try {
        $pdo = get_pdo();
        
        // Ajout des nouveaux champs à la table participants
        $sql = "ALTER TABLE participants 
                ADD COLUMN IF NOT EXISTS mot_de_passe VARCHAR(255) AFTER email,
                ADD COLUMN IF NOT EXISTS poids FLOAT AFTER telephone,
                ADD COLUMN IF NOT EXISTS taille FLOAT AFTER poids,
                ADD COLUMN IF NOT EXISTS imc FLOAT AFTER taille,
                ADD COLUMN IF NOT EXISTS lieu VARCHAR(255) AFTER imc,
                ADD COLUMN IF NOT EXISTS objectif VARCHAR(100) AFTER lieu";
        
        $pdo->exec($sql);
        
        // Ajout de la colonne is_published aux evenements si elle n'existe pas
        $pdo->exec("ALTER TABLE evenements ADD COLUMN IF NOT EXISTS is_published TINYINT DEFAULT 1");
        
        // Ajout de la colonne is_published aux categories si elle n'existe pas
        $pdo->exec("ALTER TABLE categories ADD COLUMN IF NOT EXISTS is_published TINYINT DEFAULT 1");
        
        // Mise à jour des images des événements pour mettre des images réelles Unsplash
        $pdo->exec("UPDATE evenements SET image_url = 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=800' WHERE id = 1");
        $pdo->exec("UPDATE evenements SET image_url = 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800' WHERE id = 2");
        
        echo "Mise à jour de la base de données réussie !\n";
    } catch (PDOException $e) {
        die("Erreur lors de la mise à jour : " . $e->getMessage() . "\n");
    }
}

migrate_update_participants();

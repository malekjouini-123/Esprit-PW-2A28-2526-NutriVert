<?php
declare(strict_types=1);

/**
 * Migration Auto - Exécute les migrations SQL manquantes
 * Ce fichier s'exécute automatiquement si des colonnes manquent
 */

require_once __DIR__ . '/config/database.php';

function runMigration(): void {
    $pdo = getDB();
    
    try {
        // Vérifier si la colonne 'age' existe
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'age'");
        if ($stmt->rowCount() === 0) {
            echo "🔄 Ajout des colonnes manquantes à la table users...\n";
            
            // Ajouter les colonnes manquantes
            $pdo->exec("ALTER TABLE users ADD COLUMN age INT AFTER taille");
            $pdo->exec("ALTER TABLE users ADD COLUMN sexe ENUM('homme', 'femme') AFTER age");
            $pdo->exec("ALTER TABLE users ADD COLUMN objectif ENUM('perte', 'maintien', 'muscle') AFTER sexe");
            
            echo "✅ Colonnes ajoutées avec succès!\n";
        }

        // Vérifier si la colonne 'imc' existe
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'imc'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN imc FLOAT AFTER objectif");
        }

        // Vérifier si la colonne 'calories' existe
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'calories'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN calories INT AFTER imc");
        }

        // Vérifier si les colonnes specialite et bio existent
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'specialite'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN specialite VARCHAR(255) AFTER calories");
            $pdo->exec("ALTER TABLE users ADD COLUMN bio TEXT AFTER specialite");
        }

        // Vérifier si les timestamps existent
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'created_at'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER bio");
            $pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        }

        // Vérifier la table coaching_programs
        $stmt = $pdo->query("SHOW TABLES LIKE 'coaching_programs'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE coaching_programs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                duration_weeks INT NOT NULL DEFAULT 1,
                difficulty_level ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'easy',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "✅ Table coaching_programs créée!\n";
        }

        // Vérifier la table exercises
        $stmt = $pdo->query("SHOW TABLES LIKE 'exercises'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE exercises (
                id INT AUTO_INCREMENT PRIMARY KEY,
                coaching_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                sets INT NOT NULL DEFAULT 1,
                reps INT NOT NULL DEFAULT 1,
                rest_time VARCHAR(50) NOT NULL,
                video_url VARCHAR(500),
                image VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (coaching_id) REFERENCES coaching_programs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "✅ Table exercises créée!\n";
        }

        echo "✅ Migration terminée avec succès!\n";
    } catch (PDOException $e) {
        echo "❌ Erreur migration: " . $e->getMessage() . "\n";
        // Ne pas arrêter, laisser continuer
    }
}

// Exécuter la migration
runMigration();

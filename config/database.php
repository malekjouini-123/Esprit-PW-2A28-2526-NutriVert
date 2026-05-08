<?php
declare(strict_types=1);
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nutrivert');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Get database connection
function getDB(): PDO {
    static $pdo = null;
    static $migrationDone = false;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Exécuter la migration automatique une seule fois
            if (!$migrationDone && !isset($_SESSION['_migration_done'])) {
                runAutoMigration($pdo);
                $_SESSION['_migration_done'] = true;
                $migrationDone = true;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed.');
        }
    }
    
    return $pdo;
}

// Fonction d'auto-migration
function runAutoMigration(PDO $pdo): void {
    try {
        // Vérifier et ajouter la colonne 'age' si elle n'existe pas
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'age'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN age INT AFTER taille");
            $pdo->exec("ALTER TABLE users ADD COLUMN sexe ENUM('homme', 'femme') AFTER age");
            $pdo->exec("ALTER TABLE users ADD COLUMN objectif ENUM('perte', 'maintien', 'muscle') AFTER sexe");
        }

        // Vérifier et ajouter 'imc' si absent
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'imc'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN imc FLOAT AFTER objectif");
        }

        // Vérifier et ajouter 'calories' si absent
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'calories'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN calories INT AFTER imc");
        }

        // Vérifier et ajouter 'specialite' et 'bio' si absents
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'specialite'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN specialite VARCHAR(255) AFTER calories");
            $pdo->exec("ALTER TABLE users ADD COLUMN bio TEXT AFTER specialite");
        }

        // Vérifier et ajouter 'created_at' si absent
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'created_at'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER bio");
            $pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        }

        // Créer table coaching_programs si elle n'existe pas
        $stmt = $pdo->query("SHOW TABLES LIKE 'coaching_programs'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE coaching_programs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                image VARCHAR(500),
                duration_weeks INT NOT NULL DEFAULT 1,
                difficulty_level ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'easy',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            // Ajouter la colonne 'image' si elle n'existe pas
            $stmt = $pdo->query("SHOW COLUMNS FROM coaching_programs LIKE 'image'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE coaching_programs ADD COLUMN image VARCHAR(500) AFTER description");
            }
        }

        // Créer table exercises si elle n'existe pas
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
                ordre INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (coaching_id) REFERENCES coaching_programs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            // Ajouter la colonne 'ordre' si elle n'existe pas
            $stmt = $pdo->query("SHOW COLUMNS FROM exercises LIKE 'ordre'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE exercises ADD COLUMN ordre INT DEFAULT 0 AFTER image");
            }

            // Ajouter la colonne 'duree_sec' si elle n'existe pas
            $stmt = $pdo->query("SHOW COLUMNS FROM exercises LIKE 'duree_sec'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE exercises ADD COLUMN duree_sec INT DEFAULT 30 AFTER ordre");
            }
        }
    } catch (PDOException $e) {
        // Ignorer les erreurs de migration, continuer quand même
        // Cela peut arriver si la table n'existe pas encore
    }
}

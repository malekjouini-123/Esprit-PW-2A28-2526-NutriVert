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
            
            $migrationVersion = 6;
            if (!$migrationDone && (($_SESSION['_migration_version'] ?? 0) < $migrationVersion)) {
                runAutoMigration($pdo);
                $_SESSION['_migration_version'] = $migrationVersion;
                $migrationDone = true;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Fonction d'auto-migration
function runAutoMigration(PDO $pdo): void {
    try {
        // Rendre nullable les colonnes NOT NULL sans défaut que le module final ne collecte pas
        foreach (['prenom', 'face_descriptor'] as $colName) {
            $col = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE '$colName'")->fetch(PDO::FETCH_ASSOC);
            if ($col && strpos($col['Null'], 'YES') === false) {
                $type = $col['Type'];
                $pdo->exec("ALTER TABLE utilisateurs MODIFY COLUMN `$colName` $type NULL DEFAULT NULL");
            }
        }

        // Vérifier que le ENUM 'role' inclut 'user' (le module teammates utilise 'client')
        $col = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
        if ($col && strpos($col['Type'], "'user'") === false) {
            $pdo->exec("ALTER TABLE utilisateurs MODIFY COLUMN role ENUM('user','client','coach','admin') NOT NULL DEFAULT 'user'");
        }

        // Vérifier et ajouter la colonne 'age' si elle n'existe pas
        $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'age'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN age INT AFTER taille");
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN sexe ENUM('homme', 'femme') AFTER age");
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN objectif ENUM('perte', 'maintien', 'muscle') AFTER sexe");
        }

        // Vérifier et ajouter 'imc' si absent
        $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'imc'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN imc FLOAT AFTER objectif");
        }

        // Vérifier et ajouter 'calories' si absent
        $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'calories'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN calories INT AFTER imc");
        }

        // Vérifier et ajouter 'specialite' et 'bio' si absents
        $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'specialite'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN specialite VARCHAR(255) AFTER calories");
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN bio TEXT AFTER specialite");
        }

        // Vérifier et ajouter 'created_at' si absent
        $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'created_at'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER bio");
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
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
        // ── Password reset columns ─────────────────────────────────────────
        $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'reset_token'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN reset_token VARCHAR(64) NULL AFTER bio");
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN reset_token_expiry DATETIME NULL AFTER reset_token");
        }

        // ── Face ID table (drop old schema if user_id FK pointed to users) ─
        $stmt = $pdo->query("SHOW TABLES LIKE 'visages_utilisateurs'");
        if ($stmt->rowCount() > 0) {
            $col = $pdo->query("SHOW COLUMNS FROM visages_utilisateurs LIKE 'user_id'");
            if ($col->rowCount() > 0) {
                $pdo->exec("DROP TABLE visages_utilisateurs");
            }
        }
        $stmt = $pdo->query("SHOW TABLES LIKE 'visages_utilisateurs'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE visages_utilisateurs (
                id_visage INT AUTO_INCREMENT PRIMARY KEY,
                id_utilisateur INT NOT NULL,
                face_encoding LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_visage_utilisateur (id_utilisateur),
                CONSTRAINT fk_visages_utilisateurs
                    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        // ── AI chat table (drop old schema if user_id FK pointed to users) ─
        $stmt = $pdo->query("SHOW TABLES LIKE 'ai_chats'");
        if ($stmt->rowCount() > 0) {
            $col = $pdo->query("SHOW COLUMNS FROM ai_chats LIKE 'user_id'");
            if ($col->rowCount() > 0) {
                $pdo->exec("DROP TABLE ai_chats");
            }
        }
        $stmt = $pdo->query("SHOW TABLES LIKE 'ai_chats'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE ai_chats (
                id_chat INT AUTO_INCREMENT PRIMARY KEY,
                id_utilisateur INT NOT NULL,
                user_message TEXT NOT NULL,
                ai_response MEDIUMTEXT NOT NULL,
                model VARCHAR(100) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_ai_chats_utilisateurs
                    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        // ── Auth log table (drop old schema if user_id FK pointed to users) ─
        $stmt = $pdo->query("SHOW TABLES LIKE 'authentifications'");
        if ($stmt->rowCount() > 0) {
            $col = $pdo->query("SHOW COLUMNS FROM authentifications LIKE 'user_id'");
            if ($col->rowCount() > 0) {
                $pdo->exec("DROP TABLE authentifications");
            }
        }
        $stmt = $pdo->query("SHOW TABLES LIKE 'authentifications'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE authentifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_utilisateur INT NOT NULL,
                type_connexion VARCHAR(50) NOT NULL DEFAULT 'email',
                derniere_connexion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_authentifications_utilisateurs
                    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

    } catch (PDOException $e) {
        // Ignorer les erreurs de migration, continuer quand même
        // Cela peut arriver si la table n'existe pas encore
    }
}

-- ============================================================================
-- Migration: Ajouter/Mettre à jour les champs utilisateurs
-- ============================================================================

-- Créer la table users si elle n'existe pas
CREATE TABLE IF NOT EXISTS users (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    nom               VARCHAR(255) NOT NULL,
    email             VARCHAR(255) NOT NULL UNIQUE,
    password          VARCHAR(255) NOT NULL,
    role              ENUM('user', 'coach', 'admin') NOT NULL DEFAULT 'user',
    
    -- Champs spécifiques aux utilisateurs
    poids             FLOAT,                                      -- kg
    taille            FLOAT,                                      -- cm
    age               INT,                                        -- années
    sexe              ENUM('homme', 'femme'),
    objectif          ENUM('perte', 'maintien', 'muscle'),
    imc               FLOAT,                                      -- calculé
    calories          INT,                                        -- kcal/jour
    
    -- Champs spécifiques aux coachs
    specialite        VARCHAR(255),                               -- Ex: "Musculation"
    bio               TEXT,                                       -- Présentation
    
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Créer la table coaching_programs si elle n'existe pas
CREATE TABLE IF NOT EXISTS coaching_programs (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    title             VARCHAR(255) NOT NULL,
    description       TEXT,
    image             VARCHAR(500),                               -- chemin relatif uploads/...
    duration_weeks    INT NOT NULL DEFAULT 1,
    difficulty_level  ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'easy',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Créer la table exercises si elle n'existe pas
CREATE TABLE IF NOT EXISTS exercises (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    coaching_id       INT NOT NULL,
    name              VARCHAR(255) NOT NULL,
    description       TEXT,
    sets              INT NOT NULL DEFAULT 1,
    reps              INT NOT NULL DEFAULT 1,
    rest_time         VARCHAR(50) NOT NULL,                       -- "30 secondes", "1 minute", etc.
    video_url         VARCHAR(500),
    image             VARCHAR(500),                               -- chemin relatif uploads/...
    ordre             INT NOT NULL DEFAULT 1,                     -- Ordre d'exécution (croissant)
    duree_sec         INT NOT NULL DEFAULT 30,                    -- Durée de l'exercice en secondes
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (coaching_id) REFERENCES coaching_programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Créer les index
CREATE INDEX IF NOT EXISTS idx_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_exercises_coaching ON exercises(coaching_id);

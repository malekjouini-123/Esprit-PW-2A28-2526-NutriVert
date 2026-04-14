-- SQL Script pour l'initialisation complète de la base de données NutriVert (Version Auto-Increment)
-- Date: 2026-04-14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Création de la base de données
CREATE DATABASE IF NOT EXISTS NutriVert CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE NutriVert;

-- --------------------------------------------------------

-- 2. Structure de la table 'evenements'
CREATE TABLE IF NOT EXISTS evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    categorie VARCHAR(100),
    description TEXT NOT NULL,
    date_evenement DATETIME NOT NULL,
    lieu VARCHAR(255) NOT NULL,
    prix_participation DECIMAL(10,2) DEFAULT 0.00,
    capacite_max INT DEFAULT 0,
    statut VARCHAR(50) DEFAULT 'Actif',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Structure de la table 'categories'
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    atelier VARCHAR(150) NOT NULL,
    images TEXT NOT NULL, -- URLs séparées par des virgules
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Structure de la table 'inscriptions' (Participants)
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    lieu VARCHAR(255) NOT NULL,
    date_naissance DATE NOT NULL,
    poids FLOAT NOT NULL,
    taille FLOAT NOT NULL,
    imc FLOAT NOT NULL,
    categorie_preferee VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

-- 5. Insertion de données de démonstration

-- Données pour 'categories'
INSERT INTO categories (nom, description, atelier, images) VALUES 
('Atelier Cuisine Bio', 'Découvrez les secrets d''une cuisine saine et respectueuse de l''environnement.', 'Cuisine Bio & Durable', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=800'),
('Nutrition Sportive', 'Optimisez vos performances avec une alimentation adaptée.', 'Énergie & Récupération', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800');

-- Données pour 'evenements'
INSERT INTO evenements (titre, categorie, description, date_evenement, lieu, prix_participation, capacite_max, statut, image_url) VALUES 
('Coaching Nutrition Premium', 'Nutrition', 'Un programme complet pour transformer vos habitudes alimentaires.', '2026-05-20 10:00:00', 'Paris', 49.00, 50, 'Actif', 'https://images.unsplash.com/photo-1490818387583-1baba5e638af?auto=format&fit=crop&w=800'),
('Atelier Cuisine Détox', 'Cuisine', 'Apprenez à cuisiner des plats légers et revitalisants.', '2026-06-15 14:30:00', 'Lyon', 35.00, 20, 'Actif', 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800');

-- Données pour 'inscriptions' (Participants)
INSERT INTO inscriptions (evenement_id, nom, prenom, email, mot_de_passe, telephone, lieu, date_naissance, poids, taille, imc, categorie_preferee) VALUES 
(1, 'Dupont', 'Jean', 'jean.dupont@email.com', 'password123', '0601020304', 'Paris', '1990-05-15', 75.5, 180, 23.3, 'Nutrition'),
(1, 'Martin', 'Sophie', 'sophie.martin@email.com', 'sophie2026', '0705060708', 'Lyon', '1995-10-22', 62.0, 165, 22.8, 'Cuisine');

COMMIT;

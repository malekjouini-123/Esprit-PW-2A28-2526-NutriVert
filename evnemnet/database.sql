-- Initialisation de la base de données
CREATE DATABASE IF NOT EXISTS projet_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projet_db;

-- Table des catégories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    is_published TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des événements
CREATE TABLE IF NOT EXISTS evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_evenement DATETIME NOT NULL,
    lieu VARCHAR(255),
    prix DECIMAL(10,2) DEFAULT 0.00,
    capacite INT DEFAULT 0,
    categorie_id INT,
    image_url VARCHAR(255),
    is_published TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table des participants
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255),
    telephone VARCHAR(20),
    poids DECIMAL(5,2),
    taille DECIMAL(5,2),
    imc DECIMAL(5,2),
    lieu VARCHAR(255),
    objectif TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des inscriptions (Lien entre Evenements et Participants)
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT NOT NULL,
    participant_id INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des recommandations personnalisées créées par l'utilisateur
CREATE TABLE IF NOT EXISTS recommendations_personnalisees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    categorie_preferee VARCHAR(100),
    budget_max DECIMAL(10,2),
    localisation VARCHAR(255),
    ai_suggestion TEXT,
    evenements_suggeres TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Données de test
INSERT INTO categories (nom, description) VALUES 
('Sport', 'Événements sportifs et activités physiques'),
('Culture', 'Expositions, concerts et théâtre'),
('Technologie', 'Conférences et ateliers tech');

INSERT INTO evenements (titre, description, date_evenement, lieu, prix, capacite, categorie_id, image_url) VALUES 
('Marathon de Paris', 'Le grand marathon annuel de Paris où les coureurs de tous niveaux se rassemblent pour une expérience inoubliable.', '2026-06-12 09:00:00', 'Paris', 50.00, 1000, 1, 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=500&h=300&fit=crop'),
('Conférence IA', 'Découvrez le futur de l\'intelligence artificielle avec les experts du domaine et les innovations les plus récentes.', '2026-05-15 14:00:00', 'Lyon', 30.00, 200, 3, 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&h=300&fit=crop'),
('Festival de Musique', 'Trois jours de musique live avec les meilleurs artistes français et internationaux.', '2026-07-20 18:00:00', 'Nantes', 80.00, 5000, 2, 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=500&h=300&fit=crop'),
('Tournoi de Tennis', 'Compétition sportive intense avec les meilleurs joueurs amateurs et professionnels.', '2026-06-01 10:00:00', 'Bordeaux', 25.00, 500, 1, 'https://images.unsplash.com/photo-1554224311-beee415c15b7?w=500&h=300&fit=crop'),
('Atelier Photographie', 'Apprenez les techniques professionnelles de la photographie avec des experts du secteur.', '2026-05-25 15:00:00', 'Marseille', 45.00, 30, 2, 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=500&h=300&fit=crop');

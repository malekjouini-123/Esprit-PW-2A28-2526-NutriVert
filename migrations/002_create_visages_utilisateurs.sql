-- Migration: Create Face ID table
-- Date: 2026-05-04
-- Description: Stores JSON face encodings for Face ID authentication.

CREATE TABLE IF NOT EXISTS visages_utilisateurs (
    id_visage INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    face_encoding LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_visage_utilisateur (id_utilisateur),
    CONSTRAINT fk_visages_utilisateurs_user
        FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id_utilisateur)
        ON DELETE CASCADE
);

-- Migration: Add icons support for posts
-- Run this SQL on your nutrivert_db database

-- 1. Create the PostIcon table with predefined icons
CREATE TABLE IF NOT EXISTS PostIcon (
    id_icon INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    emoji VARCHAR(10) NOT NULL,
    category VARCHAR(30) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add icon_id column to the Post table
ALTER TABLE Post ADD COLUMN icon_id INT DEFAULT NULL AFTER type_post;
ALTER TABLE Post ADD CONSTRAINT fk_post_icon FOREIGN KEY (icon_id) REFERENCES PostIcon(id_icon) ON DELETE SET NULL;

-- 3. Insert predefined icons
INSERT INTO PostIcon (name, emoji, category) VALUES
-- Nutrition
('Pomme', '🍎', 'nutrition'),
('Salade', '🥗', 'nutrition'),
('Avocat', '🥑', 'nutrition'),
('Brocoli', '🥦', 'nutrition'),
('Carotte', '🥕', 'nutrition'),
('Smoothie', '🥤', 'nutrition'),
('Eau', '💧', 'nutrition'),
-- Fitness
('Musculation', '💪', 'fitness'),
('Course', '🏃', 'fitness'),
('Yoga', '🧘', 'fitness'),
('Vélo', '🚴', 'fitness'),
('Médaille', '🏅', 'fitness'),
-- Bien-être
('Cœur', '❤️', 'bien-etre'),
('Étoile', '⭐', 'bien-etre'),
('Soleil', '☀️', 'bien-etre'),
('Feuille', '🍃', 'bien-etre'),
('Zen', '🧠', 'bien-etre'),
('Sommeil', '😴', 'bien-etre'),
-- Général
('Feu', '🔥', 'general'),
('Idée', '💡', 'general'),
('Question', '❓', 'general'),
('Annonce', '📢', 'general'),
('Recette', '👨‍🍳', 'general'),
('Objectif', '🎯', 'general');

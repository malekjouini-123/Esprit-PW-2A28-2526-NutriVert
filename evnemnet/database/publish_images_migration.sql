-- Ajout des images + statut "publié" pour catégories / événements
-- MySQL 8.0.12+ : ADD COLUMN IF NOT EXISTS
-- Si erreur de syntaxe, utilisez publish_images_migration_mysql57.sql

ALTER TABLE evenements
  ADD COLUMN IF NOT EXISTS is_published TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE categories
  ADD COLUMN IF NOT EXISTS image_url VARCHAR(512) NULL,
  ADD COLUMN IF NOT EXISTS is_published TINYINT(1) NOT NULL DEFAULT 1;


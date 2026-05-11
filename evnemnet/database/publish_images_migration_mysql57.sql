-- Compatible MySQL / MariaDB sans "IF NOT EXISTS" sur ALTER.
-- Exécutez chaque bloc ; ignorez l'erreur "Duplicate column" si la colonne existe déjà.

ALTER TABLE evenements ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE categories ADD COLUMN image_url VARCHAR(512) NULL;
ALTER TABLE categories ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1;

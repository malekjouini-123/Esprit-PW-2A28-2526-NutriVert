-- À exécuter si la base NutriVert existait déjà sans la colonne `nom` sur `commande`.
USE `NutriVert`;
ALTER TABLE `commande`
  ADD COLUMN `nom` varchar(180) NOT NULL DEFAULT 'Ma commande'
  COMMENT 'Libellé affiché (ex. course du marché)' AFTER `id`;

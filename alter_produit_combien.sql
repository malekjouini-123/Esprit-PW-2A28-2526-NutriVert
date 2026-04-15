-- Stock produit : colonne `combien` (existante base sans cette colonne).
USE `NutriVert`;
ALTER TABLE `produit`
  ADD COLUMN `combien` int(11) NOT NULL DEFAULT 0 COMMENT 'Quantité en stock (unités)' AFTER `empreinte_co2`;

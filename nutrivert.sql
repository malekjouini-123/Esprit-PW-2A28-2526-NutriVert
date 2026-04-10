-- ============================================================
-- NutriVert — script à copier-coller dans phpMyAdmin (localhost)
-- Base : NutriVert  |  Tables : categorie, produit, commande, ligne_commande
-- ============================================================

CREATE DATABASE IF NOT EXISTS `NutriVert` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `NutriVert`;

-- Catégories (ex. Légumes, Fruits, Viandes…)
DROP TABLE IF EXISTS `ligne_commande`;
DROP TABLE IF EXISTS `commande`;
DROP TABLE IF EXISTS `produit`;
DROP TABLE IF EXISTS `categorie`;

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `produit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categorie_id` int(11) NOT NULL,
  `nom` varchar(180) NOT NULL,
  `label` varchar(255) DEFAULT NULL COMMENT 'Ex: Bio, Local, Sans gluten',
  `producteur` varchar(150) DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL DEFAULT 0.00,
  `empreinte_co2` decimal(8,2) DEFAULT NULL COMMENT 'kg CO2 équivalent',
  `icone` varchar(64) DEFAULT 'fa-seedling' COMMENT 'classe Font Awesome sans préfixe',
  PRIMARY KEY (`id`),
  KEY `fk_produit_categorie` (`categorie_id`),
  CONSTRAINT `fk_produit_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commande` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_commande` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('en_attente','validee','livree','annulee') NOT NULL DEFAULT 'en_attente',
  `client_email` varchar(180) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ligne_commande` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commande_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_lc_commande` (`commande_id`),
  KEY `fk_lc_produit` (`produit_id`),
  CONSTRAINT `fk_lc_commande` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lc_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de démonstration
INSERT INTO `categorie` (`nom`, `description`) VALUES
('Légumes', 'Courgettes, tomates, salades…'),
('Fruits', 'Bananes, pommes, agrumes…'),
('Viandes', 'Volaille, bœuf, agneau…'),
('Épicerie', 'Céréales, huiles, conserves…');

INSERT INTO `produit` (`categorie_id`, `nom`, `label`, `producteur`, `prix`, `empreinte_co2`, `icone`) VALUES
((SELECT id FROM categorie WHERE nom = 'Légumes' LIMIT 1), 'Tomates anciennes', 'Bio, Local', 'Maraîcher du Sahel', 4.20, 0.35, 'fa-apple-alt'),
((SELECT id FROM categorie WHERE nom = 'Fruits' LIMIT 1), 'Bananes équitable', 'Bio', 'Coop Sud', 5.50, 0.90, 'fa-lemon'),
((SELECT id FROM categorie WHERE nom = 'Viandes' LIMIT 1), 'Poulet fermier', 'Local', 'Ferme des Vallées', 12.00, 2.10, 'fa-drumstick-bite'),
((SELECT id FROM categorie WHERE nom = 'Épicerie' LIMIT 1), 'Huile d''olive vierge', 'Bio, Local', 'Moulin Vert', 18.50, 1.20, 'fa-bottle-droplet');

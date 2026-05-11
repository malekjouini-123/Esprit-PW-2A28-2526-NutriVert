-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 11 mai 2026 à 07:10
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `nutrivert`
--

-- --------------------------------------------------------

--
-- Structure de la table `ai_chats`
--

CREATE TABLE `ai_chats` (
  `id_chat` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `user_message` text NOT NULL,
  `ai_response` mediumtext NOT NULL,
  `model` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `authentifications`
--

CREATE TABLE `authentifications` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `type_connexion` varchar(50) NOT NULL DEFAULT 'email',
  `derniere_connexion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `authentifications`
--

INSERT INTO `authentifications` (`id`, `id_utilisateur`, `type_connexion`, `derniere_connexion`) VALUES
(1, 3, 'email', '2026-05-11 04:04:43'),
(2, 3, 'email', '2026-05-11 05:33:05'),
(3, 3, 'email', '2026-05-11 05:59:32'),
(4, 3, 'email', '2026-05-11 06:01:55'),
(5, 3, 'email', '2026-05-11 06:07:42'),
(6, 3, 'email', '2026-05-11 06:14:07'),
(7, 3, 'email', '2026-05-11 06:33:34'),
(8, 4, 'email', '2026-05-11 06:47:18');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `nom`, `description`) VALUES
(1, 'Légumes', 'Frais et locaux'),
(2, 'Fruits', 'Saisonniers et bio'),
(3, 'Épicerie', 'Produits secs');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `description`, `image_url`) VALUES
(1, 'Football', 'Matchs et tournois', 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=500&h=300&fit=crop'),
(2, 'Yoga', 'Séances zen', 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=500&h=300&fit=crop'),
(3, 'Natation', 'Courses et cours', 'https://images.unsplash.com/photo-1530549387074-dca31930b910?w=500&h=300&fit=crop');

-- --------------------------------------------------------

--
-- Structure de la table `coaching_programs`
--

CREATE TABLE `coaching_programs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `duration_weeks` int(11) NOT NULL DEFAULT 1,
  `difficulty_level` enum('easy','medium','hard') NOT NULL DEFAULT 'easy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `coaching_programs`
--

INSERT INTO `coaching_programs` (`id`, `title`, `description`, `image`, `duration_weeks`, `difficulty_level`, `created_at`, `updated_at`) VALUES
(1, 'Programme Débutant', 'Idéal pour commencer une activité physique douce.', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=500&q=80', 4, 'easy', '2026-05-11 01:41:51', '2026-05-11 01:41:51'),
(2, 'Renforcement Intense', 'Pour ceux qui veulent repousser leurs limites.', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=500&q=80', 8, 'hard', '2026-05-11 01:41:51', '2026-05-11 01:41:51');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id` int(11) NOT NULL,
  `nom` varchar(180) NOT NULL DEFAULT 'Ma commande',
  `date_commande` datetime NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en_attente','validee','livree','annulee') NOT NULL DEFAULT 'en_attente',
  `client_email` varchar(180) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `type_paiement` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id`, `nom`, `date_commande`, `statut`, `client_email`, `total`, `type_paiement`) VALUES
(1, 'Commande', '2026-05-11 04:05:40', 'en_attente', NULL, 0.00, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `evenements`
--

CREATE TABLE `evenements` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_evenement` datetime NOT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT 0.00,
  `capacite` int(11) DEFAULT 0,
  `categorie_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `evenements`
--

INSERT INTO `evenements` (`id`, `titre`, `description`, `date_evenement`, `lieu`, `prix`, `capacite`, `categorie_id`, `image_url`) VALUES
(1, 'Tournoi Inter-Quartier', 'Foot pour tous', '2026-06-15 10:00:00', 'Stade Central', 5.00, 22, 1, 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80'),
(2, 'Grand Marathon de Tunis', 'La plus grande course de l\'année', '2026-07-10 07:00:00', 'Avenue Bourguiba', 0.00, 1000, 3, 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=800&q=80');

-- --------------------------------------------------------

--
-- Structure de la table `exercises`
--

CREATE TABLE `exercises` (
  `id` int(11) NOT NULL,
  `coaching_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sets` int(11) NOT NULL DEFAULT 1,
  `reps` int(11) NOT NULL DEFAULT 1,
  `rest_time` varchar(50) NOT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `ordre` int(11) DEFAULT 0,
  `duree_sec` int(11) DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id` int(11) NOT NULL,
  `evenement_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `instruction`
--

CREATE TABLE `instruction` (
  `id_instruction` int(11) NOT NULL,
  `id_recette` int(11) NOT NULL,
  `etape` text NOT NULL,
  `description` text NOT NULL,
  `ingredient_produit` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`ingredient_produit`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `instruction`
--

INSERT INTO `instruction` (`id_instruction`, `id_recette`, `etape`, `description`, `ingredient_produit`) VALUES
(1, 1, 'zadefvdrzefb', 'qazdefrgazdefrgvt', '[{\"id_produit\":2,\"nom_produit\":\"sucre\",\"quantite\":\"100g\"},\r\n      {\"id_produit\":3,\"nom_produit\":\"farine\",\"quantite\":\"200g\"}]'),
(2, 2, 'Préparer les ingrédients', 'Laver et couper : farine, sucre.', '[{\"nom_produit\":\"farine\",\"quantite\":\"1 portion\",\"image\":\"https:\\/\\/placehold.co\\/60x60?text=farine\"},{\"nom_produit\":\"sucre\",\"quantite\":\"1 portion\",\"image\":\"https:\\/\\/placehold.co\\/60x60?text=sucre\"}]'),
(3, 2, 'Cuisson rapide', 'Faire cuire farine avec un filet d’huile et assaisonner selon le goût.', '[{\"nom_produit\":\"farine\",\"quantite\":\"selon besoin\",\"image\":\"https:\\/\\/placehold.co\\/60x60?text=farine\"},{\"nom_produit\":\"sucre\",\"quantite\":\"selon besoin\",\"image\":\"https:\\/\\/placehold.co\\/60x60?text=sucre\"}]'),
(4, 2, 'Dressage', 'Servir chaud ou tiède avec une présentation simple et équilibrée.', '[{\"nom_produit\":\"farine\",\"quantite\":\"selon goût\",\"image\":\"https:\\/\\/placehold.co\\/60x60?text=farine\"},{\"nom_produit\":\"sucre\",\"quantite\":\"selon goût\",\"image\":\"https:\\/\\/placehold.co\\/60x60?text=sucre\"}]'),
(14, 4, 'Préparer les ingrédients', 'Laver et couper : farine, sucre.', '[{\"nom_produit\":\"farine\",\"quantite\":\"1 portion\",\"image\":\"https://placehold.co/60x60?text=farine\"},{\"nom_produit\":\"sucre\",\"quantite\":\"1 portion\",\"image\":\"https://placehold.co/60x60?text=sucre\"}]'),
(15, 4, 'Cuisson rapide', 'Faire cuire farine avec un filet d’huile et assaisonner selon le goût.', '[{\"nom_produit\":\"farine\",\"quantite\":\"selon besoin\",\"image\":\"https://placehold.co/60x60?text=farine\"},{\"nom_produit\":\"sucre\",\"quantite\":\"selon besoin\",\"image\":\"https://placehold.co/60x60?text=sucre\"}]'),
(16, 4, 'Dressage', 'Servir chaud ou tiède avec une présentation simple et équilibrée.', '[{\"nom_produit\":\"farine\",\"quantite\":\"selon goût\",\"image\":\"https://placehold.co/60x60?text=farine\"},{\"nom_produit\":\"sucre\",\"quantite\":\"selon goût\",\"image\":\"https://placehold.co/60x60?text=sucre\"}]');

-- --------------------------------------------------------

--
-- Structure de la table `ligne_commande`
--

CREATE TABLE `ligne_commande` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `poids` decimal(5,2) DEFAULT NULL,
  `taille` decimal(5,2) DEFAULT NULL,
  `imc` decimal(5,2) DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `objectif` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `nom` varchar(180) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `producteur` varchar(150) DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL DEFAULT 0.00,
  `promo_pourcent` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `empreinte_co2` decimal(8,2) DEFAULT NULL,
  `combien` int(11) NOT NULL DEFAULT 0,
  `icone` varchar(64) DEFAULT 'fa-seedling'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`id`, `categorie_id`, `nom`, `label`, `producteur`, `prix`, `promo_pourcent`, `empreinte_co2`, `combien`, `icone`) VALUES
(1, 1, 'Tomates Bio', NULL, NULL, 4.50, 0, NULL, 20, 'fa-apple-alt'),
(2, 2, 'Bananes', NULL, NULL, 3.20, 0, NULL, 50, 'fa-lemon');

-- --------------------------------------------------------

--
-- Structure de la table `recette`
--

CREATE TABLE `recette` (
  `id_recette` int(11) NOT NULL,
  `titre` varchar(50) NOT NULL,
  `objectif` varchar(100) NOT NULL,
  `regime` varchar(50) NOT NULL,
  `duree` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `recette`
--

INSERT INTO `recette` (`id_recette`, `titre`, `objectif`, `regime`, `duree`) VALUES
(1, 'Gâteau simple', 'Dessert facile à préparer', 'Végétarien', 45),
(2, 'Bol NutriVert Farine', 'Recette saine anti-gaspillage', 'Végétarien', 25),
(4, 'Bol Farine', 'Recette saine anti-gaspillage', 'Végétarien', 25);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('user','client','coach','admin') NOT NULL DEFAULT 'user',
  `poids` decimal(5,2) DEFAULT NULL,
  `taille` decimal(5,2) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sexe` enum('homme','femme') DEFAULT NULL,
  `objectif` enum('perte','maintien','muscle') DEFAULT NULL,
  `imc` decimal(5,2) DEFAULT NULL,
  `calories` int(11) DEFAULT NULL,
  `specialite` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `objectif_nutritionnel` text DEFAULT NULL,
  `regime_alimentaire` varchar(100) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `face_descriptor` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `poids`, `taille`, `age`, `sexe`, `objectif`, `imc`, `calories`, `specialite`, `bio`, `objectif_nutritionnel`, `regime_alimentaire`, `reset_token`, `reset_token_expiry`, `face_descriptor`, `created_at`) VALUES
(1, 'Admin', NULL, 'admin@nutrivert.com', '1234', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '79b66b843c352d424f9a589e29e879caad20c42cd88ae457238deaddcb97d36e', '2026-05-11 07:11:56', NULL, '2026-05-11 01:41:51'),
(2, 'loay', 'loay', 'loay@gmail.com', '1234', 'admin', 50.00, 155.00, NULL, NULL, NULL, 20.81, NULL, NULL, NULL, NULL, 'vegan', NULL, NULL, NULL, '2026-05-11 01:41:51'),
(3, 'xjoizox', NULL, 'xznjkjakz@gmail.com', '$2y$10$3km387bT8SkCssTD3ZTZd.tWW1OU9FIvR1khRdz8/aacMEJ9fgCEy', 'admin', 60.00, 167.00, 20, 'homme', 'maintien', 21.51, 1600, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-11 02:04:32'),
(4, 'haya', NULL, 'ekadad@gmail.com', '$2y$10$vE2TFqWgNkQM0N/U0tba8OGjbsxExyYrRq.pds77DPyFhzDMPcL8u', 'user', 22.50, 125.00, 25, 'homme', 'maintien', 14.40, 1600, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-11 04:46:25');

-- --------------------------------------------------------

--
-- Structure de la table `visages_utilisateurs`
--

CREATE TABLE `visages_utilisateurs` (
  `id_visage` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `face_encoding` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ai_chats`
--
ALTER TABLE `ai_chats`
  ADD PRIMARY KEY (`id_chat`),
  ADD KEY `fk_ai_chats_utilisateurs` (`id_utilisateur`);

--
-- Index pour la table `authentifications`
--
ALTER TABLE `authentifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_authentifications_utilisateurs` (`id_utilisateur`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `coaching_programs`
--
ALTER TABLE `coaching_programs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Index pour la table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exercises_coaching` (`coaching_id`);

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evenement_id` (`evenement_id`),
  ADD KEY `participant_id` (`participant_id`);

--
-- Index pour la table `instruction`
--
ALTER TABLE `instruction`
  ADD PRIMARY KEY (`id_instruction`),
  ADD KEY `id_recette` (`id_recette`);

--
-- Index pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lc_commande_id` (`commande_id`),
  ADD KEY `fk_lc_produit_id` (`produit_id`);

--
-- Index pour la table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produit_categorie_id` (`categorie_id`);

--
-- Index pour la table `recette`
--
ALTER TABLE `recette`
  ADD PRIMARY KEY (`id_recette`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `visages_utilisateurs`
--
ALTER TABLE `visages_utilisateurs`
  ADD PRIMARY KEY (`id_visage`),
  ADD UNIQUE KEY `uniq_visage_utilisateur` (`id_utilisateur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ai_chats`
--
ALTER TABLE `ai_chats`
  MODIFY `id_chat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `authentifications`
--
ALTER TABLE `authentifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `coaching_programs`
--
ALTER TABLE `coaching_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `instruction`
--
ALTER TABLE `instruction`
  MODIFY `id_instruction` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `recette`
--
ALTER TABLE `recette`
  MODIFY `id_recette` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `visages_utilisateurs`
--
ALTER TABLE `visages_utilisateurs`
  MODIFY `id_visage` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `ai_chats`
--
ALTER TABLE `ai_chats`
  ADD CONSTRAINT `fk_ai_chats_utilisateurs` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `authentifications`
--
ALTER TABLE `authentifications`
  ADD CONSTRAINT `fk_authentifications_utilisateurs` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD CONSTRAINT `evenements_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `exercises`
--
ALTER TABLE `exercises`
  ADD CONSTRAINT `fk_exercises_coaching` FOREIGN KEY (`coaching_id`) REFERENCES `coaching_programs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`evenement_id`) REFERENCES `evenements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `instruction`
--
ALTER TABLE `instruction`
  ADD CONSTRAINT `instruction_ibfk_1` FOREIGN KEY (`id_recette`) REFERENCES `recette` (`id_recette`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD CONSTRAINT `fk_lc_commande_id` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lc_produit_id` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `fk_produit_categorie_id` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `visages_utilisateurs`
--
ALTER TABLE `visages_utilisateurs`
  ADD CONSTRAINT `fk_visages_utilisateurs` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

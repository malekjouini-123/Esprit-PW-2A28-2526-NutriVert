-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 08:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nutrivert`
--

-- --------------------------------------------------------

--
-- Table structure for table `instruction`
--

CREATE TABLE `instruction` (
  `id_instruction` int(11) NOT NULL,
  `id_recette` int(11) NOT NULL,
  `etape` text NOT NULL,
  `description` text NOT NULL,
  `ingredient_produit` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`ingredient_produit`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instruction`
--

INSERT INTO `instruction` (`id_instruction`, `id_recette`, `etape`, `description`, `ingredient_produit`) VALUES
(1, 1, 'zadefvdrzefb', 'qazdefrgazdefrgvt', '[{\"id_produit\":2,\"nom_produit\":\"sucre\",\"quantite\":\"100g\"},\r\n      {\"id_produit\":3,\"nom_produit\":\"farine\",\"quantite\":\"200g\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `produit`
--

CREATE TABLE `produit` (
  `id_produit` int(11) NOT NULL,
  `nom_produit` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produit`
--

INSERT INTO `produit` (`id_produit`, `nom_produit`) VALUES
(3, 'farine'),
(5, 'huile'),
(4, 'lait'),
(1, 'oeuf'),
(2, 'sucre');

-- --------------------------------------------------------

--
-- Table structure for table `recette`
--

CREATE TABLE `recette` (
  `id_recette` int(11) NOT NULL,
  `titre` varchar(50) NOT NULL,
  `objectif` varchar(100) NOT NULL,
  `regime` varchar(50) NOT NULL,
  `duree` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recette`
--

INSERT INTO `recette` (`id_recette`, `titre`, `objectif`, `regime`, `duree`) VALUES
(1, 'Gâteau simple', 'Dessert facile à préparer', 'Végétarien', 45);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `instruction`
--
ALTER TABLE `instruction`
  ADD PRIMARY KEY (`id_instruction`),
  ADD KEY `id_recette` (`id_recette`);

--
-- Indexes for table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id_produit`),
  ADD UNIQUE KEY `nom_produit` (`nom_produit`);

--
-- Indexes for table `recette`
--
ALTER TABLE `recette`
  ADD PRIMARY KEY (`id_recette`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `instruction`
--
ALTER TABLE `instruction`
  MODIFY `id_instruction` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `produit`
--
ALTER TABLE `produit`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `recette`
--
ALTER TABLE `recette`
  MODIFY `id_recette` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `instruction`
--
ALTER TABLE `instruction`
  ADD CONSTRAINT `instruction_ibfk_1` FOREIGN KEY (`id_recette`) REFERENCES `recette` (`id_recette`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

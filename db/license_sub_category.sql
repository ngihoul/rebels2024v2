-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : ven. 24 mai 2024 à 14:43
-- Version du serveur : 8.0.36-0ubuntu0.22.04.1
-- Version de PHP : 8.1.2-1ubuntu2.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `rebels`
--

-- --------------------------------------------------------

--
-- Structure de la table `license_sub_category`
--

--
-- Déchargement des données de la table `license_sub_category`
--

INSERT INTO `license_sub_category` (`id`, `category_id`, `name`, `value`) VALUES
(1, 1, 'Baseball', 1),
(2, 1, 'Softball', 2),
(3, 1, 'Slowpitch', 3),
(4, 1, 'Récréant', 4),
(5, 1, 'Baseball 5', 5),
(6, 2, 'Assistant coach', 6),
(7, 2, 'Coach Niv1 - Niv2', 7),
(8, 2, 'Sympathisant', 8),
(9, 2, 'Administrateur', 9),
(10, 3, 'Arbitre fédéral', 10),
(11, 3, 'Arbitre régional', 11),
(12, 3, 'Scoreur fédéral', 12),
(13, 3, 'Scoreur régional', 13);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `license_sub_category`
--
ALTER TABLE `license_sub_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_237E062412469DE2` (`category_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `license_sub_category`
--
ALTER TABLE `license_sub_category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `license_sub_category`
--
ALTER TABLE `license_sub_category`
  ADD CONSTRAINT `FK_237E062412469DE2` FOREIGN KEY (`category_id`) REFERENCES `license_category` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

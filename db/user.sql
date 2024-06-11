-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 11 juin 2024 à 18:15
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
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `firstname`, `lastname`, `license_number`, `jersey_number`, `date_of_birth`, `gender`, `address_street`, `address_number`, `zipcode`, `locality`, `phone_number`, `mobile_number`, `email`, `password`, `roles`, `profile_picture`, `newsletter_lfbbs`, `internal_rules`, `is_banned`, `is_archived`, `is_verified`, `created_at`, `updated_at`, `nationality_id`, `country_id`, `privacy_policy`) VALUES
(219, 'Bo', 'Bichette', 'BEL123456789', 11, '1998-03-05 00:00:00', 'M', 'Rogers Center', '11', '66777', 'Toronto', NULL, '+16474939240', 'bo.bichette@bluejays.ca', '$2y$13$izJHXh9cjFKHCuqTNCWi5enK0M5tGXn4Zmg0SbDK7GTQOaPjsjOS2', '[\"ROLE_COACH\"]', 'boBichette-666330a745fda.jpg', 1, 1, 0, 0, 1, '2024-06-07 18:09:11', '2024-06-07 18:57:55', 185, 31, 1),
(220, 'Vladimir Jr.', 'Guerrero', 'BEL456123', 27, '1999-03-16 00:00:00', 'M', 'Rogers Center', '27', '66777', 'Toronto', NULL, '+16474939240', 'vlad.guerrero@bluejays.ca', '$2y$13$SBkxRvH5l1.drTBhKFkz6.Bq5HBavCnCuRchkJtTZ6zIIK3C513oy', '[]', 'vladGuerrero-66633abd71d88.png', 1, 1, 0, 0, 1, '2024-06-07 18:52:13', '2024-06-10 20:34:36', 50, 31, 1),
(221, 'Yusei', 'Kikuchi', 'BEL123456', 16, '1991-06-17 00:00:00', 'M', 'Rogers Center', '16', '57268', 'Toronto', '+12367854', '+12567895', 'yusei.kikuchi@bluejays.ca', '$2y$13$zO/ZHJQwjQSKvyvxilboPOHGzfldgKhGApyTraxccG06N8t3wyvNi', '[\"ROLE_ADMIN\"]', 'yuseiKikuchi-6668781b86106.png', 1, 1, 0, 0, 1, '2024-06-11 18:15:23', '2024-06-11 18:15:28', 84, 31, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD KEY `IDX_8D93D6491C9DA55` (`nationality_id`),
  ADD KEY `IDX_8D93D649F92F3E70` (`country_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D6491C9DA55` FOREIGN KEY (`nationality_id`) REFERENCES `country` (`id`),
  ADD CONSTRAINT `FK_8D93D649F92F3E70` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

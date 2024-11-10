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
-- Structure de la table `place`
--

--
-- Déchargement des données de la table `place`
--

INSERT INTO `place` (`id`, `name`, `address_street`, `address_number`, `address_zipcode`, `address_locality`, `address_country_id`) VALUES
(42, 'Liège Rebels', 'Voie Mélotte', NULL, '4030', 'Grivegnée', 17),
(43, 'Seraing Brown Boys', 'Rue des Roselières', NULL, '4101', 'Jemeppe', 17),
(44, 'Namur Angels', 'Rue de la 1ière Armée Américaine', NULL, '5100', 'Wépion', 17),
(45, 'Andenne Black Bears', 'Rue sous Meuse', NULL, '5300', 'Andenne', 17),
(46, 'Brussels Kangaroos', 'Avenue JF Becker', NULL, '1200', 'Woluwé Saint-Lambert', 17),
(47, 'Braine Black Rickers', 'Rue du Progrès', NULL, '1400', 'Nivelles', 17),
(48, 'Mont-Saint-Guibert Phoenix', 'Avenue du Cerisier', NULL, '1435', 'Mont-Saint-Guibert', 17),
(49, 'Binche Guardians', 'Chaussée du Roi Baudouin', '141', '7030', 'Saint-Symphorien', 17),
(50, 'Tournai Celtics', 'Avenue De Gaulle', '2', '7500', 'Tournai', 17),
(51, 'Mons Athletic’s', 'Rue  du Château Guillochain', '18', '7012', 'Mons', 17),
(52, 'Marche-en-Famenne Cracks', 'Rue Victor Libert ', '36', '6900', 'Marche-en-Famenne', 17),
(53, 'Jemeppe White Sharks', 'Avenue des Lilas', NULL, '5190', 'Jemeppe-sur-Sambre', 17),
(54, 'Royal Antwerp Eagles', 'Eglantierlaan- Varenlaan', NULL, '2000', 'Antwerpen', 17),
(55, 'Hoboken Pioneers', 'Schansstraat', '1', '2660', 'Hoboken', 17),
(56, 'Merksem Royal Greys', 'Alkstraat', '57', '2170', 'Merksem', 17),
(57, 'K. Borgerhout Squirrels', 'Vosstraat', NULL, '2140', 'Borgerhout', 17),
(58, 'K. Deurne Spartans', 'Ruimtevaartlaan', '22', '2100', 'Deurne', 17),
(59, 'K. Mortsel Stars', 'Krijgsbaan naast viaduct (vliegveld)', NULL, '2640', 'Mortsel', 17),
(60, 'Brasschaat Braves BSC', 'Bijsterveld', '3', '2930', 'Brasschaat', 17),
(61, 'Berendrecht Bears', 'Steenhovenstraat – Konijnendreef', NULL, '2000', 'Antwerpen', 17),
(62, 'Beveren Lions', 'Klapperbeekstraat (naast zwembad)', NULL, '9120', 'Beveren', 17),
(63, 'Stabroek Chicaboo\'s', 'Hoogeind (Vossenvelden)', NULL, '2940', 'Stabroek', 17),
(64, 'Gent Knights', 'Oude Scheldeweg', '2', '9050', 'Gentbrugge', 17),
(65, 'Merchtem Cats', 'Brusselsesteenweg t.h.v. ', '57', '1785', 'Merchtem', 17),
(66, 'Zonhoven Sunville Tigers', 'Muizenstraat', NULL, '3520', 'Zonhoven', 17),
(67, 'Zottegem Bebops', 'Zwembadstraat ', NULL, '9620', 'Zottegem', 17),
(68, 'Leuven Twins', 'Tervuursevest', '101', '3001', 'Heverlee', 17),
(69, 'Wielsbeke Pitbulls', 'Loverstraat', NULL, '8710', 'Wielsbeke', 17),
(70, 'Oostende Piranhas', 'Schorredijk ', NULL, '8400', 'Oostende', 17),
(71, 'Heist o/d Berg Afterburners', 'Kloosterveldstraat', NULL, '2221', 'Booischot', 17),
(72, 'Tongeren Sharks', 'Luikersteenweg', '154', '3700', 'Tongeren', 17),
(73, 'Oudenaarde Frogs', 'Rodelos', '4', '9700', 'Oudenaarde', 17),
(74, 'Terneuzen Het Zeeuwse Honk', 'Vliegende Vaart', '11', '4530', 'Terneuzen', 124),
(75, 'Limburg White Sox ', 'Herkenrodestraat', NULL, '3600', 'Genk', 17),
(76, 'Pelt Nstars', 'Ralleylaan', '3', '3910', 'Neerpelt', 17),
(77, 'Olen Titans', 'Missestraat', NULL, '2250', 'Olen', 17),
(78, 'WBA Foxes', 'Modernadreef', NULL, '9100', 'Sint-Niklaas', 17),
(79, 'Poperinge The Frontliners', 'Reningelstseweg', '18', '8970', 'Poperinge', 17),
(80, 'Test', 'Rue du paradis 21', '21', '4000', 'Liège', 17),
(81, 'Liège 2', 'Voie mélotte', '32', '4030', 'Grivegnée', 17);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `place`
--
ALTER TABLE `place`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_741D53CD81B2B6EE` (`address_country_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `place`
--
ALTER TABLE `place`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `place`
--
ALTER TABLE `place`
  ADD CONSTRAINT `FK_741D53CD81B2B6EE` FOREIGN KEY (`address_country_id`) REFERENCES `country` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

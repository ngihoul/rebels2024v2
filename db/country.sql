-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : ven. 24 mai 2024 à 12:37
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
-- Structure de la table `country`
--

-- Déchargement des données de la table `country`
--

INSERT INTO `country` (`id`, `alpha2`, `name`) VALUES
(1, 'af', 'Afghanistan'),
(2, 'al', 'Albanie'),
(3, 'dz', 'Algérie'),
(4, 'ad', 'Andorre'),
(5, 'ao', 'Angola'),
(6, 'ag', 'Antigua-et-Barbuda'),
(7, 'ar', 'Argentine'),
(8, 'am', 'Arménie'),
(9, 'au', 'Australie'),
(10, 'at', 'Autriche'),
(11, 'az', 'Azerbaïdjan'),
(12, 'bs', 'Bahamas'),
(13, 'bh', 'Bahreïn'),
(14, 'bd', 'Bangladesh'),
(15, 'bb', 'Barbade'),
(16, 'by', 'Biélorussie'),
(17, 'be', 'Belgique'),
(18, 'bz', 'Belize'),
(19, 'bj', 'Bénin'),
(20, 'bt', 'Bhoutan'),
(21, 'bo', 'Bolivie'),
(22, 'ba', 'Bosnie-Herzégovine'),
(23, 'bw', 'Botswana'),
(24, 'br', 'Brésil'),
(25, 'bn', 'Brunei'),
(26, 'bg', 'Bulgarie'),
(27, 'bf', 'Burkina Faso'),
(28, 'bi', 'Burundi'),
(29, 'kh', 'Cambodge'),
(30, 'cm', 'Cameroun'),
(31, 'ca', 'Canada'),
(32, 'cv', 'Cap-Vert'),
(33, 'cf', 'République centrafricaine'),
(34, 'td', 'Tchad'),
(35, 'cl', 'Chili'),
(36, 'cn', 'Chine'),
(37, 'co', 'Colombie'),
(38, 'km', 'Comores'),
(39, 'cg', 'République du Congo'),
(40, 'cd', 'République démocratique du Congo'),
(41, 'cr', 'Costa Rica'),
(42, 'ci', 'Côte d\'Ivoire'),
(43, 'hr', 'Croatie'),
(44, 'cu', 'Cuba'),
(45, 'cy', 'Chypre'),
(46, 'cz', 'Tchéquie'),
(47, 'dk', 'Danemark'),
(48, 'dj', 'Djibouti'),
(49, 'dm', 'Dominique'),
(50, 'do', 'République dominicaine'),
(51, 'ec', 'Équateur'),
(52, 'eg', 'Égypte'),
(53, 'sv', 'Salvador'),
(54, 'gq', 'Guinée équatoriale'),
(55, 'er', 'Érythrée'),
(56, 'ee', 'Estonie'),
(57, 'et', 'Éthiopie'),
(58, 'fj', 'Fidji'),
(59, 'fi', 'Finlande'),
(60, 'fr', 'France'),
(61, 'ga', 'Gabon'),
(62, 'gm', 'Gambie'),
(63, 'ge', 'Géorgie'),
(64, 'de', 'Allemagne'),
(65, 'gh', 'Ghana'),
(66, 'gr', 'Grèce'),
(67, 'gd', 'Grenade'),
(68, 'gt', 'Guatemala'),
(69, 'gn', 'Guinée'),
(70, 'gw', 'Guinée-Bissau'),
(71, 'gy', 'Guyana'),
(72, 'ht', 'Haïti'),
(73, 'hn', 'Honduras'),
(74, 'hu', 'Hongrie'),
(75, 'is', 'Islande'),
(76, 'in', 'Inde'),
(77, 'id', 'Indonésie'),
(78, 'ir', 'Iran'),
(79, 'iq', 'Irak'),
(80, 'ie', 'Irlande'),
(81, 'il', 'Israël'),
(82, 'it', 'Italie'),
(83, 'jm', 'Jamaïque'),
(84, 'jp', 'Japon'),
(85, 'jo', 'Jordanie'),
(86, 'kz', 'Kazakhstan'),
(87, 'ke', 'Kenya'),
(88, 'ki', 'Kiribati'),
(89, 'kp', 'Corée du Nord'),
(90, 'kr', 'Corée du Sud'),
(91, 'kw', 'Koweït'),
(92, 'kg', 'Kirghizistan'),
(93, 'la', 'Laos'),
(94, 'lv', 'Lettonie'),
(95, 'lb', 'Liban'),
(96, 'ls', 'Lesotho'),
(97, 'lr', 'Liberia'),
(98, 'ly', 'Libye'),
(99, 'li', 'Liechtenstein'),
(100, 'lt', 'Lituanie'),
(101, 'lu', 'Luxembourg'),
(102, 'mk', 'Macédoine du Nord'),
(103, 'mg', 'Madagascar'),
(104, 'mw', 'Malawi'),
(105, 'my', 'Malaisie'),
(106, 'mv', 'Maldives'),
(107, 'ml', 'Mali'),
(108, 'mt', 'Malte'),
(109, 'mh', 'Îles Marshall'),
(110, 'mr', 'Mauritanie'),
(111, 'mu', 'Maurice'),
(112, 'mx', 'Mexique'),
(113, 'fm', 'États fédérés de Micronésie'),
(114, 'ma', 'Maroc'),
(115, 'md', 'Moldavie'),
(116, 'mc', 'Monaco'),
(117, 'mn', 'Mongolie'),
(118, 'me', 'Monténégro'),
(119, 'mz', 'Mozambique'),
(120, 'mm', 'Birmanie'),
(121, 'na', 'Namibie'),
(122, 'nr', 'Nauru'),
(123, 'np', 'Népal'),
(124, 'nl', 'Pays-Bas'),
(125, 'nz', 'Nouvelle-Zélande'),
(126, 'ni', 'Nicaragua'),
(127, 'ne', 'Niger'),
(128, 'ng', 'Nigeria'),
(129, 'no', 'Norvège'),
(130, 'om', 'Oman'),
(131, 'pk', 'Pakistan'),
(132, 'pw', 'Palaos'),
(133, 'pa', 'Panama'),
(134, 'pg', 'Papouasie-Nouvelle-Guinée'),
(135, 'py', 'Paraguay'),
(136, 'pe', 'Pérou'),
(137, 'ph', 'Philippines'),
(138, 'pl', 'Pologne'),
(139, 'pt', 'Portugal'),
(140, 'qa', 'Qatar'),
(141, 'ro', 'Roumanie'),
(142, 'ru', 'Russie'),
(143, 'rw', 'Rwanda'),
(144, 'kn', 'Saint-Christophe-et-Niévès'),
(145, 'lc', 'Sainte-Lucie'),
(146, 'vc', 'Saint-Vincent-et-les-Grenadines'),
(147, 'ws', 'Samoa'),
(148, 'sm', 'Saint-Marin'),
(149, 'st', 'Sao Tomé-et-Principe'),
(150, 'sa', 'Arabie saoudite'),
(151, 'sn', 'Sénégal'),
(152, 'rs', 'Serbie'),
(153, 'sc', 'Seychelles'),
(154, 'sl', 'Sierra Leone'),
(155, 'sg', 'Singapour'),
(156, 'sk', 'Slovaquie'),
(157, 'si', 'Slovénie'),
(158, 'sb', 'Îles Salomon'),
(159, 'so', 'Somalie'),
(160, 'za', 'Afrique du Sud'),
(161, 'ss', 'Soudan du Sud'),
(162, 'es', 'Espagne'),
(163, 'lk', 'Sri Lanka'),
(164, 'sd', 'Soudan'),
(165, 'sr', 'Suriname'),
(166, 'sz', 'Eswatini'),
(167, 'se', 'Suède'),
(168, 'ch', 'Suisse'),
(169, 'sy', 'Syrie'),
(170, 'tj', 'Tadjikistan'),
(171, 'tz', 'Tanzanie'),
(172, 'th', 'Thaïlande'),
(173, 'tl', 'Timor oriental'),
(174, 'tg', 'Togo'),
(175, 'to', 'Tonga'),
(176, 'tt', 'Trinité-et-Tobago'),
(177, 'tn', 'Tunisie'),
(178, 'tr', 'Turquie'),
(179, 'tm', 'Turkménistan'),
(180, 'tv', 'Tuvalu'),
(181, 'ug', 'Ouganda'),
(182, 'ua', 'Ukraine'),
(183, 'ae', 'Émirats arabes unis'),
(184, 'gb', 'Royaume-Uni'),
(185, 'us', 'États-Unis'),
(186, 'uy', 'Uruguay'),
(187, 'uz', 'Ouzbékistan'),
(188, 'vu', 'Vanuatu'),
(189, 've', 'Venezuela'),
(190, 'vn', 'Viêt Nam'),
(191, 'ye', 'Yémen'),
(192, 'zm', 'Zambie'),
(193, 'zw', 'Zimbabwe');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `country`
--
ALTER TABLE `country`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

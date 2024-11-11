-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : ven. 24 mai 2024 à 14:42
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
-- Structure de la table `ext_translations`
--


--
-- Déchargement des données de la table `ext_translations`
--

INSERT INTO `ext_translations` (`id`, `locale`, `object_class`, `field`, `foreign_key`, `content`) VALUES
(1, 'en', 'App\\Entity\\Country', 'name', '2', 'Albania'),
(2, 'en', 'App\\Entity\\Country', 'name', '3', 'Algeria'),
(3, 'en', 'App\\Entity\\Country', 'name', '4', 'Andorra'),
(4, 'en', 'App\\Entity\\Country', 'name', '6', 'Antigua and Barbuda'),
(5, 'en', 'App\\Entity\\Country', 'name', '7', 'Argentina'),
(6, 'en', 'App\\Entity\\Country', 'name', '8', 'Armenia'),
(7, 'en', 'App\\Entity\\Country', 'name', '9', 'Australia'),
(8, 'en', 'App\\Entity\\Country', 'name', '10', 'Austria'),
(9, 'en', 'App\\Entity\\Country', 'name', '11', 'Azerbaijan'),
(10, 'en', 'App\\Entity\\Country', 'name', '13', 'Bahrain'),
(11, 'en', 'App\\Entity\\Country', 'name', '15', 'Barbados'),
(12, 'en', 'App\\Entity\\Country', 'name', '16', 'Belarus'),
(13, 'en', 'App\\Entity\\Country', 'name', '17', 'Belgium'),
(14, 'en', 'App\\Entity\\Country', 'name', '19', 'Benin'),
(15, 'en', 'App\\Entity\\Country', 'name', '20', 'Bhutan'),
(16, 'en', 'App\\Entity\\Country', 'name', '21', 'Bolivia (Plurinational State of)'),
(17, 'en', 'App\\Entity\\Country', 'name', '22', 'Bosnia and Herzegovina'),
(18, 'en', 'App\\Entity\\Country', 'name', '24', 'Brazil'),
(19, 'en', 'App\\Entity\\Country', 'name', '25', 'Brunei Darussalam'),
(20, 'en', 'App\\Entity\\Country', 'name', '26', 'Bulgaria'),
(21, 'en', 'App\\Entity\\Country', 'name', '29', 'Cambodia'),
(22, 'en', 'App\\Entity\\Country', 'name', '30', 'Cameroon'),
(23, 'en', 'App\\Entity\\Country', 'name', '32', 'Cabo Verde'),
(24, 'en', 'App\\Entity\\Country', 'name', '33', 'Central African Republic'),
(25, 'en', 'App\\Entity\\Country', 'name', '34', 'Chad'),
(26, 'en', 'App\\Entity\\Country', 'name', '35', 'Chile'),
(27, 'en', 'App\\Entity\\Country', 'name', '36', 'China'),
(28, 'en', 'App\\Entity\\Country', 'name', '37', 'Colombia'),
(29, 'en', 'App\\Entity\\Country', 'name', '38', 'Comoros'),
(30, 'en', 'App\\Entity\\Country', 'name', '39', 'Congo'),
(31, 'en', 'App\\Entity\\Country', 'name', '40', 'Congo, Democratic Republic of the'),
(32, 'en', 'App\\Entity\\Country', 'name', '43', 'Croatia'),
(33, 'en', 'App\\Entity\\Country', 'name', '45', 'Cyprus'),
(34, 'en', 'App\\Entity\\Country', 'name', '46', 'Czechia'),
(35, 'en', 'App\\Entity\\Country', 'name', '47', 'Denmark'),
(36, 'en', 'App\\Entity\\Country', 'name', '49', 'Dominica'),
(37, 'en', 'App\\Entity\\Country', 'name', '50', 'Dominican Republic'),
(38, 'en', 'App\\Entity\\Country', 'name', '51', 'Ecuador'),
(39, 'en', 'App\\Entity\\Country', 'name', '52', 'Egypt'),
(40, 'en', 'App\\Entity\\Country', 'name', '53', 'El Salvador'),
(41, 'en', 'App\\Entity\\Country', 'name', '54', 'Equatorial Guinea'),
(42, 'en', 'App\\Entity\\Country', 'name', '55', 'Eritrea'),
(43, 'en', 'App\\Entity\\Country', 'name', '56', 'Estonia'),
(44, 'en', 'App\\Entity\\Country', 'name', '57', 'Ethiopia'),
(45, 'en', 'App\\Entity\\Country', 'name', '58', 'Fiji'),
(46, 'en', 'App\\Entity\\Country', 'name', '59', 'Finland'),
(47, 'en', 'App\\Entity\\Country', 'name', '62', 'Gambia'),
(48, 'en', 'App\\Entity\\Country', 'name', '63', 'Georgia'),
(49, 'en', 'App\\Entity\\Country', 'name', '64', 'Germany'),
(50, 'en', 'App\\Entity\\Country', 'name', '66', 'Greece'),
(51, 'en', 'App\\Entity\\Country', 'name', '67', 'Grenada'),
(52, 'en', 'App\\Entity\\Country', 'name', '69', 'Guinea'),
(53, 'en', 'App\\Entity\\Country', 'name', '70', 'Guinea-Bissau'),
(54, 'en', 'App\\Entity\\Country', 'name', '72', 'Haiti'),
(55, 'en', 'App\\Entity\\Country', 'name', '74', 'Hungary'),
(56, 'en', 'App\\Entity\\Country', 'name', '75', 'Iceland'),
(57, 'en', 'App\\Entity\\Country', 'name', '76', 'India'),
(58, 'en', 'App\\Entity\\Country', 'name', '77', 'Indonesia'),
(59, 'en', 'App\\Entity\\Country', 'name', '78', 'Iran (Islamic Republic of)'),
(60, 'en', 'App\\Entity\\Country', 'name', '79', 'Iraq'),
(61, 'en', 'App\\Entity\\Country', 'name', '80', 'Ireland'),
(62, 'en', 'App\\Entity\\Country', 'name', '81', 'Israel'),
(63, 'en', 'App\\Entity\\Country', 'name', '82', 'Italy'),
(64, 'en', 'App\\Entity\\Country', 'name', '83', 'Jamaica'),
(65, 'en', 'App\\Entity\\Country', 'name', '84', 'Japan'),
(66, 'en', 'App\\Entity\\Country', 'name', '85', 'Jordan'),
(67, 'en', 'App\\Entity\\Country', 'name', '89', 'Korea (Democratic People\'s Republic of)'),
(68, 'en', 'App\\Entity\\Country', 'name', '90', 'Korea, Republic of'),
(69, 'en', 'App\\Entity\\Country', 'name', '91', 'Kuwait'),
(70, 'en', 'App\\Entity\\Country', 'name', '92', 'Kyrgyzstan'),
(71, 'en', 'App\\Entity\\Country', 'name', '93', 'Lao People\'s Democratic Republic'),
(72, 'en', 'App\\Entity\\Country', 'name', '94', 'Latvia'),
(73, 'en', 'App\\Entity\\Country', 'name', '95', 'Lebanon'),
(74, 'en', 'App\\Entity\\Country', 'name', '98', 'Libya'),
(75, 'en', 'App\\Entity\\Country', 'name', '100', 'Lithuania'),
(76, 'en', 'App\\Entity\\Country', 'name', '102', 'North Macedonia'),
(77, 'en', 'App\\Entity\\Country', 'name', '105', 'Malaysia'),
(78, 'en', 'App\\Entity\\Country', 'name', '108', 'Malta'),
(79, 'en', 'App\\Entity\\Country', 'name', '109', 'Marshall Islands'),
(80, 'en', 'App\\Entity\\Country', 'name', '110', 'Mauritania'),
(81, 'en', 'App\\Entity\\Country', 'name', '111', 'Mauritius'),
(82, 'en', 'App\\Entity\\Country', 'name', '112', 'Mexico'),
(83, 'en', 'App\\Entity\\Country', 'name', '113', 'Micronesia (Federated States of)'),
(84, 'en', 'App\\Entity\\Country', 'name', '114', 'Morocco'),
(85, 'en', 'App\\Entity\\Country', 'name', '115', 'Moldova, Republic of'),
(86, 'en', 'App\\Entity\\Country', 'name', '117', 'Mongolia'),
(87, 'en', 'App\\Entity\\Country', 'name', '118', 'Montenegro'),
(88, 'en', 'App\\Entity\\Country', 'name', '120', 'Myanmar'),
(89, 'en', 'App\\Entity\\Country', 'name', '121', 'Namibia'),
(90, 'en', 'App\\Entity\\Country', 'name', '123', 'Nepal'),
(91, 'en', 'App\\Entity\\Country', 'name', '124', 'Netherlands'),
(92, 'en', 'App\\Entity\\Country', 'name', '125', 'New Zealand'),
(93, 'en', 'App\\Entity\\Country', 'name', '129', 'Norway'),
(94, 'en', 'App\\Entity\\Country', 'name', '132', 'Palau'),
(95, 'en', 'App\\Entity\\Country', 'name', '134', 'Papua New Guinea'),
(96, 'en', 'App\\Entity\\Country', 'name', '136', 'Peru'),
(97, 'en', 'App\\Entity\\Country', 'name', '138', 'Poland'),
(98, 'en', 'App\\Entity\\Country', 'name', '141', 'Romania'),
(99, 'en', 'App\\Entity\\Country', 'name', '142', 'Russian Federation'),
(100, 'en', 'App\\Entity\\Country', 'name', '144', 'Saint Kitts and Nevis'),
(101, 'en', 'App\\Entity\\Country', 'name', '145', 'Saint Lucia'),
(102, 'en', 'App\\Entity\\Country', 'name', '146', 'Saint Vincent and the Grenadines'),
(103, 'en', 'App\\Entity\\Country', 'name', '148', 'San Marino'),
(104, 'en', 'App\\Entity\\Country', 'name', '149', 'Sao Tome and Principe'),
(105, 'en', 'App\\Entity\\Country', 'name', '150', 'Saudi Arabia'),
(106, 'en', 'App\\Entity\\Country', 'name', '151', 'Senegal'),
(107, 'en', 'App\\Entity\\Country', 'name', '152', 'Serbia'),
(108, 'en', 'App\\Entity\\Country', 'name', '155', 'Singapore'),
(109, 'en', 'App\\Entity\\Country', 'name', '156', 'Slovakia'),
(110, 'en', 'App\\Entity\\Country', 'name', '157', 'Slovenia'),
(111, 'en', 'App\\Entity\\Country', 'name', '158', 'Solomon Islands'),
(112, 'en', 'App\\Entity\\Country', 'name', '159', 'Somalia'),
(113, 'en', 'App\\Entity\\Country', 'name', '160', 'South Africa'),
(114, 'en', 'App\\Entity\\Country', 'name', '161', 'South Sudan'),
(115, 'en', 'App\\Entity\\Country', 'name', '162', 'Spain'),
(116, 'en', 'App\\Entity\\Country', 'name', '164', 'Sudan'),
(117, 'en', 'App\\Entity\\Country', 'name', '167', 'Sweden'),
(118, 'en', 'App\\Entity\\Country', 'name', '168', 'Switzerland'),
(119, 'en', 'App\\Entity\\Country', 'name', '169', 'Syrian Arab Republic'),
(120, 'en', 'App\\Entity\\Country', 'name', '170', 'Tajikistan'),
(121, 'en', 'App\\Entity\\Country', 'name', '171', 'Tanzania, United Republic of'),
(122, 'en', 'App\\Entity\\Country', 'name', '172', 'Thailand'),
(123, 'en', 'App\\Entity\\Country', 'name', '173', 'Timor-Leste'),
(124, 'en', 'App\\Entity\\Country', 'name', '176', 'Trinidad and Tobago'),
(125, 'en', 'App\\Entity\\Country', 'name', '177', 'Tunisia'),
(126, 'en', 'App\\Entity\\Country', 'name', '178', 'Türkiye'),
(127, 'en', 'App\\Entity\\Country', 'name', '179', 'Turkmenistan'),
(128, 'en', 'App\\Entity\\Country', 'name', '181', 'Uganda'),
(129, 'en', 'App\\Entity\\Country', 'name', '183', 'United Arab Emirates'),
(130, 'en', 'App\\Entity\\Country', 'name', '184', 'United Kingdom of Great Britain and Northern Ireland'),
(131, 'en', 'App\\Entity\\Country', 'name', '185', 'United States of America'),
(132, 'en', 'App\\Entity\\Country', 'name', '187', 'Uzbekistan'),
(133, 'en', 'App\\Entity\\Country', 'name', '189', 'Venezuela (Bolivarian Republic of)'),
(134, 'en', 'App\\Entity\\Country', 'name', '190', 'Viet Nam'),
(135, 'en', 'App\\Entity\\Country', 'name', '191', 'Yemen'),
(136, 'en', 'App\\Entity\\Country', 'name', '192', 'Zambia'),
(137, 'en', 'App\\Entity\\EventCategory', 'name', '1', 'Training'),
(138, 'en', 'App\\Entity\\EventCategory', 'name', '2', 'Game'),
(139, 'en', 'App\\Entity\\EventCategory', 'name', '3', 'Tournament'),
(140, 'en', 'App\\Entity\\EventCategory', 'name', '4', 'Party'),
(141, 'en', 'App\\Entity\\EventCategory', 'name', '5', 'Other'),
(142, 'en', 'App\\Entity\\LicenseCategory', 'name', '1', 'Player'),
(143, 'en', 'App\\Entity\\LicenseCategory', 'name', '2', 'Club official'),
(144, 'en', 'App\\Entity\\LicenseCategory', 'name', '3', 'Official'),
(145, 'en', 'App\\Entity\\LicenseSubCategory', 'name', '4', 'Recreational'),
(146, 'en', 'App\\Entity\\LicenseSubCategory', 'name', '7', 'Coach Level 1 - Level 2'),
(147, 'en', 'App\\Entity\\LicenseSubCategory', 'name', '8', 'Supporter'),
(148, 'en', 'App\\Entity\\LicenseSubCategory', 'name', '9', 'Administrator'),
(149, 'en', 'App\\Entity\\LicenseSubCategory', 'name', '10', 'Federal referee'),
(150, 'en', 'App\\Entity\\LicenseSubCategory', 'name', '11', 'Regional referee'),
(151, 'en', 'App\\Entity\\LicenseSubCategory', 'name', '12', 'Federal scorer'),
(152, 'en', 'App\\Entity\\LicenseSubCategory', 'name', '13', 'Regional scorer'),
(153, 'en', 'App\\Entity\\RelationType', 'name', '2', 'Legal representative'),
(154, 'en', 'App\\Entity\\RelationType', 'name', '3', 'Brother/Sister');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ext_translations`
--
ALTER TABLE `ext_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lookup_unique_idx` (`locale`,`object_class`,`field`,`foreign_key`),
  ADD KEY `translations_lookup_idx` (`locale`,`object_class`,`foreign_key`),
  ADD KEY `general_translations_lookup_idx` (`object_class`,`foreign_key`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ext_translations`
--
ALTER TABLE `ext_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

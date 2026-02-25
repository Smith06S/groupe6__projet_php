-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3006
-- Généré le : mer. 25 fév. 2026 à 23:05
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
-- Base de données : `php_exam_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `date_publication` datetime DEFAULT current_timestamp(),
  `auteur_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `genre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `nom`, `description`, `prix`, `date_publication`, `auteur_id`, `image_url`, `type`, `genre`) VALUES
(1, 'Vinyle 24k magic - Bruno Mars', 'Vinyle de l\'album 24k magic de Bruno Mars', 79.99, '2026-02-24 00:00:00', 1, 'https://imusic.b-cdn.net/images/item/original/340/0075678626340.jpg?bruno-mars-2023-24k-magic-clear-vinyl-lp&class=scaled&v=1704893802', '', ''),
(2, 'Vinyle Thriller - Michael Jackson', 'Vinyle de l\'album Thriller de Michael Jackson', 109.99, '2026-02-24 00:00:00', 1, 'https://vinylcollector.store/cdn/shop/files/Small_SS_A_Side_c9eedfbe-ac0d-4a63-8a6e-16157781dcf0.png?v=1718122871&width=1080', '', ''),
(3, 'Vinyle Bohemian Rhapsody - Queens', 'Vinyle de l\'album Bohemian Rhapsody de Queens Edition du 50ème anniversaire', 99.99, '2026-02-24 00:00:00', 1, 'https://www.blackvinylrecordsspain.com/cdn/shop/files/0602475946366.jpg?v=1758362664', '', ''),
(4, 'Vinyle The Album - Abba', 'Vinyle de The Album de Abba', 97.99, '2026-02-24 00:00:00', 1, 'https://www.umusicstore.co.id/cdn/shop/files/ABBATheAlbum_1200x1200.png?v=1711175897', '', ''),
(5, 'Vinyle Tour Collection - Ed Sheeran', 'Vinyle de Tour Collection de Ed Sheeran', 69.99, '2026-02-24 00:00:00', 1, 'https://static.fnac-static.com/multimedia/Images/FR/NR/37/8d/16/18255159/1507-1/tsp20240916164317/TOUR-COLLECTION-Vinyle-Bleu.jpg', '', ''),
(6, 'Vinyle Love Goes - Sam Smith', 'Vinyle de l\'album Love Goes de Sam Smith', 56.99, '2026-02-24 00:00:00', 1, 'https://drownedworldrecords.com/cdn/shop/products/Sam-Smith---Love-Goes-_2LP_-Vinyl-1664535531.jpg', '', ''),
(7, 'Vinyle The Four Seasons - Vivaldi', 'Vinyle de l\'album The Four Seasons de Vivaldi', 79.99, '2026-02-24 00:00:00', 1, 'https://m.media-amazon.com/images/I/81zMu3cG+UL._UF894,1000_QL80_.jpg', '', ''),
(8, 'Vinyle Classical Music Masterpieces Vol. 1 - Mozart', 'Vinyle de Classical Music Masterpieces Vol. 1 de Mozart', 89.99, '2026-02-24 00:00:00', 1, 'https://m.media-amazon.com/images/I/71uDyaqcztL.jpg', '', ''),
(9, 'Vinyle Symphonie Nr.6 - Beethoven', 'Vinyle de Symphonie Nr.6 de Beethoven', 69.99, '2026-02-24 00:00:00', 1, 'https://static.fnac-static.com/multimedia/Images/FR/NR/e2/70/0d/17658082/1507-1/tsp20240327121122/Beethoven-Symphony-Number-6-In-F-Major-Opus-68-Pastorale-Edition-Limitee-et-Numerotee.jpg', '', ''),
(10, 'Vinyle Clair Obscur : Expedition 33', 'Vinyle de la bande originale du jeu vidéo Clair Obscur : Expedition 33', 39.99, '2026-02-24 00:00:00', 1, 'https://m.media-amazon.com/images/I/81Iq9MZ82SL._UF894,1000_QL80_.jpg', '', ''),
(11, 'Vinyle Bande Originale - Fairy Tail', 'Vinyle de la Bande Originale de l\'anime Fairy Tail.', 49.99, '2026-02-25 20:46:07', 1, 'https://edition-collector-production.s3.amazonaws.com/uploads/image/file/75221/Fairy-Tail-Bande-Originale-%C3%89dition-Limit%C3%A9e-double-vinyle-color%C3%A9.jpg', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `montant` decimal(10,2) NOT NULL,
  `facturation_address` varchar(255) DEFAULT NULL,
  `facturation_city` varchar(100) DEFAULT NULL,
  `facturation_zip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `invoice_item`
--

CREATE TABLE `invoice_item` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `article_id`, `quantite`) VALUES
(1, 11, 1),
(2, 1, 1),
(3, 2, 1),
(4, 3, 1),
(5, 4, 1),
(6, 5, 1),
(7, 6, 1),
(8, 7, 1),
(9, 8, 1),
(10, 9, 1),
(11, 10, 1);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `solde` decimal(10,2) DEFAULT 0.00,
  `photo_profil` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `mdp`, `mail`, `solde`, `photo_profil`, `role`) VALUES
(1, 'admin', '$2y$10$qkbWq.v.n2Jrk5z/tkc9jOEHPnofaaApfK/M9utgXf4/0zhQXPJS2', 'admin@mail.com', 133.00, 'https://t3.ftcdn.net/jpg/06/19/26/46/360_F_619264680_x2PBdGLF54sFe7kTBtAvZnPyXgvaRw0Y.jpg', 'admin'),
(5, 'Xelea', '$2y$10$XgcBp9BFAOl2YBDhmyLq5eeeaQYWBh9xRcKosfJfRrvnBNmIp2xPO', 'xelea@gmail.com', 0.00, NULL, 'user');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur_id` (`auteur_id`);

--
-- Index pour la table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_username` (`username`),
  ADD UNIQUE KEY `uniq_mail` (`mail`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Contraintes pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD CONSTRAINT `invoice_item_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`),
  ADD CONSTRAINT `invoice_item_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

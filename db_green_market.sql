-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : jeu. 07 mai 2026 à 03:30
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
-- Base de données : `green_market`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `ID_Avis` int(11) NOT NULL,
  `Note` int(11) DEFAULT NULL,
  `Commentaire` text DEFAULT NULL,
  `Date_Avis` date DEFAULT NULL,
  `ID_Rep` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `boutique`
--

CREATE TABLE `boutique` (
  `ID_boutique` int(11) NOT NULL,
  `nom_boutique` varchar(100) DEFAULT NULL,
  `description_Bout` text DEFAULT NULL,
  `ID_Prod` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `ID_Categ` int(11) NOT NULL,
  `nom_Categ` varchar(100) DEFAULT NULL,
  `description_Categ` text DEFAULT NULL,
  `ID_Prod` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `ID_Com` int(11) NOT NULL,
  `Date_com` date DEFAULT NULL,
  `Status_com` varchar(20) DEFAULT NULL,
  `Prix_Total` decimal(8,2) DEFAULT NULL,
  `ID_Reclam` int(11) DEFAULT NULL,
  `ID_Pay` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ligne_commande`
--

CREATE TABLE `ligne_commande` (
  `ID_Com` int(11) NOT NULL,
  `ID_Prod` int(11) NOT NULL,
  `Quantite` int(11) DEFAULT NULL,
  `Prix_Unitaire` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ligne_panier`
--

CREATE TABLE `ligne_panier` (
  `ID_Panier` int(11) NOT NULL,
  `ID_Prod` int(11) NOT NULL,
  `Quantite` int(11) DEFAULT NULL,
  `Prix_Unitaire` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `ID_Noti` int(11) NOT NULL,
  `msg_noti` text DEFAULT NULL,
  `Date_noti` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

CREATE TABLE `panier` (
  `ID_Panier` int(11) NOT NULL,
  `ID_utili` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `payement`
--

CREATE TABLE `payement` (
  `ID_Pay` int(11) NOT NULL,
  `Mode_pay` varchar(10) DEFAULT NULL,
  `Montant` decimal(8,2) DEFAULT NULL,
  `Date_pay` date DEFAULT NULL,
  `Facture` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `ID_Prod` int(11) NOT NULL,
  `nom_Prod` varchar(100) DEFAULT NULL,
  `Image` text DEFAULT NULL,
  `Prix` decimal(8,2) DEFAULT NULL,
  `Stock` int(11) DEFAULT NULL,
  `ID_Avis` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reclamation`
--

CREATE TABLE `reclamation` (
  `ID_Reclam` int(11) NOT NULL,
  `Descrip_reclam` text DEFAULT NULL,
  `Date_reclam` date DEFAULT NULL,
  `Status_reclam` varchar(20) DEFAULT NULL,
  `ID_Rep` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reponse`
--

CREATE TABLE `reponse` (
  `ID_Rep` int(11) NOT NULL,
  `msg_rep` text DEFAULT NULL,
  `Date_rep` date DEFAULT NULL,
  `Type_rep` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `ID_utili` int(11) NOT NULL,
  `nom_utili` varchar(50) DEFAULT NULL,
  `prenom_utili` varchar(50) DEFAULT NULL,
  `Email_utili` varchar(250) DEFAULT NULL,
  `Adresse` text DEFAULT NULL,
  `mot_de_pass` varchar(250) DEFAULT NULL,
  `Role` varchar(20) DEFAULT NULL,
  `Est_active` tinyint(1) DEFAULT NULL,
  `ID_Com` int(11) DEFAULT NULL,
  `ID_Avis` int(11) DEFAULT NULL,
  `ID_Noti` int(11) DEFAULT NULL,
  `ID_boutique` int(11) DEFAULT NULL,
  `ID_Reclam` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`ID_Avis`),
  ADD KEY `ID_Rep` (`ID_Rep`);

--
-- Index pour la table `boutique`
--
ALTER TABLE `boutique`
  ADD PRIMARY KEY (`ID_boutique`),
  ADD KEY `ID_Prod` (`ID_Prod`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`ID_Categ`),
  ADD KEY `ID_Prod` (`ID_Prod`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`ID_Com`),
  ADD KEY `ID_Reclam` (`ID_Reclam`),
  ADD KEY `ID_Pay` (`ID_Pay`);

--
-- Index pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD PRIMARY KEY (`ID_Com`,`ID_Prod`),
  ADD KEY `ID_Prod` (`ID_Prod`);

--
-- Index pour la table `ligne_panier`
--
ALTER TABLE `ligne_panier`
  ADD PRIMARY KEY (`ID_Panier`,`ID_Prod`),
  ADD KEY `ID_Prod` (`ID_Prod`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`ID_Noti`);

--
-- Index pour la table `panier`
--
ALTER TABLE `panier`
  ADD PRIMARY KEY (`ID_Panier`),
  ADD KEY `ID_utili` (`ID_utili`);

--
-- Index pour la table `payement`
--
ALTER TABLE `payement`
  ADD PRIMARY KEY (`ID_Pay`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`ID_Prod`),
  ADD KEY `ID_Avis` (`ID_Avis`);

--
-- Index pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`ID_Reclam`),
  ADD KEY `ID_Rep` (`ID_Rep`);

--
-- Index pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD PRIMARY KEY (`ID_Rep`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`ID_utili`),
  ADD UNIQUE KEY `Email_utili` (`Email_utili`),
  ADD KEY `ID_Com` (`ID_Com`),
  ADD KEY `ID_Avis` (`ID_Avis`),
  ADD KEY `ID_Noti` (`ID_Noti`),
  ADD KEY `ID_boutique` (`ID_boutique`),
  ADD KEY `ID_Reclam` (`ID_Reclam`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `ID_Avis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `boutique`
--
ALTER TABLE `boutique`
  MODIFY `ID_boutique` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `ID_Categ` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `ID_Com` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `ID_Noti` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `panier`
--
ALTER TABLE `panier`
  MODIFY `ID_Panier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `payement`
--
ALTER TABLE `payement`
  MODIFY `ID_Pay` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `ID_Prod` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reclamation`
--
ALTER TABLE `reclamation`
  MODIFY `ID_Reclam` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reponse`
--
ALTER TABLE `reponse`
  MODIFY `ID_Rep` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `ID_utili` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`ID_Rep`) REFERENCES `reponse` (`ID_Rep`);

--
-- Contraintes pour la table `boutique`
--
ALTER TABLE `boutique`
  ADD CONSTRAINT `boutique_ibfk_1` FOREIGN KEY (`ID_Prod`) REFERENCES `produit` (`ID_Prod`);

--
-- Contraintes pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD CONSTRAINT `categorie_ibfk_1` FOREIGN KEY (`ID_Prod`) REFERENCES `produit` (`ID_Prod`);

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`ID_Reclam`) REFERENCES `reclamation` (`ID_Reclam`),
  ADD CONSTRAINT `commande_ibfk_2` FOREIGN KEY (`ID_Pay`) REFERENCES `payement` (`ID_Pay`);

--
-- Contraintes pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD CONSTRAINT `ligne_commande_ibfk_1` FOREIGN KEY (`ID_Com`) REFERENCES `commande` (`ID_Com`),
  ADD CONSTRAINT `ligne_commande_ibfk_2` FOREIGN KEY (`ID_Prod`) REFERENCES `produit` (`ID_Prod`);

--
-- Contraintes pour la table `ligne_panier`
--
ALTER TABLE `ligne_panier`
  ADD CONSTRAINT `ligne_panier_ibfk_1` FOREIGN KEY (`ID_Panier`) REFERENCES `panier` (`ID_Panier`),
  ADD CONSTRAINT `ligne_panier_ibfk_2` FOREIGN KEY (`ID_Prod`) REFERENCES `produit` (`ID_Prod`);

--
-- Contraintes pour la table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`ID_utili`) REFERENCES `utilisateur` (`ID_utili`);

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1` FOREIGN KEY (`ID_Avis`) REFERENCES `avis` (`ID_Avis`);

--
-- Contraintes pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD CONSTRAINT `reclamation_ibfk_1` FOREIGN KEY (`ID_Rep`) REFERENCES `reponse` (`ID_Rep`);

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`ID_Com`) REFERENCES `commande` (`ID_Com`),
  ADD CONSTRAINT `utilisateur_ibfk_2` FOREIGN KEY (`ID_Avis`) REFERENCES `avis` (`ID_Avis`),
  ADD CONSTRAINT `utilisateur_ibfk_3` FOREIGN KEY (`ID_Noti`) REFERENCES `notification` (`ID_Noti`),
  ADD CONSTRAINT `utilisateur_ibfk_4` FOREIGN KEY (`ID_boutique`) REFERENCES `boutique` (`ID_boutique`),
  ADD CONSTRAINT `utilisateur_ibfk_5` FOREIGN KEY (`ID_Reclam`) REFERENCES `reclamation` (`ID_Reclam`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

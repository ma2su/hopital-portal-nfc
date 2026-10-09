/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.19-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: DPI
-- ------------------------------------------------------
-- Server version	10.11.19-MariaDB-ubu2204

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `DPI`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `DPI` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `DPI`;

--
-- Table structure for table `docteur`
--

DROP TABLE IF EXISTS `docteur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `docteur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(535) NOT NULL,
  `prenom` varchar(535) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docteur`
--

LOCK TABLES `docteur` WRITE;
/*!40000 ALTER TABLE `docteur` DISABLE KEYS */;
INSERT INTO `docteur` VALUES
(1,'MVULA','Sven'),
(2,'Dupont','Jean'),
(3,'Martin','Sophie'),
(4,'Bernard','Thomas'),
(5,'Petit','Claire'),
(6,'Robert','Nicolas'),
(7,'Richard','Julie'),
(8,'Durand','Antoine'),
(9,'Moreau','Camille');
/*!40000 ALTER TABLE `docteur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient`
--

DROP TABLE IF EXISTS `patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(535) NOT NULL,
  `prenom` varchar(535) NOT NULL,
  `maladie` varchar(535) NOT NULL,
  `chambre` int(11) NOT NULL,
  `docteur_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_patient_docteur` (`docteur_id`),
  CONSTRAINT `fk_patient_docteur` FOREIGN KEY (`docteur_id`) REFERENCES `docteur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient`
--

LOCK TABLES `patient` WRITE;
/*!40000 ALTER TABLE `patient` DISABLE KEYS */;
INSERT INTO `patient` VALUES
(1,'di gusto','gabin','tetanos',1,1),
(5,'ikando','Marilyn','Gourmandise',2,1),
(6,'Leroy','Lucas','Grippe',101,1),
(7,'Morel','Emma','Diabète',102,1),
(8,'Fournier','Hugo','Hypertension',103,1),
(9,'Girard','Chloé','Asthme',104,2),
(10,'Bonnet','Louis','Bronchite',105,2),
(11,'Dupuis','Manon','Migraine',106,2),
(12,'Lambert','Nathan','Pneumonie',107,3),
(13,'Fontaine','Léa','Angine',108,3),
(14,'Rousseau','Gabriel','Diabète',109,3),
(15,'Vincent','Inès','Grippe',110,4),
(16,'Muller','Ethan','Asthme',111,4),
(17,'Lefevre','Sarah','Hypertension',112,4),
(18,'Mercier','Tom','Bronchite',113,5),
(19,'Legrand','Alice','Migraine',114,5),
(20,'Gauthier','Paul','Pneumonie',115,5),
(21,'Garcia','Jade','Angine',116,6),
(22,'Perrin','Mathis','Diabète',117,6),
(23,'Robin','Louise','Grippe',118,6),
(24,'Clement','Arthur','Asthme',119,7),
(25,'Morin','Mia','Hypertension',120,7),
(26,'Nicolas','Enzo','Bronchite',121,7),
(27,'Henry','Eva','Migraine',122,8),
(28,'Roussel','Noah','Pneumonie',123,8),
(29,'Mathieu','Lina','Angine',124,8);
/*!40000 ALTER TABLE `patient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `role` varchar(50) NOT NULL DEFAULT 'docteur',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'mvula','password','2026-10-06 15:25:46','docteur'),
(2,'Dupont','1234','2026-10-06 21:37:11','administrateur'),
(3,'Martin','1234','2026-10-06 21:37:11','docteur'),
(4,'Bernard','1234','2026-10-06 21:37:11','docteur'),
(5,'Petit','1234','2026-10-06 21:37:11','docteur'),
(6,'Robert','1234','2026-10-06 21:37:11','docteur'),
(7,'Richard','1234','2026-10-06 21:37:11','docteur'),
(8,'Durand','1234','2026-10-06 21:37:11','docteur'),
(9,'Moreau','1234','2026-10-06 21:37:11','docteur'),
(10,'admin','admin','2026-10-06 22:15:02','docteur');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Current Database: `CONTROLE_ACCES`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `CONTROLE_ACCES` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `CONTROLE_ACCES`;

--
-- Table structure for table `acces`
--

DROP TABLE IF EXISTS `acces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `acces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `badge_id` int(11) NOT NULL,
  `date_heure` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_acces_badge` (`badge_id`),
  CONSTRAINT `fk_acces_badge` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acces`
--

LOCK TABLES `acces` WRITE;
/*!40000 ALTER TABLE `acces` DISABLE KEYS */;
INSERT INTO `acces` VALUES
(1,1,'2026-10-07 08:02:15'),
(2,1,'2026-10-07 12:14:32'),
(3,1,'2026-10-07 13:05:21'),
(4,1,'2026-10-07 17:42:08'),
(5,2,'2026-10-07 07:51:43'),
(6,2,'2026-10-07 12:03:12'),
(7,2,'2026-10-07 13:01:54'),
(8,3,'2026-10-06 09:15:27'),
(9,3,'2026-10-06 18:02:41'),
(10,4,'2026-10-07 08:23:11'),
(11,4,'2026-10-07 12:27:56'),
(12,4,'2026-10-07 13:16:04'),
(13,4,'2026-10-07 18:11:39'),
(14,5,'2026-09-29 08:14:22'),
(15,5,'2026-09-29 17:46:31'),
(16,6,'2026-10-01 10:05:44'),
(17,7,'2026-10-07 07:42:19'),
(18,7,'2026-10-07 12:01:33'),
(19,7,'2026-10-07 13:08:47'),
(20,7,'2026-10-07 19:02:15'),
(21,8,'2026-10-07 08:37:52'),
(22,8,'2026-10-07 12:10:26');
/*!40000 ALTER TABLE `acces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_badge` varchar(50) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `date_expiration` date NOT NULL,
  `statut` enum('actif','bloque') NOT NULL DEFAULT 'actif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_badge` (`numero_badge`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
INSERT INTO `badges` VALUES
(1,'BADGE-0001','Dupont','Jean','2026-10-06 22:35:26','2027-12-31','actif'),
(2,'BADGE-0002','Martin','Sophie','2026-10-06 22:35:26','2027-06-30','actif'),
(3,'BADGE-0003','Bernard','Thomas','2026-10-06 22:35:26','2026-11-30','bloque'),
(4,'BADGE-0004','Petit','Camille','2026-10-06 22:35:26','2028-01-31','actif'),
(5,'BADGE-0005','Robert','Lucas','2026-10-06 22:35:26','2026-09-30','actif'),
(6,'BADGE-0006','Richard','Emma','2026-10-06 22:35:26','2027-03-31','bloque'),
(7,'BADGE-0007','Durand','Nicolas','2026-10-06 22:35:26','2028-05-31','actif'),
(8,'BADGE-0008','Moreau','Chloé','2026-10-06 22:35:26','2027-10-31','actif');
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','admin');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-07  7:57:47

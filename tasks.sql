-- MariaDB dump 10.19  Distrib 10.7.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: givehub
-- ------------------------------------------------------
-- Server version	10.7.8-MariaDB-1:10.7.8+maria~deb11

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
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) DEFAULT NULL,
  `subcategory` varchar(255) DEFAULT NULL,
  `task_name` varchar(255) DEFAULT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `assignee_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tranche` varchar(25) NOT NULL DEFAULT '1',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES
(1,'Frontend Engineering','Setup & Configuration','Initialize project structure with global app object',1,1,'2025-02-26 22:30:50','1',NULL),
(2,'Frontend Engineering','Setup & Configuration','Set up build process and development environment',1,1,'2025-02-26 22:30:50','1',NULL),
(3,'Frontend Engineering','Setup & Configuration','Configure JWT handling utilities',1,1,'2025-02-26 22:30:50','1',NULL),
(4,'Frontend Engineering','Setup & Configuration','Implement local storage management',1,1,'2025-02-26 22:30:50','1',NULL),
(5,'Frontend Engineering','Registration Flow','Create registration form with validation',1,1,'2025-02-26 22:30:50','1',NULL),
(6,'Frontend Engineering','Registration Flow','Implement real-time field validation',1,1,'2025-02-26 22:30:50','1',NULL),
(7,'Frontend Engineering','Registration Flow','Add Google OAuth integration',1,1,'2025-02-26 22:30:50','1',NULL),
(8,'Frontend Engineering','Registration Flow','Build email verification UI',1,1,'2025-02-26 22:30:50','1',NULL),
(9,'Frontend Engineering','Registration Flow','Create success/error handling states',1,1,'2025-02-26 22:30:50','1',NULL),
(10,'Frontend Engineering','Login System','Build login form with validation',1,1,'2025-02-26 22:30:50','1',NULL),
(11,'Frontend Engineering','Login System','Implement session management',1,1,'2025-02-26 22:30:50','1',NULL),
(12,'Frontend Engineering','Login System','Add \"Remember Me\" functionality',1,1,'2025-02-26 22:30:50','1',NULL),
(13,'Frontend Engineering','Login System','Create password reset flow',1,1,'2025-02-28 13:17:40','1',''),
(14,'Frontend Engineering','Login System','Add login error handling',1,1,'2025-02-28 13:21:38','1',''),
(15,'Frontend Engineering','Profile Management','Create profile editor interface',1,1,'2025-03-25 23:06:52','1','Available in the main client app, under the \'Settings\' navigation item'),
(16,'Frontend Engineering','Profile Management','Implement avatar upload/cropping',1,1,'2025-03-25 23:06:05','1','Allows uploading during registration time or while editing your profile'),
(17,'Frontend Engineering','Profile Management','Add profile completion indicator',1,2,'2025-03-25 23:04:44','1','displayed at the bottom of the main app navigation'),
(18,'Frontend Engineering','Profile Management','Build contact information editor',1,1,'2025-02-26 22:30:50','1',NULL),
(19,'Frontend Engineering','Profile Management','Create profile validation system',1,1,'2025-03-06 18:43:24','1',NULL),
(20,'Frontend Engineering','Basic Structure','Create campaign form layout',1,1,'2025-02-26 22:30:50','1',NULL),
(21,'Frontend Engineering','Basic Structure','Implement form validation system',1,1,'2025-02-26 22:30:50','1',NULL),
(22,'Frontend Engineering','Basic Structure','Add auto-save functionality',1,1,'2025-02-28 12:50:50','1',NULL),
(23,'Frontend Engineering','Basic Structure','Build draft/preview toggle',1,2,'2025-03-04 19:42:01','1',NULL),
(24,'Frontend Engineering','Media Management','Create image upload interface',1,1,'2025-02-26 22:30:50','1',NULL),
(25,'Frontend Engineering','Media Management','Implement upload progress indicators',1,1,'2025-02-26 22:30:50','1',NULL),
(26,'Frontend Engineering','Media Management','Build media gallery management',1,1,'2025-03-05 10:07:10','1',NULL),
(27,'Frontend Engineering','Media Management','Add drag-and-drop support',1,1,'2025-02-26 22:30:50','1',NULL),
(28,'Frontend Engineering','Media Management','Implement image optimization',1,1,'2025-02-26 22:30:50','1',NULL),
(29,'Frontend Engineering','Campaign Preview','Create preview mode toggle',1,2,'2025-03-04 19:42:10','1',NULL),
(30,'Frontend Engineering','Campaign Preview','Build mobile/desktop preview',1,2,'2025-03-04 19:42:17','1',NULL),
(31,'Frontend Engineering','Campaign Preview','Implement social share preview',1,2,'2025-03-06 05:41:55','1',NULL),
(32,'Frontend Engineering','Campaign Preview','Add SEO preview functionality',1,2,'2025-03-06 05:42:12','1',NULL),
(33,'Backend Engineering','Basic Setup','Initialize Express.js project',1,1,'2025-02-28 12:47:05','1',NULL),
(34,'Backend Engineering','Basic Setup','Set up middleware architecture',1,1,'2025-02-28 12:47:05','1',NULL),
(35,'Backend Engineering','Basic Setup','Configure error handling',1,1,'2025-02-28 12:47:05','1',NULL),
(36,'Backend Engineering','Basic Setup','Implement logging system',1,1,'2025-02-28 12:47:05','1',NULL),
(37,'Backend Engineering','API Endpoints','Create user management endpoints',1,1,'2025-03-15 22:05:07','1','See documentation at https://api.thegivehub.com'),
(38,'Backend Engineering','API Endpoints','Build campaign management routes',1,1,'2025-03-19 02:11:59','1','Contained in \'app\' repository \'/lib/Campaign.php\'&nbsp;'),
(39,'Backend Engineering','API Endpoints','Implement donation processing',1,1,'2025-03-06 18:32:22','1',NULL),
(40,'Backend Engineering','API Endpoints','Add media handling endpoints',1,1,'2025-02-28 12:47:05','1',NULL),
(41,'Backend Engineering','Data Validation','Implement input sanitization',1,1,'2025-02-28 12:47:05','1',NULL),
(42,'Backend Engineering','Data Validation','Create schema validation',1,1,'2025-02-28 12:47:05','1',NULL),
(43,'Backend Engineering','Data Validation','Add request/response logging',1,1,'2025-02-28 12:47:05','1',NULL),
(44,'Backend Engineering','Data Validation','Build error handling system',1,1,'2025-02-28 12:47:05','1',NULL),
(45,'Backend Engineering','Collection Setup','Design and implement user schema',1,1,'2025-02-28 12:47:05','1',NULL),
(46,'Backend Engineering','Collection Setup','Create campaign schema',1,1,'2025-02-28 12:47:05','1',NULL),
(47,'Backend Engineering','Collection Setup','Build transaction schema',1,1,'2025-02-28 12:47:05','1',NULL),
(48,'Backend Engineering','Collection Setup','Add milestone schema',1,1,'2025-02-28 12:47:05','1',NULL),
(49,'Backend Engineering','Optimization','Configure database indexes',1,1,'2025-02-28 12:47:05','1',NULL),
(50,'Backend Engineering','Optimization','Implement query optimization',1,1,'2025-02-28 12:47:05','1',NULL),
(51,'Backend Engineering','Optimization','Set up data migration system',1,1,'2025-02-28 12:47:05','1',NULL),
(52,'Backend Engineering','Optimization','Add data validation rules',1,1,'2025-02-28 12:47:05','1',NULL),
(53,'Backend Engineering','Basic Verification','Set up document upload system',1,1,'2025-03-06 18:32:31','1',NULL),
(54,'Backend Engineering','Basic Verification','Implement face verification',1,2,'2025-03-13 17:43:39','1',NULL),
(55,'Backend Engineering','Basic Verification','Add address validation',1,2,'2025-03-06 18:32:40','1',NULL),
(56,'Backend Engineering','Basic Verification','Create verification tracking',1,2,'2025-03-13 17:43:43','1',NULL),
(57,'Backend Engineering','Jumio Integration','Configure Jumio API client',1,2,'2025-03-13 17:43:44','1',NULL),
(58,'Backend Engineering','Jumio Integration','Implement webhook handling',1,2,'2025-03-13 17:43:46','1',NULL),
(59,'Backend Engineering','Jumio Integration','Add result processing',1,2,'2025-03-13 17:43:48','1',NULL),
(60,'Backend Engineering','Jumio Integration','Create retry mechanism',1,2,'2025-03-13 17:43:52','1',NULL),
(61,'Backend Engineering','JWT Implementation','Set up token generation',1,1,'2025-02-28 12:47:05','1',NULL),
(62,'Backend Engineering','JWT Implementation','Implement token validation',1,1,'2025-02-28 12:47:05','1',NULL),
(63,'Backend Engineering','JWT Implementation','Create refresh token system',1,1,'2025-02-28 12:47:05','1',NULL),
(64,'Backend Engineering','JWT Implementation','Add token revocation',1,1,'2025-02-28 12:47:05','1',NULL),
(65,'Blockchain Engineering','Wallet Setup','Implement key pair generation',1,1,'2025-02-28 12:47:05','1',NULL),
(66,'Blockchain Engineering','Wallet Setup','Add testnet account funding',1,1,'2025-02-28 12:47:05','1',NULL),
(67,'Blockchain Engineering','Wallet Setup','Create balance management',1,1,'2025-02-28 12:47:05','1',NULL),
(68,'Blockchain Engineering','Wallet Setup','Build error handling',1,1,'2025-02-28 12:47:05','1',NULL),
(69,'Blockchain Engineering','Transaction Handling','Implement transaction building',1,1,'2025-03-07 17:23:16','1',NULL),
(70,'Blockchain Engineering','Transaction Handling','Add signature collection',1,1,'2025-03-14 09:01:57','1',NULL),
(71,'Blockchain Engineering','Transaction Handling','Create status tracking',1,1,'2025-03-15 19:56:30','1',''),
(72,'Blockchain Engineering','Transaction Handling','Implement fee management',1,1,'2025-03-14 08:38:30','1',NULL),
(73,'Testing & Documentation','Testing Setup','Configure testing environment',1,1,'2025-02-26 22:30:50','1',NULL),
(74,'Testing & Documentation','Testing Setup','Create unit test suite',1,1,'2025-02-28 12:47:05','1',NULL),
(75,'Testing & Documentation','Testing Setup','Implement integration tests',1,1,'2025-02-28 12:52:11','1',NULL),
(76,'Testing & Documentation','Testing Setup','Set up CI pipeline',1,1,'2025-02-26 22:30:50','1',NULL),
(77,'Testing & Documentation','Documentation','Create API documentation',1,1,'2025-02-28 12:47:05','1',NULL),
(78,'Testing & Documentation','Documentation','Write setup instructions',1,1,'2025-02-26 22:30:50','1',NULL),
(79,'Testing & Documentation','Documentation','Document deployment process',1,1,'2025-02-28 12:47:05','1',NULL),
(80,'Testing & Documentation','Documentation','Create user guides',1,1,'2025-03-06 07:35:14','1',NULL),
(81,'Frontend Engineering','Digital Nomad Portal','Create verification interface',0,1,'2025-02-26 22:30:50','2',NULL),
(82,'Frontend Engineering','Digital Nomad Portal','Implement document upload management',0,1,'2025-02-26 22:30:50','2',NULL),
(83,'Frontend Engineering','Digital Nomad Portal','Build progress tracking system',0,1,'2025-02-26 22:30:50','2',NULL),
(84,'Frontend Engineering','Digital Nomad Portal','Add real-time status updates',0,1,'2025-02-26 22:30:50','2',NULL),
(85,'Frontend Engineering','Milestone Tracking','Design milestone creation interface',0,1,'2025-02-26 22:30:50','2',NULL),
(86,'Frontend Engineering','Milestone Tracking','Implement budget allocation tools',0,1,'2025-02-26 22:30:50','2',NULL),
(87,'Frontend Engineering','Milestone Tracking','Create timeline visualization',0,1,'2025-02-26 22:30:50','2',NULL),
(88,'Frontend Engineering','Milestone Tracking','Add progress tracking features',0,1,'2025-02-26 22:30:50','2',NULL),
(89,'Frontend Engineering','Impact Metrics','Build metrics visualization components',1,1,'2025-03-13 01:03:55','2',NULL),
(90,'Frontend Engineering','Impact Metrics','Create reporting interface',0,1,'2025-02-26 22:30:50','2',NULL),
(91,'Frontend Engineering','Impact Metrics','Implement data analysis tools',0,1,'2025-02-26 22:30:50','2',NULL),
(92,'Frontend Engineering','Impact Metrics','Add trend analysis features',0,1,'2025-02-26 22:30:50','2',NULL),
(93,'Backend Engineering','Verification System','Implement multi-step verification process',0,1,'2025-02-28 12:47:05','2',NULL),
(94,'Backend Engineering','Verification System','Create document processing pipeline',0,1,'2025-02-28 12:47:05','2',NULL),
(95,'Backend Engineering','Verification System','Build advanced notification system',0,1,'2025-02-28 12:47:05','2',NULL),
(96,'Backend Engineering','Verification System','Add approval workflows',0,1,'2025-02-28 12:47:05','2',NULL),
(97,'Backend Engineering','KYC/AML Processing','Enhance identity verification',0,1,'2025-02-28 12:47:05','2',NULL),
(98,'Backend Engineering','KYC/AML Processing','Implement transaction monitoring',0,1,'2025-02-28 12:47:05','2',NULL),
(99,'Backend Engineering','KYC/AML Processing','Create compliance reporting',0,1,'2025-02-28 12:47:05','2',NULL),
(100,'Backend Engineering','KYC/AML Processing','Add risk scoring system',0,1,'2025-02-28 12:47:05','2',NULL),
(101,'Backend Engineering','Impact Analytics','Build metrics processing engine',0,1,'2025-02-28 12:47:05','2',NULL),
(102,'Backend Engineering','Impact Analytics','Implement data integration services',0,1,'2025-02-28 12:47:05','2',NULL),
(103,'Backend Engineering','Impact Analytics','Create reporting system',0,1,'2025-02-28 12:47:05','2',NULL),
(104,'Backend Engineering','Impact Analytics','Add custom calculations',0,1,'2025-02-28 12:47:05','2',NULL),
(105,'Blockchain Engineering','Smart Contracts','Develop campaign contract',1,1,'2025-03-13 01:03:11','2',NULL),
(106,'Blockchain Engineering','Smart Contracts','Create milestone contract',1,1,'2025-03-13 01:03:11','2',NULL),
(107,'Blockchain Engineering','Smart Contracts','Build verification contract',1,1,'2025-03-13 01:03:12','2',NULL),
(108,'Blockchain Engineering','Smart Contracts','Implement multi-signature support',0,1,'2025-02-28 12:47:05','2',NULL),
(109,'Blockchain Engineering','Testing & Security','Create comprehensive test suite',1,1,'2025-03-13 01:03:27','2',NULL),
(110,'Blockchain Engineering','Testing & Security','Implement security controls',0,1,'2025-02-28 12:47:05','2',NULL),
(111,'Blockchain Engineering','Testing & Security','Add contract documentation',1,1,'2025-03-13 01:03:33','2',NULL),
(112,'Blockchain Engineering','Testing & Security','Optimize gas usage',0,1,'2025-02-28 12:47:05','2',NULL),
(113,'Frontend Engineering','Mobile Optimization','Enhance responsive design',0,1,'2025-02-26 22:30:50','3',NULL),
(114,'Frontend Engineering','Mobile Optimization','Implement progressive web app features',0,1,'2025-02-26 22:30:50','3',NULL),
(115,'Frontend Engineering','Mobile Optimization','Add offline functionality',0,1,'2025-02-26 22:30:50','3',NULL),
(116,'Frontend Engineering','Mobile Optimization','Create mobile payment flow',0,1,'2025-02-26 22:30:50','3',NULL),
(117,'Frontend Engineering','Multi-language Support','Build translation system',0,1,'2025-02-26 22:30:50','3',NULL),
(118,'Frontend Engineering','Multi-language Support','Implement currency formatting',0,1,'2025-02-26 22:30:50','3',NULL),
(119,'Frontend Engineering','Multi-language Support','Add content management for translations',0,1,'2025-02-26 22:30:50','3',NULL),
(120,'Frontend Engineering','Multi-language Support','Create RTL support',0,1,'2025-02-26 22:30:50','3',NULL),
(121,'Frontend Engineering','Payment Flow','Optimize donation interface',0,1,'2025-02-26 22:30:50','3',NULL),
(122,'Frontend Engineering','Payment Flow','Create recurring payment setup',0,1,'2025-02-26 22:30:50','3',NULL),
(123,'Frontend Engineering','Payment Flow','Implement transaction tracking',0,1,'2025-02-26 22:30:50','3',NULL),
(124,'Frontend Engineering','Payment Flow','Add analytics integration',0,1,'2025-02-26 22:30:50','3',NULL),
(125,'Backend Engineering','Performance','Optimize database queries',0,1,'2025-02-28 12:47:05','3',NULL),
(126,'Backend Engineering','Performance','Implement caching system',0,1,'2025-02-28 12:47:05','3',NULL),
(127,'Backend Engineering','Performance','Add load testing',0,1,'2025-02-28 12:47:05','3',NULL),
(128,'Backend Engineering','Performance','Create performance monitoring',0,1,'2025-02-28 12:47:05','3',NULL),
(129,'Backend Engineering','Security','Implement security hardening',0,1,'2025-02-28 12:47:05','3',NULL),
(130,'Backend Engineering','Security','Enhance access control system',0,1,'2025-02-28 12:47:05','3',NULL),
(131,'Backend Engineering','Security','Add protection systems',0,1,'2025-02-28 12:47:05','3',NULL),
(132,'Backend Engineering','Security','Create security monitoring',0,1,'2025-02-28 12:47:05','3',NULL),
(133,'Backend Engineering','Documentation','Create comprehensive API docs',1,1,'2025-03-13 01:04:25','3',NULL),
(134,'Backend Engineering','Documentation','Document system architecture',1,1,'2025-03-13 01:04:28','3',NULL),
(135,'Backend Engineering','Documentation','Build developer resources',1,1,'2025-03-13 01:04:29','3',NULL),
(136,'Backend Engineering','Documentation','Add integration guides',1,1,'2025-03-13 01:04:31','3',NULL),
(137,'Blockchain Engineering','Mainnet','Perform contract migration',0,1,'2025-02-28 12:47:05','3',NULL),
(138,'Blockchain Engineering','Mainnet','Implement security verification',0,1,'2025-02-28 12:47:05','3',NULL),
(139,'Blockchain Engineering','Mainnet','Create production integration',0,1,'2025-02-28 12:47:05','3',NULL),
(140,'Blockchain Engineering','Mainnet','Add monitoring system',0,1,'2025-02-28 12:47:05','3',NULL),
(141,'DevOps','Infrastructure','Configure server environment',1,1,'2025-03-13 01:05:33','3',NULL),
(142,'DevOps','Infrastructure','Implement monitoring systems',1,1,'2025-03-13 01:05:42','3',NULL),
(143,'DevOps','Infrastructure','Create backup systems',1,1,'2025-03-13 01:05:39','3',NULL),
(144,'DevOps','Infrastructure','Set up auto-scaling',0,1,'2025-02-28 12:47:05','3',NULL),
(145,'DevOps','CI/CD','Implement deployment automation',0,1,'2025-02-28 12:47:05','3',NULL),
(146,'DevOps','CI/CD','Create environment management',0,1,'2025-02-28 12:47:05','3',NULL),
(147,'DevOps','CI/CD','Add testing automation',0,1,'2025-02-28 12:47:05','3',NULL),
(148,'DevOps','CI/CD','Implement rollback procedures',0,1,'2025-02-28 12:47:05','3',NULL),
(149,'Quality Assurance','Testing','Perform system testing',0,1,'2025-02-26 22:30:50','3',NULL),
(150,'Quality Assurance','Testing','Run cross-browser testing',0,1,'2025-02-26 22:30:50','3',NULL),
(151,'Quality Assurance','Testing','Execute performance validation',0,1,'2025-02-28 12:47:05','3',NULL),
(152,'Quality Assurance','Testing','Create test reports',0,1,'2025-02-26 22:30:50','3',NULL);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-27 10:55:22

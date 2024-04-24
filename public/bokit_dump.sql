-- MySQL dump 10.13  Distrib 8.0.36, for macos14 (x86_64)
--
-- Host: 127.0.0.1    Database: bokit
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academies`
--

DROP TABLE IF EXISTS `academies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `role` enum('manager','owner','partner') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `commercial_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trade_license_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trade_license_expire_date` date DEFAULT NULL,
  `tax_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `percentage` bigint unsigned DEFAULT NULL,
  `national_id_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `contract_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_manager` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_registered` tinyint(1) NOT NULL DEFAULT '0',
  `branch_to` bigint unsigned DEFAULT NULL,
  `country_id` bigint unsigned DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL,
  `area_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academies_email_unique` (`email`),
  UNIQUE KEY `academies_phone_unique` (`phone`),
  KEY `academies_branch_to_foreign` (`branch_to`),
  KEY `academies_country_id_foreign` (`country_id`),
  KEY `academies_city_id_foreign` (`city_id`),
  KEY `academies_area_id_foreign` (`area_id`),
  CONSTRAINT `academies_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `academies_branch_to_foreign` FOREIGN KEY (`branch_to`) REFERENCES `academies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academies_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `academies_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academies`
--

LOCK TABLES `academies` WRITE;
/*!40000 ALTER TABLE `academies` DISABLE KEYS */;
INSERT INTO `academies` VALUES (3,'admin@mail.com','01144166700','$2y$12$xM.7/gPumAhKpbCafiqvluH2RmK4OMNsugCW.L0X9elkzYlSHKhZm','active','manager','{\"en\":\"Arsenal Academy\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}','149','2024-03-04',NULL,NULL,NULL,'مدينه نصر',NULL,NULL,NULL,NULL,NULL,'2024-03-04 10:18:02','2024-03-31 06:59:53','ef3fr3f','mohamed',0,NULL,NULL,NULL,NULL),(4,'test@mail.com','01005556224','$2y$12$Ebo6apG0LlOAIgcBlc06MO54B7fNA1q6oWe41np1iIHJZmE1.pvCS','active','manager','{\"en\":\"Baraclona\",\"ar\":\"\\u0628\\u0631\\u0634\\u0644\\u0648\\u0646\\u0647\"}','149','2024-04-05','560',NULL,'54534324546','مدينه نصر',NULL,NULL,NULL,NULL,NULL,'2024-03-10 17:52:07','2024-03-31 06:49:58','678677t8','mohamed',0,3,1,2,3);
/*!40000 ALTER TABLE `academies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `academy_sport`
--

DROP TABLE IF EXISTS `academy_sport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academy_sport` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academy_id` bigint unsigned NOT NULL,
  `sport_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `academy_sport_academy_id_foreign` (`academy_id`),
  KEY `academy_sport_sport_id_foreign` (`sport_id`),
  CONSTRAINT `academy_sport_academy_id_foreign` FOREIGN KEY (`academy_id`) REFERENCES `academies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `academy_sport_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academy_sport`
--

LOCK TABLES `academy_sport` WRITE;
/*!40000 ALTER TABLE `academy_sport` DISABLE KEYS */;
INSERT INTO `academy_sport` VALUES (2,3,3),(3,4,3),(4,4,4);
/*!40000 ALTER TABLE `academy_sport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academy_id` bigint unsigned NOT NULL,
  `city_id` bigint unsigned NOT NULL,
  `area_id` bigint unsigned NOT NULL,
  `longitude` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `country_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_academy_id_foreign` (`academy_id`),
  KEY `addresses_city_id_foreign` (`city_id`),
  KEY `addresses_area_id_foreign` (`area_id`),
  CONSTRAINT `addresses_academy_id_foreign` FOREIGN KEY (`academy_id`) REFERENCES `academies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `addresses_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `addresses_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (2,3,1,2,NULL,NULL,'{\"en\":\"egypt\",\"ar\":\"\\u0645\\u062f\\u064a\\u0646\\u0647 \\u0646\\u0635\\u0631\"}',1,'2024-03-04 10:54:33','2024-03-04 10:54:33',1),(3,3,2,3,NULL,NULL,'{\"en\":\"bulaq\",\"ar\":\"\\u0628\\u0648\\u0644\\u0627\\u0642\"}',0,'2024-03-04 10:55:07','2024-03-04 10:55:07',1);
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`),
  UNIQUE KEY `admins_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Mohamed','Aly','admin@admin.com','01144166700','$2y$12$vLfiizQZ16ZhBROTlI.LEOtJk/NY2EMX2CMfYzcfoQmaQfchNkW62','2024-02-18 12:52:06','2024-02-18 12:52:06');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `areas_city_id_foreign` (`city_id`),
  CONSTRAINT `areas_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` VALUES (2,'{\"en\":\"NasrCity\",\"ar\":\"\\u0645\\u062f\\u064a\\u0646\\u0647 \\u0646\\u0635\\u0631\"}',1,'2024-02-24 05:16:28','2024-02-24 05:16:28'),(3,'{\"en\":\"faisal\",\"ar\":\"\\u0645\\u062f\\u064a\\u0646\\u0647 \\u0646\\u0635\\u0631\"}',2,'2024-02-24 05:16:28','2024-02-24 05:16:28'),(4,'{\"en\":\"haram\",\"ar\":\"\\u0645\\u062f\\u064a\\u0646\\u0647 \\u0646\\u0635\\u0631\"}',2,'2024-02-24 05:16:28','2024-02-24 05:16:28');
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `canceled_bookings`
--

DROP TABLE IF EXISTS `canceled_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `canceled_bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `canceled_bookings_invoice_id_foreign` (`invoice_id`),
  KEY `canceled_bookings_user_id_foreign` (`user_id`),
  CONSTRAINT `canceled_bookings_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `canceled_bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `canceled_bookings`
--

LOCK TABLES `canceled_bookings` WRITE;
/*!40000 ALTER TABLE `canceled_bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `canceled_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `country_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cities_country_id_foreign` (`country_id`),
  CONSTRAINT `cities_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
INSERT INTO `cities` VALUES (1,'{\"en\":\"Cairo\",\"ar\":\"\\u0627\\u0644\\u0642\\u0627\\u0647\\u0631\\u0647\"}','2024-02-25 12:24:14','2024-02-25 12:24:14',1),(2,'{\"en\":\"giza\",\"ar\":\"\\u0627\\u0644\\u062c\\u064a\\u0632\\u0647\"}','2024-02-25 12:24:31','2024-02-28 06:16:26',1),(3,'{\"en\":\"doha\",\"ar\":\"\\u0627\\u0644\\u062f\\u0648\\u062d\\u0647\"}','2024-02-26 07:52:47','2024-02-26 07:52:47',2),(5,'{\"en\":\"fayoum\",\"ar\":\"\\u0627\\u0644\\u0641\\u064a\\u0648\\u0645\"}','2024-02-28 06:47:32','2024-02-28 06:47:32',1),(6,'{\"en\":\"sohag\",\"ar\":\"\\u0633\\u0648\\u0647\\u0627\\u062c\"}','2024-02-28 07:05:11','2024-02-28 07:05:11',1);
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coach_sports`
--

DROP TABLE IF EXISTS `coach_sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coach_sports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coach_id` bigint unsigned NOT NULL,
  `sport_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coach_sports_coach_id_foreign` (`coach_id`),
  KEY `coach_sports_sport_id_foreign` (`sport_id`),
  CONSTRAINT `coach_sports_coach_id_foreign` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `coach_sports_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coach_sports`
--

LOCK TABLES `coach_sports` WRITE;
/*!40000 ALTER TABLE `coach_sports` DISABLE KEYS */;
INSERT INTO `coach_sports` VALUES (1,4,3,NULL,NULL);
/*!40000 ALTER TABLE `coach_sports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coaches`
--

DROP TABLE IF EXISTS `coaches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coaches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `academy_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `license` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coaches_academy_id_foreign` (`academy_id`),
  CONSTRAINT `coaches_academy_id_foreign` FOREIGN KEY (`academy_id`) REFERENCES `academies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coaches`
--

LOCK TABLES `coaches` WRITE;
/*!40000 ALTER TABLE `coaches` DISABLE KEYS */;
INSERT INTO `coaches` VALUES (4,'Mohamed Ali','football trainer','U5btQ9zsITETfppfuBjPU8S65n4BI2LjZOJ4w6al.jpg','01005556224',1,3,'2024-03-04 10:55:42','2024-04-21 05:08:01',NULL,NULL,'male','2024-04-22');
/*!40000 ALTER TABLE `coaches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (1,'{\"en\":\"egypt\",\"ar\":\"\\u0645\\u0635\\u0631\"}','2024-02-25 12:23:49','2024-02-25 12:23:49'),(2,'{\"en\":\"Qatar\",\"ar\":\"\\u0642\\u0637\\u0631\"}','2024-02-26 07:52:18','2024-02-26 07:52:18'),(3,'{\"en\":\"KSA\",\"ar\":\"\\u0627\\u0644\\u0645\\u0645\\u0644\\u0643\\u0647 \\u0627\\u0644\\u0639\\u0631\\u0628\\u064a\\u0647 \\u0627\\u0644\\u0633\\u0639\\u0648\\u062f\\u064a\\u0647\"}','2024-02-28 11:28:09','2024-02-28 11:28:09');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'{\"en\":\"Quia nihil totam voluptas commodo aut veritatis qu\",\"ar\":\"Quia nihil totam voluptas commodo aut veritatis qu\"}','{\"en\":\"Aut maiores sit cupiditate qui\",\"ar\":\"Vel consequatur Aut qui exercitation quis\"}','2024-02-25 12:18:25','2024-02-25 12:18:30');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `training_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `favorites_user_id_foreign` (`user_id`),
  KEY `favorites_training_id_foreign` (`training_id`),
  CONSTRAINT `favorites_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
INSERT INTO `favorites` VALUES (5,1,2,'2024-03-23 11:36:37','2024-03-23 11:36:37'),(6,1,3,'2024-04-16 08:46:27','2024-04-16 08:46:27');
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `follows`
--

DROP TABLE IF EXISTS `follows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `follows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `followable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `followable_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `follows_user_id_foreign` (`user_id`),
  KEY `follows_followable_type_followable_id_index` (`followable_type`,`followable_id`),
  CONSTRAINT `follows_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `follows`
--

LOCK TABLES `follows` WRITE;
/*!40000 ALTER TABLE `follows` DISABLE KEYS */;
INSERT INTO `follows` VALUES (2,1,'App\\Models\\Coach',4,'2024-03-04 07:09:25','2024-03-04 07:09:25');
/*!40000 ALTER TABLE `follows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `galleries`
--

DROP TABLE IF EXISTS `galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `galleries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `academy_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `galleries_academy_id_foreign` (`academy_id`),
  CONSTRAINT `galleries_academy_id_foreign` FOREIGN KEY (`academy_id`) REFERENCES `academies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
INSERT INTO `galleries` VALUES (2,'vbq3p5Etl6qBZ7i6eYY3l7LkufQ7vGsLnD2R9ckd.png',3,'2024-02-24 05:29:03','2024-02-24 05:29:03',0),(3,'M2oo9lSiZdXrxzJB3szWLQ49MZh7eqDJj0JDD4po.jpg',3,'2024-03-04 10:55:54','2024-03-04 10:55:54',0);
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `training_id` bigint unsigned NOT NULL,
  `order_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `is_canceled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_type` enum('online','offline') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'online',
  PRIMARY KEY (`id`),
  KEY `invoices_user_id_foreign` (`user_id`),
  KEY `invoices_training_id_foreign` (`training_id`),
  CONSTRAINT `invoices_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,1,2,'2222','pending',200,0,NULL,'2024-03-25 11:04:15','online'),(4,5,12,'6611636d10e10','paid',578,1,'2024-04-06 12:59:57','2024-04-21 12:34:54','offline');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `joins`
--

DROP TABLE IF EXISTS `joins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `joins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `training_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `price` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `joins_user_id_foreign` (`user_id`),
  KEY `joins_training_id_foreign` (`training_id`),
  KEY `joins_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `joins_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `joins_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `joins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `joins`
--

LOCK TABLES `joins` WRITE;
/*!40000 ALTER TABLE `joins` DISABLE KEYS */;
INSERT INTO `joins` VALUES (2,1,2,1,2222,NULL,NULL),(3,5,12,4,578,'2024-04-06 12:59:57','2024-04-06 12:59:57');
/*!40000 ALTER TABLE `joins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2024_02_18_122848_create_admins_table',1),(6,'2024_02_18_123237_create_academies_table',1),(7,'2024_02_18_124254_create_cities_table',1),(8,'2024_02_18_124424_create_areas_table',1),(9,'2024_02_18_124935_create_sports_table',1),(10,'2024_02_18_125556_create_banners_table',1),(11,'2024_02_18_125748_create_settings_table',1),(12,'2024_02_20_064714_add_owner_name_in_academies',2),(13,'2024_02_20_065112_create_addresses_table',2),(14,'2024_02_20_065741_create_galleries_table',2),(15,'2024_02_20_065848_create_coaches_table',2),(16,'2024_02_20_070855_create_trainings_table',2),(17,'2024_02_20_071339_create_t_classes_table',2),(18,'2024_02_20_071340_create_training_classes_table',2),(19,'2024_02_25_093625_create_faqs_table',3),(20,'2024_02_25_102416_update_column_academies',3),(21,'2024_02_25_102930_add_academies_table',3),(22,'2024_02_25_114939_create_countries_table',3),(23,'2024_02_25_120706_add_gender_in_users',4),(24,'2024_02_25_130639_add_to_cities_table',4),(25,'2024_02_25_143022_create_user_sport_table',5),(26,'2024_02_25_143023_add_to_addresses',6),(27,'2024_02_26_155637_add_to_sports_table',7),(28,'2024_02_27_105522_add_academy_id_in_t_classes',8),(29,'2024_02_27_112615_create_table_academy_sport_table',9),(30,'2024_03_02_085640_add_academy_id_in_galleries',10),(31,'2024_03_02_090208_add_academy_id_in_trainings',11),(32,'2024_03_02_124825_drop_key_in_settings',12),(33,'2024_03_02_124852_add_key_in_settings',12),(34,'2024_03_03_132615_drop_level_from_sports',13),(35,'2024_03_03_135006_add_price_in_trainings',14),(36,'2024_03_03_145923_add_start_time_in_trainings',15),(37,'2024_03_04_071449_create_follows_table',16),(38,'2024_03_04_111558_drop_first_name_from_academies',17),(39,'2024_03_04_120550_create_invoices_table',18),(40,'2024_03_04_120553_create_joins_table',19),(41,'2024_03_04_100702_add_to_classes_table',20),(42,'2024_03_04_124557_drop_start_time_in_trainings',21),(43,'2024_03_04_135459_create_favorites_table',22),(44,'2024_03_04_145601_add_active_in_trainings',22),(45,'2024_03_05_103549_add_license_in_coaches',23),(46,'2024_03_06_152923_add_out_comes_in_t_classes',24),(47,'2024_03_06_163013_add_sport_id_in_trainings',25),(48,'2024_03_09_093353_drop_academy_id_from_t_classes',26),(50,'2024_03_09_100421_drop_sport_id_from_t_classes',27),(51,'2024_03_09_115246_add_lang_in_users',28),(52,'2024_03_10_071454_update_brings_with_you_in_t_classes',29),(53,'2024_03_10_091437_add_branch_to_in_academis',30),(54,'2024_03_17_114016_add_otp_in_users',31),(55,'2024_03_18_122333_drop_image_in_training',32),(56,'2024_03_23_082237_create_notifications_table',33),(57,'2024_03_23_091753_add_is_canceled_in_invoices',34),(58,'2024_03_23_092255_create_canceled_bookings_table',34),(59,'2024_03_23_132915_add_title_in_notifications',34),(60,'2024_03_24_085631_add_fcm_token_in_users',35),(61,'2024_03_25_145643_change_key_in_settings',36),(62,'2024_03_31_065643_add_city_id_in_academies',37),(63,'2024_03_31_090220_update_active_in_trainings',38),(64,'2024_03_31_111942_add_active_in_galleries',39),(65,'2024_04_01_111901_add_discount_price_in_trainings',40),(66,'2024_04_06_144622_add_user_type_in_invoices',41),(67,'2024_04_06_145844_change_birth_date_in_users',42),(68,'2024_04_15_122710_add_phone_in_coaches',43),(69,'2024_04_15_144644_create_coach_sports_table',44),(70,'2024_04_21_065240_add_gender_in_coaches',45),(71,'2024_04_22_122601_add_details_in_notifications',46);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` json DEFAULT NULL,
  `image` text COLLATE utf8mb4_unicode_ci,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('0c130e89-5f3e-4bb6-b5eb-c81aeb543578','Don\'t miss out','App\\Models\\User',1,NULL,'Only two slots are available in a training you saved',NULL,NULL,NULL,'2024-03-23 11:21:02','2024-03-23 11:21:02'),('7980d58c-9e7a-4869-bd50-b4ab5e89d15e','1','App\\Models\\User',1,'Booking Rescheduled','The Training you booked with Arsenal Academy is rescheduled, please check the new dates','{\"latitude\": null, \"longitude\": null, \"training_id\": 2}',NULL,NULL,'2024-04-22 10:41:56','2024-04-22 10:41:56'),('a05e345b-160a-415f-b122-cb292f37edb9','Saved Training','App\\Models\\User',1,'Don\'t miss out','Only two slots are available in a training you saved',NULL,NULL,NULL,'2024-03-23 11:31:57','2024-03-23 11:31:57'),('a77722a6-d81d-4869-8ca3-c85f0f48a523','1','App\\Models\\User',1,'Booking Rescheduled','The Training you booked with Arsenal Academy is rescheduled, please check the new dates','{\"latitude\": null, \"longitude\": null, \"training_id\": 2}',NULL,NULL,'2024-04-22 11:59:05','2024-04-22 11:59:05');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` enum('logo','phone','whatsapp','about','email','facebook','twitter','instagram','telegram','address','snapchat','youtube','terms','privacy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'phone','01144166700','text','2024-02-20 04:38:05','2024-03-02 10:56:46'),(2,'whatsapp','01144166700','text','2024-03-02 12:00:58','2024-03-02 12:00:58'),(3,'terms','<p>test data</p>','textarea',NULL,'2024-03-30 12:03:54');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sports`
--

DROP TABLE IF EXISTS `sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sports`
--

LOCK TABLES `sports` WRITE;
/*!40000 ALTER TABLE `sports` DISABLE KEYS */;
INSERT INTO `sports` VALUES (3,'{\"en\":\"football\",\"ar\":\"\\u0643\\u0631\\u0647 \\u0627\\u0644\\u0642\\u062f\\u0645\"}','lE0nr0RWwjAebqeryfUQfQlOIv9fOdpa0xgUa02T.jpg','active','2024-02-27 09:49:09','2024-02-27 09:50:28'),(4,'{\"en\":\"football\",\"ar\":\"\\u0643\\u0631\\u0647 \\u0627\\u0644\\u0642\\u062f\\u0645\"}','lE0nr0RWwjAebqeryfUQfQlOIv9fOdpa0xgUa02T.jpg','active','2024-02-27 09:49:09','2024-02-27 09:50:28');
/*!40000 ALTER TABLE `sports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_classes`
--

DROP TABLE IF EXISTS `t_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `training_id` bigint unsigned NOT NULL,
  `out_comes` json DEFAULT NULL,
  `bring_with_me` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_classes_training_id_foreign` (`training_id`),
  CONSTRAINT `t_classes_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_classes`
--

LOCK TABLES `t_classes` WRITE;
/*!40000 ALTER TABLE `t_classes` DISABLE KEYS */;
INSERT INTO `t_classes` VALUES (4,'{\"en\":\"Perferendis voluptas sunt dolores sit tempor sit\",\"ar\":\"Ipsum anim quia repellendus Earum labore nesciunt\"}','{\"en\":\"Voluptatem anim beatae non porro deleniti reprehen\",\"ar\":\"Doloremque ut necessitatibus quos nisi officia off\"}','2024-03-12','2024-03-10 09:36:34','2024-03-10 18:10:09','04:05:00','22:01:00',2,'[\"Quis officia aut ani\", \"Et ut non numquam ne\"]','[\"Voluptas fugit cons\", \"Labore necessitatibu\"]'),(5,'{\"en\":\"Ad id iusto quam nulla obcaecati rerum\",\"ar\":\"Magna exercitationem adipisicing esse anim minima\"}','{\"en\":\"Qui cum fugiat minim vero illum laudantium veni\",\"ar\":\"Dolore ut dignissimos nihil rerum omnis anim dicta\"}','2024-03-12','2024-03-10 09:52:37','2024-03-10 09:52:37','11:29:00','14:18:00',2,'\"[\\\"Et ut non numquam ne\\\"]\"','\"[\\\"Vel voluptates ut co\\\"]\"'),(6,'{\"en\":\"Iusto dolorem laborum adipisci aspernatur quia et\",\"ar\":\"Quasi dicta earum ipsam iure exercitation neque\"}','{\"en\":\"Pariatur Voluptatum est ut illum repudiandae cul\",\"ar\":\"Nemo rerum et ut inventore exercitation est non od\"}','2024-03-18','2024-03-10 10:12:06','2024-03-10 10:12:06','04:38:00','09:45:00',2,'[\"Dolores consequat O\"]','[\"Labore necessitatibu\"]');
/*!40000 ALTER TABLE `t_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_classes`
--

DROP TABLE IF EXISTS `training_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `training_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `training_classes_training_id_foreign` (`training_id`),
  KEY `training_classes_class_id_foreign` (`class_id`),
  CONSTRAINT `training_classes_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `t_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_classes_training_id_foreign` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_classes`
--

LOCK TABLES `training_classes` WRITE;
/*!40000 ALTER TABLE `training_classes` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trainings`
--

DROP TABLE IF EXISTS `trainings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL DEFAULT '0',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `coach_id` bigint unsigned NOT NULL,
  `academy_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `max_players` bigint unsigned NOT NULL,
  `level` enum('Beginner','Intermediate','Advanced') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('All','Men','Women') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `age_group` enum('All','Kids','Juniors','Adults') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_id` bigint unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `sport_id` bigint unsigned DEFAULT NULL,
  `discount_price` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `trainings_coach_id_foreign` (`coach_id`),
  KEY `trainings_academy_id_foreign` (`academy_id`),
  KEY `trainings_address_id_foreign` (`address_id`),
  KEY `trainings_sport_id_foreign` (`sport_id`),
  CONSTRAINT `trainings_academy_id_foreign` FOREIGN KEY (`academy_id`) REFERENCES `academies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trainings_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trainings_coach_id_foreign` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trainings_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainings`
--

LOCK TABLES `trainings` WRITE;
/*!40000 ALTER TABLE `trainings` DISABLE KEYS */;
INSERT INTO `trainings` VALUES (2,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',593,'2024-04-22','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"اكاديميه الارسنال\"}',4,3,'2024-03-04 11:19:50','2024-04-22 10:41:56',3,'Beginner','Men','All',2,1,3,0),(3,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',600,'2024-04-23','2024-05-08','{\"en\":\"Consequatur archite\",\"ar\":\"اكاديميه الارسنال\"}',4,3,'2024-03-04 11:19:50','2024-04-22 11:57:41',42,'Beginner','Men','All',2,1,3,0),(4,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',890,'2024-04-24','2024-05-01','{\"en\":\"Consequatur archite\",\"ar\":\"اكاديميه الارسنال\"}',4,3,'2024-03-04 11:19:50','2024-04-22 11:48:21',42,'Beginner','Men','All',2,1,3,0),(5,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',765,'2024-03-09','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"Atque nihil ea ullam\"}',4,3,'2024-03-04 11:19:50','2024-03-09 07:58:38',42,'Beginner','Men','All',2,1,3,0),(6,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',665,'2024-03-09','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"Atque nihil ea ullam\"}',4,3,'2024-03-04 11:19:50','2024-03-09 07:58:38',42,'Beginner','Men','All',2,1,3,0),(7,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',665,'2024-03-09','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"Atque nihil ea ullam\"}',4,3,'2024-03-04 11:19:50','2024-03-09 07:58:38',42,'Beginner','Men','All',2,1,3,0),(8,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',665,'2024-03-09','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"Atque nihil ea ullam\"}',4,3,'2024-03-04 11:19:50','2024-03-09 07:58:38',42,'Beginner','Men','All',2,1,3,0),(9,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',655,'2024-03-09','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"Atque nihil ea ullam\"}',4,3,'2024-03-04 11:19:50','2024-03-09 07:58:38',42,'Beginner','Men','All',2,1,3,0),(10,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',555,'2024-03-09','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"Atque nihil ea ullam\"}',4,3,'2024-03-04 11:19:50','2024-03-09 07:58:38',42,'Beginner','Men','All',2,1,3,0),(11,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',555,'2024-03-09','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"Atque nihil ea ullam\"}',4,3,'2024-03-04 11:19:50','2024-03-09 07:58:38',42,'Beginner','Men','All',2,1,3,0),(12,'{\"en\":\"Lunea Mckinney\",\"ar\":\"\\u0627\\u0643\\u0627\\u062f\\u064a\\u0645\\u064a\\u0647 \\u0627\\u0644\\u0627\\u0631\\u0633\\u0646\\u0627\\u0644\"}',578,'2024-03-09','2024-04-30','{\"en\":\"Consequatur archite\",\"ar\":\"Atque nihil ea ullam\"}',4,3,'2024-03-04 11:19:50','2024-03-31 08:50:01',42,'Beginner','Men','All',2,1,3,0);
/*!40000 ALTER TABLE `trainings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sport`
--

DROP TABLE IF EXISTS `user_sport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sport` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `sport_id` bigint unsigned NOT NULL,
  `level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_sport_user_id_foreign` (`user_id`),
  KEY `user_sport_sport_id_foreign` (`sport_id`),
  CONSTRAINT `user_sport_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_sport_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sport`
--

LOCK TABLES `user_sport` WRITE;
/*!40000 ALTER TABLE `user_sport` DISABLE KEYS */;
INSERT INTO `user_sport` VALUES (2,1,3,'begginer');
/*!40000 ALTER TABLE `user_sport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` enum('en','ar') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ar',
  `gender` enum('male','female') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `country_id` bigint unsigned NOT NULL,
  `city_id` bigint unsigned NOT NULL,
  `area_id` bigint unsigned NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `otp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `users_country_id_foreign` (`country_id`),
  KEY `users_city_id_foreign` (`city_id`),
  KEY `users_area_id_foreign` (`area_id`),
  CONSTRAINT `users_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'mohamed',NULL,'01144166700','en','male','1988-05-07',NULL,1,1,2,NULL,'2024-02-27 06:30:55','2024-04-23 05:26:19','33658','123'),(5,'hamza',NULL,'01005556224','ar','male',NULL,NULL,1,2,3,NULL,'2024-04-06 12:59:57','2024-04-06 12:59:57',NULL,NULL);
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

-- Dump completed on 2024-04-24 13:41:25

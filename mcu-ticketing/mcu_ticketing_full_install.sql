-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mcu_ticketing
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `cost_codes`
--

DROP TABLE IF EXISTS `cost_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `category` enum('RAB','Konsumsi','Vendor (Internal Memo)') DEFAULT 'Vendor (Internal Memo)',
  `lookup_value` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cost_codes`
--

LOCK TABLES `cost_codes` WRITE;
/*!40000 ALTER TABLE `cost_codes` DISABLE KEYS */;
-- INSERT INTO `cost_codes` VALUES (cleaned);
/*!40000 ALTER TABLE `cost_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_type` enum('ASET','KONSUMABLE') NOT NULL,
  `unit` varchar(50) NOT NULL,
  `target_warehouse` enum('GUDANG_ASET','GUDANG_KONSUMABLE') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
INSERT INTO `inventory_items` VALUES (1,'Dokter','Stetoskop','ASET','Pcs','GUDANG_ASET',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(2,'Dokter','Tensimeter Digital','ASET','Unit','GUDANG_ASET',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(3,'Dokter','Sarung Tangan Medis','KONSUMABLE','Box','GUDANG_KONSUMABLE',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(4,'Pleboto','Spuit 3cc','KONSUMABLE','Box','GUDANG_KONSUMABLE',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(5,'Pleboto','Alcohol Swab','KONSUMABLE','Box','GUDANG_KONSUMABLE',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(6,'Pleboto','Tourniquet','ASET','Pcs','GUDANG_ASET',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(7,'Tanda Vital','Termometer Gun','ASET','Unit','GUDANG_ASET',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(8,'Registrasi','Kertas A4','KONSUMABLE','Rim','GUDANG_KONSUMABLE',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(9,'Registrasi','Pulpen','KONSUMABLE','Pcs','GUDANG_KONSUMABLE',1,'2026-01-31 04:38:14','2026-01-31 04:38:14'),(10,'Rontgen','Film Rontgen','KONSUMABLE','Lembar','GUDANG_KONSUMABLE',1,'2026-01-31 04:38:14','2026-01-31 04:38:14');
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_request_items`
--

DROP TABLE IF EXISTS `inventory_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty_request` int(11) NOT NULL DEFAULT 0,
  `item_type_snapshot` enum('ASET','KONSUMABLE') NOT NULL,
  `warehouse_snapshot` enum('GUDANG_ASET','GUDANG_KONSUMABLE') NOT NULL,
  `is_checked` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_request_items`
--

LOCK TABLES `inventory_request_items` WRITE;
/*!40000 ALTER TABLE `inventory_request_items` DISABLE KEYS */;
-- INSERT INTO `inventory_request_items` VALUES (cleaned);
/*!40000 ALTER TABLE `inventory_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_requests`
--

DROP TABLE IF EXISTS `inventory_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `request_number` varchar(50) NOT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('DRAFT','SUBMITTED','SPLIT_SYSTEM','COMPLETED') DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_inventory_project` (`project_id`),
  KEY `idx_inventory_status` (`status`),
  KEY `idx_inventory_creator` (`created_by`),
  KEY `idx_inventory_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_requests`
--

LOCK TABLES `inventory_requests` WRITE;
/*!40000 ALTER TABLE `inventory_requests` DISABLE KEYS */;
-- INSERT INTO `inventory_requests` VALUES (cleaned);
/*!40000 ALTER TABLE `inventory_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `request_item_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (1,1,8,'MCU + SPIRO + AUDIO (PT ISTEM)',55000.00,6,330000.00);
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_request_items`
--

DROP TABLE IF EXISTS `invoice_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_request_id` int(11) NOT NULL,
  `project_id` varchar(50) DEFAULT NULL,
  `item_description` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty` int(11) NOT NULL DEFAULT 1,
  `total` decimal(15,2) GENERATED ALWAYS AS (`price` * `qty`) STORED,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_request_id` (`invoice_request_id`),
  CONSTRAINT `invoice_request_items_ibfk_1` FOREIGN KEY (`invoice_request_id`) REFERENCES `invoice_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_request_items`
--

LOCK TABLES `invoice_request_items` WRITE;
/*!40000 ALTER TABLE `invoice_request_items` DISABLE KEYS */;
-- INSERT INTO `invoice_request_items` VALUES (cleaned);
/*!40000 ALTER TABLE `invoice_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_request_projects`
--

DROP TABLE IF EXISTS `invoice_request_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_request_projects` (
  `invoice_request_id` int(11) NOT NULL,
  `project_id` varchar(50) NOT NULL,
  PRIMARY KEY (`invoice_request_id`,`project_id`),
  CONSTRAINT `invoice_request_projects_ibfk_1` FOREIGN KEY (`invoice_request_id`) REFERENCES `invoice_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_request_projects`
--

LOCK TABLES `invoice_request_projects` WRITE;
/*!40000 ALTER TABLE `invoice_request_projects` DISABLE KEYS */;
INSERT INTO `invoice_request_projects` VALUES (5,'341'),(6,'341');
/*!40000 ALTER TABLE `invoice_request_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_requests`
--

DROP TABLE IF EXISTS `invoice_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_number` varchar(50) NOT NULL,
  `request_date` date NOT NULL,
  `pic_sales_id` int(11) NOT NULL,
  `partner_type` varchar(50) DEFAULT 'Corporate',
  `event_type` varchar(50) DEFAULT NULL,
  `client_company` varchar(255) NOT NULL,
  `client_pic` varchar(255) DEFAULT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `invoice_terms` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `link_gdrive_npwp` text DEFAULT NULL,
  `link_gdrive_absensi` text DEFAULT NULL,
  `status` enum('DRAFT','SUBMITTED','APPROVED_SALES','APPROVED_SPV','APPROVED_MANAGER','PROCESSED') DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by_sales` int(11) DEFAULT NULL,
  `approved_by_sales_at` datetime DEFAULT NULL,
  `approved_by_supervisor` int(11) DEFAULT NULL,
  `approved_by_supervisor_at` datetime DEFAULT NULL,
  `approved_by_manager` int(11) DEFAULT NULL,
  `approved_by_manager_at` datetime DEFAULT NULL,
  `approved_by_sales_id` int(11) DEFAULT NULL,
  `approved_at_sales` datetime DEFAULT NULL,
  `approved_by_spv_id` int(11) DEFAULT NULL,
  `approved_at_spv` datetime DEFAULT NULL,
  `approved_by_manager_id` int(11) DEFAULT NULL,
  `approved_at_manager` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pic_sales_id` (`pic_sales_id`),
  CONSTRAINT `invoice_requests_ibfk_1` FOREIGN KEY (`pic_sales_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_requests`
--

LOCK TABLES `invoice_requests` WRITE;
/*!40000 ALTER TABLE `invoice_requests` DISABLE KEYS */;
-- INSERT INTO `invoice_requests` VALUES (cleaned);
/*!40000 ALTER TABLE `invoice_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_request_id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  `status` enum('DRAFT_FINANCE','ISSUED','SENT','PAID') DEFAULT 'DRAFT_FINANCE',
  `delivery_receipt_number` varchar(100) DEFAULT NULL,
  `is_hardcopy_sent` tinyint(1) DEFAULT 0,
  `payment_date` date DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `invoice_request_id` (`invoice_request_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`invoice_request_id`) REFERENCES `invoice_requests` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,5,'rwrwe','2026-02-06','PT Logisticsplus Internationals 1','PAID','fwf',1,'2026-02-05','weqwqrerg',330000.00,'2026-02-05 06:53:41','2026-02-05 07:43:49');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_companies`
--

DROP TABLE IF EXISTS `master_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_companies`
--

LOCK TABLES `master_companies` WRITE;
/*!40000 ALTER TABLE `master_companies` DISABLE KEYS */;
INSERT INTO `master_companies` VALUES (1,'PT. Sample Company','12.345.678.9-012.000',NULL,'2026-02-03 08:18:03');
/*!40000 ALTER TABLE `master_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_result_followups`
--

DROP TABLE IF EXISTS `medical_result_followups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_result_followups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medical_result_item_id` int(11) NOT NULL,
  `pax_susulan` int(11) DEFAULT 0,
  `pax_names` text DEFAULT NULL,
  `release_date_susulan` date DEFAULT NULL,
  `reason` text NOT NULL,
  `tat_overdue` tinyint(1) DEFAULT 0,
  `tat_issue` enum('Internal','External','System','Other') DEFAULT NULL,
  `tat_issue_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `medical_result_item_id` (`medical_result_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_result_followups`
--

LOCK TABLES `medical_result_followups` WRITE;
/*!40000 ALTER TABLE `medical_result_followups` DISABLE KEYS */;
/*!40000 ALTER TABLE `medical_result_followups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_result_items`
--

DROP TABLE IF EXISTS `medical_result_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_result_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medical_result_id` int(11) NOT NULL,
  `assigned_to_user_id` int(11) DEFAULT NULL,
  `date_mcu` date NOT NULL,
  `actual_pax_checked` int(11) DEFAULT 0,
  `actual_pax_released` int(11) DEFAULT 0,
  `release_date` date DEFAULT NULL,
  `link_pdf` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `has_difference` tinyint(1) DEFAULT 0,
  `difference_names` text DEFAULT NULL,
  `difference_reason` text DEFAULT NULL,
  `tat_overdue` tinyint(1) DEFAULT 0,
  `tat_issue` enum('Internal','External','System','Other') DEFAULT NULL,
  `tat_issue_notes` text DEFAULT NULL,
  `status` enum('PENDING','RELEASED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `medical_result_id` (`medical_result_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_result_items`
--

LOCK TABLES `medical_result_items` WRITE;
/*!40000 ALTER TABLE `medical_result_items` DISABLE KEYS */;
INSERT INTO `medical_result_items` VALUES (1,1,23,'2026-02-07',10,9,'2026-02-04','http://localhost/Project_Bumame/mcu-ticketing/public/index.php?page=medical_results_detail&id=313','',1,'[{\"name\":\"reza\",\"reason\":\"susulan urine\"}]','susulan urine',0,NULL,'','RELEASED','2026-02-04 09:18:43');
/*!40000 ALTER TABLE `medical_result_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_result_realizations`
--

DROP TABLE IF EXISTS `medical_result_realizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_result_realizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rab_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rab_id` (`rab_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `medical_result_realizations_ibfk_1` FOREIGN KEY (`rab_id`) REFERENCES `rab_medical_results` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_result_realizations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_result_realizations`
--

LOCK TABLES `medical_result_realizations` WRITE;
/*!40000 ALTER TABLE `medical_result_realizations` DISABLE KEYS */;
/*!40000 ALTER TABLE `medical_result_realizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_results`
--

DROP TABLE IF EXISTS `medical_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `link_summary_excel` text DEFAULT NULL,
  `link_summary_dashboard` text DEFAULT NULL,
  `status` enum('IN_PROGRESS','COMPLETED','NOT_NEEDED','PENDING_PARTICIPANTS') DEFAULT 'IN_PROGRESS',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pending_participants_count` int(11) DEFAULT 0,
  `pending_participants_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_results`
--

LOCK TABLES `medical_results` WRITE;
/*!40000 ALTER TABLE `medical_results` DISABLE KEYS */;
INSERT INTO `medical_results` VALUES (1,'341','','','COMPLETED','2026-02-04 08:58:54','2026-02-05 04:47:07',11,'mcu ke klinik cideng');
/*!40000 ALTER TABLE `medical_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_holidays`
--

DROP TABLE IF EXISTS `national_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `national_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `holiday_date` (`holiday_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_holidays`
--

LOCK TABLES `national_holidays` WRITE;
/*!40000 ALTER TABLE `national_holidays` DISABLE KEYS */;
INSERT INTO `national_holidays` VALUES (1,'2026-02-17','Tahun Baru Imlek','2026-01-23 02:05:07');
/*!40000 ALTER TABLE `national_holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
-- INSERT INTO `notifications` VALUES (cleaned);
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_berita_acara`
--

DROP TABLE IF EXISTS `project_berita_acara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_berita_acara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `tanggal_mcu` date NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('uploaded','cancelled','cancelled_approved') NOT NULL,
  `cancel_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `tanggal_mcu` (`tanggal_mcu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_berita_acara`
--

LOCK TABLES `project_berita_acara` WRITE;
/*!40000 ALTER TABLE `project_berita_acara` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_berita_acara` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_chat_participants`
--

DROP TABLE IF EXISTS `project_chat_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_chat_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_muted` tinyint(1) DEFAULT 0,
  `last_read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_participant` (`project_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_chat_participants`
--

LOCK TABLES `project_chat_participants` WRITE;
/*!40000 ALTER TABLE `project_chat_participants` DISABLE KEYS */;
-- INSERT INTO `project_chat_participants` VALUES (cleaned);
/*!40000 ALTER TABLE `project_chat_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_comments`
--

DROP TABLE IF EXISTS `project_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `project_comments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `project_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `project_comments_ibfk_parent` FOREIGN KEY (`parent_id`) REFERENCES `project_comments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_comments`
--

LOCK TABLES `project_comments` WRITE;
/*!40000 ALTER TABLE `project_comments` DISABLE KEYS */;
-- INSERT INTO `project_comments` VALUES (cleaned);
/*!40000 ALTER TABLE `project_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_logs`
--

DROP TABLE IF EXISTS `project_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_logs_project_id` (`project_id`),
  KEY `idx_project_logs_project` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_logs`
--

LOCK TABLES `project_logs` WRITE;
/*!40000 ALTER TABLE `project_logs` DISABLE KEYS */;
-- INSERT INTO `project_logs` VALUES (cleaned);
/*!40000 ALTER TABLE `project_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_vendor_requirements`
--

DROP TABLE IF EXISTS `project_vendor_requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_vendor_requirements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `exam_type` varchar(255) DEFAULT NULL,
  `participant_count` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `assigned_vendor_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `project_vendor_requirements_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_vendor_requirements`
--

LOCK TABLES `project_vendor_requirements` WRITE;
/*!40000 ALTER TABLE `project_vendor_requirements` DISABLE KEYS */;
-- INSERT INTO `project_vendor_requirements` VALUES (cleaned);
/*!40000 ALTER TABLE `project_vendor_requirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `project_id` varchar(50) NOT NULL,
  `nama_project` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `sales_person_id` int(11) DEFAULT NULL,
  `jenis_pemeriksaan` text DEFAULT NULL,
  `foto_peserta` enum('Ya','Tidak') DEFAULT 'Tidak',
  `lunch` enum('Ya','Tidak') DEFAULT 'Tidak',
  `lunch_notes` text DEFAULT NULL,
  `snack` enum('Ya','Tidak') DEFAULT 'Tidak',
  `snack_notes` text DEFAULT NULL,
  `header_footer` enum('bumame','whitelabel','co-branding') DEFAULT 'bumame',
  `total_peserta` int(11) DEFAULT 0,
  `tanggal_mcu` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tanggal_mcu`)),
  `alamat` text DEFAULT NULL,
  `sph_file` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status_project` enum('need_approval_manager','need_approval_head','approved','rejected','cancelled','re-nego','in_progress_ops','process_vendor','vendor_assigned','no_vendor_needed','ready_for_invoicing','invoice_requested','invoiced','paid','completed') DEFAULT NULL,
  `project_type` enum('on_site','walk_in') NOT NULL DEFAULT 'on_site',
  `clinic_location` varchar(100) DEFAULT NULL,
  `status_vendor` enum('pending','requested','assigned','no_vendor_needed') DEFAULT 'pending',
  `korlap_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `approved_date_manager` timestamp NULL DEFAULT NULL,
  `approved_by_manager` int(11) DEFAULT NULL,
  `approved_date_head` timestamp NULL DEFAULT NULL,
  `approved_by_head` int(11) DEFAULT NULL,
  `consumption_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `procurement_lunch_qty` int(11) DEFAULT 0,
  `procurement_snack_qty` int(11) DEFAULT 0,
  `realization_lunch_qty` int(11) DEFAULT 0,
  `realization_snack_qty` int(11) DEFAULT 0,
  PRIMARY KEY (`project_id`),
  KEY `korlap_id` (`korlap_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_projects_status` (`status_project`),
  KEY `idx_projects_sales_person` (`sales_person_id`),
  KEY `idx_projects_korlap` (`korlap_id`),
  KEY `idx_projects_created` (`created_at`),
  KEY `idx_projects_tanggal_mcu` (`tanggal_mcu`(768)),
  KEY `idx_projects_status_created` (`status_project`,`created_at`),
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`korlap_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
-- INSERT INTO `projects` VALUES (cleaned);
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rab_items`
--

DROP TABLE IF EXISTS `rab_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rab_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rab_id` int(11) NOT NULL,
  `category` enum('personnel','transport','consumption','vendor') NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `qty` int(11) DEFAULT 0,
  `days` int(11) DEFAULT 0,
  `price` decimal(15,2) DEFAULT 0.00,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rab_id` (`rab_id`),
  CONSTRAINT `rab_items_ibfk_1` FOREIGN KEY (`rab_id`) REFERENCES `rabs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rab_items`
--

LOCK TABLES `rab_items` WRITE;
/*!40000 ALTER TABLE `rab_items` DISABLE KEYS */;
-- INSERT INTO `rab_items` VALUES (cleaned);
/*!40000 ALTER TABLE `rab_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rab_medical_result_dates`
--

DROP TABLE IF EXISTS `rab_medical_result_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rab_medical_result_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rab_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `personnel_count` int(11) NOT NULL DEFAULT 1,
  `personnel_details` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rab_id` (`rab_id`),
  CONSTRAINT `rab_medical_result_dates_ibfk_1` FOREIGN KEY (`rab_id`) REFERENCES `rab_medical_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rab_medical_result_dates`
--

LOCK TABLES `rab_medical_result_dates` WRITE;
/*!40000 ALTER TABLE `rab_medical_result_dates` DISABLE KEYS */;
-- INSERT INTO `rab_medical_result_dates` VALUES (cleaned);
/*!40000 ALTER TABLE `rab_medical_result_dates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rab_medical_results`
--

DROP TABLE IF EXISTS `rab_medical_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rab_medical_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `needs_hardcopy` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `status` enum('draft','submitted','approved_manager','approved_head','rejected') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_manager_by` int(11) DEFAULT NULL,
  `approved_manager_at` datetime DEFAULT NULL,
  `approved_head_by` int(11) DEFAULT NULL,
  `approved_head_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `rab_medical_results_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rab_medical_results`
--

LOCK TABLES `rab_medical_results` WRITE;
/*!40000 ALTER TABLE `rab_medical_results` DISABLE KEYS */;
-- INSERT INTO `rab_medical_results` VALUES (cleaned);
/*!40000 ALTER TABLE `rab_medical_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rab_realization_items`
--

DROP TABLE IF EXISTS `rab_realization_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rab_realization_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `realization_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `expense_code` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `is_extra_item` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `realization_id` (`realization_id`),
  CONSTRAINT `rab_realization_items_ibfk_1` FOREIGN KEY (`realization_id`) REFERENCES `rab_realizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rab_realization_items`
--

LOCK TABLES `rab_realization_items` WRITE;
/*!40000 ALTER TABLE `rab_realization_items` DISABLE KEYS */;
INSERT INTO `rab_realization_items` VALUES (1,1,'personnel','Admin',NULL,1.00,200000.00,200000.00,'',0),(2,1,'personnel','TTV',NULL,1.00,200000.00,200000.00,'',0),(3,1,'personnel','Plebo',NULL,1.00,200000.00,200000.00,'',0),(4,1,'personnel','Dokter',NULL,1.00,400000.00,400000.00,'',0),(5,1,'personnel','Petugas Loading',NULL,2.00,200000.00,400000.00,'',0),(6,1,'vendor','Rontgen',NULL,62.00,0.00,0.00,'Klinik Sejahtera',0),(7,1,'transport','BBM',NULL,2.00,50000.00,100000.00,'avanza zzzz, avanva 2222',0),(8,1,'transport','Tol',NULL,2.00,25000.00,50000.00,'avanza zzzz, avanva 2222',0),(9,1,'transport','EMERGENCY: Tambal Ban',NULL,1.00,50000.00,50000.00,'avanza zzzz',0),(10,1,'consumption','Air Mineral Petugas',NULL,5.00,2500.00,12500.00,'',0),(11,1,'consumption','Snack Peserta',NULL,62.00,0.00,0.00,'',0),(12,1,'consumption','Makan Siang Peserta',NULL,62.00,0.00,0.00,'',0),(13,1,'personnel','Overload Fee Dokter (11 pax)',NULL,1.00,165000.00,165000.00,'Auto-calculated: 61 pax > 50 cap (1 dr x 50)',1);
/*!40000 ALTER TABLE `rab_realization_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rab_realizations`
--

DROP TABLE IF EXISTS `rab_realizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rab_realizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rab_id` int(11) NOT NULL,
  `project_id` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `actual_participants` int(11) NOT NULL DEFAULT 0,
  `doctor_participants` int(11) DEFAULT 0,
  `doctor_fee_per_patient` decimal(15,2) NOT NULL DEFAULT 0.00,
  `doctor_total_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `accommodation_advance` decimal(15,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'submitted',
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rab_id` (`rab_id`),
  KEY `project_id` (`project_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rab_realizations`
--

LOCK TABLES `rab_realizations` WRITE;
/*!40000 ALTER TABLE `rab_realizations` DISABLE KEYS */;
INSERT INTO `rab_realizations` VALUES (1,1,'341','2026-02-07',1777500.00,61,61,15000.00,165000.00,200000.00,'','approved',16,'2026-02-05 11:49:52','2026-02-05 11:52:16');
/*!40000 ALTER TABLE `rab_realizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rabs`
--

DROP TABLE IF EXISTS `rabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rab_number` varchar(50) NOT NULL,
  `project_id` varchar(50) NOT NULL,
  `created_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('draft','need_approval_manager','need_approval_head','need_approval_ceo','approved','rejected','cancelled','submitted_to_finance','advance_paid','need_approval_realization','realization_approved','realization_rejected','completed') DEFAULT 'draft',
  `total_personnel` decimal(15,2) DEFAULT 0.00,
  `total_transport` decimal(15,2) DEFAULT 0.00,
  `total_consumption` decimal(15,2) DEFAULT 0.00,
  `grand_total` decimal(15,2) DEFAULT 0.00,
  `total_realization` decimal(15,2) DEFAULT 0.00,
  `location_type` enum('dalam_kota','luar_kota') NOT NULL,
  `selected_dates` text DEFAULT NULL,
  `total_participants` int(11) DEFAULT 0,
  `approved_by_manager` int(11) DEFAULT NULL,
  `approved_date_manager` datetime DEFAULT NULL,
  `approved_by_head` int(11) DEFAULT NULL,
  `approved_date_head` datetime DEFAULT NULL,
  `approved_by_ceo` int(11) DEFAULT NULL,
  `approved_date_ceo` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rejection_stage` varchar(50) DEFAULT NULL,
  `cost_value` decimal(15,2) DEFAULT NULL,
  `cost_percentage` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `submitted_to_finance_at` datetime DEFAULT NULL,
  `submitted_to_finance_by` int(11) DEFAULT NULL,
  `finance_paid_at` datetime DEFAULT NULL,
  `finance_paid_by` int(11) DEFAULT NULL,
  `transfer_proof_path` varchar(255) DEFAULT NULL,
  `finance_note` text DEFAULT NULL,
  `personnel_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rab_number` (`rab_number`),
  KEY `project_id` (`project_id`),
  KEY `status` (`status`),
  KEY `idx_rabs_status` (`status`),
  KEY `idx_rabs_project` (`project_id`),
  KEY `idx_rabs_created` (`created_date`),
  KEY `idx_rabs_creator` (`created_by`),
  KEY `idx_rabs_status_created` (`status`,`created_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rabs`
--

LOCK TABLES `rabs` WRITE;
/*!40000 ALTER TABLE `rabs` DISABLE KEYS */;
INSERT INTO `rabs` VALUES (1,'RAB/II/2026/001','341','2026-02-04',16,'completed','SETTLEMENT_1_1770267304.png',1400000.00,200000.00,12500.00,1612500.00,0.00,'dalam_kota','[\"2026-02-07\"]',62,13,'2026-02-04 16:27:51',14,'2026-02-04 16:28:13',NULL,NULL,'makan siangnya petugas tolong hilangkan aja (Oleh: Iqbal Adhika - Manager Ops pada 04/02/2026 16:26)',NULL,1700000.00,12.00,'2026-02-04 09:22:48','2026-02-05 04:55:04',NULL,NULL,NULL,NULL,NULL,NULL,'Petugas TTV sebagai petugas Visus juga');
/*!40000 ALTER TABLE `rabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_managers`
--

DROP TABLE IF EXISTS `sales_managers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_managers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_managers`
--

LOCK TABLES `sales_managers` WRITE;
/*!40000 ALTER TABLE `sales_managers` DISABLE KEYS */;
INSERT INTO `sales_managers` VALUES (3,'Senja Delima',NULL,NULL,'2026-01-23 01:49:06',26),(4,'Erick Erdiansyah',NULL,NULL,'2026-01-31 09:57:38',27),(5,'Rangga Aditya Perdhana',NULL,NULL,'2026-01-31 09:58:28',28),(6,'Sri Martonah',NULL,NULL,'2026-01-31 09:59:12',29);
/*!40000 ALTER TABLE `sales_managers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_persons`
--

DROP TABLE IF EXISTS `sales_persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_persons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_name` varchar(255) NOT NULL,
  `sales_manager_id` int(11) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_persons`
--

LOCK TABLES `sales_persons` WRITE;
/*!40000 ALTER TABLE `sales_persons` DISABLE KEYS */;
INSERT INTO `sales_persons` VALUES (4,'Ridho Erdiyansyah',3,NULL,'2026-01-23 01:49:15',30),(5,'Amalia Miladiati',3,NULL,'2026-01-23 04:20:05',31),(6,'Mohamad Siddiq',4,NULL,'2026-01-31 09:59:28',32),(7,'Chelsi Luisyena',4,NULL,'2026-01-31 09:59:38',33),(8,'Eha Nursaleha',4,NULL,'2026-01-31 09:59:46',34),(9,'Nur Aini Bafadhal',5,NULL,'2026-01-31 09:59:55',35),(10,'Thariq Anugerah Putra',3,NULL,'2026-01-31 10:00:04',36),(11,'Cindie Nathalia',5,NULL,'2026-01-31 10:00:22',37),(12,'Desillia Wati',6,NULL,'2026-01-31 10:00:39',38);
/*!40000 ALTER TABLE `sales_persons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES ('approval_head_medical_result','false','Enable/Disable Head Approval for Medical Result (true/false)','2026-02-04 04:08:35'),('approval_sla_days','1','SLA for Manager/Head approval (in working days)','2026-01-28 08:36:25'),('company_address','JL. TB SIMATUPANG NO.33 RT.01/ RW.05, RAGUNAN, PS MINGGU, JAKARTA SELATAN, DKI JAKARTA 12550','Alamat perusahaan yang muncul di header surat','2026-01-29 17:36:00'),('doctor_extra_fee','15000','Fee tambahan per pasien jika melebihi kapasitas (Overload Fee)','2026-01-29 17:24:35'),('doctor_max_patient','50','Kapasitas maksimal pasien per dokter (untuk perhitungan overload)','2026-01-29 17:24:35'),('fee_dalam_kota_Admin','200000','Fee Admin (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Audiometri','200000','Fee Audiometri (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Dokter','400000','Fee Dokter (Dalam Kota)','2026-01-29 12:13:02'),('fee_dalam_kota_Driver','200000','Fee Driver (Dalam Kota)','2026-01-29 12:13:02'),('fee_dalam_kota_EKG','200000','Fee EKG (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Feses','200000','Fee Feses (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Pap_Smear','200000','Fee Pap Smear (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Petugas_Loading','200000','Fee Petugas Loading (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Plebo','200000','Fee Plebo (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Rectal','200000','Fee Rectal (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Rontgen','200000','Fee Rontgen (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Spirometri','200000','Fee Spirometri (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Treadmill','200000','Fee Treadmill (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_TTV','200000','Fee TTV (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_USG_Abdomen','200000','Fee USG Abdomen (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_USG_Mammae','200000','Fee USG Mammae (Dalam Kota)','2026-01-28 17:59:52'),('fee_dalam_kota_Visus','200000','Fee Visus (Dalam Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Admin','250000','Fee Admin (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Audiometri','250000','Fee Audiometri (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Dokter','500000','Fee Dokter (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Driver','250000','Fee Driver (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_EKG','250000','Fee EKG (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Feses','250000','Fee Feses (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Pap_Smear','250000','Fee Pap Smear (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Petugas_Loading','250000','Fee Petugas Loading (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Plebo','250000','Fee Plebo (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Rectal','250000','Fee Rectal (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Rontgen','250000','Fee Rontgen (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Spirometri','250000','Fee Spirometri (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Treadmill','250000','Fee Treadmill (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_TTV','250000','Fee TTV (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_USG_Abdomen','250000','Fee USG Abdomen (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_USG_Mammae','250000','Fee USG Mammae (Luar Kota)','2026-01-28 17:59:52'),('fee_luar_kota_Visus','250000','Fee Visus (Luar Kota)','2026-01-28 17:59:52'),('max_projects_daily','5','Maximum projects allowed per day','2026-01-23 12:10:01'),('min_days_notice','3','Minimum days notice required for booking','2026-01-23 12:10:01'),('rab_items_consumption','Air Mineral Petugas, Lunch Petugas, Snack Petugas, Snack Peserta, Lunch Peserta','Daftar item konsumsi (JSON Format)','2026-01-30 18:28:52'),('rab_items_transport','BBM, Tol, Parkir - Project Operation, Tambal Ban, Transport Luar Kota B2B Project, Akomodasi Luar Kota B2B Project, Lalamove','Daftar item transportasi (JSON Format)','2026-01-30 18:28:52'),('rab_personnel_codes','admin=Admin\r\naudiometri=Audiometri\r\ndokter=Dokter\r\ndriver=Driver\r\nekg=EKG\r\nfeses=Nakes Feses\r\npap_smear=Pap smear\r\npetugas_loading=Petugas Loading\r\nplebo=Plebo\r\nrectal=Rectal\r\nrontgen=Rontgen\r\nspirometri=Spirometri\r\nttv=TTV\r\ntreadmill=Treadmill\r\nusg_abdomen=USG Abdomen\r\nusg_mammae=USG Mammae\r\nvisus=Visus','Mapping Expense Code untuk Petugas (Role -> Code, JSON Object)','2026-01-31 04:45:43'),('tat_calculation_mode','calendar','TAT Calculation Mode: calendar or working_days','2026-01-30 07:52:08'),('tat_config_rules','[{\"keyword\":\"Rectal\",\"days\":\"7\"},{\"keyword\":\"USG\",\"days\":4},{\"keyword\":\"Papsmear\",\"days\":10}]','Configuration Rules for TAT based on Exam Keywords (JSON)','2026-02-05 09:09:14'),('tat_normal_days','3','Standard Turn Around Time (days)','2026-01-30 07:52:08'),('vendor_memo_signer_2_name','Ari Yulis Tiansyah','Nama penanda tangan 2 (Approved By - Kanan Atas)','2026-01-23 15:48:43'),('vendor_memo_signer_2_title','Supervisor Operations','Jabatan penanda tangan 2 (Approved By - Kanan Atas)','2026-01-23 15:48:43');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `technical_meetings`
--

DROP TABLE IF EXISTS `technical_meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `technical_meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `tm_date` datetime NOT NULL,
  `tm_type` enum('Online','Offline') NOT NULL,
  `setting_alat_date` datetime DEFAULT NULL,
  `notes` text NOT NULL,
  `tm_file_path` varchar(255) NOT NULL,
  `layout_file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `tm_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `technical_meetings`
--

LOCK TABLES `technical_meetings` WRITE;
/*!40000 ALTER TABLE `technical_meetings` DISABLE KEYS */;
-- INSERT INTO `technical_meetings` VALUES (cleaned);
/*!40000 ALTER TABLE `technical_meetings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin_sales','admin_ops','manager_ops','head_ops','procurement','korlap','finance','ceo','dw_tim_hasil','surat_hasil','admin_gudang_warehouse','admin_gudang_aset','sales','manager_sales','sales_support_supervisor','sales_performance_manager') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (8,'superadmin','$2y$10$43CRakMez4p7kLlFkSmj6ezbLrrCOCpqfneg9wkkC0Bf.duKJIc/6','Super Administrator',NULL,'superadmin',1,'2026-01-22 10:39:51'),(11,'Admin_ops','$2y$10$WoLMuqUrgKCL.xqEuYxSiup0C35BFXX2ZFp9LlkJ.afv6QmABC0Wa','Ari Yulis Tiansyah','Supervisor Operations','admin_ops',1,'2026-01-23 02:00:48'),(12,'Admin_sales','$2y$10$eKJwQWUeKiZEZIKiyVJPYulXuBkWwgg8REX7JSWTBJ13BuZCyLDGy','Indah Permata W','Admin Sales','admin_sales',1,'2026-01-23 02:01:06'),(13,'Manager_ops','$2y$10$pym/ZIHInLuTRY/xh3rFf.i6p9kUD9soEJAZ4GZCsVbsNarPZyKw6','Iqbal Adhika','Manager Operations','manager_ops',1,'2026-01-23 02:01:27'),(14,'Head_ops','$2y$10$D.KmeExUk8XElYj4SO/FlO1nUcm0g48n/O.NSBT1owxA6KFfs88vC','Peter Lukito Ferdian','Head Operations','head_ops',1,'2026-01-23 02:02:42'),(15,'procurement','$2y$10$ZKnOesFR/JVWjccskE3nP.VuDa71i6NB14Lsm6ou30GVajhTwPAr2','Muhammad Fadhlurrahman Fajari','Procurement','procurement',1,'2026-01-23 02:03:24'),(16,'korlap','$2y$10$Yz8kzKjUq3OEfC6ozhlpXeuUgpRl3XqeUrz9OEpEov3TAMU073Slm','Eka Wijaya','Field Coordinator','korlap',1,'2026-01-23 02:03:44'),(17,'CEO','$2y$10$qLx7tBtlmZWD4UWxI9m50ujvMp/9sYKWc/eRtlydf.MZBgHgGtWg6','James Andrew Wihardja','Chief Executive Officer','ceo',1,'2026-01-29 06:36:39'),(18,'Finance','$2y$10$NL6gGLLMXSqGzzNiRBgwDu59RgR31IuzMdQt9NijKAD/1bEA3YuIW','Abdau Fajar','Finance','finance',1,'2026-01-29 08:04:55'),(21,'admingudangaset','$2y$10$CN.1RxSDqMl.zjUI3lfzSug6gmlus.P610G47WaFz52UikDhVMR26','Mohammad Riza Perdana','Warehouse Staff','admin_gudang_aset',1,'2026-01-31 04:43:31'),(22,'admingudangwarehouse','$2y$10$CN.1RxSDqMl.zjUI3lfzSug6gmlus.P610G47WaFz52UikDhVMR26','Anto Ruhiyat','Warehouse Staff','admin_gudang_warehouse',1,'2026-01-31 04:43:32'),(23,'kohas','$2y$10$EkBqTNv47D3Hk9YJWBz2puXpcrmheCrKeG/oki.m1tDo2hbKQuP2C','ervan Artiadi','Operation Support Lead','surat_hasil',1,'2026-01-31 09:50:48'),(24,'1','$2y$10$agt7co3aM0wlM4xtjW40juZv81pQx8R7WBlSfD.NcabsVQzrYn3uC','zizah','','dw_tim_hasil',1,'2026-02-03 15:21:56'),(25,'2','$2y$10$THUiQPX4PcCyg0Kq64sge.6q/.UCbSjeG92kPiZRr16zgX0JG0dFC','yusuf','','dw_tim_hasil',1,'2026-02-03 15:22:06'),(26,'senja','$2y$10$4SeRR/hqQQT5/m.Hc.XeaO5paNAoNdWGBpc9xk7Kwrj0j5l7PxuPa','Senja Delima',NULL,'manager_sales',1,'2026-02-03 15:43:49'),(27,'erick','$2y$10$cmrrT6QRUTnbDoNon.U5D.ZUikYg7ndao2QSrYXmdwyxdNeLzhBEC','Erick Erdiansyah',NULL,'manager_sales',1,'2026-02-03 15:43:49'),(28,'rangga','$2y$10$0XGmoO6fA3jba8cL3RYIF..SoXV34ic24p1X2IN6o1UPTdKmXuVvm','Rangga Aditya Perdhana',NULL,'manager_sales',1,'2026-02-03 15:43:49'),(29,'sri','$2y$10$4HNHPOgIaNmAVXmD9gMv/OnlY0/WqsvgyPDIFs97Z2OjryridWwUq','Sri Martonah',NULL,'manager_sales',1,'2026-02-03 15:43:49'),(30,'ridho','$2y$10$1uD.aTBlsHPLnc.nbWvmc.9MxZsXwjqQkGPxhaDlfXigPBmBFq0lK','Ridho Erdiyansyah',NULL,'sales',1,'2026-02-03 15:43:49'),(31,'amalia','$2y$10$H/R.zFphApnrqco9ac0cvurWOPLziMyFXurBPEDB0gdgmFuDBAUd2','Amalia Miladiati',NULL,'sales',1,'2026-02-03 15:43:50'),(32,'mohamad','$2y$10$h7QjtYsl.lpy3E4xIpWxLeuen2jXFoxnjatj.Ay2GW.puIcaeSlRe','Mohamad Siddiq',NULL,'sales',1,'2026-02-03 15:43:50'),(33,'chelsi','$2y$10$2Pg2qX7QrRFRsJ.IQCYV3.Y8W2/KgN3eB32D4AAWCt.fLt6ADCEpW','Chelsi Luisyena',NULL,'sales',1,'2026-02-03 15:43:50'),(34,'eha','$2y$10$LBn9ZpL8/EKeQmINoB1Ix.5U7/xAC1snAdrNmprfC2IoO720TJDl6','Eha Nursaleha',NULL,'sales',1,'2026-02-03 15:43:50'),(35,'nur','$2y$10$Kv7rINAg2abhYQOyvJ16mu0L0aaU1XvdkMxl0qpC3QqS6.LTvCtkm','Nur Aini Bafadhal',NULL,'sales',1,'2026-02-03 15:43:50'),(36,'thariq','$2y$10$n1t.5h/Xsm/GDc7Bxzar/.ueJ33EE5C2FEHDcn9I8wpAEMKiHdQya','Thariq Anugerah Putra',NULL,'sales',1,'2026-02-03 15:43:50'),(37,'cindie','$2y$10$k0tEDbFU0hkJecOkY2/11e8VHLCSpXqv9WzCQKd1lpzyLt1Xjdzce','Cindie Nathalia',NULL,'sales',1,'2026-02-03 15:43:50'),(38,'desillia','$2y$10$J16mbRB3saTLbyT2h.uKju9kv8dNMfV7U08o/fcScrIO.4pXCtxqq','Desillia Wati',NULL,'sales',1,'2026-02-03 15:43:50'),(40,'spv_sales','$2y$10$/f9hLscUAKh2MwSsLC36ae0xmZlsj3bvF.zs9fQ4hc8FfCj.sv/Ie','SPV Sales','SPV Sales','sales_support_supervisor',1,'2026-02-04 06:43:02'),(41,'perf_manager_sales','$2y$10$6B.0z2HSmRBUj0Kd7exWpuJ05dvZwnojJbav2i6p3pf.p5hY0Q3FG','Performance Manager Sales','Performance Manager Sales','sales_performance_manager',1,'2026-02-04 06:43:02'),(42,'dw_surat_hasil','$2y$10$k4GS6sHPsRUnBi3eeEYKk.hG1vih01eAkhsOr4crBGCKIuYYNZuWS','DW Surat Hasil','DW Surat Hasil','dw_tim_hasil',1,'2026-02-04 06:43:03'),(43,'wildan','$2y$10$IvHkekArf1kGmz8FZVjOGuZ7rJUM93j1q2RNzwRn7zuPk/..jgdTa','wildan','','sales_support_supervisor',1,'2026-02-04 06:43:17'),(44,'paksam','$2y$10$D3LZqwBZJdFWpcNUT5QVz.rix2w13i.8swRvQqjln15/FKoMcqX9S','pak sam','','sales_performance_manager',1,'2026-02-04 06:43:30'),(45,'surat_hasil','$2y$10$2sAd5yregcOqhHev6B32FOqsDyT7UnD9mQDIvbv3paV1aNwnymA3q','Surat Hasil (Kohas)','Surat Hasil (Kohas)','surat_hasil',1,'2026-02-04 08:54:49'),(46,'reza.mahendra@bumame.com','$2y$10$uNdVsjIMJNWcNlsztkXqQOuLy0SlbC7AIycrGIBDs6GssrE2RhGDe','reza','Manager Operations','manager_ops',1,'2026-02-05 09:33:23');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `pic_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `services` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (1,'Klinik Sejahtera',NULL,'Pak Irawan','','2026-01-23 10:32:45','Rontgen, EKG, Spirometri');
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_requests`
--

DROP TABLE IF EXISTS `warehouse_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_request_id` int(11) NOT NULL,
  `warehouse_type` enum('GUDANG_ASET','GUDANG_KONSUMABLE') NOT NULL,
  `status` enum('PENDING','IN_PREPARATION','READY','COMPLETED') DEFAULT 'PENDING',
  `proof_file` text DEFAULT NULL,
  `prepared_by` int(11) DEFAULT NULL,
  `prepared_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `inventory_request_id` (`inventory_request_id`),
  KEY `prepared_by` (`prepared_by`),
  KEY `idx_warehouse_inventory` (`inventory_request_id`),
  KEY `idx_warehouse_type` (`warehouse_type`),
  KEY `idx_warehouse_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_requests`
--

LOCK TABLES `warehouse_requests` WRITE;
/*!40000 ALTER TABLE `warehouse_requests` DISABLE KEYS */;
-- INSERT INTO `warehouse_requests` VALUES (cleaned);
/*!40000 ALTER TABLE `warehouse_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `man_powers`
--

DROP TABLE IF EXISTS `man_powers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `man_powers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` enum('Internal','External') DEFAULT 'Internal',
  `email` varchar(255) DEFAULT NULL,
  `skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_man_powers_name` (`name`),
  KEY `idx_man_powers_status` (`status`),
  KEY `idx_man_powers_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `man_powers`
--

LOCK TABLES `man_powers` WRITE;
/*!40000 ALTER TABLE `man_powers` DISABLE KEYS */;
/*!40000 ALTER TABLE `man_powers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_man_power`
--

DROP TABLE IF EXISTS `project_man_power`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_man_power` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `man_power_id` int(11) NOT NULL,
  `role` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `doctor_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`doctor_details`)),
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `man_power_id` (`man_power_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_pmp_date` (`date`),
  KEY `idx_pmp_project_date` (`project_id`,`date`),
  KEY `idx_pmp_man_power_date` (`man_power_id`,`date`),
  CONSTRAINT `project_man_power_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `project_man_power_ibfk_2` FOREIGN KEY (`man_power_id`) REFERENCES `man_powers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_man_power`
--

LOCK TABLES `project_man_power` WRITE;
/*!40000 ALTER TABLE `project_man_power` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_man_power` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-05 18:14:03

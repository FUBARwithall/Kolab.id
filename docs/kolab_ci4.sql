/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: kolab_ci4
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

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
-- Table structure for table `apify_jobs`
--

DROP TABLE IF EXISTS `apify_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `apify_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `actor_type` enum('profile','post') NOT NULL,
  `target_url` varchar(255) NOT NULL,
  `status` enum('QUEUED','RUNNING','SUCCEEDED','FAILED') NOT NULL DEFAULT 'QUEUED',
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`)),
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_aj_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apify_jobs`
--

LOCK TABLES `apify_jobs` WRITE;
/*!40000 ALTER TABLE `apify_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `apify_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Marketing',
  `author_name` varchar(150) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('DRAFT','PUBLISHED') NOT NULL DEFAULT 'DRAFT',
  `author_id` int(10) unsigned NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_art_status` (`status`),
  KEY `fk_art_author` (`author_id`),
  CONSTRAINT `fk_art_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES
(9,'Tes','tes','Tes lorem ipsum dolor sit amet','Tes lorem ipsum dolor sit amet&nbsp;Tes lorem ipsum dolor sit amet','20261020144451-2026-10-20articles144437.png',NULL,'Bismillah','developer','kol, keren, bismillah',0,14,'PUBLISHED',1,'2026-10-20 15:04:24','2026-10-20 14:44:51','2026-10-27 08:24:16'),
(22,'Panduan Strategi Influencer Marketing Enterprise: Mengukur Efektivitas & ROI Campaign','panduan-strategi-influencer-marketing-enterprise','Pelajari langkah taktis merancang kampanye KOL enterprise: mulai dari penetapan metrik kinerja utama (KPI), eliminasi fake engagement dengan AI, hingga kalkulasi Return on Investment (ROI) secara akurat.','<p>Dalam lanskap digital yang kian kompetitif, kampanye <strong>Key Opinion Leader (KOL)</strong> bukan lagi sekadar membagikan produk atau menyebarkan pesan pemasaran umum. Bagi perusahaan skala enterprise, influencer marketing telah bertransformasi menjadi pilar strategis yang menuntut <strong>presisi data</strong>, <strong>transparansi metrik</strong>, dan akuntabilitas <em>Return on Investment (ROI)</em> yang terukur.</p>\r\n\r\n<h2>1. Paradigma Baru: Dari Vanity Metrics ke Metrik Berdampak Nyata</h2>\r\n\r\n<p>Banyak brand terjebak dalam metrik semu seperti jumlah pengikut (<em>followers count</em>) atau interaksi awal yang rentan terhadap manipulasi bot. Di level enterprise, keberhasilan diukur melalui <strong>tiga pilar utama</strong>:</p>\r\n\r\n<ul>\r\n	<li><strong>Audience Quality Score (AQS):</strong> Memverifikasi keaslian audiens kreator guna memastikan rasio audiens aktif dan relevan berada di atas ambang batas 85%.</li>\r\n	<li><strong>Cost Per True Engagement (CPTE):</strong> Menghitung biaya riil per interaksi bermakna (komentar berkualitas, share, bookmark) ketimbang sekadar impresi pasif.</li>\r\n	<li><strong>Conversion &amp; Attribution Velocity:</strong> Memantau laju konversi dari klik link unik, kode voucher eksklusif, hingga pendaftaran lead secara real-time.</li>\r\n</ul>\r\n\r\n<blockquote>&quot;Kampanye KOL yang efektif bukan tentang siapa yang memiliki audiens terbesar, melainkan siapa yang memiliki tingkat kepercayaan (trust equity) tertinggi di mata segmen audiens yang tepat.&quot;</blockquote>\r\n\r\n<h2>2. Empat Langkah Taktis Pelaksanaan Campaign</h2>\r\n\r\n<p>Mengelola puluhan hingga ratusan kreator secara serentak membutuhkan alur orkestrasi yang terstandarisasi. Berikut kerangka kerja yang terbukti menghasilkan efisiensi operasional hingga 60%:</p>\r\n\r\n<ol>\r\n	<li><strong>Data-Driven Discovery:</strong> Seleksi kreator berdasarkan demografi audiens mendalam (lokasi, usia, minat, dan daya beli), bukan sekadar preferensi subjektif.</li>\r\n	<li><strong>SLA &amp; Brief Otomatis:</strong> Distribusikan dokumen brief kreatif terintegrasi dengan klausul hak cipta dan ketentuan jadwal publikasi yang tersinkronisasi.</li>\r\n	<li><strong>Live Monitoring &amp; Validasi Real-Time:</strong> Lacak status penayangan konten otomatis menggunakan teknologi <em>link scraping</em> dan <em>timestamp tracking</em> tanpa perlu meminta tangkapan layar manual.</li>\r\n	<li><strong>Rekonsiliasi Payout &amp; Pajak:</strong> Pencairan dana escrow otomatis begitu seluruh deliverables lolos verifikasi, lengkap dengan bukti potong pajak PPh 21/23 resmi.</li>\r\n</ol>\r\n\r\n<h3>Tips Optimalisasi Konten Kreator</h3>\r\n\r\n<p>Untuk memastikan performa optimal, berikan ruang kreasi yang cukup bagi KOL seraya tetap menjaga <strong>brand safety guidelines</strong>:</p>\r\n\r\n<ul>\r\n	<li><strong>3 Detik Pertama (The Hook):</strong> Pastikan kreator membuka video pendek dengan masalah nyata yang langsung relevan dengan pain-point penonton.</li>\r\n	<li><strong>Storytelling Organik:</strong> Hindari skrip kaku bergaya iklan konvensional. Integrasikan fitur produk ke dalam rutinitas keseharian kreator.</li>\r\n	<li><strong>Clear Call-to-Action (CTA):</strong> Berikan satu instruksi spesifik dan mudah dilakukan (misal: <em>&quot;Cek link di bio untuk mendapatkan diskon peluncuran 20%&quot;</em>).</li>\r\n</ul>\r\n\r\n<h2>3. Kesimpulan</h2>\r\n\r\n<p>Dengan memadukan analitik mendalam, automasi alur kerja, dan kompensasi berbasis performa, sistem pemasaran berbasis influencer mampu menjadi kanal pertumbuhan yang konsisten dan berdaya saing tinggi bagi brand enterprise Anda.</p>','20261020144451-2026-10-20articles144437.png',NULL,'Insight, Riset, Strategi','developer','Influencer Marketing, ROI, Enterprise, KOL Analytics',1,21,'PUBLISHED',1,'2026-10-21 09:29:57','2026-10-21 09:29:57','2026-10-26 15:38:41');
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `storage_path` varchar(255) NOT NULL,
  `uploader_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_att_uploader` (`uploader_id`),
  CONSTRAINT `fk_att_uploader` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(100) NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_al_entity` (`entity_type`,`entity_id`),
  KEY `idx_al_user` (`user_id`),
  CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brief_approval_histories`
--

DROP TABLE IF EXISTS `brief_approval_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `brief_approval_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `brief_id` int(10) unsigned NOT NULL,
  `reviewed_by` int(10) unsigned NOT NULL,
  `status` enum('APPROVED','REVISION_NEEDED','REJECTED') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bah_brief` (`brief_id`),
  KEY `fk_bah_user` (`reviewed_by`),
  CONSTRAINT `fk_bah_brief` FOREIGN KEY (`brief_id`) REFERENCES `briefs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bah_user` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brief_approval_histories`
--

LOCK TABLES `brief_approval_histories` WRITE;
/*!40000 ALTER TABLE `brief_approval_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `brief_approval_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brief_content_histories`
--

DROP TABLE IF EXISTS `brief_content_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `brief_content_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `brief_id` int(10) unsigned NOT NULL,
  `content_diff` text NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bch_brief` (`brief_id`),
  KEY `fk_bch_user` (`updated_by`),
  CONSTRAINT `fk_bch_brief` FOREIGN KEY (`brief_id`) REFERENCES `briefs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bch_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brief_content_histories`
--

LOCK TABLES `brief_content_histories` WRITE;
/*!40000 ALTER TABLE `brief_content_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `brief_content_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `briefs`
--

DROP TABLE IF EXISTS `briefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `briefs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `guidelines` text NOT NULL,
  `dos` text DEFAULT NULL,
  `donts` text DEFAULT NULL,
  `reference_url` varchar(255) DEFAULT NULL,
  `approval_status` enum('PENDING','APPROVED','REVISION_NEEDED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `revision_notes` text DEFAULT NULL,
  `submitted_by` int(10) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_briefs_task` (`task_id`),
  KEY `fk_briefs_user` (`submitted_by`),
  CONSTRAINT `fk_briefs_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_briefs_user` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `briefs`
--

LOCK TABLES `briefs` WRITE;
/*!40000 ALTER TABLE `briefs` DISABLE KEYS */;
/*!40000 ALTER TABLE `briefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_allowed_categories`
--

DROP TABLE IF EXISTS `campaign_allowed_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_allowed_categories` (
  `campaign_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`category_id`),
  KEY `fk_cac_cat` (`category_id`),
  CONSTRAINT `fk_cac_camp` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cac_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_allowed_categories`
--

LOCK TABLES `campaign_allowed_categories` WRITE;
/*!40000 ALTER TABLE `campaign_allowed_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_allowed_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_allowed_tiers`
--

DROP TABLE IF EXISTS `campaign_allowed_tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_allowed_tiers` (
  `campaign_id` int(10) unsigned NOT NULL,
  `tier_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`tier_id`),
  KEY `fk_cat_tier` (`tier_id`),
  CONSTRAINT `fk_cat_camp` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cat_tier` FOREIGN KEY (`tier_id`) REFERENCES `tiers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_allowed_tiers`
--

LOCK TABLES `campaign_allowed_tiers` WRITE;
/*!40000 ALTER TABLE `campaign_allowed_tiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_allowed_tiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_applications`
--

DROP TABLE IF EXISTS `campaign_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_applications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `kol_id` int(10) unsigned NOT NULL,
  `pitch_notes` text DEFAULT NULL,
  `proposed_rate` decimal(15,2) DEFAULT NULL,
  `status` enum('APPLIED','ACCEPTED','REJECTED') NOT NULL DEFAULT 'APPLIED',
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_camp_kol_app` (`campaign_id`,`kol_id`),
  KEY `fk_capp_kol` (`kol_id`),
  KEY `fk_capp_user` (`reviewed_by`),
  CONSTRAINT `fk_capp_camp` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_capp_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_capp_user` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_applications`
--

LOCK TABLES `campaign_applications` WRITE;
/*!40000 ALTER TABLE `campaign_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_kol_contracts`
--

DROP TABLE IF EXISTS `campaign_kol_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_kol_contracts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_kol_id` int(10) unsigned NOT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `contract_number` varchar(100) NOT NULL,
  `clauses_rendered` longtext NOT NULL,
  `signed_at` datetime DEFAULT NULL,
  `signed_ip` varchar(45) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_number` (`contract_number`),
  KEY `fk_ckc_ck` (`campaign_kol_id`),
  KEY `fk_ckc_tmpl` (`template_id`),
  CONSTRAINT `fk_ckc_ck` FOREIGN KEY (`campaign_kol_id`) REFERENCES `campaign_kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ckc_tmpl` FOREIGN KEY (`template_id`) REFERENCES `contract_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_kol_contracts`
--

LOCK TABLES `campaign_kol_contracts` WRITE;
/*!40000 ALTER TABLE `campaign_kol_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_kol_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_kol_variants`
--

DROP TABLE IF EXISTS `campaign_kol_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_kol_variants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_kol_id` int(10) unsigned NOT NULL,
  `variant_id` int(10) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ckv_ck` (`campaign_kol_id`),
  KEY `fk_ckv_var` (`variant_id`),
  CONSTRAINT `fk_ckv_ck` FOREIGN KEY (`campaign_kol_id`) REFERENCES `campaign_kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ckv_var` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_kol_variants`
--

LOCK TABLES `campaign_kol_variants` WRITE;
/*!40000 ALTER TABLE `campaign_kol_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_kol_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_kols`
--

DROP TABLE IF EXISTS `campaign_kols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_kols` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned NOT NULL,
  `kol_id` int(10) unsigned NOT NULL,
  `deal_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_vt` int(10) unsigned NOT NULL DEFAULT 1,
  `contract_status` enum('DRAFT','SENT','SIGNED') NOT NULL DEFAULT 'DRAFT',
  `payment_status` enum('UNPAID','PARTIAL','PAID') NOT NULL DEFAULT 'UNPAID',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_camp_kol_deal` (`campaign_id`,`kol_id`),
  KEY `fk_ck_kol` (`kol_id`),
  KEY `fk_ck_creator` (`created_by`),
  CONSTRAINT `fk_ck_camp` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ck_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ck_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_kols`
--

LOCK TABLES `campaign_kols` WRITE;
/*!40000 ALTER TABLE `campaign_kols` DISABLE KEYS */;
INSERT INTO `campaign_kols` VALUES
(1,1,2,15000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-22 09:03:36',NULL),
(2,2,3,10000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-22 10:16:08',NULL),
(3,1,4,1000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-09-14 10:44:39',NULL),
(4,1,10,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-09-16 12:56:12',NULL),
(5,1,8,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-09-16 14:05:57',NULL),
(6,3,9,2000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-09-18 09:39:57',NULL),
(7,3,7,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-09-18 12:36:11',NULL),
(8,4,10,1500000.00,1,'DRAFT','UNPAID',NULL,1,'2026-09-21 13:14:20',NULL),
(9,5,2,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-03 09:00:00',NULL),
(10,5,3,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-04 09:00:00',NULL),
(11,5,4,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-05 09:00:00',NULL),
(12,5,5,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-05 09:00:00',NULL),
(13,5,6,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-06 09:00:00',NULL),
(14,5,7,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-06 09:00:00',NULL),
(15,5,8,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-08 09:00:00',NULL),
(16,5,9,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-08 09:00:00',NULL),
(17,5,10,5000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-08-10 09:00:00',NULL),
(18,6,2,0.00,1,'DRAFT','UNPAID',NULL,1,'2026-10-06 13:14:17',NULL),
(19,7,9,1000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-10-06 14:36:56',NULL),
(20,8,3,2000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-10-06 14:19:36',NULL),
(21,8,5,2000000.00,1,'DRAFT','UNPAID',NULL,1,'2026-10-09 10:11:37',NULL);
/*!40000 ALTER TABLE `campaign_kols` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_products`
--

DROP TABLE IF EXISTS `campaign_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_products` (
  `campaign_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`product_id`),
  KEY `fk_cp_prod` (`product_id`),
  CONSTRAINT `fk_cp_camp` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cp_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_products`
--

LOCK TABLES `campaign_products` WRITE;
/*!40000 ALTER TABLE `campaign_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_required_platforms`
--

DROP TABLE IF EXISTS `campaign_required_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_required_platforms` (
  `campaign_id` int(10) unsigned NOT NULL,
  `platform_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`platform_id`),
  KEY `fk_crp_plat` (`platform_id`),
  CONSTRAINT `fk_crp_camp` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_crp_plat` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_required_platforms`
--

LOCK TABLES `campaign_required_platforms` WRITE;
/*!40000 ALTER TABLE `campaign_required_platforms` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_required_platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_variants`
--

DROP TABLE IF EXISTS `campaign_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaign_variants` (
  `campaign_id` int(10) unsigned NOT NULL,
  `variant_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`variant_id`),
  KEY `fk_cv_var` (`variant_id`),
  CONSTRAINT `fk_cv_camp` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cv_var` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_variants`
--

LOCK TABLES `campaign_variants` WRITE;
/*!40000 ALTER TABLE `campaign_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `brand_id` int(10) unsigned DEFAULT NULL,
  `budget_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `target_views` int(10) unsigned NOT NULL DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('DRAFT','ACTIVE','COMPLETED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  `is_open_registration` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_camp_status` (`status`),
  KEY `idx_camp_created_by` (`created_by`),
  CONSTRAINT `fk_camp_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
INSERT INTO `campaigns` VALUES
(1,'Metafora Parfume Launching Day','metafora-parfume-launching-day-5',NULL,125000000.00,1200000,'2026-08-31','2026-09-11','COMPLETED',0,'',1,'2026-08-22 09:02:53','2026-09-14 08:40:04',NULL),
(2,'Campaign Baru','campaign-baru-6',NULL,20000000.00,1200000,'2026-09-01','2026-09-27','COMPLETED',0,'Campaign Baru',1,'2026-08-22 10:11:55','2026-09-28 13:20:14',NULL),
(3,'Summer Glow Campaign (Open Post)','summer-glow-campaign-open-post-19',NULL,10000000.00,50000,'2026-09-30','2026-10-15','COMPLETED',1,'Test Open Post Campaign for KOL registration',1,'2026-09-18 02:05:18','2026-10-16 08:32:50',NULL),
(4,'Test Campaign','test-campaign-20',NULL,10000000.00,1000000,'2026-10-12','2026-10-31','ACTIVE',1,'Test campaign',1,'2026-09-21 08:38:11','2026-10-13 13:32:34',NULL),
(5,'Metafora Exclusive Summer Launch 2026','metafora-exclusive-summer-launch-2026-21',NULL,50000000.00,1000000,'2026-08-01','2026-08-31','COMPLETED',0,NULL,1,'2026-08-01 08:00:00','2026-08-31 18:00:00',NULL),
(6,'New Year','new-year-22',NULL,100000000.00,1000000,'2026-12-20','2027-01-03','ACTIVE',1,'new year',1,'2026-10-06 13:01:04','2026-10-06 13:06:39',NULL),
(7,'maulid','maulid-23',NULL,200000000.00,1000000,'2026-11-06','2026-12-11','ACTIVE',1,'ok',1,'2026-10-06 14:32:03','2026-10-06 14:34:10',NULL),
(8,'tes','tes-27',NULL,20000000.00,100000,'2026-11-10','2026-12-10','DRAFT',0,'oke',1,'2026-10-06 14:19:14','2026-10-06 14:19:14',NULL);
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,'Beauty & Fashion','beauty-fashion','Beauty & Fashion','2026-08-22 08:40:34','2026-08-22 08:40:34'),
(2,'Tech','tech','Technology','2026-08-22 08:40:46','2026-08-22 08:40:46'),
(3,'Gaming','gaming','Gaming','2026-08-22 08:40:56','2026-08-22 08:40:56'),
(4,'Automotive','automotive','Automotive','2026-08-22 08:41:06','2026-08-22 08:41:06'),
(5,'Horror','horror','Horror','2026-09-08 08:38:49','2026-09-08 08:38:49'),
(6,'Culinary','culinary','Culinary','2026-09-08 08:39:02','2026-09-08 08:39:02'),
(7,'Lifestyle','lifestyle','Lifestyle','2026-09-08 09:04:14','2026-09-08 09:04:14');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contract_templates`
--

DROP TABLE IF EXISTS `contract_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `body_template` longtext NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contract_templates`
--

LOCK TABLES `contract_templates` WRITE;
/*!40000 ALTER TABLE `contract_templates` DISABLE KEYS */;
INSERT INTO `contract_templates` VALUES
(1,'Perjanjian Kerjasama Kolaborasi Konten (Standard)','SURAT PERJANJIAN KERJASAMA PEMBUATAN KONTEN\n\nPada hari ini, telah disepakati kerjasama antara:\n1. Pihak Pertama (Brand): {brand_name}\n2. Pihak Kedua (KOL): {kol_name} (NIK: {kol_nik}, Rekening: {kol_bank_account})\n\nPASAL 1: RUANG LINGKUP PEKERJAAN\nPihak Kedua bersedia memproduksi konten video ({total_vt} Video) untuk produk parfum {product_name} sesuai brief yang telah disetujui.\n\nPASAL 2: HONORARIUM & PEMBAYARAN\nTotal kompensasi sebesar {deal_amount} yang akan dibayarkan setelah seluruh konten tayang dan invoice terverifikasi dipotong pajak penghasilan yang berlaku.\n\nPASAL 3: HAK CIPTA\nHak penggunaan materi video menjadi milik bersama antara Brand dan Kreator untuk keperluan komersial promosi.',1,'2026-10-27 08:43:47',NULL);
/*!40000 ALTER TABLE `contract_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draft_review_histories`
--

DROP TABLE IF EXISTS `draft_review_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `draft_review_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `draft_id` int(10) unsigned NOT NULL,
  `reviewed_by` int(10) unsigned NOT NULL,
  `status` enum('APPROVED','REVISION_NEEDED') NOT NULL,
  `timestamp_notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`timestamp_notes`)),
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_drh_draft` (`draft_id`),
  KEY `fk_drh_user` (`reviewed_by`),
  CONSTRAINT `fk_drh_draft` FOREIGN KEY (`draft_id`) REFERENCES `drafts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_drh_user` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draft_review_histories`
--

LOCK TABLES `draft_review_histories` WRITE;
/*!40000 ALTER TABLE `draft_review_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `draft_review_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drafts`
--

DROP TABLE IF EXISTS `drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `drafts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `video_url` varchar(255) NOT NULL,
  `status` enum('PENDING','APPROVED','REVISION_NEEDED') NOT NULL DEFAULT 'PENDING',
  `review_notes` text DEFAULT NULL,
  `timestamp_notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`timestamp_notes`)),
  `submitted_at` datetime NOT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_drafts_task` (`task_id`),
  KEY `fk_drafts_reviewer` (`reviewed_by`),
  CONSTRAINT `fk_drafts_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_drafts_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drafts`
--

LOCK TABLES `drafts` WRITE;
/*!40000 ALTER TABLE `drafts` DISABLE KEYS */;
/*!40000 ALTER TABLE `drafts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internal_evaluation`
--

DROP TABLE IF EXISTS `internal_evaluation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `internal_evaluation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `staff_user_id` int(10) unsigned NOT NULL,
  `evaluation_period` varchar(30) NOT NULL,
  `total_deals` int(10) unsigned NOT NULL DEFAULT 0,
  `budget_managed` decimal(15,2) NOT NULL DEFAULT 0.00,
  `budget_deviation` decimal(15,2) NOT NULL DEFAULT 0.00,
  `kpi_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `leader_notes` text DEFAULT NULL,
  `evaluated_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ie_staff` (`staff_user_id`),
  KEY `fk_ie_evaluator` (`evaluated_by`),
  CONSTRAINT `fk_ie_evaluator` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ie_staff` FOREIGN KEY (`staff_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internal_evaluation`
--

LOCK TABLES `internal_evaluation` WRITE;
/*!40000 ALTER TABLE `internal_evaluation` DISABLE KEYS */;
/*!40000 ALTER TABLE `internal_evaluation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_histories`
--

DROP TABLE IF EXISTS `invoice_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `from_status` varchar(50) NOT NULL,
  `to_status` varchar(50) NOT NULL,
  `changed_by` int(10) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ih_inv` (`invoice_id`),
  KEY `fk_ih_user` (`changed_by`),
  CONSTRAINT `fk_ih_inv` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ih_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_histories`
--

LOCK TABLES `invoice_histories` WRITE;
/*!40000 ALTER TABLE `invoice_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_sequences`
--

DROP TABLE IF EXISTS `invoice_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_sequences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(10) unsigned NOT NULL,
  `month` int(10) unsigned NOT NULL,
  `last_number` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_is_year_month` (`year`,`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_sequences`
--

LOCK TABLES `invoice_sequences` WRITE;
/*!40000 ALTER TABLE `invoice_sequences` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_kol_id` int(10) unsigned NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_type` enum('PPh21','PPh23','NONE') NOT NULL DEFAULT 'PPh21',
  `tax_rate` decimal(6,4) NOT NULL DEFAULT 0.0000,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('DRAFT','SUBMITTED','VERIFIED','PAID','REJECTED') NOT NULL DEFAULT 'DRAFT',
  `invoice_file` varchar(255) DEFAULT NULL,
  `bupot_file` varchar(255) DEFAULT NULL,
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_inv_ck` (`campaign_kol_id`),
  KEY `fk_inv_verifier` (`verified_by`),
  CONSTRAINT `fk_inv_ck` FOREIGN KEY (`campaign_kol_id`) REFERENCES `campaign_kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_approval_histories`
--

DROP TABLE IF EXISTS `kol_approval_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_approval_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kol_id` int(10) unsigned NOT NULL,
  `reviewed_by` int(10) unsigned NOT NULL,
  `status` enum('APPROVED','REJECTED') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kah_kol` (`kol_id`),
  KEY `fk_kah_user` (`reviewed_by`),
  CONSTRAINT `fk_kah_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kah_user` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_approval_histories`
--

LOCK TABLES `kol_approval_histories` WRITE;
/*!40000 ALTER TABLE `kol_approval_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `kol_approval_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_categories`
--

DROP TABLE IF EXISTS `kol_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_categories` (
  `kol_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`kol_id`,`category_id`),
  KEY `fk_kc_cat` (`category_id`),
  CONSTRAINT `fk_kc_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kc_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_categories`
--

LOCK TABLES `kol_categories` WRITE;
/*!40000 ALTER TABLE `kol_categories` DISABLE KEYS */;
INSERT INTO `kol_categories` VALUES
(1,1),
(1,2);
/*!40000 ALTER TABLE `kol_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_platforms`
--

DROP TABLE IF EXISTS `kol_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_platforms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kol_id` int(10) unsigned NOT NULL,
  `platform_id` int(10) unsigned NOT NULL,
  `username` varchar(100) NOT NULL,
  `profile_url` varchar(255) DEFAULT NULL,
  `followers_count` int(10) unsigned NOT NULL DEFAULT 0,
  `avg_views` int(10) unsigned NOT NULL DEFAULT 0,
  `avg_er` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `last_scraped_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kol_plat_user` (`platform_id`,`username`),
  KEY `idx_kp_kol` (`kol_id`),
  CONSTRAINT `fk_kp_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kp_plat` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_platforms`
--

LOCK TABLES `kol_platforms` WRITE;
/*!40000 ALTER TABLE `kol_platforms` DISABLE KEYS */;
INSERT INTO `kol_platforms` VALUES
(1,1,1,'demotalent_perfume','https://tiktok.com/@demotalent_perfume',125000,35000,0.0450,NULL,'2026-10-27 08:43:47',NULL),
(2,2,2,'jessicajane99',NULL,0,747834,0.1837,NULL,'2026-08-31 15:35:55',NULL),
(3,2,3,'JessicaJane99',NULL,0,391263,0.0170,NULL,'2026-08-31 15:35:55',NULL),
(4,2,1,'graciajessicajane',NULL,0,2052360,0.1003,NULL,'2026-08-31 15:35:55',NULL),
(6,2,4,'grcjessicajane',NULL,0,0,0.0000,NULL,'2026-08-31 15:35:55',NULL),
(7,3,2,'mamankkuliner','https://www.instagram.com/mamankkuliner',0,36300,0.0886,NULL,'2026-09-01 09:06:40',NULL),
(8,3,3,'MamankKuliner','https://www.youtube.com/@MamankKuliner',0,284789,0.0142,NULL,'2026-09-01 09:06:40',NULL),
(9,4,3,'tanboykun','https://www.youtube.com/@tanboykun',0,1546565,0.0132,NULL,'2026-09-04 10:24:19',NULL),
(10,5,3,'RJL5-FAJARADITYA',NULL,0,1333313,0.0098,NULL,'2026-09-08 08:41:23',NULL),
(11,5,2,'donfajaraditya',NULL,0,35958,0.0490,NULL,'2026-09-08 08:41:23',NULL),
(12,5,4,'RJL5_official',NULL,0,0,0.0000,NULL,'2026-09-08 08:41:23',NULL),
(13,5,1,'donfajaraditya',NULL,0,468670,0.0526,NULL,'2026-09-08 08:41:23',NULL),
(14,5,5,'RJL5OfficialAccount',NULL,0,0,0.0000,NULL,'2026-09-08 08:41:23',NULL),
(15,6,3,'devinahermawan','https://www.youtube.com/@devinahermawan',0,148452,0.0140,NULL,'2026-09-08 08:45:51',NULL),
(16,7,3,'corbuzier',NULL,0,2817281,0.0141,NULL,'2026-09-08 08:50:08',NULL),
(17,7,2,'mastercorbuzier',NULL,0,1078693,0.0709,NULL,'2026-09-08 08:50:08',NULL),
(18,7,4,'corbuzier',NULL,0,0,0.0000,NULL,'2026-09-08 08:50:08',NULL),
(19,7,5,'mastercorbuzier',NULL,0,0,0.0000,NULL,'2026-09-08 08:50:08',NULL),
(20,8,3,'WindahBasudara',NULL,0,1851079,0.0170,NULL,'2026-09-08 08:54:11',NULL),
(21,9,3,'Miawaug',NULL,0,468110,0.0207,NULL,'2026-09-08 08:58:28',NULL),
(22,9,1,'miawaug',NULL,0,1860633,0.0777,NULL,'2026-09-08 08:58:28',NULL),
(23,9,2,'miawaug',NULL,0,19805,0.0419,NULL,'2026-09-08 08:58:28',NULL),
(24,9,4,'miawaug',NULL,0,0,0.0000,NULL,'2026-09-08 08:58:28',NULL),
(25,9,5,'miawaug',NULL,0,0,0.0000,NULL,'2026-09-08 08:58:28',NULL),
(26,10,3,'LuthfiHalimawan',NULL,0,94967,0.0257,NULL,'2026-09-08 09:01:50',NULL),
(27,10,4,'luthfihalimawan',NULL,0,87470,0.0248,NULL,'2026-09-08 09:01:50',NULL),
(28,10,5,'luthfi.halimawan',NULL,0,87470,0.0248,NULL,'2026-09-08 09:01:50',NULL),
(29,3,1,'mamankkuliner','https://www.tiktok.com/@mamankkuliner',0,0,0.0000,NULL,'2026-10-01 14:38:46',NULL),
(30,4,1,'tanboykun_asli','https://www.tiktok.com/@tanboykun_asli',0,0,0.0000,NULL,'2026-10-03 09:19:11',NULL),
(31,6,1,'devina.hermawan','https://www.tiktok.com/@devina.hermawan',0,0,0.0000,NULL,'2026-10-03 09:21:55',NULL);
/*!40000 ALTER TABLE `kol_platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_profile_snapshots`
--

DROP TABLE IF EXISTS `kol_profile_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_profile_snapshots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kol_id` int(10) unsigned NOT NULL,
  `platform_id` int(10) unsigned NOT NULL,
  `followers` int(10) unsigned NOT NULL DEFAULT 0,
  `avg_views` int(10) unsigned NOT NULL DEFAULT 0,
  `avg_er` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `snapshot_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`snapshot_data`)),
  `captured_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kps_kol` (`kol_id`),
  KEY `fk_kps_plat` (`platform_id`),
  CONSTRAINT `fk_kps_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kps_plat` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_profile_snapshots`
--

LOCK TABLES `kol_profile_snapshots` WRITE;
/*!40000 ALTER TABLE `kol_profile_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `kol_profile_snapshots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_rate_cards`
--

DROP TABLE IF EXISTS `kol_rate_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_rate_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kol_id` int(10) unsigned NOT NULL,
  `platform_id` int(10) unsigned NOT NULL,
  `content_type` varchar(100) NOT NULL,
  `rate_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `negotiable` tinyint(1) NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_krc_kol` (`kol_id`),
  KEY `fk_krc_plat` (`platform_id`),
  CONSTRAINT `fk_krc_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_krc_plat` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_rate_cards`
--

LOCK TABLES `kol_rate_cards` WRITE;
/*!40000 ALTER TABLE `kol_rate_cards` DISABLE KEYS */;
INSERT INTO `kol_rate_cards` VALUES
(1,1,1,'1x Video TikTok (VT)',1500000.00,1,NULL,'2026-10-27 08:43:47',NULL),
(2,9,1,'1x Video TikTok (VT)',3000000.00,1,NULL,'2026-10-27 08:46:36',NULL),
(3,9,1,'Live Streaming',3000000.00,1,NULL,'2026-10-27 08:46:36',NULL),
(4,9,1,'Long Form Video',4000000.00,1,NULL,'2026-10-27 08:46:36',NULL),
(5,9,1,'Unboxing Video',4000000.00,1,NULL,'2026-10-27 08:46:36',NULL);
/*!40000 ALTER TABLE `kol_rate_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_reviews`
--

DROP TABLE IF EXISTS `kol_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kol_id` int(10) unsigned NOT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `review_text` text DEFAULT NULL,
  `reviewed_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kr_kol` (`kol_id`),
  KEY `fk_kr_user` (`reviewed_by`),
  CONSTRAINT `fk_kr_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kr_user` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_reviews`
--

LOCK TABLES `kol_reviews` WRITE;
/*!40000 ALTER TABLE `kol_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `kol_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_shipment_items`
--

DROP TABLE IF EXISTS `kol_shipment_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_shipment_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shipment_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `variant_id` int(10) unsigned DEFAULT NULL,
  `qty` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ksi_ship` (`shipment_id`),
  KEY `fk_ksi_prod` (`product_id`),
  KEY `fk_ksi_var` (`variant_id`),
  CONSTRAINT `fk_ksi_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_ksi_ship` FOREIGN KEY (`shipment_id`) REFERENCES `kol_shipments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ksi_var` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_shipment_items`
--

LOCK TABLES `kol_shipment_items` WRITE;
/*!40000 ALTER TABLE `kol_shipment_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `kol_shipment_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_shipments`
--

DROP TABLE IF EXISTS `kol_shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_shipments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_kol_id` int(10) unsigned NOT NULL,
  `courier_name` varchar(50) NOT NULL,
  `tracking_number` varchar(100) NOT NULL,
  `shipping_status` enum('PENDING','SHIPPED','DELIVERED','RETURNED') NOT NULL DEFAULT 'PENDING',
  `notes` varchar(255) DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ship_ck` (`campaign_kol_id`),
  CONSTRAINT `fk_ship_ck` FOREIGN KEY (`campaign_kol_id`) REFERENCES `campaign_kols` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_shipments`
--

LOCK TABLES `kol_shipments` WRITE;
/*!40000 ALTER TABLE `kol_shipments` DISABLE KEYS */;
/*!40000 ALTER TABLE `kol_shipments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kol_shop_snapshots`
--

DROP TABLE IF EXISTS `kol_shop_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kol_shop_snapshots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kol_id` int(10) unsigned NOT NULL,
  `gmv_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `order_count` int(10) unsigned NOT NULL DEFAULT 0,
  `commission_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `snapshot_date` date NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kss_kol` (`kol_id`),
  CONSTRAINT `fk_kss_kol` FOREIGN KEY (`kol_id`) REFERENCES `kols` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kol_shop_snapshots`
--

LOCK TABLES `kol_shop_snapshots` WRITE;
/*!40000 ALTER TABLE `kol_shop_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `kol_shop_snapshots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kols`
--

DROP TABLE IF EXISTS `kols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kols` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `nik` varchar(30) DEFAULT NULL,
  `whatsapp` varchar(30) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_holder` varchar(100) DEFAULT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `npwp_file` varchar(255) DEFAULT NULL,
  `id_card_file` varchar(255) DEFAULT NULL,
  `approval_status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `is_blacklisted` tinyint(1) NOT NULL DEFAULT 0,
  `blacklist_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kols_user` (`user_id`),
  KEY `idx_kols_status` (`approval_status`),
  KEY `idx_kols_city` (`city`),
  CONSTRAINT `fk_kol_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kols`
--

LOCK TABLES `kols` WRITE;
/*!40000 ALTER TABLE `kols` DISABLE KEYS */;
INSERT INTO `kols` VALUES
(1,4,'Demo Talent Influencer','3201000000000001','081200000004','Jakarta Selatan','Jl. Senopati No. 10, Kebayoran Baru',NULL,'BCA','8800112233','Demo Talent Influencer','09.123.456.7-011.000',NULL,NULL,'APPROVED',0,NULL,'Talent percontohan untuk uji coba portal kreator.','2026-10-27 08:43:47',NULL,NULL),
(2,NULL,'Jessica Jane',NULL,'6285794387741','DKI Jakarta','DKI Jakarta',NULL,'BCA','1234567890','Developer','99.888.777.6-555.000',NULL,NULL,'APPROVED',0,NULL,NULL,'2026-08-22 08:57:52','2026-10-14 09:05:24',NULL),
(3,NULL,'Mamank Kuliner','','6287735490376','DKI Jakarta','DKI Jakarta',NULL,'Mandiri','0491433230','Mamank Kuliner','',NULL,NULL,'APPROVED',0,NULL,NULL,'2026-08-22 10:14:43','2026-10-13 15:21:18',NULL),
(4,NULL,'Tanboy kun','','6289619419867','DKI Jakarta','DKI Jakarta',NULL,'BNI','0744013179','Tanboy kun','',NULL,NULL,'APPROVED',0,NULL,NULL,'2026-09-04 10:24:19','2026-10-13 15:21:34',NULL),
(5,NULL,'RJL 5',NULL,'6285431929432','DKI Jakarta','DKI Jakarta',NULL,'Mandiri','0769588572','RJL 5',NULL,NULL,NULL,'APPROVED',0,NULL,NULL,'2026-09-08 08:41:23','2026-10-14 08:47:03',NULL),
(6,NULL,'Devina Hermawan','','6285328143209','DKI Jakarta','DKI Jakarta',NULL,'BRI','0672112814','Devina Hermawan','',NULL,NULL,'APPROVED',0,NULL,NULL,'2026-09-08 08:45:51','2026-10-13 15:21:39',NULL),
(7,NULL,'Deddy Corbuzier',NULL,'6285621494233','DKI Jakarta','DKI Jakarta',NULL,'BSI','0444485913','Deddy Corbuzier',NULL,NULL,NULL,'APPROVED',0,NULL,NULL,'2026-09-08 08:50:08','2026-09-23 10:46:24',NULL),
(8,NULL,'Windah Basudara',NULL,'6285787229840','DKI Jakarta','DKI Jakarta',NULL,'Mandiri','0892409102','Windah Basudara',NULL,NULL,NULL,'APPROVED',0,NULL,NULL,'2026-09-08 08:54:11','2026-09-23 10:48:00',NULL),
(9,NULL,'Miawaug',NULL,'628569902324','DKI Jakarta','DKI Jakarta',NULL,'BRI','0587757356','Miawaug',NULL,NULL,NULL,'APPROVED',0,NULL,NULL,'2026-09-08 08:58:28','2026-10-14 08:43:33',NULL),
(10,NULL,'Luthfi Halimawan',NULL,'6285791217725','DKI Jakarta','DKI Jakarta',NULL,'BCA','0265409401','Luthfi Halimawan',NULL,NULL,NULL,'APPROVED',0,NULL,NULL,'2026-09-08 09:01:50','2026-09-23 10:49:37',NULL);
/*!40000 ALTER TABLE `kols` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `amount_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL DEFAULT 'BANK_TRANSFER',
  `bank_reference` varchar(100) DEFAULT NULL,
  `transfer_receipt_file` varchar(255) DEFAULT NULL,
  `paid_by` int(10) unsigned NOT NULL,
  `paid_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pay_inv` (`invoice_id`),
  KEY `fk_pay_user` (`paid_by`),
  CONSTRAINT `fk_pay_inv` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_user` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platforms`
--

DROP TABLE IF EXISTS `platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `platforms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `base_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platforms`
--

LOCK TABLES `platforms` WRITE;
/*!40000 ALTER TABLE `platforms` DISABLE KEYS */;
INSERT INTO `platforms` VALUES
(1,'TikTok','fab fa-tiktok',NULL,1,'2026-08-18 10:54:54','2026-08-19 12:26:20'),
(2,'Instagram','fab fa-instagram',NULL,1,'2026-08-18 10:54:54','2026-08-19 12:26:20'),
(3,'YouTube','fab fa-youtube',NULL,1,'2026-08-18 10:54:54','2026-08-19 12:26:20'),
(4,'Twitter/X','fab fa-twitter',NULL,1,'2026-08-18 10:54:54','2026-08-19 12:26:20'),
(5,'Facebook','fab fa-facebook',NULL,1,'2026-08-18 10:54:54','2026-08-19 12:26:20');
/*!40000 ALTER TABLE `platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posting_metrics`
--

DROP TABLE IF EXISTS `posting_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `posting_metrics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `posting_id` int(10) unsigned NOT NULL,
  `views_count` int(10) unsigned NOT NULL DEFAULT 0,
  `likes_count` int(10) unsigned NOT NULL DEFAULT 0,
  `comments_count` int(10) unsigned NOT NULL DEFAULT 0,
  `shares_count` int(10) unsigned NOT NULL DEFAULT 0,
  `saves_count` int(10) unsigned NOT NULL DEFAULT 0,
  `actual_cpv` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `last_scraped_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pm_posting` (`posting_id`),
  CONSTRAINT `fk_pm_posting` FOREIGN KEY (`posting_id`) REFERENCES `postings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posting_metrics`
--

LOCK TABLES `posting_metrics` WRITE;
/*!40000 ALTER TABLE `posting_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `posting_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `postings`
--

DROP TABLE IF EXISTS `postings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `postings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `platform_id` int(10) unsigned NOT NULL,
  `live_url` varchar(255) NOT NULL,
  `post_identifier` varchar(150) DEFAULT NULL,
  `posted_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_postings_task` (`task_id`),
  KEY `fk_postings_plat` (`platform_id`),
  CONSTRAINT `fk_postings_plat` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_postings_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postings`
--

LOCK TABLES `postings` WRITE;
/*!40000 ALTER TABLE `postings` DISABLE KEYS */;
/*!40000 ALTER TABLE `postings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(1,'Kolab Signature Extrait de Parfum 50ml','KLB-EXT-01','Top notes bergamot, middle woody cedarwood, base amber & vanilla.',500,349000.00,NULL,1,'2026-10-27 08:43:47',NULL),
(2,'Kolab Bloom Floral Romance EDP 50ml','KLB-FLR-02','Aroma segar bunga mawar prancis dengan sentuhan buah pir manis.',450,289000.00,NULL,1,'2026-10-27 08:43:47',NULL),
(3,'Metafora Extrait de Parfum - Velvet Oud 50ml','PRF-MET-001',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-09-16 10:23:43'),
(4,'Metafora Eau de Parfum - Golden Amber 50ml','PRF-MET-002',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-19 14:45:05'),
(5,'Metafora Eau de Parfum - Rose Vanilla 50ml','PRF-MET-003',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-19 14:45:05'),
(6,'Metafora Eau de Parfum - Citrus Blossom 50ml','PRF-MET-004',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-22 09:09:47'),
(7,'Metafora Eau de Parfum - Midnight Leather 50ml','PRF-MET-005',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-19 14:45:05'),
(8,'Metafora Eau de Parfum - Sweet Gourmand 50ml','PRF-MET-006',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-19 14:45:05'),
(9,'Discovery Set 5x5ml (All Variants)','PRF-TRV-001',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-10-15 12:30:46'),
(10,'Pocket Spray 10ml - Velvet Oud','PRF-TRV-002',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-22 13:20:26'),
(11,'Pocket Spray 10ml - Golden Amber','PRF-TRV-003',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-22 13:20:26'),
(12,'Pocket Spray 10ml - Rose Vanilla','PRF-TRV-004',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-22 13:20:26'),
(13,'KOL Seeding Exclusive Box (Full Bottle + Discovery Set)','KOL-KIT-001',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-19 14:45:05'),
(14,'Special Launching PR Box - Velvet Edition','KOL-KIT-002',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-19 14:45:05'),
(15,'Tester & Scent Strip Seeding Bundle','KOL-KIT-003',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-19 14:45:05'),
(16,'Metafora Perfumed Body Lotion 200ml - Rose Vanilla','BDY-MET-001',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-22 09:09:47'),
(17,'Metafora Scented Candle 180g - Midnight Leather','BDY-MET-002',NULL,100,0.00,NULL,1,'2026-08-19 14:45:05','2026-08-22 10:09:03');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_rp_perm` (`permission_id`),
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'admin','Super Admin','System and infrastructure administrator with full access','2026-10-27 08:43:47','2026-10-27 08:43:47'),
(2,'leader','Leader KOL','Campaign strategist, brief & profile approver, KPI manager','2026-10-27 08:43:47','2026-10-27 08:43:47'),
(3,'staff','Staff Operational','Field operator for dealing, draft review, logistics, and finance','2026-10-27 08:43:47','2026-10-27 08:43:47'),
(4,'kol','Talent KOL','External content creator accessing self-service portal','2026-10-27 08:43:47','2026-10-27 08:43:47');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scraping_jobs`
--

DROP TABLE IF EXISTS `scraping_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scraping_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'QUEUED',
  `error_message` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scraping_jobs`
--

LOCK TABLES `scraping_jobs` WRITE;
/*!40000 ALTER TABLE `scraping_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `scraping_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_history`
--

DROP TABLE IF EXISTS `stock_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `variant_id` int(10) unsigned DEFAULT NULL,
  `type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `qty` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sh_prod` (`product_id`),
  KEY `fk_sh_user` (`created_by`),
  CONSTRAINT `fk_sh_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sh_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_history`
--

LOCK TABLES `stock_history` WRITE;
/*!40000 ALTER TABLE `stock_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_budget_histories`
--

DROP TABLE IF EXISTS `task_budget_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_budget_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `old_rate` decimal(15,2) NOT NULL,
  `new_rate` decimal(15,2) NOT NULL,
  `reason` text NOT NULL,
  `authorized_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tbh_task` (`task_id`),
  KEY `fk_tbh_user` (`authorized_by`),
  CONSTRAINT `fk_tbh_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tbh_user` FOREIGN KEY (`authorized_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_budget_histories`
--

LOCK TABLES `task_budget_histories` WRITE;
/*!40000 ALTER TABLE `task_budget_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_budget_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_rate_histories`
--

DROP TABLE IF EXISTS `task_rate_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_rate_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `old_rate` decimal(15,2) NOT NULL,
  `new_rate` decimal(15,2) NOT NULL,
  `changed_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_trh_task` (`task_id`),
  KEY `fk_trh_user` (`changed_by`),
  CONSTRAINT `fk_trh_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trh_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_rate_histories`
--

LOCK TABLES `task_rate_histories` WRITE;
/*!40000 ALTER TABLE `task_rate_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_rate_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_sentiments`
--

DROP TABLE IF EXISTS `task_sentiments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_sentiments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `positive_count` int(10) unsigned NOT NULL DEFAULT 0,
  `neutral_count` int(10) unsigned NOT NULL DEFAULT 0,
  `negative_count` int(10) unsigned NOT NULL DEFAULT 0,
  `sentiment_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `analyzed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tsen_task` (`task_id`),
  CONSTRAINT `fk_tsen_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_sentiments`
--

LOCK TABLES `task_sentiments` WRITE;
/*!40000 ALTER TABLE `task_sentiments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_sentiments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_status_histories`
--

DROP TABLE IF EXISTS `task_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_status_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL,
  `from_status` varchar(50) NOT NULL,
  `to_status` varchar(50) NOT NULL,
  `changed_by` int(10) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tsh_task` (`task_id`),
  KEY `fk_tsh_user` (`changed_by`),
  CONSTRAINT `fk_tsh_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tsh_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_status_histories`
--

LOCK TABLES `task_status_histories` WRITE;
/*!40000 ALTER TABLE `task_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_kol_id` int(10) unsigned NOT NULL,
  `task_number` int(10) unsigned NOT NULL DEFAULT 1,
  `title` varchar(200) DEFAULT NULL,
  `rate_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_rate_locked` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('DRAFT','BRIEF_SUBMITTED','BRIEF_REVISION','BRIEF_APPROVED','DRAFT_SUBMITTED','DRAFT_REVISION','DRAFT_APPROVED','SCHEDULED','POSTED','SCRAPING','COMPLETED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  `deadline` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tasks_ck` (`campaign_kol_id`),
  KEY `idx_tasks_status` (`status`),
  CONSTRAINT `fk_tasks_ck` FOREIGN KEY (`campaign_kol_id`) REFERENCES `campaign_kols` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES
(1,1,1,'Unboxing Metafora Parfume',0.00,0,'POSTED',NULL,'2026-08-22 09:05:39','2026-10-07 13:09:11'),
(2,1,2,'Live Streaming Brand Awareness',0.00,0,'',NULL,'2026-08-22 09:05:39','2026-08-31 09:20:43'),
(3,2,1,'Task #1',0.00,0,'',NULL,'2026-08-22 10:25:11','2026-10-07 13:09:11'),
(4,2,2,'Task #2',0.00,0,'',NULL,'2026-08-22 10:25:11','2026-10-07 13:09:11'),
(5,2,3,'Task #3',0.00,0,'',NULL,'2026-08-22 10:25:11','2026-10-07 15:38:35'),
(6,2,4,'Task #4',0.00,0,'',NULL,'2026-08-22 10:25:11','2026-10-07 13:09:11'),
(7,3,1,'Task #1',0.00,0,'DRAFT',NULL,'2026-09-14 10:54:44','2026-10-09 10:41:47'),
(8,4,1,'Task #1',0.00,0,'DRAFT',NULL,'2026-09-16 12:57:46','2026-10-07 13:10:44'),
(9,4,2,'Task #2',0.00,0,'DRAFT',NULL,'2026-09-16 12:57:46','2026-10-07 13:10:44'),
(10,5,1,'Task #1',0.00,0,'DRAFT',NULL,'2026-09-16 14:20:04','2026-10-07 13:10:44'),
(11,5,2,'Task #2',0.00,0,'DRAFT',NULL,'2026-09-16 14:20:04','2026-10-07 13:10:44'),
(12,6,1,'Task unboxing ',0.00,0,'',NULL,'2026-09-18 09:56:54','2026-10-07 13:09:11'),
(13,6,2,'Task Review',0.00,0,'',NULL,'2026-09-18 09:56:54','2026-10-07 13:09:11'),
(14,7,1,'coba close',0.00,0,'DRAFT',NULL,'2026-09-18 12:38:29','2026-10-07 13:10:44'),
(15,8,1,'Task #1',0.00,0,'DRAFT',NULL,'2026-09-21 13:15:41','2026-10-07 13:10:44'),
(16,8,2,'Task #2',0.00,0,'DRAFT',NULL,'2026-09-21 13:15:41','2026-10-07 13:10:44'),
(17,9,1,'Video Review Exclusive Summer: Jessica Jane',0.00,0,'',NULL,'2026-08-03 10:00:00','2026-08-30 10:00:00'),
(18,10,1,'Video Review Exclusive Summer: Mamank Kuliner',0.00,0,'',NULL,'2026-08-04 10:00:00','2026-08-30 10:00:00'),
(19,11,1,'Video Review Exclusive Summer: Tanboy kun',0.00,0,'',NULL,'2026-08-05 10:00:00','2026-08-30 10:00:00'),
(20,12,1,'Video Review Exclusive Summer: RJL 5',0.00,0,'',NULL,'2026-08-05 10:00:00','2026-08-30 10:00:00'),
(21,13,1,'Video Review Exclusive Summer: Devina Hermawan',0.00,0,'',NULL,'2026-08-06 10:00:00','2026-08-30 10:00:00'),
(22,14,1,'Video Review Exclusive Summer: Deddy Corbuzier',0.00,0,'',NULL,'2026-08-06 10:00:00','2026-08-30 10:00:00'),
(23,15,1,'Video Review Exclusive Summer: Windah Basudara',0.00,0,'',NULL,'2026-08-08 10:00:00','2026-08-30 10:00:00'),
(24,16,1,'Video Review Exclusive Summer: Miawaug',0.00,0,'',NULL,'2026-08-08 10:00:00','2026-08-30 10:00:00'),
(25,17,1,'Video Review Exclusive Summer: Luthfi Halimawan',0.00,0,'',NULL,'2026-08-10 10:00:00','2026-08-30 10:00:00'),
(26,19,1,'Task #1',0.00,0,'',NULL,'2026-10-07 11:07:33','2026-10-07 11:07:33'),
(27,20,1,'Task #1',0.00,0,'DRAFT',NULL,'2026-10-07 11:20:55','2026-10-07 13:10:44'),
(28,21,1,'Task #1',0.00,0,'DRAFT',NULL,'2026-10-09 10:13:23','2026-10-09 10:13:23'),
(29,21,2,'Task #2',0.00,0,'',NULL,'2026-10-09 10:13:23','2026-10-09 10:16:56');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiers`
--

DROP TABLE IF EXISTS `tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tiers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `min_followers` int(10) unsigned NOT NULL DEFAULT 0,
  `max_followers` int(10) unsigned NOT NULL DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiers`
--

LOCK TABLES `tiers` WRITE;
/*!40000 ALTER TABLE `tiers` DISABLE KEYS */;
INSERT INTO `tiers` VALUES
(1,'Nano',1000,10000,'KOL skala kecil, komunitas niche tinggi','2026-08-18 10:54:54','2026-08-19 12:26:20'),
(2,'Micro',10001,100000,'KOL engagement tinggi, audiens spesifik','2026-08-18 10:54:54','2026-08-19 12:26:20'),
(3,'Mid',100001,500000,'KOL dengan jangkauan menengah','2026-08-18 10:54:54','2026-08-19 12:26:20'),
(4,'Macro',500001,1000000,'KOL dengan jangkauan luas','2026-08-18 10:54:54','2026-08-19 12:26:20'),
(5,'Mega',1000001,99999999,'Selebriti / influencer nasional','2026-08-18 10:54:54','2026-08-19 12:26:20');
/*!40000 ALTER TABLE `tiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_ur_role` (`role_id`),
  CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES
(1,1),
(2,2),
(3,3),
(4,4);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'remember',
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_tokens_user` (`user_id`),
  CONSTRAINT `fk_ut_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('active','pending','suspended') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Super Administrator','admin@kolab.id','$2y$10$oi0YjCniW14k9XBZiPqYCuaj5dEWK1v0aOYo6NdskDgs5mTLhU7Sa','081200000001',NULL,'active','2026-10-27 08:43:47',NULL,NULL),
(2,'Leader KOL Manager','leader@kolab.id','$2y$10$oi0YjCniW14k9XBZiPqYCuaj5dEWK1v0aOYo6NdskDgs5mTLhU7Sa','081200000002',NULL,'active','2026-10-27 08:43:47',NULL,NULL),
(3,'Staff Operational Specialist','staff@kolab.id','$2y$10$oi0YjCniW14k9XBZiPqYCuaj5dEWK1v0aOYo6NdskDgs5mTLhU7Sa','081200000003',NULL,'active','2026-10-27 08:43:47',NULL,NULL),
(4,'Demo Talent Influencer','kol@kolab.id','$2y$10$oi0YjCniW14k9XBZiPqYCuaj5dEWK1v0aOYo6NdskDgs5mTLhU7Sa','081200000004',NULL,'active','2026-10-27 08:43:47',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variants`
--

DROP TABLE IF EXISTS `variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `variants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `additional_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `fk_var_product` (`product_id`),
  CONSTRAINT `fk_var_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variants`
--

LOCK TABLES `variants` WRITE;
/*!40000 ALTER TABLE `variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_messages`
--

DROP TABLE IF EXISTS `whatsapp_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recipient_phone` varchar(30) NOT NULL,
  `message_text` text NOT NULL,
  `status` enum('PENDING','SENT','FAILED') NOT NULL DEFAULT 'PENDING',
  `error_message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wm_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_messages`
--

LOCK TABLES `whatsapp_messages` WRITE;
/*!40000 ALTER TABLE `whatsapp_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_sessions`
--

DROP TABLE IF EXISTS `whatsapp_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_name` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'DISCONNECTED',
  `qr_code` text DEFAULT NULL,
  `last_active_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_name` (`session_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_sessions`
--

LOCK TABLES `whatsapp_sessions` WRITE;
/*!40000 ALTER TABLE `whatsapp_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_templates`
--

DROP TABLE IF EXISTS `whatsapp_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template_code` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body_text` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_code` (`template_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_templates`
--

LOCK TABLES `whatsapp_templates` WRITE;
/*!40000 ALTER TABLE `whatsapp_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_templates` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-28 13:16:31

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.9-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: democrm_test
-- ------------------------------------------------------
-- Server version	10.11.9-MariaDB

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
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event` varchar(255) NOT NULL,
  `resource` varchar(255) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `useragent` varchar(510) NOT NULL,
  `location` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit`
--

LOCK TABLES `audit` WRITE;
/*!40000 ALTER TABLE `audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event_attendees`
--

DROP TABLE IF EXISTS `calendar_event_attendees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event_attendees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `response_status` int(11) NOT NULL DEFAULT 1 COMMENT '1=pending, 2=accepted, 3=declined, 4=tentative',
  `is_organizer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event_attendees`
--

LOCK TABLES `calendar_event_attendees` WRITE;
/*!40000 ALTER TABLE `calendar_event_attendees` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_event_attendees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_event_reminders`
--

DROP TABLE IF EXISTS `calendar_event_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_event_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reminder_datetime` datetime NOT NULL,
  `reminder_type` int(11) NOT NULL DEFAULT 1 COMMENT '1=email, 2=sms, 3=push, 4=popup',
  `is_sent` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_reminder_datetime` (`reminder_datetime`),
  KEY `idx_is_sent` (`is_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_event_reminders`
--

LOCK TABLES `calendar_event_reminders` WRITE;
/*!40000 ALTER TABLE `calendar_event_reminders` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_event_reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` int(11) NOT NULL DEFAULT 1 COMMENT '1=call, 2=email, 3=text, 4=internal, 5=virtual_meeting, 6=in_person',
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 1 COMMENT '1=pending, 2=completed, 3=cancelled, 4=in_progress',
  `priority` int(11) NOT NULL DEFAULT 5 COMMENT '1-10 priority system (1=lowest, 10=highest)',
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reminder_minutes` int(11) DEFAULT NULL COMMENT 'Minutes before event to remind',
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `recurrence_rule` text DEFAULT NULL COMMENT 'RRULE for recurring events',
  `timezone` varchar(50) DEFAULT 'UTC',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_start_datetime` (`start_datetime`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `fk_calendar_events_created_by` (`created_by`),
  KEY `fk_calendar_events_updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_user_settings`
--

DROP TABLE IF EXISTS `calendar_user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `default_view` varchar(20) NOT NULL DEFAULT 'month' COMMENT 'month, week, day, list',
  `work_hours_start` time NOT NULL DEFAULT '09:00:00',
  `work_hours_end` time NOT NULL DEFAULT '17:00:00',
  `work_days` varchar(20) NOT NULL DEFAULT '1,2,3,4,5' COMMENT 'Comma-separated day numbers (0=Sunday)',
  `default_event_duration` int(11) NOT NULL DEFAULT 60 COMMENT 'Default duration in minutes',
  `timezone` varchar(50) NOT NULL DEFAULT 'UTC',
  `email_reminders` tinyint(1) NOT NULL DEFAULT 1,
  `popup_reminders` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_user_settings`
--

LOCK TABLES `calendar_user_settings` WRITE;
/*!40000 ALTER TABLE `calendar_user_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) DEFAULT NULL,
  `contact_type` int(11) NOT NULL DEFAULT 1,
  `call_order` int(1) unsigned DEFAULT NULL,
  `first_name` varchar(25) NOT NULL,
  `family_name` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `cell_phone` varchar(15) DEFAULT NULL,
  `business_phone` varchar(15) DEFAULT NULL,
  `alt_phone` varchar(15) DEFAULT NULL,
  `phones` longtext NOT NULL,
  `personal_email` varchar(50) DEFAULT NULL,
  `business_email` varchar(50) DEFAULT NULL,
  `alt_email` varchar(50) DEFAULT NULL,
  `emails` longtext NOT NULL,
  `p_street_1` varchar(100) DEFAULT NULL,
  `p_street_2` varchar(50) DEFAULT NULL,
  `p_city` varchar(50) DEFAULT NULL,
  `p_state` varchar(50) DEFAULT NULL,
  `p_postcode` varchar(15) DEFAULT NULL,
  `p_country` varchar(25) DEFAULT NULL,
  `business_name` varchar(50) DEFAULT NULL,
  `b_street_1` varchar(100) DEFAULT NULL,
  `b_street_2` varchar(50) DEFAULT NULL,
  `b_city` varchar(50) DEFAULT NULL,
  `b_state` varchar(50) DEFAULT NULL,
  `b_postcode` varchar(15) DEFAULT NULL,
  `b_country` varchar(25) DEFAULT NULL,
  `m_street_1` varchar(100) DEFAULT NULL,
  `m_street_2` varchar(50) DEFAULT NULL,
  `m_city` varchar(50) DEFAULT NULL,
  `m_state` varchar(50) DEFAULT NULL,
  `m_postcode` varchar(15) DEFAULT NULL,
  `m_country` varchar(25) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL COMMENT 'Contact timezone (e.g., America/New_York)',
  `status` int(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `prop_id` (`lead_id`),
  KEY `idx_contacts_timezone` (`timezone`),
  KEY `idx_contacts_lead_id` (`lead_id`),
  KEY `idx_contacts_email` (`personal_email`),
  KEY `idx_contacts_phone` (`cell_phone`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES
(1,NULL,1,NULL,'Contact','Test 1','','555-1001',NULL,NULL,'','contact_test_1@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(2,NULL,1,NULL,'Contact','Test 2','','555-1002',NULL,NULL,'','contact_test_2@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(3,NULL,1,NULL,'Contact','Test 3','','555-1003',NULL,NULL,'','contact_test_3@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(4,NULL,1,NULL,'Contact','Test 4','','555-1004',NULL,NULL,'','contact_test_4@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(5,NULL,1,NULL,'Contact','Test 5','','555-1005',NULL,NULL,'','contact_test_5@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(6,NULL,1,NULL,'Contact','Test 6','','555-1006',NULL,NULL,'','contact_test_6@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(7,NULL,1,NULL,'Contact','Test 7','','555-1007',NULL,NULL,'','contact_test_7@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(8,NULL,1,NULL,'Contact','Test 8','','555-1008',NULL,NULL,'','contact_test_8@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(9,NULL,1,NULL,'Contact','Test 9','','555-1009',NULL,NULL,'','contact_test_9@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(10,NULL,1,NULL,'Contact','Test 10','','555-1010',NULL,NULL,'','contact_test_10@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(11,NULL,1,NULL,'Contact','Test 11','','555-1011',NULL,NULL,'','contact_test_11@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(12,NULL,1,NULL,'Contact','Test 12','','555-1012',NULL,NULL,'','contact_test_12@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(13,NULL,1,NULL,'Contact','Test 13','','555-1013',NULL,NULL,'','contact_test_13@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(14,NULL,1,NULL,'Contact','Test 14','','555-1014',NULL,NULL,'','contact_test_14@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(15,NULL,1,NULL,'Contact','Test 15','','555-1015',NULL,NULL,'','contact_test_15@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(16,NULL,1,NULL,'Contact','Test 16','','555-1016',NULL,NULL,'','contact_test_16@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(17,NULL,1,NULL,'Contact','Test 17','','555-1017',NULL,NULL,'','contact_test_17@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(18,NULL,1,NULL,'Contact','Test 18','','555-1018',NULL,NULL,'','contact_test_18@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(19,NULL,1,NULL,'Contact','Test 19','','555-1019',NULL,NULL,'','contact_test_19@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(20,NULL,1,NULL,'Contact','Test 20','','555-1020',NULL,NULL,'','contact_test_20@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(21,NULL,1,NULL,'Contact','Test 21','','555-1021',NULL,NULL,'','contact_test_21@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(22,NULL,1,NULL,'Contact','Test 22','','555-1022',NULL,NULL,'','contact_test_22@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(23,NULL,1,NULL,'Contact','Test 23','','555-1023',NULL,NULL,'','contact_test_23@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(24,NULL,1,NULL,'Contact','Test 24','','555-1024',NULL,NULL,'','contact_test_24@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(25,NULL,1,NULL,'Contact','Test 25','','555-1025',NULL,NULL,'','contact_test_25@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(26,NULL,1,NULL,'Contact','Test 26','','555-1026',NULL,NULL,'','contact_test_26@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(27,NULL,1,NULL,'Contact','Test 27','','555-1027',NULL,NULL,'','contact_test_27@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(28,NULL,1,NULL,'Contact','Test 28','','555-1028',NULL,NULL,'','contact_test_28@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(29,NULL,1,NULL,'Contact','Test 29','','555-1029',NULL,NULL,'','contact_test_29@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20'),
(30,NULL,1,NULL,'Contact','Test 30','','555-1030',NULL,NULL,'','contact_test_30@test.com',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2025-11-20 02:15:53','2025-11-20');
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `alpha_2` char(2) NOT NULL DEFAULT '',
  `alpha_3` char(3) NOT NULL DEFAULT '',
  `ar` varchar(75) NOT NULL DEFAULT '',
  `bg` varchar(75) NOT NULL DEFAULT '',
  `cs` varchar(75) NOT NULL DEFAULT '',
  `da` varchar(75) NOT NULL DEFAULT '',
  `de` varchar(75) NOT NULL DEFAULT '',
  `el` varchar(75) NOT NULL DEFAULT '',
  `en` varchar(75) NOT NULL DEFAULT '',
  `eo` varchar(75) NOT NULL DEFAULT '',
  `es` varchar(75) NOT NULL DEFAULT '',
  `et` varchar(75) NOT NULL DEFAULT '',
  `eu` varchar(75) NOT NULL DEFAULT '',
  `fi` varchar(75) NOT NULL DEFAULT '',
  `fr` varchar(75) NOT NULL DEFAULT '',
  `hu` varchar(75) NOT NULL DEFAULT '',
  `hy` varchar(75) NOT NULL DEFAULT '',
  `it` varchar(75) NOT NULL DEFAULT '',
  `ja` varchar(75) NOT NULL DEFAULT '',
  `ko` varchar(75) NOT NULL DEFAULT '',
  `lt` varchar(75) NOT NULL DEFAULT '',
  `nl` varchar(75) NOT NULL DEFAULT '',
  `no` varchar(75) NOT NULL DEFAULT '',
  `pl` varchar(75) NOT NULL DEFAULT '',
  `pt` varchar(75) NOT NULL DEFAULT '',
  `ro` varchar(75) NOT NULL DEFAULT '',
  `ru` varchar(75) NOT NULL DEFAULT '',
  `sk` varchar(75) NOT NULL DEFAULT '',
  `sv` varchar(75) NOT NULL DEFAULT '',
  `th` varchar(75) NOT NULL DEFAULT '',
  `uk` varchar(75) NOT NULL DEFAULT '',
  `zh` varchar(75) NOT NULL DEFAULT '',
  `zh-tw` varchar(75) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crm_sync_queue`
--

DROP TABLE IF EXISTS `crm_sync_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_sync_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL COMMENT 'Lead to sync to external CRM',
  `sync_action` enum('create','update','note_add') NOT NULL COMMENT 'Type of sync action',
  `external_system` enum('hubspot','salesforce','mailchimp','custom') DEFAULT 'custom' COMMENT 'Target CRM system',
  `sync_status` enum('pending','in_progress','completed','failed') DEFAULT 'pending' COMMENT 'Current sync status',
  `retry_count` int(11) DEFAULT 0 COMMENT 'Number of retry attempts',
  `max_retries` int(11) DEFAULT 3 COMMENT 'Maximum retry attempts',
  `next_retry_at` timestamp NULL DEFAULT NULL COMMENT 'When to retry if failed',
  `last_error` text DEFAULT NULL COMMENT 'Last error message',
  `external_id` varchar(255) DEFAULT NULL COMMENT 'ID in external system',
  `sync_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Data to sync as JSON' CHECK (json_valid(`sync_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When sync was queued',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update time',
  PRIMARY KEY (`id`),
  KEY `idx_sync_status` (`sync_status`),
  KEY `idx_next_retry` (`next_retry_at`),
  KEY `idx_external_system` (`external_system`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Queue for syncing leads to external CRM systems';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crm_sync_queue`
--

LOCK TABLES `crm_sync_queue` WRITE;
/*!40000 ALTER TABLE `crm_sync_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_sync_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `effective_permissions_cache`
--

DROP TABLE IF EXISTS `effective_permissions_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `effective_permissions_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) NOT NULL,
  `permission_source` enum('direct','inherited','delegated','temporary') DEFAULT 'direct',
  `calculation_method` enum('full','hierarchy','delegation','approval') DEFAULT 'full',
  `is_active` tinyint(1) DEFAULT 1,
  `cached_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_perm_cache` (`user_id`,`permission_id`,`role_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_permission_id` (`permission_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_permission_source` (`permission_source`),
  KEY `fk_cache_role` (`role_id`),
  KEY `idx_effective_perm_user` (`user_id`,`permission_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cache of effective permissions (direct + inherited + delegated)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `effective_permissions_cache`
--

LOCK TABLES `effective_permissions_cache` WRITE;
/*!40000 ALTER TABLE `effective_permissions_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `effective_permissions_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_accounts_config`
--

DROP TABLE IF EXISTS `email_accounts_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_accounts_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_address` varchar(255) NOT NULL COMMENT 'Email address to monitor',
  `form_type` enum('estimate','ltr','contact') NOT NULL COMMENT 'Type of forms this email receives',
  `imap_host` varchar(255) NOT NULL COMMENT 'IMAP server hostname',
  `imap_port` int(11) DEFAULT 993 COMMENT 'IMAP server port',
  `imap_encryption` enum('ssl','tls','none') DEFAULT 'ssl' COMMENT 'IMAP encryption type',
  `username` varchar(255) NOT NULL COMMENT 'IMAP username',
  `password` varchar(500) NOT NULL COMMENT 'IMAP password (encrypted)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Whether to process this account',
  `last_check` timestamp NULL DEFAULT NULL COMMENT 'Last time emails were checked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When account was added',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update time',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_address` (`email_address`),
  UNIQUE KEY `idx_email_address` (`email_address`),
  KEY `idx_form_type` (`form_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Configuration for email accounts to monitor';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_accounts_config`
--

LOCK TABLES `email_accounts_config` WRITE;
/*!40000 ALTER TABLE `email_accounts_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_accounts_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_form_processing`
--

DROP TABLE IF EXISTS `email_form_processing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_form_processing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_account` varchar(255) NOT NULL COMMENT 'Email address that received the form',
  `form_type` enum('estimate','ltr','contact') NOT NULL COMMENT 'Type of form processed',
  `message_id` varchar(255) DEFAULT NULL COMMENT 'Email message ID for duplicate detection',
  `subject` varchar(500) DEFAULT NULL COMMENT 'Email subject line',
  `sender_email` varchar(255) DEFAULT NULL COMMENT 'Email address of form sender',
  `received_at` timestamp NULL DEFAULT NULL COMMENT 'When email was received',
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When processing occurred',
  `processing_status` enum('success','failed','skipped','duplicate') DEFAULT 'success' COMMENT 'Processing result',
  `lead_id` int(11) DEFAULT NULL COMMENT 'Created/updated lead ID',
  `raw_email_content` text DEFAULT NULL COMMENT 'Original email content for debugging',
  `parsed_form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Extracted form data as JSON' CHECK (json_valid(`parsed_form_data`)),
  `error_message` text DEFAULT NULL COMMENT 'Error details if processing failed',
  PRIMARY KEY (`id`),
  KEY `idx_email_account` (`email_account`),
  KEY `idx_form_type` (`form_type`),
  KEY `idx_processing_status` (`processing_status`),
  KEY `idx_processed_at` (`processed_at`),
  KEY `idx_sender_email` (`sender_email`),
  KEY `idx_message_id` (`message_id`),
  KEY `fk_email_processing_lead_id` (`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log of email form processing activities';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_form_processing`
--

LOCK TABLES `email_form_processing` WRITE;
/*!40000 ALTER TABLE `email_form_processing` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_form_processing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_global_templates`
--

DROP TABLE IF EXISTS `email_global_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_global_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_type` enum('header','footer') NOT NULL,
  `language_code` varchar(5) NOT NULL DEFAULT 'en',
  `html_content` text NOT NULL,
  `plain_text_content` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_language` (`template_type`,`language_code`),
  KEY `language_code` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Global header/footer templates inherited by all modules';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_global_templates`
--

LOCK TABLES `email_global_templates` WRITE;
/*!40000 ALTER TABLE `email_global_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_global_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_queue`
--

DROP TABLE IF EXISTS `email_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL COMMENT 'ID of lead, referral, etc.',
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL COMMENT 'Uses full_name from source table',
  `language_code` varchar(5) DEFAULT 'en',
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `body_plain_text` text DEFAULT NULL,
  `variables_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Rendered variables for this email' CHECK (json_valid(`variables_json`)),
  `status` enum('pending','approved','sent','failed','cancelled') DEFAULT 'pending',
  `requires_approval` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `scheduled_send_at` datetime DEFAULT NULL COMMENT 'For future scheduled emails',
  `sent_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `module_record` (`module`,`record_id`),
  KEY `scheduled_send_at` (`scheduled_send_at`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Queue for emails pending approval or scheduled sending';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_queue`
--

LOCK TABLES `email_queue` WRITE;
/*!40000 ALTER TABLE `email_queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_send_log`
--

DROP TABLE IF EXISTS `email_send_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_send_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_config_id` int(11) DEFAULT NULL COMMENT 'SMTP config used',
  `lead_id` int(11) DEFAULT NULL COMMENT 'Related lead ID',
  `contact_id` int(11) DEFAULT NULL COMMENT 'Related contact ID',
  `user_id` int(11) DEFAULT NULL COMMENT 'User who triggered the email',
  `email_type` varchar(50) NOT NULL COMMENT 'Type of email (lead_thank_you, notification, etc)',
  `lead_source_id` int(11) DEFAULT NULL COMMENT 'Lead source type (1-6)',
  `recipient_email` varchar(255) NOT NULL COMMENT 'Recipient email address',
  `recipient_name` varchar(255) DEFAULT NULL COMMENT 'Recipient name',
  `subject` varchar(500) NOT NULL COMMENT 'Email subject',
  `body_html` longtext DEFAULT NULL COMMENT 'HTML email body',
  `body_text` longtext DEFAULT NULL COMMENT 'Plain text email body',
  `status` enum('pending','sent','failed','bounced') NOT NULL DEFAULT 'pending' COMMENT 'Email status',
  `error_message` text DEFAULT NULL COMMENT 'Error message if failed',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT 'When email was sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_smtp_config_id` (`smtp_config_id`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_email_type` (`email_type`),
  KEY `idx_lead_source_id` (`lead_source_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log of all emails sent from the system';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_send_log`
--

LOCK TABLES `email_send_log` WRITE;
/*!40000 ALTER TABLE `email_send_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_send_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template_content`
--

DROP TABLE IF EXISTS `email_template_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL DEFAULT 'en',
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL COMMENT 'Main email body with shortcodes',
  `body_plain_text` text DEFAULT NULL COMMENT 'Plain text version',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_language` (`template_id`,`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multilingual content for email templates (body only, header/footer inherited)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template_content`
--

LOCK TABLES `email_template_content` WRITE;
/*!40000 ALTER TABLE `email_template_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template_variables`
--

DROP TABLE IF EXISTS `email_template_variables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template_variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `variable_key` varchar(100) NOT NULL COMMENT 'e.g., lead_name, company_name, assigned_user',
  `variable_label` varchar(255) NOT NULL,
  `variable_description` text DEFAULT NULL,
  `variable_type` varchar(50) DEFAULT 'text' COMMENT 'text, date, currency, url, phone',
  `variable_source` varchar(100) DEFAULT NULL COMMENT 'Database field or method to get value',
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available variables/shortcodes for each template';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template_variables`
--

LOCK TABLES `email_template_variables` WRITE;
/*!40000 ALTER TABLE `email_template_variables` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template_variables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_key` varchar(100) NOT NULL COMMENT 'Unique identifier (e.g., lead_welcome, lead_assigned)',
  `template_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL COMMENT 'leads, referrals, prospects, contacts, users',
  `category` varchar(50) DEFAULT 'general' COMMENT 'welcome, status_change, assignment, reminder',
  `trigger_event` varchar(100) DEFAULT NULL COMMENT 'stage_change, assignment, manual, scheduled',
  `trigger_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Conditions for automatic sending' CHECK (json_valid(`trigger_conditions`)),
  `requires_approval` tinyint(1) DEFAULT 0 COMMENT '0=auto send, 1=requires approval',
  `log_to_communications` tinyint(1) DEFAULT 1 COMMENT '1=log in communications table',
  `supports_sms` tinyint(1) DEFAULT 0 COMMENT 'Future: can be sent as SMS',
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_key` (`template_key`),
  KEY `module` (`module`),
  KEY `trigger_event` (`trigger_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Module-specific email templates with trigger configuration';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_templates`
--

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_trigger_rules`
--

DROP TABLE IF EXISTS `email_trigger_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_trigger_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `trigger_type` enum('stage_change','assignment','field_update','time_based') NOT NULL,
  `trigger_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Conditions: stage_from, stage_to, field_name, etc.' CHECK (json_valid(`trigger_condition`)),
  `recipient_type` enum('lead_contact','assigned_user','custom_email','both') DEFAULT 'lead_contact',
  `custom_recipient_email` varchar(255) DEFAULT NULL COMMENT 'For custom_email recipient type',
  `delay_minutes` int(11) DEFAULT 0 COMMENT 'Delay before sending (0=immediate)',
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  KEY `module_trigger` (`module`,`trigger_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rules for automatic email triggering based on events';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_trigger_rules`
--

LOCK TABLES `email_trigger_rules` WRITE;
/*!40000 ALTER TABLE `email_trigger_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_trigger_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_permissions`
--

DROP TABLE IF EXISTS `field_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(150) DEFAULT NULL,
  `access_level` enum('none','view','edit') DEFAULT 'view',
  `module_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission_field` (`permission_id`,`field_name`),
  KEY `idx_field_name` (`field_name`),
  KEY `idx_access_level` (`access_level`),
  KEY `idx_module_name` (`module_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_permissions`
--

LOCK TABLES `field_permissions` WRITE;
/*!40000 ALTER TABLE `field_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `field_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iso_code` char(2) NOT NULL COMMENT 'ISO 639-1 language code (e.g., en, es, fr)',
  `country_code` char(2) DEFAULT NULL COMMENT 'ISO 3166-1 country code (e.g., US, ES, MX)',
  `locale_code` varchar(10) NOT NULL COMMENT 'Full locale code (e.g., en-US, es-ES, es-MX)',
  `name_english` varchar(100) NOT NULL COMMENT 'Language name in English',
  `name_native` varchar(100) NOT NULL COMMENT 'Language name in native language',
  `file_name` varchar(50) NOT NULL COMMENT 'Language file name (e.g., en.php, es.php)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether language is available for selection',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether this is the system default language',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_locale` (`locale_code`),
  UNIQUE KEY `unique_filename` (`file_name`),
  UNIQUE KEY `unique_iso_country` (`iso_code`,`country_code`),
  KEY `idx_active` (`is_active`),
  KEY `idx_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_contracting`
--

DROP TABLE IF EXISTS `lead_contracting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_contracting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `contract_number` varchar(50) DEFAULT NULL,
  `contract_type` enum('standard','custom','government','commercial') DEFAULT 'standard',
  `contract_value` decimal(12,2) DEFAULT NULL,
  `contract_signed_date` date DEFAULT NULL,
  `contract_start_date` date DEFAULT NULL,
  `contract_completion_date` date DEFAULT NULL,
  `payment_terms` varchar(200) DEFAULT NULL,
  `payment_schedule` enum('upfront','50_50','milestone','net_30','custom') DEFAULT 'milestone',
  `deposit_amount` decimal(10,2) DEFAULT NULL,
  `deposit_received` tinyint(1) DEFAULT 0,
  `deposit_date` date DEFAULT NULL,
  `project_manager_id` int(11) DEFAULT NULL,
  `lead_technician_id` int(11) DEFAULT NULL,
  `project_start_date` date DEFAULT NULL,
  `estimated_completion_date` date DEFAULT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `project_status` enum('pending','in_progress','on_hold','completed','cancelled') DEFAULT 'pending',
  `completion_percentage` int(3) DEFAULT 0,
  `deliverables` text DEFAULT NULL COMMENT 'JSON array of deliverables',
  `milestones` text DEFAULT NULL COMMENT 'JSON array of milestones',
  `current_milestone` varchar(200) DEFAULT NULL,
  `permits_required` text DEFAULT NULL,
  `permits_obtained` text DEFAULT NULL,
  `insurance_verified` tinyint(1) DEFAULT 0,
  `warranty_terms` text DEFAULT NULL,
  `warranty_start_date` date DEFAULT NULL,
  `warranty_end_date` date DEFAULT NULL,
  `quality_check_completed` tinyint(1) DEFAULT 0,
  `quality_check_date` date DEFAULT NULL,
  `client_satisfaction_score` int(2) DEFAULT NULL COMMENT '1-10 scale',
  `project_notes` text DEFAULT NULL,
  `change_orders` text DEFAULT NULL COMMENT 'JSON array of change orders',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lead_contract` (`lead_id`),
  KEY `idx_contract_number` (`contract_number`),
  KEY `idx_project_status` (`project_status`),
  KEY `idx_project_manager` (`project_manager_id`),
  KEY `idx_completion_date` (`estimated_completion_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_contracting`
--

LOCK TABLES `lead_contracting` WRITE;
/*!40000 ALTER TABLE `lead_contracting` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_contracting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_documents`
--

DROP TABLE IF EXISTS `lead_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `document_type` enum('picture','plan','contract','proposal','survey','other') NOT NULL,
  `document_category` varchar(100) DEFAULT NULL COMMENT 'e.g., initial_submission, site_survey, final_plans',
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_document_type` (`document_type`),
  KEY `idx_category` (`document_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_documents`
--

LOCK TABLES `lead_documents` WRITE;
/*!40000 ALTER TABLE `lead_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_prospects`
--

DROP TABLE IF EXISTS `lead_prospects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_prospects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `site_survey_completed` tinyint(1) DEFAULT 0,
  `site_survey_date` date DEFAULT NULL,
  `site_survey_by` int(11) DEFAULT NULL COMMENT 'User ID who conducted survey',
  `site_survey_notes` text DEFAULT NULL,
  `engineering_review_required` tinyint(1) DEFAULT 0,
  `engineering_review_completed` tinyint(1) DEFAULT 0,
  `engineering_review_date` date DEFAULT NULL,
  `engineering_review_by` int(11) DEFAULT NULL,
  `engineering_notes` text DEFAULT NULL,
  `estimated_system_size` decimal(8,2) DEFAULT NULL COMMENT 'kW or sq ft',
  `system_type` varchar(100) DEFAULT NULL,
  `equipment_specifications` text DEFAULT NULL,
  `estimated_cost_low` decimal(10,2) DEFAULT NULL,
  `estimated_cost_high` decimal(10,2) DEFAULT NULL,
  `final_quoted_price` decimal(10,2) DEFAULT NULL,
  `pricing_notes` text DEFAULT NULL,
  `proposal_version` int(3) DEFAULT 1,
  `proposal_sent_date` date DEFAULT NULL,
  `proposal_valid_until` date DEFAULT NULL,
  `proposal_status` enum('draft','sent','viewed','accepted','rejected','expired') DEFAULT 'draft',
  `last_contact_date` date DEFAULT NULL,
  `next_follow_up_date` date DEFAULT NULL,
  `follow_up_method` enum('email','phone','meeting','site_visit') DEFAULT NULL,
  `prospect_temperature` enum('hot','warm','cold') DEFAULT 'warm',
  `prospect_notes` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lead_prospect` (`lead_id`),
  KEY `idx_survey_date` (`site_survey_date`),
  KEY `idx_proposal_status` (`proposal_status`),
  KEY `idx_follow_up_date` (`next_follow_up_date`),
  KEY `idx_temperature` (`prospect_temperature`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_prospects`
--

LOCK TABLES `lead_prospects` WRITE;
/*!40000 ALTER TABLE `lead_prospects` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_prospects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_referrals`
--

DROP TABLE IF EXISTS `lead_referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `referral_source_type` enum('partner','customer','employee','online','other') DEFAULT 'partner',
  `referral_source_name` varchar(255) DEFAULT NULL,
  `referral_contact_id` int(11) DEFAULT NULL COMMENT 'Links to contacts table',
  `referral_code` varchar(50) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `commission_amount` decimal(10,2) DEFAULT NULL,
  `commission_type` enum('percentage','fixed','tiered') DEFAULT 'percentage',
  `commission_paid` tinyint(1) DEFAULT 0,
  `commission_paid_date` date DEFAULT NULL,
  `agreement_type` varchar(100) DEFAULT NULL,
  `agreement_signed_date` date DEFAULT NULL,
  `referral_notes` text DEFAULT NULL,
  `follow_up_required` tinyint(1) DEFAULT 0,
  `follow_up_date` date DEFAULT NULL,
  `referral_status` enum('pending','qualified','converted','declined') DEFAULT 'pending',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lead_referral` (`lead_id`),
  KEY `idx_referral_contact` (`referral_contact_id`),
  KEY `idx_referral_status` (`referral_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_referrals`
--

LOCK TABLES `lead_referrals` WRITE;
/*!40000 ALTER TABLE `lead_referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_structure_info`
--

DROP TABLE IF EXISTS `lead_structure_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_structure_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `structure_type` tinyint(4) DEFAULT 1,
  `structure_description` varchar(100) DEFAULT NULL,
  `structure_other` varchar(255) DEFAULT NULL,
  `structure_additional` text DEFAULT NULL,
  `building_age` int(4) DEFAULT NULL,
  `building_stories` int(2) DEFAULT NULL,
  `roof_type` varchar(100) DEFAULT NULL,
  `roof_condition` enum('excellent','good','fair','poor') DEFAULT NULL,
  `roof_age` int(3) DEFAULT NULL,
  `electrical_panel_type` varchar(100) DEFAULT NULL,
  `electrical_capacity` varchar(50) DEFAULT NULL,
  `hvac_type` varchar(100) DEFAULT NULL,
  `special_requirements` text DEFAULT NULL,
  `access_restrictions` text DEFAULT NULL,
  `hoa_restrictions` text DEFAULT NULL,
  `permit_requirements` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lead_structure` (`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_structure_info`
--

LOCK TABLES `lead_structure_info` WRITE;
/*!40000 ALTER TABLE `lead_structure_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_structure_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `stage` int(11) DEFAULT 1,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `family_name` varchar(255) DEFAULT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `cell_phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `business_name` varchar(255) DEFAULT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `contact_type` int(11) NOT NULL DEFAULT 1,
  `form_street_1` varchar(100) DEFAULT NULL,
  `form_street_2` varchar(50) DEFAULT NULL,
  `form_city` varchar(50) DEFAULT NULL,
  `form_state` varchar(10) DEFAULT NULL,
  `form_postcode` varchar(15) DEFAULT NULL,
  `form_country` varchar(5) DEFAULT 'US',
  `timezone` varchar(50) DEFAULT NULL COMMENT 'Client timezone (e.g., America/New_York)',
  `full_address` varchar(512) DEFAULT NULL,
  `services_interested_in` varchar(20) DEFAULT NULL,
  `structure_type` tinyint(4) DEFAULT 1,
  `structure_description` varchar(20) DEFAULT NULL,
  `structure_other` varchar(255) DEFAULT NULL,
  `structure_additional` text DEFAULT NULL,
  `eng_system_cost_low` int(11) DEFAULT NULL COMMENT 'Engineering estimate - system cost low range (whole dollars)',
  `eng_system_cost_high` int(11) DEFAULT NULL COMMENT 'Engineering estimate - system cost high range (whole dollars)',
  `eng_protected_area` int(11) DEFAULT NULL COMMENT 'Engineering estimate - protected area (square feet)',
  `eng_cabinets` int(11) DEFAULT NULL COMMENT 'Engineering estimate - number of cabinets',
  `eng_total_pumps` int(11) DEFAULT NULL COMMENT 'Engineering estimate - total number of pumps',
  `picture_submitted_1` varchar(255) DEFAULT NULL,
  `picture_submitted_2` varchar(255) DEFAULT NULL,
  `picture_submitted_3` varchar(255) DEFAULT NULL,
  `plans_submitted_1` varchar(255) DEFAULT NULL,
  `plans_submitted_2` varchar(255) DEFAULT NULL,
  `plans_submitted_3` varchar(255) DEFAULT NULL,
  `picture_submitted` text DEFAULT NULL,
  `plans_submitted` text DEFAULT NULL,
  `get_updates` int(1) DEFAULT 1,
  `hear_about` varchar(20) DEFAULT NULL,
  `hear_about_other` varchar(255) DEFAULT NULL,
  `picture_upload_link` varchar(500) DEFAULT NULL,
  `plans_upload_link` varchar(500) DEFAULT NULL,
  `plans_and_pics` int(1) DEFAULT 0,
  `lead_source` tinyint(4) NOT NULL DEFAULT 1,
  `last_edited_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lead_source` (`lead_source`),
  KEY `idx_email` (`email`),
  KEY `idx_stage` (`stage`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_state` (`form_state`),
  KEY `idx_country` (`form_country`),
  KEY `idx_structure_type` (`structure_type`),
  KEY `idx_last_edited_by` (`last_edited_by`),
  KEY `idx_leads_timezone` (`timezone`),
  KEY `idx_leads_contact_id` (`contact_id`),
  KEY `idx_leads_email` (`email`),
  KEY `idx_leads_phone` (`cell_phone`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES
(1,NULL,NULL,6,'Lead','Test 1',NULL,'555-0001','lead_test_1@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(2,NULL,NULL,1,'Lead','Test 2',NULL,'555-0002','lead_test_2@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(3,NULL,NULL,6,'Lead','Test 3',NULL,'555-0003','lead_test_3@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(4,NULL,NULL,2,'Lead','Test 4',NULL,'555-0004','lead_test_4@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(5,NULL,NULL,4,'Lead','Test 5',NULL,'555-0005','lead_test_5@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(6,NULL,NULL,4,'Lead','Test 6',NULL,'555-0006','lead_test_6@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(7,NULL,NULL,3,'Lead','Test 7',NULL,'555-0007','lead_test_7@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(8,NULL,NULL,1,'Lead','Test 8',NULL,'555-0008','lead_test_8@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(9,NULL,NULL,5,'Lead','Test 9',NULL,'555-0009','lead_test_9@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(10,NULL,NULL,5,'Lead','Test 10',NULL,'555-0010','lead_test_10@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(11,NULL,NULL,6,'Lead','Test 11',NULL,'555-0011','lead_test_11@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(12,NULL,NULL,1,'Lead','Test 12',NULL,'555-0012','lead_test_12@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(13,NULL,NULL,2,'Lead','Test 13',NULL,'555-0013','lead_test_13@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(14,NULL,NULL,2,'Lead','Test 14',NULL,'555-0014','lead_test_14@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(15,NULL,NULL,5,'Lead','Test 15',NULL,'555-0015','lead_test_15@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(16,NULL,NULL,4,'Lead','Test 16',NULL,'555-0016','lead_test_16@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(17,NULL,NULL,1,'Lead','Test 17',NULL,'555-0017','lead_test_17@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(18,NULL,NULL,4,'Lead','Test 18',NULL,'555-0018','lead_test_18@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(19,NULL,NULL,1,'Lead','Test 19',NULL,'555-0019','lead_test_19@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(20,NULL,NULL,3,'Lead','Test 20',NULL,'555-0020','lead_test_20@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53');
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads_contacts`
--

DROP TABLE IF EXISTS `leads_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `relationship_type` varchar(50) DEFAULT 'primary',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lead_contact_relationship` (`lead_id`,`contact_id`,`relationship_type`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_relationship_type` (`relationship_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads_contacts`
--

LOCK TABLES `leads_contacts` WRITE;
/*!40000 ALTER TABLE `leads_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads_extras`
--

DROP TABLE IF EXISTS `leads_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads_extras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_number` int(11) DEFAULT NULL,
  `stage` int(11) DEFAULT 1,
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `family_name` varchar(255) DEFAULT NULL,
  `fullname` varchar(200) NOT NULL,
  `existing_client` varchar(255) DEFAULT NULL,
  `cell_phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `ctype` tinyint(4) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `p_street_1` varchar(100) DEFAULT NULL,
  `p_street_2` varchar(50) DEFAULT NULL,
  `p_city` varchar(50) DEFAULT NULL,
  `p_state` varchar(10) DEFAULT NULL,
  `p_postcode` varchar(15) DEFAULT NULL,
  `p_country` varchar(5) DEFAULT 'US',
  `services_interested_in` varchar(20) DEFAULT NULL,
  `structure_type` tinyint(4) DEFAULT 1,
  `structure_description` varchar(20) DEFAULT NULL,
  `structure_other` varchar(255) DEFAULT NULL,
  `structure_additional` text DEFAULT NULL,
  `picture_submitted_1` varchar(255) DEFAULT NULL,
  `picture_submitted_2` varchar(255) DEFAULT NULL,
  `picture_submitted_3` varchar(255) DEFAULT NULL,
  `plans_submitted_1` varchar(255) DEFAULT NULL,
  `plans_submitted_2` varchar(255) DEFAULT NULL,
  `plans_submitted_3` varchar(255) DEFAULT NULL,
  `picture_submitted` text DEFAULT NULL,
  `plans_submitted` text DEFAULT NULL,
  `get_updates` int(1) DEFAULT 1,
  `hear_about` varchar(20) DEFAULT NULL,
  `hear_about_other` varchar(255) DEFAULT NULL,
  `picture_upload_link` varchar(500) DEFAULT NULL,
  `plans_upload_link` varchar(500) DEFAULT NULL,
  `plans_and_pics` int(1) DEFAULT 0,
  `lead_source` tinyint(4) NOT NULL DEFAULT 1,
  `proposal_sent_date` date DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `lead_lost_notes` text DEFAULT NULL,
  `site_visit_by` varchar(255) DEFAULT NULL,
  `referred_to` varchar(255) DEFAULT NULL,
  `lead_notes` text DEFAULT NULL,
  `prospect_notes` text DEFAULT NULL,
  `lead_lost` varchar(5) DEFAULT NULL,
  `site_visit_completed` varchar(5) DEFAULT NULL,
  `closer` varchar(255) DEFAULT NULL,
  `referred_services` text DEFAULT NULL,
  `assigned_to` varchar(255) DEFAULT NULL,
  `referred` varchar(5) DEFAULT NULL,
  `site_visit_date` date DEFAULT NULL,
  `date_qualified` date DEFAULT NULL,
  `contacted_date` date DEFAULT NULL,
  `referral_done` varchar(5) DEFAULT NULL,
  `jd_referral_notes` text DEFAULT NULL,
  `closing_notes` text DEFAULT NULL,
  `prospect_lost` varchar(5) DEFAULT NULL,
  `to_contracting` varchar(5) DEFAULT NULL,
  `last_edited_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lead_source` (`lead_source`),
  KEY `idx_email` (`email`),
  KEY `idx_stage` (`stage`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_state` (`p_state`),
  KEY `idx_country` (`p_country`),
  KEY `idx_structure_type` (`structure_type`),
  KEY `idx_last_edited_by` (`last_edited_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads_extras`
--

LOCK TABLES `leads_extras` WRITE;
/*!40000 ALTER TABLE `leads_extras` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads_extras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads_notes`
--

DROP TABLE IF EXISTS `leads_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `date_linked` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lead_note` (`lead_id`,`note_id`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_note_id` (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads_notes`
--

LOCK TABLES `leads_notes` WRITE;
/*!40000 ALTER TABLE `leads_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` int(11) NOT NULL DEFAULT 1,
  `note_text` mediumtext NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `form_source` varchar(50) DEFAULT 'leads',
  PRIMARY KEY (`id`),
  KEY `idx_date_created` (`date_created`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_contact_id` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_approval_requests`
--

DROP TABLE IF EXISTS `permission_approval_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_approval_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestor_user_id` int(11) NOT NULL COMMENT 'User requesting permission',
  `permission_id` int(11) NOT NULL,
  `requested_role_id` int(11) DEFAULT NULL COMMENT 'Role context',
  `business_justification` text NOT NULL,
  `approval_chain` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of approvers in sequence' CHECK (json_valid(`approval_chain`)),
  `current_approver_user_id` int(11) DEFAULT NULL COMMENT 'Next person to approve',
  `approval_status` enum('pending','approved','rejected','pending_more_info','expired') DEFAULT 'pending',
  `approval_level` int(11) DEFAULT 0 COMMENT 'How many levels approved so far',
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL COMMENT 'Request validity expires',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_requestor_user_id` (`requestor_user_id`),
  KEY `idx_current_approver_user_id` (`current_approver_user_id`),
  KEY `idx_permission_id` (`permission_id`),
  KEY `idx_approval_status` (`approval_status`),
  KEY `idx_requested_at` (`requested_at`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `fk_approval_role` (`requested_role_id`),
  KEY `idx_approval_chain` (`current_approver_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Multi-level permission approval requests workflow';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_approval_requests`
--

LOCK TABLES `permission_approval_requests` WRITE;
/*!40000 ALTER TABLE `permission_approval_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_approval_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_audit_log`
--

DROP TABLE IF EXISTS `permission_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'User who made the change',
  `action_type` enum('grant','revoke','delegate','approve_delegation','reject_delegation','modify','inherit') NOT NULL,
  `target_user_id` int(11) DEFAULT NULL COMMENT 'User affected by the action',
  `target_role_id` int(11) DEFAULT NULL COMMENT 'Role affected by the action',
  `permission_id` int(11) DEFAULT NULL COMMENT 'Permission affected by the action',
  `delegation_id` int(11) DEFAULT NULL COMMENT 'Delegation affected (if applicable)',
  `old_value` text DEFAULT NULL COMMENT 'Previous value',
  `new_value` text DEFAULT NULL COMMENT 'New value',
  `change_reason` text DEFAULT NULL COMMENT 'Reason for the change',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of user making change',
  `user_agent` text DEFAULT NULL COMMENT 'Browser user agent',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_target_user_id` (`target_user_id`),
  KEY `idx_target_role_id` (`target_role_id`),
  KEY `idx_permission_id` (`permission_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_delegation_id` (`delegation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Complete audit trail of all permission changes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_audit_log`
--

LOCK TABLES `permission_audit_log` WRITE;
/*!40000 ALTER TABLE `permission_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_cache`
--

DROP TABLE IF EXISTS `permission_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) DEFAULT NULL,
  `permission_string` varchar(255) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `result` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_permission_string` (`permission_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_cache`
--

LOCK TABLES `permission_cache` WRITE;
/*!40000 ALTER TABLE `permission_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_delegations`
--

DROP TABLE IF EXISTS `permission_delegations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_delegations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delegating_user_id` int(11) NOT NULL COMMENT 'User granting the permission',
  `receiving_user_id` int(11) NOT NULL COMMENT 'User receiving the permission',
  `permission_id` int(11) NOT NULL,
  `granted_role_id` int(11) DEFAULT NULL COMMENT 'Role context in which permission is delegated',
  `delegation_type` enum('temporary','conditional','approval_pending') DEFAULT 'temporary',
  `approval_status` enum('pending','approved','rejected','revoked') DEFAULT 'pending',
  `approved_by_user_id` int(11) DEFAULT NULL COMMENT 'User who approved the delegation',
  `restrictions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Field-level or record-level restrictions' CHECK (json_valid(`restrictions`)),
  `reason` text DEFAULT NULL COMMENT 'Reason for delegation',
  `start_date` datetime NOT NULL DEFAULT current_timestamp(),
  `end_date` datetime DEFAULT NULL COMMENT 'When delegation expires',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_receiving_user_id` (`receiving_user_id`),
  KEY `idx_delegating_user_id` (`delegating_user_id`),
  KEY `idx_permission_id` (`permission_id`),
  KEY `idx_granted_role_id` (`granted_role_id`),
  KEY `idx_approval_status` (`approval_status`),
  KEY `idx_delegation_type` (`delegation_type`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_end_date` (`end_date`),
  KEY `fk_approving_user` (`approved_by_user_id`),
  KEY `idx_perm_deleg_status` (`approval_status`,`end_date`),
  KEY `idx_perm_deleg_user_date` (`receiving_user_id`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks temporary permission delegations with approval workflow';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_delegations`
--

LOCK TABLES `permission_delegations` WRITE;
/*!40000 ALTER TABLE `permission_delegations` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_delegations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_restrictions`
--

DROP TABLE IF EXISTS `permission_restrictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_restrictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_id` int(11) NOT NULL,
  `restriction_type` enum('field_restriction','record_restriction','time_based','ip_based') NOT NULL,
  `restriction_rule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Specific restriction configuration' CHECK (json_valid(`restriction_rule`)),
  `priority` int(11) DEFAULT 1 COMMENT 'Higher priority restrictions override lower',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_permission_id` (`permission_id`),
  KEY `idx_restriction_type` (`restriction_type`),
  KEY `idx_priority` (`priority`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_restriction_active` (`is_active`,`restriction_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Advanced restrictions on permissions (field-level, time-based, IP-based)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_restrictions`
--

LOCK TABLES `permission_restrictions` WRITE;
/*!40000 ALTER TABLE `permission_restrictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_restrictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `pobject` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL DEFAULT 'general',
  `action` varchar(50) NOT NULL DEFAULT 'access',
  `field_name` varchar(100) DEFAULT NULL,
  `scope` varchar(20) DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `pdescription` varchar(100) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`pid`) USING BTREE,
  KEY `pobject` (`pobject`) USING BTREE,
  KEY `idx_module_action` (`module`,`action`),
  KEY `idx_scope` (`scope`),
  KEY `idx_field_name` (`field_name`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_module_action_scope` (`module`,`action`,`scope`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES
(1,100,'leads.access','general','access',NULL,'all',1,'Leads access','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(2,101,'leads.view','general','access',NULL,'all',1,'Leads view','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(3,102,'leads.create','general','access',NULL,'all',1,'Leads create','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(4,103,'leads.edit','general','access',NULL,'all',1,'Leads edit','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(5,104,'leads.delete','general','access',NULL,'all',1,'Leads delete','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(6,105,'contacts.access','general','access',NULL,'all',1,'Contacts access','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(7,106,'contacts.view','general','access',NULL,'all',1,'Contacts view','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(8,107,'contacts.create','general','access',NULL,'all',1,'Contacts create','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(9,108,'contacts.edit','general','access',NULL,'all',1,'Contacts edit','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(10,109,'contacts.delete','general','access',NULL,'all',1,'Contacts delete','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(11,110,'admin.access','general','access',NULL,'all',1,'Admin access','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(12,111,'admin.users','general','access',NULL,'all',1,'Admin users','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(13,112,'admin.roles','general','access',NULL,'all',1,'Admin roles','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(14,113,'admin.permissions','general','access',NULL,'all',1,'Admin permissions','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(15,1000,'leads.access','general','access',NULL,'all',1,'Leads access','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(16,1001,'contacts.access','general','access',NULL,'all',1,'Contacts access','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(17,1002,'admin.access','general','access',NULL,'all',1,'Admin access','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(18,1003,'leads.view','general','access',NULL,'all',1,'Leads view','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(19,1004,'leads.create','general','access',NULL,'all',1,'Leads create','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(20,1005,'leads.edit','general','access',NULL,'all',1,'Leads edit','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(21,1006,'leads.delete','general','access',NULL,'all',1,'Leads delete','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(22,1007,'leads.export','general','access',NULL,'all',1,'Leads export','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(23,1008,'contacts.view','general','access',NULL,'all',1,'Contacts view','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(24,1009,'contacts.create','general','access',NULL,'all',1,'Contacts create','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(25,1010,'contacts.edit','general','access',NULL,'all',1,'Contacts edit','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(26,1011,'contacts.delete','general','access',NULL,'all',1,'Contacts delete','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(27,1012,'leads.view.email','general','access',NULL,'all',1,'Leads view email','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(28,1013,'leads.edit.stage','general','access',NULL,'all',1,'Leads edit stage','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(29,1014,'leads.view.notes','general','access',NULL,'all',1,'Leads view notes','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(30,1015,'leads.view.own','general','access',NULL,'all',1,'Leads view own','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(31,1016,'leads.edit.own','general','access',NULL,'all',1,'Leads edit own','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(32,1017,'leads.view.team','general','access',NULL,'all',1,'Leads view team','2025-11-20 02:15:53','2025-11-20 02:15:53'),
(33,1018,'leads.view.all','general','access',NULL,'all',1,'Leads view all','2025-11-20 02:15:53','2025-11-20 02:15:53');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phplist_config`
--

DROP TABLE IF EXISTS `phplist_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phplist_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0 COMMENT 'Whether the value is encrypted (for passwords)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList integration configuration';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phplist_config`
--

LOCK TABLES `phplist_config` WRITE;
/*!40000 ALTER TABLE `phplist_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `phplist_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phplist_subscribers`
--

DROP TABLE IF EXISTS `phplist_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phplist_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL COMMENT 'Foreign key to leads table',
  `contact_id` int(11) DEFAULT NULL COMMENT 'Foreign key to contacts table (optional)',
  `phplist_subscriber_id` int(11) DEFAULT NULL COMMENT 'phpList subscriber ID after sync',
  `email` varchar(255) NOT NULL COMMENT 'Email address (copied from lead for quick access)',
  `first_name` varchar(100) DEFAULT NULL COMMENT 'First name (copied from lead)',
  `last_name` varchar(100) DEFAULT NULL COMMENT 'Last name (copied from lead)',
  `sync_status` enum('pending','synced','failed','skipped','unsubscribed') DEFAULT 'pending' COMMENT 'Current sync status',
  `sync_attempts` int(3) DEFAULT 0 COMMENT 'Number of sync attempts made',
  `last_sync_attempt` datetime DEFAULT NULL COMMENT 'Last sync attempt timestamp',
  `last_successful_sync` datetime DEFAULT NULL COMMENT 'Last successful sync timestamp',
  `phplist_lists` text DEFAULT NULL COMMENT 'JSON array of phpList list IDs subscriber belongs to',
  `segmentation_data` text DEFAULT NULL COMMENT 'JSON data for list segmentation (state, service, source)',
  `subscription_preferences` text DEFAULT NULL COMMENT 'JSON data for subscription preferences',
  `error_message` text DEFAULT NULL COMMENT 'Last error message if sync failed',
  `opt_in_date` datetime DEFAULT NULL COMMENT 'When user opted in for updates',
  `opt_out_date` datetime DEFAULT NULL COMMENT 'When user opted out (if applicable)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lead_email` (`lead_id`,`email`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_email` (`email`),
  KEY `idx_sync_status` (`sync_status`),
  KEY `idx_phplist_subscriber_id` (`phplist_subscriber_id`),
  KEY `idx_last_sync_attempt` (`last_sync_attempt`),
  KEY `idx_sync_pending` (`sync_status`,`sync_attempts`,`last_sync_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList subscriber management and sync tracking';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phplist_subscribers`
--

LOCK TABLES `phplist_subscribers` WRITE;
/*!40000 ALTER TABLE `phplist_subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phplist_subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phplist_sync_log`
--

DROP TABLE IF EXISTS `phplist_sync_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phplist_sync_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) DEFAULT NULL COMMENT 'Reference to phplist_subscribers table',
  `sync_type` enum('create','update','delete','bulk_sync') NOT NULL COMMENT 'Type of sync operation',
  `status` enum('success','error','warning') NOT NULL COMMENT 'Sync result status',
  `phplist_response` text DEFAULT NULL COMMENT 'Response from phpList API',
  `error_details` text DEFAULT NULL COMMENT 'Detailed error information',
  `processing_time_ms` int(11) DEFAULT NULL COMMENT 'Processing time in milliseconds',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_subscriber_id` (`subscriber_id`),
  KEY `idx_sync_type` (`sync_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList sync operation logging';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phplist_sync_log`
--

LOCK TABLES `phplist_sync_log` WRITE;
/*!40000 ALTER TABLE `phplist_sync_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `phplist_sync_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_ownership`
--

DROP TABLE IF EXISTS `record_ownership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_ownership` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_type` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `owner_user_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `shared_with_users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shared_with_users`)),
  `shared_with_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shared_with_roles`)),
  `access_level` enum('private','team','department','public') DEFAULT 'private',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_record` (`record_type`,`record_id`),
  KEY `idx_owner_user_id` (`owner_user_id`),
  KEY `idx_record_type` (`record_type`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_access_level` (`access_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_ownership`
--

LOCK TABLES `record_ownership` WRITE;
/*!40000 ALTER TABLE `record_ownership` DISABLE KEYS */;
/*!40000 ALTER TABLE `record_ownership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_hierarchy`
--

DROP TABLE IF EXISTS `role_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_role_id` int(11) NOT NULL,
  `child_role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hierarchy` (`parent_role_id`,`child_role_id`),
  KEY `idx_parent_role` (`parent_role_id`),
  KEY `idx_child_role` (`child_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_hierarchy`
--

LOCK TABLES `role_hierarchy` WRITE;
/*!40000 ALTER TABLE `role_hierarchy` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_hierarchy_cache`
--

DROP TABLE IF EXISTS `role_hierarchy_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_hierarchy_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `inherited_role_id` int(11) NOT NULL,
  `hierarchy_level` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_hierarchy` (`role_id`,`inherited_role_id`),
  KEY `idx_inherited_role` (`inherited_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_hierarchy_cache`
--

LOCK TABLES `role_hierarchy_cache` WRITE;
/*!40000 ALTER TABLE `role_hierarchy_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_hierarchy_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_inheritance`
--

DROP TABLE IF EXISTS `role_inheritance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_inheritance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_role_id` int(11) NOT NULL,
  `child_role_id` int(11) NOT NULL,
  `inheritance_type` enum('full','partial','none') DEFAULT 'full' COMMENT 'full=all perms, partial=selected, none=no inheritance',
  `depth` int(11) NOT NULL COMMENT 'Distance in hierarchy from parent to child',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_parent_child` (`parent_role_id`,`child_role_id`),
  KEY `idx_child_role_id` (`child_role_id`),
  KEY `idx_inheritance_type` (`inheritance_type`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_depth` (`depth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maps role hierarchy relationships with inheritance types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_inheritance`
--

LOCK TABLES `role_inheritance` WRITE;
/*!40000 ALTER TABLE `role_inheritance` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_inheritance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permission_inheritance`
--

DROP TABLE IF EXISTS `role_permission_inheritance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permission_inheritance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `inherited_from_role_id` int(11) NOT NULL COMMENT 'Original role in hierarchy',
  `inheritance_depth` int(11) NOT NULL COMMENT 'Number of levels inherited from parent',
  `inheritance_method` enum('direct','inherited','delegated') DEFAULT 'direct',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_perm_source` (`role_id`,`permission_id`,`inherited_from_role_id`),
  KEY `idx_permission_id` (`permission_id`),
  KEY `idx_inherited_from` (`inherited_from_role_id`),
  KEY `idx_inheritance_depth` (`inheritance_depth`),
  KEY `idx_inheritance_method` (`inheritance_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks which permissions are inherited vs directly assigned';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permission_inheritance`
--

LOCK TABLES `role_permission_inheritance` WRITE;
/*!40000 ALTER TABLE `role_permission_inheritance` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permission_inheritance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `parent_role_id` int(11) DEFAULT NULL,
  `hierarchy_level` int(11) DEFAULT 0,
  `hierarchy_version` int(11) DEFAULT 0,
  `max_delegable_depth` int(11) DEFAULT 1,
  `allows_delegation` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_system` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(0,1,'Admin',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,2,'Manager',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,3,'User',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,4,'Viewer',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,5,'Restricted',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,100,'super_admin',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,101,'sales_manager',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,102,'sales_rep',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,103,'viewer',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1),
(0,104,'restricted',NULL,0,0,1,1,NULL,'2025-11-20 02:15:53','2025-11-20 02:15:53',0,1);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_permissions`
--

DROP TABLE IF EXISTS `roles_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_permissions` (
  `role_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_permissions`
--

LOCK TABLES `roles_permissions` WRITE;
/*!40000 ALTER TABLE `roles_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smtp_config`
--

DROP TABLE IF EXISTS `smtp_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smtp_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'User ID (NULL = default for all users)',
  `config_name` varchar(100) NOT NULL COMMENT 'Friendly name for this configuration',
  `smtp_host` varchar(255) NOT NULL COMMENT 'SMTP server hostname',
  `smtp_port` int(11) NOT NULL DEFAULT 587 COMMENT 'SMTP port (587 for TLS, 465 for SSL)',
  `smtp_encryption` enum('tls','ssl') NOT NULL DEFAULT 'tls' COMMENT 'Encryption type',
  `smtp_username` varchar(255) NOT NULL COMMENT 'SMTP authentication username',
  `smtp_password` text NOT NULL COMMENT 'Encrypted SMTP password',
  `from_email` varchar(255) NOT NULL COMMENT 'From email address',
  `from_name` varchar(255) NOT NULL COMMENT 'From name',
  `reply_to_email` varchar(255) DEFAULT NULL COMMENT 'Reply-to email address',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Is this the default config for the user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Is this configuration active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_default` (`is_default`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='SMTP server configurations for sending emails';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smtp_config`
--

LOCK TABLES `smtp_config` WRITE;
/*!40000 ALTER TABLE `smtp_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `smtp_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_role` varchar(50) DEFAULT 'member',
  `is_lead` tinyint(1) DEFAULT 0,
  `joined_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` timestamp NULL DEFAULT current_timestamp(),
  `end_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_member` (`team_id`,`user_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_is_lead` (`is_lead`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_team_user_active` (`team_id`,`user_id`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_members`
--

LOCK TABLES `team_members` WRITE;
/*!40000 ALTER TABLE `team_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `manager_user_id` int(11) DEFAULT NULL,
  `budget_year` int(11) DEFAULT NULL,
  `status` enum('active','inactive','archived') DEFAULT 'active',
  `is_system` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`),
  KEY `idx_department` (`department`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_manager_user_id` (`manager_user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(250) NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `email` varchar(250) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL COMMENT 'Foreign key to languages table',
  `language` int(2) NOT NULL DEFAULT 1,
  `timezone` varchar(50) DEFAULT 'UTC' COMMENT 'User timezone (e.g., America/New_York)',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_users_language` (`language_id`),
  KEY `idx_users_timezone` (`timezone`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Super Administrator','superadmin','$2y$10$dzbeREFscbGw87NyKR9L1uENv7ciITHG9SwGnVZxFNongzL1475a6',1,'superadmin@democrm.local',NULL,1,'UTC',1,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(2,'Administrator','admin','$2y$10$dzbeREFscbGw87NyKR9L1uENv7ciITHG9SwGnVZxFNongzL1475a6',2,'admin@democrm.local',NULL,1,'UTC',1,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(3,'Sales Manager','salesman','$2y$10$dzbeREFscbGw87NyKR9L1uENv7ciITHG9SwGnVZxFNongzL1475a6',3,'salesman@democrm.local',NULL,1,'UTC',1,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(4,'Sales Assistant','salesasst','$2y$10$dzbeREFscbGw87NyKR9L1uENv7ciITHG9SwGnVZxFNongzL1475a6',4,'salesasst@democrm.local',NULL,1,'UTC',1,'2025-11-20 02:15:53','2025-11-20 02:15:53'),
(5,'Sales Person','salesperson','$2y$10$dzbeREFscbGw87NyKR9L1uENv7ciITHG9SwGnVZxFNongzL1475a6',5,'salesperson@democrm.local',NULL,1,'UTC',1,'2025-11-20 02:15:53','2025-11-20 02:15:53');
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

-- Dump completed on 2025-11-20  2:15:53

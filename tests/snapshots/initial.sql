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
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_attendees_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_attendees_event` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attendees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
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
  KEY `idx_is_sent` (`is_sent`),
  CONSTRAINT `fk_reminders_event` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reminders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
  KEY `fk_calendar_events_updated_by` (`updated_by`),
  CONSTRAINT `fk_calendar_events_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_calendar_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_calendar_events_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_calendar_events_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_calendar_events_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
  UNIQUE KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_calendar_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
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
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_crm_sync_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
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
  KEY `fk_email_processing_lead_id` (`lead_id`),
  CONSTRAINT `fk_email_processing_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL
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
  KEY `idx_permission_id` (`permission_id`),
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
  KEY `idx_completion_date` (`estimated_completion_date`),
  CONSTRAINT `lead_contracting_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
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
  KEY `idx_category` (`document_category`),
  CONSTRAINT `lead_documents_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
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
  KEY `idx_temperature` (`prospect_temperature`),
  CONSTRAINT `lead_prospects_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
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
  KEY `idx_referral_status` (`referral_status`),
  CONSTRAINT `lead_referrals_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lead_referrals_ibfk_2` FOREIGN KEY (`referral_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL
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
  UNIQUE KEY `unique_lead_structure` (`lead_id`),
  CONSTRAINT `lead_structure_info_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
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
  KEY `idx_leads_phone` (`cell_phone`),
  CONSTRAINT `fk_leads_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES
(1,NULL,NULL,1,'Lead','Test 1',NULL,'555-0001','lead_test_1@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(2,NULL,NULL,1,'Lead','Test 2',NULL,'555-0002','lead_test_2@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(3,NULL,NULL,4,'Lead','Test 3',NULL,'555-0003','lead_test_3@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(4,NULL,NULL,6,'Lead','Test 4',NULL,'555-0004','lead_test_4@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(5,NULL,NULL,4,'Lead','Test 5',NULL,'555-0005','lead_test_5@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(6,NULL,NULL,5,'Lead','Test 6',NULL,'555-0006','lead_test_6@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(7,NULL,NULL,4,'Lead','Test 7',NULL,'555-0007','lead_test_7@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(8,NULL,NULL,4,'Lead','Test 8',NULL,'555-0008','lead_test_8@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(9,NULL,NULL,2,'Lead','Test 9',NULL,'555-0009','lead_test_9@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(10,NULL,NULL,5,'Lead','Test 10',NULL,'555-0010','lead_test_10@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(11,NULL,NULL,2,'Lead','Test 11',NULL,'555-0011','lead_test_11@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(12,NULL,NULL,6,'Lead','Test 12',NULL,'555-0012','lead_test_12@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(13,NULL,NULL,5,'Lead','Test 13',NULL,'555-0013','lead_test_13@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(14,NULL,NULL,2,'Lead','Test 14',NULL,'555-0014','lead_test_14@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(15,NULL,NULL,3,'Lead','Test 15',NULL,'555-0015','lead_test_15@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(16,NULL,NULL,6,'Lead','Test 16',NULL,'555-0016','lead_test_16@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(17,NULL,NULL,1,'Lead','Test 17',NULL,'555-0017','lead_test_17@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(18,NULL,NULL,1,'Lead','Test 18',NULL,'555-0018','lead_test_18@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(19,NULL,NULL,5,'Lead','Test 19',NULL,'555-0019','lead_test_19@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(20,NULL,NULL,4,'Lead','Test 20',NULL,'555-0020','lead_test_20@test.com',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,'US',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,1,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14');
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads_backup_20241209`
--

DROP TABLE IF EXISTS `leads_backup_20241209`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads_backup_20241209` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `stage` varchar(20) DEFAULT 'Lead',
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
  `sales_system_cost_low` int(11) DEFAULT NULL COMMENT 'Sales estimate - system cost low range (whole dollars)',
  `sales_system_cost_high` int(11) DEFAULT NULL COMMENT 'Sales estimate - system cost high range (whole dollars)',
  `sales_protected_area` int(11) DEFAULT NULL COMMENT 'Sales estimate - protected area (square feet)',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads_backup_20241209`
--

LOCK TABLES `leads_backup_20241209` WRITE;
/*!40000 ALTER TABLE `leads_backup_20241209` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads_backup_20241209` ENABLE KEYS */;
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
  KEY `idx_relationship_type` (`relationship_type`),
  CONSTRAINT `fk_leads_contacts_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leads_contacts_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
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
  KEY `idx_note_id` (`note_id`),
  CONSTRAINT `leads_notes_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leads_notes_ibfk_2` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE
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
-- Table structure for table `leads_old`
--

DROP TABLE IF EXISTS `leads_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `estimate_number` int(11) DEFAULT NULL,
  `stage` varchar(50) DEFAULT NULL,
  `proposal_sent_date` date DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL,
  `structure_type` varchar(50) DEFAULT NULL,
  `lead_source` varchar(50) DEFAULT NULL,
  `lead_lost_notes` text DEFAULT NULL,
  `plans_submitted` varchar(5) DEFAULT NULL,
  `structure_description` text DEFAULT NULL,
  `structure_other` text DEFAULT NULL,
  `site_visit_by` varchar(255) DEFAULT NULL,
  `picture_submitted` varchar(5) DEFAULT NULL,
  `referred_to` varchar(255) DEFAULT NULL,
  `picture_upload_link` text DEFAULT NULL,
  `plans_upload_link` text DEFAULT NULL,
  `existing_client` varchar(5) DEFAULT NULL,
  `get_updates` varchar(5) DEFAULT NULL,
  `hear_about` varchar(255) DEFAULT NULL,
  `hear_about_other` text DEFAULT NULL,
  `structure_additional` text DEFAULT NULL,
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
  `plans_and_pics` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads_old`
--

LOCK TABLES `leads_old` WRITE;
/*!40000 ALTER TABLE `leads_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads_old` ENABLE KEYS */;
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
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `pobject` varchar(100) NOT NULL,
  `pdescription` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL DEFAULT 'general',
  `action` varchar(50) NOT NULL DEFAULT 'access',
  `field_name` varchar(100) DEFAULT NULL,
  `scope` varchar(20) DEFAULT 'all',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`pid`),
  KEY `idx_pid` (`pid`),
  KEY `idx_pobject` (`pobject`),
  KEY `idx_module_action` (`module`,`action`),
  KEY `idx_scope` (`scope`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES
(1,1000,'leads.access','Access Leads Module','leads','access',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(2,1001,'leads.view','View Leads','leads','view',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(3,1002,'leads.create','Create Leads','leads','create',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(4,1003,'leads.edit','Edit Leads','leads','edit',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(5,1004,'leads.delete','Delete Leads','leads','delete',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(6,1005,'leads.export','Export Leads','leads','export',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(7,1010,'contacts.access','Access Contacts Module','contacts','access',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(8,1011,'contacts.view','View Contacts','contacts','view',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(9,1012,'contacts.create','Create Contacts','contacts','create',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(10,1013,'contacts.edit','Edit Contacts','contacts','edit',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(11,1014,'contacts.delete','Delete Contacts','contacts','delete',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(12,1020,'users.access','Access Users Module','users','access',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(13,1021,'users.view','View Users','users','view',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(14,1022,'users.create','Create Users','users','create',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(15,1023,'users.edit','Edit Users','users','edit',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(16,1024,'users.delete','Delete Users','users','delete',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(17,1030,'admin.access','Access Admin Module','admin','access',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(18,1031,'admin.security','Manage Security','admin','security',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(19,1032,'admin.settings','Manage Settings','admin','settings',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(20,1033,'admin.users','Manage Users','admin','users',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(21,1034,'admin.roles','Manage Roles','admin','roles',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(22,1035,'admin.permissions','Manage Permissions','admin','permissions',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(23,1040,'calendar.access','Access Calendar Module','calendar','access',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(24,1041,'calendar.view','View Calendar','calendar','view',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(25,1042,'calendar.create','Create Events','calendar','create',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(26,1043,'calendar.edit','Edit Events','calendar','edit',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(27,1044,'calendar.delete','Delete Events','calendar','delete',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(28,1050,'reports.access','Access Reports Module','reports','access',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(29,1051,'reports.view','View Reports','reports','view',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(30,1052,'reports.create','Create Reports','reports','create',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(31,1053,'reports.export','Export Reports','reports','export',NULL,'all','2025-11-18 20:29:13','2025-11-18 20:29:13'),
(32,100,'leads.access','Leads access','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(33,101,'leads.view','Leads view','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(34,102,'leads.create','Leads create','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(35,103,'leads.edit','Leads edit','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(36,104,'leads.delete','Leads delete','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(37,105,'contacts.access','Contacts access','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(38,106,'contacts.view','Contacts view','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(39,107,'contacts.create','Contacts create','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(40,108,'contacts.edit','Contacts edit','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(41,109,'contacts.delete','Contacts delete','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(42,110,'admin.access','Admin access','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(43,111,'admin.users','Admin users','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(44,112,'admin.roles','Admin roles','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(45,113,'admin.permissions','Admin permissions','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(49,1006,'leads.view','Leads view','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(50,1007,'leads.create','Leads create','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(51,1008,'leads.edit','Leads edit','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(52,1009,'leads.delete','Leads delete','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(56,1016,'contacts.edit','Contacts edit','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(57,1017,'contacts.delete','Contacts delete','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(58,1018,'leads.view.email','Leads view email','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(59,1019,'leads.edit.stage','Leads edit stage','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(63,1026,'leads.view.team','Leads view team','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14'),
(64,1027,'leads.view.all','Leads view all','general','access',NULL,'all','2025-11-18 20:29:14','2025-11-18 20:29:14');
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
  KEY `idx_sync_pending` (`sync_status`,`sync_attempts`,`last_sync_attempt`),
  CONSTRAINT `fk_phplist_subscribers_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_phplist_subscribers_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
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
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_phplist_sync_log_subscriber` FOREIGN KEY (`subscriber_id`) REFERENCES `phplist_subscribers` (`id`) ON DELETE CASCADE
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
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `parent_role_id` int(11) DEFAULT NULL,
  `hierarchy_level` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id` (`role_id`),
  UNIQUE KEY `role` (`role`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,1,'Admin',NULL,0,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(2,2,'Manager',NULL,0,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(3,3,'User',NULL,0,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(4,4,'Viewer',NULL,0,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(5,5,'Restricted',NULL,0,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(6,100,'super_admin',NULL,0,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(7,101,'sales_manager',NULL,0,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(8,102,'sales_rep',NULL,0,NULL,'2025-11-18 20:29:14','2025-11-18 20:29:14');
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`role_id`,`pid`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_permissions`
--

LOCK TABLES `roles_permissions` WRITE;
/*!40000 ALTER TABLE `roles_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles_permissions` ENABLE KEYS */;
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_member` (`team_id`,`user_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_is_lead` (`is_lead`)
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`),
  KEY `idx_department` (`department`),
  KEY `idx_is_active` (`is_active`)
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
  KEY `idx_users_timezone` (`timezone`),
  CONSTRAINT `fk_users_language` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Test User 1','test_user_1','$2y$10$yczDkVWi5L1LqdTNB6xj2uHgS2escWAmSbJ6DHD5vWOeNHMBF47EC',1,'test_user_1@test.com',NULL,1,'UTC',1,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(2,'Test User 2','test_user_2','$2y$10$wLTDPcsod5aymK0h.dRRpOGFcbzssofoOxcJbZUcRs8usS9ufmreu',2,'test_user_2@test.com',NULL,1,'UTC',1,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(3,'Test User 3','test_user_3','$2y$10$zWZOuFeV4w/CJiQIfShq3esu52LQqlgEJ98ue7PJ7AYZn77OjPP7y',2,'test_user_3@test.com',NULL,1,'UTC',1,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(4,'Test User 4','test_user_4','$2y$10$Fe05trEpbSXrOfsd66Ss5OYAdpBwt0J3bXRKUJDkVNUUaHHVAzdsy',2,'test_user_4@test.com',NULL,1,'UTC',1,'2025-11-18 20:29:14','2025-11-18 20:29:14'),
(5,'Test User 5','test_user_5','$2y$10$s1fUt/kbIKKzflL4vryrtePH5YRe.ldfaDkfPv60ybj4i25jml4ue',2,'test_user_5@test.com',NULL,1,'UTC',1,'2025-11-18 20:29:14','2025-11-18 20:29:14');
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

-- Dump completed on 2025-11-18 20:29:14

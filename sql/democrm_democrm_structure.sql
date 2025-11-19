-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 19, 2025 at 01:38 AM
-- Server version: 10.11.9-MariaDB
-- PHP Version: 7.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `democrm_democrm`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit`
--

CREATE TABLE `audit` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event` varchar(255) NOT NULL,
  `resource` varchar(255) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `useragent` varchar(510) NOT NULL,
  `location` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
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
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_event_attendees`
--

CREATE TABLE `calendar_event_attendees` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `response_status` int(11) NOT NULL DEFAULT 1 COMMENT '1=pending, 2=accepted, 3=declined, 4=tentative',
  `is_organizer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_event_reminders`
--

CREATE TABLE `calendar_event_reminders` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reminder_datetime` datetime NOT NULL,
  `reminder_type` int(11) NOT NULL DEFAULT 1 COMMENT '1=email, 2=sms, 3=push, 4=popup',
  `is_sent` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_user_settings`
--

CREATE TABLE `calendar_user_settings` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `contact_type` int(11) NOT NULL DEFAULT 1,
  `call_order` int(1) UNSIGNED DEFAULT NULL,
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
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

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
  `zh-tw` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crm_sync_queue`
--

CREATE TABLE `crm_sync_queue` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Queue for syncing leads to external CRM systems';

-- --------------------------------------------------------

--
-- Table structure for table `effective_permissions_cache`
--

CREATE TABLE `effective_permissions_cache` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) NOT NULL,
  `permission_source` enum('direct','inherited','delegated','temporary') DEFAULT 'direct',
  `calculation_method` enum('full','hierarchy','delegation','approval') DEFAULT 'full',
  `is_active` tinyint(1) DEFAULT 1,
  `cached_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cache of effective permissions (direct + inherited + delegated)';

-- --------------------------------------------------------

--
-- Table structure for table `email_accounts_config`
--

CREATE TABLE `email_accounts_config` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Configuration for email accounts to monitor';

-- --------------------------------------------------------

--
-- Table structure for table `email_form_processing`
--

CREATE TABLE `email_form_processing` (
  `id` int(11) NOT NULL,
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
  `error_message` text DEFAULT NULL COMMENT 'Error details if processing failed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log of email form processing activities';

-- --------------------------------------------------------

--
-- Table structure for table `email_global_templates`
--

CREATE TABLE `email_global_templates` (
  `id` int(11) NOT NULL,
  `template_type` enum('header','footer') NOT NULL,
  `language_code` varchar(5) NOT NULL DEFAULT 'en',
  `html_content` text NOT NULL,
  `plain_text_content` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Global header/footer templates inherited by all modules';

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
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
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Queue for emails pending approval or scheduled sending';

-- --------------------------------------------------------

--
-- Table structure for table `email_send_log`
--

CREATE TABLE `email_send_log` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log of all emails sent from the system';

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
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
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Module-specific email templates with trigger configuration';

-- --------------------------------------------------------

--
-- Table structure for table `email_template_content`
--

CREATE TABLE `email_template_content` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `language_code` varchar(5) NOT NULL DEFAULT 'en',
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL COMMENT 'Main email body with shortcodes',
  `body_plain_text` text DEFAULT NULL COMMENT 'Plain text version',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multilingual content for email templates (body only, header/footer inherited)';

-- --------------------------------------------------------

--
-- Table structure for table `email_template_variables`
--

CREATE TABLE `email_template_variables` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `variable_key` varchar(100) NOT NULL COMMENT 'e.g., lead_name, company_name, assigned_user',
  `variable_label` varchar(255) NOT NULL,
  `variable_description` text DEFAULT NULL,
  `variable_type` varchar(50) DEFAULT 'text' COMMENT 'text, date, currency, url, phone',
  `variable_source` varchar(100) DEFAULT NULL COMMENT 'Database field or method to get value',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available variables/shortcodes for each template';

-- --------------------------------------------------------

--
-- Table structure for table `email_trigger_rules`
--

CREATE TABLE `email_trigger_rules` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `trigger_type` enum('stage_change','assignment','field_update','time_based') NOT NULL,
  `trigger_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Conditions: stage_from, stage_to, field_name, etc.' CHECK (json_valid(`trigger_condition`)),
  `recipient_type` enum('lead_contact','assigned_user','custom_email','both') DEFAULT 'lead_contact',
  `custom_recipient_email` varchar(255) DEFAULT NULL COMMENT 'For custom_email recipient type',
  `delay_minutes` int(11) DEFAULT 0 COMMENT 'Delay before sending (0=immediate)',
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rules for automatic email triggering based on events';

-- --------------------------------------------------------

--
-- Table structure for table `field_permissions`
--

CREATE TABLE `field_permissions` (
  `id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(150) DEFAULT NULL,
  `access_level` enum('none','view','edit') DEFAULT 'view',
  `module_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `iso_code` char(2) NOT NULL COMMENT 'ISO 639-1 language code (e.g., en, es, fr)',
  `country_code` char(2) DEFAULT NULL COMMENT 'ISO 3166-1 country code (e.g., US, ES, MX)',
  `locale_code` varchar(10) NOT NULL COMMENT 'Full locale code (e.g., en-US, es-ES, es-MX)',
  `name_english` varchar(100) NOT NULL COMMENT 'Language name in English',
  `name_native` varchar(100) NOT NULL COMMENT 'Language name in native language',
  `file_name` varchar(50) NOT NULL COMMENT 'Language file name (e.g., en.php, es.php)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether language is available for selection',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether this is the system default language',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads_contacts`
--

CREATE TABLE `leads_contacts` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `relationship_type` varchar(50) DEFAULT 'primary',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads_extras`
--

CREATE TABLE `leads_extras` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads_notes`
--

CREATE TABLE `leads_notes` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `date_linked` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_contracting`
--

CREATE TABLE `lead_contracting` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_documents`
--

CREATE TABLE `lead_documents` (
  `id` int(11) NOT NULL,
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
  `sort_order` int(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_prospects`
--

CREATE TABLE `lead_prospects` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_referrals`
--

CREATE TABLE `lead_referrals` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_structure_info`
--

CREATE TABLE `lead_structure_info` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `source` int(11) NOT NULL DEFAULT 1,
  `note_text` mediumtext NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `form_source` varchar(50) DEFAULT 'leads'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `pobject` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL DEFAULT 'general',
  `action` varchar(50) NOT NULL DEFAULT 'access',
  `field_name` varchar(100) DEFAULT NULL,
  `scope` varchar(20) DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `pdescription` varchar(100) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_approval_requests`
--

CREATE TABLE `permission_approval_requests` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Multi-level permission approval requests workflow';

-- --------------------------------------------------------

--
-- Table structure for table `permission_audit_log`
--

CREATE TABLE `permission_audit_log` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Complete audit trail of all permission changes';

-- --------------------------------------------------------

--
-- Table structure for table `permission_cache`
--

CREATE TABLE `permission_cache` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) DEFAULT NULL,
  `permission_string` varchar(255) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `result` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_delegations`
--

CREATE TABLE `permission_delegations` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks temporary permission delegations with approval workflow';

-- --------------------------------------------------------

--
-- Table structure for table `permission_restrictions`
--

CREATE TABLE `permission_restrictions` (
  `id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `restriction_type` enum('field_restriction','record_restriction','time_based','ip_based') NOT NULL,
  `restriction_rule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Specific restriction configuration' CHECK (json_valid(`restriction_rule`)),
  `priority` int(11) DEFAULT 1 COMMENT 'Higher priority restrictions override lower',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Advanced restrictions on permissions (field-level, time-based, IP-based)';

-- --------------------------------------------------------

--
-- Table structure for table `phplist_config`
--

CREATE TABLE `phplist_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0 COMMENT 'Whether the value is encrypted (for passwords)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList integration configuration';

-- --------------------------------------------------------

--
-- Table structure for table `phplist_subscribers`
--

CREATE TABLE `phplist_subscribers` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList subscriber management and sync tracking';

-- --------------------------------------------------------

--
-- Table structure for table `phplist_sync_log`
--

CREATE TABLE `phplist_sync_log` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) DEFAULT NULL COMMENT 'Reference to phplist_subscribers table',
  `sync_type` enum('create','update','delete','bulk_sync') NOT NULL COMMENT 'Type of sync operation',
  `status` enum('success','error','warning') NOT NULL COMMENT 'Sync result status',
  `phplist_response` text DEFAULT NULL COMMENT 'Response from phpList API',
  `error_details` text DEFAULT NULL COMMENT 'Detailed error information',
  `processing_time_ms` int(11) DEFAULT NULL COMMENT 'Processing time in milliseconds',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList sync operation logging';

-- --------------------------------------------------------

--
-- Table structure for table `record_ownership`
--

CREATE TABLE `record_ownership` (
  `id` int(11) NOT NULL,
  `record_type` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `owner_user_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `shared_with_users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shared_with_users`)),
  `shared_with_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shared_with_roles`)),
  `access_level` enum('private','team','department','public') DEFAULT 'private',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `roles_permissions`
--

CREATE TABLE `roles_permissions` (
  `role_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_hierarchy`
--

CREATE TABLE `role_hierarchy` (
  `id` int(11) NOT NULL,
  `parent_role_id` int(11) NOT NULL,
  `child_role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_hierarchy_cache`
--

CREATE TABLE `role_hierarchy_cache` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `inherited_role_id` int(11) NOT NULL,
  `hierarchy_level` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_inheritance`
--

CREATE TABLE `role_inheritance` (
  `id` int(11) NOT NULL,
  `parent_role_id` int(11) NOT NULL,
  `child_role_id` int(11) NOT NULL,
  `inheritance_type` enum('full','partial','none') DEFAULT 'full' COMMENT 'full=all perms, partial=selected, none=no inheritance',
  `depth` int(11) NOT NULL COMMENT 'Distance in hierarchy from parent to child',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maps role hierarchy relationships with inheritance types';

-- --------------------------------------------------------

--
-- Table structure for table `role_permission_inheritance`
--

CREATE TABLE `role_permission_inheritance` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `inherited_from_role_id` int(11) NOT NULL COMMENT 'Original role in hierarchy',
  `inheritance_depth` int(11) NOT NULL COMMENT 'Number of levels inherited from parent',
  `inheritance_method` enum('direct','inherited','delegated') DEFAULT 'direct',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks which permissions are inherited vs directly assigned';

-- --------------------------------------------------------

--
-- Table structure for table `smtp_config`
--

CREATE TABLE `smtp_config` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='SMTP server configurations for sending emails';

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
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
  `is_system` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_role` varchar(50) DEFAULT 'member',
  `is_lead` tinyint(1) DEFAULT 0,
  `joined_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` timestamp NULL DEFAULT current_timestamp(),
  `end_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(250) NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(250) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL COMMENT 'Foreign key to languages table',
  `language` int(2) NOT NULL DEFAULT 1,
  `timezone` varchar(50) DEFAULT 'UTC' COMMENT 'User timezone (e.g., America/New_York)',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit`
--
ALTER TABLE `audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_contact_id` (`contact_id`),
  ADD KEY `idx_start_datetime` (`start_datetime`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `fk_calendar_events_created_by` (`created_by`),
  ADD KEY `fk_calendar_events_updated_by` (`updated_by`);

--
-- Indexes for table `calendar_event_attendees`
--
ALTER TABLE `calendar_event_attendees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_contact_id` (`contact_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `calendar_event_reminders`
--
ALTER TABLE `calendar_event_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reminder_datetime` (`reminder_datetime`),
  ADD KEY `idx_is_sent` (`is_sent`);

--
-- Indexes for table `calendar_user_settings`
--
ALTER TABLE `calendar_user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prop_id` (`lead_id`),
  ADD KEY `idx_contacts_timezone` (`timezone`),
  ADD KEY `idx_contacts_lead_id` (`lead_id`),
  ADD KEY `idx_contacts_email` (`personal_email`),
  ADD KEY `idx_contacts_phone` (`cell_phone`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crm_sync_queue`
--
ALTER TABLE `crm_sync_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sync_status` (`sync_status`),
  ADD KEY `idx_next_retry` (`next_retry_at`),
  ADD KEY `idx_external_system` (`external_system`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `effective_permissions_cache`
--
ALTER TABLE `effective_permissions_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_perm_cache` (`user_id`,`permission_id`,`role_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_permission_id` (`permission_id`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_permission_source` (`permission_source`),
  ADD KEY `fk_cache_role` (`role_id`),
  ADD KEY `idx_effective_perm_user` (`user_id`,`permission_source`);

--
-- Indexes for table `email_accounts_config`
--
ALTER TABLE `email_accounts_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_address` (`email_address`),
  ADD UNIQUE KEY `idx_email_address` (`email_address`),
  ADD KEY `idx_form_type` (`form_type`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `email_form_processing`
--
ALTER TABLE `email_form_processing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_account` (`email_account`),
  ADD KEY `idx_form_type` (`form_type`),
  ADD KEY `idx_processing_status` (`processing_status`),
  ADD KEY `idx_processed_at` (`processed_at`),
  ADD KEY `idx_sender_email` (`sender_email`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `fk_email_processing_lead_id` (`lead_id`);

--
-- Indexes for table `email_global_templates`
--
ALTER TABLE `email_global_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_language` (`template_type`,`language_code`),
  ADD KEY `language_code` (`language_code`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `module_record` (`module`,`record_id`),
  ADD KEY `scheduled_send_at` (`scheduled_send_at`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `email_send_log`
--
ALTER TABLE `email_send_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_smtp_config_id` (`smtp_config_id`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_contact_id` (`contact_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email_type` (`email_type`),
  ADD KEY `idx_lead_source_id` (`lead_source_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_key` (`template_key`),
  ADD KEY `module` (`module`),
  ADD KEY `trigger_event` (`trigger_event`);

--
-- Indexes for table `email_template_content`
--
ALTER TABLE `email_template_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_language` (`template_id`,`language_code`);

--
-- Indexes for table `email_template_variables`
--
ALTER TABLE `email_template_variables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `email_trigger_rules`
--
ALTER TABLE `email_trigger_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `module_trigger` (`module`,`trigger_type`);

--
-- Indexes for table `field_permissions`
--
ALTER TABLE `field_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permission_field` (`permission_id`,`field_name`),
  ADD KEY `idx_field_name` (`field_name`),
  ADD KEY `idx_access_level` (`access_level`),
  ADD KEY `idx_module_name` (`module_name`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_locale` (`locale_code`),
  ADD UNIQUE KEY `unique_filename` (`file_name`),
  ADD UNIQUE KEY `unique_iso_country` (`iso_code`,`country_code`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_default` (`is_default`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_source` (`lead_source`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_stage` (`stage`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_state` (`form_state`),
  ADD KEY `idx_country` (`form_country`),
  ADD KEY `idx_structure_type` (`structure_type`),
  ADD KEY `idx_last_edited_by` (`last_edited_by`),
  ADD KEY `idx_leads_timezone` (`timezone`),
  ADD KEY `idx_leads_contact_id` (`contact_id`),
  ADD KEY `idx_leads_email` (`email`),
  ADD KEY `idx_leads_phone` (`cell_phone`);

--
-- Indexes for table `leads_contacts`
--
ALTER TABLE `leads_contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_contact_relationship` (`lead_id`,`contact_id`,`relationship_type`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_contact_id` (`contact_id`),
  ADD KEY `idx_relationship_type` (`relationship_type`);

--
-- Indexes for table `leads_extras`
--
ALTER TABLE `leads_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_source` (`lead_source`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_stage` (`stage`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_state` (`p_state`),
  ADD KEY `idx_country` (`p_country`),
  ADD KEY `idx_structure_type` (`structure_type`),
  ADD KEY `idx_last_edited_by` (`last_edited_by`);

--
-- Indexes for table `leads_notes`
--
ALTER TABLE `leads_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_note` (`lead_id`,`note_id`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_note_id` (`note_id`);

--
-- Indexes for table `lead_contracting`
--
ALTER TABLE `lead_contracting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_contract` (`lead_id`),
  ADD KEY `idx_contract_number` (`contract_number`),
  ADD KEY `idx_project_status` (`project_status`),
  ADD KEY `idx_project_manager` (`project_manager_id`),
  ADD KEY `idx_completion_date` (`estimated_completion_date`);

--
-- Indexes for table `lead_documents`
--
ALTER TABLE `lead_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_category` (`document_category`);

--
-- Indexes for table `lead_prospects`
--
ALTER TABLE `lead_prospects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_prospect` (`lead_id`),
  ADD KEY `idx_survey_date` (`site_survey_date`),
  ADD KEY `idx_proposal_status` (`proposal_status`),
  ADD KEY `idx_follow_up_date` (`next_follow_up_date`),
  ADD KEY `idx_temperature` (`prospect_temperature`);

--
-- Indexes for table `lead_referrals`
--
ALTER TABLE `lead_referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_referral` (`lead_id`),
  ADD KEY `idx_referral_contact` (`referral_contact_id`),
  ADD KEY `idx_referral_status` (`referral_status`);

--
-- Indexes for table `lead_structure_info`
--
ALTER TABLE `lead_structure_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_structure` (`lead_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_created` (`date_created`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_contact_id` (`contact_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pid` (`pid`) USING BTREE,
  ADD KEY `pobject` (`pobject`) USING BTREE,
  ADD KEY `idx_module_action` (`module`,`action`),
  ADD KEY `idx_scope` (`scope`),
  ADD KEY `idx_field_name` (`field_name`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_module_action_scope` (`module`,`action`,`scope`);

--
-- Indexes for table `permission_approval_requests`
--
ALTER TABLE `permission_approval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requestor_user_id` (`requestor_user_id`),
  ADD KEY `idx_current_approver_user_id` (`current_approver_user_id`),
  ADD KEY `idx_permission_id` (`permission_id`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `idx_requested_at` (`requested_at`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `fk_approval_role` (`requested_role_id`),
  ADD KEY `idx_approval_chain` (`current_approver_user_id`);

--
-- Indexes for table `permission_audit_log`
--
ALTER TABLE `permission_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_target_user_id` (`target_user_id`),
  ADD KEY `idx_target_role_id` (`target_role_id`),
  ADD KEY `idx_permission_id` (`permission_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_delegation_id` (`delegation_id`);

--
-- Indexes for table `permission_cache`
--
ALTER TABLE `permission_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_permission_string` (`permission_string`);

--
-- Indexes for table `permission_delegations`
--
ALTER TABLE `permission_delegations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receiving_user_id` (`receiving_user_id`),
  ADD KEY `idx_delegating_user_id` (`delegating_user_id`),
  ADD KEY `idx_permission_id` (`permission_id`),
  ADD KEY `idx_granted_role_id` (`granted_role_id`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `idx_delegation_type` (`delegation_type`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `fk_approving_user` (`approved_by_user_id`),
  ADD KEY `idx_perm_deleg_status` (`approval_status`,`end_date`),
  ADD KEY `idx_perm_deleg_user_date` (`receiving_user_id`,`end_date`);

--
-- Indexes for table `permission_restrictions`
--
ALTER TABLE `permission_restrictions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_permission_id` (`permission_id`),
  ADD KEY `idx_restriction_type` (`restriction_type`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_restriction_active` (`is_active`,`restriction_type`);

--
-- Indexes for table `phplist_config`
--
ALTER TABLE `phplist_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indexes for table `phplist_subscribers`
--
ALTER TABLE `phplist_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_email` (`lead_id`,`email`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_contact_id` (`contact_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_sync_status` (`sync_status`),
  ADD KEY `idx_phplist_subscriber_id` (`phplist_subscriber_id`),
  ADD KEY `idx_last_sync_attempt` (`last_sync_attempt`),
  ADD KEY `idx_sync_pending` (`sync_status`,`sync_attempts`,`last_sync_attempt`);

--
-- Indexes for table `phplist_sync_log`
--
ALTER TABLE `phplist_sync_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_sync_type` (`sync_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `record_ownership`
--
ALTER TABLE `record_ownership`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_record` (`record_type`,`record_id`),
  ADD KEY `idx_owner_user_id` (`owner_user_id`),
  ADD KEY `idx_record_type` (`record_type`),
  ADD KEY `idx_team_id` (`team_id`),
  ADD KEY `idx_access_level` (`access_level`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_id` (`role_id`),
  ADD UNIQUE KEY `role` (`role`),
  ADD KEY `idx_hierarchy_level` (`hierarchy_level`),
  ADD KEY `idx_parent_role` (`parent_role_id`),
  ADD KEY `idx_parent_role_id` (`parent_role_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_role_active` (`is_active`,`id`);

--
-- Indexes for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  ADD PRIMARY KEY (`role_id`,`pid`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_role_permission` (`role_id`,`pid`);

--
-- Indexes for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hierarchy` (`parent_role_id`,`child_role_id`),
  ADD KEY `idx_parent_role` (`parent_role_id`),
  ADD KEY `idx_child_role` (`child_role_id`);

--
-- Indexes for table `role_hierarchy_cache`
--
ALTER TABLE `role_hierarchy_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hierarchy` (`role_id`,`inherited_role_id`),
  ADD KEY `idx_inherited_role` (`inherited_role_id`);

--
-- Indexes for table `role_inheritance`
--
ALTER TABLE `role_inheritance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_parent_child` (`parent_role_id`,`child_role_id`),
  ADD KEY `idx_child_role_id` (`child_role_id`),
  ADD KEY `idx_inheritance_type` (`inheritance_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_depth` (`depth`);

--
-- Indexes for table `role_permission_inheritance`
--
ALTER TABLE `role_permission_inheritance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_role_perm_source` (`role_id`,`permission_id`,`inherited_from_role_id`),
  ADD KEY `idx_permission_id` (`permission_id`),
  ADD KEY `idx_inherited_from` (`inherited_from_role_id`),
  ADD KEY `idx_inheritance_depth` (`inheritance_depth`),
  ADD KEY `idx_inheritance_method` (`inheritance_method`);

--
-- Indexes for table `smtp_config`
--
ALTER TABLE `smtp_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_default` (`is_default`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_department` (`department`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_manager_user_id` (`manager_user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_member` (`team_id`,`user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_team_id` (`team_id`),
  ADD KEY `idx_is_lead` (`is_lead`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_team_user_active` (`team_id`,`user_id`,`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_language` (`language_id`),
  ADD KEY `idx_users_timezone` (`timezone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit`
--
ALTER TABLE `audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calendar_event_attendees`
--
ALTER TABLE `calendar_event_attendees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calendar_event_reminders`
--
ALTER TABLE `calendar_event_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calendar_user_settings`
--
ALTER TABLE `calendar_user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crm_sync_queue`
--
ALTER TABLE `crm_sync_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `effective_permissions_cache`
--
ALTER TABLE `effective_permissions_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_accounts_config`
--
ALTER TABLE `email_accounts_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_form_processing`
--
ALTER TABLE `email_form_processing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_global_templates`
--
ALTER TABLE `email_global_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_send_log`
--
ALTER TABLE `email_send_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_template_content`
--
ALTER TABLE `email_template_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_template_variables`
--
ALTER TABLE `email_template_variables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_trigger_rules`
--
ALTER TABLE `email_trigger_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field_permissions`
--
ALTER TABLE `field_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads_contacts`
--
ALTER TABLE `leads_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads_extras`
--
ALTER TABLE `leads_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads_notes`
--
ALTER TABLE `leads_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_contracting`
--
ALTER TABLE `lead_contracting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_documents`
--
ALTER TABLE `lead_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_prospects`
--
ALTER TABLE `lead_prospects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_referrals`
--
ALTER TABLE `lead_referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_structure_info`
--
ALTER TABLE `lead_structure_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_approval_requests`
--
ALTER TABLE `permission_approval_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_audit_log`
--
ALTER TABLE `permission_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_cache`
--
ALTER TABLE `permission_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_delegations`
--
ALTER TABLE `permission_delegations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_restrictions`
--
ALTER TABLE `permission_restrictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phplist_config`
--
ALTER TABLE `phplist_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phplist_subscribers`
--
ALTER TABLE `phplist_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phplist_sync_log`
--
ALTER TABLE `phplist_sync_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `record_ownership`
--
ALTER TABLE `record_ownership`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_hierarchy_cache`
--
ALTER TABLE `role_hierarchy_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_inheritance`
--
ALTER TABLE `role_inheritance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permission_inheritance`
--
ALTER TABLE `role_permission_inheritance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `smtp_config`
--
ALTER TABLE `smtp_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD CONSTRAINT `fk_calendar_events_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_calendar_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_calendar_events_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_calendar_events_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_calendar_events_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `calendar_event_attendees`
--
ALTER TABLE `calendar_event_attendees`
  ADD CONSTRAINT `fk_attendees_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_attendees_event` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `calendar_event_reminders`
--
ALTER TABLE `calendar_event_reminders`
  ADD CONSTRAINT `fk_reminders_event` FOREIGN KEY (`event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reminders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `calendar_user_settings`
--
ALTER TABLE `calendar_user_settings`
  ADD CONSTRAINT `fk_calendar_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `crm_sync_queue`
--
ALTER TABLE `crm_sync_queue`
  ADD CONSTRAINT `fk_crm_sync_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `effective_permissions_cache`
--
ALTER TABLE `effective_permissions_cache`
  ADD CONSTRAINT `fk_cache_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cache_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cache_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_form_processing`
--
ALTER TABLE `email_form_processing`
  ADD CONSTRAINT `fk_email_processing_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD CONSTRAINT `email_queue_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `email_templates` (`id`);

--
-- Constraints for table `email_send_log`
--
ALTER TABLE `email_send_log`
  ADD CONSTRAINT `fk_email_log_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_email_log_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_email_log_smtp_config` FOREIGN KEY (`smtp_config_id`) REFERENCES `smtp_config` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_email_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_template_content`
--
ALTER TABLE `email_template_content`
  ADD CONSTRAINT `email_template_content_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `email_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_template_variables`
--
ALTER TABLE `email_template_variables`
  ADD CONSTRAINT `email_template_variables_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `email_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_trigger_rules`
--
ALTER TABLE `email_trigger_rules`
  ADD CONSTRAINT `email_trigger_rules_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `email_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `field_permissions`
--
ALTER TABLE `field_permissions`
  ADD CONSTRAINT `fk_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `fk_leads_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leads_contacts`
--
ALTER TABLE `leads_contacts`
  ADD CONSTRAINT `fk_leads_contacts_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_leads_contacts_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leads_notes`
--
ALTER TABLE `leads_notes`
  ADD CONSTRAINT `leads_notes_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leads_notes_ibfk_2` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_contracting`
--
ALTER TABLE `lead_contracting`
  ADD CONSTRAINT `lead_contracting_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_documents`
--
ALTER TABLE `lead_documents`
  ADD CONSTRAINT `lead_documents_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_prospects`
--
ALTER TABLE `lead_prospects`
  ADD CONSTRAINT `lead_prospects_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_referrals`
--
ALTER TABLE `lead_referrals`
  ADD CONSTRAINT `lead_referrals_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_referrals_ibfk_2` FOREIGN KEY (`referral_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_structure_info`
--
ALTER TABLE `lead_structure_info`
  ADD CONSTRAINT `lead_structure_info_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_approval_requests`
--
ALTER TABLE `permission_approval_requests`
  ADD CONSTRAINT `fk_approval_approver` FOREIGN KEY (`current_approver_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_approval_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_approval_requestor` FOREIGN KEY (`requestor_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_approval_role` FOREIGN KEY (`requested_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permission_delegations`
--
ALTER TABLE `permission_delegations`
  ADD CONSTRAINT `fk_approving_user` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_delegated_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_delegating_user` FOREIGN KEY (`delegating_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_delegation_role` FOREIGN KEY (`granted_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_receiving_user` FOREIGN KEY (`receiving_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_restrictions`
--
ALTER TABLE `permission_restrictions`
  ADD CONSTRAINT `fk_restriction_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `phplist_subscribers`
--
ALTER TABLE `phplist_subscribers`
  ADD CONSTRAINT `fk_phplist_subscribers_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_phplist_subscribers_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `phplist_sync_log`
--
ALTER TABLE `phplist_sync_log`
  ADD CONSTRAINT `fk_phplist_sync_log_subscriber` FOREIGN KEY (`subscriber_id`) REFERENCES `phplist_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `record_ownership`
--
ALTER TABLE `record_ownership`
  ADD CONSTRAINT `fk_team_owner` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `fk_parent_role` FOREIGN KEY (`parent_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_roles_parent` FOREIGN KEY (`parent_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  ADD CONSTRAINT `fk_child` FOREIGN KEY (`child_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_parent` FOREIGN KEY (`parent_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_hierarchy_cache`
--
ALTER TABLE `role_hierarchy_cache`
  ADD CONSTRAINT `role_hierarchy_cache_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_hierarchy_cache_ibfk_2` FOREIGN KEY (`inherited_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_inheritance`
--
ALTER TABLE `role_inheritance`
  ADD CONSTRAINT `fk_child_role_inheritance` FOREIGN KEY (`child_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_parent_role_inheritance` FOREIGN KEY (`parent_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permission_inheritance`
--
ALTER TABLE `role_permission_inheritance`
  ADD CONSTRAINT `fk_role_perm_inheritance_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_perm_inheritance_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_perm_inheritance_source` FOREIGN KEY (`inherited_from_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `smtp_config`
--
ALTER TABLE `smtp_config`
  ADD CONSTRAINT `fk_smtp_config_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `fk_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_language` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

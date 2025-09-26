SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


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

CREATE TABLE `leads_backup_20241209` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `leads_contacts` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `relationship_type` varchar(50) DEFAULT 'primary',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `leads_notes` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `date_linked` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `leads_old` (
  `id` int(11) NOT NULL,
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
  `plans_and_pics` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `source` int(11) NOT NULL DEFAULT 1,
  `note_text` mediumtext NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `form_source` varchar(50) DEFAULT 'leads'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `pobject` varchar(15) NOT NULL,
  `pdescription` varchar(100) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `phplist_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0 COMMENT 'Whether the value is encrypted (for passwords)',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList integration configuration';

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

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `rid` int(11) NOT NULL,
  `rname` varchar(50) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `roles_permissions` (
  `rid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(250) NOT NULL,
  `rid` int(10) UNSIGNED NOT NULL,
  `email` varchar(250) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL COMMENT 'Foreign key to languages table',
  `language` int(2) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prop_id` (`lead_id`),
  ADD KEY `idx_contacts_timezone` (`timezone`),
  ADD KEY `idx_contacts_lead_id` (`lead_id`),
  ADD KEY `idx_contacts_email` (`personal_email`),
  ADD KEY `idx_contacts_phone` (`cell_phone`);

ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `crm_sync_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sync_status` (`sync_status`),
  ADD KEY `idx_next_retry` (`next_retry_at`),
  ADD KEY `idx_external_system` (`external_system`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_created_at` (`created_at`);

ALTER TABLE `email_accounts_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_address` (`email_address`),
  ADD UNIQUE KEY `idx_email_address` (`email_address`),
  ADD KEY `idx_form_type` (`form_type`),
  ADD KEY `idx_is_active` (`is_active`);

ALTER TABLE `email_form_processing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_account` (`email_account`),
  ADD KEY `idx_form_type` (`form_type`),
  ADD KEY `idx_processing_status` (`processing_status`),
  ADD KEY `idx_processed_at` (`processed_at`),
  ADD KEY `idx_sender_email` (`sender_email`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `fk_email_processing_lead_id` (`lead_id`);

ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_locale` (`locale_code`),
  ADD UNIQUE KEY `unique_filename` (`file_name`),
  ADD UNIQUE KEY `unique_iso_country` (`iso_code`,`country_code`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_default` (`is_default`);

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

ALTER TABLE `leads_backup_20241209`
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

ALTER TABLE `leads_contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_contact_relationship` (`lead_id`,`contact_id`,`relationship_type`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_contact_id` (`contact_id`),
  ADD KEY `idx_relationship_type` (`relationship_type`);

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

ALTER TABLE `leads_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_note` (`lead_id`,`note_id`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_note_id` (`note_id`);

ALTER TABLE `leads_old`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lead_contracting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_contract` (`lead_id`),
  ADD KEY `idx_contract_number` (`contract_number`),
  ADD KEY `idx_project_status` (`project_status`),
  ADD KEY `idx_project_manager` (`project_manager_id`),
  ADD KEY `idx_completion_date` (`estimated_completion_date`);

ALTER TABLE `lead_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_category` (`document_category`);

ALTER TABLE `lead_prospects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_prospect` (`lead_id`),
  ADD KEY `idx_survey_date` (`site_survey_date`),
  ADD KEY `idx_proposal_status` (`proposal_status`),
  ADD KEY `idx_follow_up_date` (`next_follow_up_date`),
  ADD KEY `idx_temperature` (`prospect_temperature`);

ALTER TABLE `lead_referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_referral` (`lead_id`),
  ADD KEY `idx_referral_contact` (`referral_contact_id`),
  ADD KEY `idx_referral_status` (`referral_status`);

ALTER TABLE `lead_structure_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lead_structure` (`lead_id`);

ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_created` (`date_created`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_contact_id` (`contact_id`);

ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pid` (`pid`) USING BTREE,
  ADD KEY `pobject` (`pobject`) USING BTREE;

ALTER TABLE `phplist_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

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

ALTER TABLE `phplist_sync_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_sync_type` (`sync_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rname` (`rname`),
  ADD UNIQUE KEY `rid` (`rid`) USING BTREE;

ALTER TABLE `roles_permissions`
  ADD PRIMARY KEY (`rid`,`pid`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_language` (`language_id`);


ALTER TABLE `audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `crm_sync_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `email_accounts_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `email_form_processing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `leads_backup_20241209`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `leads_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `leads_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `leads_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `leads_old`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lead_contracting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lead_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lead_prospects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lead_referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lead_structure_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `phplist_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `phplist_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `phplist_sync_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `crm_sync_queue`
  ADD CONSTRAINT `fk_crm_sync_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

ALTER TABLE `email_form_processing`
  ADD CONSTRAINT `fk_email_processing_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

ALTER TABLE `leads`
  ADD CONSTRAINT `fk_leads_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

ALTER TABLE `leads_contacts`
  ADD CONSTRAINT `fk_leads_contacts_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_leads_contacts_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

ALTER TABLE `leads_notes`
  ADD CONSTRAINT `leads_notes_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leads_notes_ibfk_2` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE;

ALTER TABLE `lead_contracting`
  ADD CONSTRAINT `lead_contracting_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

ALTER TABLE `lead_documents`
  ADD CONSTRAINT `lead_documents_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

ALTER TABLE `lead_prospects`
  ADD CONSTRAINT `lead_prospects_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

ALTER TABLE `lead_referrals`
  ADD CONSTRAINT `lead_referrals_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_referrals_ibfk_2` FOREIGN KEY (`referral_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

ALTER TABLE `lead_structure_info`
  ADD CONSTRAINT `lead_structure_info_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

ALTER TABLE `phplist_subscribers`
  ADD CONSTRAINT `fk_phplist_subscribers_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_phplist_subscribers_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

ALTER TABLE `phplist_sync_log`
  ADD CONSTRAINT `fk_phplist_sync_log_subscriber` FOREIGN KEY (`subscriber_id`) REFERENCES `phplist_subscribers` (`id`) ON DELETE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_language` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

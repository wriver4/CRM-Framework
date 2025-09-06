-- Email Processing System Tables Migration - SAFE VERSION
-- Adds tables for email form processing and CRM sync functionality
-- Compatible with existing democrm_democrm database structure
-- This version avoids foreign key constraint conflicts
-- ========================================
-- 1. EMAIL PROCESSING LOG TABLE
-- ========================================
CREATE TABLE
  IF NOT EXISTS `email_form_processing` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `email_account` varchar(255) NOT NULL COMMENT 'Email address that received the form',
    `form_type` enum ('estimate', 'ltr', 'contact') NOT NULL COMMENT 'Type of form processed',
    `message_id` varchar(255) DEFAULT NULL COMMENT 'Email message ID for duplicate detection',
    `subject` varchar(500) DEFAULT NULL COMMENT 'Email subject line',
    `sender_email` varchar(255) DEFAULT NULL COMMENT 'Email address of form sender',
    `received_at` timestamp NULL DEFAULT NULL COMMENT 'When email was received',
    `processed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When processing occurred',
    `processing_status` enum ('success', 'failed', 'skipped', 'duplicate') DEFAULT 'success' COMMENT 'Processing result',
    `lead_id` int (11) DEFAULT NULL COMMENT 'Created/updated lead ID',
    `raw_email_content` text COMMENT 'Original email content for debugging',
    `parsed_form_data` json DEFAULT NULL COMMENT 'Extracted form data as JSON',
    `error_message` text COMMENT 'Error details if processing failed',
    PRIMARY KEY (`id`),
    KEY `idx_email_account` (`email_account`),
    KEY `idx_form_type` (`form_type`),
    KEY `idx_processing_status` (`processing_status`),
    KEY `idx_processed_at` (`processed_at`),
    KEY `idx_sender_email` (`sender_email`),
    KEY `idx_message_id` (`message_id`),
    KEY `idx_lead_id` (`lead_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'Log of email form processing activities';

-- ========================================
-- 2. CRM SYNC QUEUE TABLE
-- ========================================
CREATE TABLE
  IF NOT EXISTS `crm_sync_queue` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL COMMENT 'Lead to sync to external CRM',
    `sync_action` enum ('create', 'update', 'note_add') NOT NULL COMMENT 'Type of sync action',
    `external_system` enum ('hubspot', 'salesforce', 'mailchimp', 'custom') DEFAULT 'custom' COMMENT 'Target CRM system',
    `sync_status` enum ('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending' COMMENT 'Current sync status',
    `retry_count` int (11) DEFAULT 0 COMMENT 'Number of retry attempts',
    `max_retries` int (11) DEFAULT 3 COMMENT 'Maximum retry attempts',
    `next_retry_at` timestamp NULL DEFAULT NULL COMMENT 'When to retry if failed',
    `last_error` text COMMENT 'Last error message',
    `external_id` varchar(255) DEFAULT NULL COMMENT 'ID in external system',
    `sync_data` json DEFAULT NULL COMMENT 'Data to sync as JSON',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When sync was queued',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
    PRIMARY KEY (`id`),
    KEY `idx_sync_status` (`sync_status`),
    KEY `idx_next_retry` (`next_retry_at`),
    KEY `idx_external_system` (`external_system`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_created_at` (`created_at`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'Queue for syncing leads to external CRM systems';

-- ========================================
-- 3. EMAIL ACCOUNTS CONFIGURATION TABLE
-- ========================================
CREATE TABLE
  IF NOT EXISTS `email_accounts_config` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `email_address` varchar(255) NOT NULL UNIQUE COMMENT 'Email address to monitor',
    `form_type` enum ('estimate', 'ltr', 'contact') NOT NULL COMMENT 'Type of forms this email receives',
    `imap_host` varchar(255) NOT NULL COMMENT 'IMAP server hostname',
    `imap_port` int (11) DEFAULT 993 COMMENT 'IMAP server port',
    `imap_encryption` enum ('ssl', 'tls', 'none') DEFAULT 'ssl' COMMENT 'IMAP encryption type',
    `username` varchar(255) NOT NULL COMMENT 'IMAP username',
    `password` varchar(500) NOT NULL COMMENT 'IMAP password (encrypted)',
    `is_active` tinyint (1) DEFAULT 1 COMMENT 'Whether to process this account',
    `last_check` timestamp NULL DEFAULT NULL COMMENT 'Last time emails were checked',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When account was added',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_email_address` (`email_address`),
    KEY `idx_form_type` (`form_type`),
    KEY `idx_is_active` (`is_active`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'Configuration for email accounts to monitor';

-- ========================================
-- 4. INSERT DEFAULT EMAIL CONFIGURATIONS
-- ========================================
-- Insert default email account configurations
-- Passwords should be updated with actual encrypted values after installation
INSERT IGNORE INTO `email_accounts_config` (
  `email_address`,
  `form_type`,
  `imap_host`,
  `username`,
  `password`
)
VALUES
  (
    'estimates@waveguardco.com',
    'estimate',
    'mail.waveguardco.com',
    'estimates@waveguardco.com',
    'base64_encoded_password_here'
  ),
  (
    'ltr@waveguardco.com',
    'ltr',
    'mail.waveguardco.com',
    'ltr@waveguardco.com',
    'base64_encoded_password_here'
  ),
  (
    'contact@waveguardco.com',
    'contact',
    'mail.waveguardco.com',
    'contact@waveguardco.com',
    'base64_encoded_password_here'
  );

-- ========================================
-- 5. ADD FOREIGN KEY CONSTRAINTS (OPTIONAL)
-- ========================================
-- These will be added manually after verifying the leads table structure
-- Uncomment and run these separately if you want foreign key constraints:
-- ALTER TABLE `email_form_processing` 
-- ADD CONSTRAINT `fk_email_processing_lead_id` 
-- FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `crm_sync_queue` 
-- ADD CONSTRAINT `fk_crm_sync_lead_id` 
-- FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;
-- ========================================
-- 6. VERIFICATION QUERIES
-- ========================================
-- Show created tables
SHOW TABLES LIKE '%email%';

SHOW TABLES LIKE '%crm_sync%';

-- Show table structures
DESCRIBE `email_form_processing`;

DESCRIBE `crm_sync_queue`;

DESCRIBE `email_accounts_config`;

-- Show default configurations
SELECT
  *
FROM
  `email_accounts_config`;

-- ========================================
-- 7. CLEANUP QUERIES (for testing/rollback)
-- ========================================
-- Uncomment these lines to remove the tables if needed:
-- DROP TABLE IF EXISTS `email_form_processing`;
-- DROP TABLE IF EXISTS `crm_sync_queue`;
-- DROP TABLE IF EXISTS `email_accounts_config`;
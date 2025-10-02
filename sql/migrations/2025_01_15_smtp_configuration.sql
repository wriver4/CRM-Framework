-- Migration: SMTP Configuration for Outgoing Emails
-- Date: 2025-01-15
-- Description: Create tables for SMTP configuration and email sending logs
-- Table for SMTP server configurations per user
CREATE TABLE
  IF NOT EXISTS `smtp_config` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `user_id` int (11) DEFAULT NULL COMMENT 'User ID (NULL = default for all users)',
    `config_name` varchar(100) NOT NULL COMMENT 'Friendly name for this configuration',
    `smtp_host` varchar(255) NOT NULL COMMENT 'SMTP server hostname',
    `smtp_port` int (11) NOT NULL DEFAULT 587 COMMENT 'SMTP port (587 for TLS, 465 for SSL)',
    `smtp_encryption` enum ('tls', 'ssl') NOT NULL DEFAULT 'tls' COMMENT 'Encryption type',
    `smtp_username` varchar(255) NOT NULL COMMENT 'SMTP authentication username',
    `smtp_password` text NOT NULL COMMENT 'Encrypted SMTP password',
    `from_email` varchar(255) NOT NULL COMMENT 'From email address',
    `from_name` varchar(255) NOT NULL COMMENT 'From name',
    `reply_to_email` varchar(255) DEFAULT NULL COMMENT 'Reply-to email address',
    `is_default` tinyint (1) NOT NULL DEFAULT 0 COMMENT 'Is this the default config for the user',
    `is_active` tinyint (1) NOT NULL DEFAULT 1 COMMENT 'Is this configuration active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_default` (`is_default`),
    KEY `idx_is_active` (`is_active`),
    CONSTRAINT `fk_smtp_config_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'SMTP server configurations for sending emails';

-- Table for email sending logs
CREATE TABLE
  IF NOT EXISTS `email_send_log` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `smtp_config_id` int (11) DEFAULT NULL COMMENT 'SMTP config used',
    `lead_id` int (11) DEFAULT NULL COMMENT 'Related lead ID',
    `contact_id` int (11) DEFAULT NULL COMMENT 'Related contact ID',
    `user_id` int (11) DEFAULT NULL COMMENT 'User who triggered the email',
    `email_type` varchar(50) NOT NULL COMMENT 'Type of email (lead_thank_you, notification, etc)',
    `lead_source_id` int (11) DEFAULT NULL COMMENT 'Lead source type (1-6)',
    `recipient_email` varchar(255) NOT NULL COMMENT 'Recipient email address',
    `recipient_name` varchar(255) DEFAULT NULL COMMENT 'Recipient name',
    `subject` varchar(500) NOT NULL COMMENT 'Email subject',
    `body_html` longtext DEFAULT NULL COMMENT 'HTML email body',
    `body_text` longtext DEFAULT NULL COMMENT 'Plain text email body',
    `status` enum ('pending', 'sent', 'failed', 'bounced') NOT NULL DEFAULT 'pending' COMMENT 'Email status',
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
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_email_log_smtp_config` FOREIGN KEY (`smtp_config_id`) REFERENCES `smtp_config` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_email_log_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_email_log_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_email_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'Log of all emails sent from the system';

-- Insert default SMTP configuration (to be updated with real credentials)
INSERT INTO
  `smtp_config` (
    `user_id`,
    `config_name`,
    `smtp_host`,
    `smtp_port`,
    `smtp_encryption`,
    `smtp_username`,
    `smtp_password`,
    `from_email`,
    `from_name`,
    `is_default`,
    `is_active`
  )
VALUES
  (
    NULL,
    'Default SMTP Server',
    'smtp.example.com',
    587,
    'tls',
    'noreply@example.com',
    '',
    'noreply@example.com',
    'Demo CRM',
    1,
    0
  );
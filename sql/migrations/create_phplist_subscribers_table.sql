-- Create separate phpList subscribers table for better data organization
-- This keeps the leads table clean and follows proper normalization
-- FIXED VERSION: Create tables first, then add foreign keys separately

-- Create phpList subscribers table (WITHOUT foreign keys initially)
CREATE TABLE IF NOT EXISTS `phplist_subscribers` (
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
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lead_email` (`lead_id`, `email`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_email` (`email`),
  KEY `idx_sync_status` (`sync_status`),
  KEY `idx_phplist_subscriber_id` (`phplist_subscriber_id`),
  KEY `idx_last_sync_attempt` (`last_sync_attempt`),
  KEY `idx_sync_pending` (`sync_status`, `sync_attempts`, `last_sync_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList subscriber management and sync tracking';

-- Create phpList configuration table
CREATE TABLE IF NOT EXISTS `phplist_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0 COMMENT 'Whether the value is encrypted (for passwords)',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList integration configuration';

-- Create phpList sync log table (WITHOUT foreign keys initially)
CREATE TABLE IF NOT EXISTS `phplist_sync_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) DEFAULT NULL COMMENT 'Reference to phplist_subscribers table',
  `sync_type` enum('create','update','delete','bulk_sync') NOT NULL COMMENT 'Type of sync operation',
  `status` enum('success','error','warning') NOT NULL COMMENT 'Sync result status',
  `phplist_response` text DEFAULT NULL COMMENT 'Response from phpList API',
  `error_details` text DEFAULT NULL COMMENT 'Detailed error information',
  `processing_time_ms` int(11) DEFAULT NULL COMMENT 'Processing time in milliseconds',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subscriber_id` (`subscriber_id`),
  KEY `idx_sync_type` (`sync_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='phpList sync operation logging';

-- Insert default phpList configuration (only if not exists)
INSERT IGNORE INTO `phplist_config` (
  `config_key`,
  `config_value`,
  `description`,
  `is_encrypted`
) VALUES
  (
    'phplist_api_url',
    'https://your-phplist-domain.com/lists/admin/',
    'phpList admin URL',
    0
  ),
  (
    'phplist_api_username',
    '',
    'phpList API username',
    0
  ),
  (
    'phplist_api_password',
    '',
    'phpList API password (encrypted)',
    1
  ),
  (
    'phplist_default_list_id',
    '1',
    'Default list ID for new subscribers',
    0
  ),
  (
    'phplist_geographic_lists',
    '{"US-CA": 2, "US-TX": 3, "US-CO": 4, "US-WA": 5, "US-UT": 6, "US-MT": 7}',
    'JSON mapping of states to list IDs',
    0
  ),
  (
    'phplist_service_lists',
    '{"1": 10, "2": 11}',
    'JSON mapping of service types to list IDs',
    0
  ),
  (
    'phplist_source_lists',
    '{"Internet search": 20, "Referral": 21, "Insurance provider": 22, "google": 23}',
    'JSON mapping of lead sources to list IDs',
    0
  ),
  (
    'sync_enabled',
    '1',
    'Enable/disable phpList sync (1=enabled, 0=disabled)',
    0
  ),
  (
    'sync_frequency_minutes',
    '15',
    'How often to run sync cron job (in minutes)',
    0
  ),
  (
    'max_sync_attempts',
    '3',
    'Maximum sync attempts before marking as failed',
    0
  ),
  (
    'batch_size',
    '50',
    'Number of records to process per sync batch',
    0
  ),
  (
    'api_timeout_seconds',
    '30',
    'API request timeout in seconds',
    0
  ),
  (
    'debug_mode',
    '0',
    'Enable debug logging (1=enabled, 0=disabled)',
    0
  ),
  (
    'auto_create_lists',
    '0',
    'Automatically create missing lists in phpList (1=enabled, 0=disabled)',
    0
  );

-- Now add foreign key constraints AFTER tables are created
-- This is done separately to avoid constraint errors

-- Add foreign key for phplist_subscribers -> leads
-- First check if the constraint doesn't already exist
SET @constraint_exists = (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS 
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'phplist_subscribers'
    AND CONSTRAINT_NAME = 'fk_phplist_subscribers_lead'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql = IF(@constraint_exists = 0,
  'ALTER TABLE phplist_subscribers ADD CONSTRAINT fk_phplist_subscribers_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE',
  'SELECT "Foreign key fk_phplist_subscribers_lead already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for phplist_subscribers -> contacts
SET @constraint_exists = (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS 
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'phplist_subscribers'
    AND CONSTRAINT_NAME = 'fk_phplist_subscribers_contact'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql = IF(@constraint_exists = 0,
  'ALTER TABLE phplist_subscribers ADD CONSTRAINT fk_phplist_subscribers_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL',
  'SELECT "Foreign key fk_phplist_subscribers_contact already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for phplist_sync_log -> phplist_subscribers
SET @constraint_exists = (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS 
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'phplist_sync_log'
    AND CONSTRAINT_NAME = 'fk_phplist_sync_log_subscriber'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql = IF(@constraint_exists = 0,
  'ALTER TABLE phplist_sync_log ADD CONSTRAINT fk_phplist_sync_log_subscriber FOREIGN KEY (subscriber_id) REFERENCES phplist_subscribers(id) ON DELETE CASCADE',
  'SELECT "Foreign key fk_phplist_sync_log_subscriber already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
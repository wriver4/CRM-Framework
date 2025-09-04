-- Add phpList integration fields to leads table
-- Run this migration to add phpList tracking capabilities
ALTER TABLE `leads`
ADD COLUMN `phplist_subscriber_id` INT (11) NULL DEFAULT NULL COMMENT 'phpList subscriber ID after sync',
ADD COLUMN `phplist_sync_status` ENUM ('pending', 'synced', 'failed', 'skipped') DEFAULT 'pending' COMMENT 'Sync status with phpList',
ADD COLUMN `phplist_sync_date` DATETIME NULL DEFAULT NULL COMMENT 'Last sync attempt date',
ADD COLUMN `phplist_lists` TEXT NULL DEFAULT NULL COMMENT 'Comma-separated list IDs for segmentation',
ADD COLUMN `phplist_error_message` TEXT NULL DEFAULT NULL COMMENT 'Error message if sync failed';

-- Add index for efficient cron job queries
CREATE INDEX `idx_phplist_sync_status` ON `leads` (`phplist_sync_status`);

CREATE INDEX `idx_phplist_sync_date` ON `leads` (`phplist_sync_date`);

CREATE INDEX `idx_get_updates` ON `leads` (`get_updates`);

-- Create phpList configuration table
CREATE TABLE
  `phplist_config` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `config_key` varchar(100) NOT NULL,
    `config_value` text NOT NULL,
    `description` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `config_key` (`config_key`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Insert default phpList configuration
INSERT INTO
  `phplist_config` (`config_key`, `config_value`, `description`)
VALUES
  (
    'phplist_api_url',
    'https://your-phplist-domain.com/lists/admin/',
    'phpList admin URL'
  ),
  (
    'phplist_api_username',
    '',
    'phpList API username'
  ),
  (
    'phplist_api_password',
    '',
    'phpList API password'
  ),
  (
    'phplist_default_list_id',
    '1',
    'Default list ID for new subscribers'
  ),
  (
    'phplist_geographic_lists',
    '{"US-CA": 2, "US-TX": 3, "US-CO": 4}',
    'JSON mapping of states to list IDs'
  ),
  (
    'phplist_service_lists',
    '{"1": 5, "2": 6}',
    'JSON mapping of services to list IDs'
  ),
  (
    'phplist_source_lists',
    '{"Internet search": 7, "Referral": 8}',
    'JSON mapping of lead sources to list IDs'
  ),
  (
    'sync_enabled',
    '1',
    'Enable/disable phpList sync'
  ),
  (
    'sync_frequency_minutes',
    '15',
    'How often to run sync (in minutes)'
  ),
  (
    'max_sync_attempts',
    '3',
    'Maximum sync attempts before marking as failed'
  );
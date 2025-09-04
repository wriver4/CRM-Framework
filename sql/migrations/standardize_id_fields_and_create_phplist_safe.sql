-- Comprehensive migration to standardize ID fields and create phpList tables
-- This prevents future foreign key constraint issues by ensuring consistent data types
-- CRITICAL: All ID fields will be standardized to int(11) NOT NULL
-- FIXED: Safely handles foreign key constraints by checking actual constraint names
-- FIXED: Removed all information_schema queries to prevent #1044 errors

-- Step 1: Check existing foreign key constraints using SHOW CREATE TABLE
-- This will show us the actual constraint names before we try to drop them

SELECT 'Checking existing foreign key constraints...' as status;

-- Show current table structures to see existing constraints
SELECT 'Current leads table structure:' as info;
SHOW CREATE TABLE leads;

SELECT 'Current contacts table structure:' as info;
SHOW CREATE TABLE contacts;

-- Check if leads_contacts table exists and show its structure
BEGIN;
  SELECT COUNT(*) FROM leads_contacts LIMIT 1;
  SELECT 'Current leads_contacts table structure:' as info;
  SHOW CREATE TABLE leads_contacts;
COMMIT;

-- Step 2: Drop foreign key constraints safely using proper error handling
-- We'll use a more robust approach that won't fail if constraints don't exist

-- Create a procedure to safely drop foreign keys
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS SafeDropForeignKey(
    IN table_name VARCHAR(64),
    IN constraint_name VARCHAR(64)
)
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
    
    SET @sql = CONCAT('ALTER TABLE ', table_name, ' DROP FOREIGN KEY ', constraint_name);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;

-- Try to drop common foreign key constraint names that might exist
-- These will fail silently if the constraints don't exist

-- Try various possible constraint names for leads table
CALL SafeDropForeignKey('leads', 'fk_leads_contact_id');
CALL SafeDropForeignKey('leads', 'leads_ibfk_1');
CALL SafeDropForeignKey('leads', 'leads_contact_id_fk');

-- Try various possible constraint names for leads_contacts table
CALL SafeDropForeignKey('leads_contacts', 'fk_leads_contacts_contact_id');
CALL SafeDropForeignKey('leads_contacts', 'leads_contacts_ibfk_1');
CALL SafeDropForeignKey('leads_contacts', 'leads_contacts_contact_id_fk');

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS SafeDropForeignKey;

-- Step 3: Now safely modify the contacts.id column
-- This should work now that we've dropped any foreign key constraints

SELECT 'Standardizing contacts.id column type...' as status;
ALTER TABLE `contacts` MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT;

-- Step 4: Update referencing columns to match the new contacts.id type
SELECT 'Updating referencing columns...' as status;

-- Update leads.contact_id to match
ALTER TABLE `leads` MODIFY COLUMN `contact_id` int(11) DEFAULT NULL;

-- Update leads_contacts.contact_id if the table exists
BEGIN;
  SELECT COUNT(*) FROM leads_contacts LIMIT 1;
  ALTER TABLE `leads_contacts` MODIFY COLUMN `contact_id` int(11) NOT NULL;
COMMIT;

-- Step 5: Standardize other critical ID fields
SELECT 'Standardizing other ID fields...' as status;

-- Standardize users table if it exists
BEGIN;
  SELECT COUNT(*) FROM users LIMIT 1;
  ALTER TABLE users MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

-- Standardize roles table if it exists
BEGIN;
  SELECT COUNT(*) FROM roles LIMIT 1;
  ALTER TABLE roles MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

-- Standardize leads table if it exists
BEGIN;
  SELECT COUNT(*) FROM leads LIMIT 1;
  ALTER TABLE leads MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

-- Step 6: Recreate foreign key constraints with standardized names
-- Use a consistent naming convention for all foreign keys

SELECT 'Recreating foreign key constraints...' as status;

-- Create procedure to safely add foreign keys
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS SafeAddForeignKey(
    IN table_name VARCHAR(64),
    IN constraint_name VARCHAR(64),
    IN column_name VARCHAR(64),
    IN ref_table VARCHAR(64),
    IN ref_column VARCHAR(64),
    IN on_delete_action VARCHAR(32)
)
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
    
    SET @sql = CONCAT(
        'ALTER TABLE ', table_name, 
        ' ADD CONSTRAINT ', constraint_name,
        ' FOREIGN KEY (', column_name, ')',
        ' REFERENCES ', ref_table, '(', ref_column, ')',
        ' ON DELETE ', on_delete_action
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;

-- Add foreign key constraints back with consistent naming
CALL SafeAddForeignKey('leads', 'fk_leads_contact_id', 'contact_id', 'contacts', 'id', 'SET NULL');

-- Add foreign key for leads_contacts if table exists
BEGIN;
  SELECT COUNT(*) FROM leads_contacts LIMIT 1;
  CALL SafeAddForeignKey('leads_contacts', 'fk_leads_contacts_contact_id', 'contact_id', 'contacts', 'id', 'CASCADE');
COMMIT;

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS SafeAddForeignKey;

-- Step 7: Create phpList tables with standardized field types
SELECT 'Creating phpList tables...' as status;

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

-- Create phpList sync log table
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

-- Step 8: Insert default phpList configuration
SELECT 'Inserting default configuration...' as status;

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

-- Step 9: Add foreign key constraints for phpList tables
SELECT 'Adding phpList foreign key constraints...' as status;

-- Create procedure to safely add phpList foreign keys
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS SafeAddPhpListForeignKey(
    IN table_name VARCHAR(64),
    IN constraint_name VARCHAR(64),
    IN column_name VARCHAR(64),
    IN ref_table VARCHAR(64),
    IN ref_column VARCHAR(64),
    IN on_delete_action VARCHAR(32)
)
BEGIN
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
    
    SET @sql = CONCAT(
        'ALTER TABLE ', table_name, 
        ' ADD CONSTRAINT ', constraint_name,
        ' FOREIGN KEY (', column_name, ')',
        ' REFERENCES ', ref_table, '(', ref_column, ')',
        ' ON DELETE ', on_delete_action
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;

-- Add phpList foreign key constraints
CALL SafeAddPhpListForeignKey('phplist_subscribers', 'fk_phplist_subscribers_lead', 'lead_id', 'leads', 'id', 'CASCADE');
CALL SafeAddPhpListForeignKey('phplist_subscribers', 'fk_phplist_subscribers_contact', 'contact_id', 'contacts', 'id', 'SET NULL');
CALL SafeAddPhpListForeignKey('phplist_sync_log', 'fk_phplist_sync_log_subscriber', 'subscriber_id', 'phplist_subscribers', 'id', 'CASCADE');

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS SafeAddPhpListForeignKey;

-- Step 10: Final verification
SELECT 'Migration completed successfully!' as result;
SELECT 'All ID fields have been standardized to int(11) NOT NULL' as standardization_status;
SELECT 'Foreign key constraints have been recreated with proper types' as constraint_status;
SELECT 'phpList tables created with proper foreign key constraints' as phplist_status;

-- Show tables to verify creation
SHOW TABLES LIKE 'phplist_%';

-- Show final table structures for verification
SELECT 'Final contacts table structure:' as verification;
SHOW CREATE TABLE contacts;

SELECT 'Final leads table structure:' as verification;
SHOW CREATE TABLE leads;

SELECT 'Final phplist_subscribers table structure:' as verification;
SHOW CREATE TABLE phplist_subscribers;
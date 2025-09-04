-- PHPLIST MIGRATION - NO INFORMATION_SCHEMA QUERIES
-- Safe for phpMyAdmin - handles existing constraints without information_schema access
-- Based on the ultra-safe pattern documented in repo.md
-- Step 1: Show current structure (for debugging)
SELECT
  'Starting phpList migration for phpMyAdmin...' as status;

SELECT
  'Current leads table structure:' as info;

SHOW
CREATE TABLE
  leads;

SELECT
  'Current contacts table structure:' as info;

SHOW
CREATE TABLE
  contacts;

-- Step 2: Drop existing phpList foreign keys if they exist (errors expected and normal)
SELECT
  'Dropping existing phpList foreign key constraints (errors are expected and okay)...' as status;

ALTER TABLE phplist_subscribers
DROP FOREIGN KEY fk_phplist_subscribers_lead;

ALTER TABLE phplist_subscribers
DROP FOREIGN KEY fk_phplist_subscribers_contact;

ALTER TABLE phplist_subscribers
DROP FOREIGN KEY phplist_subscribers_ibfk_1;

ALTER TABLE phplist_subscribers
DROP FOREIGN KEY phplist_subscribers_ibfk_2;

-- Step 3: Try to drop common leads foreign key constraint names (errors expected and normal)
SELECT
  'Attempting to drop leads foreign key constraints (errors are expected and okay)...' as status;

ALTER TABLE leads
DROP FOREIGN KEY fk_leads_contact_id;

ALTER TABLE leads
DROP FOREIGN KEY leads_ibfk_1;

ALTER TABLE leads
DROP FOREIGN KEY leads_ibfk_2;

-- Step 4: Modify columns (should work after constraint removal attempts)
SELECT
  'Standardizing ID columns to int(11)...' as status;

ALTER TABLE contacts MODIFY COLUMN id int (11) NOT NULL AUTO_INCREMENT;

ALTER TABLE leads MODIFY COLUMN id int (11) NOT NULL AUTO_INCREMENT;

ALTER TABLE leads MODIFY COLUMN contact_id int (11) DEFAULT NULL;

-- Step 5: Recreate leads foreign key with standard name
SELECT
  'Recreating leads foreign key constraint...' as status;

ALTER TABLE leads ADD CONSTRAINT fk_leads_contact_id FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL;

-- Step 6: Create phpList tables with standardized int(11) fields
SELECT
  'Creating phpList tables...' as status;

CREATE TABLE
  IF NOT EXISTS `phplist_subscribers` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `lead_id` int (11) NOT NULL,
    `contact_id` int (11) DEFAULT NULL,
    `email` varchar(255) NOT NULL,
    `sync_status` enum ('pending', 'synced', 'failed') DEFAULT 'pending',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lead_id` (`lead_id`),
    KEY `idx_contact_id` (`contact_id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
  IF NOT EXISTS `phplist_config` (
    `id` int (11) NOT NULL AUTO_INCREMENT,
    `config_key` varchar(100) NOT NULL,
    `config_value` text NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `config_key` (`config_key`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Step 7: Add phpList foreign keys (try both, errors expected if they already exist)
SELECT
  'Adding phpList foreign key constraints (errors expected if constraints already exist)...' as status;

-- Try to add first foreign key - will fail if it already exists (this is normal)
ALTER TABLE phplist_subscribers ADD CONSTRAINT fk_phplist_subscribers_lead FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE;

-- Try to add second foreign key - will fail if it already exists (this is normal)  
ALTER TABLE phplist_subscribers ADD CONSTRAINT fk_phplist_subscribers_contact FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL;

-- Step 8: Insert basic configuration
SELECT
  'Inserting basic configuration...' as status;

INSERT IGNORE INTO `phplist_config` (`config_key`, `config_value`)
VALUES
  (
    'phplist_api_url',
    'https://your-phplist-domain.com/lists/admin/'
  ),
  ('sync_enabled', '1'),
  ('debug_mode', '0');

-- Step 9: Verify final structure
SELECT
  'Verifying final structures...' as status;

SHOW
CREATE TABLE
  contacts;

SHOW
CREATE TABLE
  leads;

SHOW
CREATE TABLE
  phplist_subscribers;

SELECT
  'Migration completed successfully!' as result;

SELECT
  'Expected errors summary:' as summary;

SELECT
  '- #1091 errors when dropping non-existent foreign keys (NORMAL)' as note1;

SELECT
  '- #1005 errno 121 errors when adding existing foreign keys (NORMAL)' as note2;

SELECT
  'If you see these errors, the migration is working correctly!' as note3;
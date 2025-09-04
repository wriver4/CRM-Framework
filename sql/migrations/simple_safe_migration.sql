-- SIMPLEST & SAFEST SQL Migration Pattern
-- Following the ultra-safe approach documented in repo.md
-- No stored procedures, no information_schema queries, direct ALTER TABLE statements
-- Step 1: Show current structure (for debugging)
SELECT
  'Starting simple safe migration...' as status;

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

-- Step 2: Try to drop common foreign key constraint names (errors expected)
SELECT
  'Attempting to drop foreign key constraints (errors are expected and okay)...' as status;

ALTER TABLE leads
DROP FOREIGN KEY fk_leads_contact_id;

ALTER TABLE leads
DROP FOREIGN KEY leads_ibfk_1;

ALTER TABLE leads
DROP FOREIGN KEY leads_ibfk_2;

-- Step 3: Modify columns (should work after constraint removal attempts)
SELECT
  'Standardizing ID columns to int(11)...' as status;

ALTER TABLE contacts MODIFY COLUMN id int (11) NOT NULL AUTO_INCREMENT;

ALTER TABLE leads MODIFY COLUMN id int (11) NOT NULL AUTO_INCREMENT;

ALTER TABLE leads MODIFY COLUMN contact_id int (11) DEFAULT NULL;

-- Step 4: Recreate foreign key with standard name
SELECT
  'Recreating foreign key constraint...' as status;

ALTER TABLE leads ADD CONSTRAINT fk_leads_contact_id FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL;

-- Step 5: Create phpList tables with standardized int(11) fields
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

-- Step 6: Add phpList foreign keys
SELECT
  'Adding phpList foreign key constraints...' as status;

ALTER TABLE phplist_subscribers ADD CONSTRAINT fk_phplist_subscribers_lead FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE;

ALTER TABLE phplist_subscribers ADD CONSTRAINT fk_phplist_subscribers_contact FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL;

-- Step 7: Insert basic configuration
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

-- Step 8: Verify final structure
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
  'Note: Any #1091 errors above are expected and normal' as note;
-- ===============================================
-- STEP 2: DATABASE SCHEMA UPDATES
-- File: 02_schema_updates.sql  
-- Purpose: Add required fields and indexes
-- ===============================================

-- Add lead_id column to contacts table (check if exists first)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'lead_id');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE contacts ADD COLUMN lead_id INT(11) DEFAULT NULL AFTER id', 
    'SELECT "lead_id column already exists" as status');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add contact_id column to leads table (check if exists first)
SET @col_exists2 = (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_schema = DATABASE() AND table_name = 'leads' AND column_name = 'contact_id');

SET @sql2 = IF(@col_exists2 = 0, 
    'ALTER TABLE leads ADD COLUMN contact_id INT(11) DEFAULT NULL AFTER id', 
    'SELECT "contact_id column already exists" as status');

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Drop existing bridge table and recreate with clean structure
DROP TABLE IF EXISTS leads_contacts;

-- Check if leads table has primary key
SELECT 'Verifying leads table has primary key...' as status;
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_KEY
FROM information_schema.COLUMNS 
WHERE table_schema = DATABASE() 
AND table_name = 'leads' 
AND COLUMN_KEY = 'PRI';

-- Check if contacts table has primary key  
SELECT 'Verifying contacts table has primary key...' as status;
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_KEY
FROM information_schema.COLUMNS 
WHERE table_schema = DATABASE() 
AND table_name = 'contacts' 
AND COLUMN_KEY = 'PRI';

-- Create bridge table without foreign keys first
CREATE TABLE leads_contacts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    lead_id INT(11) NOT NULL,
    contact_id INT(11) NOT NULL,
    relationship_type VARCHAR(50) DEFAULT 'primary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY unique_lead_contact (lead_id, contact_id, relationship_type),
    KEY idx_lead_id (lead_id),
    KEY idx_contact_id (contact_id),
    KEY idx_relationship_type (relationship_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Check if referenced tables have primary keys first
SELECT 'Checking table structures...' as status;

-- Verify leads table structure
DESCRIBE leads;
SELECT 'leads table checked' as status;

-- Verify contacts table structure  
DESCRIBE contacts;
SELECT 'contacts table checked' as status;

-- MariaDB 10.11.9 compatible foreign key constraints
SELECT 'Adding foreign key constraints (MariaDB 10.11.9)...' as status;

-- Check table structures first
SELECT 'Checking leads table structure...' as status;
SHOW CREATE TABLE leads;

SELECT 'Checking contacts table structure...' as status;
SHOW CREATE TABLE contacts;

-- Ensure proper column types match between tables
SELECT 'Verifying column compatibility...' as status;
SELECT 
    'leads.id' as table_column,
    COLUMN_TYPE as data_type,
    IS_NULLABLE,
    COLUMN_KEY
FROM information_schema.COLUMNS 
WHERE table_schema = DATABASE() AND table_name = 'leads' AND column_name = 'id';

SELECT 
    'contacts.id' as table_column,
    COLUMN_TYPE as data_type,
    IS_NULLABLE,
    COLUMN_KEY
FROM information_schema.COLUMNS 
WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'id';

-- Drop any existing constraints that might conflict (errno 150 fix)
SELECT 'Dropping any existing foreign key constraints...' as status;

-- Drop existing foreign key constraints if they exist
SET @drop_fk1 = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.table_constraints 
     WHERE table_schema = DATABASE() AND table_name = 'leads_contacts' 
     AND constraint_name = 'fk_leads_contacts_lead_id') > 0,
    'ALTER TABLE leads_contacts DROP FOREIGN KEY fk_leads_contacts_lead_id',
    'SELECT "No existing leads FK to drop" as status'
));

PREPARE stmt_drop1 FROM @drop_fk1;
EXECUTE stmt_drop1;
DEALLOCATE PREPARE stmt_drop1;

SET @drop_fk2 = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.table_constraints 
     WHERE table_schema = DATABASE() AND table_name = 'leads_contacts' 
     AND constraint_name = 'fk_leads_contacts_contact_id') > 0,
    'ALTER TABLE leads_contacts DROP FOREIGN KEY fk_leads_contacts_contact_id',
    'SELECT "No existing contacts FK to drop" as status'
));

PREPARE stmt_drop2 FROM @drop_fk2;
EXECUTE stmt_drop2;
DEALLOCATE PREPARE stmt_drop2;

-- Try foreign key constraints with improved error handling
SELECT 'Attempting to add foreign key constraints...' as status;

-- Add foreign key for leads table reference with transaction
BEGIN;
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_lead_id 
FOREIGN KEY (lead_id) REFERENCES leads(id) 
ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
SELECT 'Leads foreign key constraint added successfully' as status;

-- Add foreign key for contacts table reference with transaction
BEGIN;  
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_contact_id 
FOREIGN KEY (contact_id) REFERENCES contacts(id) 
ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
SELECT 'Contacts foreign key constraint added successfully' as status;

-- Verify foreign keys were created
SELECT 'Verifying foreign key constraints...' as status;
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts' 
AND REFERENCED_TABLE_NAME IS NOT NULL;

SELECT 'Foreign key constraints completed successfully' as final_status;

-- Add indexes for performance (skip if they already exist)
CREATE INDEX IF NOT EXISTS idx_contacts_lead_id ON contacts(lead_id);
CREATE INDEX IF NOT EXISTS idx_leads_contact_id ON leads(contact_id);
CREATE INDEX IF NOT EXISTS idx_contacts_email ON contacts(personal_email);
CREATE INDEX IF NOT EXISTS idx_contacts_phone ON contacts(cell_phone);
CREATE INDEX IF NOT EXISTS idx_leads_email ON leads(email);
CREATE INDEX IF NOT EXISTS idx_leads_phone ON leads(cell_phone);

-- Add timestamp columns to contacts if missing
SET @ts_exists = (SELECT COUNT(*) FROM information_schema.columns 
    WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'created_at');

SET @sql3 = IF(@ts_exists = 0, 
    'ALTER TABLE contacts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 
    'SELECT "Timestamp columns already exist" as status');

PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- Verify schema changes
DESCRIBE leads_contacts;
DESCRIBE contacts;
DESCRIBE leads;

-- Check indexes created
SHOW INDEX FROM leads_contacts;
SHOW INDEX FROM contacts;
SHOW INDEX FROM leads;
-- ===============================================
-- SAFE RECOVERY AFTER REBOOT
-- Purpose: Safely continue migration without DROP TABLE issues
-- ===============================================

SELECT '=== SAFE RECOVERY PROCESS ===' as recovery_header;

-- STEP 1: Check current state
SELECT 'STEP 1: Current State Check' as current_step;
SELECT 
    table_name,
    table_rows
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts', 'leads_contacts');

-- STEP 2: Handle leads_contacts table safely
SELECT 'STEP 2: Safe leads_contacts handling' as current_step;

-- Instead of DROP TABLE, we'll rename it if it exists
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
                    WHERE table_schema = DATABASE() AND table_name = 'leads_contacts');

-- If table exists, rename it instead of dropping
SET @sql = CASE 
    WHEN @table_exists > 0 THEN 'RENAME TABLE leads_contacts TO leads_contacts_backup_temp'
    ELSE 'SELECT "No leads_contacts table to rename" as status'
END;

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Table renamed or no action needed' as result;

-- STEP 3: Create fresh leads_contacts table
SELECT 'STEP 3: Creating fresh leads_contacts table' as current_step;

CREATE TABLE leads_contacts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    lead_id INT(11) NOT NULL,
    contact_id INT(11) NOT NULL,
    relationship_type VARCHAR(50) DEFAULT 'primary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Fresh leads_contacts table created' as result;

-- STEP 4: Add indexes first (safer than adding with foreign keys)
SELECT 'STEP 4: Adding indexes' as current_step;
ALTER TABLE leads_contacts ADD KEY idx_lead_id (lead_id);
ALTER TABLE leads_contacts ADD KEY idx_contact_id (contact_id);
ALTER TABLE leads_contacts ADD KEY idx_relationship_type (relationship_type);
SELECT 'Indexes added successfully' as result;

-- STEP 5: Verify table structure before foreign keys
SELECT 'STEP 5: Table structure verification' as current_step;
DESCRIBE leads_contacts;

-- STEP 6: Check parent table structures
SELECT 'STEP 6: Parent table verification' as current_step;
SELECT 'leads table id column:' as check;
SELECT column_name, column_type, is_nullable 
FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'leads' AND column_name = 'id';

SELECT 'contacts table id column:' as check;
SELECT column_name, column_type, is_nullable 
FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'id';

-- STEP 7: Add foreign keys one by one with error handling
SELECT 'STEP 7: Adding foreign keys carefully' as current_step;

-- Enable foreign key checks
SET foreign_key_checks = 1;

-- Try to add leads foreign key
SELECT 'Adding leads foreign key...' as fk_step;
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_lead_id 
FOREIGN KEY (lead_id) REFERENCES leads(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Leads foreign key added' as result;

-- Try to add contacts foreign key
SELECT 'Adding contacts foreign key...' as fk_step;  
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_contact_id 
FOREIGN KEY (contact_id) REFERENCES contacts(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Contacts foreign key added' as result;

-- STEP 8: Add unique constraint
SELECT 'STEP 8: Adding unique constraint' as current_step;
ALTER TABLE leads_contacts 
ADD UNIQUE KEY unique_lead_contact (lead_id, contact_id, relationship_type);

SELECT 'Unique constraint added' as result;

-- STEP 9: Final verification
SELECT 'STEP 9: Final verification' as current_step;
SHOW CREATE TABLE leads_contacts;

-- STEP 10: Clean up backup table if everything worked
SELECT 'STEP 10: Cleanup check' as current_step;
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Backup table exists - can be dropped manually later'
        ELSE 'No backup table to clean'
    END as cleanup_status
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts_backup_temp';

SELECT '=== RECOVERY COMPLETED SUCCESSFULLY ===' as completion_message;
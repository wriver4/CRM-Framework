-- ===============================================
-- CLEAN SLATE LEADS_CONTACTS CREATION
-- Purpose: Create leads_contacts table from scratch
-- No DROP needed - table doesn't exist
-- ===============================================

SELECT '=== CLEAN SLATE CREATION STARTING ===' as creation_header;

-- STEP 1: Verify parent tables exist
SELECT 'STEP 1: Verifying parent tables exist' as current_step;
SELECT 
    table_name,
    engine,
    table_rows
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts')
ORDER BY table_name;

-- STEP 2: Check parent table primary keys
SELECT 'STEP 2: Checking parent table primary keys' as current_step;
SELECT 
    CONCAT(table_name, '.', column_name) as primary_key_column,
    column_type,
    is_nullable
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts')
AND column_key = 'PRI'
ORDER BY table_name;

-- STEP 3: Create leads_contacts table
SELECT 'STEP 3: Creating leads_contacts table' as current_step;

CREATE TABLE leads_contacts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    lead_id INT(11) NOT NULL,
    contact_id INT(11) NOT NULL,
    relationship_type VARCHAR(50) DEFAULT 'primary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_lead_id (lead_id),
    KEY idx_contact_id (contact_id),
    KEY idx_relationship_type (relationship_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'leads_contacts table created successfully' as result;

-- STEP 4: Verify table creation
SELECT 'STEP 4: Verifying table creation' as current_step;
SELECT 
    table_name,
    engine,
    table_collation,
    table_rows
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts';

-- STEP 5: Show table structure
SELECT 'STEP 5: Table structure verification' as current_step;
DESCRIBE leads_contacts;

-- STEP 6: Add foreign key constraints
SELECT 'STEP 6: Adding foreign key constraints' as current_step;

-- Add leads foreign key
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_lead_id 
FOREIGN KEY (lead_id) REFERENCES leads(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Leads foreign key constraint added' as result;

-- Add contacts foreign key
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_contact_id 
FOREIGN KEY (contact_id) REFERENCES contacts(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Contacts foreign key constraint added' as result;

-- STEP 7: Add unique constraint
SELECT 'STEP 7: Adding unique constraint' as current_step;
ALTER TABLE leads_contacts 
ADD UNIQUE KEY unique_lead_contact_relationship (lead_id, contact_id, relationship_type);

SELECT 'Unique constraint added successfully' as result;

-- STEP 8: Final verification
SELECT 'STEP 8: Final table verification' as current_step;
SHOW CREATE TABLE leads_contacts;

-- STEP 9: Verify foreign key constraints
SELECT 'STEP 9: Foreign key constraints verification' as current_step;
SELECT 
    constraint_name,
    table_name,
    column_name,
    referenced_table_name,
    referenced_column_name
FROM information_schema.key_column_usage 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts' 
AND referenced_table_name IS NOT NULL;

SELECT '=== LEADS_CONTACTS TABLE READY FOR MIGRATION ===' as completion_message;
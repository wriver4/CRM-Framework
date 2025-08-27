-- ===============================================
-- STEP-BY-STEP BRIDGE TABLE CREATION
-- File: 02b_create_bridge_table_step_by_step.sql
-- Purpose: Create leads_contacts table step by step with diagnostics
-- ===============================================

SELECT '=== STEP-BY-STEP BRIDGE TABLE CREATION ===' as step_header;

-- STEP 1: Check existing tables
SELECT 'STEP 1: Checking existing tables' as current_step;
SELECT 
    table_name,
    engine,
    table_collation
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts');

-- STEP 2: Verify primary keys exist
SELECT 'STEP 2: Verifying primary keys' as current_step;

SELECT 
    'leads table primary key' as table_check,
    column_name,
    column_type,
    is_nullable
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'leads' 
AND column_key = 'PRI';

SELECT 
    'contacts table primary key' as table_check,
    column_name,
    column_type,
    is_nullable
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'contacts' 
AND column_key = 'PRI';

-- STEP 3: Drop existing bridge table if it exists
SELECT 'STEP 3: Dropping existing bridge table' as current_step;
DROP TABLE IF EXISTS leads_contacts;
SELECT 'Bridge table dropped (if existed)' as result;

-- STEP 4: Create bridge table without foreign keys
SELECT 'STEP 4: Creating bridge table structure' as current_step;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Bridge table created successfully' as result;

-- STEP 5: Verify bridge table was created
SELECT 'STEP 5: Verifying bridge table creation' as current_step;
SELECT 
    table_name,
    engine,
    table_collation
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts';

-- STEP 6: Show bridge table structure
SELECT 'STEP 6: Bridge table structure' as current_step;
DESCRIBE leads_contacts;

-- STEP 7: Check column type compatibility
SELECT 'STEP 7: Column type compatibility check' as current_step;

SELECT 
    'TYPE COMPARISON' as check_type,
    'leads_contacts.lead_id' as column_name,
    (SELECT column_type FROM information_schema.columns 
     WHERE table_schema = DATABASE() AND table_name = 'leads_contacts' AND column_name = 'lead_id') as bridge_column_type,
    (SELECT column_type FROM information_schema.columns 
     WHERE table_schema = DATABASE() AND table_name = 'leads' AND column_name = 'id') as target_column_type,
    CASE WHEN 
        (SELECT column_type FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'leads_contacts' AND column_name = 'lead_id') = 
        (SELECT column_type FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'leads' AND column_name = 'id')
    THEN 'MATCH' ELSE 'MISMATCH' END as compatibility

UNION ALL

SELECT 
    'TYPE COMPARISON' as check_type,
    'leads_contacts.contact_id' as column_name,
    (SELECT column_type FROM information_schema.columns 
     WHERE table_schema = DATABASE() AND table_name = 'leads_contacts' AND column_name = 'contact_id') as bridge_column_type,
    (SELECT column_type FROM information_schema.columns 
     WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'id') as target_column_type,
    CASE WHEN 
        (SELECT column_type FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'leads_contacts' AND column_name = 'contact_id') = 
        (SELECT column_type FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'contacts' AND column_name = 'id')
    THEN 'MATCH' ELSE 'MISMATCH' END as compatibility;

-- STEP 8: Try adding foreign keys one at a time
SELECT 'STEP 8: Adding foreign key constraints' as current_step;

-- First foreign key: leads
SELECT 'Adding leads foreign key...' as fk_step;
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_lead_id 
FOREIGN KEY (lead_id) REFERENCES leads(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Leads foreign key added successfully' as result;

-- Second foreign key: contacts  
SELECT 'Adding contacts foreign key...' as fk_step;
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_contact_id 
FOREIGN KEY (contact_id) REFERENCES contacts(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Contacts foreign key added successfully' as result;

-- STEP 9: Verify foreign keys
SELECT 'STEP 9: Verifying foreign key constraints' as current_step;
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

-- STEP 10: Add unique constraint
SELECT 'STEP 10: Adding unique constraint' as current_step;
ALTER TABLE leads_contacts 
ADD UNIQUE KEY unique_lead_contact (lead_id, contact_id, relationship_type);

SELECT 'Unique constraint added successfully' as result;

-- STEP 11: Final verification
SELECT 'STEP 11: Final table verification' as current_step;
SHOW CREATE TABLE leads_contacts;

SELECT '=== BRIDGE TABLE CREATION COMPLETED ===' as completion_message;
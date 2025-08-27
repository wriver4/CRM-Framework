-- ===============================================
-- SIMPLE CLEAN SLATE LEADS_CONTACTS CREATION
-- Purpose: Create leads_contacts table without information_schema queries
-- Works with limited database permissions
-- ===============================================

-- STEP 1: Basic table existence check using direct queries
SELECT '=== SIMPLE CLEAN SLATE CREATION ===' as creation_header;

SELECT 'STEP 1: Checking if parent tables exist' as current_step;

-- Check if leads table exists by trying to select from it
SELECT 'Checking leads table...' as check_status;
SELECT COUNT(*) as leads_count FROM leads LIMIT 1;

-- Check if contacts table exists by trying to select from it  
SELECT 'Checking contacts table...' as check_status;
SELECT COUNT(*) as contacts_count FROM contacts LIMIT 1;

-- STEP 2: Create leads_contacts table (will fail if exists)
SELECT 'STEP 2: Creating leads_contacts table' as current_step;

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

-- STEP 3: Verify table was created by selecting from it
SELECT 'STEP 3: Verifying table creation' as current_step;
SELECT COUNT(*) as table_count FROM leads_contacts;
SELECT 'Table verification successful' as result;

-- STEP 4: Show table structure using DESCRIBE
SELECT 'STEP 4: Table structure' as current_step;
DESCRIBE leads_contacts;

-- STEP 5: Add foreign key constraints one by one
SELECT 'STEP 5: Adding foreign key constraints' as current_step;

-- Add leads foreign key
SELECT 'Adding leads foreign key...' as fk_step;
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_lead_id 
FOREIGN KEY (lead_id) REFERENCES leads(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Leads foreign key added successfully' as result;

-- Add contacts foreign key
SELECT 'Adding contacts foreign key...' as fk_step;
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_contact_id 
FOREIGN KEY (contact_id) REFERENCES contacts(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Contacts foreign key added successfully' as result;

-- STEP 6: Add unique constraint
SELECT 'STEP 6: Adding unique constraint' as current_step;
ALTER TABLE leads_contacts 
ADD UNIQUE KEY unique_lead_contact_relationship (lead_id, contact_id, relationship_type);

SELECT 'Unique constraint added successfully' as result;

-- STEP 7: Final verification using SHOW CREATE TABLE
SELECT 'STEP 7: Final table structure verification' as current_step;
SHOW CREATE TABLE leads_contacts;

-- STEP 8: Test basic operations
SELECT 'STEP 8: Testing basic table operations' as current_step;
SELECT 'Table is ready for data' as status;
SELECT COUNT(*) as current_row_count FROM leads_contacts;

SELECT '=== LEADS_CONTACTS TABLE READY ===' as completion_message;
SELECT 'You can now proceed to Phase 2 Step 3: Clear contacts table' as next_step;
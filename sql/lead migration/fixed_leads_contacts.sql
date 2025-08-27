-- ===============================================
-- FIXED LEADS_CONTACTS TABLE CREATION  
-- Purpose: Fix column type mismatches for foreign keys
-- Problem: contacts.id is unsigned, leads_contacts.contact_id was not
-- ===============================================

SELECT '=== FIXING LEADS_CONTACTS TABLE ===' as fix_header;

-- STEP 1: Drop existing leads_contacts table (it exists but has wrong column types)
SELECT 'STEP 1: Dropping existing leads_contacts table' as current_step;
DROP TABLE IF EXISTS leads_contacts;
SELECT 'Table dropped successfully' as result;

-- STEP 2: Create leads_contacts with correct column types
SELECT 'STEP 2: Creating leads_contacts with correct column types' as current_step;

CREATE TABLE leads_contacts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    lead_id INT(11) NOT NULL,                    -- matches leads.id (int(11))  
    contact_id INT(10) UNSIGNED NOT NULL,        -- matches contacts.id (int(10) unsigned)
    relationship_type VARCHAR(50) DEFAULT 'primary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_lead_id (lead_id),
    KEY idx_contact_id (contact_id),
    KEY idx_relationship_type (relationship_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'leads_contacts table created with correct column types' as result;

-- STEP 3: Verify table structure
SELECT 'STEP 3: Verifying corrected table structure' as current_step;
DESCRIBE leads_contacts;

-- STEP 4: Add foreign key to leads (should work now)
SELECT 'STEP 4: Adding leads foreign key' as current_step;
ALTER TABLE leads_contacts 
ADD CONSTRAINT fk_leads_contacts_lead_id 
FOREIGN KEY (lead_id) REFERENCES leads(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

SELECT 'Leads foreign key added successfully' as result;

-- STEP 5: Add foreign key to contacts (should work now - types match)
SELECT 'STEP 5: Adding contacts foreign key' as current_step;
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

-- STEP 7: Final verification
SELECT 'STEP 7: Final verification' as current_step;
SHOW CREATE TABLE leads_contacts;

-- STEP 8: Test the table
SELECT 'STEP 8: Testing table functionality' as current_step;
SELECT COUNT(*) as row_count FROM leads_contacts;
SELECT 'Table is ready for migration data' as status;

SELECT '=== LEADS_CONTACTS TABLE FIXED AND READY ===' as completion_message;
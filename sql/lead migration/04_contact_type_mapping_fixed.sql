-- ===============================================
-- STEP 4: CONTACT TYPE MAPPING (FIXED)
-- File: 04_contact_type_mapping.sql
-- Purpose: Map contact types between leads and contacts tables
-- Fixed: Use correct column name 'ctype' instead of 'contact_type'
-- ===============================================

SELECT '=== CONTACT TYPE MAPPING ANALYSIS ===' as mapping_header;

-- STEP 1: Check current ctype values in leads table
SELECT 'STEP 1: Analyzing leads ctype values' as current_step;
SELECT 
    ctype as leads_ctype,
    COUNT(*) as lead_count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM leads), 2) as percentage
FROM leads 
GROUP BY ctype 
ORDER BY ctype;

-- STEP 2: Check current ctype values in contacts table
SELECT 'STEP 2: Analyzing contacts ctype values' as current_step;
SELECT 
    ctype as contacts_ctype,
    COUNT(*) as contact_count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM contacts), 2) as percentage
FROM contacts 
GROUP BY ctype 
ORDER BY ctype;

-- STEP 3: Create mapping table for contact types
SELECT 'STEP 3: Creating contact type mapping table' as current_step;

-- Drop existing mapping table if it exists
DROP TABLE IF EXISTS lead_contact_type_mapping;

-- Create mapping table
CREATE TABLE lead_contact_type_mapping (
    id INT(11) NOT NULL AUTO_INCREMENT,
    leads_ctype TINYINT(4) NOT NULL,
    leads_type_description VARCHAR(100) NOT NULL,
    contacts_ctype INT(2) UNSIGNED NOT NULL,
    contacts_type_description VARCHAR(100) NOT NULL,
    migration_priority INT(2) DEFAULT 1,
    notes TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_mapping (leads_ctype, contacts_ctype),
    KEY idx_leads_ctype (leads_ctype),
    KEY idx_contacts_ctype (contacts_ctype)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Contact type mapping table created' as result;

-- STEP 4: Insert mapping data based on analysis
SELECT 'STEP 4: Inserting mapping data' as current_step;

-- Basic mappings (adjust based on your business logic)
INSERT INTO lead_contact_type_mapping 
(leads_ctype, leads_type_description, contacts_ctype, contacts_type_description, migration_priority, notes) 
VALUES
(1, 'Standard Lead', 1, 'Primary Contact', 1, 'Default mapping for standard leads'),
(2, 'Premium Lead', 1, 'Primary Contact', 1, 'Premium leads map to primary contacts'),
(3, 'Referral Lead', 2, 'Secondary Contact', 2, 'Referral leads may be secondary contacts');

-- Add more mappings as needed based on your actual ctype values
-- You may need to adjust these values after seeing your actual data

SELECT 'Basic mapping data inserted' as result;

-- STEP 5: Show current mappings
SELECT 'STEP 5: Current type mappings' as current_step;
SELECT 
    leads_ctype,
    leads_type_description,
    contacts_ctype,
    contacts_type_description,
    migration_priority,
    notes
FROM lead_contact_type_mapping
ORDER BY leads_ctype;

-- STEP 6: Test mapping against actual data
SELECT 'STEP 6: Testing mappings against actual data' as current_step;
SELECT 
    l.ctype as leads_ctype,
    m.leads_type_description,
    m.contacts_ctype,
    m.contacts_type_description,
    COUNT(*) as lead_count
FROM leads l
LEFT JOIN lead_contact_type_mapping m ON l.ctype = m.leads_ctype
GROUP BY l.ctype, m.leads_type_description, m.contacts_ctype, m.contacts_type_description
ORDER BY l.ctype;

-- STEP 7: Check for unmapped types
SELECT 'STEP 7: Checking for unmapped ctype values' as current_step;
SELECT 
    l.ctype as unmapped_leads_ctype,
    COUNT(*) as lead_count,
    'NEEDS MAPPING' as status
FROM leads l
LEFT JOIN lead_contact_type_mapping m ON l.ctype = m.leads_ctype
WHERE m.leads_ctype IS NULL
GROUP BY l.ctype
ORDER BY l.ctype;

SELECT '=== CONTACT TYPE MAPPING COMPLETED ===' as completion_message;
SELECT 'Review the unmapped types above and add mappings as needed' as next_action;
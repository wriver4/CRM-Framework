-- ===============================================
-- STEP 5: LEAD TO CONTACT MIGRATION (FIXED)
-- File: 05_migration_execution_fixed.sql
-- Purpose: Migrate lead data to contacts table
-- Fixed: Use 'ctype' instead of 'contact_type'
-- ===============================================

SELECT '=== STARTING LEAD TO CONTACT MIGRATION ===' as migration_header;

-- Enable detailed error logging
SET SESSION sql_mode = 'TRADITIONAL';

SELECT 'STEP 1: Pre-migration validation' as current_step;

-- Check current state
SELECT 
    (SELECT COUNT(*) FROM leads) as total_leads,
    (SELECT COUNT(*) FROM contacts) as total_contacts,
    (SELECT COUNT(*) FROM leads_contacts) as total_bridge_records;

-- STEP 2: Create contact records from leads data
SELECT 'STEP 2: Migrating leads to contacts' as current_step;

INSERT INTO contacts (
    lead_id,
    first_name,
    family_name,
    fullname,
    cell_phone,
    personal_email,
    business_email,
    business_name,
    ctype,
    phones,
    emails,
    p_street_1,
    p_street_2,
    p_city,
    p_state,
    p_postcode,
    p_country,
    b_street_1,
    b_street_2,
    b_city,
    b_state,
    b_postcode,
    b_country,
    status,
    created_at,
    updated_at
)
SELECT 
    l.id as lead_id,
    l.first_name,
    l.family_name,
    CONCAT_WS(' ', l.first_name, COALESCE(l.family_name, '')) as fullname,
    l.cell_phone,
    l.email as personal_email,
    CASE 
        WHEN l.business_name IS NOT NULL AND l.business_name != '' 
        THEN l.email 
        ELSE NULL 
    END as business_email,
    l.business_name,
    -- Use the mapping we created - all leads are ctype=1, map to contacts ctype=1
    COALESCE(m.contacts_ctype, 1) as ctype,
    -- JSON phone data (MariaDB compatible)
    CONCAT('{"1":"', COALESCE(l.cell_phone, ''), '","2":"","3":""}') as phones,
    -- JSON email data (MariaDB compatible)  
    CONCAT('{"1":"', COALESCE(l.email, ''), '","2":"', 
           CASE 
               WHEN l.business_name IS NOT NULL AND l.business_name != '' 
               THEN l.email 
               ELSE '' 
           END, '","3":""}') as emails,
    -- Personal address (from lead form data)
    l.form_street_1 as p_street_1,
    l.form_street_2 as p_street_2,
    l.form_city as p_city,
    l.form_state as p_state,
    l.form_postcode as p_postcode,
    l.form_country as p_country,
    -- Business address (copy from personal if business exists)
    CASE 
        WHEN l.business_name IS NOT NULL AND l.business_name != '' 
        THEN l.form_street_1 
        ELSE NULL 
    END as b_street_1,
    CASE 
        WHEN l.business_name IS NOT NULL AND l.business_name != '' 
        THEN l.form_street_2 
        ELSE NULL 
    END as b_street_2,
    CASE 
        WHEN l.business_name IS NOT NULL AND l.business_name != '' 
        THEN l.form_city 
        ELSE NULL 
    END as b_city,
    CASE 
        WHEN l.business_name IS NOT NULL AND l.business_name != '' 
        THEN l.form_state 
        ELSE NULL 
    END as b_state,
    CASE 
        WHEN l.business_name IS NOT NULL AND l.business_name != '' 
        THEN l.form_postcode 
        ELSE NULL 
    END as b_postcode,
    CASE 
        WHEN l.business_name IS NOT NULL AND l.business_name != '' 
        THEN l.form_country 
        ELSE NULL 
    END as b_country,
    1 as status, -- Active
    l.created_at,
    l.updated_at
FROM leads l
LEFT JOIN lead_contact_type_mapping m ON l.ctype = m.leads_ctype
ORDER BY l.id;

SELECT 'Contact records created from leads data' as result;

-- STEP 3: Update leads table with contact references
SELECT 'STEP 3: Linking leads to contacts' as current_step;

UPDATE leads l
INNER JOIN contacts c ON c.lead_id = l.id
SET l.contact_id = c.id;

SELECT 'Leads table updated with contact references' as result;

-- STEP 4: Create bridge table relationships
SELECT 'STEP 4: Creating bridge relationships' as current_step;

INSERT INTO leads_contacts (lead_id, contact_id, relationship_type, status)
SELECT 
    l.id as lead_id,
    l.contact_id,
    'primary' as relationship_type,
    1 as status
FROM leads l
WHERE l.contact_id IS NOT NULL;

SELECT 'Bridge relationships created' as result;

-- STEP 5: Validation and results
SELECT 'STEP 5: Migration validation' as current_step;

SELECT 'Migration Summary:' as summary;
SELECT 
    COUNT(*) as total_leads,
    COUNT(contact_id) as leads_with_contacts,
    COUNT(*) - COUNT(contact_id) as leads_without_contacts
FROM leads;

SELECT COUNT(*) as total_contacts_created FROM contacts;
SELECT COUNT(*) as bridge_relationships_created FROM leads_contacts;

-- Check for any issues
SELECT 'Data Integrity Check:' as integrity_check;

SELECT 
    'Leads without contacts' as issue,
    COUNT(*) as count 
FROM leads 
WHERE contact_id IS NULL;

SELECT 
    'Contacts without leads' as issue,
    COUNT(*) as count 
FROM contacts 
WHERE lead_id IS NULL;

SELECT 
    'Orphaned bridge records' as issue,
    COUNT(*) as count
FROM leads_contacts lc
LEFT JOIN leads l ON lc.lead_id = l.id
LEFT JOIN contacts c ON lc.contact_id = c.id
WHERE l.id IS NULL OR c.id IS NULL;

-- Sample migrated data
SELECT 'Sample Migrated Data (first 5 contacts):' as sample_data;
SELECT 
    c.id as contact_id,
    c.lead_id,
    c.first_name,
    c.family_name,
    c.fullname,
    c.cell_phone,
    c.personal_email,
    c.ctype
FROM contacts c
ORDER BY c.id
LIMIT 5;

SELECT '=== MIGRATION COMPLETED ===' as completion_message;
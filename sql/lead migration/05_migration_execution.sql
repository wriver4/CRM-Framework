-- ===============================================
-- STEP 5: LEAD TO CONTACT MIGRATION
-- File: 05_migration_execution.sql
-- Purpose: Migrate lead data to contacts table
-- ===============================================

-- Enable detailed error logging
SET SESSION sql_mode = 'TRADITIONAL';

-- Create contact records from leads data
INSERT INTO contacts (
    lead_id,
    first_name,
    family_name,
    full_name,
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
    CONCAT_WS(' ', l.first_name, l.family_name) as full_name,
    l.cell_phone,
    l.email as personal_email,
    CASE 
        WHEN l.business_name IS NOT NULL AND l.business_name != '' 
        THEN l.email 
        ELSE NULL 
    END as business_email,
    l.business_name,
    -- Contact type mapping
    CASE 
        WHEN l.contact_type = 1 THEN 1  -- Owner -> Primary Owner
        WHEN l.contact_type = 2 THEN 1  -- Owner with Existing System -> Primary Owner  
        WHEN l.contact_type = 3 THEN 6  -- Representative -> Owner's Rep.
        WHEN l.contact_type = 4 THEN 11 -- Architect or Engineer -> Developer
        WHEN l.contact_type = 5 THEN 10 -- Product Dealer or Installer -> Installer
        ELSE 1 -- Default to Primary Owner
    END as ctype,
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
ORDER BY l.id;

-- Get contact IDs and update leads table
UPDATE leads l
INNER JOIN contacts c ON c.lead_id = l.id
SET l.contact_id = c.id;

-- Create bridge table relationships
INSERT INTO leads_contacts (lead_id, contact_id, relationship_type, status)
SELECT 
    l.id as lead_id,
    l.contact_id,
    'primary' as relationship_type,
    1 as status
FROM leads l
WHERE l.contact_id IS NOT NULL;

-- Validation queries
SELECT 'Migration Results' as step;

SELECT 
    COUNT(*) as total_leads,
    COUNT(contact_id) as leads_with_contacts,
    COUNT(*) - COUNT(contact_id) as leads_without_contacts
FROM leads;

SELECT COUNT(*) as total_contacts_created FROM contacts;

SELECT COUNT(*) as bridge_relationships_created FROM leads_contacts;

-- Check for any issues
SELECT 'Potential Issues' as check_type;

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
-- ===============================================
-- STEP 6: VALIDATION AND CLEANUP
-- File: 06_validation_and_cleanup.sql
-- Purpose: Validate migration results and cleanup
-- ===============================================

-- Comprehensive validation report
SELECT '=== MIGRATION VALIDATION REPORT ===' as report_section;

-- 1. Record count validation
SELECT 
    'Record Counts' as validation_type,
    (SELECT COUNT(*) FROM leads) as total_leads,
    (SELECT COUNT(*) FROM contacts) as total_contacts,
    (SELECT COUNT(*) FROM leads_contacts) as bridge_relationships;

-- 2. Data integrity checks
SELECT 
    'Data Integrity' as validation_type,
    SUM(CASE WHEN contact_id IS NULL THEN 1 ELSE 0 END) as leads_without_contact_id,
    SUM(CASE WHEN lead_id IS NULL THEN 1 ELSE 0 END) as contacts_without_lead_id,
    COUNT(*) as total_leads_checked
FROM leads l
LEFT JOIN contacts c ON l.contact_id = c.id;

-- 3. Contact type distribution
SELECT 
    'Contact Type Distribution' as validation_type,
    c.ctype,
    CASE c.ctype
        WHEN 1 THEN 'Primary Owner'
        WHEN 6 THEN 'Owner\'s Rep.'
        WHEN 10 THEN 'Installer'
        WHEN 11 THEN 'Developer'
        ELSE 'Other'
    END as contact_type_name,
    COUNT(*) as count
FROM contacts c
GROUP BY c.ctype
ORDER BY c.ctype;

-- 4. JSON data validation (MariaDB compatible)
SELECT 
    'JSON Data Validation' as validation_type,
    COUNT(*) as total_contacts,
    SUM(CASE WHEN phones LIKE '{"1":"%","2":"%","3":"%"}' THEN 1 ELSE 0 END) as valid_phone_json,
    SUM(CASE WHEN emails LIKE '{"1":"%","2":"%","3":"%"}' THEN 1 ELSE 0 END) as valid_email_json,
    SUM(CASE WHEN phones IS NULL OR phones = '' THEN 1 ELSE 0 END) as empty_phones,
    SUM(CASE WHEN emails IS NULL OR emails = '' THEN 1 ELSE 0 END) as empty_emails
FROM contacts;

-- 5. Address data validation
SELECT 
    'Address Data' as validation_type,
    COUNT(*) as total_contacts,
    SUM(CASE WHEN p_street_1 IS NOT NULL AND p_street_1 != '' THEN 1 ELSE 0 END) as has_personal_address,
    SUM(CASE WHEN b_street_1 IS NOT NULL AND b_street_1 != '' THEN 1 ELSE 0 END) as has_business_address,
    SUM(CASE WHEN business_name IS NOT NULL AND business_name != '' THEN 1 ELSE 0 END) as has_business_name
FROM contacts;

-- 6. Bridge table validation
SELECT 
    'Bridge Table Validation' as validation_type,
    COUNT(*) as total_relationships,
    COUNT(DISTINCT lead_id) as unique_leads_in_bridge,
    COUNT(DISTINCT contact_id) as unique_contacts_in_bridge,
    SUM(CASE WHEN relationship_type = 'primary' THEN 1 ELSE 0 END) as primary_relationships
FROM leads_contacts;

-- 7. Check for duplicates
SELECT 
    'Duplicate Check' as validation_type,
    COUNT(*) - COUNT(DISTINCT personal_email) as duplicate_emails,
    COUNT(*) - COUNT(DISTINCT cell_phone) as duplicate_phones
FROM contacts 
WHERE personal_email IS NOT NULL AND personal_email != ''
   OR cell_phone IS NOT NULL AND cell_phone != '';

-- 8. Sample data verification (first 5 records)
SELECT 
    'Sample Data Preview' as validation_type,
    l.id as lead_id,
    l.first_name,
    l.family_name,
    l.email as lead_email,
    l.contact_id,
    c.id as contact_table_id,
    c.personal_email as contact_email,
    c.ctype as contact_type,
    lc.relationship_type
FROM leads l
LEFT JOIN contacts c ON l.contact_id = c.id
LEFT JOIN leads_contacts lc ON l.id = lc.lead_id AND c.id = lc.contact_id
LIMIT 5;

-- 9. Error detection
SELECT '=== POTENTIAL ISSUES ===' as error_section;

-- Missing contact relationships
SELECT 
    'Missing Relationships' as issue_type,
    l.id as lead_id,
    l.first_name,
    l.family_name,
    l.email,
    'Lead has no contact_id' as issue
FROM leads l
WHERE l.contact_id IS NULL
LIMIT 10;

-- Orphaned contacts
SELECT 
    'Orphaned Contacts' as issue_type,
    c.id as contact_id,
    c.first_name,
    c.family_name,
    c.personal_email,
    'Contact has no lead_id' as issue
FROM contacts c
WHERE c.lead_id IS NULL
LIMIT 10;

-- Invalid JSON data (MariaDB compatible)
SELECT 
    'Invalid JSON' as issue_type,
    id as contact_id,
    first_name,
    family_name,
    CASE 
        WHEN phones NOT LIKE '{"1":"%","2":"%","3":"%"}' THEN 'Invalid phones JSON'
        WHEN emails NOT LIKE '{"1":"%","2":"%","3":"%"}' THEN 'Invalid emails JSON'
        ELSE 'Unknown JSON issue'
    END as issue
FROM contacts
WHERE phones NOT LIKE '{"1":"%","2":"%","3":"%"}' OR emails NOT LIKE '{"1":"%","2":"%","3":"%"}'
LIMIT 10;

-- Final success confirmation
SELECT 
    '=== MIGRATION STATUS ===' as final_status,
    CASE 
        WHEN (SELECT COUNT(*) FROM leads WHERE contact_id IS NULL) = 0
         AND (SELECT COUNT(*) FROM contacts WHERE lead_id IS NULL) = 0
         AND (SELECT COUNT(*) FROM leads) = (SELECT COUNT(*) FROM contacts)
        THEN 'SUCCESS: Migration completed successfully'
        ELSE 'WARNING: Migration has issues - check error reports above'
    END as migration_result;
-- ===============================================
-- VALIDATE CTYPE COMPATIBILITY
-- Purpose: Check for any ctype errors between tables
-- ===============================================

SELECT '=== CTYPE COMPATIBILITY VALIDATION ===' as validation_header;

-- STEP 1: Check current ctype values in migrated contacts
SELECT 'STEP 1: Contacts ctype distribution' as current_step;
SELECT 
    ctype,
    COUNT(*) as contact_count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM contacts), 2) as percentage
FROM contacts 
GROUP BY ctype 
ORDER BY ctype;

-- STEP 2: Check original leads ctype vs migrated contacts ctype
SELECT 'STEP 2: Leads vs Contacts ctype comparison' as current_step;
SELECT 
    l.ctype as leads_original_ctype,
    c.ctype as contacts_migrated_ctype,
    COUNT(*) as record_count,
    CASE 
        WHEN l.ctype = c.ctype THEN 'MATCH'
        ELSE 'MISMATCH - POTENTIAL ERROR'
    END as compatibility_status
FROM leads l
INNER JOIN contacts c ON c.lead_id = l.id
GROUP BY l.ctype, c.ctype
ORDER BY l.ctype, c.ctype;

-- STEP 3: Check if contacts ctype values are valid according to expected ranges
SELECT 'STEP 3: Contacts ctype validation' as current_step;
SELECT 
    c.ctype,
    COUNT(*) as count,
    CASE 
        WHEN c.ctype BETWEEN 1 AND 20 THEN 'VALID RANGE'
        WHEN c.ctype = 0 THEN 'WARNING - Zero value'
        WHEN c.ctype IS NULL THEN 'ERROR - NULL value'
        ELSE 'WARNING - Unexpected value'
    END as validation_status
FROM contacts c
GROUP BY c.ctype
ORDER BY c.ctype;

-- STEP 4: Check the mapping table we used
SELECT 'STEP 4: Applied mapping validation' as current_step;
SELECT 
    leads_ctype,
    leads_type_description,
    contacts_ctype,
    contacts_type_description,
    (SELECT COUNT(*) FROM leads WHERE ctype = leads_ctype) as leads_with_this_type,
    (SELECT COUNT(*) FROM contacts WHERE ctype = contacts_ctype) as contacts_with_mapped_type
FROM lead_contact_type_mapping
ORDER BY leads_ctype;

-- STEP 5: Check for any data type incompatibilities
SELECT 'STEP 5: Data type compatibility check' as current_step;

-- Check leads.ctype column definition
SELECT 'leads.ctype column info:' as column_check;
DESCRIBE leads;

-- Check contacts.ctype column definition  
SELECT 'contacts.ctype column info:' as column_check;
DESCRIBE contacts;

-- STEP 6: Identify any potential issues
SELECT 'STEP 6: Issue identification' as current_step;

-- Check for any contacts with unexpected ctype values
SELECT 
    'Contacts with potentially invalid ctype' as issue_type,
    COUNT(*) as issue_count
FROM contacts 
WHERE ctype IS NULL OR ctype = 0 OR ctype > 20;

-- Check for leads-contacts ctype mismatches
SELECT 
    'Lead-Contact ctype mismatches' as issue_type,
    COUNT(*) as issue_count
FROM leads l
INNER JOIN contacts c ON c.lead_id = l.id
WHERE l.ctype != c.ctype;

-- STEP 7: Recommendations
SELECT 'STEP 7: Recommendations' as current_step;

SELECT CASE 
    WHEN (SELECT COUNT(*) FROM contacts WHERE ctype IS NULL OR ctype = 0) > 0
    THEN 'RECOMMENDATION: Fix NULL or zero ctype values'
    WHEN (SELECT COUNT(*) FROM leads l INNER JOIN contacts c ON c.lead_id = l.id WHERE l.ctype != c.ctype) > 0  
    THEN 'RECOMMENDATION: Review ctype mapping - mismatches detected'
    ELSE 'SUCCESS: No ctype issues detected'
END as recommendation;

SELECT '=== CTYPE VALIDATION COMPLETED ===' as completion_message;
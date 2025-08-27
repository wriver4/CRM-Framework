-- ===============================================
-- DIAGNOSTIC SCRIPT FOR FOREIGN KEY ERROR 150
-- File: 02a_diagnose_fk_error.sql
-- Purpose: Diagnose why foreign keys are failing
-- ===============================================

SELECT '=== FOREIGN KEY DIAGNOSTIC REPORT ===' as diagnostic_section;

-- 1. Check if tables exist
SELECT 'Table Existence Check' as check_type;
SELECT 
    table_name,
    engine,
    table_collation
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts', 'leads_contacts');

-- 2. Check table engines (must be InnoDB for foreign keys)
SELECT 'Engine Compatibility Check' as check_type;
SELECT 
    table_name,
    engine,
    CASE 
        WHEN engine = 'InnoDB' THEN 'OK - Supports FK'
        ELSE 'ERROR - Must be InnoDB'
    END as fk_support
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts', 'leads_contacts');

-- 3. Check primary key existence and structure
SELECT 'Primary Key Check - leads table' as check_type;
SELECT 
    column_name,
    column_type,
    is_nullable,
    column_key,
    extra
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'leads' 
AND column_key = 'PRI';

SELECT 'Primary Key Check - contacts table' as check_type;
SELECT 
    column_name,
    column_type,
    is_nullable,
    column_key,
    extra
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'contacts' 
AND column_key = 'PRI';

-- 4. Check foreign key column types
SELECT 'Foreign Key Column Types Comparison' as check_type;

-- leads_contacts.lead_id vs leads.id
SELECT 
    'leads_contacts.lead_id' as column_ref,
    column_type,
    is_nullable,
    column_default,
    character_set_name,
    collation_name
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts' 
AND column_name = 'lead_id'

UNION ALL

SELECT 
    'leads.id (target)' as column_ref,
    column_type,
    is_nullable,
    column_default,
    character_set_name,
    collation_name
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'leads' 
AND column_name = 'id'

UNION ALL

-- leads_contacts.contact_id vs contacts.id  
SELECT 
    'leads_contacts.contact_id' as column_ref,
    column_type,
    is_nullable,
    column_default,
    character_set_name,
    collation_name
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts' 
AND column_name = 'contact_id'

UNION ALL

SELECT 
    'contacts.id (target)' as column_ref,
    column_type,
    is_nullable,
    column_default,
    character_set_name,
    collation_name
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'contacts' 
AND column_name = 'id';

-- 5. Check existing foreign key constraints
SELECT 'Existing Foreign Key Constraints' as check_type;
SELECT 
    constraint_name,
    table_name,
    column_name,
    referenced_table_name,
    referenced_column_name
FROM information_schema.key_column_usage 
WHERE table_schema = DATABASE() 
AND referenced_table_name IS NOT NULL;

-- 6. Check for any existing indexes that might conflict
SELECT 'Index Check - leads_contacts table' as check_type;
SHOW INDEX FROM leads_contacts;

-- 7. Show actual table structures
SELECT 'Complete Table Structures' as check_type;
SHOW CREATE TABLE leads;
SHOW CREATE TABLE contacts; 
SHOW CREATE TABLE leads_contacts;

-- 8. MariaDB version check
SELECT 'MariaDB Version Information' as check_type;
SELECT VERSION() as mariadb_version;

-- 9. Check foreign key checks setting
SELECT 'Foreign Key Checks Status' as check_type;
SHOW VARIABLES LIKE 'foreign_key_checks';

SELECT '=== END DIAGNOSTIC REPORT ===' as diagnostic_end;
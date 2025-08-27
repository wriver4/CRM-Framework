-- ===============================================
-- POST-REBOOT DATABASE DIAGNOSIS (FIXED)
-- Purpose: Check current state after server reboot
-- Compatible with older MariaDB versions
-- ===============================================

SELECT '=== POST-REBOOT DIAGNOSTIC REPORT ===' as diagnostic_section;

-- 1. Check which tables currently exist
SELECT 'Current Table Status' as check_type;
SELECT 
    table_name,
    engine,
    table_collation,
    table_rows,
    data_length
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts', 'leads_contacts')
ORDER BY table_name;

-- 2. Check if leads_contacts table exists and its structure
SELECT 'leads_contacts Table Check' as check_type;
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'EXISTS'
        ELSE 'NOT EXISTS'
    END as table_status
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts';

-- 3. If leads_contacts exists, show its structure
SELECT 'leads_contacts Structure (if exists)' as check_type;
SELECT 
    column_name,
    column_type,
    is_nullable,
    column_key,
    column_default,
    extra
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts'
ORDER BY ordinal_position;

-- 4. Check foreign key constraints (fixed for older MariaDB)
SELECT 'Foreign Key Constraints Status' as check_type;
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

-- 5. Alternative foreign key check using referential_constraints
SELECT 'Referential Constraints Check' as check_type;
SELECT 
    constraint_name,
    table_name,
    referenced_table_name
FROM information_schema.referential_constraints 
WHERE constraint_schema = DATABASE() 
AND table_name = 'leads_contacts';

-- 6. Check MariaDB version and settings
SELECT 'MariaDB Status' as check_type;
SELECT VERSION() as version;
SELECT @@foreign_key_checks as foreign_key_checks_setting;

-- 7. Check table engines
SELECT 'Table Engine Verification' as check_type;
SELECT 
    table_name,
    engine,
    CASE 
        WHEN engine = 'InnoDB' THEN 'OK'
        ELSE 'NEEDS INNODB'
    END as status
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts', 'leads_contacts');

-- 8. Check primary keys of parent tables
SELECT 'Parent Table Primary Keys' as check_type;
SELECT 
    table_name,
    column_name,
    column_type,
    is_nullable
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name IN ('leads', 'contacts')
AND column_key = 'PRI';

-- 9. Show actual table creation statements if leads_contacts exists
SELECT 'Table Creation Statement' as check_type;
-- Note: This will only work if the table exists

SELECT '=== END POST-REBOOT DIAGNOSIS ===' as diagnostic_end;
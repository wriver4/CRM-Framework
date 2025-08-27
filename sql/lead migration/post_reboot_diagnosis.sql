-- ===============================================
-- POST-REBOOT DATABASE DIAGNOSIS
-- Purpose: Check current state after server reboot
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

-- 4. Check foreign key constraints
SELECT 'Foreign Key Constraints Status' as check_type;
SELECT 
    constraint_name,
    table_name,
    column_name,
    referenced_table_name,
    referenced_column_name,
    delete_rule,
    update_rule
FROM information_schema.key_column_usage 
WHERE table_schema = DATABASE() 
AND table_name = 'leads_contacts' 
AND referenced_table_name IS NOT NULL;

-- 5. Check for any processes or locks
SELECT 'Process List Check' as check_type;
SHOW PROCESSLIST;

-- 6. Check MariaDB status
SELECT 'MariaDB Status' as check_type;
SELECT VERSION() as version;
SHOW VARIABLES LIKE 'foreign_key_checks';

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

SELECT '=== END POST-REBOOT DIAGNOSIS ===' as diagnostic_end;
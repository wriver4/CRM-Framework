-- ===============================================
-- STEP 1: DATABASE BACKUP (Run FIRST!)
-- File: 01_database_backup.sql
-- Purpose: Create complete backup before migration
-- ===============================================

-- Export database tables to backup files
-- NOTE: Run these commands in PHPMyAdmin Export section
-- Select: Custom export type
-- Tables to export: ALL tables
-- Format: SQL
-- Options: Structure and data

-- Alternative: Use this query to verify table counts before backup
SELECT 
    'leads' as table_name, 
    COUNT(*) as record_count, 
    'BACKUP_REQUIRED' as status
FROM leads
UNION ALL
SELECT 
    'contacts' as table_name, 
    COUNT(*) as record_count, 
    'WILL_BE_CLEARED' as status
FROM contacts
UNION ALL
SELECT 
    'audit' as table_name, 
    COUNT(*) as record_count, 
    'BACKUP_REQUIRED' as status
FROM audit;

-- Verify critical tables exist before backup
SHOW TABLES LIKE 'leads';
SHOW TABLES LIKE 'contacts';
SHOW TABLES LIKE 'audit';

-- INSTRUCTIONS:
-- 1. Record these counts for validation after migration
-- 2. Export complete database using PHPMyAdmin Export
-- 3. Save backup file with timestamp: backup_YYYY-MM-DD_HH-MM.sql
-- 4. Verify backup file size > 0 bytes before proceeding
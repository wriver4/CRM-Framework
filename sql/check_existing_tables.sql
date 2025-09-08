-- ========================================
-- CHECK EXISTING DATABASE STRUCTURE
-- Copy and paste this into phpMyAdmin SQL tab first
-- ========================================
-- 1. Show all tables in the database
SHOW TABLES;

-- 2. Check if email-related tables already exist
SHOW TABLES LIKE '%email%';

SHOW TABLES LIKE '%crm%';

SHOW TABLES LIKE '%sync%';

-- 3. Check if the specific tables we need exist
SELECT
  TABLE_NAME,
  TABLE_COMMENT
FROM
  INFORMATION_SCHEMA.TABLES
WHERE
  TABLE_SCHEMA = DATABASE ()
  AND TABLE_NAME IN (
    'email_form_processing',
    'crm_sync_queue',
    'email_accounts_config'
  );

-- 4. If any of these tables exist, show their structure
-- (Uncomment the lines below if the tables exist)
-- DESCRIBE `email_form_processing`;
-- DESCRIBE `crm_sync_queue`;  
-- DESCRIBE `email_accounts_config`;
-- 5. Check leads table structure (for foreign key compatibility)
DESCRIBE `leads`;

-- 6. Show database name and version
SELECT
  DATABASE () as current_database;

SELECT
  VERSION () as database_version;
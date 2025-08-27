-- ===============================================
-- DIAGNOSE FOREIGN KEY ERROR 150
-- Purpose: Find why foreign key constraint is failing
-- No information_schema queries - direct table checks only
-- ===============================================

SELECT '=== FOREIGN KEY ERROR 150 DIAGNOSIS ===' as diagnosis_header;

-- STEP 1: Check if target tables exist and their basic structure
SELECT 'STEP 1: Checking target table structures' as current_step;

SELECT 'leads table structure:' as table_check;
DESCRIBE leads;

SELECT 'contacts table structure:' as table_check;  
DESCRIBE contacts;

SELECT 'leads_contacts table structure:' as table_check;
DESCRIBE leads_contacts;

-- STEP 2: Check table engines and character sets
SELECT 'STEP 2: Checking table engines' as current_step;

SELECT 'leads table creation:' as table_info;
SHOW CREATE TABLE leads;

SELECT 'contacts table creation:' as table_info;
SHOW CREATE TABLE contacts;

SELECT 'leads_contacts table creation:' as table_info;
SHOW CREATE TABLE leads_contacts;

-- STEP 3: Test if we can reference these tables at all
SELECT 'STEP 3: Testing table accessibility' as current_step;

SELECT 'Sample data from leads (first few rows):' as data_check;
SELECT id, created_at FROM leads LIMIT 3;

SELECT 'Sample data from contacts (first few rows):' as data_check;
SELECT id, created_at FROM contacts LIMIT 3;

-- STEP 4: Check MariaDB version and settings
SELECT 'STEP 4: MariaDB version and settings' as current_step;
SELECT VERSION() as mariadb_version;
SELECT @@sql_mode as sql_mode;
SELECT @@foreign_key_checks as fk_checks;

SELECT '=== END DIAGNOSIS - CHECK RESULTS ABOVE ===' as diagnosis_end;
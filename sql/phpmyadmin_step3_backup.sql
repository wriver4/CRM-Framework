-- STEP 3: Create backup table
SELECT
  'STEP 3: Creating backup table' as current_step;

CREATE TABLE
  contacts_backup_before_lead_id_sync AS
SELECT
  *
FROM
  contacts;

SELECT
  'Backup table created: contacts_backup_before_lead_id_sync' as result;
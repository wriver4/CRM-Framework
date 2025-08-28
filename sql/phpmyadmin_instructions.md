# phpMyAdmin Instructions: Sync contacts.lead_id with leads.lead_id

## Overview
This migration will update `contacts.lead_id` to store the business identifier (`leads.lead_id`) instead of the database primary key (`leads.id`).

## Steps to Execute in phpMyAdmin

### 1. **Run Step 1: Analyze Current Data**
- Open `phpmyadmin_sync_contacts_lead_id.sql`
- Copy and paste the content into phpMyAdmin SQL tab
- Execute to see current relationship counts

### 2. **Run Step 2: View Sample Data**
- Open `phpmyadmin_step2_sample_data.sql`
- Execute to see sample of current data before migration
- Note the difference between `leads_db_id` and `leads_business_id`

### 3. **Run Step 3: Create Backup**
- Open `phpmyadmin_step3_backup.sql`
- Execute to create backup table `contacts_backup_before_lead_id_sync`
- **IMPORTANT**: Verify backup was created successfully

### 4. **Run Step 4: Preview Update**
- Open `phpmyadmin_step4_preview_update.sql`
- Execute to see what records will be changed
- Review the `current_value` vs `new_value` columns

### 5. **Run Step 5: Perform Update**
- Open `phpmyadmin_step5_perform_update.sql`
- **WARNING**: This modifies your data!
- Execute the UPDATE statement
- Note how many rows were affected

### 6. **Run Step 6: Verify Changes**
- Open `phpmyadmin_step6_verify.sql`
- Execute to verify the migration worked correctly
- All records should show `MATCH` status
- Orphaned references count should be 0

### 7. **Run Step 7: Check Data Types**
- Open `phpmyadmin_step7_check_datatypes.sql`
- Execute to check if data types need alignment
- If `contacts.lead_id` and `leads.lead_id` have different types, run the data type alignment script

## After Migration

### Update Code References
After the database migration, you may need to update code that queries contacts by lead_id:

**Before (using database ID):**
```sql
SELECT * FROM contacts WHERE lead_id = 123  -- where 123 is leads.id
```

**After (using business identifier):**
```sql
SELECT * FROM contacts WHERE lead_id = 'L001'  -- where 'L001' is leads.lead_id
```

### Verify Application Functionality
- Test lead-contact relationships in the application
- Verify contact creation from leads works correctly
- Check that contact listings show correct lead associations

## Rollback (if needed)
If something goes wrong, you can restore from the backup:
```sql
DROP TABLE contacts;
CREATE TABLE contacts AS SELECT * FROM contacts_backup_before_lead_id_sync;
```

## Notes
- The bridge table `leads_contacts` continues to use `leads.id` (database primary key)
- Direct `contacts.lead_id` field now uses `leads.lead_id` (business identifier)
- This creates a cleaner separation between internal relationships and business identifiers
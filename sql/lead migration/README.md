# Lead-Contact Integration Migration SQL Files

## Overview
This directory contains all SQL queries for PHPMyAdmin execution during the lead-contact integration migration.

## File Execution Order

### 🚨 **CRITICAL: Execute in This Exact Order**

1. **01_database_backup.sql** - Create complete database backup
2. **02_schema_updates.sql** - Add required fields and indexes  
3. **03_clear_contacts_table.sql** - Clear existing contacts data
4. **04_contact_type_mapping.sql** - Validate contact type mapping
5. **05_migration_execution.sql** - Migrate leads to contacts
6. **06_validation_and_cleanup.sql** - Validate migration results
7. **07_rollback_procedure.sql** - Emergency rollback (only if needed)

## PHPMyAdmin Execution Instructions

### Before Starting
- Ensure 3 terminals are open for file ownership management
- Have PHPMyAdmin open and connected to database
- Verify you have database admin privileges

### Execution Steps

#### Step 1: Backup (01_database_backup.sql)
```sql
-- In PHPMyAdmin, go to Export tab
-- Select: Custom export type
-- Tables: Select ALL
-- Format: SQL  
-- Structure and data: Both checked
-- Save as: backup_YYYY-MM-DD_HH-MM.sql
```

#### Step 2-6: Execute SQL Files
- Copy content from each SQL file
- Paste into PHPMyAdmin SQL tab
- Click "Go" to execute
- Review results before proceeding to next file

#### Step 7: Rollback (Only if migration fails)
- Only use if critical errors occur
- Will restore database to pre-migration state
- Requires manual restore of contacts table from backup

## Validation Checkpoints

### After Each Step:
- ✅ Check for SQL errors
- ✅ Verify expected record counts
- ✅ Review validation output
- ✅ Confirm no data corruption

### Success Criteria:
- All leads have associated contacts
- All contacts have lead_id references
- Bridge table relationships created
- JSON data valid
- No orphaned records

## Error Handling

### If Errors Occur:
1. **STOP immediately**
2. Record error message
3. Do NOT continue to next step
4. Use rollback procedure if necessary
5. Restore from backup
6. Investigate and fix issue

### Common Issues:
- Foreign key constraint violations
- Invalid JSON data format
- Missing required fields
- Duplicate key errors

## File Ownership Management

When prompted, run these commands in your 3 open terminals:

### Terminal 1: Web Files
```bash
chown -R democrm:democrm /home/democrm/public_html/
```

### Terminal 2: Class Files  
```bash
chown -R democrm:democrm /home/democrm/classes/
```

### Terminal 3: Logs and Session Files
```bash
chown -R democrm:democrm /home/democrm/logs/
chown -R democrm:democrm /home/democrm/tmp/
# Note: tmp directory contains PHP session files
```

## Support Information

### Migration Timeline:
- Estimated time: 15-30 minutes total
- Backup: 2-5 minutes
- Schema updates: 1-2 minutes  
- Migration execution: 5-15 minutes
- Validation: 2-5 minutes

### Rollback Time:
- Emergency rollback: 2-5 minutes
- Full restore from backup: 5-10 minutes

### Contact Information:
- Keep development team informed of progress
- Report any issues immediately
- Do not attempt fixes without consultation
# Database Migrations Guide

## Quick Start

1. **Create Migration**: Copy `_MIGRATION_TEMPLATE.sql` and rename it
2. **Execute in phpMyAdmin**: Copy SQL statements and run them
3. **Run Reminder Script**: `php scripts/update_structure_reminder.php your_migration.sql`
4. **Update Structure File**: Export database structure and replace `/sql/democrm_democrm_structure.sql`

## 🔔 Don't Forget the Structure File!

**Every migration requires updating the structure file.** The reminder script will guide you through the process.

## Migration Template

Use `_MIGRATION_TEMPLATE.sql` as your starting point. It includes:
- ✅ Proper header format with date and description
- ✅ Developer action instructions
- ✅ Structure update reminder with exact command
- ✅ Post-migration checklist
- ✅ Consistent formatting and documentation

## Reminder Script Features

The `update_structure_reminder.php` script provides:
- 📋 Step-by-step phpMyAdmin export instructions
- 🕐 File modification date checking
- 📁 Recent migrations list
- ✅ Verification steps
- 💡 Helpful tips and automation suggestions

## Why Structure Updates Matter

- **Fresh Installations**: New deployments use the structure file
- **Developer Environments**: Team members need updated schema
- **Documentation**: Serves as authoritative database reference
- **Deployment Tools**: Automated processes rely on this file

## Example Workflow

```bash
# 1. Create migration from template
cp sql/migrations/_MIGRATION_TEMPLATE.sql sql/migrations/add_new_feature.sql

# 2. Edit migration file with your changes
# 3. Execute SQL in phpMyAdmin
# 4. Run reminder script
php scripts/update_structure_reminder.php sql/migrations/add_new_feature.sql

# 5. Follow the detailed instructions to update structure file
```

## File Locations

- **Migrations**: `/sql/migrations/`
- **Archive**: `/Archive/sql/migrations/` (permanent archive for completed migrations)
- **Structure File**: `/sql/democrm_democrm_structure.sql`
- **Reminder Script**: `/scripts/update_structure_reminder.php`
- **Template**: `/sql/migrations/_MIGRATION_TEMPLATE.sql`

## Need Help?

Check the repository documentation at `.zencoder/rules/repo.md` for detailed information about the database migration workflow and hosting environment constraints.
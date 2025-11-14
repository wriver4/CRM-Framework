# Column Refactoring: rid → role_id, rname → role

## Overview
This document outlines the comprehensive method to rename database columns and update all related code:
- `rid` → `role_id` 
- `rname` → `role`

**Scope**: 2 tables, 6 PHP files, 2 language files, 1 migration

---

## Phase 1: Database Migration

### Step 1.1: Create SQL Migration File
**File**: `/sql/migrations/2025_01_XX_rename_role_columns.sql`

```sql
-- Rename columns in roles table
ALTER TABLE `roles`
  DROP KEY `rname`,
  DROP KEY `rid`;

ALTER TABLE `roles`
  CHANGE COLUMN `rid` `role_id` INT(11) NOT NULL,
  CHANGE COLUMN `rname` `role` VARCHAR(50) NOT NULL;

ALTER TABLE `roles`
  ADD UNIQUE KEY `role` (`role`),
  ADD UNIQUE KEY `role_id` (`role_id`) USING BTREE;

-- Rename column in roles_permissions table
ALTER TABLE `roles_permissions`
  CHANGE COLUMN `rid` `role_id` INT(11) NOT NULL;

-- Update primary key constraint
ALTER TABLE `roles_permissions`
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (`role_id`, `pid`);
```

### Step 1.2: Update Structure File
Update `/sql/democrm_democrm_structure.sql` to reflect new column names in both table definitions.

---

## Phase 2: PHP Model Classes

### Step 2.1: Update `Roles.php` Model
**File**: `/classes/Models/Roles.php`

Changes needed:
- Line 14: Update comment from `rid` to `role_id`
- Line 15: Update comment from `rname` to `role`
- Line 32: `SELECT id, rid, rname` → `SELECT id, role_id, role`
- Line 45: `$role['rname']` → `$role['role']`
- Line 50: `ORDER BY rid` → `ORDER BY role_id`
- Line 55: `$role['rname']` → `$role['role']`

### Step 2.2: Update `RolesPermissions.php` Model
**File**: `/classes/Models/RolesPermissions.php`

Changes needed:
- Any query referencing `rid` → `role_id`
- Update comments referencing `rid`

---

## Phase 3: View Classes

### Step 3.1: Update `RolesList.php` View
**File**: `/classes/Views/RolesList.php`

```php
// Line 15-16: Update column mapping
'action' => $lang['action'],
'role_id' => $lang['role_id'],    // changed from 'rid'
'role' => $lang['role']            // changed from 'rname'
```

### Step 3.2: Update `RolesPermissionsList.php` View
**File**: `/classes/Views/RolesPermissionsList.php`

Changes needed:
- Any reference to `rid` in column mappings → `role_id`

---

## Phase 4: Page Files

### Step 4.1: Update `edit_role.php`
**File**: `/public_html/security/roles/edit_role.php`

Changes needed:
- Search and replace all `rid` → `role_id`
- Search and replace all `rname` → `role`
- Update form field names if applicable

### Step 4.2: Update `assign_role_permissions.php`
**File**: `/public_html/security/permissions/assign_role_permissions.php`

Changes needed:
- Search and replace all `rid` → `role_id`
- Search and replace all `rname` → `role`

---

## Phase 5: Language Files

### Step 5.1: Update English Language File
**File**: `/admin/languages/en.php` (or appropriate language file)

Changes needed:
- Find any reference to `rname` key
- Map `'rname'` to `'role'`
- Ensure `'role_id'` key exists

### Step 5.2: Update Spanish Language File
**File**: `/admin/languages/es.php` (or equivalent)

Changes needed:
- Same as English file in Spanish

---

## Phase 6: Code References (Comprehensive Search)

### Step 6.1: Search All PHP Files
Execute these searches to find any remaining references:

```bash
# Search for rid usage
grep -r "\$.*\['rid'\]" /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm --include="*.php"
grep -r "'rid'" /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm --include="*.php"
grep -r '"rid"' /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm --include="*.php"

# Search for rname usage
grep -r "\$.*\['rname'\]" /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm --include="*.php"
grep -r "'rname'" /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm --include="*.php"
grep -r '"rname"' /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm --include="*.php"
```

---

## Phase 7: Testing & Verification

### Step 7.1: Unit Tests
- Verify `Roles::get_all()` returns `role_id` and `role` fields
- Verify `Roles::get_role_name()` works with new column
- Verify `Roles::get_role_names()` renders correctly
- Verify `RolesList` view displays columns correctly

### Step 7.2: Integration Tests
- Create a role with new column names
- Edit a role with new column names
- Assign permissions using new column names
- Verify role dropdown renders correctly

### Step 7.3: Manual Testing
- Navigate to roles management page
- Create new role
- Edit existing role
- Delete role
- Assign permissions to role

---

## Implementation Order

**Recommended sequence** to avoid breaking code:

1. ✅ Create migration SQL file
2. ✅ Update `/sql/democrm_democrm_structure.sql`
3. ✅ Update Model classes (`Roles.php`, `RolesPermissions.php`)
4. ✅ Update View classes (`RolesList.php`, `RolesPermissionsList.php`)
5. ✅ Update Page files (`edit_role.php`, `assign_role_permissions.php`)
6. ✅ Update Language files
7. ✅ Execute migration on database
8. ✅ Run tests and verify
9. ✅ Manual testing

---

## Rollback Plan

If issues arise, rollback migration:

```sql
-- Rollback to old column names
ALTER TABLE `roles`
  DROP KEY `role`,
  DROP KEY `role_id`;

ALTER TABLE `roles`
  CHANGE COLUMN `role_id` `rid` INT(11) NOT NULL,
  CHANGE COLUMN `role` `rname` VARCHAR(50) NOT NULL;

ALTER TABLE `roles`
  ADD UNIQUE KEY `rname` (`rname`),
  ADD UNIQUE KEY `rid` (`rid`) USING BTREE;

ALTER TABLE `roles_permissions`
  CHANGE COLUMN `role_id` `rid` INT(11) NOT NULL;

ALTER TABLE `roles_permissions`
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (`rid`, `pid`);
```

---

## Notes

- **Backward Compatibility**: This is a breaking change for any API consumers expecting `rid` and `rname`
- **Language Keys**: Ensure language files have both `'role_id'` and `'role'` keys defined
- **Foreign Keys**: Check if other tables reference these columns via foreign keys
- **Git History**: Consider this a major refactoring commit with clear commit message
- **Documentation**: Update API documentation after migration complete

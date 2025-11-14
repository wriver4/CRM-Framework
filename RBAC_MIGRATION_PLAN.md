# RBAC Enhancement Migration Plan
## Module + Action + Field + Record Level Permissions

---

## 📋 Executive Summary

This document outlines the migration plan to enhance the existing RBAC system to support **4 levels of granularity**:

1. **Module Level** - Can access Leads, Contacts, etc.
2. **Action Level** - Can view/create/edit/delete/export per module
3. **Field Level** - Can view/edit specific fields
4. **Record Level** - Ownership-based access (own/team/all)

---

## 🔍 Current State Analysis

### Existing Schema

```sql
-- Current permissions table
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `pobject` varchar(15) NOT NULL,
  `pdescription` varchar(100) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Current roles table
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `rid` int(11) NOT NULL,
  `rname` varchar(50) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Current roles_permissions bridge table
CREATE TABLE `roles_permissions` (
  `rid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Current Security Class

- Basic permission checking in `Security::check_user_permissions()`
- Simple role-based logic (admin vs user)
- No granular permission enforcement
- No field-level or record-level controls

---

## 🎯 Target State

### Enhanced Permission Structure

Permissions will follow a **hierarchical dot notation**:

```
{module}.{action}.{field/scope}

Examples:
- leads.access                    (Module level)
- leads.view                      (Action level)
- leads.create                    (Action level)
- leads.edit                      (Action level)
- leads.delete                    (Action level)
- leads.export                    (Action level)
- leads.view.email                (Field level - view)
- leads.edit.stage                (Field level - edit)
- leads.view.own                  (Record level - ownership)
- leads.view.team                 (Record level - team)
- leads.view.all                  (Record level - all records)
- leads.edit.own                  (Record level - ownership)
- leads.edit.team                 (Record level - team)
- leads.edit.all                  (Record level - all records)
```

---

## 📊 Migration Steps

### Phase 1: Schema Enhancement (Week 1)

#### Step 1.1: Enhance Permissions Table

```sql
-- Add new columns to permissions table
ALTER TABLE `permissions` 
  ADD COLUMN `module` VARCHAR(50) NULL AFTER `id`,
  ADD COLUMN `action` VARCHAR(50) NULL AFTER `module`,
  ADD COLUMN `field` VARCHAR(100) NULL AFTER `action`,
  ADD COLUMN `scope` ENUM('own', 'team', 'all', 'none') NULL DEFAULT 'none' AFTER `field`,
  ADD COLUMN `permission_type` ENUM('module', 'action', 'field', 'record') NOT NULL DEFAULT 'module' AFTER `scope`,
  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `permission_type`,
  MODIFY COLUMN `pobject` VARCHAR(100) NOT NULL COMMENT 'Full permission string (e.g., leads.view.email)',
  ADD INDEX `idx_module` (`module`),
  ADD INDEX `idx_action` (`action`),
  ADD INDEX `idx_type` (`permission_type`),
  ADD INDEX `idx_active` (`is_active`);
```

#### Step 1.2: Enhance Roles Table

```sql
-- Add role hierarchy and metadata
ALTER TABLE `roles`
  ADD COLUMN `parent_role_id` INT(11) NULL AFTER `id` COMMENT 'For role hierarchy',
  ADD COLUMN `role_level` INT(11) NOT NULL DEFAULT 0 AFTER `parent_role_id` COMMENT 'Hierarchy level (0=top)',
  ADD COLUMN `is_system_role` TINYINT(1) NOT NULL DEFAULT 0 AFTER `role_level` COMMENT 'System roles cannot be deleted',
  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_system_role`,
  ADD COLUMN `description` TEXT NULL AFTER `rname`,
  ADD INDEX `idx_parent` (`parent_role_id`),
  ADD INDEX `idx_level` (`role_level`),
  ADD INDEX `idx_active` (`is_active`),
  ADD FOREIGN KEY `fk_parent_role` (`parent_role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL;
```

#### Step 1.3: Create Field Permissions Table

```sql
-- New table for field-level permissions
CREATE TABLE `field_permissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_id` INT(11) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `field_name` VARCHAR(100) NOT NULL,
  `can_view` TINYINT(1) NOT NULL DEFAULT 0,
  `can_edit` TINYINT(1) NOT NULL DEFAULT 0,
  `is_required` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Field is required for this role',
  `is_hidden` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Field is hidden for this role',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_module_field` (`role_id`, `module`, `field_name`),
  INDEX `idx_role` (`role_id`),
  INDEX `idx_module` (`module`),
  INDEX `idx_field` (`field_name`),
  FOREIGN KEY `fk_field_perm_role` (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Field-level permissions per role';
```

#### Step 1.4: Create Record Ownership Table

```sql
-- New table for record ownership tracking
CREATE TABLE `record_ownership` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(50) NOT NULL,
  `record_id` INT(11) NOT NULL,
  `owner_user_id` INT(11) NOT NULL,
  `team_id` INT(11) NULL COMMENT 'For team-based access',
  `is_shared` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Shared with other users',
  `shared_with_users` TEXT NULL COMMENT 'JSON array of user IDs',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_module_record` (`module`, `record_id`),
  INDEX `idx_owner` (`owner_user_id`),
  INDEX `idx_team` (`team_id`),
  INDEX `idx_module_record` (`module`, `record_id`),
  FOREIGN KEY `fk_owner_user` (`owner_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Record ownership for granular access control';
```

#### Step 1.5: Create Permission Cache Table

```sql
-- Cache table for performance optimization
CREATE TABLE `permission_cache` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `permission_key` VARCHAR(200) NOT NULL,
  `has_permission` TINYINT(1) NOT NULL,
  `cache_data` JSON NULL COMMENT 'Additional permission metadata',
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`, `permission_key`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_expires` (`expires_at`),
  FOREIGN KEY `fk_cache_user` (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Permission cache for performance';
```

#### Step 1.6: Create Teams Table (for team-based access)

```sql
-- Teams table for team-based record access
CREATE TABLE `teams` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `team_name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `manager_user_id` INT(11) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_manager` (`manager_user_id`),
  INDEX `idx_active` (`is_active`),
  FOREIGN KEY `fk_team_manager` (`manager_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Teams for team-based access control';

-- Team members bridge table
CREATE TABLE `team_members` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `team_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `is_manager` TINYINT(1) NOT NULL DEFAULT 0,
  `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_user` (`team_id`, `user_id`),
  INDEX `idx_team` (`team_id`),
  INDEX `idx_user` (`user_id`),
  FOREIGN KEY `fk_tm_team` (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY `fk_tm_user` (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Team membership';
```

---

### Phase 2: Data Migration (Week 1-2)

#### Step 2.1: Migrate Existing Permissions

```sql
-- Backup existing permissions
CREATE TABLE `permissions_backup` AS SELECT * FROM `permissions`;

-- Update existing permissions with new structure
UPDATE `permissions` SET
  `module` = SUBSTRING_INDEX(`pobject`, '.', 1),
  `action` = SUBSTRING_INDEX(SUBSTRING_INDEX(`pobject`, '.', 2), '.', -1),
  `permission_type` = CASE
    WHEN `pobject` NOT LIKE '%.%' THEN 'module'
    WHEN `pobject` LIKE '%.%.%' THEN 'field'
    ELSE 'action'
  END
WHERE `pobject` LIKE '%.%';
```

#### Step 2.2: Seed Standard Permissions

```sql
-- Insert standard module permissions
INSERT INTO `permissions` (`pobject`, `module`, `action`, `permission_type`, `pdescription`) VALUES
-- Leads module
('leads.access', 'leads', 'access', 'module', 'Access leads module'),
('leads.view', 'leads', 'view', 'action', 'View leads'),
('leads.create', 'leads', 'create', 'action', 'Create leads'),
('leads.edit', 'leads', 'edit', 'action', 'Edit leads'),
('leads.delete', 'leads', 'delete', 'action', 'Delete leads'),
('leads.export', 'leads', 'export', 'action', 'Export leads'),

-- Contacts module
('contacts.access', 'contacts', 'access', 'module', 'Access contacts module'),
('contacts.view', 'contacts', 'view', 'action', 'View contacts'),
('contacts.create', 'contacts', 'create', 'action', 'Create contacts'),
('contacts.edit', 'contacts', 'edit', 'action', 'Edit contacts'),
('contacts.delete', 'contacts', 'delete', 'action', 'Delete contacts'),
('contacts.export', 'contacts', 'export', 'action', 'Export contacts'),

-- Admin module
('admin.access', 'admin', 'access', 'module', 'Access admin module'),
('admin.users', 'admin', 'users', 'action', 'Manage users'),
('admin.roles', 'admin', 'roles', 'action', 'Manage roles'),
('admin.permissions', 'admin', 'permissions', 'action', 'Manage permissions'),
('admin.settings', 'admin', 'settings', 'action', 'Manage settings');

-- Insert field-level permissions
INSERT INTO `permissions` (`pobject`, `module`, `action`, `field`, `permission_type`, `pdescription`) VALUES
('leads.view.email', 'leads', 'view', 'email', 'field', 'View lead email'),
('leads.edit.email', 'leads', 'edit', 'email', 'field', 'Edit lead email'),
('leads.view.phone', 'leads', 'view', 'phone', 'field', 'View lead phone'),
('leads.edit.phone', 'leads', 'edit', 'phone', 'field', 'Edit lead phone'),
('leads.view.stage', 'leads', 'view', 'stage', 'field', 'View lead stage'),
('leads.edit.stage', 'leads', 'edit', 'stage', 'field', 'Edit lead stage'),
('leads.view.notes', 'leads', 'view', 'notes', 'field', 'View lead notes'),
('leads.edit.notes', 'leads', 'edit', 'notes', 'field', 'Edit lead notes');

-- Insert record-level permissions
INSERT INTO `permissions` (`pobject`, `module`, `action`, `scope`, `permission_type`, `pdescription`) VALUES
('leads.view.own', 'leads', 'view', 'own', 'record', 'View own leads'),
('leads.view.team', 'leads', 'view', 'team', 'record', 'View team leads'),
('leads.view.all', 'leads', 'view', 'all', 'record', 'View all leads'),
('leads.edit.own', 'leads', 'edit', 'own', 'record', 'Edit own leads'),
('leads.edit.team', 'leads', 'edit', 'team', 'record', 'Edit team leads'),
('leads.edit.all', 'leads', 'edit', 'all', 'record', 'Edit all leads'),
('leads.delete.own', 'leads', 'delete', 'own', 'record', 'Delete own leads'),
('leads.delete.team', 'leads', 'delete', 'team', 'record', 'Delete team leads'),
('leads.delete.all', 'leads', 'delete', 'all', 'record', 'Delete all leads');
```

#### Step 2.3: Create Standard Roles

```sql
-- Insert standard roles with hierarchy
INSERT INTO `roles` (`rname`, `parent_role_id`, `role_level`, `is_system_role`, `description`) VALUES
('Super Admin', NULL, 0, 1, 'Full system access - cannot be modified'),
('Administrator', 1, 1, 1, 'Administrative access to all modules'),
('Sales Manager', 2, 2, 0, 'Manage sales team and all leads'),
('Sales Representative', 3, 3, 0, 'Manage own leads and team leads'),
('Viewer', 2, 2, 0, 'Read-only access to assigned records'),
('Restricted', NULL, 0, 0, 'Minimal access for testing');
```

---

### Phase 3: Core RBAC Service Implementation (Week 2)

#### Step 3.1: Create RbacService Class

**File:** `/classes/Core/RbacService.php`

```php
<?php

class RbacService extends Database
{
    protected $permissionCache = [];
    protected $cacheEnabled = true;
    protected $cacheTTL = 3600; // 1 hour
    
    /**
     * Check if user has permission
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        // Check cache first
        if ($this->cacheEnabled && isset($this->permissionCache[$userId][$permission])) {
            return $this->permissionCache[$userId][$permission];
        }
        
        // Check database
        $result = $this->checkPermissionInDatabase($userId, $permission);
        
        // Cache result
        if ($this->cacheEnabled) {
            $this->permissionCache[$userId][$permission] = $result;
        }
        
        return $result;
    }
    
    /**
     * Check module-level permission
     */
    public function canAccessModule(int $userId, string $module): bool
    {
        return $this->hasPermission($userId, "$module.access");
    }
    
    /**
     * Check action-level permission
     */
    public function canPerformAction(int $userId, string $module, string $action): bool
    {
        return $this->hasPermission($userId, "$module.$action");
    }
    
    /**
     * Check field-level permission
     */
    public function canAccessField(int $userId, string $module, string $field, string $action = 'view'): bool
    {
        return $this->hasPermission($userId, "$module.$action.$field");
    }
    
    /**
     * Check record-level permission
     */
    public function canAccessRecord(int $userId, string $module, int $recordId, string $action = 'view'): bool
    {
        // Check if user has 'all' scope
        if ($this->hasPermission($userId, "$module.$action.all")) {
            return true;
        }
        
        // Check ownership
        $ownership = $this->getRecordOwnership($module, $recordId);
        
        if ($ownership['owner_user_id'] === $userId) {
            return $this->hasPermission($userId, "$module.$action.own");
        }
        
        // Check team access
        if ($ownership['team_id'] && $this->isUserInTeam($userId, $ownership['team_id'])) {
            return $this->hasPermission($userId, "$module.$action.team");
        }
        
        return false;
    }
    
    // ... additional methods
}
```

---

### Phase 4: Security Class Enhancement (Week 2-3)

Update `/classes/Core/Security.php` to use RbacService:

```php
public function check_user_permissions($module, $action, $redirect = true)
{
    if (!isset($_SESSION['loggedin'])) {
        if ($redirect) {
            header("Location: /login");
            exit;
        }
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    $rbac = new RbacService();
    
    // Check module access first
    if (!$rbac->canAccessModule($userId, $module)) {
        if ($redirect) {
            header("Location: /access_denied.php");
            exit;
        }
        return false;
    }
    
    // Check action permission
    if (!$rbac->canPerformAction($userId, $module, $action)) {
        if ($redirect) {
            header("Location: /access_denied.php");
            exit;
        }
        return false;
    }
    
    return true;
}
```

---

### Phase 5: UI Implementation (Week 3-4)

#### SuiteCRM-Style Permission Matrix

**File:** `/public_html/admin/rbac/permission_matrix.php`

Features:
- Grid layout: Modules × Actions
- Checkbox toggles for quick assignment
- Bulk operations
- Visual inheritance indicators
- Field-level permission drill-down
- Record-level scope selectors

---

## 🧪 Testing Strategy

### Unit Tests

```php
// Test permission checking
public function testUserHasModulePermission()
{
    $rbac = new RbacService();
    $this->assertTrue($rbac->canAccessModule($this->adminUserId, 'leads'));
    $this->assertFalse($rbac->canAccessModule($this->restrictedUserId, 'admin'));
}

// Test field-level permissions
public function testUserCanViewField()
{
    $rbac = new RbacService();
    $this->assertTrue($rbac->canAccessField($this->salesRepId, 'leads', 'email', 'view'));
    $this->assertFalse($rbac->canAccessField($this->viewerId, 'leads', 'email', 'edit'));
}

// Test record-level permissions
public function testUserCanAccessOwnRecords()
{
    $rbac = new RbacService();
    $this->assertTrue($rbac->canAccessRecord($this->salesRepId, 'leads', $this->ownLeadId, 'edit'));
    $this->assertFalse($rbac->canAccessRecord($this->salesRepId, 'leads', $this->otherLeadId, 'edit'));
}
```

### Integration Tests

- Test role hierarchy inheritance
- Test permission caching
- Test team-based access
- Test field visibility in forms

### E2E Tests (Playwright)

- Test permission matrix UI
- Test role assignment workflow
- Test access denial scenarios
- Test field hiding based on permissions

---

## 📈 Performance Considerations

1. **Permission Caching**
   - In-memory cache per request
   - Database cache table for persistence
   - Cache invalidation on permission changes

2. **Query Optimization**
   - Indexed permission lookups
   - Batch permission checks
   - Lazy loading of field permissions

3. **Database Optimization**
   - Proper indexing on all foreign keys
   - Composite indexes for common queries
   - Query result caching

---

## 🔄 Rollback Plan

1. **Database Rollback**
   ```sql
   -- Restore from backup
   DROP TABLE IF EXISTS `permissions`;
   CREATE TABLE `permissions` AS SELECT * FROM `permissions_backup`;
   ```

2. **Code Rollback**
   - Revert Security class changes
   - Remove RbacService class
   - Restore original permission checking

3. **Data Integrity**
   - Verify all foreign key constraints
   - Validate user access after rollback
   - Test critical workflows

---

## 📅 Timeline

| Phase                         | Duration      | Deliverables                      |
| ----------------------------- | ------------- | --------------------------------- |
| Phase 1: Schema               | 3-5 days      | Enhanced database schema          |
| Phase 2: Migration            | 3-5 days      | Migrated data, seeded permissions |
| Phase 3: Core Service         | 5-7 days      | RbacService implementation        |
| Phase 4: Security Enhancement | 3-5 days      | Updated Security class            |
| Phase 5: UI                   | 7-10 days     | Permission matrix interface       |
| Testing                       | 5-7 days      | Comprehensive test coverage       |
| **Total**                     | **4-6 weeks** | Fully functional RBAC system      |

---

## ✅ Success Criteria

1. ✅ All 4 permission levels working correctly
2. ✅ SuiteCRM-style permission matrix functional
3. ✅ Role hierarchy properly enforced
4. ✅ Field-level permissions hiding/showing fields
5. ✅ Record-level permissions enforcing ownership
6. ✅ Performance within acceptable limits (<100ms for permission checks)
7. ✅ 100% test coverage for RBAC functionality
8. ✅ Zero security vulnerabilities
9. ✅ Complete documentation
10. ✅ User training materials created

---

## 📝 Next Steps

1. **Review and approve this migration plan**
2. **Set up test database for RBAC development**
3. **Begin Phase 1: Schema Enhancement**
4. **Create comprehensive test suite**
5. **Implement core RBAC service**
6. **Build permission matrix UI**
7. **Conduct security audit**
8. **Deploy to production**

---

**Document Version:** 1.0  
**Last Updated:** January 2025  
**Status:** Ready for Implementation
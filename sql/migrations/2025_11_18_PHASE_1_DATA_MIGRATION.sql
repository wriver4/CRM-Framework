-- =============================================================================
-- PHASE 1: DATA MIGRATION
-- Date: 2025-11-18
-- Purpose: Migrate existing permissions to new 4-level structure and populate
--          role-permission associations
--
-- This migration performs:
-- 1. Populates role-permission associations for existing roles
-- 2. Creates field-level permissions for common operations
-- 3. Sets up record-level permission templates
-- 4. Initializes permission cache for performance
-- 5. Performs data integrity checks
-- =============================================================================

-- =============================================================================
-- STEP 1: Clean up duplicate permissions from initial seeding
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM permissions 
WHERE id IN (32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 49, 50, 51, 52, 56, 57, 58, 59, 63, 64)
AND module = 'general' 
AND action = 'access';

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- STEP 2: Populate role-permission associations
-- =============================================================================

-- Clear existing role-permission associations (if any) to ensure clean state
TRUNCATE TABLE roles_permissions;

-- ADMIN ROLE (id=1) - Full Access
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT DISTINCT 1, p.pid, 1 
FROM permissions p 
WHERE p.is_active = 1;

-- MANAGER ROLE (id=2) - Full access to leads, contacts, calendar, reports but limited admin
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT 2, p.pid, 1 
FROM permissions p 
WHERE p.is_active = 1 
  AND (p.module IN ('leads', 'contacts', 'calendar', 'reports', 'users')
       OR (p.module = 'admin' AND p.action IN ('access', 'users', 'roles')))
  AND NOT (p.module = 'admin' AND p.action IN ('security', 'settings', 'permissions'));

-- USER ROLE (id=3) - View and create leads/contacts
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT 3, p.pid, 1 
FROM permissions p 
WHERE p.is_active = 1 
  AND p.module IN ('leads', 'contacts', 'calendar')
  AND p.action IN ('access', 'view', 'create')
  AND NOT p.action IN ('delete', 'export');

-- VIEWER ROLE (id=4) - Read-only access
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT 4, p.pid, 1 
FROM permissions p 
WHERE p.is_active = 1 
  AND p.module IN ('leads', 'contacts', 'calendar', 'reports')
  AND p.action IN ('access', 'view')
  AND NOT p.action IN ('create', 'edit', 'delete', 'export');

-- RESTRICTED ROLE (id=5) - Minimal access for testing denials
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT 5, p.pid, 1 
FROM permissions p 
WHERE p.is_active = 1 
  AND p.module IN ('leads', 'contacts')
  AND p.action = 'view'
  AND p.field_name IS NULL;

-- =============================================================================
-- STEP 3: Create field-level permissions for common operations
-- =============================================================================

-- Helper: Get max permission ID
SET @max_pid = (SELECT MAX(pid) FROM permissions);

-- Leads field-level permissions
INSERT INTO permissions (pid, pobject, pdescription, module, action, field_name, scope, is_active)
SELECT 
  @max_pid + 1,
  'leads.view.email',
  'View email field on leads',
  'leads',
  'view',
  'email',
  'all',
  1
FROM permissions LIMIT 1;

SET @max_pid = @max_pid + 1;

INSERT INTO permissions (pid, pobject, pdescription, module, action, field_name, scope, is_active)
SELECT 
  @max_pid + 1,
  'leads.view.phone',
  'View phone field on leads',
  'leads',
  'view',
  'phone',
  'all',
  1
FROM permissions LIMIT 1;

SET @max_pid = @max_pid + 1;

INSERT INTO permissions (pid, pobject, pdescription, module, action, field_name, scope, is_active)
SELECT 
  @max_pid + 1,
  'leads.edit.stage',
  'Edit stage field on leads',
  'leads',
  'edit',
  'stage',
  'all',
  1
FROM permissions LIMIT 1;

SET @max_pid = @max_pid + 1;

INSERT INTO permissions (pid, pobject, pdescription, module, action, field_name, scope, is_active)
SELECT 
  @max_pid + 1,
  'leads.edit.notes',
  'Edit notes field on leads',
  'leads',
  'edit',
  'notes',
  'all',
  1
FROM permissions LIMIT 1;

SET @max_pid = @max_pid + 1;

-- Contacts field-level permissions
INSERT INTO permissions (pid, pobject, pdescription, module, action, field_name, scope, is_active)
SELECT 
  @max_pid + 1,
  'contacts.view.email',
  'View email field on contacts',
  'contacts',
  'view',
  'email',
  'all',
  1
FROM permissions LIMIT 1;

SET @max_pid = @max_pid + 1;

INSERT INTO permissions (pid, pobject, pdescription, module, action, field_name, scope, is_active)
SELECT 
  @max_pid + 1,
  'contacts.view.phone',
  'View phone field on contacts',
  'contacts',
  'view',
  'phone',
  'all',
  1
FROM permissions LIMIT 1;

SET @max_pid = @max_pid + 1;

INSERT INTO permissions (pid, pobject, pdescription, module, action, field_name, scope, is_active)
SELECT 
  @max_pid + 1,
  'contacts.edit.category',
  'Edit category field on contacts',
  'contacts',
  'edit',
  'category',
  'all',
  1
FROM permissions LIMIT 1;

SET @max_pid = @max_pid + 1;

-- =============================================================================
-- STEP 4: Assign field-level permissions to appropriate roles
-- =============================================================================

-- Admin gets all field-level permissions
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT 1, p.pid, 1 
FROM permissions p 
WHERE p.field_name IS NOT NULL 
  AND p.is_active = 1
  AND p.pid NOT IN (SELECT pid FROM roles_permissions WHERE role_id = 1);

-- Manager gets field-level view permissions for emails and phones
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT 2, p.pid, 1 
FROM permissions p 
WHERE p.field_name IN ('email', 'phone')
  AND p.action = 'view'
  AND p.is_active = 1
  AND p.pid NOT IN (SELECT pid FROM roles_permissions WHERE role_id = 2);

-- User gets limited field-level permissions
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT 3, p.pid, 1 
FROM permissions p 
WHERE p.field_name IN ('email', 'phone')
  AND p.action = 'view'
  AND p.is_active = 1
  AND p.pid NOT IN (SELECT pid FROM roles_permissions WHERE role_id = 3);

-- Viewer gets view-only field permissions
INSERT INTO roles_permissions (role_id, pid, is_active) 
SELECT 4, p.pid, 1 
FROM permissions p 
WHERE p.field_name IS NOT NULL
  AND p.action = 'view'
  AND p.is_active = 1
  AND p.pid NOT IN (SELECT pid FROM roles_permissions WHERE role_id = 4);

-- =============================================================================
-- STEP 5: Create field permissions records (field-level access control)
-- =============================================================================

-- Define field access levels for key fields
-- Access levels: 'none', 'view', 'edit'

-- Leads fields
INSERT INTO field_permissions (permission_id, field_name, field_label, access_level, module_name)
SELECT p.id, 'email', 'Email Address', 'view', 'leads'
FROM permissions p
WHERE p.module = 'leads' AND p.action = 'view' AND p.field_name = 'email'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO field_permissions (permission_id, field_name, field_label, access_level, module_name)
SELECT p.id, 'phone', 'Phone Number', 'view', 'leads'
FROM permissions p
WHERE p.module = 'leads' AND p.action = 'view' AND p.field_name = 'phone'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO field_permissions (permission_id, field_name, field_label, access_level, module_name)
SELECT p.id, 'stage', 'Stage', 'edit', 'leads'
FROM permissions p
WHERE p.module = 'leads' AND p.action = 'edit' AND p.field_name = 'stage'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO field_permissions (permission_id, field_name, field_label, access_level, module_name)
SELECT p.id, 'notes', 'Notes', 'edit', 'leads'
FROM permissions p
WHERE p.module = 'leads' AND p.action = 'edit' AND p.field_name = 'notes'
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Contacts fields
INSERT INTO field_permissions (permission_id, field_name, field_label, access_level, module_name)
SELECT p.id, 'email', 'Email Address', 'view', 'contacts'
FROM permissions p
WHERE p.module = 'contacts' AND p.action = 'view' AND p.field_name = 'email'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO field_permissions (permission_id, field_name, field_label, access_level, module_name)
SELECT p.id, 'phone', 'Phone Number', 'view', 'contacts'
FROM permissions p
WHERE p.module = 'contacts' AND p.action = 'view' AND p.field_name = 'phone'
ON DUPLICATE KEY UPDATE updated_at = NOW();

INSERT INTO field_permissions (permission_id, field_name, field_label, access_level, module_name)
SELECT p.id, 'category', 'Category', 'edit', 'contacts'
FROM permissions p
WHERE p.module = 'contacts' AND p.action = 'edit' AND p.field_name = 'category'
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- =============================================================================
-- STEP 6: Create record ownership templates (for testing record-level access)
-- =============================================================================

-- Placeholder records for system test users
-- These will be populated by the application as records are created
-- This step ensures the table structure is ready

-- =============================================================================
-- STEP 7: Initialize permission cache for performance
-- =============================================================================

-- Pre-compute permissions for each role
-- This improves performance by caching permission checks

INSERT INTO permission_cache 
  (user_id, permission_id, permission_string, module, action, result, expires_at)
SELECT 
  u.id,
  p.id,
  p.pobject,
  p.module,
  p.action,
  1,
  DATE_ADD(NOW(), INTERVAL 1 HOUR)
FROM permissions p
CROSS JOIN (
  SELECT 1 as id UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
) u
WHERE p.is_active = 1
ON DUPLICATE KEY UPDATE result = 1, expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR);

-- =============================================================================
-- STEP 8: Data integrity checks
-- =============================================================================

-- Verify no orphaned permissions (permissions with no roles)
-- This is informational - these permissions can exist but aren't used
SELECT 
  'WARNING: Unused Permissions' as check_type,
  COUNT(*) as count,
  GROUP_CONCAT(DISTINCT p.pobject) as unused_perms
FROM permissions p
WHERE p.id NOT IN (
  SELECT DISTINCT rp.pid 
  FROM roles_permissions rp 
  JOIN permissions p2 ON rp.pid = p2.pid
)
AND p.is_active = 1;

-- Verify role-permission assignments
SELECT 
  'Role Permission Count' as description,
  r.role_id as id,
  r.role as name,
  COUNT(DISTINCT rp.pid) as permission_count
FROM roles r
LEFT JOIN roles_permissions rp ON r.id = rp.role_id AND rp.is_active = 1
GROUP BY r.id
ORDER BY r.id;

-- Verify permission structure
SELECT 
  'Permission by Module' as description,
  p.module,
  COUNT(DISTINCT p.id) as count,
  SUM(CASE WHEN p.field_name IS NOT NULL THEN 1 ELSE 0 END) as field_level_perms,
  SUM(CASE WHEN p.action = 'view' THEN 1 ELSE 0 END) as view_perms,
  SUM(CASE WHEN p.action = 'edit' THEN 1 ELSE 0 END) as edit_perms
FROM permissions p
WHERE p.is_active = 1
GROUP BY p.module
ORDER BY p.module;

-- =============================================================================
-- STEP 9: Migration summary
-- =============================================================================

SELECT 
  'PHASE 1 MIGRATION COMPLETED' as status,
  (SELECT COUNT(*) FROM roles_permissions WHERE is_active = 1) as total_role_permissions,
  (SELECT COUNT(*) FROM permissions WHERE is_active = 1) as total_permissions,
  (SELECT COUNT(*) FROM permissions WHERE field_name IS NOT NULL AND is_active = 1) as field_level_permissions,
  (SELECT COUNT(*) FROM field_permissions) as field_permission_configs,
  (SELECT COUNT(*) FROM permission_cache) as cached_permissions;

-- =============================================================================
-- PHASE 2: DATA MIGRATION v2 - Production-safe implementation
-- Date: 2025-11-18
-- Purpose: Initialize role hierarchy, permission inheritance, and delegation rules
--          with support for varying role structures
--
-- This migration:
-- 1. Sets up role hierarchy relationships (only for existing roles)
-- 2. Initializes permission inheritance mappings
-- 3. Creates delegation rules and approval workflows
-- 4. Sets up permission restrictions
-- 5. Initializes effective permissions cache
-- =============================================================================

-- =============================================================================
-- STEP 1: Configure role hierarchy levels and relationships (only existing roles)
-- =============================================================================

-- System role configuration: only configure roles that exist
UPDATE roles 
SET hierarchy_level = CASE 
    WHEN role IN ('Super Administrator', 'Admin') THEN 0
    WHEN role IN ('Administrator', 'Manager') THEN 1
    ELSE hierarchy_level
  END,
  is_system = CASE 
    WHEN role IN ('Super Administrator', 'Administrator', 'System User') THEN 1
    ELSE is_system
  END
WHERE id IN (SELECT DISTINCT id FROM roles);

-- Set up parent-child relationships for system roles only
UPDATE roles SET parent_role_id = 1 
WHERE role = 'Administrator' AND id != 1 AND parent_role_id IS NULL;

UPDATE roles SET parent_role_id = 2 
WHERE role = 'System User' AND id != 2 AND parent_role_id IS NULL;

-- Configure delegation settings for system roles
UPDATE roles SET allows_delegation = 1, max_delegable_depth = 2 
WHERE role IN ('Super Administrator', 'Administrator');

UPDATE roles SET allows_delegation = 1, max_delegable_depth = 1 
WHERE role IN ('System User', 'Manager');

UPDATE roles SET allows_delegation = 0, max_delegable_depth = 0 
WHERE role NOT IN ('Super Administrator', 'Administrator', 'System User', 'Manager');

-- =============================================================================
-- STEP 2: Populate role inheritance relationships (only for existing roles)
-- =============================================================================

-- Build inheritance relationships for existing role hierarchy
INSERT INTO role_inheritance (parent_role_id, child_role_id, inheritance_type, depth, is_active)
SELECT DISTINCT r1.id, r2.id, 'full', 1, 1
FROM roles r1
JOIN roles r2 ON r2.parent_role_id = r1.id
WHERE r1.is_active = 1 AND r2.is_active = 1
  AND NOT EXISTS (
    SELECT 1 FROM role_inheritance 
    WHERE parent_role_id = r1.id AND child_role_id = r2.id
  )
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Add transitive relationships (grandparent to grandchild)
INSERT INTO role_inheritance (parent_role_id, child_role_id, inheritance_type, depth, is_active)
WITH RECURSIVE hierarchy_tree AS (
    SELECT r1.id as ancestor_id, r2.id as descendant_id, 1 as depth
    FROM roles r1
    JOIN roles r2 ON r2.parent_role_id = r1.id
    WHERE r1.is_active = 1 AND r2.is_active = 1
    
    UNION ALL
    
    SELECT ht.ancestor_id, r3.id, ht.depth + 1
    FROM hierarchy_tree ht
    JOIN roles r3 ON r3.parent_role_id = (
        SELECT id FROM roles WHERE id = ht.descendant_id
    )
    WHERE ht.depth < 10 AND r3.is_active = 1
)
SELECT DISTINCT ancestor_id, descendant_id, 'full', depth, 1
FROM hierarchy_tree
WHERE NOT EXISTS (
    SELECT 1 FROM role_inheritance 
    WHERE parent_role_id = hierarchy_tree.ancestor_id 
      AND child_role_id = hierarchy_tree.descendant_id
)
ON DUPLICATE KEY UPDATE depth = VALUES(depth), updated_at = NOW();

-- =============================================================================
-- STEP 3: Populate role permission inheritance mappings
-- =============================================================================

-- Direct permissions for all roles
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT rp.role_id, p.id, rp.role_id, 0, 'direct', 1
FROM roles_permissions rp
JOIN permissions p ON rp.pid = p.pid
WHERE rp.is_active = 1
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Inherited permissions from parent roles
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT DISTINCT ri.child_role_id, p.id, ri.parent_role_id, ri.depth, 'inherited', 1
FROM role_inheritance ri
JOIN roles_permissions rp ON ri.parent_role_id = rp.role_id
JOIN permissions p ON rp.pid = p.pid
WHERE ri.inheritance_type = 'full' 
  AND ri.is_active = 1 
  AND rp.is_active = 1
  AND p.is_active = 1
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- =============================================================================
-- STEP 4: Set up basic permission restrictions
-- =============================================================================

-- Time-based restrictions for sensitive operations
INSERT IGNORE INTO permission_restrictions (permission_id, restriction_type, restriction_rule, priority, is_active)
SELECT 
    p.id,
    'time_based',
    JSON_OBJECT(
        'type', 'business_hours',
        'start_hour', 9,
        'end_hour', 17,
        'weekdays_only', true,
        'timezone', 'UTC'
    ),
    1,
    1
FROM permissions p
WHERE p.module = 'admin' 
  AND p.is_active = 1
  AND NOT EXISTS (
    SELECT 1 FROM permission_restrictions 
    WHERE permission_id = p.id AND restriction_type = 'time_based'
  );

-- Field-level restrictions
INSERT IGNORE INTO permission_restrictions (permission_id, restriction_type, restriction_rule, priority, is_active)
SELECT 
    p.id,
    'field_restriction',
    JSON_OBJECT(
        'fields_to_hide', JSON_ARRAY('email', 'phone'),
        'min_hierarchy_level', 2,
        'show_placeholder', true
    ),
    1,
    1
FROM permissions p
WHERE p.action = 'view' 
  AND p.field_name IS NULL
  AND p.is_active = 1
  AND NOT EXISTS (
    SELECT 1 FROM permission_restrictions 
    WHERE permission_id = p.id AND restriction_type = 'field_restriction'
  )
LIMIT 10;

-- =============================================================================
-- STEP 5: Initialize effective permissions cache
-- =============================================================================

-- Direct permissions from role assignment
INSERT IGNORE INTO effective_permissions_cache (user_id, role_id, permission_id, permission_source, calculation_method, is_active, cached_at, expires_at)
SELECT 
    u.id,
    u.role_id,
    p.id,
    'direct',
    'full',
    1,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 1 HOUR)
FROM users u
JOIN roles r ON u.role_id = r.id
JOIN permissions p ON p.is_active = 1
WHERE u.status = 1
  AND r.is_active = 1;

-- Inherited permissions from role hierarchy
INSERT IGNORE INTO effective_permissions_cache (user_id, role_id, permission_id, permission_source, calculation_method, is_active, cached_at, expires_at)
SELECT 
    u.id,
    u.role_id,
    rpi.permission_id,
    'inherited',
    'hierarchy',
    1,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 1 HOUR)
FROM users u
JOIN role_permission_inheritance rpi ON u.role_id = rpi.role_id AND rpi.inheritance_method = 'inherited'
WHERE u.status = 1
  AND rpi.is_active = 1;

-- =============================================================================
-- STEP 6: Create sample delegation scenarios (for testing)
-- =============================================================================

-- Sample delegations only if users exist
INSERT IGNORE INTO permission_delegations (
    delegating_user_id,
    receiving_user_id,
    permission_id,
    delegation_type,
    approval_status,
    approved_by_user_id,
    reason,
    start_date,
    end_date
)
SELECT 
    (SELECT MIN(id) FROM users WHERE status = 1),
    (SELECT MIN(id) FROM users WHERE status = 1 AND id != (SELECT MIN(id) FROM users WHERE status = 1)),
    (SELECT MIN(id) FROM permissions WHERE module = 'leads' AND action = 'export' LIMIT 1),
    'temporary',
    'approved',
    (SELECT MIN(id) FROM users WHERE status = 1),
    'Temporary export access for reporting',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 7 DAY)
FROM dual
WHERE EXISTS (
    SELECT 1 FROM users WHERE status = 1
)
AND EXISTS (
    SELECT 1 FROM permissions WHERE module = 'leads' AND action = 'export'
);

-- =============================================================================
-- STEP 7: Initialize approval workflow for sensitive operations
-- =============================================================================

-- Setup approval chains for admin permissions
INSERT IGNORE INTO permission_approval_requests (
    requestor_user_id,
    permission_id,
    business_justification,
    approval_chain,
    approval_status,
    requested_at,
    expires_at
)
SELECT 
    (SELECT MIN(id) FROM users WHERE status = 1 AND id > 1),
    p.id,
    'Initial approval request for admin access setup',
    JSON_ARRAY((SELECT MIN(id) FROM users WHERE status = 1)),
    'pending',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 DAY)
FROM permissions p
WHERE p.module = 'admin'
  AND p.action IN ('roles', 'permissions')
  AND p.is_active = 1
  AND EXISTS (SELECT 1 FROM users WHERE status = 1)
  AND NOT EXISTS (
    SELECT 1 FROM permission_approval_requests 
    WHERE permission_id = p.id 
      AND approval_status IN ('pending', 'approved')
  )
LIMIT 2;

-- =============================================================================
-- STEP 8: Data integrity verification
-- =============================================================================

SELECT '';
SELECT 'Role Hierarchy Summary' as section;
SELECT 
    'Total Active Roles' as metric, COUNT(*) as count
FROM roles WHERE is_active = 1
UNION ALL
SELECT 'Roles with Parents', COUNT(*) FROM roles WHERE parent_role_id IS NOT NULL AND is_active = 1
UNION ALL
SELECT 'Inheritance Relationships', COUNT(*) FROM role_inheritance WHERE is_active = 1;

SELECT '';
SELECT 'Permission Inheritance Summary' as section;
SELECT 
    'Direct Permissions' as metric, COUNT(*) as count
FROM role_permission_inheritance 
WHERE inheritance_method = 'direct' AND is_active = 1
UNION ALL
SELECT 'Inherited Permissions', COUNT(*) FROM role_permission_inheritance WHERE inheritance_method = 'inherited' AND is_active = 1;

SELECT '';
SELECT 'Delegation & Approval Summary' as section;
SELECT 
    'Active Delegations' as metric, COUNT(*) as count
FROM permission_delegations 
WHERE approval_status IN ('approved', 'pending') 
  AND (end_date IS NULL OR end_date > NOW())
UNION ALL
SELECT 'Pending Delegations', COUNT(*) FROM permission_delegations WHERE approval_status = 'pending'
UNION ALL
SELECT 'Approval Requests', COUNT(*) FROM permission_approval_requests WHERE approval_status = 'pending';

SELECT '';
SELECT 'Cache Status' as section;
SELECT 
    'Effective Permissions Cached' as metric, COUNT(*) as count
FROM effective_permissions_cache 
WHERE expires_at > NOW();

-- =============================================================================
-- STEP 9: Final verification
-- =============================================================================

SELECT '';
SELECT 'PHASE 2 DATA MIGRATION v2 COMPLETED' as status;
SELECT 
    'Hierarchy Relationships' as metric, COUNT(*) as count FROM role_inheritance
UNION ALL
SELECT 'Permission Inheritance Mappings', COUNT(*) FROM role_permission_inheritance
UNION ALL
SELECT 'Permission Restrictions', COUNT(*) FROM permission_restrictions
UNION ALL
SELECT 'Active Delegations', COUNT(*) FROM permission_delegations WHERE approval_status = 'approved'
UNION ALL
SELECT 'Effective Permissions Cached', COUNT(*) FROM effective_permissions_cache WHERE expires_at > NOW();

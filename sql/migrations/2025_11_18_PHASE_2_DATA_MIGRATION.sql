-- =============================================================================
-- PHASE 2: DATA MIGRATION - Role Hierarchy, Inheritance, and Delegation Setup
-- Date: 2025-11-18
-- Purpose: Initialize role hierarchy, permission inheritance, and delegation rules
--
-- This migration:
-- 1. Sets up role hierarchy relationships
-- 2. Initializes permission inheritance mappings
-- 3. Creates delegation rules and approval workflows
-- 4. Sets up permission restrictions
-- 5. Initializes effective permissions cache
-- =============================================================================

-- =============================================================================
-- STEP 1: Configure role hierarchy levels and relationships
-- =============================================================================

-- System hierarchy:
-- Level 0: Super Admin (root of hierarchy)
-- Level 1: Admin (direct children of Super Admin)
-- Level 2: Manager (direct children of Admin)
-- Level 3: User roles (direct children of Manager)

-- Update hierarchy levels (if not already set)
UPDATE roles SET hierarchy_level = 0, is_system = 1 WHERE id IN (1) AND role = 'Admin';
UPDATE roles SET hierarchy_level = 1, is_system = 0 WHERE id IN (2) AND role IN ('Manager');
UPDATE roles SET hierarchy_level = 2, is_system = 0 WHERE id IN (3, 4, 5) AND role IN ('User', 'Viewer', 'Restricted');

-- Set parent-child relationships
UPDATE roles SET parent_role_id = 1 WHERE id = 2 AND role = 'Manager';
UPDATE roles SET parent_role_id = 2 WHERE id IN (3, 4) AND role IN ('User', 'Viewer');
UPDATE roles SET parent_role_id = 4 WHERE id = 5 AND role = 'Restricted';

-- Configure delegation settings
UPDATE roles SET allows_delegation = 1, max_delegable_depth = 2 WHERE id = 1;
UPDATE roles SET allows_delegation = 1, max_delegable_depth = 1 WHERE id = 2;
UPDATE roles SET allows_delegation = 0, max_delegable_depth = 0 WHERE id IN (3, 4, 5);

-- =============================================================================
-- STEP 2: Populate role inheritance relationships
-- =============================================================================

-- Admin inherits nothing (root of hierarchy)
-- Manager inherits from Admin (full inheritance)
INSERT INTO role_inheritance (parent_role_id, child_role_id, inheritance_type, depth, is_active)
SELECT 1, 2, 'full', 1, 1 WHERE NOT EXISTS (
    SELECT 1 FROM role_inheritance WHERE parent_role_id = 1 AND child_role_id = 2
);

-- User/Viewer inherit from Manager (full inheritance)
INSERT INTO role_inheritance (parent_role_id, child_role_id, inheritance_type, depth, is_active)
SELECT 2, 3, 'full', 1, 1 WHERE NOT EXISTS (
    SELECT 1 FROM role_inheritance WHERE parent_role_id = 2 AND child_role_id = 3
);

INSERT INTO role_inheritance (parent_role_id, child_role_id, inheritance_type, depth, is_active)
SELECT 2, 4, 'full', 1, 1 WHERE NOT EXISTS (
    SELECT 1 FROM role_inheritance WHERE parent_role_id = 2 AND child_role_id = 4
);

-- Viewer inherits from Admin through Manager (depth 2)
INSERT INTO role_inheritance (parent_role_id, child_role_id, inheritance_type, depth, is_active)
SELECT 1, 3, 'full', 2, 1 WHERE NOT EXISTS (
    SELECT 1 FROM role_inheritance WHERE parent_role_id = 1 AND child_role_id = 3
);

INSERT INTO role_inheritance (parent_role_id, child_role_id, inheritance_type, depth, is_active)
SELECT 1, 4, 'full', 2, 1 WHERE NOT EXISTS (
    SELECT 1 FROM role_inheritance WHERE parent_role_id = 1 AND child_role_id = 4
);

-- Restricted inherits from Viewer (partial inheritance)
INSERT INTO role_inheritance (parent_role_id, child_role_id, inheritance_type, depth, is_active)
SELECT 4, 5, 'partial', 1, 1 WHERE NOT EXISTS (
    SELECT 1 FROM role_inheritance WHERE parent_role_id = 4 AND child_role_id = 5
);

-- =============================================================================
-- STEP 3: Populate role permission inheritance mappings
-- =============================================================================

-- For each role with a parent, create inherited permission records
-- Admin role (no inheritance) - all permissions are direct
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT rp.role_id, p.id, 1, 0, 'direct', 1
FROM roles_permissions rp
JOIN permissions p ON rp.pid = p.pid
WHERE rp.role_id = 1 AND rp.is_active = 1
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Manager role inherits from Admin
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT 2, p.id, 1, 1, 'inherited', 1
FROM permissions p
JOIN role_inheritance ri ON ri.parent_role_id = 1 AND ri.child_role_id = 2
WHERE ri.inheritance_type = 'full' AND p.is_active = 1
AND p.id NOT IN (
    SELECT permission_id FROM role_permission_inheritance WHERE role_id = 2
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Manager direct permissions
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT rp.role_id, p.id, rp.role_id, 0, 'direct', 1
FROM roles_permissions rp
JOIN permissions p ON rp.pid = p.pid
WHERE rp.role_id = 2 AND rp.is_active = 1
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- User role inherits from Manager
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT 3, p.id, 2, 1, 'inherited', 1
FROM permissions p
JOIN role_inheritance ri ON ri.parent_role_id = 2 AND ri.child_role_id = 3
WHERE ri.inheritance_type = 'full' AND p.is_active = 1
AND p.id NOT IN (
    SELECT permission_id FROM role_permission_inheritance WHERE role_id = 3
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- User direct permissions
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT rp.role_id, p.id, rp.role_id, 0, 'direct', 1
FROM roles_permissions rp
JOIN permissions p ON rp.pid = p.pid
WHERE rp.role_id = 3 AND rp.is_active = 1
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Viewer role inherits from Manager
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT 4, p.id, 2, 1, 'inherited', 1
FROM permissions p
JOIN role_inheritance ri ON ri.parent_role_id = 2 AND ri.child_role_id = 4
WHERE ri.inheritance_type = 'full' AND p.is_active = 1
AND p.id NOT IN (
    SELECT permission_id FROM role_permission_inheritance WHERE role_id = 4
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Viewer direct permissions
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT rp.role_id, p.id, rp.role_id, 0, 'direct', 1
FROM roles_permissions rp
JOIN permissions p ON rp.pid = p.pid
WHERE rp.role_id = 4 AND rp.is_active = 1
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Restricted role inherits from Viewer (partial)
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT 5, p.id, 4, 1, 'inherited', 1
FROM roles_permissions rp
JOIN permissions p ON rp.pid = p.pid
WHERE rp.role_id = 4 AND rp.is_active = 1
AND p.id NOT IN (
    SELECT permission_id FROM role_permission_inheritance WHERE role_id = 5
)
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Restricted direct permissions
INSERT INTO role_permission_inheritance (role_id, permission_id, inherited_from_role_id, inheritance_depth, inheritance_method, is_active)
SELECT rp.role_id, p.id, rp.role_id, 0, 'direct', 1
FROM roles_permissions rp
JOIN permissions p ON rp.pid = p.pid
WHERE rp.role_id = 5 AND rp.is_active = 1
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- =============================================================================
-- STEP 4: Set up basic permission restrictions
-- =============================================================================

-- Time-based restrictions: Business hours only for sensitive operations
INSERT INTO permission_restrictions (permission_id, restriction_type, restriction_rule, priority, is_active)
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
  AND p.action IN ('users', 'roles', 'permissions', 'security')
  AND p.is_active = 1
  AND NOT EXISTS (
    SELECT 1 FROM permission_restrictions 
    WHERE permission_id = p.id AND restriction_type = 'time_based'
  );

-- Field-level restrictions: Hide sensitive fields based on role
INSERT INTO permission_restrictions (permission_id, restriction_type, restriction_rule, priority, is_active)
SELECT 
    p.id,
    'field_restriction',
    JSON_OBJECT(
        'fields_to_hide', JSON_ARRAY('email', 'phone'),
        'hide_from_roles', JSON_ARRAY('Restricted'),
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

-- Populate effective permissions from direct role assignments
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

-- Populate inherited permissions from role hierarchy
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

-- Sample: Manager delegating a permission temporarily to a User
-- This is for testing/demo purposes
INSERT INTO permission_delegations (
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
    1,
    COALESCE((SELECT id FROM users WHERE id != 1 LIMIT 1), 2),
    (SELECT id FROM permissions WHERE module = 'leads' AND action = 'export' LIMIT 1),
    'temporary',
    'approved',
    1,
    'Temporary export access for end-of-month report',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 7 DAY)
WHERE NOT EXISTS (
    SELECT 1 FROM permission_delegations 
    WHERE delegating_user_id = 1 
      AND delegation_type = 'temporary'
      AND approval_status = 'approved'
);

-- =============================================================================
-- STEP 7: Initialize approval workflow for sensitive operations
-- =============================================================================

-- Set up approval chains for admin permissions
INSERT INTO permission_approval_requests (
    requestor_user_id,
    permission_id,
    business_justification,
    approval_chain,
    approval_status,
    requested_at,
    expires_at
)
SELECT 
    COALESCE((SELECT id FROM users WHERE id != 1 LIMIT 1), 2),
    p.id,
    'Initial approval request for admin access setup',
    JSON_ARRAY(1),
    'pending',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 DAY)
FROM permissions p
WHERE p.module = 'admin'
  AND p.action IN ('roles', 'permissions')
  AND p.is_active = 1
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
    'Total Roles' as metric, COUNT(*) as count 
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
    'Effective Permissions Cached', COUNT(*) 
FROM effective_permissions_cache 
WHERE expires_at > NOW();

-- =============================================================================
-- STEP 9: Final verification
-- =============================================================================

SELECT '';
SELECT 'PHASE 2 DATA MIGRATION COMPLETED' as status;
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

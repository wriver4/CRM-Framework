-- =============================================================================
-- PHASE 2: SCHEMA ENHANCEMENT - Advanced Permission Features
-- Date: 2025-11-18
-- Purpose: Add hierarchy, inheritance, and delegation capabilities
--
-- This migration adds:
-- 1. Role hierarchy relationship tracking
-- 2. Permission inheritance mechanism
-- 3. Permission delegation with approval workflow
-- 4. Permission restrictions and temporal access
-- 5. Audit trail for permission changes
-- 6. Stored procedures for advanced operations
-- =============================================================================

-- =============================================================================
-- STEP 1: Enhance role hierarchy with validation
-- =============================================================================

-- Add hierarchy validation constraints and tracking
ALTER TABLE roles ADD COLUMN IF NOT EXISTS hierarchy_version INT DEFAULT 0 AFTER hierarchy_level;
ALTER TABLE roles ADD COLUMN IF NOT EXISTS max_delegable_depth INT DEFAULT 1 AFTER hierarchy_version;
ALTER TABLE roles ADD COLUMN IF NOT EXISTS allows_delegation TINYINT(1) DEFAULT 1 AFTER max_delegable_depth;

-- Add indexes for hierarchy queries
CREATE INDEX IF NOT EXISTS idx_parent_role_id ON roles(parent_role_id);
CREATE INDEX IF NOT EXISTS idx_hierarchy_level ON roles(hierarchy_level);
CREATE INDEX IF NOT EXISTS idx_role_active ON roles(is_active, id);

-- =============================================================================
-- STEP 2: Create role inheritance mapping table
-- =============================================================================

CREATE TABLE IF NOT EXISTS role_inheritance (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    parent_role_id INT NOT NULL,
    child_role_id INT NOT NULL,
    inheritance_type ENUM('full', 'partial', 'none') DEFAULT 'full' COMMENT 'full=all perms, partial=selected, none=no inheritance',
    depth INT NOT NULL COMMENT 'Distance in hierarchy from parent to child',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_parent_child (parent_role_id, child_role_id),
    KEY idx_child_role_id (child_role_id),
    KEY idx_inheritance_type (inheritance_type),
    KEY idx_is_active (is_active),
    KEY idx_depth (depth),
    
    CONSTRAINT fk_parent_role_inheritance FOREIGN KEY (parent_role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_child_role_inheritance FOREIGN KEY (child_role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Maps role hierarchy relationships with inheritance types';

-- =============================================================================
-- STEP 3: Create inherited permissions view/table
-- =============================================================================

CREATE TABLE IF NOT EXISTS role_permission_inheritance (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    inherited_from_role_id INT NOT NULL COMMENT 'Original role in hierarchy',
    inheritance_depth INT NOT NULL COMMENT 'Number of levels inherited from parent',
    inheritance_method ENUM('direct', 'inherited', 'delegated') DEFAULT 'direct',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_role_perm_source (role_id, permission_id, inherited_from_role_id),
    KEY idx_permission_id (permission_id),
    KEY idx_inherited_from (inherited_from_role_id),
    KEY idx_inheritance_depth (inheritance_depth),
    KEY idx_inheritance_method (inheritance_method),
    
    CONSTRAINT fk_role_perm_inheritance_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_perm_inheritance_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_perm_inheritance_source FOREIGN KEY (inherited_from_role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Tracks which permissions are inherited vs directly assigned';

-- =============================================================================
-- STEP 4: Create permission delegation table
-- =============================================================================

CREATE TABLE IF NOT EXISTS permission_delegations (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    delegating_user_id INT NOT NULL COMMENT 'User granting the permission',
    receiving_user_id INT NOT NULL COMMENT 'User receiving the permission',
    permission_id INT NOT NULL,
    granted_role_id INT COMMENT 'Role context in which permission is delegated',
    delegation_type ENUM('temporary', 'conditional', 'approval_pending') DEFAULT 'temporary',
    approval_status ENUM('pending', 'approved', 'rejected', 'revoked') DEFAULT 'pending',
    approved_by_user_id INT COMMENT 'User who approved the delegation',
    restrictions JSON COMMENT 'Field-level or record-level restrictions',
    reason TEXT COMMENT 'Reason for delegation',
    start_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME COMMENT 'When delegation expires',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    KEY idx_receiving_user_id (receiving_user_id),
    KEY idx_delegating_user_id (delegating_user_id),
    KEY idx_permission_id (permission_id),
    KEY idx_granted_role_id (granted_role_id),
    KEY idx_approval_status (approval_status),
    KEY idx_delegation_type (delegation_type),
    KEY idx_start_date (start_date),
    KEY idx_end_date (end_date),
    
    CONSTRAINT fk_delegating_user FOREIGN KEY (delegating_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_receiving_user FOREIGN KEY (receiving_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_delegated_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_delegation_role FOREIGN KEY (granted_role_id) REFERENCES roles(id) ON DELETE SET NULL,
    CONSTRAINT fk_approving_user FOREIGN KEY (approved_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Tracks temporary permission delegations with approval workflow';

-- =============================================================================
-- STEP 5: Create permission restrictions table
-- =============================================================================

CREATE TABLE IF NOT EXISTS permission_restrictions (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    permission_id INT NOT NULL,
    restriction_type ENUM('field_restriction', 'record_restriction', 'time_based', 'ip_based') NOT NULL,
    restriction_rule JSON NOT NULL COMMENT 'Specific restriction configuration',
    priority INT DEFAULT 1 COMMENT 'Higher priority restrictions override lower',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    KEY idx_permission_id (permission_id),
    KEY idx_restriction_type (restriction_type),
    KEY idx_priority (priority),
    KEY idx_is_active (is_active),
    
    CONSTRAINT fk_restriction_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Advanced restrictions on permissions (field-level, time-based, IP-based)';

-- =============================================================================
-- STEP 6: Create permission audit log
-- =============================================================================

CREATE TABLE IF NOT EXISTS permission_audit_log (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT COMMENT 'User who made the change',
    action_type ENUM('grant', 'revoke', 'delegate', 'approve_delegation', 'reject_delegation', 'modify', 'inherit') NOT NULL,
    target_user_id INT COMMENT 'User affected by the action',
    target_role_id INT COMMENT 'Role affected by the action',
    permission_id INT COMMENT 'Permission affected by the action',
    delegation_id INT COMMENT 'Delegation affected (if applicable)',
    old_value TEXT COMMENT 'Previous value',
    new_value TEXT COMMENT 'New value',
    change_reason TEXT COMMENT 'Reason for the change',
    ip_address VARCHAR(45) COMMENT 'IP address of user making change',
    user_agent TEXT COMMENT 'Browser user agent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    KEY idx_user_id (user_id),
    KEY idx_target_user_id (target_user_id),
    KEY idx_target_role_id (target_role_id),
    KEY idx_permission_id (permission_id),
    KEY idx_action_type (action_type),
    KEY idx_created_at (created_at),
    KEY idx_delegation_id (delegation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Complete audit trail of all permission changes';

-- =============================================================================
-- STEP 7: Create permission approval workflow table
-- =============================================================================

CREATE TABLE IF NOT EXISTS permission_approval_requests (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    requestor_user_id INT NOT NULL COMMENT 'User requesting permission',
    permission_id INT NOT NULL,
    requested_role_id INT COMMENT 'Role context',
    business_justification TEXT NOT NULL,
    approval_chain JSON COMMENT 'Array of approvers in sequence',
    current_approver_user_id INT COMMENT 'Next person to approve',
    approval_status ENUM('pending', 'approved', 'rejected', 'pending_more_info', 'expired') DEFAULT 'pending',
    approval_level INT DEFAULT 0 COMMENT 'How many levels approved so far',
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME,
    expires_at DATETIME COMMENT 'Request validity expires',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    KEY idx_requestor_user_id (requestor_user_id),
    KEY idx_current_approver_user_id (current_approver_user_id),
    KEY idx_permission_id (permission_id),
    KEY idx_approval_status (approval_status),
    KEY idx_requested_at (requested_at),
    KEY idx_expires_at (expires_at),
    
    CONSTRAINT fk_approval_requestor FOREIGN KEY (requestor_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_approval_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_approval_role FOREIGN KEY (requested_role_id) REFERENCES roles(id) ON DELETE SET NULL,
    CONSTRAINT fk_approval_approver FOREIGN KEY (current_approver_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Multi-level permission approval requests workflow';

-- =============================================================================
-- STEP 8: Create effective permissions cache table
-- =============================================================================

CREATE TABLE IF NOT EXISTS effective_permissions_cache (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT,
    permission_id INT NOT NULL,
    permission_source ENUM('direct', 'inherited', 'delegated', 'temporary') DEFAULT 'direct',
    calculation_method ENUM('full', 'hierarchy', 'delegation', 'approval') DEFAULT 'full',
    is_active TINYINT(1) DEFAULT 1,
    cached_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_user_perm_cache (user_id, permission_id, role_id),
    KEY idx_user_id (user_id),
    KEY idx_permission_id (permission_id),
    KEY idx_expires_at (expires_at),
    KEY idx_permission_source (permission_source),
    
    CONSTRAINT fk_cache_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cache_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_cache_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Cache of effective permissions (direct + inherited + delegated)';

-- =============================================================================
-- STEP 9: Create helper stored procedures
-- =============================================================================

-- Procedure to calculate role hierarchy depth
DROP PROCEDURE IF EXISTS sp_calculate_role_depth;
DELIMITER $$
CREATE PROCEDURE sp_calculate_role_depth(
    IN p_role_id INT,
    IN p_max_depth INT,
    OUT p_calculated_depth INT
)
BEGIN
    DECLARE v_parent_id INT;
    DECLARE v_depth INT DEFAULT 0;
    DECLARE v_current_id INT;
    
    SET v_current_id = p_role_id;
    
    WHILE v_current_id IS NOT NULL AND v_depth < COALESCE(p_max_depth, 100) DO
        SELECT parent_role_id INTO v_parent_id FROM roles WHERE id = v_current_id;
        IF v_parent_id IS NOT NULL THEN
            SET v_depth = v_depth + 1;
            SET v_current_id = v_parent_id;
        ELSE
            SET v_current_id = NULL;
        END IF;
    END WHILE;
    
    SET p_calculated_depth = v_depth;
END$$
DELIMITER ;

-- Procedure to get all ancestor roles (for hierarchy traversal)
DROP PROCEDURE IF EXISTS sp_get_ancestor_roles;
DELIMITER $$
CREATE PROCEDURE sp_get_ancestor_roles(IN p_role_id INT)
BEGIN
    WITH RECURSIVE role_ancestors AS (
        SELECT id, parent_role_id, role, hierarchy_level, 0 as depth
        FROM roles
        WHERE id = p_role_id
        
        UNION ALL
        
        SELECT r.id, r.parent_role_id, r.role, r.hierarchy_level, ra.depth + 1
        FROM roles r
        INNER JOIN role_ancestors ra ON r.id = ra.parent_role_id
        WHERE ra.parent_role_id IS NOT NULL AND ra.depth < 100
    )
    SELECT id, role, hierarchy_level, depth
    FROM role_ancestors
    ORDER BY depth DESC;
END$$
DELIMITER ;

-- Procedure to get all descendant roles
DROP PROCEDURE IF EXISTS sp_get_descendant_roles;
DELIMITER $$
CREATE PROCEDURE sp_get_descendant_roles(IN p_role_id INT)
BEGIN
    WITH RECURSIVE role_descendants AS (
        SELECT id, parent_role_id, role, hierarchy_level, 0 as depth
        FROM roles
        WHERE parent_role_id = p_role_id OR id = p_role_id
        
        UNION ALL
        
        SELECT r.id, r.parent_role_id, r.role, r.hierarchy_level, rd.depth + 1
        FROM roles r
        INNER JOIN role_descendants rd ON r.parent_role_id = rd.id
        WHERE rd.depth < 100
    )
    SELECT id, role, hierarchy_level, depth
    FROM role_descendants
    ORDER BY depth ASC;
END$$
DELIMITER ;

-- Procedure to check for circular role hierarchy
DROP PROCEDURE IF EXISTS sp_check_circular_hierarchy;
DELIMITER $$
CREATE PROCEDURE sp_check_circular_hierarchy(
    IN p_role_id INT,
    IN p_parent_role_id INT,
    OUT p_is_circular INT
)
BEGIN
    DECLARE v_found INT DEFAULT 0;
    
    WITH RECURSIVE hierarchy_check AS (
        SELECT id, parent_role_id, 0 as depth
        FROM roles
        WHERE id = p_parent_role_id
        
        UNION ALL
        
        SELECT r.id, r.parent_role_id, hc.depth + 1
        FROM roles r
        INNER JOIN hierarchy_check hc ON r.parent_role_id = hc.id
        WHERE hc.depth < 100
    )
    SELECT COUNT(*) INTO v_found
    FROM hierarchy_check
    WHERE id = p_role_id;
    
    SET p_is_circular = IF(v_found > 0, 1, 0);
END$$
DELIMITER ;

-- Procedure to revoke expired delegations
DROP PROCEDURE IF EXISTS sp_revoke_expired_delegations;
DELIMITER $$
CREATE PROCEDURE sp_revoke_expired_delegations()
BEGIN
    UPDATE permission_delegations
    SET approval_status = 'revoked'
    WHERE approval_status IN ('approved', 'pending')
      AND end_date IS NOT NULL
      AND end_date < NOW();
    
    SELECT ROW_COUNT() as revoked_count;
END$$
DELIMITER ;

-- =============================================================================
-- STEP 10: Create permissions views for easy querying
-- =============================================================================

DROP VIEW IF EXISTS v_active_delegations;
CREATE VIEW v_active_delegations AS
SELECT 
    pd.id,
    pd.delegating_user_id,
    pd.receiving_user_id,
    pd.permission_id,
    p.pobject as permission_name,
    p.module,
    p.action,
    pd.delegation_type,
    pd.approval_status,
    pd.start_date,
    pd.end_date,
    CASE 
        WHEN pd.end_date IS NULL THEN 'indefinite'
        WHEN pd.end_date > NOW() THEN 'active'
        ELSE 'expired'
    END as delegation_status
FROM permission_delegations pd
JOIN permissions p ON pd.permission_id = p.id
WHERE pd.approval_status IN ('approved', 'pending');

DROP VIEW IF EXISTS v_role_hierarchy_tree;
CREATE VIEW v_role_hierarchy_tree AS
SELECT 
    r.id,
    r.role,
    r.parent_role_id,
    r.hierarchy_level,
    r.is_active,
    pr.role as parent_role,
    COUNT(DISTINCT c.id) as child_count,
    COUNT(DISTINCT rp.pid) as permission_count
FROM roles r
LEFT JOIN roles pr ON r.parent_role_id = pr.id
LEFT JOIN roles c ON c.parent_role_id = r.id
LEFT JOIN roles_permissions rp ON r.id = rp.role_id AND rp.is_active = 1
GROUP BY r.id
ORDER BY r.hierarchy_level, r.role;

-- =============================================================================
-- STEP 11: Add indexes for performance
-- =============================================================================

CREATE INDEX IF NOT EXISTS idx_perm_deleg_status ON permission_delegations(approval_status, end_date);
CREATE INDEX IF NOT EXISTS idx_perm_deleg_user_date ON permission_delegations(receiving_user_id, end_date);
CREATE INDEX IF NOT EXISTS idx_effective_perm_user ON effective_permissions_cache(user_id, permission_source);
CREATE INDEX IF NOT EXISTS idx_approval_chain ON permission_approval_requests(current_approver_user_id);
CREATE INDEX IF NOT EXISTS idx_restriction_active ON permission_restrictions(is_active, restriction_type);

-- =============================================================================
-- STEP 12: Summary and verification
-- =============================================================================

SELECT 'PHASE 2 SCHEMA ENHANCEMENT COMPLETED' as status;
SELECT 
    'New Tables Created' as metric,
    COUNT(*) as count
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
    'role_inheritance',
    'role_permission_inheritance',
    'permission_delegations',
    'permission_restrictions',
    'permission_audit_log',
    'permission_approval_requests',
    'effective_permissions_cache'
  );

SELECT 
    'Stored Procedures' as metric,
    COUNT(*) as count
FROM information_schema.routines
WHERE routine_schema = DATABASE()
  AND routine_name LIKE 'sp_%';

SELECT 
    'Views Created' as metric,
    COUNT(*) as count
FROM information_schema.views
WHERE table_schema = DATABASE()
  AND table_name LIKE 'v_%';

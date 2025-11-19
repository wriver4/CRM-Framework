-- =========================================================================
-- RBAC SYSTEM SCHEMA ENHANCEMENT MIGRATION - 2025-11-18
-- =========================================================================
-- Purpose: Implement 4-level granular permissions system:
-- 1. Module level (leads, contacts, etc.)
-- 2. Action level (view, create, edit, delete)
-- 3. Field level (specific field access)
-- 4. Record level (own, team, all)
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ============== PHASE 1: Enhance existing tables ===============

-- STEP 1: Enhance roles table with hierarchy support
ALTER TABLE roles ADD COLUMN IF NOT EXISTS parent_role_id INT DEFAULT NULL;
ALTER TABLE roles ADD COLUMN IF NOT EXISTS hierarchy_level INT DEFAULT 0;
ALTER TABLE roles ADD COLUMN IF NOT EXISTS is_system BOOLEAN DEFAULT FALSE;
ALTER TABLE roles ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE;

-- Add indexes for hierarchy queries
ALTER TABLE roles ADD INDEX IF NOT EXISTS idx_parent_role_id (parent_role_id);
ALTER TABLE roles ADD INDEX IF NOT EXISTS idx_hierarchy_level (hierarchy_level);
ALTER TABLE roles ADD INDEX IF NOT EXISTS idx_is_active (is_active);

-- Add foreign key for role hierarchy (if not exists)
-- Using ALTER IGNORE to suppress error if constraint already exists
ALTER IGNORE TABLE roles ADD CONSTRAINT fk_roles_parent 
  FOREIGN KEY (parent_role_id) REFERENCES roles(id) ON DELETE SET NULL;

-- STEP 2: Enhance permissions table with module/action/field/scope structure
ALTER TABLE permissions 
  ADD COLUMN IF NOT EXISTS module VARCHAR(50) NOT NULL DEFAULT 'general' AFTER pobject,
  ADD COLUMN IF NOT EXISTS action VARCHAR(50) NOT NULL DEFAULT 'access' AFTER module,
  ADD COLUMN IF NOT EXISTS field_name VARCHAR(100) DEFAULT NULL AFTER action,
  ADD COLUMN IF NOT EXISTS scope VARCHAR(20) DEFAULT 'all' AFTER field_name,
  ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE AFTER scope;

-- Create composite index for permission lookups
ALTER TABLE permissions ADD INDEX IF NOT EXISTS idx_module_action (module, action);
ALTER TABLE permissions ADD INDEX IF NOT EXISTS idx_scope (scope);
ALTER TABLE permissions ADD INDEX IF NOT EXISTS idx_field_name (field_name);
ALTER TABLE permissions ADD INDEX IF NOT EXISTS idx_is_active (is_active);

-- STEP 3: Enhance roles_permissions junction table
ALTER TABLE roles_permissions ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE roles_permissions ADD INDEX IF NOT EXISTS idx_is_active (is_active);

-- ============== PHASE 2: Create new tables for advanced features ===============

-- STEP 4: Create field_permissions table for field-level access control
CREATE TABLE IF NOT EXISTS field_permissions (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  permission_id INT NOT NULL,
  module VARCHAR(50) NOT NULL,
  field_name VARCHAR(100) NOT NULL,
  access_level ENUM('none', 'view', 'edit', 'hidden') DEFAULT 'view',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
  UNIQUE KEY uk_permission_module_field (permission_id, module, field_name),
  INDEX idx_module (module),
  INDEX idx_field_name (field_name),
  INDEX idx_access_level (access_level),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 5: Create record_ownership table for record-level access tracking
CREATE TABLE IF NOT EXISTS record_ownership (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  record_type VARCHAR(50) NOT NULL,
  record_id INT NOT NULL,
  owner_user_id INT NOT NULL,
  team_id INT DEFAULT NULL,
  shared_with_users JSON,
  shared_with_roles JSON,
  access_type ENUM('owner', 'team', 'shared') DEFAULT 'owner',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_record_ownership (record_type, record_id),
  INDEX idx_owner_user_id (owner_user_id),
  INDEX idx_team_id (team_id),
  INDEX idx_record_type_id (record_type, record_id),
  INDEX idx_access_type (access_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 6: Create teams table for team-based access
CREATE TABLE IF NOT EXISTS teams (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  team_name VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  department VARCHAR(50),
  manager_user_id INT,
  budget_year INT,
  status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
  is_system BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_team_name (team_name),
  INDEX idx_department (department),
  INDEX idx_manager_user_id (manager_user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 7: Create team_members table for team membership
CREATE TABLE IF NOT EXISTS team_members (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  user_id INT NOT NULL,
  team_role VARCHAR(50) DEFAULT 'member',
  is_lead BOOLEAN DEFAULT FALSE,
  start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  end_date TIMESTAMP NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  UNIQUE KEY uk_team_user (team_id, user_id),
  INDEX idx_user_id (user_id),
  INDEX idx_is_lead (is_lead),
  INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 8: Create permission_cache table for performance optimization
CREATE TABLE IF NOT EXISTS permission_cache (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  permission_id INT NOT NULL,
  module VARCHAR(50) DEFAULT NULL,
  action VARCHAR(50) DEFAULT NULL,
  has_permission BOOLEAN DEFAULT FALSE,
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
  UNIQUE KEY uk_cache_lookup (user_id, permission_id),
  INDEX idx_module_action (module, action),
  INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 9: Create role_hierarchy_cache for role inheritance lookups
CREATE TABLE IF NOT EXISTS role_hierarchy_cache (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  inherited_role_id INT NOT NULL,
  hierarchy_level INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (inherited_role_id) REFERENCES roles(id) ON DELETE CASCADE,
  UNIQUE KEY uk_hierarchy (role_id, inherited_role_id),
  INDEX idx_inherited_role (inherited_role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== PHASE 3: Seed initial permission structure ===============

-- STEP 10: Initialize system roles (if not already present)
INSERT IGNORE INTO roles (id, role_id, role, is_system, hierarchy_level) VALUES
  (1, 1, 'Super Admin', TRUE, 0),
  (2, 2, 'Admin', TRUE, 1),
  (3, 3, 'System User', TRUE, 2);

-- STEP 11: Initialize common modules for permissions
-- These will be used for permission classification
INSERT IGNORE INTO permissions (pid, pobject, pdescription, module, action, scope) VALUES
  (1000, 'leads.access', 'Access Leads Module', 'leads', 'access', 'all'),
  (1001, 'leads.view', 'View Leads', 'leads', 'view', 'all'),
  (1002, 'leads.create', 'Create Leads', 'leads', 'create', 'all'),
  (1003, 'leads.edit', 'Edit Leads', 'leads', 'edit', 'all'),
  (1004, 'leads.delete', 'Delete Leads', 'leads', 'delete', 'all'),
  (1005, 'leads.view.own', 'View Own Leads', 'leads', 'view', 'own'),
  (1006, 'leads.view.team', 'View Team Leads', 'leads', 'view', 'team'),
  (1007, 'contacts.access', 'Access Contacts Module', 'contacts', 'access', 'all'),
  (1008, 'contacts.view', 'View Contacts', 'contacts', 'view', 'all'),
  (1009, 'contacts.create', 'Create Contacts', 'contacts', 'create', 'all'),
  (1010, 'contacts.edit', 'Edit Contacts', 'contacts', 'edit', 'all'),
  (1011, 'contacts.delete', 'Delete Contacts', 'contacts', 'delete', 'all'),
  (1012, 'admin.security.roles', 'Manage Roles', 'admin', 'manage', 'all'),
  (1013, 'admin.security.permissions', 'Manage Permissions', 'admin', 'manage', 'all'),
  (1014, 'admin.security.roles_permissions', 'Assign Permissions', 'admin', 'manage', 'all');

-- STEP 12: Create default team (if not exists)
INSERT IGNORE INTO teams (id, team_name, description, status, is_system) VALUES
  (1, 'System', 'System-level team', 'active', TRUE);

-- ============== PHASE 4: Create stored procedures for common operations ===============

DELIMITER //

-- STEP 13: Procedure to check user permission (4-level check)
DROP PROCEDURE IF EXISTS check_user_permission//
CREATE PROCEDURE check_user_permission(
  IN p_user_id INT,
  IN p_module VARCHAR(50),
  IN p_action VARCHAR(50),
  IN p_record_type VARCHAR(50),
  IN p_record_id INT,
  OUT p_has_permission BOOLEAN
)
BEGIN
  DECLARE v_role_id INT;
  DECLARE v_permission_id INT;
  DECLARE v_owner_user_id INT;
  DECLARE v_team_id INT;
  
  -- Initialize result
  SET p_has_permission = FALSE;
  
  -- Get user's role(s) - simplified for first pass
  -- In full implementation, would check all roles including inherited
  
  -- Check module + action level permission
  SELECT id INTO v_permission_id FROM permissions
  WHERE module = p_module AND action = p_action AND is_active = TRUE
  LIMIT 1;
  
  IF v_permission_id IS NOT NULL THEN
    -- Permission structure exists, now check record level if applicable
    IF p_record_type IS NOT NULL AND p_record_id IS NOT NULL THEN
      SELECT owner_user_id, team_id INTO v_owner_user_id, v_team_id
      FROM record_ownership
      WHERE record_type = p_record_type AND record_id = p_record_id
      LIMIT 1;
      
      -- Check if user owns record or is on team
      IF p_user_id = v_owner_user_id THEN
        SET p_has_permission = TRUE;
      ELSEIF v_team_id IS NOT NULL AND EXISTS(
        SELECT 1 FROM team_members
        WHERE team_id = v_team_id AND user_id = p_user_id AND is_active = TRUE
      ) THEN
        SET p_has_permission = TRUE;
      END IF;
    ELSE
      -- No record-level restriction, just check permission exists
      SET p_has_permission = TRUE;
    END IF;
  END IF;
END//

-- STEP 14: Procedure to cache permission lookup
DROP PROCEDURE IF EXISTS cache_user_permission//
CREATE PROCEDURE cache_user_permission(
  IN p_user_id INT,
  IN p_permission_id INT,
  IN p_module VARCHAR(50),
  IN p_action VARCHAR(50),
  IN p_has_permission BOOLEAN,
  IN p_ttl_seconds INT
)
BEGIN
  INSERT INTO permission_cache (user_id, permission_id, module, action, has_permission, expires_at)
  VALUES (p_user_id, p_permission_id, p_module, p_action, p_has_permission, DATE_ADD(NOW(), INTERVAL p_ttl_seconds SECOND))
  ON DUPLICATE KEY UPDATE
    has_permission = p_has_permission,
    expires_at = DATE_ADD(NOW(), INTERVAL p_ttl_seconds SECOND),
    updated_at = NOW();
END//

DELIMITER ;

-- ============== PHASE 5: Add indexes for performance ===============

-- STEP 15: Add composite indexes for common queries
ALTER TABLE permissions ADD INDEX IF NOT EXISTS idx_module_action_scope (module, action, scope);
ALTER TABLE field_permissions ADD INDEX IF NOT EXISTS idx_module_access (module, access_level);
ALTER TABLE record_ownership ADD INDEX IF NOT EXISTS idx_owner_type_id (owner_user_id, record_type, record_id);
ALTER TABLE team_members ADD INDEX IF NOT EXISTS idx_team_user_active (team_id, user_id, is_active);
ALTER TABLE roles_permissions ADD INDEX IF NOT EXISTS idx_role_permission (role_id, pid);

SET FOREIGN_KEY_CHECKS = 1;

-- ============== Verification Queries ===============

-- Verify schema creation
SELECT 'Roles table enhanced' as status;
SHOW COLUMNS FROM roles WHERE Field IN ('parent_role_id', 'hierarchy_level', 'is_system');

SELECT 'Permissions table enhanced' as status;
SHOW COLUMNS FROM permissions WHERE Field IN ('module', 'action', 'field_name', 'scope');

SELECT 'New tables created' as status;
SHOW TABLES LIKE 'field_permissions';
SHOW TABLES LIKE 'record_ownership';
SHOW TABLES LIKE 'teams';
SHOW TABLES LIKE 'team_members';
SHOW TABLES LIKE 'permission_cache';
SHOW TABLES LIKE 'role_hierarchy_cache';

SELECT 'Migration complete' as status, COUNT(*) as total_roles FROM roles;
SELECT 'Initial permissions seeded' as status, COUNT(*) as total_permissions FROM permissions WHERE pid >= 1000;

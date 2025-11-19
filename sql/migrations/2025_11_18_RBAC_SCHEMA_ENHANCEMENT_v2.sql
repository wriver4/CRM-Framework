-- =========================================================================
-- RBAC SYSTEM SCHEMA ENHANCEMENT MIGRATION - 2025-11-18 (v2)
-- =========================================================================
-- Works with existing teams and team_members tables
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

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
ALTER TABLE permissions ADD INDEX IF NOT EXISTS idx_module_action_scope (module, action, scope);

-- STEP 3: Enhance roles_permissions junction table
ALTER TABLE roles_permissions ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE roles_permissions ADD INDEX IF NOT EXISTS idx_is_active (is_active);
ALTER TABLE roles_permissions ADD INDEX IF NOT EXISTS idx_role_permission (role_id, pid);

-- STEP 4: Enhance existing teams table with additional columns
ALTER TABLE teams ADD COLUMN IF NOT EXISTS manager_user_id INT DEFAULT NULL;
ALTER TABLE teams ADD COLUMN IF NOT EXISTS budget_year INT DEFAULT NULL;
ALTER TABLE teams ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'archived') DEFAULT 'active';
ALTER TABLE teams ADD COLUMN IF NOT EXISTS is_system BOOLEAN DEFAULT FALSE;
ALTER TABLE teams ADD INDEX IF NOT EXISTS idx_manager_user_id (manager_user_id);
ALTER TABLE teams ADD INDEX IF NOT EXISTS idx_status (status);

-- STEP 5: Enhance existing team_members table
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS end_date TIMESTAMP NULL;
ALTER TABLE team_members ADD INDEX IF NOT EXISTS idx_is_active (is_active);
ALTER TABLE team_members ADD INDEX IF NOT EXISTS idx_team_user_active (team_id, user_id, is_active);

-- ============== PHASE 2: Create new tables for advanced features ===============

-- STEP 6: Create field_permissions table for field-level access control
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
  INDEX idx_is_active (is_active),
  INDEX idx_module_access (module, access_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STEP 7: Create record_ownership table for record-level access tracking
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
  INDEX idx_access_type (access_type),
  INDEX idx_owner_type_id (owner_user_id, record_type, record_id)
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

-- ============== PHASE 4: Create stored procedures for common operations ===============

DELIMITER //

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
  DECLARE v_permission_id INT;
  DECLARE v_owner_user_id INT;
  DECLARE v_team_id INT;
  
  SET p_has_permission = FALSE;
  
  SELECT id INTO v_permission_id FROM permissions
  WHERE module = p_module AND action = p_action AND is_active = TRUE
  LIMIT 1;
  
  IF v_permission_id IS NOT NULL THEN
    IF p_record_type IS NOT NULL AND p_record_id IS NOT NULL THEN
      SELECT owner_user_id, team_id INTO v_owner_user_id, v_team_id
      FROM record_ownership
      WHERE record_type = p_record_type AND record_id = p_record_id
      LIMIT 1;
      
      IF p_user_id = v_owner_user_id THEN
        SET p_has_permission = TRUE;
      ELSEIF v_team_id IS NOT NULL AND EXISTS(
        SELECT 1 FROM team_members
        WHERE team_id = v_team_id AND user_id = p_user_id AND is_active = TRUE
      ) THEN
        SET p_has_permission = TRUE;
      END IF;
    ELSE
      SET p_has_permission = TRUE;
    END IF;
  END IF;
END//

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

SET FOREIGN_KEY_CHECKS = 1;

-- ============== Verification Queries ===============

SELECT 'RBAC Schema Enhancement Complete' as status;
SELECT COUNT(*) as total_roles FROM roles;
SELECT COUNT(*) as total_permissions FROM permissions;
SELECT COUNT(*) as field_permission_tables FROM information_schema.tables WHERE table_name='field_permissions' AND table_schema=database();
SELECT COUNT(*) as record_ownership_tables FROM information_schema.tables WHERE table_name='record_ownership' AND table_schema=database();

-- =========================================================================
-- MULTIPLE ROLES PER USER MIGRATION - 2025-11-19
-- =========================================================================
-- Purpose: Enable users to have multiple roles while maintaining backward compatibility
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============== STEP 1: Create user_roles junction table ===============
CREATE TABLE IF NOT EXISTS user_roles (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  assigned_by INT DEFAULT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY uk_user_role (user_id, role_id),
  INDEX idx_user_id (user_id),
  INDEX idx_role_id (role_id),
  INDEX idx_is_primary (is_primary),
  INDEX idx_is_active (is_active),
  INDEX idx_user_active_primary (user_id, is_active, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============== STEP 2: Migrate existing role_id to user_roles ===============
INSERT INTO user_roles (user_id, role_id, is_primary, assigned_at)
SELECT id, role_id, TRUE, NOW()
FROM users
WHERE role_id IS NOT NULL
ON DUPLICATE KEY UPDATE is_primary = TRUE;

-- ============== STEP 3: Add role_id_primary for backward compatibility ===============
ALTER TABLE users ADD COLUMN IF NOT EXISTS role_id_primary INT DEFAULT NULL AFTER role_id;

-- ============== STEP 4: Update role_id_primary from user_roles ===============
UPDATE users u
SET u.role_id_primary = (
  SELECT role_id FROM user_roles 
  WHERE user_id = u.id AND is_primary = TRUE 
  LIMIT 1
)
WHERE u.id IN (SELECT DISTINCT user_id FROM user_roles);

-- ============== STEP 5: Create view for backward compatibility ===============
DROP VIEW IF EXISTS v_user_roles;
CREATE VIEW v_user_roles AS
SELECT 
  u.id as user_id,
  u.full_name,
  u.username,
  ur.role_id,
  r.role,
  ur.is_primary,
  ur.is_active,
  ur.assigned_at
FROM users u
INNER JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
WHERE ur.is_active = TRUE;

-- ============== STEP 6: Create function to get all user roles ===============
DELIMITER //

DROP FUNCTION IF EXISTS get_user_roles//
CREATE FUNCTION get_user_roles(p_user_id INT)
RETURNS JSON
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_roles JSON;
  SELECT JSON_ARRAYAGG(JSON_OBJECT('role_id', ur.role_id, 'role', r.role, 'is_primary', ur.is_primary))
  INTO v_roles
  FROM user_roles ur
  LEFT JOIN roles r ON ur.role_id = r.id
  WHERE ur.user_id = p_user_id AND ur.is_active = TRUE;
  RETURN COALESCE(v_roles, JSON_ARRAY());
END//

DROP FUNCTION IF EXISTS get_user_primary_role//
CREATE FUNCTION get_user_primary_role(p_user_id INT)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
  DECLARE v_role_id INT;
  SELECT role_id INTO v_role_id
  FROM user_roles
  WHERE user_id = p_user_id AND is_primary = TRUE AND is_active = TRUE
  LIMIT 1;
  RETURN COALESCE(v_role_id, 0);
END//

DELIMITER ;

-- ============== STEP 7: Ensure at least one primary role per user ===============
UPDATE user_roles ur
SET ur.is_primary = TRUE
WHERE ur.user_id IN (
  SELECT u.id FROM users u
  WHERE NOT EXISTS (
    SELECT 1 FROM user_roles WHERE user_id = u.id AND is_primary = TRUE AND is_active = TRUE
  )
)
AND ur.is_active = TRUE
LIMIT 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ============== Verification Queries ===============
SELECT 'Multiple Roles Migration Complete' as status;
SELECT COUNT(DISTINCT user_id) as users_with_roles FROM user_roles;
SELECT COUNT(*) as total_user_roles FROM user_roles;
SELECT COUNT(*) as users_with_multiple_roles FROM (
  SELECT user_id FROM user_roles WHERE is_active = TRUE GROUP BY user_id HAVING COUNT(*) > 1
) AS multi_role_users;

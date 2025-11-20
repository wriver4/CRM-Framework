-- =========================================================================
-- FIX USER ROLES FOREIGN KEY - 2025-11-19
-- =========================================================================
-- Purpose: Fix incorrect user_roles entries that used wrong role IDs
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM user_roles WHERE id > 0;

INSERT INTO user_roles (user_id, role_id, is_primary, assigned_at)
SELECT u.id, r.id, TRUE, NOW()
FROM users u
INNER JOIN roles r ON u.role_id = r.role_id
WHERE u.role_id IS NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'User Roles Foreign Key Fixed' as status;
SELECT COUNT(*) as total_user_roles FROM user_roles;
SELECT COUNT(DISTINCT user_id) as users_with_roles FROM user_roles;
SELECT u.id, u.full_name, r.role, ur.is_primary 
FROM user_roles ur 
INNER JOIN users u ON ur.user_id = u.id 
INNER JOIN roles r ON ur.role_id = r.id 
ORDER BY u.id LIMIT 10;

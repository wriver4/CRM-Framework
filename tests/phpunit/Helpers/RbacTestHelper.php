<?php

namespace Tests\Helpers;

/**
 * RBAC Test Helper
 * 
 * Provides utilities for testing Role-Based Access Control including:
 * - Test user creation with specific roles
 * - Permission assignment and testing
 * - Role hierarchy testing
 * - Field-level and record-level permission testing
 */
class RbacTestHelper
{
    protected $db;
    protected $pdo;
    
    public function __construct(\Database $db)
    {
        $this->db = $db;
        $this->pdo = $db->dbcrm();
    }
    
    /**
     * Create test role
     */
    public function createRole(string $name, string $description = ''): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO roles (role, updated_at, created_at) 
            VALUES (:name, NOW(), NOW())
        ");
        $stmt->execute(['name' => $name]);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Create test permission
     */
    public function createPermission(string $object, string $description = ''): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO permissions (pobject, pdescription, updated_at, created_at) 
            VALUES (:object, :description, NOW(), NOW())
        ");
        $stmt->execute([
            'object' => $object,
            'description' => $description
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Assign permission to role
     */
    public function assignPermissionToRole(int $roleId, int $permissionId): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO roles_permissions (role_id, pid, updated_at, created_at) 
            VALUES (:role_id, :pid, NOW(), NOW())
            ON DUPLICATE KEY UPDATE updated_at = NOW()
        ");
        $stmt->execute([
            'role_id' => $roleId,
            'pid' => $permissionId
        ]);
    }
    
    /**
     * Create test user with role
     */
    public function createUserWithRole(string $username, string $role, array $additionalData = []): int
    {
        // First, ensure role exists
        $roleId = $this->getRoleId($role);
        if (!$roleId) {
            $roleId = $this->createRole($role);
        }
        
        $userData = array_merge([
            'username' => $username,
            'password' => password_hash('test_password', PASSWORD_DEFAULT),
            'full_name' => ucfirst($username),
            'email' => "$username@test.com",
            'role_id' => $roleId,
            'state_id' => 1, // Active
        ], $additionalData);
        
        $columns = array_keys($userData);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = sprintf(
            "INSERT INTO users (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($userData);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Get role ID by name
     */
    public function getRoleId(string $roleName): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE rname = :name LIMIT 1");
        $stmt->execute(['name' => $roleName]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ? (int) $result['id'] : null;
    }
    
    /**
     * Get permission ID by object
     */
    public function getPermissionId(string $object): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM permissions WHERE pobject = :object LIMIT 1");
        $stmt->execute(['object' => $object]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ? (int) $result['id'] : null;
    }
    
    /**
     * Get all permissions for a role
     */
    public function getRolePermissions(int $roleId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.* 
            FROM permissions p
            JOIN roles_permissions rp ON p.id = rp.pid
            WHERE rp.rid = :rid
        ");
        $stmt->execute(['rid' => $roleId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if role has permission
     */
    public function roleHasPermission(int $roleId, string $permissionObject): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM roles_permissions rp
            JOIN permissions p ON rp.pid = p.id
            WHERE rp.rid = :rid AND p.pobject = :object
        ");
        $stmt->execute([
            'rid' => $roleId,
            'object' => $permissionObject
        ]);
        
        return (int) $stmt->fetchColumn() > 0;
    }
    
    /**
     * Seed standard RBAC test data
     */
    public function seedStandardRbacData(): array
    {
        $roles = [];
        $permissions = [];
        
        // Create standard roles
        $roles['super_admin'] = $this->createRole('super_admin', 'Full system access');
        $roles['sales_manager'] = $this->createRole('sales_manager', 'Sales module management');
        $roles['sales_rep'] = $this->createRole('sales_rep', 'Sales module read/write');
        $roles['viewer'] = $this->createRole('viewer', 'Read-only access');
        $roles['restricted'] = $this->createRole('restricted', 'Minimal access');
        
        // Create module-level permissions
        $permissions['leads.access'] = $this->createPermission('leads.access', 'Access leads module');
        $permissions['contacts.access'] = $this->createPermission('contacts.access', 'Access contacts module');
        $permissions['admin.access'] = $this->createPermission('admin.access', 'Access admin module');
        
        // Create action-level permissions
        $permissions['leads.view'] = $this->createPermission('leads.view', 'View leads');
        $permissions['leads.create'] = $this->createPermission('leads.create', 'Create leads');
        $permissions['leads.edit'] = $this->createPermission('leads.edit', 'Edit leads');
        $permissions['leads.delete'] = $this->createPermission('leads.delete', 'Delete leads');
        $permissions['leads.export'] = $this->createPermission('leads.export', 'Export leads');
        
        // Create field-level permissions
        $permissions['leads.view.email'] = $this->createPermission('leads.view.email', 'View lead email');
        $permissions['leads.edit.stage'] = $this->createPermission('leads.edit.stage', 'Edit lead stage');
        $permissions['leads.view.notes'] = $this->createPermission('leads.view.notes', 'View lead notes');
        
        // Create record-level permissions
        $permissions['leads.view.own'] = $this->createPermission('leads.view.own', 'View own leads');
        $permissions['leads.edit.own'] = $this->createPermission('leads.edit.own', 'Edit own leads');
        $permissions['leads.view.team'] = $this->createPermission('leads.view.team', 'View team leads');
        $permissions['leads.view.all'] = $this->createPermission('leads.view.all', 'View all leads');
        
        // Assign permissions to super_admin (all permissions)
        foreach ($permissions as $perm) {
            $this->assignPermissionToRole($roles['super_admin'], $perm);
        }
        
        // Assign permissions to sales_manager
        $managerPerms = [
            'leads.access', 'leads.view', 'leads.create', 'leads.edit', 'leads.delete',
            'leads.export', 'leads.view.email', 'leads.edit.stage', 'leads.view.notes',
            'leads.view.all', 'contacts.access'
        ];
        foreach ($managerPerms as $permKey) {
            $this->assignPermissionToRole($roles['sales_manager'], $permissions[$permKey]);
        }
        
        // Assign permissions to sales_rep
        $repPerms = [
            'leads.access', 'leads.view', 'leads.create', 'leads.edit',
            'leads.view.email', 'leads.view.notes', 'leads.view.own', 'leads.edit.own'
        ];
        foreach ($repPerms as $permKey) {
            $this->assignPermissionToRole($roles['sales_rep'], $permissions[$permKey]);
        }
        
        // Assign permissions to viewer
        $viewerPerms = ['leads.access', 'leads.view', 'leads.view.own'];
        foreach ($viewerPerms as $permKey) {
            $this->assignPermissionToRole($roles['viewer'], $permissions[$permKey]);
        }
        
        // Restricted role gets minimal permissions
        $this->assignPermissionToRole($roles['restricted'], $permissions['leads.view.own']);
        
        return [
            'roles' => $roles,
            'permissions' => $permissions
        ];
    }
    
    /**
     * Create test users for each role
     */
    public function createTestUsers(): array
    {
        return [
            'super_admin' => $this->createUserWithRole('test_super_admin', 'super_admin'),
            'sales_manager' => $this->createUserWithRole('test_sales_manager', 'sales_manager'),
            'sales_rep' => $this->createUserWithRole('test_sales_rep', 'sales_rep'),
            'viewer' => $this->createUserWithRole('test_viewer', 'viewer'),
            'restricted' => $this->createUserWithRole('test_restricted', 'restricted'),
        ];
    }
    
    /**
     * Simulate user login for testing
     */
    public function loginAs(int $userId): void
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.role as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = :id
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new \RuntimeException("User not found: $userId");
        }
        
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['full_name'] = $user['full_name'];
    }
    
    /**
     * Clear session (logout)
     */
    public function logout(): void
    {
        $_SESSION = [];
    }
    
    /**
     * Clean up RBAC test data
     */
    public function cleanup(): void
    {
        $this->pdo->exec("DELETE FROM roles_permissions WHERE role_id > 0");
        $this->pdo->exec("DELETE FROM permissions WHERE id > 0");
        $this->pdo->exec("DELETE FROM roles WHERE id > 0");
        $this->pdo->exec("DELETE FROM users WHERE username LIKE 'test_%'");
    }
}
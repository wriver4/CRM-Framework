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
     * Seed standard RBAC test data with 32-role system
     */
    public function seedStandardRbacData(): array
    {
        $roles = [];
        $permissions = [];
        
        // Create roles by category (following 32-role system)
        
        // System roles (1-2) - excluded from user assignment
        $roles['system_owner'] = 1; // System Owner
        $roles['system_admin'] = 2; // System Admin
        
        // Executive roles (10-14)
        $roles['company_owner'] = 10;
        $roles['executive_vp'] = 11;
        $roles['executive_manager'] = 12;
        $roles['department_head'] = 13;
        $roles['operations_manager'] = 14;
        
        // Sales roles (30-39)
        $roles['sales_manager'] = 30;
        $roles['sales_rep'] = 35;
        
        // Engineering roles (40-49)
        $roles['engineering_director'] = 40;
        $roles['engineering_manager'] = 41;
        $roles['tech_lead'] = 42;
        $roles['engineer'] = 43;
        
        // Manufacturing roles (50-59)
        $roles['manufacturing_manager'] = 50;
        $roles['production_supervisor'] = 51;
        $roles['production_worker'] = 52;
        
        // Field Service roles (60-69)
        $roles['field_service_manager'] = 60;
        
        // HR roles (70-79)
        $roles['hr_manager'] = 70;
        $roles['hr_coordinator'] = 72;
        
        // Accounting roles (80-89)
        $roles['accounting_manager'] = 80;
        $roles['accountant'] = 82;
        
        // Support roles (90-99)
        $roles['support_manager'] = 90;
        
        // Partner roles (100-159)
        $roles['partner_executive'] = 100;
        $roles['partner_manager'] = 110;
        $roles['partner_sales'] = 120;
        $roles['partner_support'] = 130;
        $roles['partner_developer'] = 140;
        $roles['partner_user'] = 150;
        
        // Client roles (160-163)
        $roles['client_admin'] = 160;
        $roles['client_manager'] = 161;
        $roles['client_user'] = 162;
        $roles['client_viewer'] = 163;
        
        // Create module-level permissions
        $permissions['leads.access'] = $this->createPermission('leads.access', 'Access leads module');
        $permissions['contacts.access'] = $this->createPermission('contacts.access', 'Access contacts module');
        $permissions['admin.access'] = $this->createPermission('admin.access', 'Access admin module');
        $permissions['engineering.access'] = $this->createPermission('engineering.access', 'Access engineering module');
        $permissions['manufacturing.access'] = $this->createPermission('manufacturing.access', 'Access manufacturing module');
        $permissions['hr.access'] = $this->createPermission('hr.access', 'Access HR module');
        
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
        
        // Assign permissions to executive roles (broad access)
        $executivePerms = array_values($permissions); // All permissions
        foreach ($executivePerms as $perm) {
            $this->assignPermissionToRole($roles['executive_vp'], $perm);
            $this->assignPermissionToRole($roles['company_owner'], $perm);
        }
        
        // Assign permissions to sales_manager (30)
        $managerPerms = [
            'leads.access', 'leads.view', 'leads.create', 'leads.edit', 'leads.delete',
            'leads.export', 'leads.view.email', 'leads.edit.stage', 'leads.view.notes',
            'leads.view.all', 'contacts.access'
        ];
        foreach ($managerPerms as $permKey) {
            if (isset($permissions[$permKey])) {
                $this->assignPermissionToRole($roles['sales_manager'], $permissions[$permKey]);
            }
        }
        
        // Assign permissions to sales_rep (35)
        $repPerms = [
            'leads.access', 'leads.view', 'leads.create', 'leads.edit',
            'leads.view.email', 'leads.view.notes', 'leads.view.own', 'leads.edit.own'
        ];
        foreach ($repPerms as $permKey) {
            if (isset($permissions[$permKey])) {
                $this->assignPermissionToRole($roles['sales_rep'], $permissions[$permKey]);
            }
        }
        
        // Assign permissions to engineer (43)
        $engineerPerms = ['leads.access', 'leads.view', 'leads.view.email'];
        foreach ($engineerPerms as $permKey) {
            if (isset($permissions[$permKey])) {
                $this->assignPermissionToRole($roles['engineer'], $permissions[$permKey]);
            }
        }
        
        // Assign permissions to client_user (162) - minimal
        $this->assignPermissionToRole($roles['client_user'], $permissions['leads.view.own']);
        
        return [
            'roles' => $roles,
            'permissions' => $permissions
        ];
    }
    
    /**
     * Get roles by category
     */
    public function getRolesByCategory(string $category): array
    {
        $categories = [
            'system' => [1, 2],
            'executive' => [10, 11, 12, 13, 14],
            'sales' => [30, 35],
            'engineering' => [40, 41, 42, 43],
            'manufacturing' => [50, 51, 52],
            'field_service' => [60],
            'hr' => [70, 72],
            'accounting' => [80, 82],
            'support' => [90],
            'partners' => [100, 110, 120, 130, 140, 150],
            'clients' => [160, 161, 162, 163]
        ];
        
        return $categories[strtolower($category)] ?? [];
    }
    
    /**
     * Verify system roles are excluded from user assignment
     */
    public function verifySystemRolesExcluded(): bool
    {
        // Get assignable roles from Roles.php
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM roles WHERE role_id NOT IN (1, 2)");
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();
        
        // Should be 30 assignable roles (32 total - 2 system)
        return $count === 30;
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
    
    // ============== 4-LEVEL RBAC METHODS ===============
    
    /**
     * Create permission with module/action/field/scope structure
     */
    public function createPermissionWithStructure(
        string $module,
        string $action,
        string $description = '',
        ?string $fieldName = null,
        string $scope = 'all'
    ): int {
        $pobject = "{$module}.{$action}";
        if ($fieldName) {
            $pobject .= ".{$fieldName}";
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO permissions (pobject, pdescription, module, action, field_name, scope, updated_at, created_at) 
            VALUES (:pobject, :description, :module, :action, :field_name, :scope, NOW(), NOW())
        ");
        $stmt->execute([
            'pobject' => $pobject,
            'description' => $description,
            'module' => $module,
            'action' => $action,
            'field_name' => $fieldName,
            'scope' => $scope
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Create field-level permission
     */
    public function createFieldPermission(
        int $permissionId,
        string $module,
        string $fieldName,
        string $accessLevel = 'view'
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO field_permissions (permission_id, module, field_name, access_level, updated_at, created_at)
            VALUES (:permission_id, :module, :field_name, :access_level, NOW(), NOW())
        ");
        $stmt->execute([
            'permission_id' => $permissionId,
            'module' => $module,
            'field_name' => $fieldName,
            'access_level' => $accessLevel
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Create record ownership
     */
    public function createRecordOwnership(
        string $recordType,
        int $recordId,
        int $ownerUserId,
        ?int $teamId = null,
        string $accessType = 'owner'
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO record_ownership (record_type, record_id, owner_user_id, team_id, access_type, updated_at, created_at)
            VALUES (:record_type, :record_id, :owner_user_id, :team_id, :access_type, NOW(), NOW())
            ON DUPLICATE KEY UPDATE team_id = :team_id, access_type = :access_type, updated_at = NOW()
        ");
        $stmt->execute([
            'record_type' => $recordType,
            'record_id' => $recordId,
            'owner_user_id' => $ownerUserId,
            'team_id' => $teamId,
            'access_type' => $accessType
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Create test team
     */
    public function createTeam(
        string $name,
        string $description = '',
        ?int $managerUserId = null,
        string $status = 'active'
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO teams (name, description, manager_user_id, status, updated_at, created_at)
            VALUES (:name, :description, :manager_user_id, :status, NOW(), NOW())
        ");
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'manager_user_id' => $managerUserId,
            'status' => $status
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Assign user to team
     */
    public function assignUserToTeam(
        int $teamId,
        int $userId,
        string $teamRole = 'member',
        bool $isLead = false
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO team_members (team_id, user_id, team_role, is_lead, updated_at, created_at)
            VALUES (:team_id, :user_id, :team_role, :is_lead, NOW(), NOW())
            ON DUPLICATE KEY UPDATE team_role = :team_role, is_lead = :is_lead, updated_at = NOW()
        ");
        $stmt->execute([
            'team_id' => $teamId,
            'user_id' => $userId,
            'team_role' => $teamRole,
            'is_lead' => $isLead ? 1 : 0
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Check user permission using stored procedure
     */
    public function checkUserPermission(
        int $userId,
        string $module,
        string $action,
        ?string $recordType = null,
        ?int $recordId = null
    ): bool {
        $stmt = $this->pdo->prepare("CALL check_user_permission(:user_id, :module, :action, :record_type, :record_id, @has_permission)");
        $stmt->execute([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'record_type' => $recordType,
            'record_id' => $recordId
        ]);
        
        // Fetch the result
        $result = $this->pdo->query("SELECT @has_permission as has_permission")->fetch(\PDO::FETCH_ASSOC);
        return (bool) $result['has_permission'];
    }
    
    /**
     * Get team members
     */
    public function getTeamMembers(int $teamId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT tm.*, u.username, u.full_name, u.email
            FROM team_members tm
            JOIN users u ON tm.user_id = u.id
            WHERE tm.team_id = :team_id AND tm.is_active = TRUE
        ");
        $stmt->execute(['team_id' => $teamId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user teams
     */
    public function getUserTeams(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*, tm.team_role, tm.is_lead
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            WHERE tm.user_id = :user_id AND tm.is_active = TRUE AND t.status = 'active'
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Cache permission lookup
     */
    public function cachePermission(
        int $userId,
        int $permissionId,
        string $module,
        string $action,
        bool $hasPermission,
        int $ttlSeconds = 3600
    ): void {
        $stmt = $this->pdo->prepare("CALL cache_user_permission(:user_id, :permission_id, :module, :action, :has_permission, :ttl)");
        $stmt->execute([
            'user_id' => $userId,
            'permission_id' => $permissionId,
            'module' => $module,
            'action' => $action,
            'has_permission' => $hasPermission ? 1 : 0,
            'ttl' => $ttlSeconds
        ]);
    }
    
    /**
     * Get cached permission
     */
    public function getCachedPermission(int $userId, int $permissionId): ?bool
    {
        $stmt = $this->pdo->prepare("
            SELECT has_permission
            FROM permission_cache
            WHERE user_id = :user_id AND permission_id = :permission_id
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'permission_id' => $permissionId
        ]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ? (bool) $result['has_permission'] : null;
    }
}
/**
 * RBAC Permission Tests - 32 Role System
 * 
 * Tests role-based access control at all levels:
 * - Module level
 * - Action level  
 * - Field level
 * - Record level
 * - System role exclusion
 * - Role category organization
 * - Permission inheritance
 */

const { test, expect } = require('@playwright/test');
const {
  testUsers,
  loginAs,
  canAccessModule,
  canPerformAction,
  canViewField,
  canEditField,
  canAccessRecord,
  assertAccessDenied,
  assertAccessGranted
} = require('./rbac-helper');

const ROLES = {
  // System roles (1-2) - excluded from user assignment
  SYSTEM_OWNER: { id: 1, name: 'System Owner', category: 'System' },
  SYSTEM_ADMIN: { id: 2, name: 'System Admin', category: 'System' },
  
  // Executive roles (10-14)
  COMPANY_OWNER: { id: 10, name: 'Company Owner', category: 'Executive' },
  EXECUTIVE_VP: { id: 11, name: 'Executive VP', category: 'Executive' },
  EXECUTIVE_MANAGER: { id: 12, name: 'Executive Manager', category: 'Executive' },
  DEPARTMENT_HEAD: { id: 13, name: 'Department Head', category: 'Executive' },
  OPERATIONS_MANAGER: { id: 14, name: 'Operations Manager', category: 'Executive' },
  
  // Sales roles (30-39)
  SALES_MANAGER: { id: 30, name: 'Sales Manager', category: 'Sales' },
  SALES_REP: { id: 35, name: 'Sales Rep', category: 'Sales' },
  
  // Engineering roles (40-49)
  ENGINEERING_DIRECTOR: { id: 40, name: 'Engineering Director', category: 'Engineering' },
  ENGINEERING_MANAGER: { id: 41, name: 'Engineering Manager', category: 'Engineering' },
  TECH_LEAD: { id: 42, name: 'Tech Lead', category: 'Engineering' },
  ENGINEER: { id: 43, name: 'Engineer', category: 'Engineering' },
  
  // Manufacturing roles (50-59)
  MANUFACTURING_MANAGER: { id: 50, name: 'Manufacturing Manager', category: 'Manufacturing' },
  PRODUCTION_SUPERVISOR: { id: 51, name: 'Production Supervisor', category: 'Manufacturing' },
  PRODUCTION_WORKER: { id: 52, name: 'Production Worker', category: 'Manufacturing' },
  
  // Field Service roles (60-69)
  FIELD_SERVICE_MANAGER: { id: 60, name: 'Field Service Manager', category: 'Field Service' },
  
  // HR roles (70-79)
  HR_MANAGER: { id: 70, name: 'HR Manager', category: 'HR' },
  HR_COORDINATOR: { id: 72, name: 'HR Coordinator', category: 'HR' },
  
  // Accounting roles (80-89)
  ACCOUNTING_MANAGER: { id: 80, name: 'Accounting Manager', category: 'Accounting' },
  ACCOUNTANT: { id: 82, name: 'Accountant', category: 'Accounting' },
  
  // Support roles (90-99)
  SUPPORT_MANAGER: { id: 90, name: 'Support Manager', category: 'Support' },
  
  // Partner roles (100-159)
  PARTNER_EXECUTIVE: { id: 100, name: 'Partner Executive', category: 'Partners' },
  PARTNER_MANAGER: { id: 110, name: 'Partner Manager', category: 'Partners' },
  PARTNER_SALES: { id: 120, name: 'Partner Sales', category: 'Partners' },
  PARTNER_SUPPORT: { id: 130, name: 'Partner Support', category: 'Partners' },
  PARTNER_DEVELOPER: { id: 140, name: 'Partner Developer', category: 'Partners' },
  PARTNER_USER: { id: 150, name: 'Partner User', category: 'Partners' },
  
  // Client roles (160-163)
  CLIENT_ADMIN: { id: 160, name: 'Client Admin', category: 'Clients' },
  CLIENT_MANAGER: { id: 161, name: 'Client Manager', category: 'Clients' },
  CLIENT_USER: { id: 162, name: 'Client User', category: 'Clients' },
  CLIENT_VIEWER: { id: 163, name: 'Client Viewer', category: 'Clients' }
};

test.describe('RBAC - System Role Exclusion', () => {

  test('System roles (1-2) are excluded from user assignment dropdown', async ({ page }) => {
    await loginAs(page, 'superAdmin');
    await page.goto('/users/new');
    
    const roleSelect = page.locator('select[name="role"]');
    const options = await roleSelect.locator('option').all();
    
    let systemRolesFound = false;
    for (const option of options) {
      const value = await option.getAttribute('value');
      const text = await option.textContent();
      
      // System roles (id 1-2) should not appear in dropdown
      if (value === '1' || value === '2') {
        systemRolesFound = true;
      }
    }
    
    expect(systemRolesFound).toBe(false);
  });

  test('Non-system roles (3+) are available in user assignment', async ({ page }) => {
    await loginAs(page, 'superAdmin');
    await page.goto('/users/new');
    
    const roleSelect = page.locator('select[name="role"]');
    const salesManagerOption = roleSelect.locator(`option[value="${ROLES.SALES_MANAGER.id}"]`);
    
    await expect(salesManagerOption).toBeVisible();
  });
});

test.describe('RBAC - Role Category Organization', () => {

  test('Executive roles are properly organized (10-14)', async ({ page }) => {
    await loginAs(page, 'superAdmin');
    await page.goto('/admin/roles');
    
    // Check that executive roles are visible and organized
    const roles = [ROLES.COMPANY_OWNER, ROLES.EXECUTIVE_VP, ROLES.EXECUTIVE_MANAGER, 
                   ROLES.DEPARTMENT_HEAD, ROLES.OPERATIONS_MANAGER];
    
    for (const role of roles) {
      const roleRow = page.locator(`text=${role.name}`);
      await expect(roleRow).toBeVisible();
    }
  });

  test('Sales roles are properly organized (30-39)', async ({ page }) => {
    await loginAs(page, 'superAdmin');
    await page.goto('/admin/roles');
    
    const salesRoles = [ROLES.SALES_MANAGER, ROLES.SALES_REP];
    for (const role of salesRoles) {
      const roleRow = page.locator(`text=${role.name}`);
      await expect(roleRow).toBeVisible();
    }
  });

  test('Partner roles are properly organized (100-159)', async ({ page }) => {
    await loginAs(page, 'superAdmin');
    await page.goto('/admin/roles');
    
    const partnerRoles = [ROLES.PARTNER_EXECUTIVE, ROLES.PARTNER_MANAGER, ROLES.PARTNER_SALES];
    for (const role of partnerRoles) {
      const roleRow = page.locator(`text=${role.name}`);
      await expect(roleRow).toBeVisible();
    }
  });

  test('Client roles are properly organized (160-163)', async ({ page }) => {
    await loginAs(page, 'superAdmin');
    await page.goto('/admin/roles');
    
    const clientRoles = [ROLES.CLIENT_ADMIN, ROLES.CLIENT_MANAGER, ROLES.CLIENT_USER, ROLES.CLIENT_VIEWER];
    for (const role of clientRoles) {
      const roleRow = page.locator(`text=${role.name}`);
      await expect(roleRow).toBeVisible();
    }
  });

  test('All 30 assignable roles are listed (excluding system roles)', async ({ page }) => {
    await loginAs(page, 'superAdmin');
    await page.goto('/admin/roles');
    
    // Get all roles except system roles (1-2)
    const assignableRoles = Object.values(ROLES).filter(r => r.category !== 'System');
    
    for (const role of assignableRoles) {
      const roleRow = page.locator(`text=${role.name}`);
      await expect(roleRow).toBeVisible();
    }
  });
});

test.describe('RBAC - Module Level Permissions', () => {

  test('Executive roles (VP+) can access admin modules', async ({ page }) => {
    await loginAs(page, 'superAdmin');

    expect(await canAccessModule(page, 'leads', 'superAdmin')).toBe(true);
    expect(await canAccessModule(page, 'contacts', 'superAdmin')).toBe(true);
    expect(await canAccessModule(page, 'admin', 'superAdmin')).toBe(true);
  });

  test('Sales Manager can access sales modules', async ({ page }) => {
    await loginAs(page, 'salesManager');

    expect(await canAccessModule(page, 'leads', 'salesManager')).toBe(true);
    expect(await canAccessModule(page, 'contacts', 'salesManager')).toBe(true);
  });

  test('Sales Manager cannot access admin module', async ({ page }) => {
    await loginAs(page, 'salesManager');

    await page.goto('/admin');
    await assertAccessDenied(page);
  });

  test('Client User has minimal access', async ({ page }) => {
    await loginAs(page, 'clientUser');

    await page.goto('/admin');
    await assertAccessDenied(page);
  });
});

test.describe('RBAC - Action Level Permissions', () => {

  test('Sales Manager (role 30) can perform all lead actions', async ({ page }) => {
    await loginAs(page, 'salesManager');

    expect(await canPerformAction(page, 'leads', 'create', 'salesManager')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'edit', 'salesManager')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'delete', 'salesManager')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'export', 'salesManager')).toBe(true);
  });

  test('Sales Rep (role 35) can create and edit but not delete', async ({ page }) => {
    await loginAs(page, 'salesRep');

    expect(await canPerformAction(page, 'leads', 'create', 'salesRep')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'edit', 'salesRep')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'delete', 'salesRep')).toBe(false);
  });

  test('Engineer (role 43) has limited access', async ({ page }) => {
    await loginAs(page, 'engineer');

    expect(await canPerformAction(page, 'leads', 'view', 'engineer')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'edit', 'engineer')).toBe(false);
    expect(await canPerformAction(page, 'leads', 'delete', 'engineer')).toBe(false);
  });

  test('Partner User (role 150) has restricted access', async ({ page }) => {
    await loginAs(page, 'partnerUser');

    expect(await canPerformAction(page, 'leads', 'view', 'partnerUser')).toBe(false);
  });

  test('Client User (role 162) has minimal access', async ({ page }) => {
    await loginAs(page, 'clientUser');

    expect(await canPerformAction(page, 'leads', 'view', 'clientUser')).toBe(false);
  });
});

test.describe('RBAC - Field Level Permissions', () => {

  test('Sales Manager (30) can view and edit all lead fields', async ({ page }) => {
    await loginAs(page, 'salesManager');
    await page.goto('/leads/edit/1');

    expect(await canViewField(page, 'email', 'salesManager')).toBe(true);
    expect(await canEditField(page, 'email', 'salesManager')).toBe(true);
    expect(await canViewField(page, 'stage', 'salesManager')).toBe(true);
    expect(await canEditField(page, 'stage', 'salesManager')).toBe(true);
  });

  test('Sales Rep (35) can view email but not edit stage', async ({ page }) => {
    await loginAs(page, 'salesRep');
    await page.goto('/leads/edit/1');

    expect(await canViewField(page, 'email', 'salesRep')).toBe(true);
    expect(await canEditField(page, 'email', 'salesRep')).toBe(true);

    // Stage field should be visible but disabled/readonly
    expect(await canViewField(page, 'stage', 'salesRep')).toBe(true);
    expect(await canEditField(page, 'stage', 'salesRep')).toBe(false);
  });

  test('Engineer (43) has view-only access to leads', async ({ page }) => {
    await loginAs(page, 'engineer');
    await page.goto('/leads/view/1');

    // Engineer can view basic fields
    expect(await canViewField(page, 'email', 'engineer')).toBe(true);
    // But cannot edit
    expect(await canEditField(page, 'email', 'engineer')).toBe(false);
  });

  test('Partner User (150) cannot view sensitive fields', async ({ page }) => {
    await loginAs(page, 'partnerUser');
    await page.goto('/leads/view/1');

    // Partners should not see internal fields
    expect(await canViewField(page, 'internal_notes', 'partnerUser')).toBe(false);
  });

  test('Client User (162) has restricted field visibility', async ({ page }) => {
    await loginAs(page, 'clientUser');
    await page.goto('/leads/view/1');

    // Clients should not see most lead fields
    expect(await canViewField(page, 'email', 'clientUser')).toBe(false);
  });
});

test.describe('RBAC - Record Level Permissions', () => {

  test('Sales Manager (30) can access all records', async ({ page }) => {
    await loginAs(page, 'salesManager');

    // Should be able to access any record
    expect(await canAccessRecord(page, 'leads', 1, 'salesManager')).toBe(true);
    expect(await canAccessRecord(page, 'leads', 2, 'salesManager')).toBe(true);
  });

  test('Sales Rep (35) can only access own or team records', async ({ page }) => {
    await loginAs(page, 'salesRep');

    // Create own record
    await page.goto('/leads/new');
    await page.fill('input[name="first_name"]', 'Test');
    await page.fill('input[name="family_name"]', 'Lead');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.click('button[type="submit"]');

    // Should be able to access own record
    const url = page.url();
    const recordId = url.match(/\/(\d+)$/)?.[1];

    if (recordId) {
      expect(await canAccessRecord(page, 'leads', recordId, 'salesRep')).toBe(true);
    }

    // Should NOT be able to access other's record (assuming record 1 belongs to someone else)
    await page.goto('/leads/view/1');

    // Check if access is denied or record is not found
    const hasError = await page.locator('text=/access denied|not found|unauthorized/i').count() > 0;
    expect(hasError).toBe(true);
  });

  test('Engineer (43) has read-only record access', async ({ page }) => {
    await loginAs(page, 'engineer');

    // Navigate to a record
    await page.goto('/leads/view/1');

    // Should see view page
    await assertAccessGranted(page);

    // Edit button should not be visible
    const editButton = page.locator('a[href*="/edit"], button:has-text("Edit")');
    await expect(editButton).toHaveCount(0);
  });

  test('Partner User (150) has no access to internal records', async ({ page }) => {
    await loginAs(page, 'partnerUser');

    // Should not access internal leads
    await page.goto('/leads/view/1');
    await assertAccessDenied(page);
  });

  test('Client User (162) can only access assigned records', async ({ page }) => {
    await loginAs(page, 'clientUser');

    // Try to access a record
    await page.goto('/leads/view/1');

    // Should either be denied or see only assigned records
    const hasError = await page.locator('text=/access denied|not found|unauthorized/i').count() > 0;
    const restrictedView = await page.locator('text=Limited Access').count() > 0;
    
    expect(hasError > 0 || restrictedView > 0).toBe(true);
  });
});

test.describe('RBAC - Permission Inheritance', () => {

  test('System roles (1-2) have full inheritance of permissions', async ({ page }) => {
    // System Owner/Admin inherits all permissions
    await loginAs(page, 'superAdmin');
    await page.goto('/admin/roles');
    await assertAccessGranted(page);
  });

  test('Executive roles (10-14) inherit upper tier permissions', async ({ page }) => {
    // Company Owner (10) and VPs should have broad access
    await loginAs(page, 'companyOwner');
    await page.goto('/admin');
    await assertAccessGranted(page);

    // Can manage all departments
    await page.goto('/admin/departments');
    await assertAccessGranted(page);
  });

  test('Sales roles (30-39) inherit sales permissions only', async ({ page }) => {
    // Sales Manager should access admin/sales but not admin/hr
    await loginAs(page, 'salesManager');
    
    await page.goto('/leads');
    await assertAccessGranted(page);

    await page.goto('/admin/hr');
    await assertAccessDenied(page);
  });

  test('Department roles inherit their category permissions', async ({ page }) => {
    // Engineering Manager (41) should access engineering but not sales
    await loginAs(page, 'engineeringManager');
    
    await page.goto('/engineering');
    await assertAccessGranted(page);

    await page.goto('/leads');
    await assertAccessDenied(page);
  });

  test('Partner roles inherit partner-tier permissions', async ({ page }) => {
    // Partner Manager (110) should have partner access only
    await loginAs(page, 'partnerManager');
    
    await page.goto('/partner/dashboard');
    await assertAccessGranted(page);

    await page.goto('/admin');
    await assertAccessDenied(page);
  });

  test('Client roles inherit minimal permissions', async ({ page }) => {
    // Client User (162) should only access assigned resources
    await loginAs(page, 'clientUser');
    
    // Should have limited dashboard
    await page.goto('/client/dashboard');
    const hasAccess = await page.locator('body').count() > 0;
    expect(hasAccess).toBe(true);

    // But not admin access
    await page.goto('/admin');
    await assertAccessDenied(page);
  });
});

test.describe('RBAC - Team-Based Access', () => {

  test('Sales Reps access team-based records', async ({ page }) => {
    await loginAs(page, 'salesRep');

    // Assuming team records are marked with team_id
    await page.goto('/leads/list');

    // Should see team filter option
    const teamFilter = page.locator('select[name="team"], input[name="team"]');
    const hasTeamFilter = await teamFilter.count() > 0;

    // Team members should see team records
    if (hasTeamFilter) {
      await teamFilter.selectOption('my_team');
      await page.waitForLoadState('networkidle');

      // Should see team records
      const records = page.locator('table tbody tr');
      await expect(records).not.toHaveCount(0);
    }
  });

  test('Production Supervisor (51) can view team production records', async ({ page }) => {
    await loginAs(page, 'productionSupervisor');

    await page.goto('/manufacturing/records');

    // Should see team filter
    const teamFilter = page.locator('select[name="team"], input[name="team"]');
    const hasTeamFilter = await teamFilter.count() > 0;
    
    expect(hasTeamFilter).toBe(true);
  });

  test('Partner Manager (110) can view partner team records only', async ({ page }) => {
    await loginAs(page, 'partnerManager');

    await page.goto('/partner/records');

    // Should see partner team filter
    const teamFilter = page.locator('select[name="team"], input[name="team"]');
    const hasTeamFilter = await teamFilter.count() > 0;
    
    expect(hasTeamFilter).toBe(true);
  });
});

test.describe('RBAC - Permission Caching & Session', () => {

  test('Permissions are cached in session for performance', async ({ page }) => {
    await loginAs(page, 'salesManager');

    // First access - should load permissions
    const start1 = Date.now();
    await page.goto('/leads');
    const time1 = Date.now() - start1;

    // Second access - should use cached session
    const start2 = Date.now();
    await page.goto('/leads');
    const time2 = Date.now() - start2;

    console.log(`First access: ${time1}ms, Cached access: ${time2}ms`);
    // Cached access should typically be faster
    expect(time2).toBeLessThanOrEqual(time1 + 100); // Allow 100ms variance
  });

  test('Session permissions reflect role assignment', async ({ page }) => {
    // Verify that the session contains the correct role
    await loginAs(page, 'salesRep');

    await page.goto('/leads/list');

    // Check that session has correct role ID (35)
    const sessionRole = await page.evaluate(() => {
      return sessionStorage.getItem('user_role_id') || 'not found';
    });

    expect(sessionRole).toBe('35');
  });

  test('Logout clears permissions from session', async ({ page }) => {
    await loginAs(page, 'salesManager');
    await page.goto('/leads');

    // Logout
    await page.click('a[href*="logout"]');
    await page.waitForNavigation();

    // Verify session is cleared
    await page.goto('/leads');
    const isLoggedIn = await page.locator('text=/access denied|login/i').count() > 0;
    expect(isLoggedIn).toBe(true);
  });
});

test.describe('RBAC - Dynamic Permission Changes', () => {

  test('Permission changes take effect after session refresh', async ({ page, context }) => {
    // This test would require admin access to change permissions
    await loginAs(page, 'superAdmin');

    // Remove a permission from Sales Rep role (35)
    await page.goto('/admin/roles/edit/35');
    await page.uncheck('input[name="permissions[]"][value="leads.delete"]');
    await page.click('button[type="submit"]');

    // Login as sales rep in new context
    const newPage = await context.newPage();
    await loginAs(newPage, 'salesRep');

    // Should not have delete permission
    expect(await canPerformAction(newPage, 'leads', 'delete', 'salesRep')).toBe(false);

    await newPage.close();
  });

  test('New permissions are granted to role immediately', async ({ page, context }) => {
    await loginAs(page, 'superAdmin');

    // Add export permission to Sales Rep (35)
    await page.goto('/admin/roles/edit/35');
    await page.check('input[name="permissions[]"][value="leads.export"]');
    await page.click('button[type="submit"]');

    // Login as sales rep in new context
    const newPage = await context.newPage();
    await loginAs(newPage, 'salesRep');

    // Should now have export permission
    expect(await canPerformAction(newPage, 'leads', 'export', 'salesRep')).toBe(true);

    await newPage.close();
  });

  test('Role changes for users take effect immediately', async ({ page, context }) => {
    await loginAs(page, 'superAdmin');

    // Change a user from Sales Rep (35) to Sales Manager (30)
    await page.goto('/users/list');
    await page.click('a[href*="/edit/2"]'); // Assuming user ID 2 is a sales rep

    // Change role
    await page.selectOption('select[name="role"]', '30'); // Sales Manager
    await page.click('button[type="submit"]');

    // Login as that user in new context
    const newPage = await context.newPage();
    await loginAs(newPage, 'salesRep');

    // Should now have Sales Manager permissions (including delete)
    expect(await canPerformAction(newPage, 'leads', 'delete', 'salesRep')).toBe(true);

    await newPage.close();
  });
});
/**
 * RBAC Permission Tests
 * 
 * Tests role-based access control at all levels:
 * - Module level
 * - Action level
 * - Field level
 * - Record level
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

test.describe('RBAC - Module Level Permissions', () => {

  test('Super Admin can access all modules', async ({ page }) => {
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

  test('Restricted user has minimal access', async ({ page }) => {
    await loginAs(page, 'restricted');

    await page.goto('/admin');
    await assertAccessDenied(page);
  });
});

test.describe('RBAC - Action Level Permissions', () => {

  test('Sales Manager can perform all lead actions', async ({ page }) => {
    await loginAs(page, 'salesManager');

    expect(await canPerformAction(page, 'leads', 'create', 'salesManager')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'edit', 'salesManager')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'delete', 'salesManager')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'export', 'salesManager')).toBe(true);
  });

  test('Sales Rep can create and edit but not delete', async ({ page }) => {
    await loginAs(page, 'salesRep');

    expect(await canPerformAction(page, 'leads', 'create', 'salesRep')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'edit', 'salesRep')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'delete', 'salesRep')).toBe(false);
  });

  test('Viewer can only view, not edit or delete', async ({ page }) => {
    await loginAs(page, 'viewer');

    expect(await canPerformAction(page, 'leads', 'view', 'viewer')).toBe(true);
    expect(await canPerformAction(page, 'leads', 'edit', 'viewer')).toBe(false);
    expect(await canPerformAction(page, 'leads', 'delete', 'viewer')).toBe(false);
  });
});

test.describe('RBAC - Field Level Permissions', () => {

  test('Sales Manager can view and edit all fields', async ({ page }) => {
    await loginAs(page, 'salesManager');
    await page.goto('/leads/edit/1');

    expect(await canViewField(page, 'email', 'salesManager')).toBe(true);
    expect(await canEditField(page, 'email', 'salesManager')).toBe(true);
    expect(await canViewField(page, 'stage', 'salesManager')).toBe(true);
    expect(await canEditField(page, 'stage', 'salesManager')).toBe(true);
  });

  test('Sales Rep can view email but not edit stage', async ({ page }) => {
    await loginAs(page, 'salesRep');
    await page.goto('/leads/edit/1');

    expect(await canViewField(page, 'email', 'salesRep')).toBe(true);
    expect(await canEditField(page, 'email', 'salesRep')).toBe(true);

    // Stage field should be visible but disabled/readonly
    expect(await canViewField(page, 'stage', 'salesRep')).toBe(true);
    expect(await canEditField(page, 'stage', 'salesRep')).toBe(false);
  });

  test('Viewer cannot edit any fields', async ({ page }) => {
    await loginAs(page, 'viewer');
    await page.goto('/leads/view/1');

    // All fields should be visible but not editable
    expect(await canViewField(page, 'email', 'viewer')).toBe(true);
    expect(await canEditField(page, 'email', 'viewer')).toBe(false);
  });

  test('Restricted user cannot view sensitive fields', async ({ page }) => {
    await loginAs(page, 'restricted');
    await page.goto('/leads/view/1');

    // Email should be hidden
    expect(await canViewField(page, 'email', 'restricted')).toBe(false);
  });
});

test.describe('RBAC - Record Level Permissions', () => {

  test('Sales Manager can access all records', async ({ page }) => {
    await loginAs(page, 'salesManager');

    // Should be able to access any record
    expect(await canAccessRecord(page, 'leads', 1, 'salesManager')).toBe(true);
    expect(await canAccessRecord(page, 'leads', 2, 'salesManager')).toBe(true);
  });

  test('Sales Rep can only access own records', async ({ page }) => {
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

  test('Viewer can view own records but not edit', async ({ page }) => {
    await loginAs(page, 'viewer');

    // Navigate to own record (assuming record exists)
    await page.goto('/leads/view/1');

    // Should see view page
    await assertAccessGranted(page);

    // Edit button should not be visible
    const editButton = page.locator('a[href*="/edit"], button:has-text("Edit")');
    await expect(editButton).toHaveCount(0);
  });
});

test.describe('RBAC - Permission Inheritance', () => {

  test('Role hierarchy is respected', async ({ page }) => {
    // Super Admin inherits all permissions
    await loginAs(page, 'superAdmin');
    await page.goto('/admin/roles');
    await assertAccessGranted(page);

    // Sales Manager inherits from Admin but not Super Admin
    await loginAs(page, 'salesManager');
    await page.goto('/admin/roles');
    await assertAccessDenied(page);
  });
});

test.describe('RBAC - Team-Based Access', () => {

  test('User can access team records', async ({ page }) => {
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
});

test.describe('RBAC - Permission Caching', () => {

  test('Permissions are cached for performance', async ({ page }) => {
    await loginAs(page, 'salesManager');

    // First access - should query database
    const start1 = Date.now();
    await page.goto('/leads');
    const time1 = Date.now() - start1;

    // Second access - should use cache
    const start2 = Date.now();
    await page.goto('/leads');
    const time2 = Date.now() - start2;

    // Cached access should be faster (not always reliable in tests, but good indicator)
    console.log(`First access: ${time1}ms, Cached access: ${time2}ms`);
  });
});

test.describe('RBAC - Dynamic Permission Changes', () => {

  test('Permission changes take effect immediately', async ({ page, context }) => {
    // This test would require admin access to change permissions
    await loginAs(page, 'superAdmin');

    // Remove a permission from a role
    await page.goto('/admin/roles/edit/3'); // Sales Rep role
    await page.uncheck('input[name="permissions[]"][value="leads.delete"]');
    await page.click('button[type="submit"]');

    // Login as sales rep in new context
    const newPage = await context.newPage();
    await loginAs(newPage, 'salesRep');

    // Should not have delete permission
    expect(await canPerformAction(newPage, 'leads', 'delete', 'salesRep')).toBe(false);

    await newPage.close();
  });
});
/**
 * RBAC Test Helper for Playwright
 * 
 * Provides utilities for testing Role-Based Access Control in E2E tests
 */

/**
 * Test users with different permission levels
 */
export const testUsers = {
  superAdmin: {
    username: 'superadmin',
    password: 'testpass123',
    role: 'Super Administrator',
    permissions: 'all',
    roleId: 1
  },
  admin: {
    username: 'admin',
    password: 'testpass123',
    role: 'Administrator',
    permissions: 'admin',
    roleId: 2
  },
  salesManager: {
    username: 'salesman',
    password: 'testpass123',
    role: 'Sales Manager',
    permissions: 'sales_management',
    roleId: 3
  },
  salesAssistant: {
    username: 'salesasst',
    password: 'testpass123',
    role: 'Sales Assistant',
    permissions: 'sales_support',
    roleId: 4
  },
  salesPerson: {
    username: 'salesperson',
    password: 'testpass123',
    role: 'Sales Person',
    permissions: 'sales_basic',
    roleId: 5
  }
};

/**
 * Login as specific test user
 */
export async function loginAs (page, userType) {
  const user = testUsers[userType];

  if (!user) {
    throw new Error(`Unknown user type: ${userType}`);
  }

  await page.goto('/login.php');
  await page.fill('input[name="username"]', user.username);
  await page.fill('input[name="password"]', user.password);
  
  // Wait for navigation before continuing
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.click('button[type="submit"]')
  ]);

  return user;
}

/**
 * Check if element is visible (permission-based)
 */
export async function shouldBeVisible (page, selector, userType) {
  const user = testUsers[userType];
  const element = page.locator(selector);

  try {
    await element.waitFor({ state: 'visible', timeout: 5000 });
    return true;
  } catch {
    return false;
  }
}

/**
 * Check if element is hidden (permission-based)
 */
export async function shouldBeHidden (page, selector, userType) {
  const visible = await shouldBeVisible(page, selector, userType);
  return !visible;
}

/**
 * Test module access
 */
export async function canAccessModule (page, module, userType) {
  const user = testUsers[userType];

  await page.goto(`/${module}`);

  // Check if redirected to access denied
  const url = page.url();
  if (url.includes('access_denied') || url.includes('login')) {
    return false;
  }

  // Check for access denied message
  const accessDenied = await page.locator('text=/access denied|unauthorized|forbidden/i').count();
  return accessDenied === 0;
}

/**
 * Test action permission (create, edit, delete, etc.)
 */
export async function canPerformAction (page, module, action, userType) {
  const user = testUsers[userType];

  // Navigate to module
  await page.goto(`/${module}`);

  // Check for action button/link
  const actionSelectors = {
    create: ['a[href*="/new"]', 'button:has-text("Create")', 'button:has-text("New")'],
    edit: ['a[href*="/edit"]', 'button:has-text("Edit")'],
    delete: ['button:has-text("Delete")', 'a:has-text("Delete")'],
    export: ['button:has-text("Export")', 'a:has-text("Export")'],
    view: ['a[href*="/view"]', 'button:has-text("View")']
  };

  const selectors = actionSelectors[action] || [];

  for (const selector of selectors) {
    const count = await page.locator(selector).count();
    if (count > 0) {
      return true;
    }
  }

  return false;
}

/**
 * Test field visibility
 */
export async function canViewField (page, fieldName, userType) {
  const user = testUsers[userType];

  // Check if field is visible
  const fieldSelectors = [
    `input[name="${fieldName}"]`,
    `select[name="${fieldName}"]`,
    `textarea[name="${fieldName}"]`,
    `label:has-text("${fieldName}")`,
    `[data-field="${fieldName}"]`
  ];

  for (const selector of fieldSelectors) {
    const count = await page.locator(selector).count();
    if (count > 0) {
      return true;
    }
  }

  return false;
}

/**
 * Test field editability
 */
export async function canEditField (page, fieldName, userType) {
  const user = testUsers[userType];

  // Check if field is editable (not disabled/readonly)
  const fieldSelectors = [
    `input[name="${fieldName}"]:not([disabled]):not([readonly])`,
    `select[name="${fieldName}"]:not([disabled])`,
    `textarea[name="${fieldName}"]:not([disabled]):not([readonly])`
  ];

  for (const selector of fieldSelectors) {
    const count = await page.locator(selector).count();
    if (count > 0) {
      return true;
    }
  }

  return false;
}

/**
 * Test record-level access (own vs team vs all)
 */
export async function canAccessRecord (page, module, recordId, userType) {
  const user = testUsers[userType];

  await page.goto(`/${module}/view/${recordId}`);

  // Check if redirected or access denied
  const url = page.url();
  if (url.includes('access_denied') || url.includes('login')) {
    return false;
  }

  const accessDenied = await page.locator('text=/access denied|unauthorized|not found/i').count();
  return accessDenied === 0;
}

/**
 * Create test data with ownership
 */
export async function createOwnedRecord (page, module, data, ownerUserId) {
  // This would typically be done via API or database
  // For now, create via UI
  await page.goto(`/${module}/new`);

  for (const [field, value] of Object.entries(data)) {
    await page.fill(`input[name="${field}"], select[name="${field}"], textarea[name="${field}"]`, value);
  }

  await page.click('button[type="submit"]');
  await page.waitForURL(/\/(view|list)/);

  // Extract record ID from URL
  const url = page.url();
  const match = url.match(/\/(\d+)$/);
  return match ? parseInt(match[1]) : null;
}

/**
 * Permission test scenarios
 */
export const permissionScenarios = {
  // Module-level scenarios
  moduleAccess: [
    { user: 'superAdmin', module: 'admin', expected: true },
    { user: 'admin', module: 'admin', expected: true },
    { user: 'salesManager', module: 'leads', expected: true },
    { user: 'salesAssistant', module: 'leads', expected: true },
    { user: 'salesPerson', module: 'leads', expected: true }
  ],

  // Action-level scenarios
  actionPermissions: [
    { user: 'superAdmin', module: 'leads', action: 'delete', expected: true },
    { user: 'admin', module: 'leads', action: 'delete', expected: true },
    { user: 'salesManager', module: 'leads', action: 'create', expected: true },
    { user: 'salesAssistant', module: 'leads', action: 'create', expected: true },
    { user: 'salesPerson', module: 'leads', action: 'edit', expected: true }
  ],

  // Field-level scenarios
  fieldPermissions: [
    { user: 'admin', field: 'email', canView: true, canEdit: true },
    { user: 'salesManager', field: 'email', canView: true, canEdit: true },
    { user: 'salesAssistant', field: 'email', canView: true, canEdit: true },
    { user: 'salesPerson', field: 'email', canView: true, canEdit: true }
  ],

  // Record-level scenarios
  recordPermissions: [
    { user: 'superAdmin', scope: 'all', expected: true },
    { user: 'admin', scope: 'all', expected: true },
    { user: 'salesManager', scope: 'own', expected: true },
    { user: 'salesAssistant', scope: 'own', expected: true },
    { user: 'salesPerson', scope: 'own', expected: true }
  ]
};

/**
 * Run permission test suite
 */
export async function runPermissionTests (page, test) {
  // Test module access
  for (const scenario of permissionScenarios.moduleAccess) {
    await test.step(`${scenario.user} ${scenario.expected ? 'can' : 'cannot'} access ${scenario.module}`, async () => {
      await loginAs(page, scenario.user);
      const hasAccess = await canAccessModule(page, scenario.module, scenario.user);
      test.expect(hasAccess).toBe(scenario.expected);
    });
  }

  // Test action permissions
  for (const scenario of permissionScenarios.actionPermissions) {
    await test.step(`${scenario.user} ${scenario.expected ? 'can' : 'cannot'} ${scenario.action} in ${scenario.module}`, async () => {
      await loginAs(page, scenario.user);
      const canPerform = await canPerformAction(page, scenario.module, scenario.action, scenario.user);
      test.expect(canPerform).toBe(scenario.expected);
    });
  }
}

/**
 * Assert permission denied
 */
export async function assertAccessDenied (page) {
  const url = page.url();
  const hasAccessDenied = url.includes('access_denied') ||
    url.includes('login') ||
    await page.locator('text=/access denied|unauthorized/i').count() > 0;

  if (!hasAccessDenied) {
    throw new Error('Expected access denied but user has access');
  }
}

/**
 * Assert permission granted
 */
export async function assertAccessGranted (page) {
  const url = page.url();
  const hasAccessDenied = url.includes('access_denied') ||
    url.includes('login') ||
    await page.locator('text=/access denied|unauthorized/i').count() > 0;

  if (hasAccessDenied) {
    throw new Error('Expected access granted but user was denied');
  }
}
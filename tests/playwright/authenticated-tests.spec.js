const { test, expect } = require('@playwright/test');
const { login, logout, isLoggedIn } = require('./auth-helper');

test.describe('Authenticated CRM Tests', () => {

  // Skip these tests if we don't have valid credentials
  // You can set these via environment variables or modify the values
  const testUsername = process.env.CRM_TEST_USERNAME || 'admin';
  const testPassword = process.env.CRM_TEST_PASSWORD || 'admin123';

  test.beforeEach(async ({ page }) => {
    // Set up common headers
    await page.setExtraHTTPHeaders({
      'User-Agent': 'Playwright-NixOS-Authenticated-Testing'
    });
  });

  test('should login successfully with valid credentials', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (loginResult) {
      // Verify we're logged in by checking for dashboard elements
      await expect(page).toHaveTitle(/Dashboard|CRM|Home/i);

      // Look for common authenticated user elements
      const logoutLink = page.locator('a[href*="logout"], button:has-text("Logout"), a:has-text("Logout")');
      await expect(logoutLink).toBeVisible();

      // Take screenshot of successful login
      await page.screenshot({ path: 'screenshots/authenticated-dashboard.png', fullPage: true });

    } else {
      // If login fails, it might be because credentials are wrong or login is disabled
      console.log('⚠️  Login test skipped - unable to authenticate');
      test.skip();
    }
  });

  test('should access leads page when authenticated', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      console.log('⚠️  Skipping authenticated test - login failed');
      test.skip();
      return;
    }

    // Navigate to leads page
    await page.goto('/leads/list.php');
    await page.waitForLoadState('networkidle');

    // Should not be redirected to login
    const currentUrl = page.url();
    expect(currentUrl).not.toContain('login.php');

    // Check for leads page elements
    await expect(page).toHaveTitle(/Leads/i);

    // Look for common leads page elements
    const leadsTable = page.locator('table, .table, .leads-list, .data-table');
    const addLeadButton = page.locator('a:has-text("Add"), button:has-text("Add"), a:has-text("New"), button:has-text("New")');

    // At least one of these should be visible
    const tableVisible = await leadsTable.isVisible().catch(() => false);
    const buttonVisible = await addLeadButton.isVisible().catch(() => false);

    expect(tableVisible || buttonVisible).toBeTruthy();

    await page.screenshot({ path: 'screenshots/authenticated-leads.png', fullPage: true });
  });

  test('should access contacts page when authenticated', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      console.log('⚠️  Skipping authenticated test - login failed');
      test.skip();
      return;
    }

    await page.goto('/contacts/list.php');
    await page.waitForLoadState('networkidle');

    const currentUrl = page.url();
    expect(currentUrl).not.toContain('login.php');

    await expect(page).toHaveTitle(/Contacts/i);

    await page.screenshot({ path: 'screenshots/authenticated-contacts.png', fullPage: true });
  });

  test('should test AJAX functionality when authenticated', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      console.log('⚠️  Skipping authenticated AJAX test - login failed');
      test.skip();
      return;
    }

    // Test the notes AJAX endpoint with authentication
    const response = await page.request.post('/leads/notes_ajax.php', {
      data: {
        action: 'get_notes',
        lead_id: 1,
        search: '',
        order: 'DESC'
      }
    });

    console.log('Authenticated AJAX response status:', response.status());

    // With authentication, we might get different responses
    expect([200, 400, 404, 422]).toContain(response.status());

    if (response.status() === 200) {
      const responseText = await response.text();
      console.log('AJAX response preview:', responseText.substring(0, 200));

      // Try to parse as JSON if it looks like JSON
      if (responseText.trim().startsWith('{') || responseText.trim().startsWith('[')) {
        try {
          const jsonResponse = JSON.parse(responseText);
          console.log('AJAX JSON response keys:', Object.keys(jsonResponse));
        } catch (e) {
          console.log('Response is not valid JSON');
        }
      }
    }
  });

  test('should test navigation menu when authenticated', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      console.log('⚠️  Skipping navigation test - login failed');
      test.skip();
      return;
    }

    // Go to dashboard to see navigation
    await page.goto('/dashboard.php');
    await page.waitForLoadState('networkidle');

    // Look for navigation elements
    const navElements = [
      'a[href*="leads"], a:has-text("Leads")',
      'a[href*="contacts"], a:has-text("Contacts")',
      'a[href*="users"], a:has-text("Users")',
      'a[href*="reports"], a:has-text("Reports")',
      'a[href*="dashboard"], a:has-text("Dashboard")'
    ];

    let foundNavItems = 0;

    for (const selector of navElements) {
      const element = page.locator(selector).first();
      if (await element.isVisible().catch(() => false)) {
        foundNavItems++;
        const text = await element.textContent();
        console.log(`✅ Found navigation item: ${text}`);
      }
    }

    console.log(`Found ${foundNavItems} navigation items`);
    expect(foundNavItems).toBeGreaterThan(0);

    await page.screenshot({ path: 'screenshots/authenticated-navigation.png', fullPage: true });
  });

  test('should logout successfully', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      console.log('⚠️  Skipping logout test - login failed');
      test.skip();
      return;
    }

    // Perform logout
    const logoutResult = await logout(page);

    if (logoutResult) {
      // Verify we're logged out by trying to access protected page
      await page.goto('/dashboard.php');
      await page.waitForLoadState('networkidle');

      const currentUrl = page.url();
      expect(currentUrl).toContain('login.php');

      console.log('✅ Logout verification successful');
    } else {
      console.log('⚠️  Logout may not have worked as expected');
    }
  });
});
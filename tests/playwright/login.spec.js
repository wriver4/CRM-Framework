// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('CRM Login Tests', () => {
  test('login page loads correctly', async ({ page }) => {
    await page.goto('/login.php');

    // Check page title
    await expect(page).toHaveTitle(/Login|CRM|DemoCRM/);

    // Check login form elements exist
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[type="submit"], button[type="submit"]')).toBeVisible();
  });

  test('login form validation', async ({ page }) => {
    await page.goto('/login.php');

    // Try to submit empty form
    await page.click('input[type="submit"], button[type="submit"]');

    // Should stay on login page or show validation message
    const currentUrl = page.url();
    expect(currentUrl).toContain('login');
  });

  test('invalid login attempt', async ({ page }) => {
    await page.goto('/login.php');

    // Fill in invalid credentials
    await page.fill('input[name="username"]', 'invalid_user');
    await page.fill('input[name="password"]', 'invalid_password');
    await page.click('input[type="submit"], button[type="submit"]');

    // Should show error or stay on login page
    await expect(page.locator('body')).toContainText(/invalid|error|incorrect/i);
  });

  test('valid login redirects to dashboard', async ({ page }) => {
    const { DEFAULT_TEST_USER } = require('./test-credentials');

    await page.goto('/login.php');

    // Fill in valid test credentials
    await page.fill('input[name="username"]', DEFAULT_TEST_USER.username);
    await page.fill('input[name="password"]', DEFAULT_TEST_USER.password);
    await page.click('input[type="submit"], button[type="submit"]');

    // Wait for navigation
    await page.waitForLoadState('networkidle');

    // Should redirect away from login page
    const currentUrl = page.url();
    expect(currentUrl).not.toContain('login.php');

    // Should be on dashboard or main page
    await expect(page).toHaveURL(/dashboard|index/);
  });

  test('test all user roles can login', async ({ page }) => {
    const { TEST_CREDENTIALS } = require('./test-credentials');

    for (const [roleName, credentials] of Object.entries(TEST_CREDENTIALS)) {
      await test.step(`Login as ${credentials.role}`, async () => {
        await page.goto('/login.php');

        await page.fill('input[name="username"]', credentials.username);
        await page.fill('input[name="password"]', credentials.password);
        await page.click('input[type="submit"], button[type="submit"]');

        await page.waitForLoadState('networkidle');

        // Should redirect away from login page
        const currentUrl = page.url();
        expect(currentUrl).not.toContain('login.php');

        // Logout for next test
        await page.goto('/logout.php');
        await page.waitForLoadState('networkidle');
      });
    }
  });
});
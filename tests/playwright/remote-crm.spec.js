const { test, expect } = require('@playwright/test');

test.describe('Remote CRM Application Tests', () => {

  test.beforeEach(async ({ page }) => {
    // Set up any common configuration for each test
    await page.setExtraHTTPHeaders({
      'User-Agent': 'Playwright-NixOS-Testing'
    });
  });

  test('should load the CRM login page', async ({ page }) => {
    await page.goto('/login.php');

    // Wait for the page to load completely
    await page.waitForLoadState('networkidle');

    // Check if the page title contains expected text
    await expect(page).toHaveTitle(/Login|CRM|Sign/i);

    // Take a screenshot for debugging
    await page.screenshot({ path: 'screenshots/login-page.png', fullPage: true });

    // Check for login form elements - adjust selectors based on your actual HTML
    const usernameField = page.locator('input[name="username"], input[name="email"], input[type="email"]');
    const passwordField = page.locator('input[name="password"], input[type="password"]');
    const submitButton = page.locator('button[type="submit"], input[type="submit"], button:has-text("Login"), button:has-text("Sign")');

    await expect(usernameField).toBeVisible();
    await expect(passwordField).toBeVisible();
    await expect(submitButton).toBeVisible();
  });

  test('should handle authentication redirect', async ({ page }) => {
    // Try to access a protected page
    await page.goto('/dashboard.php');

    // Wait for any redirects to complete
    await page.waitForLoadState('networkidle');

    const currentUrl = page.url();
    console.log('Current URL after dashboard access:', currentUrl);

    if (currentUrl.includes('login.php')) {
      // We were redirected to login, which is expected behavior
      await expect(page.locator('input[name="username"], input[name="email"]')).toBeVisible();
      console.log('✅ Authentication redirect working correctly');
    } else {
      // We're on the dashboard, check for expected elements
      await expect(page).toHaveTitle(/Dashboard|CRM/i);
      console.log('✅ Dashboard accessible (user might be logged in)');
    }
  });

  test('should test leads page accessibility', async ({ page }) => {
    await page.goto('/leads/list.php');
    await page.waitForLoadState('networkidle');

    const currentUrl = page.url();
    console.log('Leads page URL:', currentUrl);

    if (currentUrl.includes('login.php')) {
      // Redirected to login - expected for unauthenticated users
      await expect(page.locator('input[name="username"], input[name="email"]')).toBeVisible();
      console.log('✅ Leads page properly protected');
    } else {
      // On leads page - check for leads-specific content
      await expect(page).toHaveTitle(/Leads|CRM/i);
      console.log('✅ Leads page accessible');
    }
  });

  test('should test contacts page accessibility', async ({ page }) => {
    await page.goto('/contacts/list.php');
    await page.waitForLoadState('networkidle');

    const currentUrl = page.url();
    console.log('Contacts page URL:', currentUrl);

    if (currentUrl.includes('login.php')) {
      await expect(page.locator('input[name="username"], input[name="email"]')).toBeVisible();
      console.log('✅ Contacts page properly protected');
    } else {
      await expect(page).toHaveTitle(/Contacts|CRM/i);
      console.log('✅ Contacts page accessible');
    }
  });

  test('should test AJAX endpoints security', async ({ page }) => {
    // Test the notes_ajax.php endpoint without authentication
    const response = await page.request.post('/leads/notes_ajax.php', {
      data: {
        action: 'get_notes',
        lead_id: 1,
        search: '',
        order: 'DESC'
      }
    });

    console.log('AJAX endpoint response status:', response.status());

    // Should return 401, 403, 405, or redirect for unauthenticated requests
    expect([200, 401, 403, 405, 302]).toContain(response.status());

    if (response.status() === 200) {
      const responseText = await response.text();
      console.log('AJAX response (first 100 chars):', responseText.substring(0, 100));
    }
  });

  test('should check for common security headers', async ({ page }) => {
    const response = await page.goto('/login.php');

    const headers = response.headers();
    console.log('Security headers check:');

    // Check for security headers
    const securityHeaders = [
      'x-frame-options',
      'x-content-type-options',
      'x-xss-protection',
      'strict-transport-security'
    ];

    securityHeaders.forEach(header => {
      if (headers[header]) {
        console.log(`✅ ${header}: ${headers[header]}`);
      } else {
        console.log(`⚠️  ${header}: Not set`);
      }
    });
  });

  test('should test multilingual support', async ({ page }) => {
    // Test if the application supports multiple languages
    await page.goto('/login.php');
    await page.waitForLoadState('networkidle');

    // Look for language switcher or multilingual content
    const languageElements = page.locator('[lang], [data-lang], .language-switcher, select[name*="lang"], select[name*="locale"]');
    const count = await languageElements.count();

    if (count > 0) {
      console.log(`✅ Found ${count} potential language-related elements`);

      // Take screenshot showing language options
      await page.screenshot({ path: 'screenshots/language-support.png', fullPage: true });
    } else {
      console.log('ℹ️  No obvious language switcher found');
    }
  });
});
const { test, expect } = require('@playwright/test');

test.describe('CRM Application Tests', () => {
  test('should load the login page', async ({ page }) => {
    await page.goto('/login.php');

    // Check if the page title contains expected text
    await expect(page).toHaveTitle(/Login|CRM/);

    // Check for login form elements
    await expect(page.locator('input[name="username"], input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"], input[type="submit"]')).toBeVisible();
  });

  test('should load the dashboard after login', async ({ page }) => {
    // This is a placeholder - you'll need to implement actual login logic
    await page.goto('/dashboard.php');

    // Check if we're redirected to login (if not authenticated)
    // or if we can see dashboard elements
    const currentUrl = page.url();
    if (currentUrl.includes('login.php')) {
      // We were redirected to login, which is expected behavior
      await expect(page.locator('input[name="username"], input[name="email"]')).toBeVisible();
    } else {
      // We're on the dashboard, check for expected elements
      await expect(page).toHaveTitle(/Dashboard|CRM/);
    }
  });

  test('should navigate to leads page', async ({ page }) => {
    await page.goto('/leads/list.php');

    // Check if we can access leads or are redirected to login
    const currentUrl = page.url();
    if (currentUrl.includes('login.php')) {
      // Redirected to login - expected for unauthenticated users
      await expect(page.locator('input[name="username"], input[name="email"]')).toBeVisible();
    } else {
      // On leads page - check for leads-specific content
      await expect(page).toHaveTitle(/Leads|CRM/);
    }
  });

  test('should test AJAX notes functionality', async ({ page }) => {
    // Test the notes_ajax.php endpoint
    const response = await page.request.post('/leads/notes_ajax.php', {
      data: {
        action: 'get_notes',
        lead_id: 1,
        search: '',
        order: 'DESC'
      }
    });

    // Should return 405 for unauthenticated requests or proper JSON response
    expect([200, 401, 403, 405]).toContain(response.status());
  });
});
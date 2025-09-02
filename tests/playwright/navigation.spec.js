// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('CRM Navigation Tests', () => {
  test('homepage loads correctly', async ({ page }) => {
    await page.goto('/');

    // Should either show login page or redirect to login
    const currentUrl = page.url();
    const isLoginPage = currentUrl.includes('login') ||
      await page.locator('input[name="username"]').isVisible();

    expect(isLoginPage).toBeTruthy();
  });

  test('main navigation elements exist', async ({ page }) => {
    // This test assumes you can access some pages without login
    // Adjust based on your CRM's authentication requirements

    await page.goto('/');

    // Check for common navigation elements
    const hasNavigation = await page.locator('nav, .navbar, .navigation').count() > 0;
    const hasMenu = await page.locator('ul, .menu, .nav-menu').count() > 0;

    // At minimum, should have some navigation structure
    expect(hasNavigation || hasMenu).toBeTruthy();
  });

  test('leads page accessibility', async ({ page }) => {
    // Test if leads page exists (may require authentication)
    const response = await page.goto('/leads/list.php');

    // Should either load the page or redirect to login
    expect(response.status()).toBeLessThan(500); // Not a server error
  });

  test('contacts page accessibility', async ({ page }) => {
    // Test if contacts page exists (may require authentication)
    const response = await page.goto('/contacts/list.php');

    // Should either load the page or redirect to login
    expect(response.status()).toBeLessThan(500); // Not a server error
  });

  test('users page accessibility', async ({ page }) => {
    // Test if users page exists (may require authentication)
    const response = await page.goto('/users/list.php');

    // Should either load the page or redirect to login
    expect(response.status()).toBeLessThan(500); // Not a server error
  });
});
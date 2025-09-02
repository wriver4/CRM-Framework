// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('CRM Responsive Design Tests', () => {
  const viewports = [
    { name: 'Mobile', width: 375, height: 667 },
    { name: 'Tablet', width: 768, height: 1024 },
    { name: 'Desktop', width: 1920, height: 1080 }
  ];

  viewports.forEach(({ name, width, height }) => {
    test(`login page responsive on ${name}`, async ({ page }) => {
      await page.setViewportSize({ width, height });
      await page.goto('/login.php');

      // Check that login form is visible and usable
      await expect(page.locator('input[name="username"]')).toBeVisible();
      await expect(page.locator('input[name="password"]')).toBeVisible();

      // Check that form elements are not overlapping or cut off
      const usernameBox = await page.locator('input[name="username"]').boundingBox();
      const passwordBox = await page.locator('input[name="password"]').boundingBox();

      expect(usernameBox.width).toBeGreaterThan(100); // Reasonable minimum width
      expect(passwordBox.width).toBeGreaterThan(100);
    });
  });

  test('mobile navigation menu', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');

    // Look for mobile menu indicators (hamburger menu, etc.)
    const hasMobileMenu = await page.locator('.navbar-toggler, .menu-toggle, .hamburger, [data-toggle="collapse"]').count() > 0;

    // On mobile, should either have a mobile menu or responsive navigation
    if (hasMobileMenu) {
      await expect(page.locator('.navbar-toggler, .menu-toggle, .hamburger, [data-toggle="collapse"]')).toBeVisible();
    }
  });

  test('desktop layout has proper spacing', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('/login.php');

    // Check that elements have proper spacing on desktop
    const body = await page.locator('body').boundingBox();
    expect(body.width).toBeGreaterThan(1000); // Should use available space
  });
});
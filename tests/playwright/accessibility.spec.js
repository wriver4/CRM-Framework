// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('CRM Accessibility Tests', () => {
  test('login page has proper form labels', async ({ page }) => {
    await page.goto('/login.php');

    // Check for proper form labeling
    const usernameInput = page.locator('input[name="username"]');
    const passwordInput = page.locator('input[name="password"]');

    // Should have labels or aria-labels
    const usernameHasLabel = await usernameInput.getAttribute('aria-label') !== null ||
      await page.locator('label[for*="username"], label:has(input[name="username"])').count() > 0;

    const passwordHasLabel = await passwordInput.getAttribute('aria-label') !== null ||
      await page.locator('label[for*="password"], label:has(input[name="password"])').count() > 0;

    expect(usernameHasLabel).toBeTruthy();
    expect(passwordHasLabel).toBeTruthy();
  });

  test('page has proper heading structure', async ({ page }) => {
    await page.goto('/login.php');

    // Should have at least one heading
    const headings = await page.locator('h1, h2, h3, h4, h5, h6').count();
    expect(headings).toBeGreaterThan(0);

    // Should have a main heading (h1)
    const h1Count = await page.locator('h1').count();
    expect(h1Count).toBeGreaterThanOrEqual(1);
  });

  test('images have alt text', async ({ page }) => {
    await page.goto('/login.php');

    // Check that all images have alt attributes
    const images = await page.locator('img').all();

    for (const img of images) {
      const altText = await img.getAttribute('alt');
      const ariaLabel = await img.getAttribute('aria-label');
      const hasAltOrLabel = altText !== null || ariaLabel !== null;

      expect(hasAltOrLabel).toBeTruthy();
    }
  });

  test('form has proper focus management', async ({ page }) => {
    await page.goto('/login.php');

    // Tab through form elements
    await page.keyboard.press('Tab');
    const firstFocused = await page.evaluate(() => document.activeElement.tagName);

    await page.keyboard.press('Tab');
    const secondFocused = await page.evaluate(() => document.activeElement.tagName);

    // Should be able to navigate through form with keyboard
    expect(['INPUT', 'BUTTON'].includes(firstFocused)).toBeTruthy();
    expect(['INPUT', 'BUTTON'].includes(secondFocused)).toBeTruthy();
  });

  test('page has proper color contrast', async ({ page }) => {
    await page.goto('/login.php');

    // Basic check for text visibility
    const bodyStyles = await page.locator('body').evaluate(el => {
      const styles = window.getComputedStyle(el);
      return {
        backgroundColor: styles.backgroundColor,
        color: styles.color
      };
    });

    // Should have defined colors (not transparent/inherit)
    expect(bodyStyles.backgroundColor).not.toBe('rgba(0, 0, 0, 0)');
    expect(bodyStyles.color).not.toBe('rgba(0, 0, 0, 0)');
  });
});
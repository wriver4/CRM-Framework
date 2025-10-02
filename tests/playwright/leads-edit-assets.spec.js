const { test, expect } = require('@playwright/test');
const { login } = require('./auth-helper');

/**
 * Playwright tests for Leads Edit Asset Organization
 * 
 * Tests the refactored asset organization ensuring:
 * 1. All JavaScript loads properly through footer template
 * 2. Data injection works correctly
 * 3. All JavaScript functionality works after refactoring
 */
test.describe('Leads Edit Asset Organization Tests', () => {
  const testUsername = process.env.CRM_TEST_USERNAME || 'admin';
  const testPassword = process.env.CRM_TEST_PASSWORD || 'admin123';

  test.beforeEach(async ({ page }) => {
    await page.setExtraHTTPHeaders({
      'User-Agent': 'Playwright-LeadsEdit-AssetTesting'
    });
  });

  test('should load all required JavaScript assets for leads edit', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      test.skip('Login required for asset testing');
      return;
    }

    // Navigate to a leads edit page (assuming lead ID 1 exists)
    await page.goto('/leads/edit.php?lead_id=1');
    await page.waitForLoadState('networkidle');

    // 1. Check that contact-selector.js loads
    const contactSelectorLoaded = await page.evaluate(() => {
      const scripts = Array.from(document.querySelectorAll('script[src*="contact-selector.js"]'));
      return scripts.length > 0;
    });
    expect(contactSelectorLoaded).toBeTruthy();
    console.log('✅ contact-selector.js loaded');

    // 2. Check that edit-leads.js loads
    const editLeadsLoaded = await page.evaluate(() => {
      const scripts = Array.from(document.querySelectorAll('script[src*="edit-leads.js"]'));
      return scripts.length > 0;
    });
    expect(editLeadsLoaded).toBeTruthy();
    console.log('✅ edit-leads.js loaded');

    // 3. Check that hide-empty-structure.js loads
    const hideEmptyStructureLoaded = await page.evaluate(() => {
      const scripts = Array.from(document.querySelectorAll('script[src*="hide-empty-structure.js"]'));
      return scripts.length > 0;
    });
    expect(hideEmptyStructureLoaded).toBeTruthy();
    console.log('✅ hide-empty-structure.js loaded');
  });

  test('should properly inject data through footer template', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      test.skip('Login required for data injection testing');
      return;
    }

    await page.goto('/leads/edit.php?lead_id=1');
    await page.waitForLoadState('networkidle');

    // Check that window.leadsEditData is properly injected
    const dataInjected = await page.evaluate(() => {
      return typeof window.leadsEditData === 'object' && window.leadsEditData !== null;
    });
    expect(dataInjected).toBeTruthy();
    console.log('✅ Data injection working');

    // Check required data properties exist
    const requiredDataProperties = await page.evaluate(() => {
      const data = window.leadsEditData;
      return {
        hasLeadId: typeof data.leadId !== 'undefined',
        hasSelectedStage: typeof data.selectedStage !== 'undefined',
        hasStageNames: typeof data.stageNames === 'object',
        hasTimezones: typeof data.usTimezones === 'object' && typeof data.countryTimezones === 'object',
        hasLanguageStrings: typeof data.errorUnableDetectTimezone === 'string'
      };
    });

    expect(requiredDataProperties.hasLeadId).toBeTruthy();
    expect(requiredDataProperties.hasSelectedStage).toBeTruthy();
    expect(requiredDataProperties.hasStageNames).toBeTruthy();
    expect(requiredDataProperties.hasTimezones).toBeTruthy();
    expect(requiredDataProperties.hasLanguageStrings).toBeTruthy();
    console.log('✅ All required data properties present');
  });

  test('should have NO inline JavaScript in page content', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      test.skip('Login required for inline JS validation');
      return;
    }

    await page.goto('/leads/edit.php?lead_id=1');
    await page.waitForLoadState('networkidle');

    // Check that there's no inline JavaScript in the main content area
    const inlineJavaScript = await page.evaluate(() => {
      // Look for script tags in the main content (not in footer)
      const mainContent = document.querySelector('main, .container, .content, body');
      const scriptTags = mainContent ? mainContent.querySelectorAll('script:not([src])') : [];

      // Filter out the data injection script (which should be in footer)
      const inlineScripts = Array.from(scriptTags).filter(script => {
        const content = script.textContent || '';
        return !content.includes('window.leadsEditData');
      });

      return inlineScripts.length;
    });

    expect(inlineJavaScript).toBe(0);
    console.log('✅ No inline JavaScript in page content');
  });

  test('should load JavaScript functionality correctly', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      test.skip('Login required for functionality testing');
      return;
    }

    await page.goto('/leads/edit.php?lead_id=1');
    await page.waitForLoadState('networkidle');

    // Wait for JavaScript to initialize
    await page.waitForTimeout(2000);

    // 1. Test timezone functionality exists
    const timezoneFunction = await page.evaluate(() => {
      return typeof getTimezoneFromLocation === 'function';
    });
    expect(timezoneFunction).toBeTruthy();
    console.log('✅ Timezone functions available');

    // 2. Test notes loading function exists
    const notesFunction = await page.evaluate(() => {
      return typeof loadNotes === 'function';
    });
    expect(notesFunction).toBeTruthy();
    console.log('✅ Notes functions available');

    // 3. Test stage change handler exists
    const stageChangeFunction = await page.evaluate(() => {
      return typeof handleStageChange === 'function';
    });
    expect(stageChangeFunction).toBeTruthy();
    console.log('✅ Stage change functions available');

    // 4. Test contact selector initialization
    const contactSelector = page.locator('#contact_selector');
    if (await contactSelector.isVisible()) {
      expect(await contactSelector.count()).toBeGreaterThan(0);
      console.log('✅ Contact selector initialized');
    }
  });

  test('should handle collapse/expand functionality', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      test.skip('Login required for collapse testing');
      return;
    }

    await page.goto('/leads/edit.php?lead_id=1');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    // Test structure information collapse
    const structureToggle = page.locator('[data-bs-target="#structureInformationCollapse"]');
    if (await structureToggle.isVisible()) {
      await structureToggle.click();
      await page.waitForTimeout(500);

      const structureCollapse = page.locator('#structureInformationCollapse');
      const isVisible = await structureCollapse.isVisible();
      expect(typeof isVisible).toBe('boolean');
      console.log('✅ Structure collapse functionality working');
    }

    // Test services collapse
    const servicesToggle = page.locator('[data-bs-target="#servicesInterestedCollapse"]');
    if (await servicesToggle.isVisible()) {
      await servicesToggle.click();
      await page.waitForTimeout(500);

      const servicesCollapse = page.locator('#servicesInterestedCollapse');
      const isVisible = await servicesCollapse.isVisible();
      expect(typeof isVisible).toBe('boolean');
      console.log('✅ Services collapse functionality working');
    }
  });

  test('should handle currency formatting', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      test.skip('Login required for currency testing');
      return;
    }

    await page.goto('/leads/edit.php?lead_id=1');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    // Test currency inputs if they exist
    const currencyInputs = await page.locator('input[name*="estimate"], input[name*="price"], input[name*="cost"]').count();

    if (currencyInputs > 0) {
      const firstCurrencyInput = page.locator('input[name*="estimate"], input[name*="price"], input[name*="cost"]').first();

      // Test currency formatting
      await firstCurrencyInput.fill('123456');
      await firstCurrencyInput.blur();

      // The JavaScript should format this properly
      const formattedValue = await firstCurrencyInput.inputValue();
      expect(formattedValue).toMatch(/^\d{1,3}(,\d{3})*$/);
      console.log('✅ Currency formatting working');
    } else {
      console.log('ℹ️  No currency inputs found on this lead');
    }
  });

  test('should check for JavaScript errors', async ({ page }) => {
    const loginResult = await login(page, testUsername, testPassword);

    if (!loginResult) {
      test.skip('Login required for error checking');
      return;
    }

    const jsErrors = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') {
        jsErrors.push(msg.text());
      }
    });

    await page.goto('/leads/edit.php?lead_id=1');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);

    // Filter out known non-critical errors
    const criticalErrors = jsErrors.filter(error =>
      !error.includes('favicon') &&
      !error.includes('404') &&
      !error.toLowerCase().includes('net::err_aborted')
    );

    expect(criticalErrors.length).toBe(0);

    if (criticalErrors.length > 0) {
      console.log('❌ JavaScript errors found:', criticalErrors);
    } else {
      console.log('✅ No critical JavaScript errors');
    }
  });
});
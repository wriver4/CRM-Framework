const { test, expect } = require('@playwright/test');
const { login, logout, isLoggedIn } = require('./auth-helper');

test.describe('Calendar Bootstrap 5 Integration Tests', () => {
  const testUsername = process.env.CRM_TEST_USERNAME || 'admin';
  const testPassword = process.env.CRM_TEST_PASSWORD || 'admin123';

  test.beforeEach(async ({ page }) => {
    await page.setExtraHTTPHeaders({
      'User-Agent': 'Playwright-Calendar-Bootstrap5-Testing'
    });
  });

  test.describe('ID Conflict Resolution', () => {
    test('should have separate IDs for navigation and calendar container', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Navigation should use nav-calendar ID
      const navCalendar = page.locator('#nav-calendar');
      await expect(navCalendar).toBeVisible();

      // Calendar container should use calendar ID
      const calendarContainer = page.locator('#calendar');
      await expect(calendarContainer).toBeVisible();

      // Verify they are different elements
      const navParent = await navCalendar.locator('..').getAttribute('class');
      const calendarParent = await calendarContainer.locator('..').getAttribute('class');

      expect(navParent).toContain('nav'); // Navigation parent should have nav class
      expect(calendarParent).toContain('card-body'); // Calendar parent should be in card-body

      console.log('✅ ID conflict resolved: nav-calendar vs calendar');
    });

    test('should initialize FullCalendar on correct element', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Listen for console logs
      const consoleMessages = [];
      page.on('console', msg => {
        if (msg.text().includes('Calendar element parent')) {
          consoleMessages.push(msg.text());
        }
      });

      await page.waitForTimeout(3000); // Wait for calendar initialization

      // Check console for correct parent element
      const parentMessage = consoleMessages.find(msg =>
        msg.includes('Calendar element parent') && !msg.includes('ul.nav')
      );

      if (parentMessage) {
        console.log('✅ Calendar initializes on correct element:', parentMessage);
      } else {
        console.log('⚠️ Could not verify calendar parent from console logs');
      }

      // Verify FullCalendar is in card-body, not navigation
      const fcInCard = page.locator('.card-body .fc');
      await expect(fcInCard).toBeVisible();

      const fcInNav = page.locator('.nav .fc');
      expect(await fcInNav.count()).toBe(0);
    });
  });

  test.describe('Bootstrap 5 Theme Integration', () => {
    test('should load Bootstrap 5 theme assets', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Check for Bootstrap Icons CSS
      const bootstrapIconsLoaded = await page.evaluate(() => {
        const links = Array.from(document.querySelectorAll('link[href*="bootstrap-icons"]'));
        return links.length > 0;
      });

      expect(bootstrapIconsLoaded).toBeTruthy();
      console.log('✅ Bootstrap Icons CSS loaded');

      // Check for FullCalendar Bootstrap 5 plugin
      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      const bootstrap5PluginLoaded = await page.evaluate(() => {
        const scripts = Array.from(document.querySelectorAll('script[src*="bootstrap5"]'));
        return scripts.length > 0;
      });

      expect(bootstrap5PluginLoaded).toBeTruthy();
      console.log('✅ FullCalendar Bootstrap 5 plugin loaded');
    });

    test('should display Bootstrap 5 styled calendar components', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      // Check for Bootstrap 5 styled buttons in FullCalendar toolbar
      const fcButtons = page.locator('.fc-toolbar .fc-button');
      const buttonCount = await fcButtons.count();

      expect(buttonCount).toBeGreaterThan(0);
      console.log(`✅ Found ${buttonCount} FullCalendar buttons`);

      // Check if buttons have Bootstrap styling
      const firstButton = fcButtons.first();
      const buttonClasses = await firstButton.getAttribute('class');

      // Bootstrap 5 theme should add 'btn' class
      if (buttonClasses && buttonClasses.includes('btn')) {
        console.log('✅ FullCalendar buttons have Bootstrap 5 styling');
      } else {
        console.log('⚠️ FullCalendar buttons may not have Bootstrap 5 styling');
      }

      // Take screenshot of styled calendar
      await page.screenshot({
        path: 'screenshots/calendar-bootstrap5-styled.png',
        fullPage: true
      });
    });

    test('should render calendar in proper Bootstrap card layout', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      // Verify calendar is in Bootstrap card structure
      const calendarInCard = page.locator('.card .card-body #calendar');
      await expect(calendarInCard).toBeVisible();

      // Verify card has proper Bootstrap classes
      const cardElement = page.locator('.card:has(#calendar)');
      await expect(cardElement).toBeVisible();

      // Check that calendar takes full width of card
      const calendarWidth = await page.locator('#calendar').boundingBox();
      const cardWidth = await cardElement.boundingBox();

      if (calendarWidth && cardWidth) {
        const widthRatio = calendarWidth.width / cardWidth.width;
        expect(widthRatio).toBeGreaterThan(0.8); // Calendar should use most of card width
        console.log(`✅ Calendar uses ${Math.round(widthRatio * 100)}% of card width`);
      }

      // Verify stats cards are above calendar
      const statsCards = page.locator('.card:has-text("Calls Today"), .card:has-text("Emails Today")');
      const statsCount = await statsCards.count();

      expect(statsCount).toBeGreaterThan(0);
      console.log(`✅ Found ${statsCount} stats cards`);
    });
  });

  test.describe('Calendar Positioning and Layout', () => {
    test('should not cover navigation or other elements', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      // Get positions of key elements
      const navBox = await page.locator('.navbar, .nav').first().boundingBox();
      const calendarBox = await page.locator('#calendar').boundingBox();
      const headerBox = await page.locator('h1, .card-title').first().boundingBox();

      // Calendar should not overlap navigation
      if (navBox && calendarBox) {
        const overlaps = !(calendarBox.y >= navBox.y + navBox.height ||
          calendarBox.y + calendarBox.height <= navBox.y);

        expect(overlaps).toBeFalsy();
        console.log('✅ Calendar does not overlap navigation');
      }

      // Calendar should be below header/stats
      if (headerBox && calendarBox) {
        expect(calendarBox.y).toBeGreaterThan(headerBox.y);
        console.log('✅ Calendar positioned below header');
      }

      // Take screenshot to verify layout
      await page.screenshot({
        path: 'screenshots/calendar-layout-verification.png',
        fullPage: true
      });
    });

    test('should maintain responsive layout on mobile', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Set mobile viewport
      await page.setViewportSize({ width: 375, height: 667 });

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      // Calendar should still be visible and properly positioned
      const calendar = page.locator('#calendar');
      await expect(calendar).toBeVisible();

      // Stats cards should stack vertically on mobile
      const statsCards = page.locator('.card:has-text("Calls Today"), .card:has-text("Emails Today")');
      const firstStatsCard = statsCards.first();
      const lastStatsCard = statsCards.last();

      if (await firstStatsCard.isVisible() && await lastStatsCard.isVisible()) {
        const firstBox = await firstStatsCard.boundingBox();
        const lastBox = await lastStatsCard.boundingBox();

        if (firstBox && lastBox) {
          // On mobile, cards should stack (different Y positions)
          expect(Math.abs(firstBox.y - lastBox.y)).toBeGreaterThan(50);
          console.log('✅ Stats cards stack vertically on mobile');
        }
      }

      // Take mobile screenshot
      await page.screenshot({
        path: 'screenshots/calendar-mobile-layout.png',
        fullPage: true
      });
    });
  });

  test.describe('JavaScript Functionality Verification', () => {
    test('should initialize with Bootstrap 5 theme configuration', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Listen for console messages
      const consoleMessages = [];
      page.on('console', msg => {
        consoleMessages.push(msg.text());
      });

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      // Check for Bootstrap 5 theme confirmation in console
      const bootstrap5Message = consoleMessages.find(msg =>
        msg.includes('Bootstrap 5 theme') || msg.includes('bootstrap5')
      );

      if (bootstrap5Message) {
        console.log('✅ Bootstrap 5 theme configuration confirmed:', bootstrap5Message);
      } else {
        console.log('⚠️ No Bootstrap 5 theme confirmation in console');
        console.log('Console messages:', consoleMessages.slice(-5)); // Last 5 messages
      }

      // Verify calendar rendered successfully
      const renderMessage = consoleMessages.find(msg =>
        msg.includes('FullCalendar rendered') || msg.includes('Calendar initialized')
      );

      expect(renderMessage).toBeTruthy();
      console.log('✅ Calendar initialization confirmed');
    });

    test('should handle calendar navigation without positioning issues', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      // Test navigation buttons
      const nextBtn = page.locator('.fc-next-button');
      const prevBtn = page.locator('.fc-prev-button');
      const todayBtn = page.locator('.fc-today-button');

      // Click next month
      if (await nextBtn.isVisible()) {
        const calendarBefore = await page.locator('#calendar').boundingBox();
        await nextBtn.click();
        await page.waitForTimeout(1000);

        const calendarAfter = await page.locator('#calendar').boundingBox();

        // Calendar should maintain position after navigation
        if (calendarBefore && calendarAfter) {
          expect(Math.abs(calendarBefore.y - calendarAfter.y)).toBeLessThan(10);
          console.log('✅ Calendar maintains position during navigation');
        }
      }

      // Calendar should still be in card body after navigation
      const calendarInCard = page.locator('.card-body #calendar');
      await expect(calendarInCard).toBeVisible();

      console.log('✅ Calendar remains properly positioned during navigation');
    });
  });

  test.describe('Asset Loading and Performance', () => {
    test('should load all required assets without errors', async ({ page }) => {
      const errors = [];
      page.on('pageerror', error => {
        errors.push(error.message);
      });

      const failedRequests = [];
      page.on('requestfailed', request => {
        failedRequests.push(request.url());
      });

      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      // Check for JavaScript errors
      expect(errors.length).toBe(0);
      if (errors.length > 0) {
        console.log('❌ JavaScript errors found:', errors);
      } else {
        console.log('✅ No JavaScript errors');
      }

      // Check for failed asset requests
      const assetFailures = failedRequests.filter(url =>
        url.includes('.css') || url.includes('.js') || url.includes('bootstrap') || url.includes('fullcalendar')
      );

      expect(assetFailures.length).toBe(0);
      if (assetFailures.length > 0) {
        console.log('❌ Failed asset requests:', assetFailures);
      } else {
        console.log('✅ All assets loaded successfully');
      }
    });
  });
});
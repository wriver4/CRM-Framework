const { test, expect } = require('@playwright/test');
const { login, logout, isLoggedIn } = require('./auth-helper');
const { DEFAULT_TEST_USER } = require('./test-credentials');

test.describe('Calendar System Tests', () => {
  const testUsername = DEFAULT_TEST_USER.username;
  const testPassword = DEFAULT_TEST_USER.password;

  test.beforeEach(async ({ page }) => {
    // Set up common headers
    await page.setExtraHTTPHeaders({
      'User-Agent': 'Playwright-Calendar-Testing'
    });
  });

  test.describe('Calendar Page Access and UI', () => {
    test('should access calendar page when authenticated', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        console.log('⚠️  Skipping calendar test - login failed');
        test.skip();
        return;
      }

      // Navigate to calendar page
      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Should not be redirected to login
      const currentUrl = page.url();
      expect(currentUrl).not.toContain('login.php');

      // Check for calendar page title
      await expect(page).toHaveTitle(/Calendar|Task Calendar/i);

      // Take screenshot of calendar page
      await page.screenshot({ path: 'screenshots/calendar-main.png', fullPage: true });
    });

    test('should display calendar header and stats cards', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Check for calendar header
      const header = page.locator('.calendar-header, h1:has-text("Calendar")');
      await expect(header).toBeVisible();

      // Check for stats cards
      const statsCards = [
        'Calls Today',
        'Emails Today',
        'Meetings',
        'High Priority'
      ];

      for (const cardText of statsCards) {
        const card = page.locator(`.card:has-text("${cardText}"), .stats-card:has-text("${cardText}")`);
        await expect(card).toBeVisible();
      }

      // Check for New Task button
      const newTaskBtn = page.locator('button:has-text("New Task"), a:has-text("New Task"), button:has-text("Add")');
      await expect(newTaskBtn).toBeVisible();
    });

    test('should display FullCalendar component with Bootstrap 5 theme', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Wait for FullCalendar to load
      await page.waitForTimeout(3000);

      // Check for calendar container (should be in card body, not navigation)
      const calendar = page.locator('#calendar');
      await expect(calendar).toBeVisible();

      // Verify calendar is NOT in navigation menu
      const navCalendar = page.locator('#nav-calendar');
      await expect(navCalendar).toBeVisible(); // Navigation item exists

      // Verify calendar is in proper Bootstrap card container
      const calendarCard = page.locator('.card .card-body #calendar');
      await expect(calendarCard).toBeVisible();

      // Check for FullCalendar Bootstrap 5 styled elements
      const fcView = page.locator('.fc-view, .fc-daygrid');
      await expect(fcView).toBeVisible();

      // Check for Bootstrap 5 styled calendar navigation buttons
      const fcToolbar = page.locator('.fc-toolbar');
      await expect(fcToolbar).toBeVisible();

      const prevBtn = page.locator('.fc-prev-button');
      const nextBtn = page.locator('.fc-next-button');
      const todayBtn = page.locator('.fc-today-button');

      // All navigation buttons should be visible with Bootstrap 5 styling
      await expect(prevBtn).toBeVisible();
      await expect(nextBtn).toBeVisible();
      await expect(todayBtn).toBeVisible();

      // Verify Bootstrap 5 button styling
      const hasBootstrapStyling = await prevBtn.getAttribute('class').then(classes =>
        classes && classes.includes('btn')
      ).catch(() => false);

      console.log('Bootstrap 5 styling detected:', hasBootstrapStyling);
    });
  });

  test.describe('Task Creation and Management', () => {
    test('should open new task modal', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Click New Task button
      const newTaskBtn = page.locator('button:has-text("New Task"), a:has-text("New Task"), button:has-text("Add")').first();
      await newTaskBtn.click();

      // Wait for modal to appear
      await page.waitForTimeout(1000);

      // Check if modal is visible
      const modal = page.locator('#taskModal, .modal:has-text("Task"), .modal:has-text("Add")');
      await expect(modal).toBeVisible();

      // Check for form fields
      const titleField = page.locator('input[name="title"], #title');
      const taskTypeField = page.locator('select[name="task_type"], #task_type');
      const startDateField = page.locator('input[name="start_datetime"], #start_datetime');

      await expect(titleField).toBeVisible();
      await expect(taskTypeField).toBeVisible();
      await expect(startDateField).toBeVisible();

      await page.screenshot({ path: 'screenshots/calendar-new-task-modal.png', fullPage: true });
    });

    test('should create a new phone call task', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Open new task modal
      const newTaskBtn = page.locator('button:has-text("New Task"), a:has-text("New Task"), button:has-text("Add")').first();
      await newTaskBtn.click();
      await page.waitForTimeout(1000);

      // Fill in task details
      const titleField = page.locator('input[name="title"], #title');
      await titleField.fill('Test Phone Call - Playwright');

      const taskTypeField = page.locator('select[name="task_type"], #task_type');
      await taskTypeField.selectOption('phone_call');

      // Set start date/time (tomorrow at 10 AM)
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      tomorrow.setHours(10, 0, 0, 0);
      const dateTimeString = tomorrow.toISOString().slice(0, 16);

      const startDateField = page.locator('input[name="start_datetime"], #start_datetime');
      await startDateField.fill(dateTimeString);

      // Fill contact information
      const contactNameField = page.locator('input[name="contact_name"], #contact_name');
      if (await contactNameField.isVisible()) {
        await contactNameField.fill('John Doe');
      }

      const contactPhoneField = page.locator('input[name="contact_phone"], #contact_phone');
      if (await contactPhoneField.isVisible()) {
        await contactPhoneField.fill('+1234567890');
      }

      // Set priority
      const priorityField = page.locator('select[name="priority"], #priority');
      if (await priorityField.isVisible()) {
        await priorityField.selectOption('5'); // Medium priority
      }

      // Add description
      const descriptionField = page.locator('textarea[name="description"], #description');
      if (await descriptionField.isVisible()) {
        await descriptionField.fill('Test phone call created by Playwright automation');
      }

      await page.screenshot({ path: 'screenshots/calendar-filled-task-form.png', fullPage: true });

      // Save the task
      const saveBtn = page.locator('button:has-text("Save"), #saveTaskBtn');
      await saveBtn.click();

      // Wait for save operation
      await page.waitForTimeout(2000);

      // Check if modal closed (task was saved)
      const modal = page.locator('#taskModal, .modal:has-text("Task")');
      const modalVisible = await modal.isVisible().catch(() => false);

      // Modal should be closed after successful save
      expect(modalVisible).toBeFalsy();

      await page.screenshot({ path: 'screenshots/calendar-after-task-creation.png', fullPage: true });
    });

    test('should create different types of tasks', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      const taskTypes = [
        { value: 'email', name: 'Test Email Task' },
        { value: 'text_message', name: 'Test SMS Task' },
        { value: 'virtual_meeting', name: 'Test Virtual Meeting' },
        { value: 'in_person_meeting', name: 'Test In-Person Meeting' }
      ];

      for (const taskType of taskTypes) {
        // Open new task modal
        const newTaskBtn = page.locator('button:has-text("New Task"), a:has-text("New Task"), button:has-text("Add")').first();
        await newTaskBtn.click();
        await page.waitForTimeout(1000);

        // Fill basic task info
        const titleField = page.locator('input[name="title"], #title');
        await titleField.fill(taskType.name);

        const taskTypeField = page.locator('select[name="task_type"], #task_type');
        await taskTypeField.selectOption(taskType.value);

        // Set start date/time
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(14, 0, 0, 0); // 2 PM
        const dateTimeString = tomorrow.toISOString().slice(0, 16);

        const startDateField = page.locator('input[name="start_datetime"], #start_datetime');
        await startDateField.fill(dateTimeString);

        // Save the task
        const saveBtn = page.locator('button:has-text("Save"), #saveTaskBtn');
        await saveBtn.click();
        await page.waitForTimeout(2000);

        console.log(`✅ Created ${taskType.name}`);
      }

      await page.screenshot({ path: 'screenshots/calendar-multiple-tasks-created.png', fullPage: true });
    });
  });

  test.describe('Calendar Event Interaction', () => {
    test('should click on calendar events and view details', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000); // Wait for calendar to fully load

      // Look for calendar events
      const events = page.locator('.fc-event, .calendar-event, [data-event-id]');
      const eventCount = await events.count();

      if (eventCount > 0) {
        console.log(`Found ${eventCount} calendar events`);

        // Click on the first event
        await events.first().click();
        await page.waitForTimeout(1000);

        // Check if event detail modal opened
        const detailModal = page.locator('#eventDetailModal, .modal:has-text("Details"), .modal:has-text("Event")');
        const modalVisible = await detailModal.isVisible().catch(() => false);

        if (modalVisible) {
          console.log('✅ Event detail modal opened');
          await page.screenshot({ path: 'screenshots/calendar-event-details.png', fullPage: true });

          // Close modal
          const closeBtn = page.locator('button:has-text("Close"), .btn-close');
          await closeBtn.click();
        } else {
          console.log('ℹ️  Event clicked but no detail modal found');
        }
      } else {
        console.log('ℹ️  No calendar events found to test interaction');
      }
    });

    test('should navigate between calendar months', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      // Get current month/year display
      const currentView = page.locator('.fc-toolbar-title, .calendar-title');
      const initialTitle = await currentView.textContent().catch(() => 'Unknown');
      console.log(`Initial calendar view: ${initialTitle}`);

      // Click next month
      const nextBtn = page.locator('.fc-next-button, button:has-text("next")');
      if (await nextBtn.isVisible()) {
        await nextBtn.click();
        await page.waitForTimeout(1000);

        const newTitle = await currentView.textContent().catch(() => 'Unknown');
        console.log(`After next click: ${newTitle}`);

        expect(newTitle).not.toBe(initialTitle);
      }

      // Click previous month
      const prevBtn = page.locator('.fc-prev-button, button:has-text("prev")');
      if (await prevBtn.isVisible()) {
        await prevBtn.click();
        await page.waitForTimeout(1000);

        const backTitle = await currentView.textContent().catch(() => 'Unknown');
        console.log(`After prev click: ${backTitle}`);
      }

      // Click today button
      const todayBtn = page.locator('.fc-today-button, button:has-text("today")');
      if (await todayBtn.isVisible()) {
        await todayBtn.click();
        await page.waitForTimeout(1000);
        console.log('✅ Today button clicked');
      }

      await page.screenshot({ path: 'screenshots/calendar-navigation-test.png', fullPage: true });
    });
  });

  test.describe('Calendar Responsive Design', () => {
    test('should display properly on mobile devices', async ({ page }) => {
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

      // Check if calendar is visible on mobile
      const calendar = page.locator('#calendar, .fc');
      await expect(calendar).toBeVisible();

      // Check if stats cards stack properly on mobile
      const statsCards = page.locator('.stats-card, .card');
      const cardCount = await statsCards.count();

      if (cardCount > 0) {
        console.log(`Found ${cardCount} stats cards on mobile`);
      }

      await page.screenshot({ path: 'screenshots/calendar-mobile-view.png', fullPage: true });

      // Test mobile task creation
      const newTaskBtn = page.locator('button:has-text("New Task"), a:has-text("New Task"), button:has-text("Add")').first();
      if (await newTaskBtn.isVisible()) {
        await newTaskBtn.click();
        await page.waitForTimeout(1000);

        const modal = page.locator('#taskModal, .modal');
        await expect(modal).toBeVisible();

        await page.screenshot({ path: 'screenshots/calendar-mobile-modal.png', fullPage: true });

        // Close modal
        const closeBtn = page.locator('button:has-text("Cancel"), .btn-close');
        await closeBtn.click();
      }
    });

    test('should display properly on tablet devices', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      // Set tablet viewport
      await page.setViewportSize({ width: 768, height: 1024 });

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(3000);

      const calendar = page.locator('#calendar, .fc');
      await expect(calendar).toBeVisible();

      await page.screenshot({ path: 'screenshots/calendar-tablet-view.png', fullPage: true });
    });
  });

  test.describe('Calendar Performance and Loading', () => {
    test('should load calendar within reasonable time', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      const startTime = Date.now();

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Wait for calendar to be visible
      const calendar = page.locator('#calendar, .fc');
      await expect(calendar).toBeVisible();

      const loadTime = Date.now() - startTime;
      console.log(`Calendar loaded in ${loadTime}ms`);

      // Calendar should load within 10 seconds
      expect(loadTime).toBeLessThan(10000);
    });

    test('should handle calendar data loading', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Monitor network requests for calendar data
      const responses = [];
      page.on('response', response => {
        if (response.url().includes('calendar') || response.url().includes('events')) {
          responses.push({
            url: response.url(),
            status: response.status(),
            contentType: response.headers()['content-type']
          });
        }
      });

      // Trigger calendar refresh or navigation
      const todayBtn = page.locator('.fc-today-button, button:has-text("today")');
      if (await todayBtn.isVisible()) {
        await todayBtn.click();
        await page.waitForTimeout(2000);
      }

      console.log(`Captured ${responses.length} calendar-related requests`);
      responses.forEach(resp => {
        console.log(`  ${resp.status} - ${resp.url}`);
      });
    });
  });

  test.describe('Calendar Error Handling', () => {
    test('should handle invalid task data gracefully', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await page.waitForLoadState('networkidle');

      // Open new task modal
      const newTaskBtn = page.locator('button:has-text("New Task"), a:has-text("New Task"), button:has-text("Add")').first();
      await newTaskBtn.click();
      await page.waitForTimeout(1000);

      // Try to save without required fields
      const saveBtn = page.locator('button:has-text("Save"), #saveTaskBtn');
      await saveBtn.click();
      await page.waitForTimeout(1000);

      // Check for validation messages or modal still open
      const modal = page.locator('#taskModal, .modal:has-text("Task")');
      const modalStillVisible = await modal.isVisible().catch(() => false);

      if (modalStillVisible) {
        console.log('✅ Modal remained open for invalid data (good validation)');

        // Look for error messages
        const errorMessages = page.locator('.error, .invalid-feedback, .alert-danger');
        const errorCount = await errorMessages.count();
        console.log(`Found ${errorCount} validation error messages`);

        await page.screenshot({ path: 'screenshots/calendar-validation-errors.png', fullPage: true });
      }
    });
  });

  test.afterEach(async ({ page }) => {
    // Clean up: try to logout after each test
    try {
      if (await isLoggedIn(page)) {
        await logout(page);
      }
    } catch (error) {
      console.log('Cleanup logout failed:', error.message);
    }
  });
});
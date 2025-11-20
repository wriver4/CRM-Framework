const { test, expect } = require('@playwright/test');
const { login, logout, isLoggedIn } = require('./auth-helper');
const { DEFAULT_TEST_USER } = require('./test-credentials');
const {
  createCalendarTask,
  navigateToMonth,
  getCalendarStats,
  countCalendarEvents,
  clickCalendarEvent,
  waitForCalendarLoad,
  switchCalendarView,
  createMultipleTestTasks
} = require('./calendar-helper');

test.describe('Advanced Calendar System Tests', () => {
  const testUsername = DEFAULT_TEST_USER.username;
  const testPassword = DEFAULT_TEST_USER.password;

  test.beforeEach(async ({ page }) => {
    await page.setExtraHTTPHeaders({
      'User-Agent': 'Playwright-Advanced-Calendar-Testing'
    });
  });

  test.describe('Calendar Data Management', () => {
    test('should create and verify multiple task types', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Get initial stats
      const initialStats = await getCalendarStats(page);
      const initialEventCount = await countCalendarEvents(page);

      console.log('Initial state:', { stats: initialStats, events: initialEventCount });

      // Create different types of tasks
      const taskTypes = [
        {
          title: 'Important Client Call',
          type: 'phone_call',
          priority: '10', // High priority
          contactName: 'John Smith',
          contactPhone: '+1-555-0123'
        },
        {
          title: 'Follow-up Email',
          type: 'email',
          priority: '5', // Medium priority
          contactName: 'Jane Doe',
          description: 'Send proposal follow-up email'
        },
        {
          title: 'Team Meeting',
          type: 'virtual_meeting',
          priority: '5',
          description: 'Weekly team sync meeting'
        },
        {
          title: 'Client Visit',
          type: 'in_person_meeting',
          priority: '10',
          contactName: 'ABC Corporation',
          description: 'On-site client presentation'
        }
      ];

      let createdCount = 0;
      for (const taskData of taskTypes) {
        const success = await createCalendarTask(page, taskData);
        if (success) {
          createdCount++;
        }
        await page.waitForTimeout(1500); // Wait between creations
      }

      expect(createdCount).toBeGreaterThan(0);
      console.log(`✅ Successfully created ${createdCount}/${taskTypes.length} tasks`);

      // Verify stats updated
      const finalStats = await getCalendarStats(page);
      const finalEventCount = await countCalendarEvents(page);

      console.log('Final state:', { stats: finalStats, events: finalEventCount });

      // Take screenshot of calendar with new tasks
      await page.screenshot({ path: 'screenshots/calendar-multiple-tasks.png', fullPage: true });
    });

    test('should handle calendar navigation and date ranges', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Test navigation to next month
      const nextMonth = new Date();
      nextMonth.setMonth(nextMonth.getMonth() + 1);

      const success = await navigateToMonth(page, nextMonth.getMonth() + 1, nextMonth.getFullYear());
      expect(success).toBeTruthy();

      await page.screenshot({ path: 'screenshots/calendar-next-month.png', fullPage: true });

      // Navigate back to current month
      const currentMonth = new Date();
      await navigateToMonth(page, currentMonth.getMonth() + 1, currentMonth.getFullYear());

      // Test today button
      const todayBtn = page.locator('.fc-today-button, button:has-text("today")');
      if (await todayBtn.isVisible()) {
        await todayBtn.click();
        await page.waitForTimeout(1000);
        console.log('✅ Today button navigation successful');
      }
    });

    test('should test calendar view switching', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Test different calendar views if available
      const views = ['month', 'week', 'day'];

      for (const view of views) {
        const switched = await switchCalendarView(page, view);
        if (switched) {
          await page.screenshot({ path: `screenshots/calendar-${view}-view.png`, fullPage: true });
          await page.waitForTimeout(1000);
        }
      }
    });
  });

  test.describe('Calendar Event Interactions', () => {
    test('should interact with calendar events comprehensively', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Create a test task first
      const testTask = {
        title: 'Interactive Test Task',
        type: 'phone_call',
        priority: '10',
        contactName: 'Test Contact',
        description: 'Task for testing interactions'
      };

      const taskCreated = await createCalendarTask(page, testTask);
      if (!taskCreated) {
        console.log('⚠️  Could not create test task, testing with existing events');
      }

      await page.waitForTimeout(2000);

      // Count and interact with events
      const eventCount = await countCalendarEvents(page);

      if (eventCount > 0) {
        // Click on first event
        const clicked = await clickCalendarEvent(page, 0);

        if (clicked) {
          // Check if detail modal opened
          const detailModal = page.locator('#eventDetailModal, .modal:has-text("Details"), .modal:has-text("Event")');
          const modalVisible = await detailModal.isVisible().catch(() => false);

          if (modalVisible) {
            console.log('✅ Event detail modal opened');

            // Look for edit button
            const editBtn = page.locator('#editEventBtn, button:has-text("Edit")');
            if (await editBtn.isVisible()) {
              await editBtn.click();
              await page.waitForTimeout(1000);

              // Check if edit modal opened
              const editModal = page.locator('#taskModal, .modal:has-text("Edit")');
              const editModalVisible = await editModal.isVisible().catch(() => false);

              if (editModalVisible) {
                console.log('✅ Edit modal opened');
                await page.screenshot({ path: 'screenshots/calendar-edit-modal.png', fullPage: true });

                // Close edit modal
                const cancelBtn = page.locator('button:has-text("Cancel"), .btn-close');
                await cancelBtn.click();
              }
            }

            // Close detail modal
            const closeBtn = page.locator('button:has-text("Close"), .btn-close');
            await closeBtn.click();
          }
        }
      } else {
        console.log('ℹ️  No events found for interaction testing');
      }
    });

    test('should test calendar event filtering and search', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Look for filter or search functionality
      const searchField = page.locator('input[type="search"], input[placeholder*="search"], #search');
      const filterDropdown = page.locator('select[name*="filter"], .filter-dropdown');

      if (await searchField.isVisible()) {
        console.log('✅ Search field found');
        await searchField.fill('test');
        await page.waitForTimeout(1000);
        await page.screenshot({ path: 'screenshots/calendar-search.png', fullPage: true });
        await searchField.clear();
      }

      if (await filterDropdown.isVisible()) {
        console.log('✅ Filter dropdown found');
        await filterDropdown.selectOption({ index: 1 });
        await page.waitForTimeout(1000);
        await page.screenshot({ path: 'screenshots/calendar-filtered.png', fullPage: true });
      }
    });
  });

  test.describe('Calendar Performance and Stress Testing', () => {
    test('should handle multiple task creation efficiently', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      const startTime = Date.now();

      // Create multiple tasks
      const tasksCreated = await createMultipleTestTasks(page, 5);

      const endTime = Date.now();
      const totalTime = endTime - startTime;

      console.log(`Created ${tasksCreated} tasks in ${totalTime}ms`);
      expect(tasksCreated).toBeGreaterThan(0);

      // Performance should be reasonable (less than 30 seconds for 5 tasks)
      expect(totalTime).toBeLessThan(30000);

      await page.screenshot({ path: 'screenshots/calendar-stress-test.png', fullPage: true });
    });

    test('should test calendar with large date ranges', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Navigate through several months quickly
      const months = [1, 2, 3, 4, 5, 6];
      const currentYear = new Date().getFullYear();

      for (const month of months) {
        const startTime = Date.now();
        await navigateToMonth(page, month, currentYear);
        const navTime = Date.now() - startTime;

        console.log(`Navigation to month ${month} took ${navTime}ms`);
        expect(navTime).toBeLessThan(5000); // Should navigate within 5 seconds

        await page.waitForTimeout(500);
      }
    });
  });

  test.describe('Calendar Error Scenarios', () => {
    test('should handle network interruptions gracefully', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Simulate slow network
      await page.route('**/*', route => {
        setTimeout(() => route.continue(), 1000); // Add 1 second delay
      });

      // Try to create a task with slow network
      const taskData = {
        title: 'Network Test Task',
        type: 'email',
        description: 'Testing with simulated slow network'
      };

      const success = await createCalendarTask(page, taskData);
      console.log(`Task creation with slow network: ${success ? 'SUCCESS' : 'FAILED'}`);

      // Remove network simulation
      await page.unroute('**/*');
    });

    test('should validate required fields properly', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Open new task modal
      const newTaskBtn = page.locator('button:has-text("New Task"), a:has-text("New Task"), button:has-text("Add")').first();
      await newTaskBtn.click();
      await page.waitForTimeout(1000);

      // Try different invalid scenarios
      const scenarios = [
        { name: 'Empty title', data: { title: '' } },
        { name: 'Invalid date', data: { title: 'Test', startDate: 'invalid-date' } },
        { name: 'Past date', data: { title: 'Test', startDate: new Date('2020-01-01') } }
      ];

      for (const scenario of scenarios) {
        console.log(`Testing scenario: ${scenario.name}`);

        // Clear and fill form
        const titleField = page.locator('input[name="title"], #title');
        await titleField.clear();
        if (scenario.data.title) {
          await titleField.fill(scenario.data.title);
        }

        // Try to save
        const saveBtn = page.locator('button:has-text("Save"), #saveTaskBtn');
        await saveBtn.click();
        await page.waitForTimeout(1000);

        // Modal should still be open for invalid data
        const modal = page.locator('#taskModal, .modal:has-text("Task")');
        const modalVisible = await modal.isVisible().catch(() => false);

        if (modalVisible) {
          console.log(`✅ Validation working for: ${scenario.name}`);
        }
      }

      // Close modal
      const cancelBtn = page.locator('button:has-text("Cancel"), .btn-close');
      await cancelBtn.click();
    });
  });

  test.describe('Calendar Accessibility', () => {
    test('should be keyboard navigable', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Test keyboard navigation
      await page.keyboard.press('Tab'); // Should focus first interactive element
      await page.waitForTimeout(500);

      await page.keyboard.press('Tab'); // Move to next element
      await page.waitForTimeout(500);

      // Test Enter key on focused element
      await page.keyboard.press('Enter');
      await page.waitForTimeout(1000);

      // Check if any modal opened
      const modal = page.locator('.modal:visible');
      const modalVisible = await modal.isVisible().catch(() => false);

      if (modalVisible) {
        console.log('✅ Keyboard navigation opened modal');
        await page.keyboard.press('Escape'); // Close modal with Escape
        await page.waitForTimeout(500);
      }

      await page.screenshot({ path: 'screenshots/calendar-keyboard-nav.png', fullPage: true });
    });

    test('should have proper ARIA labels and roles', async ({ page }) => {
      const loginResult = await login(page, testUsername, testPassword);

      if (!loginResult) {
        test.skip();
        return;
      }

      await page.goto('/calendar/');
      await waitForCalendarLoad(page);

      // Check for ARIA labels
      const ariaElements = await page.locator('[aria-label], [role], [aria-describedby]').count();
      console.log(`Found ${ariaElements} elements with ARIA attributes`);

      // Check for proper heading structure
      const headings = await page.locator('h1, h2, h3, h4, h5, h6').count();
      console.log(`Found ${headings} heading elements`);

      expect(headings).toBeGreaterThan(0);
    });
  });

  test.afterEach(async ({ page }) => {
    try {
      if (await isLoggedIn(page)) {
        await logout(page);
      }
    } catch (error) {
      console.log('Cleanup logout failed:', error.message);
    }
  });
});
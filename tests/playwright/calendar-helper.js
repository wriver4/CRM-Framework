// Calendar helper functions for Playwright tests
// This file contains utilities for calendar-specific operations

/**
 * Create a new calendar task
 * @param {import('@playwright/test').Page} page 
 * @param {Object} taskData - Task information
 * @returns {Promise<boolean>} true if task created successfully
 */
async function createCalendarTask (page, taskData = {}) {
  try {
    // Default task data
    const defaultTask = {
      title: 'Test Task',
      type: 'phone_call',
      startDate: new Date(Date.now() + 24 * 60 * 60 * 1000), // Tomorrow
      priority: '5', // Medium
      contactName: 'Test Contact',
      contactPhone: '+1234567890',
      description: 'Test task created by automation'
    };

    const task = { ...defaultTask, ...taskData };

    // Open new task modal
    const newTaskBtn = page.locator('button:has-text("New Task"), a:has-text("New Task"), button:has-text("Add")').first();
    await newTaskBtn.click();
    await page.waitForTimeout(1000);

    // Fill in task details
    const titleField = page.locator('input[name="title"], #title');
    await titleField.fill(task.title);

    const taskTypeField = page.locator('select[name="task_type"], #task_type');
    await taskTypeField.selectOption(task.type);

    // Set start date/time
    const dateTimeString = task.startDate.toISOString().slice(0, 16);
    const startDateField = page.locator('input[name="start_datetime"], #start_datetime');
    await startDateField.fill(dateTimeString);

    // Fill optional fields if they exist
    const contactNameField = page.locator('input[name="contact_name"], #contact_name');
    if (await contactNameField.isVisible()) {
      await contactNameField.fill(task.contactName);
    }

    const contactPhoneField = page.locator('input[name="contact_phone"], #contact_phone');
    if (await contactPhoneField.isVisible()) {
      await contactPhoneField.fill(task.contactPhone);
    }

    const priorityField = page.locator('select[name="priority"], #priority');
    if (await priorityField.isVisible()) {
      await priorityField.selectOption(task.priority);
    }

    const descriptionField = page.locator('textarea[name="description"], #description');
    if (await descriptionField.isVisible()) {
      await descriptionField.fill(task.description);
    }

    // Save the task
    const saveBtn = page.locator('button:has-text("Save"), #saveTaskBtn');
    await saveBtn.click();
    await page.waitForTimeout(2000);

    // Check if modal closed (task was saved)
    const modal = page.locator('#taskModal, .modal:has-text("Task")');
    const modalVisible = await modal.isVisible().catch(() => false);

    const success = !modalVisible;
    if (success) {
      console.log(`✅ Task "${task.title}" created successfully`);
    } else {
      console.log(`❌ Failed to create task "${task.title}"`);
    }

    return success;

  } catch (error) {
    console.log('❌ Create task error:', error.message);
    return false;
  }
}

/**
 * Navigate calendar to specific month/year
 * @param {import('@playwright/test').Page} page 
 * @param {number} targetMonth - Month (1-12)
 * @param {number} targetYear - Year
 * @returns {Promise<boolean>} true if navigation successful
 */
async function navigateToMonth (page, targetMonth, targetYear) {
  try {
    const currentDate = new Date();
    const targetDate = new Date(targetYear, targetMonth - 1, 1);

    const monthDiff = (targetDate.getFullYear() - currentDate.getFullYear()) * 12 +
      (targetDate.getMonth() - currentDate.getMonth());

    if (monthDiff > 0) {
      // Navigate forward
      const nextBtn = page.locator('.fc-next-button, button:has-text("next")');
      for (let i = 0; i < Math.abs(monthDiff); i++) {
        await nextBtn.click();
        await page.waitForTimeout(500);
      }
    } else if (monthDiff < 0) {
      // Navigate backward
      const prevBtn = page.locator('.fc-prev-button, button:has-text("prev")');
      for (let i = 0; i < Math.abs(monthDiff); i++) {
        await prevBtn.click();
        await page.waitForTimeout(500);
      }
    }

    console.log(`✅ Navigated to ${targetMonth}/${targetYear}`);
    return true;

  } catch (error) {
    console.log('❌ Calendar navigation error:', error.message);
    return false;
  }
}

/**
 * Get calendar statistics from the stats cards
 * @param {import('@playwright/test').Page} page 
 * @returns {Promise<Object>} stats object
 */
async function getCalendarStats (page) {
  try {
    const stats = {};

    // Get calls today
    const callsElement = page.locator('#calls-today, .stats-card:has-text("Calls") .h4');
    if (await callsElement.isVisible()) {
      stats.callsToday = await callsElement.textContent();
    }

    // Get emails today
    const emailsElement = page.locator('#emails-today, .stats-card:has-text("Emails") .h4');
    if (await emailsElement.isVisible()) {
      stats.emailsToday = await emailsElement.textContent();
    }

    // Get meetings
    const meetingsElement = page.locator('#meetings-today, .stats-card:has-text("Meetings") .h4');
    if (await meetingsElement.isVisible()) {
      stats.meetings = await meetingsElement.textContent();
    }

    // Get high priority tasks
    const highPriorityElement = page.locator('#high-priority, .stats-card:has-text("High Priority") .h4');
    if (await highPriorityElement.isVisible()) {
      stats.highPriority = await highPriorityElement.textContent();
    }

    console.log('Calendar stats:', stats);
    return stats;

  } catch (error) {
    console.log('❌ Get stats error:', error.message);
    return {};
  }
}

/**
 * Count visible calendar events
 * @param {import('@playwright/test').Page} page 
 * @returns {Promise<number>} number of visible events
 */
async function countCalendarEvents (page) {
  try {
    await page.waitForTimeout(2000); // Wait for calendar to load

    const events = page.locator('.fc-event, .calendar-event, [data-event-id]');
    const count = await events.count();

    console.log(`Found ${count} calendar events`);
    return count;

  } catch (error) {
    console.log('❌ Count events error:', error.message);
    return 0;
  }
}

/**
 * Click on a calendar event by index
 * @param {import('@playwright/test').Page} page 
 * @param {number} eventIndex - Index of event to click (0-based)
 * @returns {Promise<boolean>} true if event clicked successfully
 */
async function clickCalendarEvent (page, eventIndex = 0) {
  try {
    const events = page.locator('.fc-event, .calendar-event, [data-event-id]');
    const eventCount = await events.count();

    if (eventCount === 0) {
      console.log('No calendar events found to click');
      return false;
    }

    if (eventIndex >= eventCount) {
      console.log(`Event index ${eventIndex} out of range (${eventCount} events found)`);
      return false;
    }

    await events.nth(eventIndex).click();
    await page.waitForTimeout(1000);

    console.log(`✅ Clicked calendar event at index ${eventIndex}`);
    return true;

  } catch (error) {
    console.log('❌ Click event error:', error.message);
    return false;
  }
}

/**
 * Wait for calendar to fully load
 * @param {import('@playwright/test').Page} page 
 * @param {number} timeout - Timeout in milliseconds
 * @returns {Promise<boolean>} true if calendar loaded
 */
async function waitForCalendarLoad (page, timeout = 10000) {
  try {
    // Wait for calendar container
    await page.waitForSelector('#calendar, .fc', { timeout });

    // Wait for calendar to be visible
    const calendar = page.locator('#calendar, .fc');
    await calendar.waitFor({ state: 'visible', timeout });

    // Wait a bit more for calendar to fully render
    await page.waitForTimeout(2000);

    console.log('✅ Calendar loaded successfully');
    return true;

  } catch (error) {
    console.log('❌ Calendar load timeout:', error.message);
    return false;
  }
}

/**
 * Check if calendar is in specific view (month, week, day)
 * @param {import('@playwright/test').Page} page 
 * @param {string} viewType - Expected view type
 * @returns {Promise<boolean>} true if in expected view
 */
async function isCalendarInView (page, viewType) {
  try {
    const viewClass = `.fc-${viewType}-view`;
    const viewElement = page.locator(viewClass);
    const isVisible = await viewElement.isVisible().catch(() => false);

    console.log(`Calendar in ${viewType} view: ${isVisible}`);
    return isVisible;

  } catch (error) {
    console.log('❌ Check view error:', error.message);
    return false;
  }
}

/**
 * Switch calendar view (month, week, day)
 * @param {import('@playwright/test').Page} page 
 * @param {string} viewType - Target view type
 * @returns {Promise<boolean>} true if view switched successfully
 */
async function switchCalendarView (page, viewType) {
  try {
    const viewButton = page.locator(`button:has-text("${viewType}"), .fc-${viewType}-button`);

    if (await viewButton.isVisible()) {
      await viewButton.click();
      await page.waitForTimeout(1000);

      // Verify view changed
      const success = await isCalendarInView(page, viewType);
      if (success) {
        console.log(`✅ Switched to ${viewType} view`);
      }
      return success;
    } else {
      console.log(`❌ ${viewType} view button not found`);
      return false;
    }

  } catch (error) {
    console.log('❌ Switch view error:', error.message);
    return false;
  }
}

/**
 * Create multiple test tasks for testing
 * @param {import('@playwright/test').Page} page 
 * @param {number} count - Number of tasks to create
 * @returns {Promise<number>} number of tasks created successfully
 */
async function createMultipleTestTasks (page, count = 3) {
  let successCount = 0;

  const taskTypes = ['phone_call', 'email', 'text_message', 'virtual_meeting', 'in_person_meeting'];
  const priorities = ['1', '5', '10']; // Low, Medium, High

  for (let i = 0; i < count; i++) {
    const taskData = {
      title: `Test Task ${i + 1}`,
      type: taskTypes[i % taskTypes.length],
      priority: priorities[i % priorities.length],
      startDate: new Date(Date.now() + (i + 1) * 24 * 60 * 60 * 1000), // i+1 days from now
      contactName: `Contact ${i + 1}`,
      description: `Test task ${i + 1} created for testing purposes`
    };

    const success = await createCalendarTask(page, taskData);
    if (success) {
      successCount++;
    }

    // Small delay between creations
    await page.waitForTimeout(1000);
  }

  console.log(`✅ Created ${successCount}/${count} test tasks`);
  return successCount;
}

module.exports = {
  createCalendarTask,
  navigateToMonth,
  getCalendarStats,
  countCalendarEvents,
  clickCalendarEvent,
  waitForCalendarLoad,
  isCalendarInView,
  switchCalendarView,
  createMultipleTestTasks
};
const { test, expect } = require('@playwright/test');
const { login, logout } = require('./auth-helper');

/**
 * End-to-End tests for note deletion functionality
 * Tests the complete user workflow for deleting notes from leads
 */
test.describe('Note Deletion Functionality', () => {

  test.beforeEach(async ({ page }) => {
    // Login before each test
    const loginSuccess = await login(page);
    expect(loginSuccess).toBe(true);
  });

  test.afterEach(async ({ page }) => {
    // Logout after each test
    await logout(page);
  });

  test('should display delete buttons for notes in lead edit page', async ({ page }) => {
    // Navigate to leads list
    await page.goto('/admin/leads/list.php');
    await page.waitForLoadState('networkidle');

    // Find a lead with notes (look for the first lead in the table)
    const firstLeadLink = page.locator('table tbody tr:first-child a[href*="edit.php"]').first();

    if (await firstLeadLink.isVisible()) {
      await firstLeadLink.click();
      await page.waitForLoadState('networkidle');

      // Check if we're on the edit page
      expect(page.url()).toContain('edit.php');

      // Look for notes section
      const notesSection = page.locator('text=Notes & Activity');

      if (await notesSection.isVisible()) {
        // Check for delete buttons on notes
        const deleteButtons = page.locator('.delete-note-btn');
        const deleteButtonCount = await deleteButtons.count();

        if (deleteButtonCount > 0) {
          console.log(`Found ${deleteButtonCount} delete buttons`);

          // Verify delete buttons have correct attributes
          const firstDeleteButton = deleteButtons.first();
          await expect(firstDeleteButton).toBeVisible();
          await expect(firstDeleteButton).toHaveAttribute('data-note-id');
          await expect(firstDeleteButton).toHaveAttribute('data-lead-id');

          // Verify button styling
          await expect(firstDeleteButton).toHaveClass(/btn-outline-danger/);
          await expect(firstDeleteButton).toHaveClass(/btn-sm/);
        } else {
          console.log('No notes with delete buttons found on this lead');
        }
      } else {
        console.log('No notes section found on this lead');
      }
    } else {
      test.skip('No leads available for testing');
    }
  });

  test('should show confirmation dialog when delete button is clicked', async ({ page }) => {
    // Navigate to a lead with notes
    await navigateToLeadWithNotes(page);

    const deleteButton = page.locator('.delete-note-btn').first();

    if (await deleteButton.isVisible()) {
      // Set up dialog handler before clicking
      let dialogShown = false;
      let dialogMessage = '';

      page.on('dialog', async dialog => {
        dialogShown = true;
        dialogMessage = dialog.message();
        await dialog.dismiss(); // Dismiss to avoid actual deletion in this test
      });

      // Click delete button
      await deleteButton.click();

      // Wait a moment for dialog to appear
      await page.waitForTimeout(500);

      // Verify confirmation dialog was shown
      expect(dialogShown).toBe(true);
      expect(dialogMessage).toContain('Are you sure you want to delete this note?');
      expect(dialogMessage).toContain('This action cannot be undone');
    } else {
      test.skip('No delete buttons available for testing');
    }
  });

  test('should successfully delete a note when confirmed', async ({ page }) => {
    // Navigate to a lead with notes
    await navigateToLeadWithNotes(page);

    const deleteButtons = page.locator('.delete-note-btn');
    const initialNoteCount = await deleteButtons.count();

    if (initialNoteCount > 0) {
      const firstDeleteButton = deleteButtons.first();
      const noteElement = firstDeleteButton.locator('..').locator('..'); // Navigate up to timeline-item

      // Set up dialog handler to confirm deletion
      page.on('dialog', async dialog => {
        expect(dialog.message()).toContain('Are you sure you want to delete this note?');
        await dialog.accept(); // Confirm deletion
      });

      // Click delete button
      await firstDeleteButton.click();

      // Wait for the note to be removed from DOM
      await expect(noteElement).not.toBeVisible({ timeout: 5000 });

      // Verify the note count decreased
      const finalNoteCount = await page.locator('.delete-note-btn').count();
      expect(finalNoteCount).toBe(initialNoteCount - 1);

      // Check for success message
      const successAlert = page.locator('.alert-success');
      await expect(successAlert).toBeVisible({ timeout: 3000 });
      await expect(successAlert).toContainText('Note deleted successfully');

    } else {
      test.skip('No notes available for deletion testing');
    }
  });

  test('should show loading state during deletion', async ({ page }) => {
    // Navigate to a lead with notes
    await navigateToLeadWithNotes(page);

    const deleteButton = page.locator('.delete-note-btn').first();

    if (await deleteButton.isVisible()) {
      // Set up dialog handler to confirm deletion
      page.on('dialog', async dialog => {
        await dialog.accept();
      });

      // Click delete button
      await deleteButton.click();

      // Check for loading spinner (should appear briefly)
      const spinner = deleteButton.locator('.fa-spinner');

      // The spinner might be very brief, so we'll check if button gets disabled
      await expect(deleteButton).toBeDisabled({ timeout: 1000 });

    } else {
      test.skip('No delete buttons available for testing');
    }
  });

  test('should handle deletion cancellation gracefully', async ({ page }) => {
    // Navigate to a lead with notes
    await navigateToLeadWithNotes(page);

    const deleteButtons = page.locator('.delete-note-btn');
    const initialNoteCount = await deleteButtons.count();

    if (initialNoteCount > 0) {
      const firstDeleteButton = deleteButtons.first();

      // Set up dialog handler to cancel deletion
      page.on('dialog', async dialog => {
        expect(dialog.message()).toContain('Are you sure you want to delete this note?');
        await dialog.dismiss(); // Cancel deletion
      });

      // Click delete button
      await firstDeleteButton.click();

      // Wait a moment
      await page.waitForTimeout(1000);

      // Verify note count hasn't changed
      const finalNoteCount = await page.locator('.delete-note-btn').count();
      expect(finalNoteCount).toBe(initialNoteCount);

      // Verify no success message appears
      const successAlert = page.locator('.alert-success');
      await expect(successAlert).not.toBeVisible();

    } else {
      test.skip('No notes available for cancellation testing');
    }
  });

  test('should update notes count after deletion', async ({ page }) => {
    // Navigate to a lead with notes
    await navigateToLeadWithNotes(page);

    // Get initial notes count from the header
    const notesHeader = page.locator('text=/Notes & Activity \\((\\d+)\\)/');

    if (await notesHeader.isVisible()) {
      const initialHeaderText = await notesHeader.textContent();
      const initialCount = parseInt(initialHeaderText.match(/\((\d+)\)/)[1]);

      const deleteButton = page.locator('.delete-note-btn').first();

      if (await deleteButton.isVisible()) {
        // Set up dialog handler to confirm deletion
        page.on('dialog', async dialog => {
          await dialog.accept();
        });

        // Click delete button
        await deleteButton.click();

        // Wait for deletion to complete
        await page.waitForTimeout(2000);

        // Check updated count in header
        const updatedHeaderText = await notesHeader.textContent();
        const updatedCount = parseInt(updatedHeaderText.match(/\((\d+)\)/)[1]);

        expect(updatedCount).toBe(initialCount - 1);

      } else {
        test.skip('No delete buttons available');
      }
    } else {
      test.skip('No notes header found');
    }
  });

  test('should show "no notes" message when all notes are deleted', async ({ page }) => {
    // Navigate to a lead with exactly one note
    await navigateToLeadWithNotes(page);

    const deleteButtons = page.locator('.delete-note-btn');
    const noteCount = await deleteButtons.count();

    if (noteCount === 1) {
      // Set up dialog handler to confirm deletion
      page.on('dialog', async dialog => {
        await dialog.accept();
      });

      // Delete the only note
      await deleteButtons.first().click();

      // Wait for deletion to complete
      await page.waitForTimeout(2000);

      // Check for "no notes" message
      const noNotesMessage = page.locator('text=No notes found for this lead');
      await expect(noNotesMessage).toBeVisible({ timeout: 3000 });

    } else if (noteCount > 1) {
      console.log(`Lead has ${noteCount} notes, skipping "no notes" test`);
      test.skip('Lead has multiple notes, cannot test "no notes" scenario');
    } else {
      test.skip('No notes available for testing');
    }
  });

  test('should handle server errors gracefully', async ({ page }) => {
    // Navigate to a lead with notes
    await navigateToLeadWithNotes(page);

    const deleteButton = page.locator('.delete-note-btn').first();

    if (await deleteButton.isVisible()) {
      // Intercept the delete request and return an error
      await page.route('**/admin/leads/delete_note.php', route => {
        route.fulfill({
          status: 500,
          contentType: 'application/json',
          body: JSON.stringify({
            success: false,
            message: 'Server error occurred'
          })
        });
      });

      // Set up dialog handler to confirm deletion
      page.on('dialog', async dialog => {
        await dialog.accept();
      });

      // Click delete button
      await deleteButton.click();

      // Wait for error handling
      await page.waitForTimeout(2000);

      // Check for error message
      const errorAlert = page.locator('.alert-danger');
      await expect(errorAlert).toBeVisible({ timeout: 3000 });
      await expect(errorAlert).toContainText('Server error occurred');

      // Verify button is re-enabled
      await expect(deleteButton).not.toBeDisabled();

    } else {
      test.skip('No delete buttons available for error testing');
    }
  });

  test('should handle authentication errors', async ({ page }) => {
    // Navigate to a lead with notes
    await navigateToLeadWithNotes(page);

    const deleteButton = page.locator('.delete-note-btn').first();

    if (await deleteButton.isVisible()) {
      // Intercept the delete request and return auth error
      await page.route('**/admin/leads/delete_note.php', route => {
        route.fulfill({
          status: 401,
          contentType: 'application/json',
          body: JSON.stringify({
            success: false,
            message: 'Authentication required. Please log in again.'
          })
        });
      });

      // Set up dialog handler to confirm deletion
      page.on('dialog', async dialog => {
        await dialog.accept();
      });

      // Click delete button
      await deleteButton.click();

      // Wait for error handling
      await page.waitForTimeout(2000);

      // Check for authentication error message
      const errorAlert = page.locator('.alert-danger');
      await expect(errorAlert).toBeVisible({ timeout: 3000 });
      await expect(errorAlert).toContainText('Authentication required');

    } else {
      test.skip('No delete buttons available for auth error testing');
    }
  });

  // Helper function to navigate to a lead with notes
  async function navigateToLeadWithNotes (page) {
    await page.goto('/admin/leads/list.php');
    await page.waitForLoadState('networkidle');

    // Find a lead and navigate to edit page
    const firstLeadLink = page.locator('table tbody tr:first-child a[href*="edit.php"]').first();

    if (await firstLeadLink.isVisible()) {
      await firstLeadLink.click();
      await page.waitForLoadState('networkidle');

      // Verify we're on edit page
      expect(page.url()).toContain('edit.php');

      // Check if notes section exists
      const notesSection = page.locator('text=Notes & Activity');
      if (!(await notesSection.isVisible())) {
        throw new Error('No notes section found on this lead');
      }

    } else {
      throw new Error('No leads available for testing');
    }
  }
});
const { test, expect } = require('@playwright/test');
const { login } = require('./auth-helper');

test.describe('Email Processing System', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should access email processing menu items', async ({ page }) => {
    // Click on profile dropdown to access email processing menu
    await page.click('[data-bs-toggle="dropdown"]');

    // Check if Email Processing submenu exists
    const emailProcessingMenu = page.locator('text=Email Processing');
    await expect(emailProcessingMenu).toBeVisible();

    // Check submenu items
    const processingLog = page.locator('text=Processing Log');
    const emailAccounts = page.locator('text=Email Accounts');
    const syncQueue = page.locator('text=CRM Sync Queue');
    const systemStatus = page.locator('text=System Status');

    await expect(processingLog).toBeVisible();
    await expect(emailAccounts).toBeVisible();
    await expect(syncQueue).toBeVisible();
    await expect(systemStatus).toBeVisible();
  });

  test('should navigate to processing log page', async ({ page }) => {
    // Navigate to processing log
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=Processing Log');

    // Verify we're on the processing log page
    await expect(page).toHaveURL(/.*admin\/email\/processing_log/);
    await expect(page.locator('h2')).toContainText('Email Processing Log');

    // Check for key elements
    await expect(page.locator('.card')).toBeVisible();
    await expect(page.locator('table')).toBeVisible();
  });

  test('should navigate to email accounts config page', async ({ page }) => {
    // Navigate to email accounts config
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=Email Accounts');

    // Verify we're on the accounts config page
    await expect(page).toHaveURL(/.*admin\/email\/accounts_config/);
    await expect(page.locator('h2')).toContainText('Email Accounts Configuration');

    // Check for key elements
    await expect(page.locator('.btn-primary')).toContainText('Add New Account');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should navigate to CRM sync queue page', async ({ page }) => {
    // Navigate to sync queue
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=CRM Sync Queue');

    // Verify we're on the sync queue page
    await expect(page).toHaveURL(/.*admin\/email\/sync_queue/);
    await expect(page.locator('h2')).toContainText('CRM Sync Queue');

    // Check for key elements
    await expect(page.locator('.btn-warning')).toContainText('Clear Completed');
    await expect(page.locator('.card')).toBeVisible();
  });

  test('should navigate to system status page', async ({ page }) => {
    // Navigate to system status
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=System Status');

    // Verify we're on the system status page
    await expect(page).toHaveURL(/.*admin\/email\/system_status/);
    await expect(page.locator('h2')).toContainText('Email Processing System Status');

    // Check for status indicators
    await expect(page.locator('.badge')).toBeVisible();
    await expect(page.locator('.card')).toBeVisible();
  });

  test('should access manual email import page', async ({ page }) => {
    // Navigate directly to email import page
    await page.goto('/leads/email_import.php');

    // Verify we're on the email import page
    await expect(page.locator('h2')).toContainText('Manual Email Import');

    // Check for key elements
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('textarea[name="email_content"]')).toBeVisible();
    await expect(page.locator('select[name="form_type"]')).toBeVisible();
  });

  test('should test email accounts configuration form', async ({ page }) => {
    // Navigate to email accounts config
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=Email Accounts');

    // Click Add New Account button
    await page.click('.btn-primary');

    // Verify form elements are present
    await expect(page.locator('input[name="email_address"]')).toBeVisible();
    await expect(page.locator('select[name="form_type"]')).toBeVisible();
    await expect(page.locator('input[name="imap_host"]')).toBeVisible();
    await expect(page.locator('input[name="imap_port"]')).toBeVisible();
    await expect(page.locator('select[name="imap_encryption"]')).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();

    // Test form validation by submitting empty form
    await page.click('button[type="submit"]');

    // Should show validation errors or stay on form
    // (Exact behavior depends on implementation)
  });

  test('should test processing log filters', async ({ page }) => {
    // Navigate to processing log
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=Processing Log');

    // Check filter elements
    await expect(page.locator('select[name="status"]')).toBeVisible();
    await expect(page.locator('select[name="form_type"]')).toBeVisible();
    await expect(page.locator('input[name="date_from"]')).toBeVisible();
    await expect(page.locator('input[name="date_to"]')).toBeVisible();

    // Test filter functionality
    await page.selectOption('select[name="status"]', 'success');
    await page.selectOption('select[name="form_type"]', 'estimate');
    await page.click('button[type="submit"]');

    // Should reload page with filters applied
    await expect(page).toHaveURL(/.*status=success/);
    await expect(page).toHaveURL(/.*form_type=estimate/);
  });

  test('should test sync queue filters', async ({ page }) => {
    // Navigate to sync queue
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=CRM Sync Queue');

    // Check filter elements
    await expect(page.locator('select[name="status"]')).toBeVisible();
    await expect(page.locator('select[name="system"]')).toBeVisible();

    // Test filter functionality
    await page.selectOption('select[name="status"]', 'pending');
    await page.selectOption('select[name="system"]', 'hubspot');
    await page.click('button[type="submit"]');

    // Should reload page with filters applied
    await expect(page).toHaveURL(/.*status=pending/);
    await expect(page).toHaveURL(/.*system=hubspot/);
  });

  test('should test system status health checks', async ({ page }) => {
    // Navigate to system status
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=System Status');

    // Check for status cards
    const statusCards = page.locator('.card .card-body');
    await expect(statusCards).toHaveCount(7); // Should have 7 status checks

    // Check for overall status badge
    const overallStatus = page.locator('.badge-lg');
    await expect(overallStatus).toBeVisible();

    // Check for quick actions
    await expect(page.locator('text=View Processing Log')).toBeVisible();
    await expect(page.locator('text=Manage Accounts')).toBeVisible();
    await expect(page.locator('text=Check Sync Queue')).toBeVisible();
    await expect(page.locator('text=Manual Import')).toBeVisible();
  });

  test('should test manual email import form', async ({ page }) => {
    // Navigate to email import page
    await page.goto('/leads/email_import.php');

    // Fill out the form with test data
    await page.fill('textarea[name="email_content"]', `
      Name: Test User
      Email: test@example.com
      Phone: 555-1234
      Service: Test Service
      Message: This is a test message
    `);

    await page.selectOption('select[name="form_type"]', 'estimate');
    await page.fill('input[name="sender_email"]', 'test@example.com');

    // Submit the form
    await page.click('button[type="submit"]');

    // Should show processing result
    // (Exact behavior depends on implementation)
    await expect(page.locator('.alert')).toBeVisible();
  });

  test('should test API endpoint accessibility', async ({ page }) => {
    // Test API status endpoint
    const response = await page.request.get('/api/email_forms.php/status?api_key=waveguard_api_key_2024');
    expect(response.status()).toBe(200);

    const data = await response.json();
    expect(data).toHaveProperty('status');
    expect(data).toHaveProperty('system_health');
  });

  test('should test responsive design on mobile', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });

    // Navigate to processing log
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=Processing Log');

    // Check that table is responsive
    const table = page.locator('.table-responsive');
    await expect(table).toBeVisible();

    // Check that cards stack properly on mobile
    const cards = page.locator('.card');
    await expect(cards.first()).toBeVisible();
  });

  test('should test accessibility features', async ({ page }) => {
    // Navigate to system status page
    await page.click('[data-bs-toggle="dropdown"]');
    await page.click('text=System Status');

    // Check for proper heading structure
    await expect(page.locator('h2')).toBeVisible();
    await expect(page.locator('h5')).toBeVisible();

    // Check for proper form labels
    await page.goto('/leads/email_import.php');
    await expect(page.locator('label[for="email_content"]')).toBeVisible();
    await expect(page.locator('label[for="form_type"]')).toBeVisible();

    // Check for ARIA attributes
    const alerts = page.locator('.alert');
    if (await alerts.count() > 0) {
      await expect(alerts.first()).toHaveAttribute('role', 'alert');
    }
  });

  test('should handle error states gracefully', async ({ page }) => {
    // Test with invalid API key
    const response = await page.request.get('/api/email_forms.php/status?api_key=invalid_key');
    expect(response.status()).toBe(401);

    // Test form submission with invalid data
    await page.goto('/leads/email_import.php');
    await page.fill('textarea[name="email_content"]', '');
    await page.click('button[type="submit"]');

    // Should show validation error
    await expect(page.locator('.alert-danger')).toBeVisible();
  });
});
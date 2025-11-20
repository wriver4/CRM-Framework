// Authentication helper for CRM tests
// This file contains utilities for handling login/logout in tests

const { DEFAULT_TEST_USER } = require('./test-credentials');

/**
 * Attempt to login to the CRM system
 * @param {import('@playwright/test').Page} page 
 * @param {string} username 
 * @param {string} password 
 * @returns {Promise<boolean>} true if login successful
 */
async function login (page, username = DEFAULT_TEST_USER.username, password = DEFAULT_TEST_USER.password) {
  try {
    console.log(`\n🔐 Attempting login with username: ${username}`);
    
    // Add header to tell remote CRM to use test database
    await page.setExtraHTTPHeaders({ 'X-Playwright-Test': 'true' });
    
    await page.goto('/login.php');
    await page.waitForLoadState('networkidle');

    // Try different possible field names
    const usernameField = page.locator('input[name="username"], input[id="username"]').first();
    const passwordField = page.locator('input[name="password"], input[id="password"]').first();
    const submitButton = page.locator('button[name="login"], button[id="login"], button[type="submit"], input[type="submit"]').first();

    // Check if login form exists
    if (!(await usernameField.isVisible()) || !(await passwordField.isVisible())) {
      console.log('Login form not found or not visible');
      console.log(`Username field visible: ${await usernameField.isVisible()}`);
      console.log(`Password field visible: ${await passwordField.isVisible()}`);
      return false;
    }
    
    // Check submit button
    const buttonText = await submitButton.textContent();
    console.log(`Submit button text: "${buttonText}"`);
    console.log(`Submit button visible: ${await submitButton.isVisible()}`);

    // Fill in credentials
    await usernameField.fill(username);
    await passwordField.fill(password);
    
    // Verify values were filled
    const filledUsername = await usernameField.inputValue();
    const filledPassword = await passwordField.inputValue();
    console.log(`📝 Filled username: "${filledUsername}" (expected: "${username}")`);
    console.log(`📝 Filled password: ${'*'.repeat(Math.min(filledPassword.length, 8))}${filledPassword.length > 8 ? '...' : ''} (length: ${filledPassword.length})`);
    
    // Log the form data being sent
    const formAction = await page.locator('form').first().getAttribute('action');
    const formMethod = await page.locator('form').first().getAttribute('method');
    console.log(`📋 Form: ${formMethod?.toUpperCase()} ${formAction}`);

    // Submit form - wait for navigation
    console.log('🔐 Submitting login form...');
    try {
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }),
        submitButton.click()
      ]);
      console.log('✓ Form submitted and page navigated');
    } catch (navError) {
      console.log(`⚠️ Navigation timeout or error: ${navError.message}`);
      try {
        await page.waitForTimeout(2000);
        console.log('✓ Waited for page settlement');
      } catch (err) {
        console.log(`⚠️ Error during wait: ${err.message}`);
      }
    }

    // Check for error messages on the page
    const errorAlert = page.locator('.alert.alert-danger, .error, [role="alert"]');
    const errorCount = await errorAlert.count();
    if (errorCount > 0) {
      const errorMessage = await errorAlert.first().textContent();
      console.log(`⚠️ Alert displayed on page: "${errorMessage?.trim()}"`);
    }

    // Check if we're still on login page (login failed) or redirected (login success)
    const currentUrl = page.url();
    const pageTitle = await page.title();
    const pageContent = await page.content();
    console.log(`📍 Current URL: ${currentUrl}`);
    console.log(`📄 Page title: ${pageTitle}`);
    
    // Multiple checks for successful login
    const stillOnLoginPage = currentUrl.includes('login.php');
    const hasLoginForm = pageContent.includes('form-login') || pageContent.includes('name="username"');
    const hasNavigationMenu = pageContent.includes('navbar') || pageContent.includes('nav ') || pageContent.includes('menu');
    const hasLogoutLink = pageContent.includes('logout') || pageContent.includes('Logout');
    
    const loginSuccessful = !stillOnLoginPage || (hasNavigationMenu && !hasLoginForm) || hasLogoutLink;

    console.log(`  - Still on login page: ${stillOnLoginPage}`);
    console.log(`  - Has login form: ${hasLoginForm}`);
    console.log(`  - Has navigation: ${hasNavigationMenu}`);
    console.log(`  - Has logout link: ${hasLogoutLink}`);

    if (loginSuccessful) {
      console.log('✅ Login successful');
    } else {
      console.log('❌ Login failed - still on login page or no navigation detected');
    }

    return loginSuccessful;

  } catch (error) {
    console.log('❌ Login error:', error.message);
    console.log(error.stack);
    return false;
  }
}

/**
 * Logout from the CRM system
 * @param {import('@playwright/test').Page} page 
 * @returns {Promise<boolean>} true if logout successful
 */
async function logout (page) {
  try {
    // Try to find logout link/button
    const logoutLink = page.locator('a[href*="logout"], button:has-text("Logout"), a:has-text("Logout")').first();

    if (await logoutLink.isVisible()) {
      await logoutLink.click();
      await page.waitForLoadState('networkidle');

      // Check if we're redirected to login page
      const currentUrl = page.url();
      const logoutSuccessful = currentUrl.includes('login.php') || currentUrl.includes('index.php');

      if (logoutSuccessful) {
        console.log('✅ Logout successful');
      } else {
        console.log('❌ Logout may have failed - not redirected to login');
      }

      return logoutSuccessful;
    } else {
      // Try direct logout URL
      await page.goto('/logout.php');
      await page.waitForLoadState('networkidle');

      const currentUrl = page.url();
      const logoutSuccessful = currentUrl.includes('login.php') || currentUrl.includes('index.php');

      return logoutSuccessful;
    }

  } catch (error) {
    console.log('❌ Logout error:', error.message);
    return false;
  }
}

/**
 * Check if user is currently logged in
 * @param {import('@playwright/test').Page} page 
 * @returns {Promise<boolean>} true if logged in
 */
async function isLoggedIn (page) {
  try {
    // Try to access a protected page
    await page.goto('/dashboard.php');
    await page.waitForLoadState('networkidle');

    const currentUrl = page.url();
    return !currentUrl.includes('login.php');

  } catch (error) {
    console.log('❌ Login check error:', error.message);
    return false;
  }
}

/**
 * Get current user info if logged in
 * @param {import('@playwright/test').Page} page 
 * @returns {Promise<object|null>} user info or null
 */
async function getCurrentUser (page) {
  try {
    if (!(await isLoggedIn(page))) {
      return null;
    }

    // Look for user info in common places
    const userInfo = {};

    // Try to find username/email in navigation or header
    const userElement = page.locator('.user-name, .username, .user-email, [data-user]').first();
    if (await userElement.isVisible()) {
      userInfo.displayName = await userElement.textContent();
    }

    return Object.keys(userInfo).length > 0 ? userInfo : { loggedIn: true };

  } catch (error) {
    console.log('❌ Get user info error:', error.message);
    return null;
  }
}

module.exports = {
  login,
  logout,
  isLoggedIn,
  getCurrentUser
};
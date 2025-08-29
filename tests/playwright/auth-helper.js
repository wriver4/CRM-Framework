// Authentication helper for CRM tests
// This file contains utilities for handling login/logout in tests

/**
 * Attempt to login to the CRM system
 * @param {import('@playwright/test').Page} page 
 * @param {string} username 
 * @param {string} password 
 * @returns {Promise<boolean>} true if login successful
 */
async function login (page, username = 'testuser', password = 'testpass') {
  try {
    await page.goto('/login.php');
    await page.waitForLoadState('networkidle');

    // Try different possible field names
    const usernameField = page.locator('input[name="username"], input[name="email"], input[type="email"]').first();
    const passwordField = page.locator('input[name="password"], input[type="password"]').first();
    const submitButton = page.locator('button[type="submit"], input[type="submit"]').first();

    // Check if login form exists
    if (!(await usernameField.isVisible()) || !(await passwordField.isVisible())) {
      console.log('Login form not found or not visible');
      return false;
    }

    // Fill in credentials
    await usernameField.fill(username);
    await passwordField.fill(password);

    // Submit form
    await submitButton.click();

    // Wait for navigation
    await page.waitForLoadState('networkidle');

    // Check if we're still on login page (login failed) or redirected (login success)
    const currentUrl = page.url();
    const loginSuccessful = !currentUrl.includes('login.php');

    if (loginSuccessful) {
      console.log('✅ Login successful');
    } else {
      console.log('❌ Login failed - still on login page');
    }

    return loginSuccessful;

  } catch (error) {
    console.log('❌ Login error:', error.message);
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
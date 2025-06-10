const { test, expect } = require('@playwright/test');

test.describe('User Authentication System', () => {
  
  test.beforeEach(async ({ page }) => {
    // Clear any existing sessions by going to logout first
    try {
      await page.goto('/logout.php');
    } catch (error) {
      // Ignore errors, just ensuring clean state
    }
    // Navigate to the main page before each test
    await page.goto('/');
  });

  test('should display login form on main page', async ({ page }) => {
    // Check if we can access the page first
    const response = await page.goto('/');
    if (!response || response.status() === 404) {
      throw new Error('Application not found - please create index.php file first');
    }
    
    // Check if login form elements are present
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[type="submit"]')).toBeVisible();
  });

  test('should show error message for invalid credentials', async ({ page }) => {
    // Try to login with invalid credentials
    await page.fill('input[name="username"]', 'wronguser');
    await page.fill('input[name="password"]', 'wrongpass');
    await page.click('input[type="submit"]');
    
    // Should stay on login page and show error
    await expect(page).toHaveURL('/');
    await expect(page.locator('.error, .alert, [class*="error"]')).toBeVisible();
  });

  test('should successfully login with valid credentials', async ({ page }) => {
    // Login with valid credentials (assuming default admin/admin)
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Should redirect to dashboard after successful login
    await expect(page).toHaveURL('/dashboard.php');
    await expect(page.locator('h1, h2')).toContainText(['Dashboard', 'Monitor']);
  });

  test('should maintain login state across page visits', async ({ page }) => {
    // Login first
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Verify we're on dashboard
    await expect(page).toHaveURL('/dashboard.php');
    
    // Refresh the page
    await page.reload();
    
    // Should still be on dashboard (session maintained)
    await expect(page).toHaveURL('/dashboard.php');
    await expect(page.locator('h1, h2')).toContainText(['Dashboard', 'Monitor']);
  });

  test('should show monitor form only when logged in', async ({ page }) => {
    // Try to access dashboard directly without login
    await page.goto('/dashboard.php');
    
    // Should redirect to login page (index.php is correct, as that's where our PHP redirects)
    await expect(page).toHaveURL('/index.php');
    
    // Login first
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Now should see monitor form
    await expect(page).toHaveURL('/dashboard.php');
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="url"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });

  test('should successfully logout', async ({ page }) => {
    // Login first
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Verify we're logged in
    await expect(page).toHaveURL('/dashboard.php');
    
    // Find and click logout button/link
    await page.click('a[href*="logout"], button:has-text("Logout"), input[value*="Logout"]');
    
    // Should redirect to login page (index.php is the actual redirect target)
    await expect(page).toHaveURL('/index.php');
    await expect(page.locator('input[name="username"]')).toBeVisible();
  });

  test('should not access protected pages after logout', async ({ page }) => {
    // Use a unique browser context to avoid session interference
    await page.goto('/logout.php'); // Start clean
    
    // Login first
    await page.goto('/');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Wait for dashboard load
    await page.waitForURL('/dashboard.php');
    await expect(page.locator('h1')).toContainText('Monitor Dashboard');
    
    // Find and click logout
    const logoutLink = page.locator('a[href*="logout"], .logout-btn');
    await expect(logoutLink).toBeVisible();
    await logoutLink.click();
    
    // Wait for logout redirect with more time and be flexible about the endpoint
    await page.waitForLoadState('networkidle');
    
    // Verify we're at login page after logout
    const currentUrl = page.url();
    expect(currentUrl).toMatch(/index\.php|\/$/);
    
    // Verify login form is visible
    await expect(page.locator('input[name="username"]')).toBeVisible();
    
    // Try to access dashboard directly (this should redirect to login)
    await page.goto('/dashboard.php');
    
    // Should redirect back to login 
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL('/index.php');
    await expect(page.locator('input[name="username"]')).toBeVisible();
  });

  test('should validate required fields in login form', async ({ page }) => {
    // Try to submit empty form
    await page.click('input[type="submit"]');
    
    // Should show validation errors or stay on same page
    await expect(page).toHaveURL('/');
    
    // Try with only username
    await page.fill('input[name="username"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Should still show validation error or stay on page
    await expect(page).toHaveURL('/');
  });

});

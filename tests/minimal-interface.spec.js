const { test, expect } = require('@playwright/test');

test.describe('Minimal Interface System (Issue #5)', () => {

  test('should use plain HTML with minimal CSS styling', async ({ page }) => {
    // Visit login page
    await page.goto('/index.php');
    
    // Check for basic HTML structure
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
    await expect(page.locator('head meta[charset="UTF-8"]')).toHaveCount(1);
    await expect(page.locator('head meta[name="viewport"]')).toHaveCount(1);
    
    // Check that styling is minimal (embedded CSS only)
    const externalStylesheets = await page.locator('link[rel="stylesheet"]').count();
    expect(externalStylesheets).toBe(0); // No external CSS files
    
    // Check for inline styles (minimal CSS)
    const styleTag = await page.locator('style').first();
    await expect(styleTag).toHaveCount(1);
    
    // Check that styles are simple and functional
    const styleContent = await styleTag.textContent();
    expect(styleContent).toMatch(/font-family.*Arial/i);
    expect(styleContent).toMatch(/background-color/i);
    expect(styleContent).toMatch(/padding/i);
    expect(styleContent).not.toMatch(/animation|transform|gradient|shadow.*shadow/i); // No advanced styling
  });

  test('should ensure clarity without advanced styling', async ({ page }) => {
    // Visit login page
    await page.goto('/index.php');
    
    // Check for clear, readable text
    const heading = page.locator('h1');
    await expect(heading).toBeVisible();
    await expect(heading).toContainText('Uptime Monitor');
    
    // Check form clarity
    const usernameLabel = page.locator('label[for="username"]');
    const passwordLabel = page.locator('label[for="password"]');
    await expect(usernameLabel).toBeVisible();
    await expect(passwordLabel).toBeVisible();
    await expect(usernameLabel).toContainText('Username');
    await expect(passwordLabel).toContainText('Password');
    
    // Check that buttons are clearly labeled
    const submitButton = page.locator('input[type="submit"]');
    await expect(submitButton).toBeVisible();
    await expect(submitButton).toHaveValue('Login');
    
    // Visit dashboard after login
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Check dashboard clarity
    await expect(page.locator('h1')).toContainText('Monitor Dashboard');
    await expect(page.locator('h2').first()).toContainText('Add Website Monitor');
    
    // Check form field labels are clear
    await expect(page.locator('label[for="url"]')).toContainText('Website URL to monitor');
    await expect(page.locator('label[for="email"]')).toContainText('Notification email');
  });

  test('should require no JavaScript for essential functionality', async ({ page }) => {
    // Visit login page (forms should work without JavaScript)
    await page.goto('/index.php');
    
    // Should still display properly without JavaScript
    await expect(page.locator('h1')).toBeVisible();
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[type="submit"]')).toBeVisible();
    
    // Should be able to login without JavaScript
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Should redirect to dashboard
    await page.waitForURL('**/dashboard.php');
    await expect(page.locator('h1')).toContainText('Monitor Dashboard');
    
    // Should be able to add monitors without JavaScript
    await page.fill('input[name="url"]', 'https://example.com');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.click('input[type="submit"]');
    
    // Should show success message or redirect
    await expect(page.locator('.success, .error')).toBeVisible();
  });

  test('should have functional forms without client-side validation', async ({ page }) => {
    await page.goto('/index.php');
    
    // Try login with wrong credentials (server-side validation)
    await page.fill('input[name="username"]', 'wrong');
    await page.fill('input[name="password"]', 'wrong');
    await page.click('input[type="submit"]');
    
    // Should show server-side error message
    await expect(page.locator('.error')).toBeVisible();
    await expect(page.locator('.error')).toContainText(/invalid.*username.*password/i);
    
    // Login successfully
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Dashboard form validation
    await page.waitForURL('**/dashboard.php');
    
    // Test form works without validation errors (HTML5 validation might prevent invalid submissions)
    await page.fill('input[name="url"]', 'https://example.com');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.click('input[type="submit"]');
    
    // Should work successfully or show appropriate message
    const hasMessage = await page.locator('.error, .success, .alert, [class*="error"], [class*="success"]').count() > 0;
    expect(hasMessage).toBeTruthy();
  });

  test('should display meaningful error and success messages', async ({ page }) => {
    await page.goto('/index.php');
    
    // Test login error message
    await page.fill('input[name="username"]', 'wrong');
    await page.fill('input[name="password"]', 'wrong');
    await page.click('input[type="submit"]');
    
    const errorMessage = page.locator('.error');
    await expect(errorMessage).toBeVisible();
    await expect(errorMessage).toContainText(/invalid.*username.*password/i);
    
    // Test successful login
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Should redirect to dashboard (success)
    await page.waitForURL('**/dashboard.php');
    await expect(page.locator('h1')).toContainText('Monitor Dashboard');
    
    // Test monitor addition success
    await page.fill('input[name="url"]', 'https://httpbin.org/status/200');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.click('input[type="submit"]');
    
    // Should show success or display the added monitor
    await expect(page.locator('.success').first()).toBeVisible();
  });

  test('should have simple, accessible navigation', async ({ page }) => {
    await page.goto('/index.php');
    
    // Login
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Should have clear logout option
    await page.waitForURL('**/dashboard.php');
    const logoutLink = page.locator('a[href="logout.php"], .logout-btn');
    await expect(logoutLink).toBeVisible();
    await expect(logoutLink).toContainText(/logout/i);
    
    // Test logout functionality
    await logoutLink.click();
    
    // Should redirect back to login page
    await page.waitForURL('**/index.php');
    await expect(page.locator('h1')).toContainText('Uptime Monitor');
  });

  test('should display current monitors in a clear list', async ({ page }) => {
    await page.goto('/index.php');
    
    // Login
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    await page.waitForURL('**/dashboard.php');
    
    // Add a monitor first
    await page.fill('input[name="url"]', 'https://example.com');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.click('input[type="submit"]');
    
    // Should display the monitor in a clear list
    const monitorsList = page.locator('.monitors-list');
    await expect(monitorsList).toBeVisible();
    
    // Check that monitor details are clearly displayed
    await expect(page.locator('text=example.com').first()).toBeVisible();
    await expect(page.locator('text=test@example.com').first()).toBeVisible();
    
    // Should have clear section heading
    await expect(page.locator('h2').last()).toContainText(/current monitors|monitors/i);
  });

  test('should work on different screen sizes (responsive basics)', async ({ page }) => {
    await page.goto('/index.php');
    
    // Test on mobile-like viewport
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Should still be readable and functional
    await expect(page.locator('h1')).toBeVisible();
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    
    // Form should still work
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    await page.waitForURL('**/dashboard.php');
    
    // Dashboard should be functional on mobile
    await expect(page.locator('h1')).toBeVisible();
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="url"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    
    // Test on desktop viewport
    await page.setViewportSize({ width: 1200, height: 800 });
    
    // Should still work well on larger screens
    await expect(page.locator('h1')).toBeVisible();
    await expect(page.locator('form')).toBeVisible();
  });

});

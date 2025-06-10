const { test, expect } = require('@playwright/test');

test.describe('Monitor Configuration System', () => {
  
  test.beforeEach(async ({ page }) => {
    // Login first as monitor form is only available when logged in
    await page.goto('/');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin');
    await page.click('input[type="submit"]');
    
    // Should be on dashboard now
    await expect(page).toHaveURL('/dashboard.php');
  });

  test('should display monitor configuration form', async ({ page }) => {
    // Check if monitor form elements are present
    await expect(page.locator('form')).toBeVisible();
    await expect(page.locator('input[name="url"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[type="submit"]')).toBeVisible();
    
    // Check for form labels or headings
    await expect(page.locator('text=URL')).toBeVisible();
    await expect(page.locator('text=Email')).toBeVisible();
  });

  test('should require both URL and email fields', async ({ page }) => {
    // Try to submit with empty URL
    await page.fill('input[name="url"]', '');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.click('input[type="submit"]');
    
    // Should show validation error or stay on same page
    await expect(page).toHaveURL('/dashboard.php');
    
    // Try to submit with empty email
    await page.fill('input[name="url"]', 'https://example.com');
    await page.fill('input[name="email"]', '');
    await page.click('input[type="submit"]');
    
    // Should show validation error or stay on same page
    await expect(page).toHaveURL('/dashboard.php');
  });

  test('should display existing monitor entries', async ({ page }) => {
    const testUrl = 'https://display-test.com';
    const testEmail = 'display@example.com';
    
    // Add a monitor entry
    await page.fill('input[name="url"]', testUrl);
    await page.fill('input[name="email"]', testEmail);
    await page.click('input[type="submit"]');
    
    // Refresh page to see if entry persists
    await page.reload();
    
    // Should still be logged in and see the entry
    await expect(page).toHaveURL('/dashboard.php');
    
    // Should display the added URL and email somewhere on the page
    await expect(page.locator(`text=${testUrl}`)).toBeVisible();
    await expect(page.locator(`text=${testEmail}`)).toBeVisible();
  });

});

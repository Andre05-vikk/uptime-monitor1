// @ts-check
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : 2, // Reduced workers to minimize session conflicts
  reporter: 'html',
  timeout: 10000, // 10 sekundi timeout testide jaoks
  expect: {
    timeout: 5000, // 5 sekundi timeout expect'ide jaoks
  },
  use: {
    baseURL: 'http://localhost:8000',
    trace: 'on-first-retry',
    actionTimeout: 5000, // 5 sekundi timeout tegevuste jaoks
    navigationTimeout: 10000, // 10 sekundi timeout navigeerimise jaoks
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  webServer: {
    command: 'php -S localhost:8000',
    url: 'http://localhost:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 5000, // 5 sekundi timeout serveri käivitamiseks
    stderr: 'pipe', // Näita serveri vigu
  },
});

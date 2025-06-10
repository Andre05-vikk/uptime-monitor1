const { test, expect } = require('@playwright/test');
const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');

test.describe.configure({ mode: 'serial' });

test.describe('Automated Server Monitoring System (Issue #3)', () => {
  
  const projectPath = '/Users/andrepark/TAK24/sites/uptime-monitor1';
  const monitorsFile = path.join(projectPath, 'monitors.json');
  const logFile = path.join(projectPath, 'monitor.log');
  const monitorScript = path.join(projectPath, 'monitor.php');

  test.beforeEach(async () => {
    // Clean up both log file and monitors file before each test
    if (fs.existsSync(logFile)) {
      fs.unlinkSync(logFile);
    }
    if (fs.existsSync(monitorsFile)) {
      fs.unlinkSync(monitorsFile);
    }
  });

  test('should have a cron-compatible PHP monitoring script', async () => {
    // Check if monitor.php exists
    const scriptExists = fs.existsSync(monitorScript);
    expect(scriptExists).toBeTruthy();
    
    // Check if script is executable from command line
    await new Promise((resolve, reject) => {
      exec(`php -l ${monitorScript}`, (error, stdout, stderr) => {
        if (error) {
          reject(new Error(`PHP syntax error: ${stderr}`));
        } else {
          expect(stdout).toContain('No syntax errors detected');
          resolve();
        }
      });
    });
  });

  test('should read monitored URLs from storage', async () => {
    // Create test monitors data
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/200',
        email: 'test@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      },
      {
        url: 'https://httpbin.org/status/404',
        email: 'test2@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    // Write test data to monitors.json
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve, reject) => {
      exec(`cd ${projectPath} && php monitor.php`, (error, stdout, stderr) => {
        if (error && error.code !== 0) {
          // Allow non-zero exit codes as some URLs might fail
          console.log('Monitor script output:', stdout);
          console.log('Monitor script stderr:', stderr);
        }
        resolve();
      });
    });
    
    // Check if log file was created (indicates URLs were read)
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    // Check if log contains evidence of reading URLs
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      expect(logContent).toContain('httpbin.org');
    }
  });

  test('should make HTTP requests to monitored URLs', async () => {
    // Create test monitor with a working URL
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/200',
        email: 'test@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve, reject) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        if (error && error.code !== 0) {
          console.log('Monitor script output:', stdout);
          console.log('Monitor script stderr:', stderr);
        }
        resolve();
      });
    });
    
    // Check log for HTTP request evidence
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      // Should contain status code or success message
      expect(logContent).toMatch(/(200|SUCCESS|OK|UP)/i);
    }
  });

  test('should detect connection failures', async () => {
    // Create test monitor with invalid URL
    const testMonitors = [
      {
        url: 'https://this-domain-definitely-does-not-exist-12345.com',
        email: 'test@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve, reject) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        // Script should run even if URLs fail
        resolve();
      });
    });
    
    // Check log for failure detection
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      // Should contain error or failure indication
      expect(logContent).toMatch(/(ERROR|FAIL|DOWN|timeout|connection|unreachable)/i);
    }
  });

  test('should detect non-200 HTTP responses', async () => {
    // Create test monitor with URL that returns 404
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/404',
        email: 'test@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve, reject) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Check log for 404 detection
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      // Should contain 404 or failure indication
      expect(logContent).toMatch(/(404|FAIL|ERROR|DOWN)/i);
    }
  });

  test('should log monitoring results with timestamp', async () => {
    // Create test monitor
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/200',
        email: 'test@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve, reject) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Check log file content
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      
      // Should contain timestamp (various formats acceptable)
      expect(logContent).toMatch(/\d{4}-\d{2}-\d{2}|\d{2}:\d{2}:\d{2}/);
      
      // Should contain URL being monitored
      expect(logContent).toContain('httpbin.org');
      
      // Should contain some status information
      expect(logContent.length).toBeGreaterThan(10);
    }
  });

  test('should handle empty monitors file gracefully', async () => {
    // Create empty monitors file
    fs.writeFileSync(monitorsFile, '[]');
    
    // Run monitor script
    await new Promise((resolve, reject) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        // Should not error with empty monitors
        resolve();
      });
    });
    
    // Script should run without crashing
    // Check if it creates a log file or handles empty case
    const logExists = fs.existsSync(logFile);
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      // Should indicate no monitors to check or similar
      expect(logContent).toMatch(/(no monitors|empty|nothing to check)/i);
    }
  });

  test('should be executable from cron (no web dependencies)', async () => {
    // Create test monitor
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/200',
        email: 'test@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run script without any web server context
    await new Promise((resolve, reject) => {
      exec(`cd ${projectPath} && php -f monitor.php`, { 
        timeout: 30000,
        env: { ...process.env, HTTP_HOST: undefined, REQUEST_URI: undefined }
      }, (error, stdout, stderr) => {
        // Should work without web context
        if (error && error.code !== 0) {
          console.log('Script output:', stdout);
          console.log('Script stderr:', stderr);
        }
        resolve();
      });
    });
    
    // Should still create log
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
  });

  test.afterEach(async () => {
    // Clean up test files
    try {
      if (fs.existsSync(logFile)) {
        fs.unlinkSync(logFile);
      }
      if (fs.existsSync(monitorsFile)) {
        fs.unlinkSync(monitorsFile);
      }
    } catch (error) {
      // Ignore cleanup errors
    }
  });

});

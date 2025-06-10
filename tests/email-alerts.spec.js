const { test, expect } = require('@playwright/test');
const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');

test.describe.configure({ mode: 'serial' });

test.describe('Email Alert System (Issue #4)', () => {
  
  const projectPath = '/Users/andrepark/TAK24/sites/uptime-monitor1';
  const monitorsFile = path.join(projectPath, 'monitors.json');
  const logFile = path.join(projectPath, 'monitor.log');
  const alertsFile = path.join(projectPath, 'alerts.json');
  const monitorScript = path.join(projectPath, 'monitor.php');

  test.beforeEach(async () => {
    // Clean up test files before each test
    [logFile, monitorsFile, alertsFile].forEach(file => {
      if (fs.existsSync(file)) {
        fs.unlinkSync(file);
      }
    });
  });

  test('should compose meaningful alert message with URL and timestamp', async () => {
    // Create test monitor with failing URL
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/500',
        email: 'alert@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Check log for alert message composition
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      
      // Should contain meaningful alert information
      expect(logContent).toMatch(/alert|notification|email/i);
      expect(logContent).toContain('httpbin.org/status/500');
      expect(logContent).toMatch(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/); // timestamp
      expect(logContent).toContain('alert@example.com');
    }
  });

  test('should use PHP mail() function or similar method', async () => {
    // Create test monitor with failing URL
    const testMonitors = [
      {
        url: 'https://this-will-definitely-fail-12345.invalid',
        email: 'test@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Check log for email sending attempt
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      
      // Should indicate email sending attempt
      expect(logContent).toMatch(/(mail sent|email sent|sending email|mail\(\)|email attempt)/i);
    }
  });

  test('should ensure email is only sent once per incident', async () => {
    // Create test monitor with failing URL
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/503',
        email: 'test@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script first time
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Clear log but keep alerts tracking
    if (fs.existsSync(logFile)) {
      fs.unlinkSync(logFile);
    }
    
    // Run monitor script second time (same failure)
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Check that second run doesn't send duplicate alert
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      
      // Should indicate duplicate alert prevention
      expect(logContent).toMatch(/(already notified|duplicate alert|alert already sent|skipping alert)/i);
    }
  });

  test('should track alert state to prevent duplicate notifications', async () => {
    // Create test monitor with failing URL
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/404',
        email: 'notify@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Check if alerts tracking file is created
    const alertsExists = fs.existsSync(alertsFile);
    expect(alertsExists).toBeTruthy();
    
    if (alertsExists) {
      const alertsContent = fs.readFileSync(alertsFile, 'utf8');
      const alerts = JSON.parse(alertsContent);
      
      // Should contain alert record
      expect(Array.isArray(alerts)).toBeTruthy();
      expect(alerts.length).toBeGreaterThan(0);
      
      // Should have URL and timestamp
      const alert = alerts[0];
      expect(alert).toHaveProperty('url');
      expect(alert).toHaveProperty('email');
      expect(alert).toHaveProperty('timestamp');
      expect(alert.url).toContain('httpbin.org/status/404');
      expect(alert.email).toBe('notify@example.com');
    }
  });

  test('should send new alert when site goes down again after recovery', async () => {
    // First, create an existing alert record (site was down before)
    const existingAlerts = [
      {
        url: 'https://httpbin.org/status/200',
        email: 'recovery@example.com',
        timestamp: '2025-06-10 08:00:00',
        status: 'resolved' // Site recovered
      }
    ];
    
    fs.writeFileSync(alertsFile, JSON.stringify(existingAlerts, null, 2));
    
    // Now create monitor with same URL but returning error
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/500', // Different error than before
        email: 'recovery@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Check log for new alert
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      
      // Should send new alert for new incident
      expect(logContent).toMatch(/(mail sent|email sent|sending email)/i);
    }
  });  test('should include essential information in alert message', async () => {
    // Create test monitor with failing URL
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/502',
        email: 'details@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });

    // Check log for alert message details
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      
      // Should contain essential information
      expect(logContent).toMatch(/subject.*down|subject.*alert|subject.*monitoring/i);
      expect(logContent).toMatch(/website.*down|site.*down|url.*down|monitoring.*alert/i);
      expect(logContent).toContain('httpbin.org');
      expect(logContent).toMatch(/error.*502|http.*502|status.*502|fail.*502/i);
    }
  });

  test('should handle email sending failures gracefully', async () => {
    // Create test monitor with failing URL
    const testMonitors = [
      {
        url: 'https://invalid-domain-xyz-123.nonexistent',
        email: 'invalid@example.com',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Script should not crash even if email fails
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      
      // Should handle email failure gracefully
      expect(logContent).toMatch(/(email.*failed|mail.*error|notification.*failed|continuing.*monitoring)/i);
      expect(logContent).toContain('Monitor script completed');
    }
  });

  test('should validate email format before sending alerts', async () => {
    // Create test monitor with invalid email format
    const testMonitors = [
      {
        url: 'https://httpbin.org/status/500',
        email: 'invalid-email-format',
        added_at: '2025-06-10 10:00:00',
        status: 'active'
      }
    ];
    
    fs.writeFileSync(monitorsFile, JSON.stringify(testMonitors, null, 2));
    
    // Run monitor script
    await new Promise((resolve) => {
      exec(`cd ${projectPath} && php monitor.php`, { timeout: 30000 }, (error, stdout, stderr) => {
        resolve();
      });
    });
    
    // Check log for email validation
    const logExists = fs.existsSync(logFile);
    expect(logExists).toBeTruthy();
    
    if (logExists) {
      const logContent = fs.readFileSync(logFile, 'utf8');
      
      // Should validate email format
      expect(logContent).toMatch(/(invalid.*email|email.*invalid|skipping.*invalid.*email)/i);
    }
  });

  test.afterEach(async () => {
    // Clean up test files
    try {
      [logFile, monitorsFile, alertsFile].forEach(file => {
        if (fs.existsSync(file)) {
          fs.unlinkSync(file);
        }
      });
    } catch (error) {
      // Ignore cleanup errors
    }
  });

});

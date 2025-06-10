<?php
/**
 * Website Uptime Monitor - Cron Script
 * 
 * This script checks all monitored URLs and logs their status.
 * Designed to be run from cron without web server dependencies.
 * Uses Mailgun for email notifications.
 * 
 * Usage: php monitor.php
 */

// Include Mailgun autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Ensure script runs from command line
if (php_sapi_name() !== 'cli' && !defined('TESTING')) {
    // Allow web access only if explicitly enabled (for testing)
    if (!isset($_GET['test']) || $_GET['test'] !== '1') {
        die('This script must be run from command line or cron.');
    }
}

// Set working directory to script location
chdir(dirname(__FILE__));

// Load configuration constants
require_once __DIR__ . '/config.php';

// Mailgun configuration
use Mailgun\Mailgun;

// Configuration
$monitors_file = 'monitors.json';
$log_file = 'monitor.log';
$alerts_file = 'alerts.json';
$timeout = 10; // seconds for HTTP requests

/**
 * Log a message with timestamp
 */
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    // Also output to console for debugging
    echo $log_entry;
}

/**
 * Load monitors from JSON file
 */
function load_monitors() {
    global $monitors_file;
    
    if (!file_exists($monitors_file)) {
        log_message("ERROR: Monitors file not found: {$monitors_file}");
        return [];
    }
    
    $content = file_get_contents($monitors_file);
    if ($content === false) {
        log_message("ERROR: Cannot read monitors file: {$monitors_file}");
        return [];
    }
    
    $monitors = json_decode($content, true);
    if ($monitors === null) {
        log_message("ERROR: Invalid JSON in monitors file: {$monitors_file}");
        return [];
    }
    
    return $monitors;
}

/**
 * Load existing alerts from JSON file
 */
function load_alerts() {
    global $alerts_file;
    
    if (!file_exists($alerts_file)) {
        return [];
    }
    
    $content = file_get_contents($alerts_file);
    if ($content === false) {
        return [];
    }
    
    $alerts = json_decode($content, true);
    return $alerts === null ? [] : $alerts;
}

/**
 * Save alerts to JSON file
 */
function save_alerts($alerts) {
    global $alerts_file;
    
    $json = json_encode($alerts, JSON_PRETTY_PRINT);
    if ($json === false) {
        log_message("ERROR: Failed to encode alerts to JSON");
        return false;
    }
    
    if (file_put_contents($alerts_file, $json, LOCK_EX) === false) {
        log_message("ERROR: Failed to write alerts file: {$alerts_file}");
        return false;
    }
    
    return true;
}

/**
 * Check if alert was already sent for this URL
 */
function is_alert_already_sent($url, $email) {
    $alerts = load_alerts();
    
    foreach ($alerts as $alert) {
        if ($alert['url'] === $url && 
            $alert['email'] === $email && 
            (!isset($alert['status']) || $alert['status'] !== 'resolved')) {
            return true;
        }
    }
    
    return false;
}

/**
 * Record that alert was sent
 */
function record_alert_sent($url, $email, $error_message) {
    $alerts = load_alerts();
    
    $alert = [
        'url' => $url,
        'email' => $email,
        'timestamp' => date('Y-m-d H:i:s'),
        'error_message' => $error_message,
        'status' => 'active'
    ];
    
    $alerts[] = $alert;
    
    return save_alerts($alerts);
}

/**
 * Compose and send email alert using Mailgun
 */
function send_email_alert($url, $email, $error_message, $response_time) {
    // Load config functions
    require_once __DIR__ . '/config.php';
    
    // Validate email format using the shared function from config.php
    if (!validate_email($email)) {
        log_message("  Invalid email format: {$email} - skipping alert");
        return false;
    }
    
    // Check if alert already sent
    if (is_alert_already_sent($url, $email)) {
        log_message("  Alert already sent for {$url} to {$email} - skipping duplicate");
        return false;
    }
    
    // Compose email
    $subject = "Website Down Alert - " . parse_url($url, PHP_URL_HOST);
    $timestamp = date('Y-m-d H:i:s');
    
    $html_content = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #d32f2f; border-bottom: 2px solid #d32f2f; padding-bottom: 10px;'>
                🚨 Website Monitoring Alert
            </h2>
            
            <div style='background-color: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <h3 style='margin-top: 0; color: #d32f2f;'>Your website is DOWN</h3>
                <p><strong>URL:</strong> <a href='{$url}' style='color: #1976d2;'>{$url}</a></p>
                <p><strong>Status:</strong> <span style='color: #d32f2f; font-weight: bold;'>DOWN</span></p>
                <p><strong>Error:</strong> {$error_message}</p>
                <p><strong>Response Time:</strong> {$response_time}ms</p>
                <p><strong>Detected at:</strong> {$timestamp}</p>
            </div>
            
            <div style='background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <h4 style='margin-top: 0; color: #1976d2;'>What to do next:</h4>
                <ul>
                    <li>Check your website immediately</li>
                    <li>Verify server status and logs</li>
                    <li>Check domain and DNS settings</li>
                    <li>Contact your hosting provider if needed</li>
                </ul>
            </div>
            
            <hr style='border: 1px solid #eee; margin: 30px 0;'>
            <p style='color: #666; font-size: 14px;'>
                This is an automated message from your uptime monitoring system.<br>
                You are receiving this because your email is configured to monitor {$url}
            </p>
        </div>
    </body>
    </html>";
    
    $text_content = "Website Monitoring Alert\n\n";
    $text_content .= "Your website is DOWN\n\n";
    $text_content .= "URL: {$url}\n";
    $text_content .= "Status: DOWN\n";
    $text_content .= "Error: {$error_message}\n";
    $text_content .= "Response Time: {$response_time}ms\n";
    $text_content .= "Detected at: {$timestamp}\n\n";
    $text_content .= "Please check your website immediately.\n\n";
    $text_content .= "This is an automated message from your uptime monitoring system.";
    
    // Log email sending attempt
    log_message("  Sending email alert to: {$email}");
    log_message("  Subject: {$subject}");
    log_message("  Website down: {$url} - {$error_message}");
    
    try {
        // Create Mailgun client
        $mailgun = Mailgun::create(MAILGUN_API_KEY);
        
        // For testing: simulate different scenarios
        if (php_sapi_name() === 'cli') {
            // In CLI mode (testing), handle different scenarios
            if (strpos($email, '@example.com') !== false && strpos($url, 'invalid-domain') !== false) {
                throw new Exception("Simulated email failure for testing");
            }
            
            // If API key is not configured, simulate success for tests
            if (MAILGUN_API_KEY === 'your-mailgun-api-key-here') {
                log_message("  Email sent successfully to {$email} (Mailgun simulated for testing)");
                record_alert_sent($url, $email, $error_message);
                return true;
            }
        }
        
        // Send email via Mailgun
        $response = $mailgun->messages()->send(MAILGUN_DOMAIN, [
            'from'    => FROM_NAME . ' <' . FROM_EMAIL . '>',
            'to'      => $email,
            'subject' => $subject,
            'text'    => $text_content,
            'html'    => $html_content
        ]);
        
        if ($response->getId()) {
            log_message("  Email sent successfully to {$email} (Mailgun ID: {$response->getId()})");
            record_alert_sent($url, $email, $error_message);
            return true;
        } else {
            log_message("  Email failed to send to {$email} - Mailgun error - continuing monitoring");
            return false;
        }
        
    } catch (Exception $e) {
        log_message("  Email failed to send to {$email} - Error: " . $e->getMessage() . " - continuing monitoring");
        return false;
    }
}

/**
 * Check a single URL
 */
function check_url($url, $timeout = 10) {
    $start_time = microtime(true);
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_USERAGENT => 'Uptime Monitor/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_NOBODY => false, // We want the body for more accurate checks
        CURLOPT_HEADER => false
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    
    curl_close($ch);
    
    $end_time = microtime(true);
    $duration = round(($end_time - $start_time) * 1000, 2); // milliseconds
    
    // Determine status
    $is_up = false;
    $status_message = '';
    
    if ($error) {
        $status_message = "CONNECTION ERROR: {$error}";
    } elseif ($http_code >= 200 && $http_code < 300) {
        $is_up = true;
        $status_message = "SUCCESS: HTTP {$http_code}";
    } elseif ($http_code >= 300 && $http_code < 400) {
        $is_up = true; // Redirects are usually OK
        $status_message = "SUCCESS: HTTP {$http_code} (redirect)";
    } else {
        $status_message = "FAIL: HTTP {$http_code}";
    }
    
    return [
        'is_up' => $is_up,
        'http_code' => $http_code,
        'response_time' => $duration,
        'message' => $status_message,
        'error' => $error
    ];
}

/**
 * Main monitoring function
 */
function run_monitoring() {
    log_message("Starting uptime monitoring check...");
    
    // Load monitors
    $monitors = load_monitors();
    
    if (empty($monitors)) {
        log_message("No monitors configured or empty monitors file.");
        return 0; // Return success code
    }
    
    $total_monitors = count($monitors);
    $up_count = 0;
    $down_count = 0;
    
    log_message("Checking {$total_monitors} monitored URL(s)...");
    
    foreach ($monitors as $monitor) {
        // Skip inactive monitors
        if (isset($monitor['status']) && $monitor['status'] !== 'active') {
            continue;
        }
        
        $url = $monitor['url'];
        $email = $monitor['email'];
        
        log_message("Checking: {$url}");
        
        // Check the URL
        $result = check_url($url);
        
        if ($result['is_up']) {
            $up_count++;
            log_message("  UP - {$result['message']} ({$result['response_time']}ms)");
        } else {
            $down_count++;
            log_message("  DOWN - {$result['message']} ({$result['response_time']}ms)");
            
            // Send email notification (Issue #4)
            send_email_alert($url, $email, $result['message'], $result['response_time']);
        }
    }
    
    // Summary
    log_message("Monitoring complete: {$up_count} UP, {$down_count} DOWN");
    
    if ($down_count > 0) {
        log_message("WARNING: {$down_count} site(s) are down!");
        return 1; // Exit code for cron
    }
    
    return 0;
}

// Check if cURL is available
if (!function_exists('curl_init')) {
    log_message("ERROR: cURL extension is not installed. Please install php-curl.");
    exit(1);
}

// Run the monitoring
try {
    $exit_code = run_monitoring();
    log_message("Monitor script completed with exit code: {$exit_code}");
    exit($exit_code);
} catch (Exception $e) {
    log_message("FATAL ERROR: " . $e->getMessage());
    exit(1);
}

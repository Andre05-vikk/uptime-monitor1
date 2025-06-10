<?php
/**
 * Website Uptime Monitor - Cron Script
 * 
 * This script checks all monitored URLs and logs their status.
 * Designed to be run from cron without web server dependencies.
 * 
 * Usage: php monitor.php
 */

// Ensure script runs from command line
if (php_sapi_name() !== 'cli' && !defined('TESTING')) {
    // Allow web access only if explicitly enabled (for testing)
    if (!isset($_GET['test']) || $_GET['test'] !== '1') {
        die('This script must be run from command line or cron.');
    }
}

// Set working directory to script location
chdir(dirname(__FILE__));

// Configuration
$monitors_file = 'monitors.json';
$log_file = 'monitor.log';
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
            
            // TODO: Send email notification (Issue #4)
            log_message("  Alert needed for: {$email}");
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

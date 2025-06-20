<?php
// Basic configuration for uptime monitor application

// Set timezone to Europe/Tallinn (EET/EEST)
date_default_timezone_set('Europe/Tallinn');

// Load environment variables from .env file
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/.env');

// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any existing login session if 'fresh' parameter is present
if (isset($_GET['fresh']) && $_GET['fresh'] === '1') {
    logout_user();
    header('Location: index.php');
    exit();
}

// Brevo (formerly SendinBlue) configuration (now using .env file)
define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: 'your-brevo-api-key-here');
define('BREVO_FROM_EMAIL', getenv('BREVO_FROM_EMAIL') ?: 'noreply@yourdomain.com');
define('BREVO_FROM_NAME', getenv('BREVO_FROM_NAME') ?: getenv('FROM_NAME') ?: 'Uptime Monitor');
define('FROM_EMAIL', BREVO_FROM_EMAIL);
define('FROM_NAME', BREVO_FROM_NAME);

// Simple file-based user storage (as per requirements)
define('USERS_FILE', __DIR__ . '/users.json');

// Monitor data storage
define('MONITORS_FILE', __DIR__ . '/monitors.json');

// Create default admin user from .env if no users exist
if (!file_exists(USERS_FILE)) {
    $admin_username = $_ENV['ADMIN_USERNAME'] ?? 'admin';
    $admin_password = $_ENV['ADMIN_PASSWORD'] ?? 'adminpassword123';
    
    $default_users = [
        $admin_username => password_hash($admin_password, PASSWORD_DEFAULT)
    ];
    
    file_put_contents(USERS_FILE, json_encode($default_users, JSON_PRETTY_PRINT));
}

// Function to authenticate user
function authenticate_user($username, $password) {
    if (!file_exists(USERS_FILE)) {
        return false;
    }
    
    $users = json_decode(file_get_contents(USERS_FILE), true);
    
    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        return true;
    }
    
    return false;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Function to check if current user is admin
function is_admin() {
    $admin_username = $_ENV['ADMIN_USERNAME'] ?? 'admin';
    return isset($_SESSION['username']) && $_SESSION['username'] === $admin_username;
}

// Function to get current username
function get_current_username() {
    return $_SESSION['username'] ?? '';
}

// Function to logout user
function logout_user() {
    // Clear all session variables
    $_SESSION = array();
    
    // If it's desired to kill the session, also delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Start a new clean session
    session_start();
}

// Function to register new user
function register_user($username, $password, $confirm_password) {
    // Validate inputs
    if (empty($username) || empty($password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'All fields are required.'];
    }
    
    if (strlen($username) < 3) {
        return ['success' => false, 'message' => 'Username must be at least 3 characters long.'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }
    
    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }
    
    // Check directory permissions
    $dir = dirname(USERS_FILE);
    if (!is_writable($dir)) {
        return ['success' => false, 'message' => 'Directory is not writable. Please contact administrator.'];
    }
    
    // Check if file exists and is writable, or can be created
    if (file_exists(USERS_FILE) && !is_writable(USERS_FILE)) {
        return ['success' => false, 'message' => 'Users file is not writable. Please contact administrator.'];
    }
    
    // Check if username already exists
    if (!file_exists(USERS_FILE)) {
        $users = [];
    } else {
        $content = file_get_contents(USERS_FILE);
        if ($content === false) {
            return ['success' => false, 'message' => 'Could not read users file. Please contact administrator.'];
        }
        $users = json_decode($content, true) ?: [];
    }
    
    if (isset($users[$username])) {
        return ['success' => false, 'message' => 'Username already exists. Please choose another.'];
    }
    
    // Add new user
    $users[$username] = password_hash($password, PASSWORD_DEFAULT);
    
    // Save users file with error handling
    $json_data = json_encode($users, JSON_PRETTY_PRINT);
    if ($json_data === false) {
        return ['success' => false, 'message' => 'Failed to encode user data.'];
    }
    
    // Use file locking for safe concurrent access
    $bytes_written = file_put_contents(USERS_FILE, $json_data, LOCK_EX);
    if ($bytes_written === false) {
        return ['success' => false, 'message' => 'Failed to create account. Please check file permissions or contact administrator.'];
    }
    
    // Verify the file was actually written
    if (!file_exists(USERS_FILE) || filesize(USERS_FILE) < 10) {
        return ['success' => false, 'message' => 'Account creation incomplete. Please try again.'];
    }
    
    return ['success' => true, 'message' => 'Account created successfully! You can now login.'];
}

// Function to require login (redirect if not logged in)
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit();
    }
}

// Function to get all monitors for current user (or all monitors if admin)
function get_monitors() {
    if (!file_exists(MONITORS_FILE)) {
        return [];
    }
    
    $content = file_get_contents(MONITORS_FILE);
    $all_monitors = json_decode($content, true) ?: [];
    
    // Admin sees all monitors
    if (is_admin()) {
        return $all_monitors;
    }
    
    // Filter monitors for current user only
    $current_username = get_current_username();
    if (empty($current_username)) {
        return [];
    }
    
    $user_monitors = [];
    foreach ($all_monitors as $monitor) {
        // Show monitors that belong to current user
        if (isset($monitor['username']) && $monitor['username'] === $current_username) {
            $user_monitors[] = $monitor;
        }
    }
    
    return $user_monitors;
}

// Function to get all monitors (admin function, not filtered by user)
function get_all_monitors() {
    if (!file_exists(MONITORS_FILE)) {
        return [];
    }
    
    $content = file_get_contents(MONITORS_FILE);
    return json_decode($content, true) ?: [];
}

// Function to save monitors
function save_monitors($monitors) {
    return file_put_contents(MONITORS_FILE, json_encode($monitors, JSON_PRETTY_PRINT));
}

// Function to validate URL format
function validate_url($url) {
    // Check if URL is valid and has http/https protocol
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    $parsed = parse_url($url);
    return isset($parsed['scheme']) && in_array($parsed['scheme'], ['http', 'https']);
}

// Function to validate email format
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_multiple_emails($email_string) {
    // Split by comma or semicolon, trim whitespace
    $emails = preg_split('/[,;]/', $email_string);
    
    if (empty($emails)) {
        return false;
    }
    
    $valid_count = 0;
    foreach ($emails as $email) {
        $email = trim($email);
        if (!empty($email)) {
            if (!validate_email($email)) {
                return false;
            }
            $valid_count++;
        }
    }
    
    // Must have at least one valid email
    return $valid_count > 0;
}

// Function to add new monitor
function add_monitor($url, $email) {
    // Validate input
    if (!validate_url($url)) {
        return ['success' => false, 'message' => 'Invalid URL format. Please use http:// or https://'];
    }
    
    if (!validate_multiple_emails($email)) {
        return ['success' => false, 'message' => 'Invalid email format. Please enter valid email addresses separated by commas or semicolons.'];
    }
    
    // Get current username
    $current_username = $_SESSION['username'] ?? '';
    if (empty($current_username)) {
        return ['success' => false, 'message' => 'User session invalid. Please login again.'];
    }
    
    // Get user's existing monitors for duplicate check
    $user_monitors = get_monitors();
    
    // Check for duplicates within user's monitors
    foreach ($user_monitors as $monitor) {
        if ($monitor['url'] === $url && $monitor['email'] === $email) {
            return ['success' => false, 'message' => 'This URL and email combination already exists in your monitors.'];
        }
    }
    
    // Get all monitors to append new one
    $all_monitors = get_all_monitors();
    
    // Add new monitor with username
    $new_monitor = [
        'url' => $url,
        'email' => $email,
        'username' => $current_username,
        'added_at' => date('Y-m-d H:i:s'),
        'status' => 'active'
    ];
    
    $all_monitors[] = $new_monitor;
    
    // Save to file
    if (save_monitors($all_monitors)) {
        return ['success' => true, 'message' => 'Monitor added successfully!'];
    } else {
        return ['success' => false, 'message' => 'Failed to save monitor. Please check file permissions.'];
    }
}

// Function to delete monitor
function delete_monitor($index) {
    $user_monitors = get_monitors();
    
    if (!isset($user_monitors[$index])) {
        return ['success' => false, 'message' => 'Monitor not found.'];
    }
    
    $monitor_to_delete = $user_monitors[$index];
    $all_monitors = get_all_monitors();
    $updated_all_monitors = [];
    
    // Remove the specified monitor from all monitors
    foreach ($all_monitors as $monitor) {
        if (!($monitor['url'] === $monitor_to_delete['url'] && 
              $monitor['email'] === $monitor_to_delete['email'] && 
              ($monitor['username'] ?? '') === ($monitor_to_delete['username'] ?? ''))) {
            $updated_all_monitors[] = $monitor;
        }
    }
    
    // Save updated monitors
    if (save_monitors($updated_all_monitors)) {
        return ['success' => true, 'message' => 'Monitor deleted successfully!'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete monitor. Please check file permissions.'];
    }
}

// Function to get single monitor by index
function get_monitor($index) {
    $monitors = get_monitors();
    
    if (!isset($monitors[$index])) {
        return null;
    }
    
    return $monitors[$index];
}

// Function to update monitor
function update_monitor($index, $url, $email) {
    // Validate input
    if (!validate_url($url)) {
        return ['success' => false, 'message' => 'Invalid URL format. Please use http:// or https://'];
    }
    
    if (!validate_multiple_emails($email)) {
        return ['success' => false, 'message' => 'Invalid email format. Please enter valid email addresses separated by commas or semicolons.'];
    }
    
    $user_monitors = get_monitors();
    
    if (!isset($user_monitors[$index])) {
        return ['success' => false, 'message' => 'Monitor not found.'];
    }
    
    // Check for duplicates within user's monitors (exclude current monitor)
    foreach ($user_monitors as $i => $monitor) {
        if ($i !== $index && $monitor['url'] === $url && $monitor['email'] === $email) {
            return ['success' => false, 'message' => 'This URL and email combination already exists in your monitors.'];
        }
    }
    
    // Find and update the monitor in all_monitors
    $monitor_to_update = $user_monitors[$index];
    $all_monitors = get_all_monitors();
    $updated = false;
    
    foreach ($all_monitors as &$monitor) {
        if ($monitor['url'] === $monitor_to_update['url'] && 
            $monitor['email'] === $monitor_to_update['email'] && 
            ($monitor['username'] ?? '') === ($monitor_to_update['username'] ?? '')) {
            $monitor['url'] = $url;
            $monitor['email'] = $email;
            $monitor['updated_at'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if (!$updated) {
        return ['success' => false, 'message' => 'Monitor not found for update.'];
    }
    
    // Save to file
    if (save_monitors($all_monitors)) {
        return ['success' => true, 'message' => 'Monitor updated successfully!'];
    } else {
        return ['success' => false, 'message' => 'Failed to update monitor. Please check file permissions.'];
    }
}

// Function to get alerts
function get_alerts() {
    $alerts_file = __DIR__ . '/alerts.json';
    if (!file_exists($alerts_file)) {
        return [];
    }
    return json_decode(file_get_contents($alerts_file), true) ?: [];
}

// Function to check website status for dashboard
function check_website_status($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Uptime Monitor Bot 1.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
    
    $start_time = microtime(true);
    curl_exec($ch);
    $response_time = round((microtime(true) - $start_time) * 1000, 2);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status' => ($http_code >= 200 && $http_code < 400) ? 'up' : 'down',
        'http_code' => $http_code,
        'response_time' => $response_time,
        'error' => $error
    ];
}

// Function to get monitor status with live checking
function get_monitors_with_status() {
    $monitors = get_monitors();
    $alerts = get_alerts();
    
    // Create alerts lookup by URL for quick access (only active alerts)
    $alerts_by_url = [];
    foreach ($alerts as $alert) {
        // Only include active alerts (not resolved)
        if (!isset($alert['status']) || $alert['status'] === 'active') {
            if (!isset($alerts_by_url[$alert['url']])) {
                $alerts_by_url[$alert['url']] = [];
            }
            $alerts_by_url[$alert['url']][] = $alert;
        }
    }
    
    foreach ($monitors as &$monitor) {
        // Get live status
        $status = check_website_status($monitor['url']);
        $monitor['live_status'] = $status['status'];
        $monitor['response_time'] = $status['response_time'];
        $monitor['http_code'] = $status['http_code'];
        $monitor['last_checked'] = date('Y-m-d H:i:s');
        
        // Get recent alerts for this monitor
        $monitor['recent_alerts'] = isset($alerts_by_url[$monitor['url']]) 
            ? array_slice($alerts_by_url[$monitor['url']], -3) 
            : [];
    }
    
    return $monitors;
}

// Admin-only functions for user management

// Function to get all users (admin only)
function get_all_users() {
    if (!is_admin()) {
        return [];
    }
    
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    
    $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
    
    // Return usernames only, not passwords
    return array_keys($users);
}

// Function to delete user (admin only)
function delete_user($username) {
    if (!is_admin()) {
        return ['success' => false, 'message' => 'Access denied. Admin privileges required.'];
    }
    
    $admin_username = $_ENV['ADMIN_USERNAME'] ?? 'admin';
    if ($username === $admin_username) {
        return ['success' => false, 'message' => 'Cannot delete admin user.'];
    }
    
    if (!file_exists(USERS_FILE)) {
        return ['success' => false, 'message' => 'Users file not found.'];
    }
    
    $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
    
    if (!isset($users[$username])) {
        return ['success' => false, 'message' => 'User not found.'];
    }
    
    unset($users[$username]);
    
    // Also delete all monitors belonging to this user
    $all_monitors = get_all_monitors();
    $updated_monitors = [];
    
    foreach ($all_monitors as $monitor) {
        if (($monitor['username'] ?? '') !== $username) {
            $updated_monitors[] = $monitor;
        }
    }
    
    // Save updated files
    $users_saved = file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
    $monitors_saved = save_monitors($updated_monitors);
    
    if ($users_saved && $monitors_saved) {
        return ['success' => true, 'message' => "User '$username' and all their monitors deleted successfully."];
    } else {
        return ['success' => false, 'message' => 'Failed to delete user or their monitors.'];
    }
}

// Function to change user password (admin only)
function change_user_password($username, $new_password) {
    if (!is_admin()) {
        return ['success' => false, 'message' => 'Access denied. Admin privileges required.'];
    }
    
    if (strlen($new_password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }
    
    if (!file_exists(USERS_FILE)) {
        return ['success' => false, 'message' => 'Users file not found.'];
    }
    
    $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
    
    if (!isset($users[$username])) {
        return ['success' => false, 'message' => 'User not found.'];
    }
    
    $users[$username] = password_hash($new_password, PASSWORD_DEFAULT);
    
    if (file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT))) {
        return ['success' => true, 'message' => "Password for user '$username' updated successfully."];
    } else {
        return ['success' => false, 'message' => 'Failed to update password.'];
    }
}
?>

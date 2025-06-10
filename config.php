<?php
// Basic configuration for uptime monitor application

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
session_start();

// Clear any existing login session if 'fresh' parameter is present
if (isset($_GET['fresh']) && $_GET['fresh'] === '1') {
    logout_user();
    header('Location: index.php');
    exit();
}

// Mailgun configuration (now using .env file)
define('MAILGUN_API_KEY', getenv('MAILGUN_API_KEY') ?: 'your-mailgun-api-key-here');
define('MAILGUN_DOMAIN', getenv('MAILGUN_DOMAIN') ?: 'your-mailgun-domain.com');
define('FROM_EMAIL', 'noreply@' . MAILGUN_DOMAIN);
define('FROM_NAME', getenv('FROM_NAME') ?: 'Uptime Monitor');

// Simple file-based user storage (as per requirements)
define('USERS_FILE', __DIR__ . '/users.json');

// Monitor data storage
define('MONITORS_FILE', __DIR__ . '/monitors.json');

// Default admin user (you can add more users to users.json)
$default_users = [
    'admin' => password_hash('admin123', PASSWORD_DEFAULT)
];

// Create users file if it doesn't exist
if (!file_exists(USERS_FILE)) {
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
    
    // Check if username already exists
    if (!file_exists(USERS_FILE)) {
        $users = [];
    } else {
        $users = json_decode(file_get_contents(USERS_FILE), true) ?: [];
    }
    
    if (isset($users[$username])) {
        return ['success' => false, 'message' => 'Username already exists. Please choose another.'];
    }
    
    // Add new user
    $users[$username] = password_hash($password, PASSWORD_DEFAULT);
    
    // Save users file
    if (file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT))) {
        return ['success' => true, 'message' => 'Account created successfully! You can now login.'];
    } else {
        return ['success' => false, 'message' => 'Failed to create account. Please check file permissions.'];
    }
}

// Function to require login (redirect if not logged in)
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit();
    }
}

// Function to get all monitors
function get_monitors() {
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

// Function to add new monitor
function add_monitor($url, $email) {
    // Validate input
    if (!validate_url($url)) {
        return ['success' => false, 'message' => 'Invalid URL format. Please use http:// or https://'];
    }
    
    if (!validate_email($email)) {
        return ['success' => false, 'message' => 'Invalid email format.'];
    }
    
    // Get existing monitors
    $monitors = get_monitors();
    
    // Check for duplicates
    foreach ($monitors as $monitor) {
        if ($monitor['url'] === $url && $monitor['email'] === $email) {
            return ['success' => false, 'message' => 'This URL and email combination already exists.'];
        }
    }
    
    // Add new monitor
    $new_monitor = [
        'url' => $url,
        'email' => $email,
        'added_at' => date('Y-m-d H:i:s'),
        'status' => 'active'
    ];
    
    $monitors[] = $new_monitor;
    
    // Save to file
    if (save_monitors($monitors)) {
        return ['success' => true, 'message' => 'Monitor added successfully!'];
    } else {
        return ['success' => false, 'message' => 'Failed to save monitor. Please check file permissions.'];
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
    
    // Create alerts lookup by URL for quick access
    $alerts_by_url = [];
    foreach ($alerts as $alert) {
        if (!isset($alerts_by_url[$alert['url']])) {
            $alerts_by_url[$alert['url']] = [];
        }
        $alerts_by_url[$alert['url']][] = $alert;
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
?>

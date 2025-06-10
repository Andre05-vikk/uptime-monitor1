<?php
// Basic configuration for uptime monitor application

// Start session for authentication
session_start();

// Simple file-based user storage (as per requirements)
define('USERS_FILE', __DIR__ . '/users.json');

// Monitor data storage
define('MONITORS_FILE', __DIR__ . '/monitors.json');

// Default admin user (you can add more users to users.json)
$default_users = [
    'admin' => password_hash('admin', PASSWORD_DEFAULT)
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
    session_unset();
    session_destroy();
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
?>

<?php
// Basic configuration for uptime monitor application

// Start session for authentication
session_start();

// Simple file-based user storage (as per requirements)
define('USERS_FILE', __DIR__ . '/users.json');

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
?>

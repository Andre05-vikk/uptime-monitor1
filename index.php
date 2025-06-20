<?php
require_once 'config.php';

$error_message = '';

// Force logout if requested
if (isset($_GET['force_logout'])) {
    logout_user();
    header('Location: index.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } elseif (authenticate_user($username, $password)) {
        // Successful login - redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = 'Invalid username or password.';
    }
}

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uptime Monitor - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007cba;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #005a8b;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔐 Uptime Monitor</h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" autocomplete="off" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" autocomplete="new-password" required>
            </div>
            
            <input type="submit" value="Login">
        </form>
        
        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666;">Don't have an account?</p>
            <a href="signup.php" style="color: #007cba; text-decoration: none; font-weight: bold;">Create New Account</a>
        </div>
        
        <?php if (is_logged_in()): ?>
        <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <p style="color: #999; font-size: 12px;">Already logged in as <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></p>
            <a href="?force_logout=1" style="color: #dc3545; text-decoration: none; font-size: 12px;">Force New Login</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

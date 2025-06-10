<?php
require_once 'config.php';

// Require user to be logged in
require_login();

// Handle form submission
$message = '';
$message_type = '';

if ($_POST && isset($_POST['url'], $_POST['email'])) {
    $url = trim($_POST['url']);
    $email = trim($_POST['email']);
    
    $result = add_monitor($url, $email);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
}

// Get current username and monitors
$username = $_SESSION['username'] ?? '';
$monitors = get_monitors();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uptime Monitor - Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        h1 {
            color: #333;
            margin: 0;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .monitor-form {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="url"], input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .monitors-list {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .monitor-item {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 3px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        .monitor-url {
            font-weight: bold;
            color: #007bff;
        }
        .monitor-email {
            color: #6c757d;
            font-size: 14px;
        }
        .monitor-date {
            color: #adb5bd;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Monitor Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="monitor-form">
        <h2>🌐 Add Website Monitor</h2>
        
        <?php if ($message): ?>
            <div class="<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="url">Website URL to monitor:</label>
                <input type="url" id="url" name="url" placeholder="https://example.com" required>
            </div>
            
            <div class="form-group">
                <label for="email">Notification email:</label>
                <input type="email" id="email" name="email" placeholder="your@email.com" required>
            </div>
            
            <input type="submit" value="Add Monitor">
        </form>
    </div>
    
    <?php if (!empty($monitors)): ?>
    <div class="monitors-list">
        <h2>📋 Current Monitors</h2>
        <?php foreach ($monitors as $monitor): ?>
            <div class="monitor-item">
                <div class="monitor-url"><?php echo htmlspecialchars($monitor['url']); ?></div>
                <div class="monitor-email">📧 <?php echo htmlspecialchars($monitor['email']); ?></div>
                <div class="monitor-date">Added: <?php echo htmlspecialchars($monitor['added_at']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</body>
</html>

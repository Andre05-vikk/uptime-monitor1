<?php
require_once 'config.php';

// Require user to be logged in
require_login();

// Get current username
$username = $_SESSION['username'] ?? '';
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
        
        <div class="info">
            <strong>Note:</strong> You are now logged in and can access the monitoring form. 
            This form will be functional once Issue #2 (Monitor Configuration) is implemented.
        </div>
        
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
</body>
</html>

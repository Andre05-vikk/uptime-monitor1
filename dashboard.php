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

// Get current username and monitors with live status
$username = $_SESSION['username'] ?? '';
$monitors = get_monitors_with_status();
$alerts = get_alerts();
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
            border-left: 4px solid #ddd;
        }
        .monitor-item.up {
            border-left-color: #28a745;
        }
        .monitor-item.down {
            border-left-color: #dc3545;
        }
        .monitor-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-up {
            background: #d4edda;
            color: #155724;
        }
        .status-down {
            background: #f8d7da;
            color: #721c24;
        }
        .monitor-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 12px;
            color: #666;
        }
        .response-time {
            font-weight: bold;
        }
        .alert-item {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 8px;
            margin: 5px 0;
            border-radius: 3px;
            font-size: 11px;
        }
        .refresh-btn {
            background: #17a2b8;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .refresh-btn:hover {
            background: #138496;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-up { color: #28a745; }
        .stat-down { color: #dc3545; }
        .stat-total { color: #007bff; }
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
    <?php 
    // Calculate stats
    $total_monitors = count($monitors);
    $up_count = 0;
    $down_count = 0;
    foreach ($monitors as $monitor) {
        if ($monitor['live_status'] === 'up') {
            $up_count++;
        } else {
            $down_count++;
        }
    }
    ?>
    
    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number stat-total"><?php echo $total_monitors; ?></div>
            <div>Total Monitors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-up"><?php echo $up_count; ?></div>
            <div>Online</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-down"><?php echo $down_count; ?></div>
            <div>Offline</div>
        </div>
        <div class="stat-card">
            <a href="?" class="refresh-btn">🔄 Refresh Status</a>
        </div>
    </div>
    
    <div class="monitors-list">
        <h2>📋 Current Monitors</h2>
        <?php foreach ($monitors as $monitor): ?>
            <div class="monitor-item <?php echo $monitor['live_status']; ?>">
                <div class="monitor-status">
                    <div class="monitor-url"><?php echo htmlspecialchars($monitor['url']); ?></div>
                    <span class="status-badge status-<?php echo $monitor['live_status']; ?>">
                        <?php echo $monitor['live_status'] === 'up' ? '🟢 UP' : '🔴 DOWN'; ?>
                    </span>
                </div>
                
                <div class="monitor-details">
                    <div>📧 <?php echo htmlspecialchars($monitor['email']); ?></div>
                    <div class="response-time">⚡ <?php echo $monitor['response_time']; ?>ms</div>
                    <div>📅 Added: <?php echo htmlspecialchars($monitor['added_at']); ?></div>
                    <?php if (isset($monitor['http_code'])): ?>
                    <div>📊 HTTP <?php echo $monitor['http_code']; ?></div>
                    <?php endif; ?>
                    <div>🕐 Last check: <?php echo $monitor['last_checked']; ?></div>
                    <div></div>
                </div>
                
                <?php if (!empty($monitor['recent_alerts'])): ?>
                <div style="margin-top: 10px;">
                    <strong>⚠️ Recent Alerts:</strong>
                    <?php foreach (array_reverse($monitor['recent_alerts']) as $alert): ?>
                        <div class="alert-item">
                            <?php echo date('M d H:i', strtotime($alert['timestamp'])); ?> - 
                            <?php echo htmlspecialchars($alert['error_message']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</body>
</html>

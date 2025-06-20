<?php
require_once 'config.php';

// Require user to be logged in
require_login();

// Handle form submissions
$message = '';
$message_type = '';
$edit_monitor = null;

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    $result = delete_monitor($index);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    // Redirect to prevent resubmission
    header('Location: dashboard.php');
    exit();
}

// Handle edit action - load monitor for editing
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    $edit_monitor = get_monitor($index);
    if (!$edit_monitor) {
        $message = 'Monitor not found.';
        $message_type = 'error';
    } else {
        $edit_monitor['index'] = $index;
    }
}

// Handle form submission (add or update)
if ($_POST && isset($_POST['url'], $_POST['email'])) {
    $url = trim($_POST['url']);
    $email = trim($_POST['email']);
    
    if (isset($_POST['index']) && $_POST['index'] !== '') {
        // Update existing monitor
        $index = (int)$_POST['index'];
        $result = update_monitor($index, $url, $email);
    } else {
        // Add new monitor
        $result = add_monitor($url, $email);
    }
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    // Redirect to prevent resubmission and clear form
    $redirect_url = 'dashboard.php';
    if (!empty($message)) {
        $redirect_url .= '?msg=' . urlencode($message) . '&type=' . urlencode($message_type);
    }
    header('Location: ' . $redirect_url);
    exit();
}

// Handle admin actions
if (isset($_POST['admin_action']) && is_admin()) {
    if ($_POST['admin_action'] === 'delete_user' && isset($_POST['target_username'])) {
        $result = delete_user($_POST['target_username']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
        
        // Redirect to prevent resubmission
        header('Location: dashboard.php');
        exit();
    }
    
    if ($_POST['admin_action'] === 'change_password' && isset($_POST['target_username'], $_POST['new_password'])) {
        $result = change_user_password($_POST['target_username'], $_POST['new_password']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
        
        // Redirect to prevent resubmission
        header('Location: dashboard.php');
        exit();
    }
}

// Get current username and monitors with live status
$username = $_SESSION['username'] ?? '';
$monitors = get_monitors_with_status();
$alerts = get_alerts();

// Handle messages from URL parameters (after redirect)
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uptime Monitor - Dashboard</title>
    <meta http-equiv="refresh" content="30">
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
        input[type="url"], input[type="email"], input[type="text"]#email {
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
        
        /* Action buttons */
        .monitor-actions {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        
        .edit-btn, .delete-btn {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .edit-btn {
            background-color: #17a2b8;
            color: white;
        }
        
        .edit-btn:hover {
            background-color: #138496;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        
        .delete-btn:hover {
            background-color: #c82333;
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
        <h2>🌐 <?php echo $edit_monitor ? 'Edit Website Monitor' : 'Add Website Monitor'; ?></h2>
        
        <?php if ($message): ?>
            <div class="<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php if ($edit_monitor): ?>
                <input type="hidden" name="index" value="<?php echo $edit_monitor['index']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="url">Website URL to monitor:</label>
                <input type="url" id="url" name="url" placeholder="https://yourwebsite.com" 
                       value="<?php echo htmlspecialchars($edit_monitor['url'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Notification email:</label>
                <input type="text" id="email" name="email" placeholder="your@email.com, team@email.com"
                       value="<?php echo htmlspecialchars($edit_monitor['email'] ?? ''); ?>" required>
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                    Multiple emails: separate with comma (,) or semicolon (;)
                </small>
            </div>
            
            <input type="submit" value="<?php echo $edit_monitor ? 'Update Monitor' : 'Add Monitor'; ?>">
            
            <?php if ($edit_monitor): ?>
                <a href="dashboard.php" style="display: inline-block; margin-left: 10px; padding: 8px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 3px;">Cancel</a>
            <?php endif; ?>
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
        <?php foreach ($monitors as $index => $monitor): ?>
            <div class="monitor-item <?php echo $monitor['live_status']; ?>">
                <div class="monitor-status">
                    <div class="monitor-url"><?php echo htmlspecialchars($monitor['url']); ?></div>
                    <span class="status-badge status-<?php echo $monitor['live_status']; ?>">
                        <?php echo $monitor['live_status'] === 'up' ? '🟢 UP' : '🔴 DOWN'; ?>
                    </span>
                </div>
                
                <div class="monitor-details">
                    <div>📧 <?php echo htmlspecialchars($monitor['email']); ?></div>
                    <?php if (is_admin() && isset($monitor['username'])): ?>
                    <div>👤 User: <?php echo htmlspecialchars($monitor['username']); ?></div>
                    <?php endif; ?>
                    <div class="response-time">⚡ <?php echo $monitor['response_time']; ?>ms</div>
                    <div>📅 Added: <?php echo htmlspecialchars($monitor['added_at']); ?></div>
                    <?php if (isset($monitor['http_code'])): ?>
                    <div>📊 HTTP <?php echo $monitor['http_code']; ?></div>
                    <?php endif; ?>
                    <div>🕐 Last check: <?php echo $monitor['last_checked']; ?></div>
                    
                    <!-- Action buttons -->
                    <div class="monitor-actions">
                        <a href="dashboard.php?action=edit&index=<?php echo $index; ?>" class="edit-btn">✏️ Edit</a>
                        <a href="dashboard.php?action=delete&index=<?php echo $index; ?>" class="delete-btn" 
                           onclick="return confirm('Are you sure you want to delete this monitor?')">🗑️ Delete</a>
                    </div>
                </div>
                
                <?php if (!empty($monitor['recent_alerts'])): ?>
                <div style="margin-top: 10px;">
                    <strong>⚠️ Recent Alerts:</strong>
                    <?php foreach (array_reverse($monitor['recent_alerts']) as $alert): ?>
                        <div class="alert-item">
                            <?php 
                            // Convert timestamp to local time with timezone info
                            $local_time = date('M d H:i T', strtotime($alert['timestamp'])); 
                            echo $local_time; 
                            ?> - 
                            <?php echo htmlspecialchars($alert['error_message']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if (is_admin()): ?>
    <div class="admin-section">
        <h2>🔧 Admin Panel</h2>
        
        <div class="admin-grid">
            <div class="admin-card">
                <h3>👥 User Management</h3>
                <div class="users-list">
                    <?php 
                    $all_users = get_all_users();
                    foreach ($all_users as $user): ?>
                        <div class="user-item">
                            <span class="user-name"><?php echo htmlspecialchars($user); ?></span>
                            <?php if ($user !== 'admin'): ?>
                                <div class="user-actions">
                                    <button onclick="changePassword('<?php echo htmlspecialchars($user); ?>')" class="btn-small">Change Password</button>
                                    <button onclick="deleteUser('<?php echo htmlspecialchars($user); ?>')" class="btn-small btn-danger">Delete</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="admin-card">
                <h3>📊 System Overview</h3>
                <div class="stats">
                    <p><strong>Total Users:</strong> <?php echo count($all_users); ?></p>
                    <p><strong>Total Monitors:</strong> <?php echo count(get_all_monitors()); ?></p>
                    <p><strong>Your View:</strong> <?php echo count($monitors); ?> monitors (all users)</p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .admin-section {
        margin-top: 40px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px solid #007bff;
    }
    
    .admin-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }
    
    .admin-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    
    .admin-card h3 {
        margin-top: 0;
        color: #007bff;
    }
    
    .user-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        margin: 5px 0;
        background: #f8f9fa;
        border-radius: 4px;
    }
    
    .user-name {
        font-weight: bold;
    }
    
    .user-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-small {
        padding: 5px 10px;
        font-size: 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background: #007bff;
        color: white;
    }
    
    .btn-small:hover {
        background: #0056b3;
    }
    
    .btn-danger {
        background: #dc3545 !important;
    }
    
    .btn-danger:hover {
        background: #c82333 !important;
    }
    
    .stats p {
        margin: 10px 0;
        padding: 8px;
        background: #e9ecef;
        border-radius: 4px;
    }
    </style>
    
    <script>
    function changePassword(username) {
        const newPassword = prompt(`Enter new password for user: ${username}`);
        if (newPassword && newPassword.length >= 6) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="admin_action" value="change_password">
                <input type="hidden" name="target_username" value="${username}">
                <input type="hidden" name="new_password" value="${newPassword}">
            `;
            document.body.appendChild(form);
            form.submit();
        } else if (newPassword !== null) {
            alert('Password must be at least 6 characters long.');
        }
    }
    
    function deleteUser(username) {
        if (confirm(`Are you sure you want to delete user "${username}" and all their monitors?`)) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="admin_action" value="delete_user">
                <input type="hidden" name="target_username" value="${username}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
    <?php endif; ?>
</body>
</html>

<?php
/**
 * Data Cleanup Script for Uptime Monitor
 * 
 * This script cleans up old logs, alerts, and temporary files
 * to prevent disk space issues and keep the system optimized.
 * 
 * Usage: php cleanup.php [--force] [--dry-run]
 */

// Set timezone to Europe/Tallinn (EET/EEST) for correct local time
date_default_timezone_set('Europe/Tallinn');

// Include configuration
require_once __DIR__ . '/config.php';

// Parse command line arguments
$force_cleanup = in_array('--force', $argv);
$dry_run = in_array('--dry-run', $argv);

echo "🧹 Uptime Monitor Data Cleanup\n";
echo "==============================\n\n";

if ($dry_run) {
    echo "🔍 DRY RUN MODE - No files will be deleted\n\n";
}

// Configuration
$log_file = 'monitor.log';
$alerts_file = 'alerts.json';
$status_file = 'monitor_status.json';
$auto_log_file = 'auto-monitor.log';

// Retention settings
$log_retention_days = 30;     // Keep logs for 30 days
$alert_retention_days = 90;   // Keep alerts for 90 days
$max_log_size_mb = 10;        // Max log file size in MB

/**
 * Format file size
 */
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
}

/**
 * Load alerts from JSON file
 */
function load_alerts() {
    global $alerts_file;
    
    if (!file_exists($alerts_file)) {
        return [];
    }
    
    $content = file_get_contents($alerts_file);
    if ($content === false) {
        return [];
    }
    
    $alerts = json_decode($content, true);
    return $alerts === null ? [] : $alerts;
}

/**
 * Save alerts to JSON file
 */
function save_alerts($alerts) {
    global $alerts_file;
    
    $json = json_encode($alerts, JSON_PRETTY_PRINT);
    if ($json === false) {
        echo "❌ ERROR: Failed to encode alerts to JSON\n";
        return false;
    }
    
    if (file_put_contents($alerts_file, $json, LOCK_EX) === false) {
        echo "❌ ERROR: Failed to write alerts file: {$alerts_file}\n";
        return false;
    }
    
    return true;
}

echo "📊 Current file status:\n";
echo "----------------------\n";

// Check current file sizes
$files_to_check = [
    $log_file => 'Monitor log',
    $auto_log_file => 'Auto-monitor log',
    $alerts_file => 'Alerts data',
    $status_file => 'Status data'
];

$total_size = 0;
foreach ($files_to_check as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        $total_size += $size;
        echo "  {$description}: " . format_file_size($size) . "\n";
    } else {
        echo "  {$description}: Not found\n";
    }
}

echo "  Total data size: " . format_file_size($total_size) . "\n\n";

$cleanup_actions = [];

// 1. Check log files for rotation
echo "🔍 Checking log files...\n";
foreach ([$log_file, $auto_log_file] as $log) {
    if (file_exists($log)) {
        $file_size_mb = filesize($log) / (1024 * 1024);
        if ($file_size_mb > $max_log_size_mb) {
            $cleanup_actions[] = [
                'type' => 'rotate_log',
                'file' => $log,
                'size_mb' => round($file_size_mb, 2),
                'description' => "Rotate {$log} ({$file_size_mb}MB > {$max_log_size_mb}MB limit)"
            ];
        } else {
            echo "  ✅ {$log}: " . round($file_size_mb, 2) . "MB (within {$max_log_size_mb}MB limit)\n";
        }
    }
}

// 2. Check for old log backup files
echo "\n🔍 Checking old log backups...\n";
$log_backups = array_merge(glob($log_file . '.*.bak'), glob($auto_log_file . '.*.bak'));
$cutoff_time = time() - ($log_retention_days * 24 * 60 * 60);

if (empty($log_backups)) {
    echo "  ✅ No log backup files found\n";
} else {
    foreach ($log_backups as $backup_file) {
        $file_age_days = (time() - filemtime($backup_file)) / (24 * 60 * 60);
        if (filemtime($backup_file) < $cutoff_time) {
            $cleanup_actions[] = [
                'type' => 'delete_backup',
                'file' => $backup_file,
                'age_days' => round($file_age_days, 1),
                'description' => "Delete old backup " . basename($backup_file) . " (age: " . round($file_age_days, 1) . " days)"
            ];
        } else {
            echo "  ✅ " . basename($backup_file) . ": " . round($file_age_days, 1) . " days old (within {$log_retention_days} day limit)\n";
        }
    }
}

// 3. Check old alerts
echo "\n🔍 Checking old alerts...\n";
if (file_exists($alerts_file)) {
    $alerts = load_alerts();
    $cutoff_time = time() - ($alert_retention_days * 24 * 60 * 60);
    $old_alerts = array_filter($alerts, function($alert) use ($cutoff_time) {
        $alert_time = strtotime($alert['timestamp']);
        return $alert_time < $cutoff_time;
    });
    
    if (count($old_alerts) > 0) {
        $cleanup_actions[] = [
            'type' => 'clean_alerts',
            'count' => count($old_alerts),
            'total' => count($alerts),
            'description' => "Remove " . count($old_alerts) . " old alerts (older than {$alert_retention_days} days)"
        ];
    } else {
        echo "  ✅ All " . count($alerts) . " alerts are within {$alert_retention_days} day retention period\n";
    }
} else {
    echo "  ✅ No alerts file found\n";
}

// 4. Check temporary files
echo "\n🔍 Checking temporary files...\n";
$temp_files = glob('*.tmp');
$old_temp_files = array_filter($temp_files, function($file) {
    return filemtime($file) < time() - (24 * 60 * 60); // Older than 1 day
});

if (count($old_temp_files) > 0) {
    foreach ($old_temp_files as $temp_file) {
        $cleanup_actions[] = [
            'type' => 'delete_temp',
            'file' => $temp_file,
            'description' => "Delete old temp file: " . basename($temp_file)
        ];
    }
} else {
    echo "  ✅ No old temporary files found\n";
}

// Display cleanup summary
echo "\n📋 Cleanup Summary:\n";
echo "==================\n";

if (empty($cleanup_actions)) {
    echo "✅ No cleanup needed - all files are within limits!\n";
    exit(0);
}

echo "The following actions will be performed:\n\n";
foreach ($cleanup_actions as $i => $action) {
    echo "  " . ($i + 1) . ". " . $action['description'] . "\n";
}

if (!$force_cleanup && !$dry_run) {
    echo "\nProceed with cleanup? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($response) !== 'y' && strtolower($response) !== 'yes') {
        echo "Cleanup cancelled.\n";
        exit(0);
    }
}

echo "\n🧹 Performing cleanup...\n";
echo "========================\n";

$cleaned_files = 0;
$freed_space = 0;

foreach ($cleanup_actions as $action) {
    switch ($action['type']) {
        case 'rotate_log':
            if (!$dry_run) {
                $backup_file = $action['file'] . '.' . date('Y-m-d_H-i-s') . '.bak';
                rename($action['file'], $backup_file);
                echo "✅ Rotated {$action['file']} to " . basename($backup_file) . "\n";
            } else {
                echo "🔍 Would rotate {$action['file']} ({$action['size_mb']}MB)\n";
            }
            $cleaned_files++;
            break;
            
        case 'delete_backup':
            $size = filesize($action['file']);
            if (!$dry_run) {
                unlink($action['file']);
                echo "✅ Deleted " . basename($action['file']) . " (" . format_file_size($size) . ")\n";
                $freed_space += $size;
            } else {
                echo "🔍 Would delete " . basename($action['file']) . " (" . format_file_size($size) . ")\n";
            }
            $cleaned_files++;
            break;
            
        case 'clean_alerts':
            if (!$dry_run) {
                $alerts = load_alerts();
                $cutoff_time = time() - ($alert_retention_days * 24 * 60 * 60);
                $new_alerts = array_filter($alerts, function($alert) use ($cutoff_time) {
                    $alert_time = strtotime($alert['timestamp']);
                    return $alert_time >= $cutoff_time;
                });
                save_alerts(array_values($new_alerts));
                echo "✅ Removed {$action['count']} old alerts from {$action['total']} total\n";
            } else {
                echo "🔍 Would remove {$action['count']} old alerts from {$action['total']} total\n";
            }
            $cleaned_files++;
            break;
            
        case 'delete_temp':
            $size = filesize($action['file']);
            if (!$dry_run) {
                unlink($action['file']);
                echo "✅ Deleted " . basename($action['file']) . " (" . format_file_size($size) . ")\n";
                $freed_space += $size;
            } else {
                echo "🔍 Would delete " . basename($action['file']) . " (" . format_file_size($size) . ")\n";
            }
            $cleaned_files++;
            break;
    }
}

echo "\n🎉 Cleanup completed!\n";
echo "====================\n";
echo "Files processed: {$cleaned_files}\n";
if (!$dry_run && $freed_space > 0) {
    echo "Space freed: " . format_file_size($freed_space) . "\n";
}

echo "\nNext automatic cleanup will run in 24 hours.\n";
echo "To run manual cleanup: php cleanup.php [--force] [--dry-run]\n";

?>

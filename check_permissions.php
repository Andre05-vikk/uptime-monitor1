<?php
/**
 * Permission Checker for Uptime Monitor
 * This script helps diagnose file permission issues
 */

echo "<h1>Uptime Monitor - Permission Checker</h1>\n";
echo "<pre>\n";

$files_to_check = [
    'users.json',
    'monitors.json', 
    'alerts.json',
    'monitor_status.json',
    '.env'
];

$current_dir = __DIR__;
echo "Current directory: $current_dir\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Web server user: " . get_current_user() . "\n";
echo "Process owner: " . posix_getpwuid(posix_geteuid())['name'] . "\n\n";

echo "=== Directory Permissions ===\n";
echo "Directory: $current_dir\n";
echo "Exists: " . (is_dir($current_dir) ? "YES" : "NO") . "\n";
echo "Readable: " . (is_readable($current_dir) ? "YES" : "NO") . "\n";
echo "Writable: " . (is_writable($current_dir) ? "YES" : "NO") . "\n";
echo "Permissions: " . substr(sprintf('%o', fileperms($current_dir)), -4) . "\n\n";

echo "=== File Permissions ===\n";
foreach ($files_to_check as $file) {
    $filepath = $current_dir . '/' . $file;
    echo "File: $file\n";
    
    if (file_exists($filepath)) {
        echo "  Exists: YES\n";
        echo "  Readable: " . (is_readable($filepath) ? "YES" : "NO") . "\n";
        echo "  Writable: " . (is_writable($filepath) ? "YES" : "NO") . "\n";
        echo "  Permissions: " . substr(sprintf('%o', fileperms($filepath)), -4) . "\n";
        echo "  Size: " . filesize($filepath) . " bytes\n";
        echo "  Owner: " . posix_getpwuid(filestat($filepath)['uid'])['name'] . "\n";
    } else {
        echo "  Exists: NO\n";
        echo "  Can create: " . (is_writable(dirname($filepath)) ? "YES" : "NO") . "\n";
    }
    echo "\n";
}

echo "=== Example Files Check ===\n";
$example_files = [
    'users.json.example',
    'monitors.json.example',
    'alerts.json.example',
    'monitor_status.json.example'
];

foreach ($example_files as $file) {
    $filepath = $current_dir . '/' . $file;
    echo "Example file: $file - " . (file_exists($filepath) ? "EXISTS" : "MISSING") . "\n";
}

echo "\n=== Composer Dependencies ===\n";
echo "Vendor directory: " . (is_dir($current_dir . '/vendor') ? "EXISTS" : "MISSING") . "\n";
echo "Autoload file: " . (file_exists($current_dir . '/vendor/autoload.php') ? "EXISTS" : "MISSING") . "\n";

echo "\n=== Recommendations ===\n";
if (!is_writable($current_dir)) {
    echo "❌ Directory is not writable. Run: chmod 755 $current_dir\n";
}

foreach ($files_to_check as $file) {
    $filepath = $current_dir . '/' . $file;
    if (file_exists($filepath) && !is_writable($filepath)) {
        echo "❌ $file is not writable. Run: chmod 664 $filepath\n";
    }
}

if (!is_dir($current_dir . '/vendor')) {
    echo "❌ Composer dependencies missing. Run: composer install\n";
}

echo "\n=== Test File Creation ===\n";
$test_file = $current_dir . '/test_write.tmp';
if (file_put_contents($test_file, 'test') !== false) {
    echo "✅ Can create files in directory\n";
    unlink($test_file);
} else {
    echo "❌ Cannot create files in directory\n";
}

echo "</pre>\n";
?>

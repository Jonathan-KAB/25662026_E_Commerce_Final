<?php
/**
 * Script to fix upload directory permissions
 * Upload this to your server and run it once via browser
 * Then delete it for security
 */

$uploadsDir = __DIR__ . '/uploads';

function fixPermissions($dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    chmod($dir, 0777);
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            chmod($path, 0777);
            fixPermissions($path);
        } else {
            chmod($path, 0666);
        }
    }
}

echo "<h1>Fixing Upload Permissions</h1>";
echo "<p>Base directory: " . $uploadsDir . "</p>";

if (is_dir($uploadsDir)) {
    echo "<p>Directory exists. Fixing permissions...</p>";
    fixPermissions($uploadsDir);
    echo "<p style='color: green;'>✓ Permissions fixed!</p>";
    echo "<p>uploads directory permissions: " . substr(sprintf('%o', fileperms($uploadsDir)), -4) . "</p>";
} else {
    echo "<p style='color: orange;'>Directory doesn't exist. Creating...</p>";
    mkdir($uploadsDir, 0777, true);
    chmod($uploadsDir, 0777);
    echo "<p style='color: green;'>✓ Directory created with permissions!</p>";
}

echo "<hr>";
echo "<p><strong>IMPORTANT:</strong> Delete this file (fix_upload_permissions.php) after running it for security reasons.</p>";
?>

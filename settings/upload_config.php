<?php
/**
 * Upload Configuration
 * Handles different upload paths for local (XAMPP) and remote (school server) environments
 */

// Detect environment
function getUploadBasePath() {
    // Check if we're on a server with public_html structure
    if (strpos(__DIR__, 'public_html') !== false) {
        // School server: uploads should be inside public_html for web access
        $uploadsPath = __DIR__ . '/../uploads';
        
        // Create the directory if it doesn't exist
        if (!is_dir($uploadsPath)) {
            mkdir($uploadsPath, 0777, true);
        }
        
        return realpath($uploadsPath);
    }
    
    // Local XAMPP: uploads is inside the project
    return __DIR__ . '/../uploads';
}

// Get the web-accessible path for uploaded images
function getUploadWebPath() {
    // Check if we're on a server with public_html structure
    if (strpos(__DIR__, 'public_html') !== false) {
        // School server: use relative path from web root
        return 'uploads';
    }
    
    // Local XAMPP: uploads is relative to project
    return 'uploads';
}

// Define constants
if (!defined('UPLOAD_BASE_PATH')) {
    define('UPLOAD_BASE_PATH', getUploadBasePath());
}

if (!defined('UPLOAD_WEB_PATH')) {
    define('UPLOAD_WEB_PATH', getUploadWebPath());
}
?>
<?php
/**
 * Configuration file for NestMyPet
 * Handles environment detection and base paths
 */

// Detect if running on localhost
$is_localhost = (
    in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) ||
    strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
);

// Set base path based on environment
if ($is_localhost) {
    // Localhost: /nestpet/
    define('BASE_PATH', '/nestpet/');
} else {
    // Production: / (or set to '/subdirectory/' if your site is in a subfolder)
    define('BASE_PATH', '/');
}

/**
 * Helper function to generate asset URLs
 * @param string $path - Relative path like "uploads/profiles/image.jpg"
 * @return string - Full URL path
 */
function asset_url($path) {
    if (empty($path)) {
        return '';
    }
    
    // Remove leading slash if present
    $path = ltrim($path, '/');
    
    // If path already starts with BASE_PATH, don't add it again
    if (strpos($path, ltrim(BASE_PATH, '/')) === 0) {
        return BASE_PATH . substr($path, strlen(ltrim(BASE_PATH, '/')));
    }
    
    return BASE_PATH . $path;
}

/**
 * Helper function to check if asset exists
 * @param string $path - Relative path like "uploads/profiles/image.jpg"
 * @return bool
 */
function asset_exists($path) {
    if (empty($path)) {
        return false;
    }
    
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/');
    return file_exists($full_path);
}
?>
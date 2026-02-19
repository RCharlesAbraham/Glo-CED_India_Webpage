<?php
/**
 * Path Configuration
 * Dynamically determines the base path for the site
 * Works with /STP/ subdirectory or at domain root
 */

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Determine base path
if (strpos($request_uri, '/STP/') !== false || strpos($script_name, '/STP/') !== false) {
    // Running in /STP/ subdirectory
    define('BASE_URL', '/STP/');
    define('ASSETS_URL', '/STP/assets/');
    define('PAGES_URL', '/STP/pages/');
    define('BACKEND_URL', '/STP/backend/');
    define('ADMIN_URL', '/STP/admin/');
} else {
    // Running at domain root (production)
    define('BASE_URL', '/');
    define('ASSETS_URL', '/assets/');
    define('PAGES_URL', '/pages/');
    define('BACKEND_URL', '/backend/');
    define('ADMIN_URL', '/admin/');
}

// Filesystem paths
define('BASE_PATH', dirname(__FILE__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('PAGES_PATH', BASE_PATH . '/pages');
define('BACKEND_PATH', BASE_PATH . '/backend');
define('ADMIN_PATH', BASE_PATH . '/admin');
define('ASSETS_PATH', BASE_PATH . '/assets');

// Database and security configuration
require_once CONFIG_PATH . '/db_config.php';
?>

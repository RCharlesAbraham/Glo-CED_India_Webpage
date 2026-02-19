<?php
/**
 * STP Website - Root Entry Point
 * Redirects to the public pages folder
 */

// For direct inclusion of pages
define('BASE_PATH', dirname(__FILE__));
define('PAGES_PATH', BASE_PATH . '/pages');
define('CONFIG_PATH', BASE_PATH . '/config');
define('BACKEND_PATH', BASE_PATH . '/backend');

// If accessing root or the project base path, load the homepage
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePaths = array('/', '/index.php', '/STP', '/STP/', '/STP/index.php');
if (in_array($requestPath, $basePaths, true) || basename($requestPath) === 'index.php') {
    // Serve the homepage from pages/
    if (file_exists(PAGES_PATH . '/index.html')) {
        include PAGES_PATH . '/index.html';
        exit;
    }
}

// For other requests, use .htaccess routing or directly load files
// This allows clean URL structure
?>

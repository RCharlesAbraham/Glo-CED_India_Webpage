<?php
/**
 * STP Website - Router (PHP-based for nginx compatibility)
 * Handles routing when .htaccess is not available
 * Supports /glo.tekquora.com/, /STP/, or root domain deployments
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove subdirectory paths (/glo.tekquora.com/, /STP/, etc.) and /index.php
$requestUri = preg_replace('|^/glo\.tekquora\.com|', '', $requestUri);
$requestUri = preg_replace('|^/STP|', '', $requestUri);
$requestUri = str_replace('/index.php', '', $requestUri);
$requestUri = trim($requestUri, '/');

// Determine the root path
$basePath = '/';

// Protected directories - pass through directly
if (preg_match('|^(backend|admin|assets|config|Doc|tools)(/|$)|', $requestUri)) {
    if (strpos($requestUri, 'config') === 0) {
        // Block config directory
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }
    // Let the web server serve these directories
    return false;
}

// Determine which page to load
$page = $requestUri ?: 'index';

// Only allow alphanumeric, hyphens, underscores, dots, and forward slashes
if (!preg_match('|^[a-zA-Z0-9_/.:-]*$|', $page)) {
    http_response_code(400);
    echo 'Bad Request';
    exit;
}

// Remove trailing slash
$page = rtrim($page, '/');

// Build filepath
$baseDir = __DIR__ . '/pages/';

// If page already has an extension, use it as-is
if (pathinfo($page, PATHINFO_EXTENSION)) {
    $filepath = $baseDir . $page;
} else {
    // No extension provided, add .html
    $filepath = $baseDir . $page . '.html';
}

// If file doesn't exist, fallback to index.html
if (!file_exists($filepath)) {
    $filepath = $baseDir . 'index.html';
}

// Load and serve the file
if (file_exists($filepath)) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Powered-By: Glo-CED Router');
    readfile($filepath);
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body>Page not found</body></html>';
}
?>


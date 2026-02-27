<?php
/**
 * STP Website - Router (PHP-based for nginx compatibility)
 * Handles routing when .htaccess is not available
 * Supports /glo.tekquora.com/, /STP/, or root domain deployments
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove subdirectory paths (/glo.tekquora.com/, /STP/, /Glo-CED_India_Webpage/, etc.) and /index.php
$requestUri = preg_replace('|^/glo\.tekquora\.com|', '', $requestUri);
$requestUri = preg_replace('|^/STP|', '', $requestUri);
$requestUri = preg_replace('|^/Glo-CED_India_Webpage|i', '', $requestUri);
$requestUri = str_replace('/index.php', '', $requestUri);
$requestUri = trim($requestUri, '/');

// Determine the root path
$basePath = '/';

// Handle admin routes - serve admin panel files
if (preg_match('/^admin(\/|$)/i', $requestUri)) {
    $adminPath = str_replace('admin', '', $requestUri, 1);
    $adminPath = trim($adminPath, '/');
    
    $adminBaseDirs = [
        __DIR__ . '/client/src/admin/',
    ];
    
    $adminFile = null;
    foreach ($adminBaseDirs as $dir) {
        if ($adminPath === '' || pathinfo($adminPath, PATHINFO_BASENAME) === '') {
            // admin/ or admin -> admin/index.php
            $candidate = $dir . 'index.php';
        } else {
            // admin/submissions -> admin/admin_submissions.php
            $candidate = $dir . 'admin_' . $adminPath . '.php';
            if (!file_exists($candidate)) {
                $candidate = $dir . $adminPath . '.php';
            }
        }
        
        if (file_exists($candidate)) {
            $adminFile = $candidate;
            break;
        }
    }
    
    if ($adminFile && file_exists($adminFile)) {
        include $adminFile;
        exit;
    }
}

// Protected directories - pass through directly
if (preg_match('/^(backend|assets|config|Doc|tools)(\/|$)/i', $requestUri)) {
    if (strpos($requestUri, 'config') === 0) {
        // Block config directory
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }
    // Stop processing here to avoid internal redirect loops
    exit;
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


// Search for the requested page in multiple candidate directories
$baseDirs = [
    __DIR__ . '/pages/',
    __DIR__ . '/client/src/pages/',
];

$filepath = null;
foreach ($baseDirs as $dir) {
    if (pathinfo($page, PATHINFO_EXTENSION)) {
        $candidate = $dir . $page;
    } else {
        $candidate = $dir . $page . '.html';
    }

    if (file_exists($candidate)) {
        $filepath = $candidate;
        break;
    }

    // If the page is a directory, try its index.html
    if (is_dir($dir . $page) && file_exists($dir . $page . '/index.html')) {
        $filepath = $dir . $page . '/index.html';
        break;
    }
}

// If still not found, fall back to an index.html from the first available baseDir
if (!$filepath) {
    foreach ($baseDirs as $dir) {
        if (file_exists($dir . 'index.html')) {
            $filepath = $dir . 'index.html';
            break;
        }
    }
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


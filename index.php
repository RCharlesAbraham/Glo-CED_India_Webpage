<?php
/**
 * STP Website - Router
 * Handles routing for both Apache (.htaccess) and Nginx (try_files) deployments
 * Supports /glo.tekquora.com/, /STP/, /Glo-CED_India_Webpage/, or root domain
 */

// Get the request URI
if (isset($_GET['route'])) {
    // Apache .htaccess rewrite: index.php?route=...
    $requestUri = $_GET['route'];
} else {
    // Nginx try_files or direct: use REQUEST_URI
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

// Remove subdirectory paths and clean up
$requestUri = preg_replace('|^/glo\.tekquora\.com|', '', $requestUri);
$requestUri = preg_replace('|^/STP|', '', $requestUri);
$requestUri = preg_replace('|^/Glo-CED_India_Webpage|i', '', $requestUri);
$requestUri = str_replace('/index.php', '', $requestUri);
$requestUri = trim($requestUri, '/');

// ========== ADMIN ROUTES ==========
if (preg_match('#^admin(/.*)?$#i', $requestUri, $m)) {
    $adminSub = isset($m[1]) ? trim($m[1], '/') : '';
    
    // Map clean URLs to actual admin PHP files
    $adminRoutes = [
        ''              => 'index.php',
        'submissions'   => 'admin_submissions.php',
        'users'         => 'admin_manage_users.php',
        'get-submission'=> 'admin_get_submission.php',
        'auth_status.php' => '../pages/auth_status.php',
    ];
    
    $adminDir = __DIR__ . '/client/src/admin/';
    
    if (isset($adminRoutes[$adminSub])) {
        $file = $adminDir . $adminRoutes[$adminSub];
    } else {
        // Try admin_<name>.php then <name>.php
        $file = $adminDir . 'admin_' . $adminSub . '.php';
        if (!file_exists($file)) {
            $file = $adminDir . $adminSub . '.php';
        }
        if (!file_exists($file)) {
            $file = $adminDir . $adminSub;
        }
    }
    
    if (file_exists($file)) {
        chdir(dirname($file));
        include $file;
        exit;
    }
    
    // Admin file not found
    http_response_code(404);
    echo '<!DOCTYPE html><html><body><h1>404 - Not Found</h1></body></html>';
    exit;
}

// ========== PROTECTED DIRECTORIES ==========
if (preg_match('/^(backend|assets|config|Doc|tools)(\/|$)/i', $requestUri)) {
    if (strpos($requestUri, 'config') === 0) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }
    exit;
}

// ========== PAGE ROUTING ==========
$page = $requestUri ?: 'index';

if (!preg_match('|^[a-zA-Z0-9_/.:-]*$|', $page)) {
    http_response_code(400);
    echo 'Bad Request';
    exit;
}

$page = rtrim($page, '/');

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

    if (is_dir($dir . $page) && file_exists($dir . $page . '/index.html')) {
        $filepath = $dir . $page . '/index.html';
        break;
    }
}

if (!$filepath) {
    foreach ($baseDirs as $dir) {
        if (file_exists($dir . 'index.html')) {
            $filepath = $dir . 'index.html';
            break;
        }
    }
}

if ($filepath && file_exists($filepath)) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Powered-By: Glo-CED Router');
    readfile($filepath);
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body>Page not found</body></html>';
}
?>


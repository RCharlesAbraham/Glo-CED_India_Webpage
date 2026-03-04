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
        ob_start();
        include $file;
        $adminOutput = ob_get_clean();

        // Compute base URL (same logic used for normal pages)
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseUrl = '/';
        if (strpos($script_name, '/Glo-CED_India_Webpage') !== false) {
            $baseUrl = '/Glo-CED_India_Webpage/';
        } elseif (strpos($script_name, '/STP/') !== false) {
            $baseUrl = '/STP/';
        } elseif (strpos($script_name, '/glo.tekquora.com') !== false) {
            $baseUrl = '/glo.tekquora.com/';
        }

        if (!preg_match('/<base[^>]*>/i', $adminOutput)) {
            $adminOutput = preg_replace('/<head(\s[^>]*)?>/i', "$0\n<base href=\"{$baseUrl}\">\n", $adminOutput, 1);
        }

        header('Content-Type: text/html; charset=UTF-8');
        header('X-Powered-By: Glo-CED Router');
        echo $adminOutput;
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

// Serve existing files directly (static assets) or execute PHP files when requested.
// This prevents the front-controller from returning HTML for asset requests (e.g. tailwind.css, images).
$fsPath = __DIR__ . '/' . ltrim($requestUri, '/');
if ($requestUri && file_exists($fsPath) && is_file($fsPath)) {
    $ext = strtolower(pathinfo($fsPath, PATHINFO_EXTENSION));
    $staticExts = ['css','js','png','jpg','jpeg','gif','svg','webp','woff2','woff','ttf','eot','otf','ico','map','json','pdf','txt','mp4','webmanifest'];
    if (in_array($ext, $staticExts)) {
        $mimeMap = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'otf' => 'font/otf',
            'ico' => 'image/x-icon',
            'map' => 'application/json',
            'json' => 'application/json',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'webmanifest' => 'application/manifest+json'
        ];
        $mime = $mimeMap[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($fsPath) : 'application/octet-stream');
        header('Content-Type: ' . $mime);
        readfile($fsPath);
        exit;
    }
    if ($ext === 'php') {
        chdir(dirname($fsPath));
        include $fsPath;
        exit;
    }
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

    // Compute a base URL so that relative asset links in static HTML resolve
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseUrl = '/';
    if (strpos($script_name, '/Glo-CED_India_Webpage') !== false) {
        $baseUrl = '/Glo-CED_India_Webpage/';
    } elseif (strpos($script_name, '/STP/') !== false) {
        $baseUrl = '/STP/';
    } elseif (strpos($script_name, '/glo.tekquora.com') !== false) {
        $baseUrl = '/glo.tekquora.com/';
    }

    $content = @file_get_contents($filepath);
    if ($content !== false) {
        // Inject <base> inside the first <head> tag if not already present
        if (!preg_match('/<base[^>]*>/i', $content)) {
            $content = preg_replace('/<head(\s[^>]*)?>/i', "$0\n<base href=\"{$baseUrl}\">\n", $content, 1);
        }
        echo $content;
        exit;
    }

    // Fallback: stream file directly
    readfile($filepath);
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body>Page not found</body></html>';
}
?>


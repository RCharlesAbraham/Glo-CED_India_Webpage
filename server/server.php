<?php
/**
 * server.php
 * Backend entry point.
 * Loads environment variables and bootstraps the application.
 */

require_once __DIR__ . '/utils/helpers.php';

// Load .env from project root
loadEnv(__DIR__ . '/../.env');

// Router for PHP built-in server.
// If a requested file exists on disk, let the server serve it directly.
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$projectRoot = realpath(__DIR__ . '/../');

// If the request maps to an existing file, let the web server handle it
$fsPath = $projectRoot . $requestUri;
if ($requestUri !== '/' && file_exists($fsPath) && is_file($fsPath)) {
    return false; // serve the requested resource as static file
}

// Dispatch API routes under /server/routes/<name>.php
if (preg_match('#^/server/routes/([a-zA-Z0-9_\-]+)(?:\.php)?$#', $requestUri, $m)) {
    $routeName = $m[1];
    $routeFile = __DIR__ . '/routes/' . $routeName . '.php';
    if (file_exists($routeFile)) {
        require $routeFile;
        exit;
    }
}

// Serve the frontend index for the root path
if ($requestUri === '/' || $requestUri === '/index.php') {
    $index = __DIR__ . '/../client/src/pages/index.html';
    if (file_exists($index)) {
        header('Content-Type: text/html');
        readfile($index);
        exit;
    }
}

// Serve client files when requested as /client/...
if (str_starts_with($requestUri, '/client/')) {
    // Attempt to serve the exact file under project root (e.g. /client/src/pages/index.html)
    $file = __DIR__ . '/../' . ltrim($requestUri, '/');
    if (file_exists($file) && is_file($file) && is_readable($file)) {
        $mime = @mime_content_type($file) ?: 'text/plain';
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }

    // Map requests like /client/src/assets/... or /client/assets/... -> projectRoot/assets/...
    if (preg_match('#^/client(?:/src)?/assets/(.*)$#', $requestUri, $m)) {
        $file = __DIR__ . '/../assets/' . $m[1];
        if (file_exists($file) && is_file($file) && is_readable($file)) {
            $mime = @mime_content_type($file) ?: 'application/octet-stream';
            header('Content-Type: ' . $mime);
            readfile($file);
            exit;
        }
    }

    // Map requests to /client/public/... -> client/public/...
    if (preg_match('#^/client/public/(.*)$#', $requestUri, $m)) {
        $file = __DIR__ . '/../client/public/' . $m[1];
        if (file_exists($file) && is_file($file) && is_readable($file)) {
            $mime = @mime_content_type($file) ?: 'application/octet-stream';
            header('Content-Type: ' . $mime);
            readfile($file);
            exit;
        }
    }
}

// Serve public assets requested as /public/... -> client/public
if (str_starts_with($requestUri, '/public/')) {
    $file = __DIR__ . '/../client' . $requestUri; // /public/css/style.css -> client/public/css/style.css
    if (file_exists($file) && is_file($file) && is_readable($file)) {
        $mime = @mime_content_type($file) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
}

// Serve project-root assets requested as /assets/... -> projectRoot/assets
if (str_starts_with($requestUri, '/assets/')) {
    $file = __DIR__ . '/../' . ltrim($requestUri, '/'); // maps directly to projectRoot/assets/...
    if (file_exists($file) && is_file($file) && is_readable($file)) {
        $mime = @mime_content_type($file) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
}

// Not found
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error' => 'Route not found']);

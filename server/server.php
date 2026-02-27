<?php
/**
 * server.php
 * Backend entry point.
 * Loads environment variables and bootstraps the application.
 */

require_once __DIR__ . '/utils/helpers.php';

// Load .env from project root
loadEnv(__DIR__ . '/../.env');

// Basic routing — delegate to specific route files based on the URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim(str_replace('/server', '', $path), '/');

switch ($path) {
    case '/routes/auth':
        require __DIR__ . '/routes/auth.php';
        break;

    case '/routes/contact':
        require __DIR__ . '/routes/contact.php';
        break;

    case '/routes/users':
        require __DIR__ . '/routes/users.php';
        break;

    case '/routes/submissions':
        require __DIR__ . '/routes/submissions.php';
        break;

    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found']);
}

<?php
/**
 * users.php
 * Route: /server/routes/users.php
 * Admin-only endpoint for user management.
 */

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/AdminController.php';

header('Content-Type: application/json');

// Only admins can access this route
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

switch ($method) {
    case 'GET':
        echo json_encode(AdminController::getAllUsers());
        break;

    case 'DELETE':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID required']);
            exit;
        }
        $deleted = AdminController::deleteUser($id);
        echo json_encode(['success' => $deleted]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

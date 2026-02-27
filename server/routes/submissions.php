<?php
/**
 * submissions.php
 * Route: /server/routes/submissions.php
 * Admin-only endpoint for viewing/deleting contact submissions.
 */

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/AdminController.php';

header('Content-Type: application/json');

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo json_encode(AdminController::getAllSubmissions());
        break;

    case 'DELETE':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Submission ID required']);
            exit;
        }
        $deleted = AdminController::deleteSubmission($id);
        echo json_encode(['success' => $deleted]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

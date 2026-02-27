<?php
/**
 * auth.php
 * Route: /server/routes/auth.php
 * Handles login and logout actions.
 */

require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        $result = AuthController::login(
            $_POST['username'] ?? '',
            $_POST['password'] ?? ''
        );
        if ($result['success']) {
            $redirect = ($result['role'] === 'admin')
                ? '/client/src/admin/dashboard.html'
                : '/client/src/pages/index.html';
            header('Location: ' . $redirect);
            exit;
        }
        echo json_encode($result);
        break;

    case 'logout':
        AuthController::logout();
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}

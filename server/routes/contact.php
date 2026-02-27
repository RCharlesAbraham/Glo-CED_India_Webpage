<?php
/**
 * contact.php
 * Route: /server/routes/contact.php
 * Handles public contact form POST.
 */

require_once __DIR__ . '/../controllers/ContactController.php';
require_once __DIR__ . '/../middleware/cors.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$result = ContactController::submit($_POST);
echo json_encode($result);

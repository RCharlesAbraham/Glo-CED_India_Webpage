<?php
// Simple auth status endpoint used by the public pages to show/hide admin links
session_start();
header('Content-Type: application/json');

$isAdmin = false;
$user = null;
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $isAdmin = true;
    $user = $_SESSION['admin_user'] ?? null;
}

echo json_encode(['admin' => $isAdmin, 'user' => $user]);

?>

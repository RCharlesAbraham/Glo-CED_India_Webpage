<?php
// Returns JSON {"admin": true|false} based on session
session_start();
header('Content-Type: application/json');
$admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
echo json_encode(['admin' => $admin]);
?>
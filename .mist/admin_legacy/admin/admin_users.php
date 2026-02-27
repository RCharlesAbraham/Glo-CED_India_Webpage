<?php
/**
 * Admin users management - fetches from database admins table
 *
 * Users are stored in the `admins` table with hashed passwords.
 * Use admin_manage_users.php to add/edit/delete admin accounts.
 */

function get_admin_user($username) {
    global $conn;
    $query = "SELECT username, password_hash FROM admins WHERE username = ? AND is_active = 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) return null;
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function verify_admin($username, $password) {
    $user = get_admin_user($username);
    if (!$user) {
        return false;
    }
    return password_verify($password, $user['password_hash']);
}

?>
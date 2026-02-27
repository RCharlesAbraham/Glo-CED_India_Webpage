<?php
/**
 * AuthController.php
 * Handles login, logout, and session management.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/helpers.php';

class AuthController
{
    /**
     * Handle admin/user login.
     */
    public static function login(string $username, string $password): array
    {
        $user = User::findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        session_start();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];

        return ['success' => true, 'role' => $user['role']];
    }

    /**
     * Destroy session and log out.
     */
    public static function logout(): void
    {
        session_start();
        session_destroy();
        header('Location: /client/src/admin/login.html');
        exit;
    }

    /**
     * Check if user is logged in and has required role.
     */
    public static function requireAuth(string $role = 'admin'): void
    {
        session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
            http_response_code(401);
            header('Location: /client/src/admin/login.html');
            exit;
        }
    }
}

<?php
/**
 * AdminController.php
 * Handles admin dashboard data, user management, and submissions.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Submission.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class AdminController
{
    /**
     * Return dashboard stats.
     */
    public static function getDashboardStats(): array
    {
        return [
            'total_users'       => User::count(),
            'total_submissions' => Submission::count(),
        ];
    }

    /**
     * Return all users.
     */
    public static function getAllUsers(): array
    {
        return User::findAll();
    }

    /**
     * Return all submissions.
     */
    public static function getAllSubmissions(): array
    {
        return Submission::findAll();
    }

    /**
     * Delete a user by ID.
     */
    public static function deleteUser(int $id): bool
    {
        return User::delete($id);
    }

    /**
     * Delete a submission by ID.
     */
    public static function deleteSubmission(int $id): bool
    {
        return Submission::delete($id);
    }
}

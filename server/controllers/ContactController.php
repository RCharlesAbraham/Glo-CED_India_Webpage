<?php
/**
 * ContactController.php
 * Handles public contact form submissions.
 */

require_once __DIR__ . '/../models/Submission.php';
require_once __DIR__ . '/../utils/helpers.php';

class ContactController
{
    /**
     * Process and store a contact form submission.
     */
    public static function submit(array $data): array
    {
        $name    = sanitize($data['name']    ?? '');
        $email   = sanitize($data['email']   ?? '');
        $phone   = sanitize($data['phone']   ?? '');
        $message = sanitize($data['message'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($message)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }

        try {
            $saved = Submission::create([
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone,
                'message'    => $message,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable $e) {
            error_log('Contact submit failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Server error while saving your message.'];
        }

        if ($saved) {
            return ['success' => true, 'message' => 'Your message has been sent!'];
        }

        return ['success' => false, 'message' => 'Failed to save. Please try again.'];
    }
}

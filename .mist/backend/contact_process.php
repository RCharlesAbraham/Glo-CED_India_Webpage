<?php
/**
 * Contact Form Processing
 * Charity Trust Project
 * 
 * Handles contact form submissions with validation,
 * sanitization, and database storage
 */

// Start session for CSRF protection
session_start();

// Include database configuration and security functions
require_once '../config/db_config.php';

// Set JSON response header
header('Content-Type: application/json');

// Initialize response array
$response = array(
    'success' => false,
    'message' => 'An error occurred. Please try again.'
);

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

try {
    // Sanitize and validate inputs
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
    
    // Validation
    $errors = array();
    
    // Validate Name
    if (!validate_required($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) < 3 || strlen($name) > 100) {
        $errors[] = 'Name must be between 3 and 100 characters';
    }
    
    // Validate Email
    if (!validate_required($email)) {
        $errors[] = 'Email is required';
    } elseif (!validate_email($email)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Validate Phone
    if (!validate_required($phone)) {
        $errors[] = 'Phone number is required';
    } elseif (!validate_phone($phone)) {
        $errors[] = 'Please enter a valid phone number';
    }
    
    // Validate Message
    if (!validate_required($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) < 10 || strlen($message) > 5000) {
        $errors[] = 'Message must be between 10 and 5000 characters';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        $response['message'] = implode(', ', $errors);
        echo json_encode($response);
        exit;
    }
    
    // Get user IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Prepare and execute database query
    $query = "INSERT INTO contact_submissions (name, email, phone, message, ip_address, submitted_at, status) 
              VALUES (?, ?, ?, ?, ?, NOW(), 'new')";
    
    $stmt = prepare_query($conn, $query, array($name, $email, $phone, $message, $ip_address));
    
    if ($stmt === false) {
        log_error("Database error: " . $conn->error);
        $response['message'] = 'Database error occurred. Please try again later.';
        echo json_encode($response);
        exit;
    }
    
    // Execute statement
    if (!$stmt->execute()) {
        log_error("Query execution error: " . $stmt->error);
        $response['message'] = 'Failed to process your request. Please try again.';
        echo json_encode($response);
        $stmt->close();
        exit;
    }
    
    $stmt->close();
    
    // Send confirmation email to user
    $to = $email;
    $subject = 'We Received Your Message - Charity Trust';
    $email_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background: #c6a16e; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background: #f9f9f9; padding: 10px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"header\">
                <h2>Thank You for Contacting Us!</h2>
            </div>
            <div class=\"content\">
                <p>Dear " . htmlspecialchars($name) . ",</p>
                <p>Thank you for reaching out to Charity Trust. We have received your message and will get back to you as soon as possible.</p>
                <h3>Your Message Summary:</h3>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                <p>We appreciate your interest and will contact you shortly.</p>
                <p>Best regards,<br>Charity Trust Team</p>
            </div>
            <div class=\"footer\">
                <p>&copy; " . date('Y') . " Charity Trust. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Try to send email (non-critical, continue even if it fails)
    @send_email($to, $subject, $email_body);
    
    // Send notification email to admin
    $admin_email = 'admin@charitytrust.com'; // Change this to your admin email
    $admin_subject = 'New Contact Form Submission';
    $admin_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background: #c6a16e; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f9f9f9; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"header\">
                <h2>New Contact Form Submission</h2>
            </div>
            <div class=\"content\">
                <p>You have received a new contact form submission. Details are below:</p>
                <table>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td>" . htmlspecialchars($name) . "</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>" . htmlspecialchars($email) . "</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>" . htmlspecialchars($phone) . "</td>
                    </tr>
                    <tr>
                        <td>Message</td>
                        <td>" . nl2br(htmlspecialchars($message)) . "</td>
                    </tr>
                    <tr>
                        <td>IP Address</td>
                        <td>" . htmlspecialchars($ip_address) . "</td>
                    </tr>
                    <tr>
                        <td>Submitted At</td>
                        <td>" . date('Y-m-d H:i:s') . "</td>
                    </tr>
                </table>
                <p><a href=\"http://yourdomain.com/admin/submissions.php\">View in Admin Panel</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Try to send admin notification
    @send_email($admin_email, $admin_subject, $admin_body);
    
    // Set success response
    $response['success'] = true;
    $response['message'] = 'Thank you! Your message has been sent successfully. We will get back to you soon.';
    
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    $response['message'] = 'An error occurred. Please try again later.';
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}

// Return JSON response
echo json_encode($response);
exit;

?>
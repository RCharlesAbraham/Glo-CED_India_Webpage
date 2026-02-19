<?php
/**
 * Database Configuration and Security Functions
 * Charity Trust Project
 * 
 * Configure your database connection and security settings here
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================

// Database connection parameters
define('DB_HOST', 'localhost');      // Your database host
define('DB_USER', 'root');           // Your database username
define('DB_PASS', '');               // Your database password
define('DB_NAME', 'charity_trust');  // Your database name

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Sanitize input to prevent SQL injection
 * 
 * @param string $input The input string to sanitize
 * @return string Sanitized input
 */
function sanitize_input($input) {
    // Trim whitespace
    $input = trim($input);
    
    // Remove slashes
    $input = stripslashes($input);
    
    // HTML Entity Encode
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    return $input;
}

/**
 * Validate email format
 * 
 * @param string $email The email to validate
 * @return bool True if valid, false otherwise
 */
function validate_email($email) {
    // Filter var with VALIDATE_EMAIL filter
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return false;
}

/**
 * Validate phone number (basic validation)
 * 
 * @param string $phone The phone number to validate
 * @return bool True if valid, false otherwise
 */
function validate_phone($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/\D/', '', $phone);
    
    // Check if length is between 10 and 15 digits (international standard)
    if (strlen($phone) >= 10 && strlen($phone) <= 15) {
        return true;
    }
    return false;
}

/**
 * Validate required fields
 * 
 * @param string $field The field value to validate
 * @return bool True if not empty, false otherwise
 */
function validate_required($field) {
    if (empty($field) || trim($field) === '') {
        return false;
    }
    return true;
}

/**
 * Escape string for database use
 * 
 * @param mysqli $conn Database connection
 * @param string $string The string to escape
 * @return string Escaped string
 */
function escape_string($conn, $string) {
    return $conn->real_escape_string($string);
}

/**
 * Prepare SQL statement for database query
 * 
 * @param mysqli $conn Database connection
 * @param string $query The SQL query with placeholders
 * @param array $params The parameters to bind
 * @return mysqli_stmt|false The prepared statement or false on error
 */
function prepare_query($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        return false;
    }
    
    if (!empty($params)) {
        // Build type string
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
    }
    
    return $stmt;
}

/**
 * Log error to file
 * 
 * @param string $error The error message to log
 * @return void
 */
function log_error($error) {
    $log_file = __DIR__ . '/error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] " . $error . "\n";
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Generate CSRF token
 * 
 * @return string The generated token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Send email notification
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param string $from Sender email
 * @return bool True if sent successfully, false otherwise
 */
function send_email($to, $subject, $message, $from = 'noreply@charitytrust.com') {
    // Headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    
    // Validate email
    if (!validate_email($to)) {
        return false;
    }
    
    // Send email
    return mail($to, $subject, $message, $headers);
}

?>

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

// Try to load .env from project root if present.
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
    $envLines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($envLines)) {
        foreach ($envLines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}

// Load an environment value with fallback support.
function env_value($key, $default = '') {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    return $default;
}

function env_first(array $keys, $default = '') {
    foreach ($keys as $key) {
        $value = env_value($key, '');
        if ($value !== '') {
            return $value;
        }
    }
    return $default;
}

// Database connection parameters (ENV first, then hosting defaults)
$dbHost = trim(env_first(['DB_HOST', 'MYSQL_HOST'], '127.0.0.1'));
$dbUser = trim(env_first(['DB_USER', 'DB_USERNAME', 'MYSQL_USER'], 'glodblive'));
$dbPass = env_first(['DB_PASS', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQL_PASS'], 'Q34kE8xTU0wVrbD6J9Mt');
$dbName = trim(env_first(['DB_NAME', 'DB_DATABASE', 'MYSQL_DATABASE'], 'glodblive'));
$dbPort = (int) env_first(['DB_PORT', 'MYSQL_PORT'], '3306');

if (!defined('DB_HOST')) define('DB_HOST', $dbHost);
if (!defined('DB_USER')) define('DB_USER', $dbUser);
if (!defined('DB_PASS')) define('DB_PASS', $dbPass);
if (!defined('DB_NAME')) define('DB_NAME', $dbName);

// Try preferred DB name first, then common fallback names used in this project.
$candidateDbNames = array_values(array_unique(array_filter([
    DB_NAME,
    'charity_trust',
    'glo_ced_india'
])));

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, null, $dbPort);
if ($conn->connect_error) {
    error_log('Database host/user connection failed: ' . $conn->connect_error);
    http_response_code(500);
    die('Database connection error. Please contact administrator.');
}

$selectedDbName = null;
foreach ($candidateDbNames as $candidateDb) {
    if (@$conn->select_db($candidateDb)) {
        $selectedDbName = $candidateDb;
        break;
    }
}

if ($selectedDbName === null) {
    error_log('Database select failed for candidates: ' . implode(', ', $candidateDbNames));
    http_response_code(500);
    die('Database setup error. Please contact administrator.');
}

// Keep DB_NAME in sync with the selected database for pages that display it.
if ($selectedDbName !== DB_NAME) {
    if (defined('DB_NAME')) {
        // Cannot redefine constants; expose selected name via global for internal use if needed.
        $GLOBALS['ACTIVE_DB_NAME'] = $selectedDbName;
    }
}

// Set charset to UTF-8
$conn->set_charset('utf8');

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

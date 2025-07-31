<?php
/**
 * Core Functions for Aviation System - Fixed Version
 */

require_once __DIR__ . '/dbconfig.php';

// Error reporting for development
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/**
 * Improved sanitizeInput function
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}


/**
 * Enhanced showToast function with better styling
 */
function showToast($messages) {
    foreach ($messages as $msg) {
        if (!empty($msg)) {
            echo "
            <div class='position-fixed top-0 start-50 translate-middle-x p-3' style='z-index: 1050'>
                <div id='liveToast' class='toast show' role='alert' aria-live='assertive' aria-atomic='true'>
                    <div class='toast-header'>
                        <strong class='me-auto'>System Message</strong>
                        <button type='button' class='btn-close' data-bs-dismiss='toast' aria-label='Close'></button>
                    </div>
                    <div class='toast-body'>
                        $msg
                    </div>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const toast = bootstrap.Toast.getOrCreateInstance(document.getElementById('liveToast'));
                    toast.hide();
                }, 5000);
            </script>";
            break;
        }
    }
}

/**
 * Secure redirect function
 */
function redirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    exit();
}

/**
 * CSRF Protection Functions
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token'], $token) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Improved User Registration
 */
function register($firstName, $lastName, $email, $password, $phone = null, $address = null, $passport = null) {
    global $db;

    try {
        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            throw new Exception("All required fields must be filled");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate password strength
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }

        // Check if email already exists
        $stmt = $db->conn->prepare("SELECT user_id FROM users WHERE userEmail = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email address already registered");
        }

        // Generate activation token
        $tokenCode = bin2hex(random_bytes(32));
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userName = substr($firstName . $lastName, 0, 50); // Ensure fits in userName field

        // Prepare the insert query
        $stmt = $db->conn->prepare("
            INSERT INTO users (
                userName, 
                userPass, 
                userEmail, 
                first_name, 
                last_name, 
                phone, 
                address, 
                passport_number, 
                tokenCode,
                user_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'customer')
        ");

        // Execute with all parameters
        $success = $stmt->execute([
            $userName,
            $hashedPassword,
            $email,
            $firstName,
            $lastName,
            $phone,
            $address,
            $passport,
            $tokenCode
        ]);

        if (!$success) {
            $error = $stmt->errorInfo();
            throw new Exception("Database error: " . $error[2]);
        }

        return $tokenCode;
    } catch (PDOException $e) {
        error_log("Registration PDO Error: " . $e->getMessage());
        throw new Exception("Database error occurred during registration");
    }
}

/**
 * Enhanced Login Function
 */
function login($email, $password) {
    global $db;

    try {
        $stmt = $db->conn->prepare("
            SELECT 
                user_id, 
                userName, 
                userPass, 
                userEmail, 
                first_name, 
                last_name, 
                user_type 
            FROM users 
            WHERE userEmail = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return "notfound";
        }

        if (!password_verify($password, $user['userPass'])) {
            return "wrongpass";
        }

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['userName'];
        $_SESSION['email'] = $user['userEmail'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['user_type'] = $user['user_type'];

        // Update last login (you might want to add this field to your table)
        $update = $db->conn->prepare("UPDATE users SET updated_at = NOW() WHERE user_id = ?");
        $update->execute([$user['user_id']]);

        return "success";
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        return "dberror";
    }
}

/**
 * Verify user email
 */
function verifyEmail($token) {
    global $db;

    try {
        // Find user by token
        $stmt = $db->conn->prepare("
            SELECT user_id FROM users 
            WHERE tokenCode = ? 
            AND email_verified_at IS NULL
        ");
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() === 0) {
            return "invalid";
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mark as verified
        $update = $db->conn->prepare("
            UPDATE users 
            SET 
                email_verified_at = NOW(),
                is_active = 1,
                tokenCode = ''
            WHERE user_id = ?
        ");
        $update->execute([$user['user_id']]);

        return "success";
    } catch (PDOException $e) {
        error_log("Verification Error: " . $e->getMessage());
        return "error";
    }
}

function initiatePasswordReset($email) {
    global $db;

    try {
        // Check if user exists
        $stmt = $db->conn->prepare("SELECT user_id FROM users WHERE userEmail = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() === 0) {
            return true; // Don't reveal if user exists
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Generate reset token (expires in 1 hour)
        $resetToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store token
        $update = $db->conn->prepare("
            UPDATE users 
            SET 
                tokenCode = ?,
                token_expires_at = ?
            WHERE user_id = ?
        ");
        $update->execute([$resetToken, $expiresAt, $user['user_id']]);

        // Send reset email
        $resetUrl = "https://yourdomain.com/reset-password.php?token=" . urlencode($resetToken);
        
        $emailBody = "
            <h2>Password Reset Request</h2>
            <p>We received a request to reset your password. Click the link below to proceed:</p>
            <p><a href='$resetUrl'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";

        return sendMail($email, $emailBody, "Password Reset Instructions");
    } catch (PDOException $e) {
        error_log("Password Reset Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Secure Email Function
 */
function sendMail($email, $message, $subject) {
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';
    require_once __DIR__ . '/PHPMailer/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = ''; //email
		$mail->Password = ''; // App password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('', 'Xeteviation System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has admin privileges
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Output format (default: 'F j, Y')
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Format datetime for display
 * @param string $datetime Datetime string
 * @param string $format Output format (default: 'F j, Y g:i a')
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = 'F j, Y g:i a') {
    $dt = new DateTime($datetime);
    return $dt->format($format);
}
// Function to calculate flight duration in minutes
function calculateFlightDuration($departure, $arrival) {
    $departureTime = new DateTime($departure);
    $arrivalTime = new DateTime($arrival);
    $interval = $departureTime->diff($arrivalTime);
    return ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
}

// Function to format flight duration
function formatFlightDuration($minutes) {
    if (!is_numeric($minutes) || $minutes < 0) {
        return "Invalid duration";
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf("%dh %02dm", $hours, $mins);
}



/**
 * Format flight duration (minutes to Hh Mm)
 * @param int $minutes Duration in minutes
 * @return string Formatted duration
 */


/**
 * Generate random booking reference
 * @return string 6-character alphanumeric reference
 */
function generateBookingReference() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = '';
    for ($i = 0; $i < 6; $i++) {
        $result .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $result;
}

/**
 * Get airport name by code
 * @param string $code Airport code
 * @return string Airport name or code if not found
 */
function getAirportName($code) {
    global $db;
    
    $stmt = $db->prepare("SELECT name FROM airports WHERE code = ?");
    $stmt->execute([$code]);
    $result = $stmt->fetch();
    
    return $result ? $result['name'] : $code;
}

/**
 * Get flight status badge HTML
 * @param string $status Flight status
 * @return string HTML badge
 */
function getFlightStatusBadge($status) {
    $statuses = [
        'scheduled' => ['class' => 'info', 'text' => 'Scheduled'],
        'delayed' => ['class' => 'warning', 'text' => 'Delayed'],
        'departed' => ['class' => 'primary', 'text' => 'Departed'],
        'arrived' => ['class' => 'success', 'text' => 'Arrived'],
        'cancelled' => ['class' => 'danger', 'text' => 'Cancelled']
    ];
    
    if (array_key_exists($status, $statuses)) {
        $info = $statuses[$status];
        return '<span class="badge badge-'.$info['class'].'">'.$info['text'].'</span>';
    }
    
    return '<span class="badge badge-secondary">'.$status.'</span>';
}

/**
 * Send notification to user
 * @param int $userId User ID
 * @param string $title Notification title
 * @param string $message Notification message
 * @return bool True on success
 */
function sendNotification($userId, $title, $message) {
    global $db;
    
    try {
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$userId, $title, $message]);
    } catch (PDOException $e) {
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's unread notifications count
 * @param int $userId User ID
 * @return int Number of unread notifications
 */
function getUnreadNotificationsCount($userId) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return $result ? (int)$result['count'] : 0;
}

/**
 * Log system events
 * @param string $message Log message
 * @param string $level Log level (info, warning, error)
 * @param array $context Additional context data
 */
function logEvent($message, $level = 'info', $context = []) {
    $logEntry = sprintf(
        "[%s] %s: %s %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $message,
        !empty($context) ? json_encode($context) : ''
    );
    
    // file_put_contents(__DIR__ . '/../logs/system.log', $logEntry, FILE_APPEND);
}

/**
 * Handle exceptions uniformly
 * @param Exception $e Exception object
 * @param bool $logError Whether to log the error (default: true)
 */
function handleException($e, $logError = true) {
    if ($logError) {
        logEvent($e->getMessage(), 'error', [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    // In production, show user-friendly message
    if (!defined('ENVIRONMENT')) {
        define('ENVIRONMENT', 'development'); // Change 'development' to 'production' as needed
    }
    if (ENVIRONMENT === 'production') {
        die("An unexpected error occurred. Our team has been notified.");
    } else {
        // In development, show detailed error
        die("<pre>Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>");
    }
}

// Set exception handler
set_exception_handler('handleException');

/**
 * Get setting value from database
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value
 */
function getSetting($key, $default = null) {
    global $db;
    
    static $settings = null;
    
    if ($settings === null) {
        $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Validate and format phone number
 * @param string $phone Phone number
 * @return string|bool Formatted number or false if invalid
 */
function validatePhoneNumber($phone) {
    // Remove all non-digit characters
    $cleaned = preg_replace('/[^0-9]/', '', $phone);
    
    // Validate based on international standards (basic validation)
    if (strlen($cleaned) < 10 || strlen($cleaned) > 15) {
        return false;
    }
    
    // Format with international code if not present
    if (strlen($cleaned) === 10 && substr($cleaned, 0, 1) !== '1') {
        $cleaned = '1' . $cleaned; // Assume US/Canada
    }
    
    return '+' . $cleaned;
}

/**
 * Generate pagination links
 * @param int $totalItems Total number of items
 * @param int $itemsPerPage Items per page
 * @param int $currentPage Current page number
 * @param string $baseUrl Base URL for links
 * @return string HTML pagination links
 */
function paginate($totalItems, $itemsPerPage, $currentPage, $baseUrl) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    if ($totalPages <= 1) return '';
    
    $queryChar = strpos($baseUrl, '?') === false ? '?' : '&';
    $html = '<ul class="pagination">';
    
    // Previous link
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="'.$baseUrl.$queryChar.'page='.($currentPage-1).'">Previous</a></li>';
    }
    
    // Page links
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i == $currentPage ? ' active' : '';
        $html .= '<li class="page-item'.$active.'"><a class="page-link" href="'.$baseUrl.$queryChar.'page='.$i.'">'.$i.'</a></li>';
    }
    
    // Next link
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="'.$baseUrl.$queryChar.'page='.($currentPage+1).'">Next</a></li>';
    }
    
    $html .= '</ul>';
    return $html;
}
?>
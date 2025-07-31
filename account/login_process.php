<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}



// Get form data
$email = trim($_POST['email']);
$password = $_POST['password'];
$remember = isset($_POST['remember']);

// Validate inputs
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Email and password are required";
    header("Location: ../index.php?show_login=1");
    exit();
}

try {
    $db = DBConfig::getInstance()->getConnection();
    
    // Prepare SQL to get user data
    $stmt = $db->prepare("SELECT * FROM users WHERE userEmail = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['login_error'] = "Invalid email or password";
        header("Location: ../index.php?show_login=1");
        exit();
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify password
    if (!password_verify($password, $user['userPass'])) {
        $_SESSION['login_error'] = "Invalid email or password";
        header("Location: ../index.php?show_login=1");
        exit();
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['userName'] = $user['userName'];
    $_SESSION['userEmail'] = $user['userEmail'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['phone'] = $user['phone'];
    $_SESSION['user_type'] = $user['user_type'];
    
    // Update last_login timestamp
    try {
        $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :user_id");
        $updateStmt->bindParam(':user_id', $user['user_id']);
        $updateStmt->execute();
    } catch (PDOException $e) {
        error_log("Failed to update last_login: " . $e->getMessage());
        // Don't fail the login if this fails, just log it
    }
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 60 * 60 * 24 * 30; // 30 days
        
        // Store token in database
        $stmt = $db->prepare("UPDATE users SET tokenCode = :token WHERE user_id = :user_id");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->execute();
        
        // Set cookie
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
    }
    
    // Redirect to appropriate page
    if (isset($_SESSION['redirect_url'])) {
        $redirect = $_SESSION['redirect_url'];
        unset($_SESSION['redirect_url']);
        header("Location: $redirect");
    } else {
        header("Location: ../index.php");
    }
    exit();
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['login_error'] = "Database error occurred. Please try again later.";
    header("Location: ../index.php?show_login=1");
    exit();
}
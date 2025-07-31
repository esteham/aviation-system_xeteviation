<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    try {
        // CSRF protection
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception("Security token validation failed");
        }

        // Sanitize inputs
        $firstName = sanitizeInput($_POST['firstName'] ?? '');
        $lastName = sanitizeInput($_POST['lastName'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $termsAgree = isset($_POST['termsAgree']);

        // Validate inputs
        $errors = [];
        if (empty($firstName)) $errors[] = "First name is required";
        if (empty($lastName)) $errors[] = "Last name is required";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
        if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";
        if ($password !== $confirmPassword) $errors[] = "Passwords don't match";
        if (!$termsAgree) $errors[] = "You must agree to the terms";
        
        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        // Check if email already exists
        $db = DBConfig::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT user_id FROM users WHERE userEmail = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already registered");
        }

        // Generate OTP (6-digit code)
        $otp = rand(100000, 999999);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Store OTP and user data in session for verification
        $_SESSION['otp_verification'] = [
            'email' => $email,
            'otp' => $otp,
            'hashed_password' => $hashedPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'expires' => time() + (10 * 60) // OTP valid for 10 minutes
        ];

        // Send OTP email
        $subject = "Your Verification OTP";
        $message = "
            <div style='font-family:Segoe UI,Arial,sans-serif;max-width:480px;margin:auto;background:#f9f9fb;padding:32px 24px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>
            <div style='text-align:center;margin-bottom:24px;'>
                <img src='https://img.icons8.com/color/96/000000/airplane-take-off.png' alt='SkyWings' style='width:64px;height:64px;'>
            </div>
            <h2 style='color:#2d3748;margin-bottom:8px;text-align:center;'>Welcome, $firstName!</h2>
            <p style='color:#4a5568;font-size:16px;text-align:center;margin-bottom:24px;'>
                To complete your registration with <strong>SkyWings</strong>, please verify your email address using the code below:
            </p>
            <div style='background:#edf2f7;color:#2b6cb0;font-size:28px;font-weight:bold;letter-spacing:4px;text-align:center;padding:16px 0;border-radius:8px;margin-bottom:24px;'>
                $otp
            </div>
            <p style='color:#718096;font-size:14px;text-align:center;margin-bottom:16px;'>
                This code is valid for <strong>10 minutes</strong>.
            </p>
            <p style='color:#a0aec0;font-size:13px;text-align:center;'>
                If you did not request this, you can safely ignore this email.<br>
                &mdash; The SkyWings Team
            </p>
            </div>
        ";

        if (sendMail($email, $message, $subject)) {
            $_SESSION['register_success'] = "An OTP has been sent to your email. Please verify to complete registration.";
            header("Location: verify_otp.php");
            exit();
        } else {
            throw new Exception("Failed to send OTP. Please try again.");
        }

    } catch (Exception $e) {
        $_SESSION['register_errors'] = [$e->getMessage()];
        $_SESSION['old_input'] = $_POST;
        header("Location: ../index.php#registerModal");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
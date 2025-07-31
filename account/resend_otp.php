<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

if (!isset($_SESSION['otp_verification'])) {
    header("Location: index.php");
    exit();
}

// Generate new OTP
$newOtp = rand(100000, 999999);
$_SESSION['otp_verification']['otp'] = $newOtp;
$_SESSION['otp_verification']['expires'] = time() + (10 * 60); // Reset expiration

// Send new OTP
$email = $_SESSION['otp_verification']['email'];
$firstName = $_SESSION['otp_verification']['first_name'];

$subject = "Your New Verification OTP";
$message = "
    <h2>New OTP Request</h2>
    <p>Your new verification code is: <strong>$newOtp</strong></p>
    <p>This code will expire in 10 minutes.</p>
";

if (sendMail($email, $message, $subject)) {
    $_SESSION['otp_message'] = "A new OTP has been sent to your email.";
} else {
    $_SESSION['otp_error'] = "Failed to resend OTP. Please try again.";
}

header("Location: verify_otp.php");
exit();
?>
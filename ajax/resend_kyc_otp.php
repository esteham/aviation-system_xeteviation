<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'errors' => ['You must be logged in to resend OTP']]);
    exit();
}

$response = ['success' => false, 'errors' => []];

try {
    // Check if KYC is already verified
    $stmt = $db->prepare("SELECT kyc_verified FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user && $user['kyc_verified']) {
        $response['errors'][] = "Your KYC is already verified";
        echo json_encode($response);
        exit();
    }
    
    // Generate new OTP
    $otpCode = rand(100000, 999999);
    $otpExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Update OTP in database
    $stmt = $db->prepare("UPDATE users SET 
                        kyc_otp = ?,
                        kyc_otp_expires = ?,
                        updated_at = NOW()
                        WHERE user_id = ?");
    
    $stmt->execute([$otpCode, $otpExpires, $_SESSION['user_id']]);
    
    // Send OTP to user's email
    $subject = "KYC Verification OTP (Resent)";
    $message = "Your new KYC verification OTP code is: $otpCode\n\nThis code will expire in 15 minutes.";
    sendMail($_SESSION['userEmail'], $subject, $message);

    
    $response['success'] = true;
    $response['message'] = "New OTP has been sent to your email address.";
} catch (PDOException $e) {
    $response['errors'][] = "Database error occurred";
}

echo json_encode($response);
?>
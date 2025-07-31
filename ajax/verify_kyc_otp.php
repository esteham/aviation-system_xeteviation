<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'errors' => ['You must be logged in to verify KYC']]);
    exit();
}

$response = ['success' => false, 'errors' => []];

$otpCode = trim($_POST['otp_code'] ?? '');

if (empty($otpCode)) {
    $response['errors'][] = "OTP code is required";
}

if (empty($response['errors'])) {
    try {
        // Verify OTP
        $stmt = $db->prepare("SELECT user_id, kyc_otp, kyc_otp_expires FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $response['errors'][] = "User not found";
        } elseif ($user['kyc_otp'] !== $otpCode) {
            $response['errors'][] = "Invalid OTP code";
        } elseif (strtotime($user['kyc_otp_expires']) < time()) {
            $response['errors'][] = "OTP code has expired";
        } else {
            // Mark KYC as verified
            $stmt = $db->prepare("UPDATE users SET 
                                kyc_verified = 1,
                                kyc_status = 'verified',
                                kyc_verified_at = NOW(),
                                updated_at = NOW()
                                WHERE user_id = ?");
            
            if ($stmt->execute([$_SESSION['user_id']])) {
                $_SESSION['kyc_verified'] = true;
                $response['success'] = true;
                $response['message'] = "KYC verified successfully!";
            } else {
                $response['errors'][] = "Failed to verify KYC";
            }
        }
    } catch (PDOException $e) {
        $response['errors'][] = "Database error occurred";
    }
}

echo json_encode($response);
?>
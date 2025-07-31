<?php
session_start();
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';

header('Content-Type: application/json');

try {
    $db = DBConfig::getInstance()->getConnection();
    
    // Validate input
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $isResend = isset($_POST['resend']);
    $requestId = isset($_POST['request_id']) ? $_POST['request_id'] : null;
    
    if (!$email) {
        throw new Exception("Invalid email address");
    }
    
    if (!in_array($paymentMethod, ['credit_card', 'paypal', 'bank_transfer'])) {
        throw new Exception("Invalid payment method");
    }
    
    // Generate 6-digit OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    if ($isResend && $requestId) {
        // Update existing OTP record
        $stmt = $db->prepare("UPDATE payment_otps SET otp = ?, expires_at = ? WHERE request_id = ?");
        $stmt->execute([$otp, $expiresAt, $requestId]);
    } else {
        // Create new OTP record
        $requestId = bin2hex(random_bytes(16));
        $stmt = $db->prepare("INSERT INTO payment_otps (request_id, email, payment_method, otp, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$requestId, $email, $paymentMethod, $otp, $expiresAt]);
    }
    
    // Send OTP email
    $subject = "Your OTP for Payment Verification";
    $message = "Your OTP for payment verification is: $otp\n\nThis OTP is valid for 15 minutes.";
    
    sendMail($email, $message, $subject);
    
    echo json_encode([
        'success' => true,
        'request_id' => $requestId,
        'message' => 'OTP sent successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'errors' => ['You must be logged in to submit KYC']]);
    exit();
}

$response = ['success' => false, 'errors' => []];

// Check if KYC is already verified
$stmt = $db->prepare("SELECT kyc_verified FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user && $user['kyc_verified']) {
    echo json_encode(['success' => false, 'errors' => ['Your KYC is already verified']]);
    exit();
}

// Validate inputs
$documentType = trim($_POST['document_type'] ?? '');
$documentNumber = trim($_POST['document_number'] ?? '');

if (empty($documentType)) {
    $response['errors'][] = "Document type is required";
}

if (empty($documentNumber)) {
    $response['errors'][] = "Document number is required";
}

// Handle file uploads
$uploadDir = __DIR__ . '/../uploads/kyc/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

$frontFile = $_FILES['document_front'] ?? null;
$backFile = $_FILES['document_back'] ?? null;
$selfieFile = $_FILES['selfie'] ?? null;

$frontPath = '';
$backPath = '';
$selfiePath = '';

if (!$frontFile || $frontFile['error'] !== UPLOAD_ERR_OK) {
    $response['errors'][] = "Document front is required";
} else {
    if (!in_array($frontFile['type'], $allowedTypes)) {
        $response['errors'][] = "Invalid file type for document front. Only JPG, PNG, or PDF allowed";
    } elseif ($frontFile['size'] > $maxFileSize) {
        $response['errors'][] = "Document front is too large. Max 5MB allowed";
    } else {
        $extension = pathinfo($frontFile['name'], PATHINFO_EXTENSION);
        $frontPath = 'kyc_' . $_SESSION['user_id'] . '_front_' . time() . '.' . $extension;
        move_uploaded_file($frontFile['tmp_name'], $uploadDir . $frontPath);
    }
}

if ($backFile && $backFile['error'] === UPLOAD_ERR_OK) {
    if (!in_array($backFile['type'], $allowedTypes)) {
        $response['errors'][] = "Invalid file type for document back. Only JPG, PNG, or PDF allowed";
    } elseif ($backFile['size'] > $maxFileSize) {
        $response['errors'][] = "Document back is too large. Max 5MB allowed";
    } else {
        $extension = pathinfo($backFile['name'], PATHINFO_EXTENSION);
        $backPath = 'kyc_' . $_SESSION['user_id'] . '_back_' . time() . '.' . $extension;
        move_uploaded_file($backFile['tmp_name'], $uploadDir . $backPath);
    }
}

if ($selfieFile && $selfieFile['error'] === UPLOAD_ERR_OK) {
    if (!in_array($selfieFile['type'], $allowedTypes)) {
        $response['errors'][] = "Invalid file type for selfie. Only JPG or PNG allowed";
    } elseif ($selfieFile['size'] > $maxFileSize) {
        $response['errors'][] = "Selfie is too large. Max 5MB allowed";
    } else {
        $extension = pathinfo($selfieFile['name'], PATHINFO_EXTENSION);
        $selfiePath = 'kyc_' . $_SESSION['user_id'] . '_selfie_' . time() . '.' . $extension;
        move_uploaded_file($selfieFile['tmp_name'], $uploadDir . $selfiePath);
    }
}

if (empty($response['errors'])) {
    try {
        // Generate OTP
        $otpCode = rand(100000, 999999);
        $otpExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Store KYC data in database (you might want a separate kyc_verifications table)
        $stmt = $db->prepare("UPDATE users SET 
                            kyc_document_type = ?,
                            kyc_document_number = ?,
                            kyc_document_front = ?,
                            kyc_document_back = ?,
                            kyc_selfie = ?,
                            kyc_otp = ?,
                            kyc_otp_expires = ?,
                            kyc_status = 'pending',
                            updated_at = NOW()
                            WHERE user_id = ?");
        
        $stmt->execute([
            $documentType,
            $documentNumber,
            $frontPath,
            $backPath,
            $selfiePath,
            $otpCode,
            $otpExpires,
            $_SESSION['user_id']
        ]);
        
        // Send OTP to user's email (implement your email sending function)
        $subject = "KYC Verification OTP";
        $message = "Your KYC verification OTP code is: $otpCode\n\nThis code will expire in 15 minutes.";
        sendMail($_SESSION['userEmail'], $subject, $message);
    
        $response['success'] = true;
        $response['message'] = "KYC documents submitted successfully. Please check your email for the verification OTP.";
    } catch (PDOException $e) {
        $response['errors'][] = "Database error occurred: " . $e->getMessage();
        
        // Clean up uploaded files if database operation failed
        if ($frontPath) unlink($uploadDir . $frontPath);
        if ($backPath) unlink($uploadDir . $backPath);
        if ($selfiePath) unlink($uploadDir . $selfiePath);
    }
}

echo json_encode($response);
?>
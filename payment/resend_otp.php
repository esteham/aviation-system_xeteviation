<?php
session_start();
require_once __DIR__ . '/config/functions.php';

if (!isset($_SESSION['booking_otp']) || !isset($_SESSION['booking_data'])) {
    echo json_encode(['success' => false, 'error' => 'Session expired']);
    exit();
}

// Resend the same OTP
$otp = $_SESSION['booking_otp'];
$to = $_SESSION['booking_data']['email'];
$subject = "Your Booking OTP";
$message = "Your OTP for flight booking is: $otp";
$headers = "From: no-reply@aviationsystem.com";

$sent = mail($to, $subject, $message, $headers);

echo json_encode(['success' => $sent]);
?>
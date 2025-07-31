<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'errors' => ['You must be logged in to update your profile']]);
    exit();
}

$response = ['success' => false, 'errors' => []];

// Validate inputs
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$passportNumber = trim($_POST['passport_number'] ?? '');

if (empty($firstName) || empty($lastName)) {
    $response['errors'][] = "First and last name are required";
}

if (empty($response['errors'])) {
    try {
        $stmt = $db->prepare("UPDATE users SET 
                            first_name = :firstName, 
                            last_name = :lastName, 
                            phone = :phone, 
                            address = :address, 
                            passport_number = :passportNumber,
                            updated_at = NOW()
                            WHERE user_id = :userId");
        
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':passportNumber', $passportNumber);
        $stmt->bindParam(':userId', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['phone'] = $phone;
            $_SESSION['address'] = $address;
            $_SESSION['passport_number'] = $passportNumber;
            
            $response['success'] = true;
            $response['message'] = "Profile updated successfully";
            $response['data'] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'address' => $address,
                'passport_number' => $passportNumber
            ];
        } else {
            $response['errors'][] = "Failed to update profile";
        }
    } catch (PDOException $e) {
        $response['errors'][] = "Database error occurred";
    }
}

echo json_encode($response);
?>
<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'errors' => ['You must be logged in to change your password']]);
    exit();
}

$response = ['success' => false, 'errors' => []];

$currentPassword = trim($_POST['current_password'] ?? '');
$newPassword = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

// Validate inputs
if (empty($currentPassword)) {
    $response['errors'][] = "Current password is required";
}

if (empty($newPassword)) {
    $response['errors'][] = "New password is required";
} elseif (strlen($newPassword) < 8) {
    $response['errors'][] = "New password must be at least 8 characters";
}

if ($newPassword !== $confirmPassword) {
    $response['errors'][] = "New passwords don't match";
}

if (empty($response['errors'])) {
    try {
        // Verify current password
        $stmt = $db->prepare("SELECT userPass FROM users WHERE user_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($currentPassword, $user['userPass'])) {
            // Update password
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET userPass = ?, updated_at = NOW() WHERE user_id = ?");
            
            if ($stmt->execute([$newHashedPassword, $_SESSION['user_id']])) {
                $response['success'] = true;
                $response['message'] = "Password changed successfully";
            } else {
                $response['errors'][] = "Failed to change password";
            }
        } else {
            $response['errors'][] = "Current password is incorrect";
        }
    } catch (PDOException $e) {
        $response['errors'][] = "Database error occurred";
    }
}

echo json_encode($response);
?>
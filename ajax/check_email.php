<?php
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    try {
        $db = DBConfig::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE userEmail = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        
        echo json_encode([
            'available' => $result['count'] == 0,
            'message' => $result['count'] > 0 ? 'Email is already registered' : 'Email is available'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'error' => true,
            'message' => 'Database error occurred'
        ]);
    }
} else {
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request'
    ]);
}
?>
<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

// Check if booking ID is provided
if (!isset($_GET['booking_id'])) {
    header("Location: bookings.php");
    exit();
}

//$booking_id = $_GET['booking_id'];
$db = DBConfig::getInstance()->getConnection();
// Handle actions
if (isset($_GET['toggle'])) {
    $stmt = $db->prepare("UPDATE newsletter_subscriptions SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
} elseif (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM newsletter_subscriptions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

// Fetch all subscribers
$subscribers = $db->query("SELECT * FROM newsletter_subscriptions ORDER BY subscribed_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subscribers</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>    
    <div class="container">
        <h1>Manage Newsletter Subscribers</h1>
        
        <div class="card">
            <h2>All Subscribers (<?= count($subscribers) ?>)</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Subscribed On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $sub): ?>
                    <tr>
                        <td><?= $sub['id'] ?></td>
                        <td><?= htmlspecialchars($sub['email']) ?></td>
                        <td><?= date('M d, Y', strtotime($sub['subscribed_at'])) ?></td>
                        <td><?= $sub['is_active'] ? 'Active' : 'Inactive' ?></td>
                        <td>
                            <a href="subscribers.php?toggle=<?= $sub['id'] ?>" class="button">
                                <?= $sub['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </a>
                            <a href="subscribers.php?delete=<?= $sub['id'] ?>" class="button danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
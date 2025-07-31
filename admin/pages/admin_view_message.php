<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check admin authentication
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Aircraft Management";

// Include admin header
// include __DIR__ . '/../templates/admin/header.php';

// Get database connection
$db = DBConfig::getInstance()->getConnection();

// Get message details
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Mark as read when viewing
        $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$message) {
            die("Message not found");
        }
    } catch(PDOException $e) {
        die("Error fetching message: " . $e->getMessage());
    }
} else {
    die("Invalid request");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message | Aviation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* [Include the same styles from admin_messages.php] */
        
        .message-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .message-meta {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .meta-item strong {
            display: block;
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .message-content {
            line-height: 1.8;
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Message Details</h1>
        </header>
        
        <a href="index.php?page=admin_messages" class="back-btn">&larr; Back to Messages</a>
        
        <div class="message-card">
            <div class="message-meta">
                <div class="meta-item">
                    <strong>Name</strong>
                    <?= htmlspecialchars($message['name']) ?>
                </div>
                <div class="meta-item">
                    <strong>Email</strong>
                    <?= htmlspecialchars($message['email']) ?>
                </div>
                <div class="meta-item">
                    <strong>Date</strong>
                    <?= date('M j, Y g:i a', strtotime($message['created_at'])) ?>
                </div>
                <div class="meta-item">
                    <strong>IP Address</strong>
                    <?= htmlspecialchars($message['ip_address']) ?>
                </div>
            </div>
            
            <div class="message-content">
                <h2><?= htmlspecialchars($message['subject']) ?></h2>
                <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
            </div>
        </div>
    </div>
</body>
</html>
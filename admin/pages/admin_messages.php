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

// Mark message as read
if (isset($_GET['mark_as_read'])) {
    $id = (int)$_GET['mark_as_read'];
    try {
        $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
    } catch(PDOException $e) {
        die("Error updating message: " . $e->getMessage());
    }
}

// Delete message
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
    } catch(PDOException $e) {
        die("Error deleting message: " . $e->getMessage());
    }
}

// Get all messages
try {
    $stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching messages: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Admin | Aviation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        h1 {
            color: var(--primary);
        }
        
        .logout-btn {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background-color: var(--primary-dark);
        }
        
        .messages-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .messages-table th, .messages-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .messages-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .messages-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .messages-table tr:hover {
            background-color: #f1f5f9;
        }
        
        .unread {
            font-weight: 600;
            background-color: #eff6ff !important;
        }
        
        .action-btn {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }
        
        .view-btn {
            background-color: var(--primary);
            color: white;
        }
        
        .delete-btn {
            background-color: #ef4444;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Contact Messages</h1>
        </header>
        
        <?php if (count($messages) > 0): ?>
            <table class="messages-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr class="<?= $message['is_read'] ? '' : 'unread' ?>">
                            <td><?= htmlspecialchars($message['name']) ?></td>
                            <td><?= htmlspecialchars($message['email']) ?></td>
                            <td><?= htmlspecialchars($message['subject']) ?></td>
                            <td><?= date('M j, Y g:i a', strtotime($message['created_at'])) ?></td>
                            <td>
                                <a href="index.php?page=admin_view_message&id=<?= $message['id'] ?>" class="action-btn view-btn">View</a>
                                <a href="?mark_as_read=<?= $message['id'] ?>" class="action-btn view-btn">Mark Read</a>
                                <a href="?delete=<?= $message['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h2>No messages found</h2>
                <p>There are no contact messages in the database.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
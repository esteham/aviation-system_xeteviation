<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

$db = DBConfig::getInstance()->getConnection();
// Fetch booking details
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_testimonial'])) {
        // Add new testimonial
        $stmt = $db->prepare("INSERT INTO testimonials (user_name, user_image, review, is_approved) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['user_name'], $_POST['user_image'], $_POST['review'], isset($_POST['is_approved']) ? 1 : 0]);
    } elseif (isset($_POST['update_testimonial'])) {
        // Update testimonial
        $stmt = $db->prepare("UPDATE testimonials SET user_name = ?, user_image = ?, review = ?, is_approved = ? WHERE id = ?");
        $stmt->execute([$_POST['user_name'], $_POST['user_image'], $_POST['review'], isset($_POST['is_approved']) ? 1 : 0, $_POST['id']]);
    } elseif (isset($_GET['delete'])) {
        // Delete testimonial
        $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
    }
}

// Fetch all testimonials
$testimonials = $db->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials</title>
    <style>
        /* Admin Styles */
body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    background: #f5f5f5;
    color: #333;
}

.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 20px;
}

.card {
    background: white;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background: #f9f9f9;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.form-group textarea {
    min-height: 100px;
}

.button {
    display: inline-block;
    padding: 10px 15px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    margin-right: 10px;
}

.button:hover {
    background: #0056b3;
}

.button.danger {
    background: #dc3545;
}

.button.danger:hover {
    background: #a71d2a;
}

.button.light {
    background: #f8f9fa;
    color: #212529;
}

.button.light:hover {
    background: #dae0e5;
}

/* Admin Header */
.admin-header {
    background: #343a40;
    color: white;
    padding: 15px 0;
}

.admin-header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
}

.admin-nav a:hover {
    text-decoration: underline;
}
    </style>
</head>
<body>
    
    <div class="container">
        <h1>Manage Testimonials</h1>
        
        <!-- Add Testimonial Form -->
        <div class="card">
            <h2><?= isset($_GET['edit']) ? 'Edit' : 'Add' ?> Testimonial</h2>
            <form method="POST">
                <?php if (isset($_GET['edit'])): 
                    $edit = $pdo->query("SELECT * FROM testimonials WHERE id = " . $_GET['edit'])->fetch();
                ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>User Name</label>
                    <input type="text" name="user_name" value="<?= $edit['user_name'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>User Image URL</label>
                    <input type="file" name="user_image" value="<?= $edit['user_image'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Review</label>
                    <textarea name="review" required><?= $edit['review'] ?? '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_approved" <?= isset($edit) && $edit['is_approved'] ? 'checked' : '' ?>>
                        Approved
                    </label>
                </div>
                
                <button type="submit" name="<?= isset($_GET['edit']) ? 'update_testimonial' : 'add_testimonial' ?>">
                    <?= isset($_GET['edit']) ? 'Update' : 'Add' ?> Testimonial
                </button>
                
                <?php if (isset($_GET['edit'])): ?>
                    <a href="testimonials.php" class="button">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Testimonials List -->
        <div class="card">
            <h2>All Testimonials</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Review</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($testimonials as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($t['user_image']) ?>" width="50" style="border-radius:50%;">
                            <?= htmlspecialchars($t['user_name']) ?>
                        </td>
                        <td><?= substr(htmlspecialchars($t['review']), 0, 50) ?>...</td>
                        <td><?= $t['is_approved'] ? 'Approved' : 'Pending' ?></td>
                        <td>
                            <a href="testimonials.php?edit=<?= $t['id'] ?>" class="button">Edit</a>
                            <a href="testimonials.php?delete=<?= $t['id'] ?>" class="button danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
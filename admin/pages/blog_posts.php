<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

$db = DBConfig::getInstance()->getConnection();
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_post'])) {
        // Add new blog post
        $stmt = $db->prepare("INSERT INTO blog_posts (title, excerpt, content, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['title'], $_POST['excerpt'], $_POST['content'], $_POST['image_url']]);
    } elseif (isset($_POST['update_post'])) {
        // Update blog post
        $stmt = $db->prepare("UPDATE blog_posts SET title = ?, excerpt = ?, content = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$_POST['title'], $_POST['excerpt'], $_POST['content'], $_POST['image_url'], $_POST['id']]);
    } elseif (isset($_GET['delete'])) {
        // Delete blog post
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
    }
}

// Fetch all blog posts
$posts = $db->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog Posts</title>
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
        <h1>Manage Blog Posts</h1>
        
        <!-- Add/Edit Blog Post Form -->
        <div class="card">
            <h2><?= isset($_GET['edit']) ? 'Edit' : 'Add' ?> Blog Post</h2>
            <form method="POST">
                <?php if (isset($_GET['edit'])): 
                    $edit = $pdo->query("SELECT * FROM blog_posts WHERE id = " . $_GET['edit'])->fetch();
                ?>
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?= $edit['title'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Excerpt</label>
                    <textarea name="excerpt" required><?= $edit['excerpt'] ?? '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" id="editor1" required><?= $edit['content'] ?? '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="file" name="image_url" value="<?= $edit['image_url'] ?? '' ?>" required>
                </div>
                
                <button type="submit" name="<?= isset($_GET['edit']) ? 'update_post' : 'add_post' ?>">
                    <?= isset($_GET['edit']) ? 'Update' : 'Add' ?> Post
                </button>
                
                <?php if (isset($_GET['edit'])): ?>
                    <a href="blog_posts.php" class="button">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Blog Posts List -->
        <div class="card">
            <h2>All Blog Posts</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Excerpt</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= $post['id'] ?></td>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td><?= substr(htmlspecialchars($post['excerpt']), 0, 50) ?>...</td>
                        <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                        <td>
                            <a href="blog_posts.php?edit=<?= $post['id'] ?>" class="button">Edit</a>
                            <a href="blog_posts.php?delete=<?= $post['id'] ?>" class="button danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- CKEditor for rich text editing -->
    <script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'editor1' );
    </script>
</body>
</html>
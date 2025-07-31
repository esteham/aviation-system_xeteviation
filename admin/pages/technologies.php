<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';


// Check admin authentication (uncomment when ready)
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Flight Management";

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;
    
    if ($action === 'delete' && $id) {
        $stmt = $db->prepare("DELETE FROM technologies WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Technology deleted successfully!";
    }
    
    //header("Location: technologies.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $icon = $_POST['icon'];
    $display_order = $_POST['display_order'];
    
    if ($id) {
        // Update existing record
        $stmt = $db->prepare("UPDATE technologies SET 
            title = ?, description = ?, icon = ?, display_order = ? 
            WHERE id = ?");
        $stmt->execute([$title, $description, $icon, $display_order, $id]);
        $_SESSION['message'] = "Technology updated successfully!";
    } else {
        // Insert new record
        $stmt = $db->prepare("INSERT INTO technologies 
            (title, description, icon, display_order) 
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon, $display_order]);
        $_SESSION['message'] = "Technology added successfully!";
    }
    
    //header("Location: technologies.php");
    exit();
}

// Get all technologies
$stmt = $db->query("SELECT * FROM technologies ORDER BY display_order, title");
$technologies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get single technology for editing
$edit_tech = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $stmt = $db->prepare("SELECT * FROM technologies WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_tech = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<div class="container">
    <h2>Technologies</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <h3><?= $edit_tech ? 'Edit' : 'Add' ?> Technology</h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= $edit_tech ? $edit_tech['id'] : '' ?>">
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" value="<?= $edit_tech ? htmlspecialchars($edit_tech['title']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" required><?= $edit_tech ? htmlspecialchars($edit_tech['description']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Icon (Font Awesome class)</label>
                    <input type="text" name="icon" class="form-control" value="<?= $edit_tech ? htmlspecialchars($edit_tech['icon']) : '' ?>" required>
                    <small class="text-muted">e.g. "fas fa-robot"</small>
                </div>
                
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="<?= $edit_tech ? $edit_tech['display_order'] : 0 ?>">
                </div>
                
                <button type="submit" class="btn btn-primary"><?= $edit_tech ? 'Update' : 'Add' ?> Technology</button>
                <?php if ($edit_tech): ?>
                    <a href="technologies.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="col-md-6">
            <h3>Current Technologies</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($technologies as $tech): ?>
                        <tr>
                            <td><i class="<?= htmlspecialchars($tech['icon']) ?>"></i></td>
                            <td><?= htmlspecialchars($tech['title']) ?></td>
                            <td><?= $tech['display_order'] ?></td>
                            <td>
                                <a href="technologies.php?edit=<?= $tech['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="technologies.php?action=delete&id=<?= $tech['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

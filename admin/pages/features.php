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
        // Delete image file first
        $stmt = $db->prepare("SELECT image FROM features WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();
        if ($image && file_exists($image)) {
            unlink($image);
        }
        
        $stmt = $db->prepare("DELETE FROM features WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Feature deleted successfully!";
    }
    
    //header("Location: features.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'];
    $description = $_POST['description'];
    $tab_id = $_POST['tab_id'];
    $display_order = $_POST['display_order'];
    
    // Handle file upload
    $image = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/features/';
        $image = $uploadDir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
        
        // Delete old image if updating
        if ($id && $_POST['existing_image']) {
            unlink($_POST['existing_image']);
        }
    }
    
    if ($id) {
        // Update existing record
        $stmt = $db->prepare("UPDATE features SET 
            title = ?, description = ?, image = ?, tab_id = ?, display_order = ? 
            WHERE id = ?");
        $stmt->execute([$title, $description, $image, $tab_id, $display_order, $id]);
        $_SESSION['message'] = "Feature updated successfully!";
    } else {
        // Insert new record
        $stmt = $db->prepare("INSERT INTO features 
            (title, description, image, tab_id, display_order) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $image, $tab_id, $display_order]);
        $_SESSION['message'] = "Feature added successfully!";
    }
    
    //header("Location: features.php");
    exit();
}

// Get all features
$stmt = $db->query("SELECT * FROM features ORDER BY display_order, title");
$features = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get single feature for editing
$edit_feature = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $stmt = $db->prepare("SELECT * FROM features WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_feature = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<div class="container">
    <h2>Features</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <h3><?= $edit_feature ? 'Edit' : 'Add' ?> Feature</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit_feature ? $edit_feature['id'] : '' ?>">
                <input type="hidden" name="existing_image" value="<?= $edit_feature ? $edit_feature['image'] : '' ?>">
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" value="<?= $edit_feature ? htmlspecialchars($edit_feature['title']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" required><?= $edit_feature ? htmlspecialchars($edit_feature['description']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Tab ID</label>
                    <input type="text" name="tab_id" class="form-control" value="<?= $edit_feature ? htmlspecialchars($edit_feature['tab_id']) : '' ?>" required>
                    <small class="text-muted">Used to group features in tabs (e.g., "tab1", "tab2")</small>
                </div>
                
                <div class="form-group">
                    <label>Image</label>
                    <?php if ($edit_feature && $edit_feature['image']): ?>
                        <img src="<?= $edit_feature['image'] ?>" class="img-thumbnail mb-2" style="max-height: 150px;"><br>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control-file" <?= !$edit_feature ? 'required' : '' ?>>
                </div>
                
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="<?= $edit_feature ? $edit_feature['display_order'] : 0 ?>">
                </div>
                
                <button type="submit" class="btn btn-primary"><?= $edit_feature ? 'Update' : 'Add' ?> Feature</button>
                <?php if ($edit_feature): ?>
                    <a href="features.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="col-md-6">
            <h3>Current Features</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Tab ID</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($features as $feature): ?>
                        <tr>
                            <td><img src="<?= $feature['image'] ?>" style="max-height: 50px;"></td>
                            <td><?= htmlspecialchars($feature['title']) ?></td>
                            <td><?= htmlspecialchars($feature['tab_id']) ?></td>
                            <td><?= $feature['display_order'] ?></td>
                            <td>
                                <a href="features.php?edit=<?= $feature['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="features.php?action=delete&id=<?= $feature['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';


// Check admin authentication (uncomment when ready)
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Flight Management";

// Handle actions (add, edit, delete)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;
    
    if ($action === 'delete' && $id) {
        $stmt = $db->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Team member deleted successfully!";
    } elseif ($action === 'toggle_active' && $id) {
        $stmt = $db->prepare("UPDATE team_members SET active = NOT active WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Status updated successfully!";
    }
    
    header("Location: team_members.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $position = $_POST['position'];
    $bio = $_POST['bio'];
    $linkedin = $_POST['linkedin'];
    $twitter = $_POST['twitter'];
    $display_order = $_POST['display_order'];
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Handle file upload
    $photo = $_POST['existing_photo'] ?? '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/team/';
        $photo = $uploadDir . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }
    
    if ($id) {
        // Update existing record
        $stmt = $db->prepare("UPDATE team_members SET 
            name = ?, position = ?, photo = ?, bio = ?, linkedin = ?, 
            twitter = ?, display_order = ?, active = ? 
            WHERE id = ?");
        $stmt->execute([$name, $position, $photo, $bio, $linkedin, 
                       $twitter, $display_order, $active, $id]);
        $_SESSION['message'] = "Team member updated successfully!";
    } else {
        // Insert new record
        $stmt = $db->prepare("INSERT INTO team_members 
            (name, position, photo, bio, linkedin, twitter, display_order, active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $position, $photo, $bio, $linkedin, 
                       $twitter, $display_order, $active]);
        $_SESSION['message'] = "Team member added successfully!";
    }
    
    //header("Location: team_members.php");
    exit();
}

// Get all team members
$stmt = $db->query("SELECT * FROM team_members ORDER BY display_order, name");
$team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get single member for editing
$edit_member = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $stmt = $db->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_member = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<div class="container">
    <h2>Team Members</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <h3><?= $edit_member ? 'Edit' : 'Add' ?> Team Member</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit_member ? $edit_member['id'] : '' ?>">
                <input type="hidden" name="existing_photo" value="<?= $edit_member ? $edit_member['photo'] : '' ?>">
                
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="<?= $edit_member ? htmlspecialchars($edit_member['name']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" class="form-control" value="<?= $edit_member ? htmlspecialchars($edit_member['position']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Photo</label>
                    <?php if ($edit_member && $edit_member['photo']): ?>
                        <img src="<?= $edit_member['photo'] ?>" class="img-thumbnail mb-2" style="max-height: 100px;"><br>
                    <?php endif; ?>
                    <input type="file" name="photo" class="form-control-file">
                </div>
                
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control" rows="3"><?= $edit_member ? htmlspecialchars($edit_member['bio']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>LinkedIn URL</label>
                    <input type="url" name="linkedin" class="form-control" value="<?= $edit_member ? htmlspecialchars($edit_member['linkedin']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Twitter URL</label>
                    <input type="url" name="twitter" class="form-control" value="<?= $edit_member ? htmlspecialchars($edit_member['twitter']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="<?= $edit_member ? $edit_member['display_order'] : 0 ?>">
                </div>
                
                <div class="form-check mb-3">
                    <input type="checkbox" name="active" class="form-check-input" id="active" 
                        <?= $edit_member && $edit_member['active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                
                <button type="submit" class="btn btn-primary"><?= $edit_member ? 'Update' : 'Add' ?> Member</button>
                <?php if ($edit_member): ?>
                    <a href="team_members.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="col-md-6">
            <h3>Current Team Members</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($team_members as $member): ?>
                        <tr>
                            <td><img src="<?= $member['photo'] ?>" style="max-height: 50px;"></td>
                            <td><?= htmlspecialchars($member['name']) ?></td>
                            <td><?= htmlspecialchars($member['position']) ?></td>
                            <td><?= $member['display_order'] ?></td>
                            <td><?= $member['active'] ? 'Active' : 'Inactive' ?></td>
                            <td>
                                <a href="team_members.php?edit=<?= $member['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="team_members.php?action=toggle_active&id=<?= $member['id'] ?>" class="btn btn-sm btn-warning">Toggle</a>
                                <a href="team_members.php?action=delete&id=<?= $member['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

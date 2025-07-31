<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $stmt = $db->prepare("DELETE FROM promotional_content WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Promotion deleted successfully'];
        } else {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'content_type' => $_POST['content_type'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'start_date' => $_POST['start_date'] ?: null,
                'end_date' => $_POST['end_date'] ?: null,
                'button_text' => $_POST['button_text'],
                'button_link' => $_POST['button_link']
            ];

            // Handle file upload
            if (!empty($_FILES['media']['name'])) {
                $uploadDir = __DIR__ . '/../../assets/uploads/promotions/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = time() . '_' . basename($_FILES['media']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
                    $data['media_path'] = 'assets/uploads/promotions/' . $fileName;
                }
            }

            if (isset($_POST['id'])) {
                // Update existing
                $stmt = $db->prepare("UPDATE promotional_content SET 
                    title = :title,
                    description = :description,
                    content_type = :content_type,
                    " . (isset($data['media_path']) ? "media_path = :media_path," : "") . "
                    is_active = :is_active,
                    start_date = :start_date,
                    end_date = :end_date,
                    button_text = :button_text,
                    button_link = :button_link
                    WHERE id = :id");

                if (isset($data['media_path'])) {
                    $stmt->bindParam(':media_path', $data['media_path']);
                }
                $data['id'] = $_POST['id'];
                $stmt->execute($data);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Promotion updated successfully'];
            } else {
                // Insert new
                if (empty($data['media_path'])) {
                    throw new Exception("Media file is required");
                }
                $stmt = $db->prepare("INSERT INTO promotional_content 
                    (title, description, content_type, media_path, is_active, start_date, end_date, button_text, button_link) 
                    VALUES (:title, :description, :content_type, :media_path, :is_active, :start_date, :end_date, :button_text, :button_link)");
                $stmt->execute($data);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Promotion added successfully'];
            }
        }
        // header("Location: promotions.php");
        echo "<script>window.location.href='index.php?page=promotions';</script>";
        exit();
    } catch (Exception $e) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Get current promotions
$promotions = $db->query("SELECT * FROM promotional_content ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get specific promotion for editing
$editPromo = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM promotional_content WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editPromo = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<div class="container-fluid py-4">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?>">
            <?= $_SESSION['flash_message']['message'] ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4><?= $editPromo ? 'Edit' : 'Add' ?> Promotion</h4>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($editPromo): ?>
                            <input type="hidden" name="id" value="<?= $editPromo['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required 
                                   value="<?= htmlspecialchars($editPromo['title'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editPromo['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content Type</label>
                            <select name="content_type" class="form-select" required>
                                <option value="image" <?= ($editPromo['content_type'] ?? '') === 'image' ? 'selected' : '' ?>>Image</option>
                                <option value="video" <?= ($editPromo['content_type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Media File</label>
                            <input type="file" name="media" class="form-control" <?= !$editPromo ? 'required' : '' ?>>
                            <?php if ($editPromo && $editPromo['media_path']): ?>
                                <small class="text-muted">Current: <?= basename($editPromo['media_path']) ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                   <?= ($editPromo['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="datetime-local" name="start_date" class="form-control" 
                                       value="<?= $editPromo ? str_replace(' ', 'T', substr($editPromo['start_date'], 0, 16)) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="datetime-local" name="end_date" class="form-control" 
                                       value="<?= $editPromo ? str_replace(' ', 'T', substr($editPromo['end_date'], 0, 16)) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Button Text</label>
                            <input type="text" name="button_text" class="form-control" 
                                   value="<?= htmlspecialchars($editPromo['button_text'] ?? 'View Offer') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Button Link</label>
                            <input type="text" name="button_link" class="form-control" 
                                   value="<?= htmlspecialchars($editPromo['button_link'] ?? '#') ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><?= $editPromo ? 'Update' : 'Save' ?></button>
                        <?php if ($editPromo): ?>
                            <a href="promotions.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Current Promotions</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Dates</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($promotions as $promo): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($promo['title']) ?></td>
                                        <td><?= ucfirst($promo['content_type']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $promo['is_active'] ? 'success' : 'secondary' ?>">
                                                <?= $promo['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $promo['start_date'] ? date('M d, Y', strtotime($promo['start_date'])) : 'N/A' ?> - 
                                            <?= $promo['end_date'] ? date('M d, Y', strtotime($promo['end_date'])) : 'N/A' ?>
                                        </td>
                                        <td>
                                            <a href="promotions.php?edit=<?= $promo['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $promo['id'] ?>">
                                                <button type="submit" name="delete" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

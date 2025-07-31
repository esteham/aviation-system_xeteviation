<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check admin authentication
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Airline Management";

// Include admin header
// include __DIR__ . '/../templates/admin/header.php';

// Get database connection
$db = DBConfig::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_airline'])) {
            // Add new airline
            $stmt = $db->prepare("
                INSERT INTO airlines (
                    name, code, logo_url, description, is_active
                ) VALUES (
                    :name, :code, :logo_url, :description, :is_active
                )
            ");
            
            $logoUrl = !empty($_FILES['logo']['name']) 
                ? uploadAirlineLogo($_FILES['logo']) 
                : '';
            
            $stmt->execute([
                ':name' => $_POST['name'],
                ':code' => $_POST['code'],
                ':logo_url' => $logoUrl,
                ':description' => $_POST['description'],
                ':is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            $_SESSION['message'] = 'Airline added successfully';
            
        } elseif (isset($_POST['update_airline'])) {
            // Update airline
            $stmt = $db->prepare("
                UPDATE airlines SET
                    name = :name,
                    code = :code,
                    description = :description,
                    is_active = :is_active
                    " . (!empty($_FILES['logo']['name']) ? ", logo_url = :logo_url" : "") . "
                WHERE airline_id = :airline_id
            ");
            
            $params = [
                ':name' => $_POST['name'],
                ':code' => $_POST['code'],
                ':description' => $_POST['description'],
                ':is_active' => isset($_POST['is_active']) ? 1 : 0,
                ':airline_id' => $_POST['airline_id']
            ];
            
            if (!empty($_FILES['logo']['name'])) {
                $logoUrl = uploadAirlineLogo($_FILES['logo']);
                $params[':logo_url'] = $logoUrl;
                
                // Delete old logo if exists
                if (!empty($_POST['current_logo']) && file_exists('../' . $_POST['current_logo'])) {
                    unlink('../' . $_POST['current_logo']);
                }
            }
            
            $stmt->execute($params);
            
            $_SESSION['message'] = 'Airline updated successfully';
            
        } elseif (isset($_POST['delete_airline'])) {
            // Delete airline
            // First get logo to delete
            $stmt = $db->prepare("SELECT logo_url FROM airlines WHERE airline_id = ?");
            $stmt->execute([$_POST['airline_id']]);
            $logoUrl = $stmt->fetchColumn();
            
            // Delete the airline
            $stmt = $db->prepare("DELETE FROM airlines WHERE airline_id = ?");
            $stmt->execute([$_POST['airline_id']]);
            
            // Delete logo file if exists
            if ($logoUrl && file_exists('../' . $logoUrl)) {
                unlink('../' . $logoUrl);
            }
            
            $_SESSION['message'] = 'Airline deleted successfully';
        }      
    } catch (PDOException $e) {
        handleException($e);
    }
}

// Get all airlines
$stmt = $db->query("SELECT * FROM airlines ORDER BY name");
$airlines = $stmt->fetchAll();

// Function to upload airline logo
function uploadAirlineLogo($file) {
    $uploadDir = 'assets/images/airlines/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/pjpeg', 'image/x-png', 'image/webp', 'image/svg+xml', 'image/avif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Only JPG, PNG, and GIF files are allowed');
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        throw new Exception('File size must be less than 2MB');
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'airline_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], '../' . $destination)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return $destination;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        img.airline-logo-sm {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1 class="mb-4">Airline Management</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">All Airlines</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAirlineModal">
                <i class="fas fa-plus me-1"></i> Add New Airline
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($airlines as $airline): ?>
                        <tr>
                            <td><?= $airline['airline_id'] ?></td>
                            <td>
                                <?php if ($airline['logo_url']): ?>
                                <img src="../<?= htmlspecialchars($airline['logo_url']) ?>" 
                                     alt="<?= htmlspecialchars($airline['name']) ?>" 
                                     class="airline-logo-sm">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($airline['name']) ?></td>
                            <td><?= htmlspecialchars($airline['code']) ?></td>
                            <td>
                                <span class="badge <?= $airline['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $airline['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1 edit-airline" 
                                        data-airline='<?= htmlspecialchars(json_encode($airline)) ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="airline_id" value="<?= $airline['airline_id'] ?>">
                                    <button type="submit" name="delete_airline" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this airline?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
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

<!-- Add Airline Modal -->
<div class="modal fade" id="addAirlineModal" tabindex="-1" aria-labelledby="addAirlineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAirlineModalLabel">Add New Airline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Airline Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Airline Code</label>
                            <input type="text" name="code" class="form-control" maxlength="3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <small class="text-muted">Recommended size: 200x200 pixels</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_airline" class="btn btn-primary">Save Airline</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Airline Modal -->
<div class="modal fade" id="editAirlineModal" tabindex="-1" aria-labelledby="editAirlineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="airline_id" id="edit_airline_id">
                <input type="hidden" name="current_logo" id="edit_current_logo">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAirlineModalLabel">Edit Airline</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Airline Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Airline Code</label>
                            <input type="text" name="code" id="edit_code" class="form-control" maxlength="3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Logo</label>
                        <div id="current-logo-container" class="mb-2"></div>
                        <label class="form-label">Change Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <small class="text-muted">Leave blank to keep current logo</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_airline" class="btn btn-primary">Update Airline</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit airline button clicks
document.querySelectorAll('.edit-airline').forEach(button => {
    button.addEventListener('click', function() {
        const airline = JSON.parse(this.dataset.airline);
        
        // Fill the edit form
        document.getElementById('edit_airline_id').value = airline.airline_id;
        document.getElementById('edit_name').value = airline.name;
        document.getElementById('edit_code').value = airline.code;
        document.getElementById('edit_description').value = airline.description || '';
        document.getElementById('edit_is_active').checked = airline.is_active == 1;
        
        // Handle logo
        const logoContainer = document.getElementById('current-logo-container');
        logoContainer.innerHTML = '';
        
        if (airline.logo_url) {
            document.getElementById('edit_current_logo').value = airline.logo_url;
            const img = document.createElement('img');
            img.src = '../' + airline.logo_url;
            img.alt = airline.name;
            img.className = 'airline-logo-sm';
            logoContainer.appendChild(img);
        } else {
            document.getElementById('edit_current_logo').value = '';
            logoContainer.innerHTML = '<span class="text-muted">No logo uploaded</span>';
        }
        
        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editAirlineModal'));
        editModal.show();
    });
});
</script>


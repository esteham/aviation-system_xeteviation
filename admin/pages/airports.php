<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check admin authentication
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Airport Management";

// Include admin header
// include __DIR__ . '/../templates/admin/header.php';

// Get database connection
$db = DBConfig::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_airport'])) {
            // Handle image upload
            $imageUrl = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../assets/images/airports/';
                $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $fileName = 'airport_' . time() . '.' . $fileExt;
                $uploadPath = $uploadDir . $fileName;
                
                // Check if directory exists, if not create it
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $imageUrl = 'assets/images/airports/' . $fileName;
                }
            }
            
            // Add new airport
            $stmt = $db->prepare("
                INSERT INTO airports (
                    code, name, city, country, 
                    latitude, longitude, timezone, image_url
                ) VALUES (
                    :code, :name, :city, :country,
                    :latitude, :longitude, :timezone, :image_url
                )
            ");
            
            $stmt->execute([
                ':code' => strtoupper($_POST['code']),
                ':name' => $_POST['name'],
                ':city' => $_POST['city'],
                ':country' => $_POST['country'],
                ':latitude' => $_POST['latitude'],
                ':longitude' => $_POST['longitude'],
                ':timezone' => $_POST['timezone'],
                ':image_url' => $imageUrl
            ]);
            
            $_SESSION['message'] = 'Airport added successfully';
            
        } elseif (isset($_POST['update_airport'])) {
            // Handle image upload for update
            $imageUrl = $_POST['current_image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../assets/images/airports/';
                $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $fileName = 'airport_' . time() . '.' . $fileExt;
                $uploadPath = $uploadDir . $fileName;
                
                // Check if directory exists, if not create it
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Delete old image if exists
                    if ($imageUrl && file_exists(__DIR__ . '/../../' . $imageUrl)) {
                        unlink(__DIR__ . '/../../' . $imageUrl);
                    }
                    $imageUrl = 'assets/images/airports/' . $fileName;
                }
            }
            
            // Update airport
            $stmt = $db->prepare("
                UPDATE airports SET
                    code = :code,
                    name = :name,
                    city = :city,
                    country = :country,
                    latitude = :latitude,
                    longitude = :longitude,
                    timezone = :timezone,
                    image_url = :image_url
                WHERE airport_id = :airport_id
            ");
            
            $stmt->execute([
                ':code' => strtoupper($_POST['code']),
                ':name' => $_POST['name'],
                ':city' => $_POST['city'],
                ':country' => $_POST['country'],
                ':latitude' => $_POST['latitude'],
                ':longitude' => $_POST['longitude'],
                ':timezone' => $_POST['timezone'],
                ':image_url' => $imageUrl,
                ':airport_id' => $_POST['airport_id']
            ]);
            
            $_SESSION['message'] = 'Airport updated successfully';
            
        } elseif (isset($_POST['delete_airport'])) {
            // Delete airport image if exists
            $stmt = $db->prepare("SELECT image_url FROM airports WHERE airport_id = ?");
            $stmt->execute([$_POST['airport_id']]);
            $airport = $stmt->fetch();
            
            if ($airport['image_url'] && file_exists(__DIR__ . '/../../' . $airport['image_url'])) {
                unlink(__DIR__ . '/../../' . $airport['image_url']);
            }
            
            // Delete airport
            $stmt = $db->prepare("DELETE FROM airports WHERE airport_id = ?");
            $stmt->execute([$_POST['airport_id']]);
            $_SESSION['message'] = 'Airport deleted successfully';
        } 
        echo "<script>window.location.href='index.php?page=airports';</script>";
        
    } catch (PDOException $e) {
        handleException($e);
    }
}

// Get all airports
$stmt = $db->query("SELECT * FROM airports ORDER BY country, city");
$airports = $stmt->fetchAll();
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
       
</head>
<body>

<div class="admin-container">
    <h1 class="mb-4">Airport Management</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">All Airports</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAirportModal">
                <i class="fas fa-plus me-1"></i> Add New Airport
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>City</th>
                            <th>Country</th>
                            <th>Coordinates</th>
                            <th>Timezone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($airports as $airport): ?>
                        <tr>
                            <td>
                                <?php if ($airport['image_url']): ?>
                                    <img src="<?= htmlspecialchars($airport['image_url']) ?>" alt="Airport Image" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="no-image" style="width: 50px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($airport['code']) ?></strong></td>
                            <td><?= htmlspecialchars($airport['name']) ?></td>
                            <td><?= htmlspecialchars($airport['city']) ?></td>
                            <td><?= htmlspecialchars($airport['country']) ?></td>
                            <td>
                                <?= round($airport['latitude'], 4) ?>, <?= round($airport['longitude'], 4) ?>
                            </td>
                            <td><?= htmlspecialchars($airport['timezone']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1 edit-airport" 
                                        data-airport='<?= htmlspecialchars(json_encode($airport)) ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="airport_id" value="<?= $airport['airport_id'] ?>">
                                    <button type="submit" name="delete_airport" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this airport?')">
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

<!-- Add Airport Modal -->
<div class="modal fade" id="addAirportModal" tabindex="-1" aria-labelledby="addAirportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAirportModalLabel">Add New Airport</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Airport Code (IATA)</label>
                            <input type="text" name="code" class="form-control" maxlength="3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Airport Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="0.0001" name="latitude" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="0.0001" name="longitude" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Timezone</label>
                        <input type="text" name="timezone" class="form-control" required>
                        <small class="text-muted">Example: America/New_York</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Airport Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Recommended size: 300x300 pixels</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_airport" class="btn btn-primary">Save Airport</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Airport Modal -->
<div class="modal fade" id="editAirportModal" tabindex="-1" aria-labelledby="editAirportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="airport_id" id="edit_airport_id">
                <input type="hidden" name="current_image" id="edit_current_image">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAirportModalLabel">Edit Airport</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Airport Code (IATA)</label>
                            <input type="text" name="code" id="edit_code" class="form-control" maxlength="3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Airport Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="edit_city" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" id="edit_country" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="0.0001" name="latitude" id="edit_latitude" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="0.0001" name="longitude" id="edit_longitude" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Timezone</label>
                        <input type="text" name="timezone" id="edit_timezone" class="form-control" required>
                        <small class="text-muted">Example: America/New_York</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div id="edit_current_image_container" class="mb-2">
                            <!-- Current image will be shown here -->
                        </div>
                        <label class="form-label">Change Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_airport" class="btn btn-primary">Update Airport</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit airport button clicks
document.querySelectorAll('.edit-airport').forEach(button => {
    button.addEventListener('click', function() {
        const airport = JSON.parse(this.dataset.airport);
        
        // Fill the edit form
        document.getElementById('edit_airport_id').value = airport.airport_id;
        document.getElementById('edit_code').value = airport.code;
        document.getElementById('edit_name').value = airport.name;
        document.getElementById('edit_city').value = airport.city;
        document.getElementById('edit_country').value = airport.country;
        document.getElementById('edit_latitude').value = airport.latitude;
        document.getElementById('edit_longitude').value = airport.longitude;
        document.getElementById('edit_timezone').value = airport.timezone;
        document.getElementById('edit_current_image').value = airport.image_url || '';
        
        // Display current image
        const imageContainer = document.getElementById('edit_current_image_container');
        imageContainer.innerHTML = '';
        
        if (airport.image_url) {
            const img = document.createElement('img');
            img.src = airport.image_url;
            img.style.maxWidth = '150px';
            img.style.maxHeight = '150px';
            img.className = 'img-thumbnail';
            imageContainer.appendChild(img);
        } else {
            const noImage = document.createElement('div');
            noImage.className = 'no-image';
            noImage.style.width = '150px';
            noImage.style.height = '150px';
            noImage.style.background = '#eee';
            noImage.style.display = 'flex';
            noImage.style.alignItems = 'center';
            noImage.style.justifyContent = 'center';
            
            const icon = document.createElement('i');
            icon.className = 'fas fa-image text-muted fa-2x';
            noImage.appendChild(icon);
            
            imageContainer.appendChild(noImage);
        }
        
        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editAirportModal'));
        editModal.show();
    });
});
</script>
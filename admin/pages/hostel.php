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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_hotel'])) {
        // Add new hotel
        $stmt = $db->prepare("INSERT INTO hotels (destination_id, name, address, stars, is_luxury, description, amenities, price_per_night, image_url) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['destination_id'],
            $_POST['name'],
            $_POST['address'],
            $_POST['stars'],
            isset($_POST['is_luxury']) ? 1 : 0,
            $_POST['description'],
            $_POST['amenities'],
            $_POST['price_per_night'],
            $_POST['image_url']
        ]);
        $success = "Hotel added successfully!";
    } elseif (isset($_POST['update_hotel'])) {
        // Update hotel
        $stmt = $db->prepare("UPDATE hotels SET 
                              destination_id = ?, name = ?, address = ?, stars = ?, is_luxury = ?, 
                              description = ?, amenities = ?, price_per_night = ?, image_url = ?
                              WHERE id = ?");
        $stmt->execute([
            $_POST['destination_id'],
            $_POST['name'],
            $_POST['address'],
            $_POST['stars'],
            isset($_POST['is_luxury']) ? 1 : 0,
            $_POST['description'],
            $_POST['amenities'],
            $_POST['price_per_night'],
            $_POST['image_url'],
            $_POST['hotel_id']
        ]);
        $success = "Hotel updated successfully!";
    } elseif (isset($_GET['delete'])) {
        // Delete hotel
        $stmt = $db->prepare("DELETE FROM hotels WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "Hotel deleted successfully!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_hotel'])) {
        // [existing hotel add logic...]
    } elseif (isset($_POST['update_hotel'])) {
        // [existing update logic...]
    } elseif (isset($_GET['delete'])) {
        // [existing delete logic...]
    } 
    elseif (isset($_POST['add_destination'])) {
        // Handle file upload
        $uploadDir = __DIR__ . '/../../assets/images/popular_destination/';
        $imagePath = '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Generate unique filename
            $extension = strtolower(end(explode('.', $_FILES['image']['name'])));
            $filename = uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            // Check if directory exists, if not create it
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = 'assets/images/popular_destination/' . $filename;
            } else {
                $error = "Failed to upload image.";
            }
        }
        
        if (empty($error)) {
            // Add new destination
            $stmt = $db->prepare("INSERT INTO destinations (city, country, description, image_url, is_popular) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['city'],
                $_POST['country'],
                $_POST['description'],
                $imagePath,
                isset($_POST['is_popular']) ? 1 : 0
            ]);
            $success = "Destination added successfully!";
            
            // Refresh destinations list
            $destinations = $db->query("SELECT id, city, country FROM destinations ORDER BY city ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = "Error uploading image: " . $error;
        }
    }
}



// Fetch all hotels with destination info
$hotels = $db->query("
    SELECT h.*, d.city, d.country 
    FROM hotels h
    JOIN destinations d ON h.destination_id = d.id
    ORDER BY h.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch destinations for dropdown
$destinations = $db->query("SELECT id, city, country FROM destinations ORDER BY city ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="container-fluid">
        <div class="row">            
            <main>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Hotels</h1>
                    <div class="btn-group">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHotelModal">
                            <i class="fas fa-plus"></i> Add New Hotel
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDestinationModal">
                            <i class="fas fa-map-marker-alt"></i> Add Destination
                        </button>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Stars</th>
                                <th>Price/Night</th>
                                <th>Luxury</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hotels as $hotel): ?>
                            <tr>
                                <td><?= $hotel['id'] ?></td>
                                <td><?= htmlspecialchars($hotel['name']) ?></td>
                                <td><?= htmlspecialchars($hotel['city']) ?>, <?= htmlspecialchars($hotel['country']) ?></td>
                                <td><?= str_repeat('★', $hotel['stars']) ?></td>
                                <td>$<?= number_format($hotel['price_per_night'], 2) ?></td>
                                <td><?= $hotel['is_luxury'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-hotel" 
                                            data-id="<?= $hotel['id'] ?>"
                                            data-name="<?= htmlspecialchars($hotel['name']) ?>"
                                            data-destination="<?= $hotel['destination_id'] ?>"
                                            data-address="<?= htmlspecialchars($hotel['address']) ?>"
                                            data-stars="<?= $hotel['stars'] ?>"
                                            data-luxury="<?= $hotel['is_luxury'] ?>"
                                            data-desc="<?= htmlspecialchars($hotel['description']) ?>"
                                            data-amenities="<?= htmlspecialchars($hotel['amenities']) ?>"
                                            data-price="<?= $hotel['price_per_night'] ?>"
                                            data-image="<?= htmlspecialchars($hotel['image_url']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="index.php?page=hostel&delete=<?= $hotel['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Hotel Modal -->
    <div class="modal fade" id="addHotelModal" tabindex="-1" aria-labelledby="addHotelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addHotelModalLabel">Add New Hotel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Hotel Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="destination_id" class="form-label">Destination</label>
                                <select class="form-select" id="destination_id" name="destination_id" required>
                                    <?php foreach ($destinations as $dest): ?>
                                        <option value="<?= $dest['id'] ?>"><?= htmlspecialchars($dest['city']) ?>, <?= htmlspecialchars($dest['country']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            <div class="col-md-3">
                                <label for="stars" class="form-label">Star Rating</label>
                                <select class="form-select" id="stars" name="stars" required>
                                    <option value="1">1 Star</option>
                                    <option value="2">2 Stars</option>
                                    <option value="3" selected>3 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="5">5 Stars</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="price_per_night" class="form-label">Price Per Night</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="price_per_night" name="price_per_night" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Luxury Hotel</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_luxury" name="is_luxury">
                                    <label class="form-check-label" for="is_luxury">Mark as luxury</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="image_url" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="image_url" name="image_url">
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="amenities" class="form-label">Amenities (comma separated)</label>
                                <textarea class="form-control" id="amenities" name="amenities" rows="2" placeholder="Pool, Gym, Spa, Free WiFi"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_hotel" class="btn btn-primary">Save Hotel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Hotel Modal -->
    <div class="modal fade" id="editHotelModal" tabindex="-1" aria-labelledby="editHotelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="hotel_id" id="edit_hotel_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editHotelModalLabel">Edit Hotel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Hotel Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_destination_id" class="form-label">Destination</label>
                                <select class="form-select" id="edit_destination_id" name="destination_id" required>
                                    <?php foreach ($destinations as $dest): ?>
                                        <option value="<?= $dest['id'] ?>"><?= htmlspecialchars($dest['city']) ?>, <?= htmlspecialchars($dest['country']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="edit_address" name="address" required>
                            </div>
                            <div class="col-md-3">
                                <label for="edit_stars" class="form-label">Star Rating</label>
                                <select class="form-select" id="edit_stars" name="stars" required>
                                    <option value="1">1 Star</option>
                                    <option value="2">2 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="5">5 Stars</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="edit_price_per_night" class="form-label">Price Per Night</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="edit_price_per_night" name="price_per_night" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Luxury Hotel</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="edit_is_luxury" name="is_luxury">
                                    <label class="form-check-label" for="edit_is_luxury">Mark as luxury</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="edit_image_url" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="edit_image_url" name="image_url">
                            </div>
                            <div class="col-12">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="edit_amenities" class="form-label">Amenities (comma separated)</label>
                                <textarea class="form-control" id="edit_amenities" name="amenities" rows="2" placeholder="Pool, Gym, Spa, Free WiFi"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_hotel" class="btn btn-primary">Update Hotel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
  
    <!-- Add Destination Modal -->
    <div class="modal fade" id="addDestinationModal" tabindex="-1" aria-labelledby="addDestinationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDestinationModalLabel">Add New Destination</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Destination Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            <small class="text-muted">Image will be saved to assets/images/popular_destination/</small>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1">
                            <label class="form-check-label" for="is_popular">Mark as Popular Destination</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_destination" class="btn btn-success">Save Destination</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-hotel').forEach(button => {
            button.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editHotelModal'));
                
                // Fill the form with hotel data
                document.getElementById('edit_hotel_id').value = this.dataset.id;
                document.getElementById('edit_name').value = this.dataset.name;
                document.getElementById('edit_destination_id').value = this.dataset.destination;
                document.getElementById('edit_address').value = this.dataset.address;
                document.getElementById('edit_stars').value = this.dataset.stars;
                document.getElementById('edit_price_per_night').value = this.dataset.price;
                document.getElementById('edit_is_luxury').checked = this.dataset.luxury === '1';
                document.getElementById('edit_image_url').value = this.dataset.image;
                document.getElementById('edit_description').value = this.dataset.desc;
                document.getElementById('edit_amenities').value = this.dataset.amenities;
                
                modal.show();
            });
        });
    </script>
</body>
</html>
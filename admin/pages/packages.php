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
    if (isset($_POST['add_package'])) {
        // Add new package
        $stmt = $db->prepare("INSERT INTO travel_packages (destination_id, name, description, package_type, duration_days, base_price, image_url, included_services) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['destination_id'],
            $_POST['name'],
            $_POST['description'],
            $_POST['package_type'],
            $_POST['duration_days'],
            $_POST['base_price'],
            $_POST['image_url'],
            $_POST['included_services']
        ]);
        $packageId = $db->lastInsertId();
        
        // Add departure dates
        if (isset($_POST['departure_dates']) && is_array($_POST['departure_dates'])) {
            foreach ($_POST['departure_dates'] as $date) {
                if (!empty($date['date']) && !empty($date['airport']) && !empty($date['price']) && !empty($date['slots'])) {
                    $returnDate = date('Y-m-d', strtotime($date['date'] . " + " . $_POST['duration_days'] . " days"));
                    $stmt = $db->prepare("INSERT INTO package_departures (package_id, departure_airport_id, departure_date, return_date, price, available_slots) 
                                          VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $packageId,
                        $date['airport'],
                        $date['date'],
                        $returnDate,
                        $date['price'],
                        $date['slots']
                    ]);
                }
            }
        }
        $success = "Package added successfully!";
    } elseif (isset($_POST['update_package'])) {
        // Update package
        $stmt = $db->prepare("UPDATE travel_packages SET 
                              destination_id = ?, name = ?, description = ?, package_type = ?, 
                              duration_days = ?, base_price = ?, image_url = ?, included_services = ?
                              WHERE id = ?");
        $stmt->execute([
            $_POST['destination_id'],
            $_POST['name'],
            $_POST['description'],
            $_POST['package_type'],
            $_POST['duration_days'],
            $_POST['base_price'],
            $_POST['image_url'],
            $_POST['included_services'],
            $_POST['package_id']
        ]);
        $success = "Package updated successfully!";
    } elseif (isset($_GET['delete'])) {
        // Delete package
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("DELETE FROM package_departures WHERE package_id = ?");
            $stmt->execute([$_GET['delete']]);
            
            $stmt = $db->prepare("DELETE FROM travel_packages WHERE id = ?");
            $stmt->execute([$_GET['delete']]);
            
            $db->commit();
            $success = "Package deleted successfully!";
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error deleting package: " . $e->getMessage();
        }
    }
}

// Fetch all packages with destination info
$packages = $db->query("
    SELECT tp.*, d.city, d.country, 
           (SELECT COUNT(*) FROM package_departures pd WHERE pd.package_id = tp.id) as departure_count
    FROM travel_packages tp
    JOIN destinations d ON tp.destination_id = d.id
    ORDER BY tp.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch destinations for dropdown
$destinations = $db->query("SELECT id, city, country FROM destinations ORDER BY city ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch airports for dropdown
$airports = $db->query("SELECT airport_id, code, city, name FROM airports ORDER BY city ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>    
    <div class="container-fluid">
        <div class="row">            
            <main>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Travel Packages</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                        <i class="fas fa-plus"></i> Add New Package
                    </button>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Package Name</th>
                                <th>Destination</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Base Price</th>
                                <th>Departures</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($packages as $package): ?>
                            <tr>
                                <td><?= $package['id'] ?></td>
                                <td><?= htmlspecialchars($package['name']) ?></td>
                                <td><?= htmlspecialchars($package['city']) ?>, <?= htmlspecialchars($package['country']) ?></td>
                                <td><?= ucfirst($package['package_type']) ?></td>
                                <td><?= $package['duration_days'] ?> days</td>
                                <td>$<?= number_format($package['base_price'], 2) ?></td>
                                <td><?= $package['departure_count'] ?></td>
                                <td>
                                    <a href="admin-package-details.php?id=<?= $package['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-warning edit-package" 
                                            data-id="<?= $package['id'] ?>"
                                            data-name="<?= htmlspecialchars($package['name']) ?>"
                                            data-destination="<?= $package['destination_id'] ?>"
                                            data-desc="<?= htmlspecialchars($package['description']) ?>"
                                            data-type="<?= $package['package_type'] ?>"
                                            data-duration="<?= $package['duration_days'] ?>"
                                            data-price="<?= $package['base_price'] ?>"
                                            data-image="<?= htmlspecialchars($package['image_url']) ?>"
                                            data-services="<?= htmlspecialchars($package['included_services']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="admin-packages.php?delete=<?= $package['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This will delete all associated departures.')">
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

    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1" aria-labelledby="addPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPackageModalLabel">Add New Travel Package</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Package Name</label>
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
                            <div class="col-md-4">
                                <label for="package_type" class="form-label">Package Type</label>
                                <select class="form-select" id="package_type" name="package_type" required>
                                    <option value="family">Family Vacation</option>
                                    <option value="honeymoon">Honeymoon</option>
                                    <option value="adventure">Adventure</option>
                                    <option value="luxury">Luxury Getaway</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="duration_days" class="form-label">Duration (days)</label>
                                <select class="form-select" id="duration_days" name="duration_days" required>
                                    <option value="3">3 Days</option>
                                    <option value="5">5 Days</option>
                                    <option value="7" selected>7 Days</option>
                                    <option value="10">10 Days</option>
                                    <option value="14">14 Days</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="base_price" class="form-label">Base Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="base_price" name="base_price" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="included_services" class="form-label">Included Services</label>
                                <textarea class="form-control" id="included_services" name="included_services" rows="2" placeholder="Flight, Hotel, Tours, Meals..."></textarea>
                            </div>
                            <div class="col-12">
                                <label for="image_url" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="image_url" name="image_url">
                            </div>
                            
                            <!-- Departure Dates Section -->
                            <div class="col-12 mt-4">
                                <h5>Departure Dates</h5>
                                <div id="departureDatesContainer">
                                    <div class="departure-date row g-3 mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-control" name="departure_dates[0][date]" min="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Departure Airport</label>
                                            <select class="form-select" name="departure_dates[0][airport]">
                                                <?php foreach ($airports as $airport): ?>
                                                    <option value="<?= $airport['airport_id'] ?>"><?= htmlspecialchars($airport['code']) ?> - <?= htmlspecialchars($airport['city']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Price</label>
                                            <input type="number" step="0.01" class="form-control" name="departure_dates[0][price]">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Available Slots</label>
                                            <input type="number" class="form-control" name="departure_dates[0][slots]">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-departure">Remove</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="addDepartureDate" class="btn btn-sm btn-secondary mt-2">
                                    <i class="fas fa-plus"></i> Add Another Departure
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_package" class="btn btn-primary">Save Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <div class="modal fade" id="editPackageModal" tabindex="-1" aria-labelledby="editPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="package_id" id="edit_package_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPackageModalLabel">Edit Travel Package</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Package Name</label>
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
                            <div class="col-md-4">
                                <label for="edit_package_type" class="form-label">Package Type</label>
                                <select class="form-select" id="edit_package_type" name="package_type" required>
                                    <option value="family">Family Vacation</option>
                                    <option value="honeymoon">Honeymoon</option>
                                    <option value="adventure">Adventure</option>
                                    <option value="luxury">Luxury Getaway</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_duration_days" class="form-label">Duration (days)</label>
                                <select class="form-select" id="edit_duration_days" name="duration_days" required>
                                    <option value="3">3 Days</option>
                                    <option value="5">5 Days</option>
                                    <option value="7">7 Days</option>
                                    <option value="10">10 Days</option>
                                    <option value="14">14 Days</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_base_price" class="form-label">Base Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="edit_base_price" name="base_price" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="edit_included_services" class="form-label">Included Services</label>
                                <textarea class="form-control" id="edit_included_services" name="included_services" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="edit_image_url" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="edit_image_url" name="image_url">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_package" class="btn btn-primary">Update Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-package').forEach(button => {
            button.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('editPackageModal'));
                
                // Fill the form with package data
                document.getElementById('edit_package_id').value = this.dataset.id;
                document.getElementById('edit_name').value = this.dataset.name;
                document.getElementById('edit_destination_id').value = this.dataset.destination;
                document.getElementById('edit_description').value = this.dataset.desc;
                document.getElementById('edit_package_type').value = this.dataset.type;
                document.getElementById('edit_duration_days').value = this.dataset.duration;
                document.getElementById('edit_base_price').value = this.dataset.price;
                document.getElementById('edit_image_url').value = this.dataset.image;
                document.getElementById('edit_included_services').value = this.dataset.services;
                
                modal.show();
            });
        });
        
        // Handle adding more departure dates
        let departureIndex = 1;
        document.getElementById('addDepartureDate').addEventListener('click', function() {
            const container = document.getElementById('departureDatesContainer');
            const newDeparture = document.createElement('div');
            newDeparture.className = 'departure-date row g-3 mb-3';
            newDeparture.innerHTML = `
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="departure_dates[${departureIndex}][date]" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Departure Airport</label>
                    <select class="form-select" name="departure_dates[${departureIndex}][airport]">
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?= $airport['id'] ?>"><?= htmlspecialchars($airport['code']) ?> - <?= htmlspecialchars($airport['city']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control" name="departure_dates[${departureIndex}][price]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Available Slots</label>
                    <input type="number" class="form-control" name="departure_dates[${departureIndex}][slots]">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-departure">Remove</button>
                </div>
            `;
            container.appendChild(newDeparture);
            departureIndex++;
        });
        
        // Handle removing departure dates
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-departure')) {
                e.target.closest('.departure-date').remove();
            }
        });
    </script>
</body>
</html>
<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check admin authentication (uncomment when ready)
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Flight Management";

// Get database connection
try {
    $db = DBConfig::getInstance()->getConnection();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_flight'])) {
            // Add new flight
            $stmt = $db->prepare("
                INSERT INTO flights (
                    flight_number, airline_id, aircraft_id,
                    departure_airport_id, arrival_airport_id,
                    departure_time, arrival_time, duration,
                    status, economy_price, business_price,
                    first_class_price, available_seats, is_international
                ) VALUES (
                    :flight_number, :airline_id, :aircraft_id,
                    :departure_airport_id, :arrival_airport_id,
                    :departure_time, :arrival_time, :duration,
                    :status, :economy_price, :business_price,
                    :first_class_price, :available_seats, :is_international
                )
            ");

            $duration = calculateFlightDuration($_POST['departure_time'], $_POST['arrival_time']);
            
            $stmt->execute([
                ':flight_number' => $_POST['flight_number'],
                ':airline_id' => $_POST['airline_id'],
                ':aircraft_id' => $_POST['aircraft_id'],
                ':departure_airport_id' => $_POST['departure_airport_id'],
                ':arrival_airport_id' => $_POST['arrival_airport_id'],
                ':departure_time' => $_POST['departure_time'],
                ':arrival_time' => $_POST['arrival_time'],
                ':duration' => $duration,
                ':status' => $_POST['status'],
                ':economy_price' => $_POST['economy_price'],
                ':business_price' => $_POST['business_price'],
                ':first_class_price' => $_POST['first_class_price'],
                ':available_seats' => $_POST['available_seats'],
                ':is_international' => isset($_POST['is_international']) ? 1 : 0
            ]);
            
            $_SESSION['message'] = 'Flight added successfully';
            
        } elseif (isset($_POST['update_flight'])) {
            // Update flight
            $stmt = $db->prepare("
                UPDATE flights SET
                    flight_number = :flight_number,
                    airline_id = :airline_id,
                    aircraft_id = :aircraft_id,
                    departure_airport_id = :departure_airport_id,
                    arrival_airport_id = :arrival_airport_id,
                    departure_time = :departure_time,
                    arrival_time = :arrival_time,
                    duration = :duration,
                    status = :status,
                    economy_price = :economy_price,
                    business_price = :business_price,
                    first_class_price = :first_class_price,
                    available_seats = :available_seats,
                    is_international = :is_international
                WHERE flight_id = :flight_id
            ");

            $duration = calculateFlightDuration($_POST['departure_time'], $_POST['arrival_time']);
            
            $stmt->execute([
                ':flight_number' => $_POST['flight_number'],
                ':airline_id' => $_POST['airline_id'],
                ':aircraft_id' => $_POST['aircraft_id'],
                ':departure_airport_id' => $_POST['departure_airport_id'],
                ':arrival_airport_id' => $_POST['arrival_airport_id'],
                ':departure_time' => $_POST['departure_time'],
                ':arrival_time' => $_POST['arrival_time'],
                ':duration' => $duration,
                ':status' => $_POST['status'],
                ':economy_price' => $_POST['economy_price'],
                ':business_price' => $_POST['business_price'],
                ':first_class_price' => $_POST['first_class_price'],
                ':available_seats' => $_POST['available_seats'],
                ':flight_id' => $_POST['flight_id'],
                ':is_international' => isset($_POST['is_international']) ? 1 : 0
            ]);
            
            $_SESSION['message'] = 'Flight updated successfully';
            
        } elseif (isset($_POST['delete_flight'])) {
            // Delete flight
            $stmt = $db->prepare("DELETE FROM flights WHERE flight_id = ?");
            $stmt->execute([$_POST['flight_id']]);
            $_SESSION['message'] = 'Flight deleted successfully';
            
        } elseif (isset($_POST['update_status'])) {
            // Update flight status only
            $stmt = $db->prepare("UPDATE flights SET status = ? WHERE flight_id = ?");
            $stmt->execute([$_POST['status'], $_POST['flight_id']]);
            $_SESSION['message'] = 'Flight status updated successfully';
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        //header("Location: flights.php");
        exit();
    }
}

// Get all flights with related information
try {
    $stmt = $db->query("
        SELECT f.*, 
            al.name as airline_name, al.logo_url,
            ac.model as aircraft_model, ac.registration_number,
            ac.economy_seats, ac.business_seats, ac.first_class_seats,
            dep.name as departure_airport, dep.code as departure_code, dep.country as departure_country,
            arr.name as arrival_airport, arr.code as arrival_code, arr.country as arrival_country
        FROM flights f
        JOIN airlines al ON f.airline_id = al.airline_id
        JOIN aircrafts ac ON f.aircraft_id = ac.aircraft_id
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        ORDER BY f.departure_time DESC
    ");
    $flights = $stmt->fetchAll();

    // Get all airlines for dropdown
    $airlines = $db->query("SELECT * FROM airlines ORDER BY name")->fetchAll();

    // Get all aircrafts for dropdown
    $aircrafts = $db->query("SELECT * FROM aircrafts ORDER BY model")->fetchAll();

    // Get all airports for dropdown
    $airports = $db->query("SELECT * FROM airports ORDER BY country, city")->fetchAll();
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .airline-logo-sm {
            width: 24px;
            height: 24px;
            object-fit: contain;
        }
        .status-select {
            width: 120px;
            display: inline-block;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .admin-container {
            padding: 20px;
        }
        .seat-config {
            display: block;
            margin-top: 5px;
            font-size: 0.85em;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1 class="mb-4">Flight Management</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">All Flights</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFlightModal">
                <i class="fas fa-plus me-1"></i> Add New Flight
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Flight #</th>
                            <th>Airline</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Aircraft</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $flight): ?>
                        <tr>
                            <td><?= htmlspecialchars($flight['flight_number']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($flight['logo_url'])): ?>
                                    <img src="../<?= htmlspecialchars($flight['logo_url']) ?>" 
                                         alt="<?= htmlspecialchars($flight['airline_name']) ?>" 
                                         class="airline-logo-sm me-2">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($flight['airline_name']) ?>
                                </div>
                            </td>
                            <td>
                                <?= $flight['departure_code'] ?> → <?= $flight['arrival_code'] ?>
                                <div class="text-muted small">
                                    <?= htmlspecialchars($flight['departure_airport']) ?> to 
                                    <?= htmlspecialchars($flight['arrival_airport']) ?>
                                </div>
                            </td>
                            <td>
                                <?= formatDateTime($flight['departure_time'], 'M j, Y H:i') ?>
                                <div class="text-muted small">
                                    <?= formatFlightDuration($flight['duration']) ?>
                                </div>
                            </td>
                            <td><?= formatDateTime($flight['arrival_time'], 'M j, Y H:i') ?></td>
                            <td>
                                <?= htmlspecialchars($flight['aircraft_model']) ?>
                                <div class="text-muted small">
                                    <?= htmlspecialchars($flight['registration_number']) ?>
                                    <div class="seat-config">
                                        Economy: <?= $flight['economy_seats'] ?>, 
                                        Business: <?= $flight['business_seats'] ?>, 
                                        First: <?= $flight['first_class_seats'] ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= $flight['is_international'] ? 'International' : 'Domestic' ?>
                                <?php if ($flight['is_international']): ?>
                                    <i class="fas fa-globe text-primary ms-1"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="flight_id" value="<?= $flight['flight_id'] ?>">
                                    <select name="status" class="form-select form-select-sm status-select" 
                                            data-flight-id="<?= $flight['flight_id'] ?>">
                                        <option value="scheduled" <?= $flight['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                        <option value="delayed" <?= $flight['status'] === 'delayed' ? 'selected' : '' ?>>Delayed</option>
                                        <option value="departed" <?= $flight['status'] === 'departed' ? 'selected' : '' ?>>Departed</option>
                                        <option value="arrived" <?= $flight['status'] === 'arrived' ? 'selected' : '' ?>>Arrived</option>
                                        <option value="cancelled" <?= $flight['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-link p-0 ms-1 d-none status-update-btn">
                                        <i class="fas fa-check text-success"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1 edit-flight" 
                                        data-flight='<?= htmlspecialchars(json_encode($flight)) ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="flight_id" value="<?= $flight['flight_id'] ?>">
                                    <button type="submit" name="delete_flight" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this flight?')">
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

<!-- Add Flight Modal -->
<div class="modal fade" id="addFlightModal" tabindex="-1" aria-labelledby="addFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFlightModalLabel">Add New Flight</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Flight Number</label>
                            <input type="text" name="flight_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Airline</label>
                            <select name="airline_id" class="form-select" required>
                                <option value="">Select Airline</option>
                                <?php foreach ($airlines as $airline): ?>
                                <option value="<?= $airline['airline_id'] ?>">
                                    <?= htmlspecialchars($airline['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Aircraft</label>
                            <select name="aircraft_id" class="form-select" required id="aircraftSelect">
                                <option value="">Select Aircraft</option>
                                <?php foreach ($aircrafts as $aircraft): ?>
                                <option value="<?= $aircraft['aircraft_id'] ?>" 
                                        data-seats="<?= $aircraft['total_seats'] ?>"
                                        data-economy="<?= $aircraft['economy_seats'] ?>"
                                        data-business="<?= $aircraft['business_seats'] ?>"
                                        data-first="<?= $aircraft['first_class_seats'] ?>">
                                    <?= htmlspecialchars($aircraft['model']) ?> (<?= $aircraft['registration_number'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Available Seats</label>
                            <input type="number" name="available_seats" class="form-control" required id="availableSeats">
                            <small class="text-muted seat-config" id="seatConfigText"></small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Departure Airport</label>
                            <select name="departure_airport_id" class="form-select" required>
                                <option value="">Select Airport</option>
                                <?php foreach ($airports as $airport): ?>
                                <option value="<?= $airport['airport_id'] ?>">
                                    <?= htmlspecialchars($airport['city']) ?> (<?= $airport['code'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Arrival Airport</label>
                            <select name="arrival_airport_id" class="form-select" required>
                                <option value="">Select Airport</option>
                                <?php foreach ($airports as $airport): ?>
                                <option value="<?= $airport['airport_id'] ?>">
                                    <?= htmlspecialchars($airport['city']) ?> (<?= $airport['code'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_international" class="form-check-input" id="is_international" value="1">
                        <label class="form-check-label" for="is_international">International Flight</label>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Departure Time</label>
                            <input type="datetime-local" name="departure_time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Arrival Time</label>
                            <input type="datetime-local" name="arrival_time" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Economy Price ($)</label>
                            <input type="number" step="0.01" name="economy_price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Business Price ($)</label>
                            <input type="number" step="0.01" name="business_price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Class Price ($)</label>
                            <input type="number" step="0.01" name="first_class_price" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="delayed">Delayed</option>
                            <option value="departed">Departed</option>
                            <option value="arrived">Arrived</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_flight" class="btn btn-primary">Save Flight</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Flight Modal -->
<div class="modal fade" id="editFlightModal" tabindex="-1" aria-labelledby="editFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="flight_id" id="edit_flight_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFlightModalLabel">Edit Flight</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Flight Number</label>
                            <input type="text" name="flight_number" id="edit_flight_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Airline</label>
                            <select name="airline_id" id="edit_airline_id" class="form-select" required>
                                <option value="">Select Airline</option>
                                <?php foreach ($airlines as $airline): ?>
                                <option value="<?= $airline['airline_id'] ?>">
                                    <?= htmlspecialchars($airline['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Aircraft</label>
                            <select name="aircraft_id" id="edit_aircraft_id" class="form-select" required>
                                <option value="">Select Aircraft</option>
                                <?php foreach ($aircrafts as $aircraft): ?>
                                <option value="<?= $aircraft['aircraft_id'] ?>" 
                                        data-seats="<?= $aircraft['total_seats'] ?>"
                                        data-economy="<?= $aircraft['economy_seats'] ?>"
                                        data-business="<?= $aircraft['business_seats'] ?>"
                                        data-first="<?= $aircraft['first_class_seats'] ?>">
                                    <?= htmlspecialchars($aircraft['model']) ?> (<?= $aircraft['registration_number'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Available Seats</label>
                            <input type="number" name="available_seats" id="edit_available_seats" class="form-control" required>
                            <small class="text-muted seat-config" id="editSeatConfigText"></small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Departure Airport</label>
                            <select name="departure_airport_id" id="edit_departure_airport_id" class="form-select" required>
                                <option value="">Select Airport</option>
                                <?php foreach ($airports as $airport): ?>
                                <option value="<?= $airport['airport_id'] ?>">
                                    <?= htmlspecialchars($airport['city']) ?> (<?= $airport['code'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Arrival Airport</label>
                            <select name="arrival_airport_id" id="edit_arrival_airport_id" class="form-select" required>
                                <option value="">Select Airport</option>
                                <?php foreach ($airports as $airport): ?>
                                <option value="<?= $airport['airport_id'] ?>">
                                    <?= htmlspecialchars($airport['city']) ?> (<?= $airport['code'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_international" class="form-check-input" id="is_international" value="1">
                        <label class="form-check-label" for="is_international">International Flight</label>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Departure Time</label>
                            <input type="datetime-local" name="departure_time" id="edit_departure_time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Arrival Time</label>
                            <input type="datetime-local" name="arrival_time" id="edit_arrival_time" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Economy Price ($)</label>
                            <input type="number" step="0.01" name="economy_price" id="edit_economy_price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Business Price ($)</label>
                            <input type="number" step="0.01" name="business_price" id="edit_business_price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Class Price ($)</label>
                            <input type="number" step="0.01" name="first_class_price" id="edit_first_class_price" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="delayed">Delayed</option>
                            <option value="departed">Departed</option>
                            <option value="arrived">Arrived</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_flight" class="btn btn-primary">Update Flight</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Function to update seat configuration display
function updateSeatConfig(selectElement, configTextElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    if (selectedOption && selectedOption.dataset.seats) {
        const economy = selectedOption.dataset.economy || 0;
        const business = selectedOption.dataset.business || 0;
        const first = selectedOption.dataset.first || 0;
        
        configTextElement.textContent = `Seats: Economy ${economy}, Business ${business}, First ${first}`;
    } else {
        configTextElement.textContent = '';
    }
}

// Handle edit flight button clicks
document.querySelectorAll('.edit-flight').forEach(button => {
    button.addEventListener('click', function() {
        const flight = JSON.parse(this.dataset.flight);
        
        // Fill the edit form
        document.getElementById('edit_flight_id').value = flight.flight_id;
        document.getElementById('edit_flight_number').value = flight.flight_number;
        document.getElementById('edit_airline_id').value = flight.airline_id;
        document.getElementById('edit_aircraft_id').value = flight.aircraft_id;
        document.getElementById('edit_available_seats').value = flight.available_seats;
        document.getElementById('edit_departure_airport_id').value = flight.departure_airport_id;
        document.getElementById('edit_arrival_airport_id').value = flight.arrival_airport_id;
        
        // Format datetime for the input fields (remove seconds)
        document.getElementById('edit_departure_time').value = flight.departure_time.slice(0, 16);
        document.getElementById('edit_arrival_time').value = flight.arrival_time.slice(0, 16);
        document.getElementById('is_international').checked = flight.is_international == 1;
        
        document.getElementById('edit_economy_price').value = flight.economy_price; 
        document.getElementById('edit_business_price').value = flight.business_price;
        document.getElementById('edit_first_class_price').value = flight.first_class_price; 
        document.getElementById('edit_status').value = flight.status;
        
        // Trigger change to show seat config
        const aircraftSelect = document.getElementById('edit_aircraft_id');
        aircraftSelect.dispatchEvent(new Event('change'));
        
        // Show the modal
        const editFlightModal = new bootstrap.Modal(document.getElementById('editFlightModal'));
        editFlightModal.show();
    });
});

// Add event listener for aircraft select in add modal
document.getElementById('aircraftSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption && selectedOption.dataset.seats) {
        document.getElementById('availableSeats').value = selectedOption.dataset.seats;
        updateSeatConfig(this, document.getElementById('seatConfigText'));
    }
});

// Add event listener for aircraft select in edit modal
document.getElementById('edit_aircraft_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption && selectedOption.dataset.seats) {
        document.getElementById('edit_available_seats').value = selectedOption.dataset.seats;
        updateSeatConfig(this, document.getElementById('editSeatConfigText'));
    }
});

// Handle status select change
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const updateBtn = this.nextElementSibling;
        updateBtn.classList.remove('d-none');
    });
});

// Initialize Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
</body>
</html>
<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is logged in and has appropriate permissions
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login.php');
// }

// Get all flights for dropdown
try {
    global $db;
    $flights = [];
    $stmt = $db->query("
        SELECT f.flight_id, f.flight_number, 
               a1.name AS departure_airport, a2.name AS arrival_airport,
               f.departure_time
        FROM flights f
        JOIN airports a1 ON f.departure_airport_id = a1.airport_id
        JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
        ORDER BY f.departure_time DESC
    ");
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    handleException($e);
}

// Process flight selection
$selectedFlightId = $_GET['flight_id'] ?? null;
$passengers = [];

if ($selectedFlightId) {
    try {
        // Get flight details
        $stmt = $db->prepare("
            SELECT f.*, 
                   a1.name AS departure_airport, 
                   a2.name AS arrival_airport,
                   al.name,
                   ac.model AS aircraft_model
            FROM flights f
            JOIN airlines al ON f.airline_id = al.airline_id
            JOIN aircrafts ac ON f.aircraft_id = ac.aircraft_id
            JOIN airports a1 ON f.departure_airport_id = a1.airport_id
            JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
            WHERE f.flight_id = ?
        ");
        $stmt->execute([$selectedFlightId]);
        $flightDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$flightDetails) {
            throw new Exception("Flight not found");
        }

        // Get passengers for this flight
        $stmt = $db->prepare("
            SELECT p.*, 
                   b.booking_number,
                   CONCAT(u.first_name, ' ', u.last_name) AS booked_by,
                   u.userEmail AS booked_by_email
            FROM passengers p
            JOIN bookings b ON p.booking_id = b.booking_id
            JOIN users u ON b.user_id = u.user_id
            WHERE b.flight_id = ? OR b.return_flight_id = ?
            ORDER BY p.is_primary DESC, p.last_name, p.first_name
        ");
        $stmt->execute([$selectedFlightId, $selectedFlightId]);
        $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        handleException($e);
    } catch (Exception $e) {
        showToast([$e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Passenger List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .flight-header {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .passenger-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 15px;
        }
        .primary-passenger {
            border-left-color: #198754;
            background-color: #f8fff8;
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4"><i class="bi bi-people-fill"></i> Flight Passenger List</h2>
                
                <!-- Flight Selection Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" action="index.php">
                            <input type="hidden" name="page" value="passenger_list_by_flight">
                            <div class="col-md-8">
                                <label for="flight_id" class="form-label">Select Flight</label>
                                <select class="form-select" id="flight_id" name="flight_id" required>
                                    <option value="">-- Select a Flight --</option>
                                    <?php foreach ($flights as $flight): ?>
                                        <option value="<?= $flight['flight_id'] ?>" 
                                            <?= ($selectedFlightId == $flight['flight_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($flight['flight_number']) ?> - 
                                            <?= htmlspecialchars($flight['departure_airport']) ?> to 
                                            <?= htmlspecialchars($flight['arrival_airport']) ?> - 
                                            <?= formatDateTime($flight['departure_time'], 'M j, Y H:i') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Show Passengers
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($selectedFlightId && $flightDetails): ?>
                    <!-- Flight Details -->
                    <div class="card flight-header mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4><?= htmlspecialchars($flightDetails['name']) ?> Flight <?= htmlspecialchars($flightDetails['flight_number']) ?></h4>
                                    <p class="mb-1">
                                        <strong>Route:</strong> 
                                        <?= htmlspecialchars($flightDetails['departure_airport']) ?> 
                                        <i class="bi bi-arrow-right"></i> 
                                        <?= htmlspecialchars($flightDetails['arrival_airport']) ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Departure:</strong> 
                                        <?= formatDateTime($flightDetails['departure_time'], 'M j, Y H:i') ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Arrival:</strong> 
                                        <?= formatDateTime($flightDetails['arrival_time'], 'M j, Y H:i') ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Aircraft:</strong> <?= htmlspecialchars($flightDetails['aircraft_model']) ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Duration:</strong> <?= formatFlightDuration($flightDetails['duration']) ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Status:</strong> <?= getFlightStatusBadge($flightDetails['status']) ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Passengers:</strong> <?= count($passengers) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger List -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Passenger Manifest</h5>
                            <div>
                                <a href="print_file/export_passengers.php?flight_id=<?= $selectedFlightId ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-download"></i> Export to CSV
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Passenger Name</th>
                                            <th>Type</th>
                                            <th>Booking Ref</th>
                                            <th>Booked By</th>
                                            <th>Seat</th>
                                            <th>Class</th>
                                            <th>Passport</th>
                                            <th>DOB</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($passengers)): ?>
                                            <tr>
                                                <td colspan="10" class="text-center py-4">No passengers found for this flight.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($passengers as $index => $passenger): ?>
                                                <tr class="<?= $passenger['is_primary'] ? 'table-primary' : '' ?>">
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']) ?>
                                                        <?php if ($passenger['is_primary']): ?>
                                                            <span class="badge bg-primary ms-1">Primary</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= ucfirst($passenger['type']) ?></td>
                                                    <td><?= htmlspecialchars($passenger['booking_number']) ?></td>
                                                    <td>
                                                        <div><?= htmlspecialchars($passenger['booked_by']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($passenger['booked_by_email']) ?></small>
                                                    </td>
                                                    <td><?= $passenger['seat_number'] ? htmlspecialchars($passenger['seat_number']) : 'Not assigned' ?></td>
                                                    <td><?= ucfirst($passenger['seat_class']) ?></td>
                                                    <td><?= $passenger['passport_number'] ? htmlspecialchars($passenger['passport_number']) : 'N/A' ?></td>
                                                    <td><?= $passenger['date_of_birth'] ? formatDate($passenger['date_of_birth'], 'M j, Y') : 'N/A' ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#passengerModal<?= $index ?>">
                                                            <i class="bi bi-eye"></i> View
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Detail Modals -->
                    <?php foreach ($passengers as $index => $passenger): ?>
                        <div class="modal fade" id="passengerModal<?= $index ?>" tabindex="-1" aria-labelledby="passengerModalLabel<?= $index ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="passengerModalLabel<?= $index ?>">
                                            Passenger Details - <?= htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Personal Information</h6>
                                                <p><strong>Name:</strong> <?= htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']) ?></p>
                                                <p><strong>Type:</strong> <?= ucfirst($passenger['type']) ?></p>
                                                <p><strong>Date of Birth:</strong> <?= $passenger['date_of_birth'] ? formatDate($passenger['date_of_birth'], 'M j, Y') : 'N/A' ?></p>
                                                <p><strong>Passport:</strong> <?= $passenger['passport_number'] ? htmlspecialchars($passenger['passport_number']) : 'N/A' ?></p>
                                                <p><strong>Email:</strong> <?= $passenger['email'] ? htmlspecialchars($passenger['email']) : 'N/A' ?></p>
                                                <p><strong>Phone:</strong> <?= $passenger['phone'] ? htmlspecialchars($passenger['phone']) : 'N/A' ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Booking Information</h6>
                                                <p><strong>Booking Reference:</strong> <?= htmlspecialchars($passenger['booking_number']) ?></p>
                                                <p><strong>Booked By:</strong> <?= htmlspecialchars($passenger['booked_by']) ?></p>
                                                <p><strong>Booked Email:</strong> <?= htmlspecialchars($passenger['booked_by_email']) ?></p>
                                                <p><strong>Seat:</strong> <?= $passenger['seat_number'] ? htmlspecialchars($passenger['seat_number']) : 'Not assigned' ?></p>
                                                <p><strong>Class:</strong> <?= ucfirst($passenger['seat_class']) ?></p>
                                                <?php if ($passenger['special_requests']): ?>
                                                    <p><strong>Special Requests:</strong> <?= htmlspecialchars($passenger['special_requests']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="edit_passenger.php?passenger_id=<?= $passenger['passenger_id'] ?>" class="btn btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh the page every 5 minutes to get updated passenger list
        setTimeout(function() {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>
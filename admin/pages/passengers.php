<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$booking_id = $_GET['id'];
$db = DBConfig::getInstance()->getConnection();

// Fetch booking details
$stmt = $db->prepare("SELECT b.*, f.flight_number, 
                     a1.name as departure_airport, a2.name as arrival_airport,
                     al.name as airline_name, u.userName, u.userEmail
                     FROM bookings b
                     JOIN flights f ON b.flight_id = f.flight_id
                     JOIN airports a1 ON f.departure_airport_id = a1.airport_id
                     JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
                     JOIN airlines al ON f.airline_id = al.airline_id
                     JOIN users u ON b.user_id = u.user_id
                     WHERE b.booking_id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    $_SESSION['error'] = "Booking not found.";
    header("Location: bookings.php");
    exit();
}

// Fetch passengers for this booking
$stmt = $db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Passenger List</h2>
        <a href="bookings.php" class="btn btn-secondary">Back to Bookings</a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h4>Booking Information</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Booking Reference:</strong> <?= htmlspecialchars($booking['booking_id']) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Flight:</strong> <?= htmlspecialchars($booking['airline_name']) ?> <?= htmlspecialchars($booking['flight_number']) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Route:</strong> <?= htmlspecialchars($booking['departure_airport']) ?> to <?= htmlspecialchars($booking['arrival_airport']) ?></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Customer:</strong> <?= htmlspecialchars($booking['userName']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?= htmlspecialchars($booking['userEmail']) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4>Passengers (<?= count($passengers) ?>)</h4>
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addPassengerModal">
                    Add Passenger
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($passengers) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Passport</th>
                                <th>Date of Birth</th>
                                <th>Class</th>
                                <th>Seat</th>
                                <th>Special Requests</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($passengers as $passenger): ?>
                                <tr>
                                    <td><?= htmlspecialchars($passenger['first_name']) ?> <?= htmlspecialchars($passenger['last_name']) ?></td>
                                    <td><?= $passenger['passport_number'] ? htmlspecialchars($passenger['passport_number']) : 'N/A' ?></td>
                                    <td><?= $passenger['date_of_birth'] ? date('M j, Y', strtotime($passenger['date_of_birth'])) : 'N/A' ?></td>
                                    <td><?= ucfirst($passenger['seat_class']) ?></td>
                                    <td>
                                        <?php if ($passenger['seat_number']): ?>
                                            <?= htmlspecialchars($passenger['seat_number']) ?>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary assign-seat" 
                                                    data-passenger-id="<?= $passenger['passenger_id'] ?>">
                                                Assign Seat
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $passenger['special_requests'] ? htmlspecialchars($passenger['special_requests']) : 'None' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info edit-passenger" 
                                                data-passenger-id="<?= $passenger['passenger_id'] ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-passenger" 
                                                data-passenger-id="<?= $passenger['passenger_id'] ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No passengers found for this booking.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle assign seat button click
    $('.assign-seat').click(function() {
        var passengerId = $(this).data('passenger-id');
        $('#assign_seat_passenger_id').val(passengerId);
        $('#assignSeatModal').modal('show');
    });
    
    // Handle form submissions via AJAX
    $('#addPassengerForm, #assignSeatForm').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    location.reload(); // Refresh the page to show changes
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Handle edit passenger (would need similar modal setup)
    $('.edit-passenger').click(function() {
        // Implementation would be similar to add/assign seat
        alert('Edit functionality would be implemented here');
    });
    
    // Handle delete passenger
    $('.delete-passenger').click(function() {
        if (confirm('Are you sure you want to delete this passenger?')) {
            var passengerId = $(this).data('passenger-id');
            
            $.ajax({
                url: 'process_passenger.php',
                type: 'POST',
                data: {
                    action: 'delete',
                    passenger_id: passengerId
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        location.reload(); // Refresh the page to show changes
                    } else {
                        alert('Error: ' + result.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
});
</script>
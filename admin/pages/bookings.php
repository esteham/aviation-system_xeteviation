<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
//     header("Location: ../login.php");
//     exit();
// }
$db = DBConfig::getInstance()->getConnection();

// Handle status changes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $stmt = $db->prepare("UPDATE bookings SET payment_status = ? WHERE booking_id = ?");
    $stmt->execute([$status, $booking_id]);
    
    $_SESSION['success'] = "Booking status updated successfully.";
    echo "<script>window.location.href='index.php?page=bookings';</script>";
    exit();
}

// Fetch all bookings
$query = "SELECT b.*, f.flight_number, u.userName, u.userEmail, 
          a1.name as departure_airport, a2.name as arrival_airport,
          al.name as airline_name
          FROM bookings b
          JOIN flights f ON b.flight_id = f.flight_id
          JOIN users u ON b.user_id = u.user_id
          JOIN airports a1 ON f.departure_airport_id = a1.airport_id
          JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
          JOIN airlines al ON f.airline_id = al.airline_id
          ORDER BY b.booking_date DESC";
$bookings = $db->query($query);

?>

<div class="container mt-5">
    <h2>Manage Bookings</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <!-- <th>Booking Ref</th> -->
                    <th>User</th>
                    <th>Flight</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings->fetchAll() as $booking): ?>
                    <tr>
                        <!-- <td><?= $booking['booking_reference'] ?></td> -->
                        <td>
                            <?= $booking['userName'] ?><br>
                            <small><?= $booking['userEmail'] ?></small>
                        </td>
                        <td>
                            <?= $booking['airline_name'] ?> <?= $booking['flight_number'] ?><br>
                            <small><?= $booking['departure_airport'] ?> to <?= $booking['arrival_airport'] ?></small>
                        </td>
                        <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                        <td>$<?= number_format($booking['total_price'], 2) ?></td>
                        <td>
                            <form method="post" class="form-inline">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="pending" <?= $booking['payment_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="paid" <?= $booking['payment_status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="refunded" <?= $booking['payment_status'] == 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                    <option value="cancelled" <?= $booking['payment_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary ml-2">Update</button>
                            </form>
                        </td>
                        <td>
                            <a href="index.php?page=booking_details&id=<?= $booking['booking_id'] ?>" class="btn btn-sm btn-info">View</a>
                            <a href="index.php?page=passengers&id=<?= $booking['booking_id'] ?>" class="btn btn-sm btn-secondary">Passengers</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


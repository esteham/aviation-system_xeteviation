<?php
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$searchTerm = isset($_POST['search']) ? trim($_POST['search']) : '';
$filterValue = isset($_POST['filter']) ? $_POST['filter'] : 'all';

// Base query
$query = "SELECT b.*, f.flight_number, f.departure_time, f.arrival_time, 
          a1.name as departure_airport, a2.name as arrival_airport,
          al.name as airline_name
          FROM bookings b
          JOIN flights f ON b.flight_id = f.flight_id
          JOIN airports a1 ON f.departure_airport_id = a1.airport_id
          JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
          JOIN airlines al ON f.airline_id = al.airline_id
          WHERE b.user_id = :user_id
          AND b.status = 'confirmed'";

$params = [
    ':user_id' => $_SESSION['user_id']
];

// Add search conditions if search term exists
if (!empty($searchTerm)) {
    $query .= " AND (f.flight_number LIKE :search 
                OR a1.name LIKE :search 
                OR a2.name LIKE :search 
                OR al.name LIKE :search)";
    $params[':search'] = "%$searchTerm%";
}

// Add filter condition
if ($filterValue !== 'all') {
    $query .= " AND b.payment_status = :payment_status";
    $params[':payment_status'] = $filterValue;
}

$query .= " ORDER BY b.booking_date DESC";

$stmt = $db->prepare($query);

// Bind all parameters at once
foreach ($params as $key => &$val) {
    $stmt->bindParam($key, $val);
}

$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($bookings) > 0) {
    foreach ($bookings as $booking): ?>
        <div class="booking-card <?= htmlspecialchars($booking['payment_status']) ?>">
            <div class="booking-airline">
                <img src="<?= htmlspecialchars($booking['airline_logo'] ?? 'assets/images/airline-default.png') ?>" 
                     alt="<?= htmlspecialchars($booking['airline_name']) ?>" class="airline-logo">
                <span class="flight-number"><?= htmlspecialchars($booking['flight_number']) ?></span>
                <span class="badge <?= $booking['payment_status'] == 'paid' ? 'badge-success' : 'badge-warning' ?>">
                    <?= ucfirst(htmlspecialchars($booking['payment_status'])) ?>
                </span>
            </div>
            
            <div class="booking-route">
                <div class="route-departure">
                    <div class="route-time"><?= date('H:i', strtotime($booking['departure_time'])) ?></div>
                    <div class="route-airport"><?= htmlspecialchars($booking['departure_airport']) ?></div>
                    <div class="route-date"><?= date('M d, Y', strtotime($booking['departure_time'])) ?></div>
                </div>
                
                <div class="route-stops">
                    <div class="route-duration">
                        <?php
                        $departure = new DateTime($booking['departure_time']);
                        $arrival = new DateTime($booking['arrival_time']);
                        $interval = $departure->diff($arrival);
                        echo $interval->format('%hh %im');
                        ?>
                    </div>
                    <div class="route-line"></div>
                </div>
                
                <div class="route-arrival">
                    <div class="route-time"><?= date('H:i', strtotime($booking['arrival_time'])) ?></div>
                    <div class="route-airport"><?= htmlspecialchars($booking['arrival_airport']) ?></div>
                    <div class="route-date"><?= date('M d, Y', strtotime($booking['arrival_time'])) ?></div>
                </div>
            </div>
            
            <div class="booking-details">
                <div class="detail-item">
                    <span class="detail-label">Booking Ref:</span>
                    <span class="detail-value">#<?= strtoupper(substr(md5($booking['booking_id']), 0, 8)) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Booking Date:</span>
                    <span class="detail-value"><?= date('M d, Y', strtotime($booking['booking_date'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value price">$<?= number_format($booking['total_price'], 2) ?></span>
                </div>
            </div>
            
            <div class="booking-actions">
                <a href="booking_details.php?id=<?= $booking['booking_id'] ?>" class="btn btn-outline">
                    <i class="fas fa-info-circle"></i> Details
                </a>
                <?php if ($booking['payment_status'] == 'pending'): ?>
                    <a href="payment.php?booking_id=<?= $booking['booking_id'] ?>" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Pay Now
                    </a>
                <?php endif; ?>
                <button class="btn btn-outline download-ticket" <?= $booking['payment_status'] == 'paid' ? '' : 'disabled' ?>>
                    <i class="fas fa-download"></i> Ticket
                </button>
            </div>
        </div>
    <?php endforeach;
} else {
    echo '<div class="empty-search">No bookings found matching your criteria.</div>';
}
?>
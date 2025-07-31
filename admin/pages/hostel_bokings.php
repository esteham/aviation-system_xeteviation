<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if user is admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
//     header("Location: ../login.php");
//     exit();
// }
$db = DBConfig::getInstance()->getConnection();

// Helper functions
function getTotalBookings($db) {
    $stmt = $db->query("SELECT COUNT(*) FROM bookings");
    return $stmt->fetchColumn();
}

function getTotalRevenue($db) {
    $stmt = $db->query("SELECT SUM(amount) FROM bookings WHERE status = 'completed'");
    return $stmt->fetchColumn() ?? 0;
}

function getTotalHotels($db) {
    $stmt = $db->query("SELECT COUNT(*) FROM hotels");
    return $stmt->fetchColumn();
}

function getActivePackages($db) {
    $stmt = $db->query("SELECT COUNT(*) FROM travel_packages");
    return $stmt->fetchColumn();
}

function getRecentBookings($db) {
    $stmt = $db->query("SELECT b.id, b.type, u.username as customer_name, b.booking_date, b.amount, b.status 
                         FROM hostel_booking b
                         JOIN users u ON b.user_id = u.user_id
                         ORDER BY b.booking_date DESC LIMIT 5");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStatusBadge($status) {
    switch(strtolower($status)) {
        case 'completed': return 'success';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>

                <!-- Recent Bookings -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach(getRecentBookings($db) as $booking): ?>
                                    <tr>
                                        <td><?= $booking['id'] ?></td>
                                        <td><?= $booking['type'] ?></td>
                                        <td><?= $booking['customer_name'] ?></td>
                                        <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                        <td>$<?= number_format($booking['amount'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= getStatusBadge($booking['status']) ?>">
                                                <?= $booking['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view-booking.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activate current nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            if(link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>


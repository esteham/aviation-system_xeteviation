<?php
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';
include __DIR__ . '/includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's bookings
$stmt = $db->prepare("SELECT b.*, f.flight_number, f.departure_time, f.arrival_time, 
                       a1.name as departure_airport, a2.name as arrival_airport,
                       al.name as airline_name
                       FROM bookings b
                       JOIN flights f ON b.flight_id = f.flight_id
                       JOIN airports a1 ON f.departure_airport_id = a1.airport_id
                       JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
                       JOIN airlines al ON f.airline_id = al.airline_id
                       WHERE b.user_id = :user_id
                       AND b.status = 'confirmed'  -- FIXED this line
                       ORDER BY b.booking_date DESC");


$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "My Profile | Aviation System";
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <img src="assets/images/default-avatar.jpg" alt="User Avatar" class="avatar-img">
        </div>
        <div class="profile-info">
            <h1>My Bookings</h1>
            <p class="text-muted">Manage your flight reservations and payments</p>
        </div>
    </div>

    <div class="dashboard-cards">
        <div class="dashboard-card">
            <div class="card-icon bg-primary">
                <i class="fas fa-plane"></i>
            </div>
            <div class="card-info">
                <h3><?= count($bookings) ?></h3>
                <p>Total Bookings</p>
            </div>
        </div>
        
        <?php 
        $paidCount = array_reduce($bookings, function($carry, $item) {
            return $carry + ($item['payment_status'] === 'paid' ? 1 : 0);
        }, 0);
        ?>
        <div class="dashboard-card">
            <div class="card-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-info">
                <h3><?= $paidCount ?></h3>
                <p>Confirmed</p>
            </div>
        </div>
        
        <?php 
        $pendingCount = array_reduce($bookings, function($carry, $item) {
            return $carry + ($item['payment_status'] === 'pending' ? 1 : 0);
        }, 0);
        ?>
        <div class="dashboard-card">
            <div class="card-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-info">
                <h3><?= $pendingCount ?></h3>
                <p>Pending Payment</p>
            </div>
        </div>
    </div>

    <?php if (count($bookings) > 0): ?>
        <div class="bookings-container">
            <div class="bookings-header">
                <h2>Booking Informations</h2>
                <div class="search-filter">
                    <input type="text" placeholder="Search bookings..." class="search-input">
                    <select class="filter-select">
                        <option value="all">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>

            <div class="booking-cards">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card <?= $booking['payment_status'] ?>">
                        <div class="booking-airline">
                            <img src="<?= htmlspecialchars($booking['airline_logo'] ?? 'assets/images/airline-default.png') ?>" alt="<?= htmlspecialchars($booking['airline_name']) ?>" class="airline-logo">
                            <span class="flight-number"><?= htmlspecialchars($booking['flight_number']) ?></span>
                            <span class="badge <?= $booking['payment_status'] == 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($booking['payment_status']) ?>
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
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-plane-slash"></i>
            </div>
            <h3>No Bookings Found</h3>
            <p>You don't have any bookings yet. Start your journey by booking a flight!</p>
            <a href="flights.php" class="btn btn-primary">
                <i class="fas fa-search"></i> Find Flights
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    const filterSelect = document.querySelector('.filter-select');
    const bookingCards = document.querySelector('.booking-cards');
    
    // Function to handle search and filter
    function handleSearchFilter() {
        const searchTerm = searchInput.value.toLowerCase();
        const filterValue = filterSelect.value;
        
        fetch('ajax/search_bookings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `search=${encodeURIComponent(searchTerm)}&filter=${filterValue}`
        })
        .then(response => response.text())
        .then(data => {
            bookingCards.innerHTML = data;
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Event listeners
    searchInput.addEventListener('input', handleSearchFilter);
    filterSelect.addEventListener('change', handleSearchFilter);
});
</script>

<style>
:root {
    --primary-color: #4361ee;
    --secondary-color: #3f37c9;
    --success-color: #4cc9f0;
    --warning-color: #f8961e;
    --danger-color: #f72585;
    --light-color: #f8f9fa;
    --dark-color: #212529;
    --gray-color: #6c757d;
    --border-radius: 12px;
    --box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

.profile-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin-bottom: 3rem;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid white;
    box-shadow: var(--box-shadow);
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.dashboard-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.dashboard-card:hover {
    transform: translateY(-5px);
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.card-info h3 {
    font-size: 1.8rem;
    margin-bottom: 0.2rem;
    color: var(--dark-color);
}

.card-info p {
    color: var(--gray-color);
    margin: 0;
}

.bookings-container {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    margin-bottom: 3rem;
}

.bookings-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.search-filter {
    display: flex;
    gap: 1rem;
}

.search-input {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    min-width: 250px;
}

.filter-select {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    background: white;
}

.empty-search {
    padding: 2rem;
    text-align: center;
    color: var(--gray-color);
    font-size: 1.1rem;
    grid-column: 1 / -1;
}

.booking-cards {
    padding: 1.5rem;
    display: grid;
    gap: 1.5rem;
}

.booking-card {
    border-radius: var(--border-radius);
    border: 1px solid #eee;
    overflow: hidden;
    transition: var(--transition);
}

.booking-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.booking-card.pending {
    border-left: 4px solid var(--warning-color);
}

.booking-card.paid {
    border-left: 4px solid var(--success-color);
}

.booking-airline {
    padding: 1rem 1.5rem;
    background: #f9f9f9;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid #eee;
}

.airline-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.flight-number {
    font-weight: bold;
    margin-right: auto;
}

.booking-route {
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.route-departure, .route-arrival {
    flex: 1;
    min-width: 150px;
}

.route-time {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--dark-color);
}

.route-airport {
    font-size: 1rem;
    color: var(--dark-color);
    margin: 0.3rem 0;
}

.route-date {
    font-size: 0.9rem;
    color: var(--gray-color);
}

.route-stops {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 100px;
}

.route-duration {
    font-size: 0.9rem;
    color: var(--gray-color);
    margin-bottom: 0.5rem;
}

.route-line {
    width: 100%;
    height: 1px;
    background: #ddd;
    position: relative;
}

.route-line::before {
    content: '';
    position: absolute;
    width: 8px;
    height: 8px;
    background: var(--primary-color);
    border-radius: 50%;
    top: -4px;
    left: 50%;
    transform: translateX(-50%);
}

.booking-details {
    padding: 1rem 1.5rem;
    background: #f9f9f9;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 0.8rem;
    color: var(--gray-color);
    margin-bottom: 0.3rem;
}

.detail-value {
    font-size: 1rem;
    color: var(--dark-color);
    font-weight: 500;
}

.price {
    color: var(--primary-color);
    font-weight: bold;
}

.booking-actions {
    padding: 1rem 1.5rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    border: none;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
}

.btn-outline {
    background: white;
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
}

.btn-outline:hover {
    background: #f0f4ff;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.empty-icon {
    font-size: 3rem;
    color: var(--gray-color);
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.empty-state p {
    color: var(--gray-color);
    margin-bottom: 1.5rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .bookings-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-filter {
        flex-direction: column;
    }
    
    .search-input, .filter-select {
        width: 100%;
    }
}

.badge {
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-success {
    background: #e6f7ee;
    color: #00a854;
}

.badge-warning {
    background: #fff7e6;
    color: #fa8c16;
}

.badge-danger {
    background: #fff1f0;
    color: #f5222d;
}
</style>
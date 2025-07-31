<?php
// Include header
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';

// Check if booking reference is provided
if (!isset($_GET['ref']) && !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$bookingRef = trim($_GET['ref'] ?? $_GET['id'] ?? '');

try {
    $db = DBConfig::getInstance()->getConnection();
    
    // Get booking details with flight information
    $stmt = $db->prepare("
        SELECT b.*, 
               f.flight_number as outbound_flight_number, 
               f.departure_time as outbound_departure,
               f.arrival_time as outbound_arrival,
               dep.name as dep_airport_name, dep.code as dep_airport_code, dep.city as dep_city,
               arr.name as arr_airport_name, arr.code as arr_airport_code, arr.city as arr_city,
               rf.flight_number as return_flight_number,
               rf.departure_time as return_departure,
               rf.arrival_time as return_arrival,
               a.name as airline_name, a.logo_url
        FROM bookings b
        LEFT JOIN flights f ON b.flight_id = f.flight_id
        LEFT JOIN airlines a ON f.airline_id = a.airline_id
        LEFT JOIN airports dep ON f.departure_airport_id = dep.airport_id
        LEFT JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        LEFT JOIN flights rf ON b.return_flight_id = rf.flight_id
        WHERE b.booking_number = ? 
        OR b.booking_id = ?
    ");
    $stmt->execute([$bookingRef, $bookingRef]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        $_SESSION['booking_errors'] = ["Booking not found"];
        header("Location: index.php");
        exit();
    }
    
    // Get passengers for this booking
    $stmt = $db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
    $stmt->execute([$booking['booking_id']]);
    $passengers = $stmt->fetchAll();
    
} catch (PDOException $e) {
    handleException($e);
    $_SESSION['booking_errors'] = ["An error occurred while retrieving your booking"];
    header("Location: index.php");
    exit();
}

// Set page title
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Aviation System');
}
$pageTitle = "Booking Confirmation | " . SITE_NAME;
?>

<!-- Main Content -->
<main class="main-content py-5 bg-light">
    <div class="container">
        <!-- Confirmation Header -->
        <div class="confirmation-header text-center mb-5 p-4 bg-white rounded-4 shadow-sm position-relative overflow-hidden">
            <div class="position-relative">
                <!-- <div class="confirmation-icon mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#28a745" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                </div> -->
                <h1 class="fw-bold mb-3">Your Booking is Confirmed!</h1>
                <p class="lead text-muted mb-2">Booking Reference: <span class="fw-bold text-dark"><?= htmlspecialchars($booking['booking_number']) ?></span></p>
                <p class="text-muted">An email confirmation has been sent to your registered email address.</p>
                <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">
                    <a href="my_booking.php" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-ticket-alt me-2"></i> View My Bookings
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fas fa-print me-2"></i> Print Itinerary
                    </button>
                    <a href="#boarding-pass" class="btn btn-success px-4 py-2">
                        <i class="fas fa-mobile-alt me-2"></i> Mobile Boarding Pass
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Booking Summary Card -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0 fw-bold">Booking Summary</h4>
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : 'danger' ?> px-3 py-2 rounded-pill">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                        <span class="badge bg-<?= $booking['payment_status'] === 'paid' ? 'success' : ($booking['payment_status'] === 'pending' ? 'warning' : 'danger') ?> px-3 py-2 rounded-pill">
                            Payment: <?= ucfirst($booking['payment_status']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Flight Details -->
                    <div class="col-lg-6">
                        <div class="flight-details" style="display: flex; flex-direction: column;">
                            <h5 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="fas fa-plane-departure me-2 text-primary"></i>
                                Flight Details
                            </h5>
                            
                            <!-- Outbound Flight -->
                            <div class="flight-card mb-4 p-3 bg-white rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                    <div class="d-flex align-items-center mb-2 mb-sm-0">
                                        <span class="badge bg-light text-dark me-2">Outbound</span>
                                        <span class="text-muted small"><?= date('D, M j, Y', strtotime($booking['outbound_departure'])) ?></span>
                                    </div>
                                    <span class="badge bg-<?= 
                                        $booking['class'] === 'business' ? 'info' : 
                                        ($booking['class'] === 'first' ? 'dark' : 
                                        ($booking['class'] === 'premium' ? 'purple' : 'primary')) ?>">
                                        <?= ucfirst($booking['class']) ?> Class
                                    </span>
                                </div>
                                
                                <div class="flight-airline d-flex align-items-center mb-3">
                                    <?php if (!empty($booking['logo_url'])): ?>
                                        <img src="<?= htmlspecialchars($booking['logo_url']) ?>" alt="<?= htmlspecialchars($booking['airline_name']) ?>" 
                                             class="img-fluid me-3" style="max-height: 30px; max-width: 80px;">
                                    <?php else: ?>
                                        <div class="airline-icon me-3 bg-light p-2 rounded">
                                            <i class="fas fa-plane text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($booking['airline_name']) ?></div>
                                        <div class="text-muted small">Flight <?= htmlspecialchars($booking['outbound_flight_number']) ?></div>
                                    </div>
                                </div>
                                
                                <div class="flight-timeline position-relative mb-3">
                                    <div class="row g-0">
                                        <div class="col-5">
                                            <div class="fw-bold"><?= date('H:i', strtotime($booking['outbound_departure'])) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($booking['dep_city']) ?> (<?= htmlspecialchars($booking['dep_airport_code']) ?>)</div>
                                        </div>
                                        <div class="col-2">
                                            <div class="flight-duration text-center small text-muted">
                                                <?= getFlightDuration($booking['outbound_departure'], $booking['outbound_arrival']) ?>
                                            </div>
                                            <div class="flight-path position-relative mt-1">
                                                <div class="position-absolute top-50 start-0 end-0 border-top border-2 border-secondary opacity-25"></div>
                                                <div class="position-absolute top-50 start-50 translate-middle">
                                                    <i class="fas fa-plane text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="text-center small text-muted">Non-stop</div>
                                        </div>
                                        <div class="col-5 text-end">
                                            <div class="fw-bold"><?= date('H:i', strtotime($booking['outbound_arrival'])) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($booking['arr_city']) ?> (<?= htmlspecialchars($booking['arr_airport_code']) ?>)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($booking['is_international']): ?>
                                    <div class="alert alert-warning small mt-2 mb-0 py-2">
                                        <i class="fas fa-passport me-2"></i> International flight - Passport required
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Return Flight (if applicable) -->
                            <?php if (!empty($booking['return_flight_id'])): ?>
                                <div class="flight-card p-3 bg-white rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                        <div class="d-flex align-items-center mb-2 mb-sm-0">
                                            <span class="badge bg-light text-dark me-2">Return</span>
                                            <span class="text-muted small"><?= date('D, M j, Y', strtotime($booking['return_departure'])) ?></span>
                                        </div>
                                        <span class="badge bg-<?= 
                                            $booking['class'] === 'business' ? 'info' : 
                                            ($booking['class'] === 'first' ? 'dark' : 
                                            ($booking['class'] === 'premium' ? 'purple' : 'primary')) ?>">
                                            <?= ucfirst($booking['class']) ?> Class
                                        </span>
                                    </div>
                                    
                                    <div class="flight-airline d-flex align-items-center mb-3">
                                        <?php if (!empty($booking['logo_url'])): ?>
                                            <img src="<?= htmlspecialchars($booking['logo_url']) ?>" alt="<?= htmlspecialchars($booking['airline_name']) ?>" 
                                                 class="img-fluid me-3" style="max-height: 30px; max-width: 80px;">
                                        <?php else: ?>
                                            <div class="airline-icon me-3 bg-light p-2 rounded">
                                                <i class="fas fa-plane text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($booking['airline_name']) ?></div>
                                            <div class="text-muted small">Flight <?= htmlspecialchars($booking['return_flight_number']) ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="flight-timeline position-relative mb-3">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="fw-bold"><?= date('H:i', strtotime($booking['return_departure'])) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($booking['arr_city']) ?> (<?= htmlspecialchars($booking['arr_airport_code']) ?>)</div>
                                            </div>
                                            <div class="col-2">
                                                <div class="flight-duration text-center small text-muted">
                                                    <?= getFlightDuration($booking['return_departure'], $booking['return_arrival']) ?>
                                                </div>
                                                <div class="flight-path position-relative mt-1">
                                                    <div class="position-absolute top-50 start-0 end-0 border-top border-2 border-secondary opacity-25"></div>
                                                    <div class="position-absolute top-50 start-50 translate-middle">
                                                        <i class="fas fa-plane text-primary"></i>
                                                    </div>
                                                </div>
                                                <div class="text-center small text-muted">Non-stop</div>
                                            </div>
                                            <div class="col-5 text-end">
                                                <div class="fw-bold"><?= date('H:i', strtotime($booking['return_arrival'])) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($booking['dep_city']) ?> (<?= htmlspecialchars($booking['dep_airport_code']) ?>)</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($booking['is_international']): ?>
                                        <div class="alert alert-warning small mt-2 mb-0 py-2">
                                            <i class="fas fa-passport me-2"></i> International flight - Passport required
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Passenger and Payment Information -->
                    <div class="col-lg-6">
                        <div class="passenger-info mb-4">
                            <h5 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="fas fa-users me-2 text-primary"></i>
                                Passenger Information (<?= count($passengers) ?>)
                            </h5>
                            
                            <div class="passenger-list">
                                <?php foreach ($passengers as $passenger): ?>
                                    <div class="passenger-card mb-3 p-3 bg-white rounded-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                            <div class="fw-bold">
                                                <?= htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']) ?>
                                                <?php if ($passenger['type'] === 'child'): ?>
                                                    <span class="badge bg-info ms-2">Child</span>
                                                <?php elseif ($passenger['type'] === 'infant'): ?>
                                                    <span class="badge bg-warning ms-2">Infant</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($passenger['is_primary']): ?>
                                                <span class="badge bg-primary rounded-pill px-2">Primary</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="passenger-details">
                                            <div class="row small text-muted">
                                                <div class="col-md-6 mb-2 mb-md-0">
                                                    <?php if (!empty($passenger['date_of_birth'])): ?>
                                                        <div><i class="fas fa-birthday-cake me-1"></i> <?= date('M j, Y', strtotime($passenger['date_of_birth'])) ?></div>
                                                    <?php endif; ?>
                                                    <div><i class="fas fa-ticket me-1"></i> <?= strtoupper($booking['class'][0]) . $passenger['seat_number'] ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if (!empty($passenger['passport_number'])): ?>
                                                        <div><i class="fas fa-passport me-1"></i> <?= htmlspecialchars($passenger['passport_number']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($passenger['frequent_flyer_number'])): ?>
                                                        <div><i class="fas fa-star me-1"></i> <?= htmlspecialchars($passenger['frequent_flyer_number']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="payment-info">
                            <h5 class="fw-bold mb-4 d-flex align-items-center">
                                <i class="fas fa-credit-card me-2 text-primary"></i>
                                Payment Summary
                            </h5>
                            
                            <div class="payment-card p-3 bg-white rounded-3 border">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="text-muted small">Booking Date:</div>
                                        <div class="fw-bold"><?= date('M j, Y H:i', strtotime($booking['booking_date'])) ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Payment Method:</div>
                                        <div class="fw-bold text-capitalize"><?= str_replace('_', ' ', $booking['payment_method']) ?></div>
                                    </div>
                                </div>
                                
                                <div class="payment-summary border-top pt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Base Fare (x<?= count($passengers) ?>):</span>
                                        <span>$<?= number_format($booking['total_price'] * 0.7, 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Taxes & Fees:</span>
                                        <span>$<?= number_format($booking['total_price'] * 0.3, 2) ?></span>
                                    </div>
                                    <?php if ($booking['class'] !== 'economy'): ?>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted"><?= ucfirst($booking['class']) ?> Class Upgrade:</span>
                                            <span>$<?= number_format($booking['total_price'] * 0.1, 2) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between fw-bold fs-5 pt-2 border-top mt-2">
                                        <span>Total Paid:</span>
                                        <span>$<?= number_format($booking['total_price'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-4">
                                <div class="d-flex">
                                    <i class="fas fa-info-circle me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold mb-2">Important Travel Information</h6>
                                        <ul class="mb-0 small">
                                            <li>Online check-in opens 24-48 hours before departure</li>
                                            <li>Baggage allowance: <?= $booking['class'] === 'economy' ? '1 x 23kg' : ($booking['class'] === 'business' ? '2 x 32kg' : '3 x 32kg') ?></li>
                                            <li>Have your travel documents ready at check-in</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Boarding Pass Section -->
        <div id="boarding-pass" class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h4 class="mb-0 fw-bold">Mobile Boarding Pass</h4>
            </div>
            <div class="card-body p-4">
                <div class="text-center">
                    <div class="mb-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($booking['booking_number']) ?>" 
                             alt="Boarding Pass QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                        <p class="text-muted">Scan this QR code at the airport for quick check-in</p>
                    </div>
                    <button class="btn btn-primary me-2">
                        <i class="fas fa-download me-2"></i> Download Boarding Pass
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-share-alt me-2"></i> Share Booking
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Need Help Section -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h4 class="mb-0 fw-bold">Need Help?</h4>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="d-flex">
                            <div class="me-3 text-primary">
                                <i class="fas fa-phone-alt fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Call Us</h6>
                                <p class="mb-0 small text-muted">+1 (800) 123-4567</p>
                                <p class="small text-muted">24/7 Customer Support</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex">
                            <div class="me-3 text-primary">
                                <i class="fas fa-envelope fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Email Us</h6>
                                <p class="mb-0 small text-muted">support@aviationsystem.com</p>
                                <p class="small text-muted">Typically replies within 2 hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex">
                            <div class="me-3 text-primary">
                                <i class="fas fa-comments fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Live Chat</h6>
                                <p class="mb-0 small text-muted">Chat with our agents</p>
                                <p class="small text-muted">Available 8am-10pm EST</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';

// Helper function to calculate flight duration
function getFlightDuration($departure, $arrival) {
    $departureTime = new DateTime($departure);
    $arrivalTime = new DateTime($arrival);
    $interval = $departureTime->diff($arrivalTime);
    
    $hours = $interval->h;
    $minutes = $interval->i;
    
    return $hours . 'h ' . $minutes . 'm';
}
?>
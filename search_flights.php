<?php
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';

// Set page title
$pageTitle = "Flight Search Results | Aviation System";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("Location: index.php");
    exit();
}

// Get form data and sanitize
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';
$departure = isset($_GET['departure']) ? trim($_GET['departure']) : '';
$return = isset($_GET['return']) ? trim($_GET['return']) : '';
$passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;
$class = isset($_GET['class']) ? trim($_GET['class']) : 'economy';

// Validate required fields
$errors = [];
if (empty($from)) {
    $errors[] = "Departure airport is required";
}
if (empty($to)) {
    $errors[] = "Arrival airport is required";
}
if (empty($departure)) {
    $errors[] = "Departure date is required";
}
if ($from === $to) {
    $errors[] = "Departure and arrival airports cannot be the same";
}

// If errors, redirect back with errors
if (!empty($errors)) {
    $_SESSION['search_errors'] = $errors;
    $_SESSION['search_data'] = $_GET;
    header("Location: index.php#flight-search");
    exit();
}

try {
    // Get database connection
    $db = DBConfig::getInstance()->getConnection();
    
    // Build base query
    $query = "
        SELECT 
            f.flight_id, f.flight_number, f.departure_time, f.arrival_time,
            f.status, f.economy_price, f.business_price, f.first_class_price,
            a.airline_id, a.name as airline_name, a.logo_url,
            dep.airport_id as dep_airport_id, dep.name as dep_airport_name, 
            dep.code as dep_airport_code, dep.city as dep_airport_city,
            arr.airport_id as arr_airport_id, arr.name as arr_airport_name,
            arr.code as arr_airport_code, arr.city as arr_airport_city,
            TIMESTAMPDIFF(HOUR, f.departure_time, f.arrival_time) as duration_hours,
            TIMESTAMPDIFF(MINUTE, f.departure_time, f.arrival_time) % 60 as duration_minutes
        FROM flights f
        JOIN airlines a ON f.airline_id = a.airline_id
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        WHERE dep.code = :from_code
        AND arr.code = :to_code
        AND DATE(f.departure_time) = :departure_date
        AND f.status = 'scheduled'
    ";
    
    // Prepare parameters
    $params = [
        ':from_code' => $from,
        ':to_code' => $to,
        ':departure_date' => $departure
    ];
    
    // Add return flight query if return date is specified
    $returnFlights = [];
    if (!empty($return)) {
        $returnQuery = "
            SELECT 
                f.flight_id, f.flight_number, f.departure_time, f.arrival_time,
                f.status, f.economy_price, f.business_price, f.first_class_price,
                a.airline_id, a.name as airline_name, a.logo_url,
                dep.airport_id as dep_airport_id, dep.name as dep_airport_name, 
                dep.code as dep_airport_code, dep.city as dep_airport_city,
                arr.airport_id as arr_airport_id, arr.name as arr_airport_name,
                arr.code as arr_airport_code, arr.city as arr_airport_city,
                TIMESTAMPDIFF(HOUR, f.departure_time, f.arrival_time) as duration_hours,
                TIMESTAMPDIFF(MINUTE, f.departure_time, f.arrival_time) % 60 as duration_minutes
            FROM flights f
            JOIN airlines a ON f.airline_id = a.airline_id
            JOIN airports dep ON f.departure_airport_id = dep.airport_id
            JOIN airports arr ON f.arrival_airport_id = arr.airport_id
            WHERE dep.code = :return_from_code
            AND arr.code = :return_to_code
            AND DATE(f.departure_time) = :return_date
            AND f.status = 'scheduled'
        ";
        
        $returnParams = [
            ':return_from_code' => $to,
            ':return_to_code' => $from,
            ':return_date' => $return
        ];
        
        $stmt = $db->prepare($returnQuery);
        $stmt->execute($returnParams);
        $returnFlights = $stmt->fetchAll();
    }
    
    // Execute main query
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $flights = $stmt->fetchAll();
    
    // Get airport information for display
    $stmt = $db->prepare("SELECT * FROM airports WHERE code = ? LIMIT 1");
    $stmt->execute([$from]);
    $fromAirport = $stmt->fetch();
    
    $stmt->execute([$to]);
    $toAirport = $stmt->fetch();
    
} catch (PDOException $e) {
    handleException($e);
    $errors[] = "Database error occurred. Please try again later.";
    $_SESSION['search_errors'] = $errors;
    $_SESSION['search_data'] = $_GET;
    header("Location: index.php#flight-search");
    exit();
}

// Calculate price based on class
function calculatePrice($flight, $class, $passengers) {
    $basePrice = 0;
    switch ($class) {
        case 'economy':
            $basePrice = $flight['economy_price'];
            break;
        case 'business':
            $basePrice = $flight['business_price'];
            break;
        case 'first':
            $basePrice = $flight['first_class_price'];
            break;
        case 'premium':
            // Assuming premium is 20% more than economy
            $basePrice = $flight['economy_price'] * 1.2;
            break;
        default:
            $basePrice = $flight['economy_price'];
    }
    
    return $basePrice * $passengers;
}

// Include header
include __DIR__ . '/includes/header.php';
?>

<!-- Main Content -->
<main class="main-content py-5">
    <div class="container">
        <!-- Search Results Header -->
        <div class="search-results-header mb-5">
            <h2 class="mb-3">Flight Search Results</h2>
            
            <!-- Search Summary -->
            <div class="search-summary card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Journey Details</h5>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-plane-departure me-3 text-primary"></i>
                                <div>
                                    <strong><?= htmlspecialchars($fromAirport['city']) ?> (<?= $fromAirport['code'] ?>)</strong>
                                    <div class="text-muted small"><?= htmlspecialchars($fromAirport['name']) ?></div>
                                </div>
                                <i class="fas fa-arrow-right mx-3 text-muted"></i>
                                <div>
                                    <strong><?= htmlspecialchars($toAirport['city']) ?> (<?= $toAirport['code'] ?>)</strong>
                                    <div class="text-muted small"><?= htmlspecialchars($toAirport['name']) ?></div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="me-4">
                                    <span class="text-muted">Departure:</span>
                                    <strong><?= date('D, M j, Y', strtotime($departure)) ?></strong>
                                </div>
                                <?php if (!empty($return)): ?>
                                <div>
                                    <span class="text-muted">Return:</span>
                                    <strong><?= date('D, M j, Y', strtotime($return)) ?></strong>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Travel Details</h5>
                            <div class="d-flex">
                                <div class="me-4">
                                    <span class="text-muted">Passengers:</span>
                                    <strong><?= $passengers ?> <?= $passengers > 1 ? 'persons' : 'person' ?></strong>
                                </div>
                                <div>
                                    <span class="text-muted">Class:</span>
                                    <strong><?= ucfirst($class) ?></strong>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="index.php#flight-search" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Modify Search
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outbound Flights -->
        <div class="outbound-flights mb-5">
            <h4 class="mb-4">
                <i class="fas fa-plane-departure text-primary me-2"></i>
                Outbound Flights
                <span class="badge bg-primary ms-2"><?= count($flights) ?> found</span>
            </h4>
            
            <?php if (empty($flights)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No flights found for your selected criteria. Please try different dates or airports.
                </div>
            <?php else: ?>
                <div class="flight-list">
                    <?php foreach ($flights as $flight): ?>
                        <div class="flight-card card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Airline Info -->
                                    <div class="col-md-2 text-center">
                                        <?php if (!empty($flight['logo_url'])): ?>
                                            <img src="<?= htmlspecialchars($flight['logo_url']) ?>" alt="<?= htmlspecialchars($flight['airline_name']) ?>" class="airline-logo img-fluid mb-2" style="max-height: 40px;">
                                        <?php else: ?>
                                            <div class="airline-placeholder bg-light p-2 mb-2 rounded">
                                                <i class="fas fa-plane text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="small text-muted"><?= htmlspecialchars($flight['airline_name']) ?></div>
                                        <div class="small"><?= $flight['flight_number'] ?></div>
                                    </div>
                                    
                                    <!-- Flight Times -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="flight-time"><?= date('H:i', strtotime($flight['departure_time'])) ?></div>
                                                <div class="small text-muted"><?= $flight['dep_airport_code'] ?></div>
                                            </div>
                                            <div class="flight-duration text-center px-3">
                                                <div class="small text-muted">
                                                    <?= $flight['duration_hours'] ?>h <?= $flight['duration_minutes'] ?>m
                                                </div>
                                                <div class="flight-path position-relative">
                                                    <div class="position-absolute top-50 start-0 end-0 border-top border-secondary"></div>
                                                    <div class="position-absolute top-50 start-50 translate-middle">
                                                        <i class="fas fa-plane text-primary"></i>
                                                    </div>
                                                </div>
                                                <div class="small text-muted">Non-stop</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="flight-time"><?= date('H:i', strtotime($flight['arrival_time'])) ?></div>
                                                <div class="small text-muted"><?= $flight['arr_airport_code'] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Flight Details -->
                                    <div class="col-md-4">
                                        <div class="small">
                                            <div class="mb-1">
                                                <i class="fas fa-calendar-alt text-muted me-2"></i>
                                                <?= date('D, M j, Y', strtotime($flight['departure_time'])) ?>
                                            </div>
                                            <div class="mb-1">
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <?= htmlspecialchars($flight['dep_airport_name']) ?>
                                            </div>
                                            <div>
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <?= htmlspecialchars($flight['arr_airport_name']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Price and Book -->
                                    <div class="col-md-3 text-end">
                                        <div class="flight-price mb-2">
                                            <span class="h5">$<?= number_format(calculatePrice($flight, $class, $passengers), 2) ?></span>
                                            <div class="small text-muted">Total for <?= $passengers ?> <?= $passengers > 1 ? 'persons' : 'person' ?></div>
                                        </div>
                                        <button class="btn btn-primary book-now-btn" 
                                                data-flight-id="<?= $flight['flight_id'] ?>" 
                                                data-class="<?= $class ?>" 
                                                data-passengers="<?= $passengers ?>"
                                                <?php if (!empty($return)): ?>
                                                data-return-flight-id="<?= $returnFlight['flight_id'] ?>"
                                                <?php endif; ?>>
                                            <i class="fas fa-ticket-alt me-1"></i> Book Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Return Flights (if applicable) -->
        <?php if (!empty($return) && !empty($returnFlights)): ?>
            <div class="return-flights mb-5">
                <h4 class="mb-4">
                    <i class="fas fa-plane-arrival text-primary me-2"></i>
                    Return Flights
                    <span class="badge bg-primary ms-2"><?= count($returnFlights) ?> found</span>
                </h4>
                
                <div class="flight-list">
                    <?php foreach ($returnFlights as $flight): ?>
                        <div class="flight-card card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Airline Info -->
                                    <div class="col-md-2 text-center">
                                        <?php if (!empty($flight['logo_url'])): ?>
                                            <img src="<?= htmlspecialchars($flight['logo_url']) ?>" alt="<?= htmlspecialchars($flight['airline_name']) ?>" class="airline-logo img-fluid mb-2" style="max-height: 40px;">
                                        <?php else: ?>
                                            <div class="airline-placeholder bg-light p-2 mb-2 rounded">
                                                <i class="fas fa-plane text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="small text-muted"><?= htmlspecialchars($flight['airline_name']) ?></div>
                                        <div class="small"><?= $flight['flight_number'] ?></div>
                                    </div>
                                    
                                    <!-- Flight Times -->
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="flight-time"><?= date('H:i', strtotime($flight['departure_time'])) ?></div>
                                                <div class="small text-muted"><?= $flight['dep_airport_code'] ?></div>
                                            </div>
                                            <div class="flight-duration text-center px-3">
                                                <div class="small text-muted">
                                                    <?= $flight['duration_hours'] ?>h <?= $flight['duration_minutes'] ?>m
                                                </div>
                                                <div class="flight-path position-relative">
                                                    <div class="position-absolute top-50 start-0 end-0 border-top border-secondary"></div>
                                                    <div class="position-absolute top-50 start-50 translate-middle">
                                                        <i class="fas fa-plane text-primary"></i>
                                                    </div>
                                                </div>
                                                <div class="small text-muted">Non-stop</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="flight-time"><?= date('H:i', strtotime($flight['arrival_time'])) ?></div>
                                                <div class="small text-muted"><?= $flight['arr_airport_code'] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Flight Details -->
                                    <div class="col-md-4">
                                        <div class="small">
                                            <div class="mb-1">
                                                <i class="fas fa-calendar-alt text-muted me-2"></i>
                                                <?= date('D, M j, Y', strtotime($flight['departure_time'])) ?>
                                            </div>
                                            <div class="mb-1">
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <?= htmlspecialchars($flight['dep_airport_name']) ?>
                                            </div>
                                            <div>
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <?= htmlspecialchars($flight['arr_airport_name']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Price and Book -->
                                    <div class="col-md-3 text-end">
                                        <div class="flight-price mb-2">
                                            <span class="h5">$<?= number_format(calculatePrice($flight, $class, $passengers), 2) ?></span>
                                            <div class="small text-muted">Total for <?= $passengers ?> <?= $passengers > 1 ? 'persons' : 'person' ?></div>
                                        </div>
                                        <button class="btn btn-outline-primary book-now-btn" 
                                                data-flight-id="<?= $flight['flight_id'] ?>" 
                                                data-class="<?= $class ?>" 
                                                data-passengers="<?= $passengers ?>">
                                            <i class="fas fa-info-circle me-1"></i> Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- No Results Alternative -->
        <?php if (empty($flights) && empty($returnFlights)): ?>
            <div class="alternative-options mt-5">
                <h4 class="mb-4">Alternative Options</h4>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt text-primary mb-3" style="font-size: 2rem;"></i>
                                <h5>Try Different Dates</h5>
                                <p class="text-muted">Flights may be available on nearby dates</p>
                                <a href="index.php#flight-search" class="btn btn-outline-primary">Modify Dates</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marked-alt text-primary mb-3" style="font-size: 2rem;"></i>
                                <h5>Nearby Airports</h5>
                                <p class="text-muted">Check flights from nearby departure airports</p>
                                <a href="index.php#flight-search" class="btn btn-outline-primary">Change Airport</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-headset text-primary mb-3" style="font-size: 2rem;"></i>
                                <h5>Need Help?</h5>
                                <p class="text-muted">Our customer service can help you find options</p>
                                <a href="contact.php" class="btn btn-outline-primary">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login to Continue Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm" action="login.php" method="POST">
                    <input type="hidden" name="redirect" value="booking">
                    <input type="hidden" id="loginFlightId" name="flight_id">
                    <input type="hidden" id="loginClass" name="class">
                    <input type="hidden" id="loginPassengers" name="passengers">
                    <input type="hidden" id="loginReturnFlightId" name="return_flight_id">
                    
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>
<script>
$(document).ready(function() {
    // Handle book now button clicks
    $('.book-now-btn').click(function(e) {
        e.preventDefault();
        
        // Get flight details from data attributes
        const flightId = $(this).data('flight-id');
        const flightClass = $(this).data('class');
        const passengers = $(this).data('passengers');
        const returnFlightId = $(this).data('return-flight-id') || '';
        
        // Check if user is logged in (you need to implement this check)
        // This could be done by checking if a session variable exists or via an AJAX call
        const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
        
        if (isLoggedIn) {
            // User is logged in - redirect to booking page
            window.location.href = `booking_summary.php?flight_id=${flightId}&class=${flightClass}&passengers=${passengers}` + 
                                  (returnFlightId ? `&return_flight_id=${returnFlightId}` : '');
        } else {
            // User is not logged in - show login modal
            $('#loginFlightId').val(flightId);
            $('#loginClass').val(flightClass);
            $('#loginPassengers').val(passengers);
            if (returnFlightId) {
                $('#loginReturnFlightId').val(returnFlightId);
            }
            
            // Show the modal
            $('#loginModal').modal('show');
        }
    });
    
    // You might also want to handle the login form submission
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        // You can add AJAX login handling here if you want, 
        // or just let the form submit normally to your login.php
        this.submit();
    });
});
</script>
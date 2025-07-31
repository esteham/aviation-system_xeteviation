<?php
// Include header
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
     echo "<script>window.location.href='index.php';</script>";
    exit();
}

// Validate and get flight details
$errors = [];
$flightId = isset($_GET['flight_id']) ? intval($_GET['flight_id']) : 0;
$class = isset($_GET['class']) ? trim($_GET['class']) : 'economy';
$passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;
$returnFlightId = isset($_GET['return_flight_id']) ? intval($_GET['return_flight_id']) : 0;

if ($flightId <= 0) {
    $errors[] = "Invalid flight selection";
}

if (!in_array($class, ['economy', 'business', 'first', 'premium'])) {
    $errors[] = "Invalid class selection";
}

if ($passengers < 1 || $passengers > 10) {
    $errors[] = "Invalid number of passengers";
}

try {
    $db = DBConfig::getInstance()->getConnection();
    
    // Get main flight details including is_international flag
    $stmt = $db->prepare("
        SELECT f.*, 
               a.name as airline_name, a.logo_url,
               dep.name as dep_airport_name, dep.code as dep_airport_code, dep.city as dep_airport_city, dep.country as dep_country,
               arr.name as arr_airport_name, arr.code as arr_airport_code, arr.city as arr_airport_city, arr.country as arr_country
        FROM flights f
        JOIN airlines a ON f.airline_id = a.airline_id
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        WHERE f.flight_id = ?
    ");
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch();
    
    if (!$flight) {
        $errors[] = "Flight not found";
    } else {
        // Determine if flight is international (departure and arrival countries differ)
        $flight['is_international'] = ($flight['dep_country'] !== $flight['arr_country']);
    }
    
    // Get return flight details if applicable
    $returnFlight = null;
    if ($returnFlightId > 0) {
        $stmt->execute([$returnFlightId]);
        $returnFlight = $stmt->fetch();
        if (!$returnFlight) {
            $errors[] = "Return flight not found";
        } else {
            $returnFlight['is_international'] = ($returnFlight['dep_country'] !== $returnFlight['arr_country']);
        }
    }
    
} catch (PDOException $e) {
    handleException($e);
    $errors[] = "Database error occurred. Please try again later.";
}

if (!empty($errors)) {
    $_SESSION['booking_errors'] = $errors;
    header("Location: flight_search.php");
    exit();
}

// Calculate prices
function calculatePrice($flight, $class, $passengers) {
    $basePrice = 0;
    switch ($class) {
        case 'economy': $basePrice = $flight['economy_price']; break;
        case 'business': $basePrice = $flight['business_price']; break;
        case 'first': $basePrice = $flight['first_class_price']; break;
        case 'premium': $basePrice = $flight['economy_price'] * 1.2; break;
        default: $basePrice = $flight['economy_price'];
    }
    return $basePrice * $passengers;
}

$totalPrice = calculatePrice($flight, $class, $passengers);
if ($returnFlight) {
    $totalPrice += calculatePrice($returnFlight, $class, $passengers);
}

// Set page title
$pageTitle = "Complete Booking | Aviation System";

?>

<!-- Main Content -->
<main class="main-content py-5">
    <div class="container">
        <div class="row">
            <!-- Booking Form Column -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Passenger Information</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['booking_errors'])): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($_SESSION['booking_errors'] as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php unset($_SESSION['booking_errors']); ?>
                        <?php endif; ?>
                        
                        <form id="bookingForm" action="process_booking.php" method="POST">
                            <input type="hidden" name="flight_id" value="<?= $flightId ?>">
                            <input type="hidden" name="class" value="<?= $class ?>">
                            <input type="hidden" name="passengers" value="<?= $passengers ?>">
                            <?php if ($returnFlightId > 0): ?>
                                <input type="hidden" name="return_flight_id" value="<?= $returnFlightId ?>">
                            <?php endif; ?>
                            <input type="hidden" name="is_international" value="<?= $flight['is_international'] ? 1 : 0 ?>">
                            
                            <!-- Primary Passenger -->
                            <h5 class="mb-3">Primary Passenger</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" 
                                           value="<?= htmlspecialchars($_SESSION['first_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" 
                                           value="<?= htmlspecialchars($_SESSION['last_name'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($_SESSION['userEmail'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <?php if ($flight['is_international']): ?>
                                <div class="mb-3">
                                    <label class="form-label">Passport Number</label>
                                    <input type="text" class="form-control" name="passport_number" 
                                           value="<?= htmlspecialchars($_SESSION['passport_number'] ?? '') ?>" required>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Additional Passengers -->
                            <?php if ($passengers > 1): ?>
                                <h5 class="mb-3 mt-4">Additional Passengers</h5>
                                <?php for ($i = 1; $i < $passengers; $i++): ?>
                                    <div class="passenger-form mb-4 border-top pt-3">
                                        <h6 class="mb-3">Passenger <?= $i + 1 ?></h6>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" name="passengers[<?= $i ?>][first_name]" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="passengers[<?= $i ?>][last_name]" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="passengers[<?= $i ?>][email]" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control" name="passengers[<?= $i ?>][phone]" required>
                                            </div>
                                        </div>
                                        <?php if ($flight['is_international']): ?>
                                            <div class="mb-3">
                                                <label class="form-label">Passport Number</label>
                                                <input type="text" class="form-control" name="passengers[<?= $i ?>][passport_number]" required>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            <?php endif; ?>
                            
                            <!-- Payment Section -->
                            <h5 class="mb-3 mt-4">Payment Method</h5>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="creditCard" value="credit_card" checked>
                                    <label class="form-check-label" for="creditCard">
                                        <i class="fab fa-cc-visa me-2"></i> Credit/Debit Card
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                    <label class="form-check-label" for="paypal">
                                        <i class="fab fa-paypal me-2"></i> PayPal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="bankTransfer" value="bank_transfer">
                                    <label class="form-check-label" for="bankTransfer">
                                        <i class="fas fa-university me-2"></i> Bank Transfer
                                    </label>
                                </div>
                            </div>
                            
                            <div id="creditCardFields">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Card Number</label>
                                        <input type="text" class="form-control" name="card_number" placeholder="1234 5678 9012 3456" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" name="card_expiry" placeholder="MM/YY" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" name="card_cvv" placeholder="123" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Card Holder Name</label>
                                    <input type="text" class="form-control" name="card_holder" placeholder="Name on card" required>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-check-circle me-1"></i> Confirm Booking
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Booking Summary Column -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Booking Summary</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-3">Flight Details</h5>
                        
                        <!-- Outbound Flight -->
                        <div class="flight-summary mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Outbound Flight</strong>
                                <span class="badge bg-info"><?= ucfirst($class) ?></span>
                                <?php if ($flight['is_international']): ?>
                                    <span class="badge bg-warning">International</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3">
                                    <?php if (!empty($flight['logo_url'])): ?>
                                        <img src="<?= htmlspecialchars($flight['logo_url']) ?>" alt="<?= htmlspecialchars($flight['airline_name']) ?>" 
                                             class="img-fluid" style="max-height: 30px;">
                                    <?php else: ?>
                                        <i class="fas fa-plane fa-2x text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div><?= $flight['flight_number'] ?></div>
                                    <div class="small text-muted"><?= $flight['airline_name'] ?></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5">
                                    <div class="fw-bold"><?= date('H:i', strtotime($flight['departure_time'])) ?></div>
                                    <div class="small"><?= date('D, M j', strtotime($flight['departure_time'])) ?></div>
                                    <div class="small text-muted"><?= $flight['dep_airport_code'] ?> (<?= $flight['dep_country'] ?>)</div>
                                </div>
                                <div class="col-2 text-center px-0">
                                    <div class="small text-muted">
                                        <?= floor((strtotime($flight['arrival_time']) - strtotime($flight['departure_time'])) / 3600) ?>h
                                        <?= floor(((strtotime($flight['arrival_time']) - strtotime($flight['departure_time'])) % 3600) / 60) ?>m
                                    </div>
                                    <div class="flight-path position-relative">
                                        <div class="position-absolute top-50 start-0 end-0 border-top border-secondary"></div>
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fas fa-plane text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="small text-muted">Non-stop</div>
                                </div>
                                <div class="col-5 text-end">
                                    <div class="fw-bold"><?= date('H:i', strtotime($flight['arrival_time'])) ?></div>
                                    <div class="small"><?= date('D, M j', strtotime($flight['arrival_time'])) ?></div>
                                    <div class="small text-muted"><?= $flight['arr_airport_code'] ?> (<?= $flight['arr_country'] ?>)</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Return Flight (if applicable) -->
                        <?php if ($returnFlight): ?>
                            <div class="flight-summary mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Return Flight</strong>
                                    <span class="badge bg-info"><?= ucfirst($class) ?></span>
                                    <?php if ($returnFlight['is_international']): ?>
                                        <span class="badge bg-warning">International</span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3">
                                        <?php if (!empty($returnFlight['logo_url'])): ?>
                                            <img src="<?= htmlspecialchars($returnFlight['logo_url']) ?>" alt="<?= htmlspecialchars($returnFlight['airline_name']) ?>" 
                                                 class="img-fluid" style="max-height: 30px;">
                                        <?php else: ?>
                                            <i class="fas fa-plane fa-2x text-muted"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div><?= $returnFlight['flight_number'] ?></div>
                                        <div class="small text-muted"><?= $returnFlight['airline_name'] ?></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-5">
                                        <div class="fw-bold"><?= date('H:i', strtotime($returnFlight['departure_time'])) ?></div>
                                        <div class="small"><?= date('D, M j', strtotime($returnFlight['departure_time'])) ?></div>
                                        <div class="small text-muted"><?= $returnFlight['dep_airport_code'] ?> (<?= $returnFlight['dep_country'] ?>)</div>
                                    </div>
                                    <div class="col-2 text-center px-0">
                                        <div class="small text-muted">
                                            <?= floor((strtotime($returnFlight['arrival_time']) - strtotime($returnFlight['departure_time'])) / 3600) ?>h
                                            <?= floor(((strtotime($returnFlight['arrival_time']) - strtotime($returnFlight['departure_time'])) % 3600) / 60) ?>m
                                        </div>
                                        <div class="flight-path position-relative">
                                            <div class="position-absolute top-50 start-0 end-0 border-top border-secondary"></div>
                                            <div class="position-absolute top-50 start-50 translate-middle">
                                                <i class="fas fa-plane text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="small text-muted">Non-stop</div>
                                    </div>
                                    <div class="col-5 text-end">
                                        <div class="fw-bold"><?= date('H:i', strtotime($returnFlight['arrival_time'])) ?></div>
                                        <div class="small"><?= date('D, M j', strtotime($returnFlight['arrival_time'])) ?></div>
                                        <div class="small text-muted"><?= $returnFlight['arr_airport_code'] ?> (<?= $returnFlight['arr_country'] ?>)</div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Price Summary -->
                        <h5 class="mb-3 mt-4">Price Summary</h5>
                        <div class="price-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Passengers (x<?= $passengers ?>):</span>
                                <span>$<?= number_format(calculatePrice($flight, $class, 1), 2) ?></span>
                            </div>
                            <?php if ($returnFlight): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Return Passengers (x<?= $passengers ?>):</span>
                                    <span>$<?= number_format(calculatePrice($returnFlight, $class, 1), 2) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between border-top pt-2">
                                <strong>Total:</strong>
                                <strong>$<?= number_format($totalPrice, 2) ?></strong>
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
?>

<style>
.is-invalid {
    border-color: #dc3545 !important;
}
.is-invalid:focus {
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
}
.invalid-feedback {
    color: #dc3545;
    font-size: 0.875em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    const submitBtn = document.getElementById('submitBtn');
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const creditCardFields = document.getElementById('creditCardFields');
    
    // Toggle credit card fields based on payment method
    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'credit_card') {
                creditCardFields.style.display = 'block';
                // Make credit card fields required
                creditCardFields.querySelectorAll('[required]').forEach(field => {
                    field.required = true;
                });
            } else {
                creditCardFields.style.display = 'none';
                // Remove required attribute for non-credit card payments
                creditCardFields.querySelectorAll('[required]').forEach(field => {
                    field.required = false;
                });
            }
        });
    });
    
    // Form submission handling
    bookingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Reset previous error states
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        
        // Validate required fields
        let isValid = true;
        const requiredFields = this.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                
                // Add error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'This field is required';
                field.parentNode.appendChild(errorDiv);
                
                isValid = false;
            }
        });
        
        // Validate credit card fields if credit card is selected
        if (document.querySelector('input[name="payment_method"]:checked').value === 'credit_card') {
            const cardNumber = document.querySelector('input[name="card_number"]');
            const cardExpiry = document.querySelector('input[name="card_expiry"]');
            const cardCvv = document.querySelector('input[name="card_cvv"]');
            
            // Simple card number validation (16 digits)
            if (!/^\d{16}$/.test(cardNumber.value.replace(/\s/g, ''))) {
                cardNumber.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Please enter a valid 16-digit card number';
                cardNumber.parentNode.appendChild(errorDiv);
                isValid = false;
            }
            
            // Simple expiry date validation (MM/YY format)
            if (!/^(0[1-9]|1[0-2])\/?([0-9]{2})$/.test(cardExpiry.value)) {
                cardExpiry.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Please enter a valid expiry date (MM/YY)';
                cardExpiry.parentNode.appendChild(errorDiv);
                isValid = false;
            }
            
            // Simple CVV validation (3 or 4 digits)
            if (!/^\d{3,4}$/.test(cardCvv.value)) {
                cardCvv.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Please enter a valid CVV (3 or 4 digits)';
                cardCvv.parentNode.appendChild(errorDiv);
                isValid = false;
            }
        }
        
        if (!isValid) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirm Booking';
            // Scroll to first error
            document.querySelector('.is-invalid').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            return;
        }
        
        // Submit the form via AJAX
        const formData = new FormData(this);

        fetch('process_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<strong>Booking failed:</strong> ' + (data.error || 'Unknown error');
                
                // Insert error message before the form
                bookingForm.parentNode.insertBefore(errorDiv, bookingForm);
                
                // Scroll to error
                errorDiv.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirm Booking';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirm Booking';
        });
    });
});
</script>
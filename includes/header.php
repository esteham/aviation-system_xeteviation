<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

// Set page title
$pageTitle = "SkyWings | Modern Air Travel Experience";

// Initialize variables
$featuredFlights = $popularDestinations = $airports = $testimonials = $blogPosts = [];
$availableHotels = $availablePackages = [];

try {
    $db = DBConfig::getInstance()->getConnection();
    
    // Get featured flights
    $stmt = $db->query("
        SELECT f.*, a.name as airline_name, a.logo_url,
               dep.name as departure_airport, dep.code as departure_code, dep.city as departure_city,
               arr.name as arrival_airport, arr.code as arrival_code, arr.city as arrival_city,
               TIMESTAMPDIFF(HOUR, f.departure_time, f.arrival_time) as duration_hours,
               TIMESTAMPDIFF(MINUTE, f.departure_time, f.arrival_time) % 60 as duration_minutes
        FROM flights f
        JOIN airlines a ON f.airline_id = a.airline_id
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        WHERE f.departure_time > NOW() AND f.status = 'scheduled'
        ORDER BY f.departure_time ASC
        LIMIT 3
    ");
    $featuredFlights = $stmt->fetchAll();

    // Get popular destinations
    $stmt = $db->query("
        SELECT arr.airport_id, arr.code, arr.name, arr.city, arr.country, arr.image_url,
               COUNT(b.booking_id) as bookings_count
        FROM bookings b
        JOIN flights f ON b.flight_id = f.flight_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        GROUP BY arr.airport_id
        ORDER BY bookings_count DESC
        LIMIT 3
    ");
    $popularDestinations = $stmt->fetchAll();

    // Get airports for dropdown
    $stmt = $db->query("SELECT code, city, name FROM airports ORDER BY city ASC");
    $airports = $stmt->fetchAll();

    // Get approved testimonials
    $testimonials = $db->query("SELECT id, user_name, user_image, review FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();

    // Get latest blog posts
    $blogPosts = $db->query("SELECT id, title, excerpt, image_url FROM blog_posts ORDER BY created_at DESC LIMIT 3")->fetchAll();

} catch (PDOException $e) {
    handleException($e);
}

// Process hotel search form
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['destination'])) {
    $destination = $_GET['destination'];
    $checkIn = $_GET['check-in'] ?? '';
    $checkOut = $_GET['check-out'] ?? '';
    $rooms = $_GET['rooms'] ?? 1;
    $guests = $_GET['guests'] ?? 1;
    $hotelClass = $_GET['hotel-class'] ?? '';
    
    $isLuxury = ($hotelClass === 'luxury') ? 1 : 0;
    $minStars = ($hotelClass === 'luxury') ? 5 : (int)$hotelClass;
    
    try {
        $sql = "SELECT h.*, d.city, d.country 
                FROM hotels h
                JOIN destinations d ON h.destination_id = d.id
                WHERE d.city = :destination 
                AND h.stars >= :minStars 
                AND (h.is_luxury = :isLuxury OR :isLuxury = 0)
                AND h.id IN (
                    SELECT DISTINCT ha.hotel_id
                    FROM hotel_availability ha
                    JOIN hotel_rooms hr ON ha.room_id = hr.id
                    WHERE ha.date BETWEEN :checkIn AND DATE_SUB(:checkOut, INTERVAL 1 DAY)
                    AND hr.max_guests >= :guests
                    AND ha.available_rooms >= :rooms
                    GROUP BY ha.hotel_id, ha.room_id
                    HAVING COUNT(ha.date) = DATEDIFF(:checkOut, :checkIn)
                )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':destination' => $destination,
            ':minStars' => $minStars,
            ':isLuxury' => $isLuxury,
            ':checkIn' => $checkIn,
            ':checkOut' => $checkOut,
            ':guests' => $guests,
            ':rooms' => $rooms
        ]);
        
        $availableHotels = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error searching hotels: " . $e->getMessage());
    }
}

// Process package search form
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['package-destination'])) {
    $destination = $_GET['package-destination'];
    $departureFrom = $_GET['package-from'] ?? '';
    $departureDate = $_GET['package-departure'] ?? '';
    $duration = (int)($_GET['package-duration'] ?? 0);
    $travelers = (int)($_GET['package-travelers'] ?? 1);
    $packageType = $_GET['package-type'] ?? '';
    
    $returnDate = date('Y-m-d', strtotime($departureDate . " + $duration days"));
    
    try {
        $sql = "SELECT tp.*, d.city, d.country, pd.price, pd.available_slots,
                       a_from.city as departure_city, a_from.name as airport_name
                FROM travel_packages tp
                JOIN destinations d ON tp.destination_id = d.id
                JOIN package_departures pd ON tp.id = pd.package_id
                JOIN airports a_from ON pd.departure_airport_id = a_from.id
                WHERE d.city = :destination
                AND a_from.code = :departureFrom
                AND pd.departure_date = :departureDate
                AND tp.duration_days = :duration
                AND tp.package_type = :packageType
                AND pd.available_slots >= :travelers
                ORDER BY pd.price ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':destination' => $destination,
            ':departureFrom' => $departureFrom,
            ':departureDate' => $departureDate,
            ':duration' => $duration,
            ':packageType' => $packageType,
            ':travelers' => $travelers
        ]);
        
        $availablePackages = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error searching packages: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    
  <meta charset="UTF-8">
  <title>Xetroot Aviation | Live Flight Info</title>
  <meta name="description" content="Get live updates on flights, airports, and airline schedules.">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://aviation.xetroot.com/">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Open Graph -->
  <meta property="og:title" content="Xetroot Aviation" />
  <meta property="og:description" content="Live flight and airport updates." />
  <meta property="og:image" content="https://aviation.xetroot.com/banner.jpg" />
  <meta property="og:url" content="https://aviation.xetroot.com/" />
  <meta property="og:type" content="website" />

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Xetroot Aviation">
  <meta name="twitter:description" content="Explore aviation data and airline info.">
  <meta name="twitter:image" content="https://aviation.xetroot.com/banner.jpg">

    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Modern CSS and JS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js@10.0.1/public/assets/styles/choices.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-plane"></i> SkyWings
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 main-nav">
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'flights.php' ? 'active' : '' ?>" href="flights.php">Flights</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'destinations.php' ? 'active' : '' ?>" href="destinations.php">Destinations</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'about.php' ? 'active' : '' ?>" href="about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : '' ?>" href="contact.php">Contact</a>
                        </li>
                    </ul>
                    
                    <div class="auth-buttons d-flex">
                        <?php if (isLoggedIn()): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <?= htmlspecialchars($_SESSION['userName'] ?? 'Guest') ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="my_profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                                    <li><a class="dropdown-item" href="my_booking.php"><i class="fas fa-ticket-alt me-2"></i> My Bookings</a></li>
                                    <?php if (isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-cog me-2"></i> Admin Panel</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="account/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="#" class="btn btn-primary mt-0 ms-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                            <a href="#" class="btn btn-primary mt-0 ms-2" data-bs-toggle="modal" data-bs-target="#registerModal">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Login Modal -->
      <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden">
          <div class="modal-header position-relative p-0">
            <div class="bg-gradient-primary-to-secondary w-100 py-4 px-5">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="modal-title text-white fs-4 fw-bold" id="loginModalLabel">
                  <i class="fas fa-user-circle me-2"></i>Welcome Back
                </h5>
                <button type="button" class="btn-close btn-close-white bg-transparent shadow-none" 
                        data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="text-white-50 small mt-1">Sign in to access your account</div>
            </div>
            <div class="position-absolute bottom-0 start-0 w-100">
              <svg viewBox="0 0 500 20" class="w-100" preserveAspectRatio="none">
                <path d="M0,20 C150,10 350,25 500,5 L500,20 L0,20 Z" fill="white"></path>
              </svg>
            </div>
          </div>
          
          <div class="modal-body px-5 py-4">
            <!-- Error Message Display -->
            <?php if (isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
              <i class="fas fa-exclamation-circle me-2 flex-shrink-0"></i>
              <div><?php echo htmlspecialchars($_SESSION['login_error']); ?></div>
              <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>
          
            <form id="loginForm" action="account/login_process.php" method="POST" class="needs-validation" novalidate>
              <div class="mb-4">
                <label for="loginEmail" class="form-label fw-medium text-muted small">Email address</label>
                <div class="input-group input-group-lg border rounded-3 bg-light overflow-hidden">
                  <span class="input-group-text bg-light border-0 text-primary px-3"><i class="fas fa-envelope"></i></span>
                  <input type="email" class="form-control border-0 shadow-none bg-light py-2" id="loginEmail" name="email" 
                        placeholder="name@example.com" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                  <div class="invalid-feedback">
                    Please enter a valid email
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="loginPassword" class="form-label fw-medium text-muted small">Password</label>
                <div class="input-group input-group-lg border rounded-3 bg-light overflow-hidden">
                  <span class="input-group-text bg-light border-0 text-primary px-3"><i class="fas fa-lock"></i></span>
                  <input type="password" class="form-control border-0 shadow-none bg-light py-2" id="loginPassword" name="password" required>
                  <button class="btn btn-link text-muted toggle-password bg-light border-0 px-3" type="button">
                    <i class="fas fa-eye-slash"></i>
                  </button>
                  <div class="invalid-feedback">
                    Please enter your password
                  </div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                    <label class="form-check-label small text-muted" for="rememberMe">Remember me</label>
                  </div>
                  <a href="#forgotPasswordModal" class="small text-decoration-none text-primary fw-medium" 
                    data-bs-toggle="modal" data-bs-dismiss="modal">Forgot password?</a>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 mt-4 py-3 fw-bold shadow-sm hover-lift">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
              </button>
            </form>
            
            <div class="position-relative my-4">
              <hr class="my-4 border-1 opacity-10">
              <div class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">or continue with</div>
            </div>
            
            <div class="d-flex justify-content-center gap-3 mb-4">
              <button type="button" class="btn btn-outline-primary rounded-circle social-btn d-flex align-items-center justify-content-center hover-lift-sm">
                <i class="fab fa-google"></i>
              </button>
              <button type="button" class="btn btn-outline-primary rounded-circle social-btn d-flex align-items-center justify-content-center hover-lift-sm">
                <i class="fab fa-apple"></i>
              </button>
              <button type="button" class="btn btn-outline-primary rounded-circle social-btn d-flex align-items-center justify-content-center hover-lift-sm">
                <i class="fab fa-facebook-f"></i>
              </button>
            </div>
            
            <div class="text-center small">
              <p class="text-muted">Don't have an account? 
                <a href="#registerModal" class="text-primary fw-medium text-decoration-none hover-underline" 
                  data-bs-toggle="modal" data-bs-dismiss="modal">Sign up</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php if (isset($_SESSION['register_errors'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-circle me-2 flex-shrink-0"></i>
        <div><?php echo htmlspecialchars(implode('<br>', $_SESSION['register_errors'])); ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['register_errors']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['register_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2 flex-shrink-0"></i>
        <div><?php echo htmlspecialchars($_SESSION['register_success']); ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['register_success']); ?>
    <?php endif; ?>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden">
          <div class="modal-header position-relative p-0">
            <div class="bg-gradient-primary-to-secondary w-100 py-4 px-5">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="modal-title text-white fs-4 fw-bold" id="registerModalLabel">
                  <i class="fas fa-user-plus me-2"></i>Join Us
                </h5>
                <button type="button" class="btn-close btn-close-white bg-transparent shadow-none" 
                        data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="text-white-50 small mt-1">Create your account in seconds</div>
            </div>
            <div class="position-absolute bottom-0 start-0 w-100">
              <svg viewBox="0 0 500 20" class="w-100" preserveAspectRatio="none">
                <path d="M0,20 C150,10 350,25 500,5 L500,20 L0,20 Z" fill="white"></path>
              </svg>
            </div>
          </div>
          
          <div class="modal-body px-5 py-4">
            <form id="registerForm" action="account/register_process.php" method="POST" class="needs-validation" novalidate>
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="firstName" class="form-label fw-medium text-muted small">First Name</label>
                  <div class="input-group input-group-lg border rounded-3 bg-light overflow-hidden">
                    <span class="input-group-text bg-light border-0 text-primary px-3"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control border-0 shadow-none bg-light py-2" id="firstName" name="firstName" required>
                    <div class="invalid-feedback">
                      Please enter your first name
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="lastName" class="form-label fw-medium text-muted small">Last Name</label>
                  <div class="input-group input-group-lg border rounded-3 bg-light overflow-hidden">
                    <span class="input-group-text bg-light border-0 text-primary px-3"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control border-0 shadow-none bg-light py-2" id="lastName" name="lastName" required>
                    <div class="invalid-feedback">
                      Please enter your last name
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mt-3">
                <label for="registerEmail" class="form-label fw-medium text-muted small">Email address</label>
                  <div class="input-group input-group-lg border rounded-3 bg-light overflow-hidden">
                      <span class="input-group-text bg-light border-0 text-primary px-3"><i class="fas fa-envelope"></i></span>
                      <input type="email" class="form-control border-0 shadow-none bg-light py-2" id="registerEmail" name="email" required>
                      <div class="invalid-feedback">
                          Please enter a valid email
                      </div>
                  </div>
                  <div class="valid-feedback"></div>
              </div>
              
              <div class="mt-3">
                <label for="registerPassword" class="form-label fw-medium text-muted small">Password</label>
                <div class="input-group input-group-lg border rounded-3 bg-light overflow-hidden">
                  <span class="input-group-text bg-light border-0 text-primary px-3"><i class="fas fa-lock"></i></span>
                  <input type="password" class="form-control border-0 shadow-none bg-light py-2" id="registerPassword" name="password" required minlength="8">
                  <button class="btn btn-link text-muted toggle-password bg-light border-0 px-3" type="button">
                    <i class="fas fa-eye-slash"></i>
                  </button>
                  <div class="invalid-feedback">
                    Password must be at least 8 characters
                  </div>
                </div>
                <div class="form-text small text-muted">Minimum 8 characters with at least one number</div>
              </div>
              
              <div class="mt-3">
                <label for="confirmPassword" class="form-label fw-medium text-muted small">Confirm Password</label>
                <div class="input-group input-group-lg border rounded-3 bg-light overflow-hidden">
                  <span class="input-group-text bg-light border-0 text-primary px-3"><i class="fas fa-lock"></i></span>
                  <input type="password" class="form-control border-0 shadow-none bg-light py-2" id="confirmPassword" name="confirmPassword" required>
                  <div class="invalid-feedback">
                    Passwords must match
                  </div>
                </div>
              </div>
              
              <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
              
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="termsAgree" name="termsAgree" required>
                <label class="form-check-label small text-muted" for="termsAgree">
                  I agree to the <a href="#" class="text-primary text-decoration-none hover-underline">Terms</a> and <a href="#" class="text-primary text-decoration-none hover-underline">Privacy Policy</a>
                </label>
              </div>
              
              <button type="submit" name="signup" class="btn btn-primary btn-lg w-100 rounded-3 mt-4 py-3 fw-bold shadow-sm hover-lift">
                <i class="fas fa-user-plus me-2"></i>Create Account
              </button>
            </form>
            
            <div class="position-relative my-4">
              <hr class="my-4 border-1 opacity-10">
              <div class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">or sign up with</div>
            </div>
            
            <div class="d-flex justify-content-center gap-3 mb-4">
              <button type="button" class="btn btn-outline-primary rounded-circle social-btn d-flex align-items-center justify-content-center hover-lift-sm">
                <i class="fab fa-google"></i>
              </button>
              <button type="button" class="btn btn-outline-primary rounded-circle social-btn d-flex align-items-center justify-content-center hover-lift-sm">
                <i class="fab fa-apple"></i>
              </button>
              <button type="button" class="btn btn-outline-primary rounded-circle social-btn d-flex align-items-center justify-content-center hover-lift-sm">
                <i class="fab fa-facebook-f"></i>
              </button>
            </div>
            
            <div class="text-center small">
              <p class="text-muted">Already have an account? 
                <a href="#loginModal" class="text-primary fw-medium text-decoration-none hover-underline" 
                  data-bs-toggle="modal" data-bs-dismiss="modal">Sign in</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden">
          <div class="modal-header position-relative p-0">
            <div class="bg-gradient-primary-to-secondary w-100 py-4 px-5">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="modal-title text-white fs-4 fw-bold" id="forgotPasswordModalLabel">
                  <i class="fas fa-key me-2"></i>Reset Password
                </h5>
                <button type="button" class="btn-close btn-close-white bg-transparent shadow-none" 
                        data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="text-white-50 small mt-1">We'll help you get back into your account</div>
            </div>
            <div class="position-absolute bottom-0 start-0 w-100">
              <svg viewBox="0 0 500 20" class="w-100" preserveAspectRatio="none">
                <path d="M0,20 C150,10 350,25 500,5 L500,20 L0,20 Z" fill="white"></path>
              </svg>
            </div>
          </div>
          
          <div class="modal-body px-5 py-4">
            <form id="forgotPasswordForm" action="forgot_password.php" method="POST" class="needs-validation" novalidate>
              <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                  <i class="fas fa-envelope-open-text text-primary fs-2"></i>
                </div>
                <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
              </div>
              
              <div class="mb-4">
                <label for="forgotEmail" class="form-label fw-medium text-muted small">Email address</label>
                <div class="input-group input-group-lg border rounded-3 bg-light overflow-hidden">
                  <span class="input-group-text bg-light border-0 text-primary px-3"><i class="fas fa-envelope"></i></span>
                  <input type="email" class="form-control border-0 shadow-none bg-light py-2" id="forgotEmail" name="email" required>
                  <div class="invalid-feedback">
                    Please enter a valid email
                  </div>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 py-3 fw-bold shadow-sm hover-lift">
                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
              </button>
            </form>
            
            <div class="text-center small mt-4">
              <a href="#loginModal" class="text-primary fw-medium text-decoration-none hover-underline d-inline-flex align-items-center" 
                data-bs-toggle="modal" data-bs-dismiss="modal">
                <i class="fas fa-arrow-left me-2"></i> Back to login
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modern JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+F2Wzebd1zKkf0r8bKjfgT2PmPw4L" 
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js@10.0.1/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
    //Email check for registration
    document.getElementById('registerEmail').addEventListener('blur', function() {
        const email = this.value;
        const emailFeedback = this.nextElementSibling; // Assuming feedback element follows input
        
        if (!email) return;
        
        // Simple email format validation first
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            this.setCustomValidity('Please enter a valid email address');
            this.reportValidity();
            return;
        }
        
        // Show loading indicator
        this.classList.add('is-checking');
        
        // AJAX request to check email
        fetch('ajax/check_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            this.classList.remove('is-checking');
            
            if (data.error) {
                console.error(data.message);
                return;
            }
            
            if (!data.available) {
                this.setCustomValidity(data.message);
                this.reportValidity();
                this.classList.add('is-invalid');
                emailFeedback.textContent = data.message;
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                emailFeedback.textContent = data.message;
                emailFeedback.style.display = 'block';
                emailFeedback.classList.remove('invalid-feedback');
                emailFeedback.classList.add('valid-feedback');
            }
        })
        .catch(error => {
            this.classList.remove('is-checking');
            console.error('Error checking email:', error);
        });
    });

    // Clear validation when user starts typing again
    document.getElementById('registerEmail').addEventListener('input', function() {
        this.classList.remove('is-valid', 'is-invalid');
        this.setCustomValidity('');
    });

    // Form validation and interaction scripts
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form validation
        document.querySelectorAll('.needs-validation').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.classList.add('was-validated');
            }, false);
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.closest('.input-group').querySelector('input');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                }
            });
        });

        // Auto-show modal if there's an error or show_login parameter
        const urlParams = new URLSearchParams(window.location.search);
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        
        if (urlParams.has('show_login') || <?php echo isset($_SESSION['login_error']) ? 'true' : 'false'; ?>) {
            loginModal.show();
            
            // Clear the parameter from URL without reloading
            if (urlParams.has('show_login')) {
                const newUrl = window.location.protocol + "//" + window.location.host + 
                              window.location.pathname + window.location.hash;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }
        }
        
        // Focus on email field when modal opens
        document.getElementById('loginModal').addEventListener('shown.bs.modal', function() {
            document.getElementById('loginEmail').focus();
        });

        // Password match validation for register form
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                document.getElementById('confirmPassword').setCustomValidity("Passwords don't match");
                document.getElementById('confirmPassword').reportValidity();
                e.preventDefault();
            } else {
                document.getElementById('confirmPassword').setCustomValidity('');
            }
        });
        
        // Terms agreement validation
        document.getElementById('termsAgree').addEventListener('change', function() {
            this.setCustomValidity(this.checked ? '' : 'You must agree to the terms');
        });

        // Initialize AOS animation
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });
        }
        
        // Initialize Owl Carousel if needed
        if (typeof OwlCarousel !== 'undefined') {
            document.querySelectorAll('.owl-carousel').forEach(el => {
                new OwlCarousel(el, {
                    loop: true,
                    margin: 10,
                    nav: true,
                    responsive: {
                        0: { items: 1 },
                        600: { items: 2 },
                        1000: { items: 3 }
                    }
                });
            });
        }
        
        // Initialize flatpickr for date inputs
        if (typeof flatpickr !== 'undefined') {
            document.querySelectorAll('.datepicker').forEach(el => {
                flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    minDate: 'today'
                });
            });
        }
    });
    </script>
    <style>
      /* Custom Styles */
      .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }
      
      .input-group-lg {
        height: 50px;
      }
      
      .input-group-text {
        transition: all 0.3s ease;
      }
      
      .form-control:focus {
        box-shadow: none;
      }
      
      .form-control:focus + .input-group-text {
        color: #764ba2;
      }
      
      .social-btn {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
      }
      
      .social-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      }
      
      .toggle-password {
        cursor: pointer;
      }
      
      .modal-content {
        border-radius: 15px;
        overflow: hidden;
      }
      
      .border-bottom {
        border-bottom: 2px solid #e9ecef !important;
      }
      
      .border-bottom:focus-within {
        border-bottom-color: #764ba2 !important;
      }
      
      .btn-primary {
        background-color: #667eea;
        border-color: #667eea;
        transition: all 0.3s ease;
      }
      
      .btn-primary:hover {
        background-color: #5a6fd1;
        border-color: #5a6fd1;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
      }

      /* Add to your existing styles */
.is-checking {
    /* background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%23667eea' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-loader'%3E%3Cline x1='12' y1='2' x2='12' y2='6'%3E%3C/line%3E%3Cline x1='12' y1='18' x2='12' y2='22'%3E%3C/line%3E%3Cline x1='4.93' y1='4.93' x2='7.76' y2='7.76'%3E%3C/line%3E%3Cline x1='16.24' y1='16.24' x2='19.07' y2='19.07'%3E%3C/line%3E%3Cline x1='2' y1='12' x2='6' y2='12'%3E%3C/line%3E%3Cline x1='18' y1='12' x2='22' y2='12'%3E%3C/line%3E%3Cline x1='4.93' y1='19.07' x2='7.76' y2='16.24'%3E%3C/line%3E%3Cline x1='16.24' y1='7.76' x2='19.07' y2='4.93'%3E%3C/line%3E%3C/svg%3E"); */
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 20px;
    padding-right: 35px;
}

.is-valid {
    border-color: #28a745 !important;
}

.is-invalid {
    border-color:rgb(250, 0, 25) !important;
}

.valid-feedback {
    display: none;
    color: #28a745;
    font-size: 0.875em;
    margin-top: 0.25rem;
}
    </style>
</body>
</html>
<?php
require_once __DIR__ . '/config/dbconfig.php';

// Check if we should show welcome modal
$showWelcomeModal = false;
if (!isset($_SESSION['last_welcome_modal']) || (time() - $_SESSION['last_welcome_modal']) > (12 * 60 * 60)) {
    $showWelcomeModal = true;
    $_SESSION['last_welcome_modal'] = time();
}

include_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="assets/css/index.css">

<div id="initialLoader" style="position:fixed; top:0; left:0; width:100%; height:100%; background:#fff; z-index:9999; display:none; justify-content:center; align-items:center;">
    <div class="loader" style="width:48px; height:48px; border:5px solid #2196f3; border-bottom-color:transparent; border-radius:50%; animation:rotation 1s linear infinite;"></div>
</div>

<script>
// Check if this is the first visit in this session
if (!sessionStorage.getItem('loadingShown')) {
    document.getElementById('initialLoader').style.display = 'flex';
    sessionStorage.setItem('loadingShown', 'true');
    
    setTimeout(function() {
        document.getElementById('initialLoader').style.display = 'none';
    }, 3000);
}
</script>
<style>
@keyframes rotation {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Main Content -->
<main class="main-content">
    <!-- Search Box Section with Video Background -->
    <section class="search-section">
         <div class="hero-content">
            <h1 class="hero-title animate-fadeInUp text-light">
                <span >EXPLORE THE WORLD <i class="fas fa-globe-americas" aria-hidden="true" style="color:#2196f3;"></i></span>
                WITH CONFIDENCE <i class="fas fa-shield-alt" aria-hidden="true" style="color:#2196f3;"></i>
            </h1>
            <p class="hero-subtitle">Find the best flight deals to over 2,000 destinations worldwide</p>
        </div>
        
        <!-- Search Container -->
        <div class="search-container">
            <div class="container">
                <div class="search-box card">
                    <div class="search-tabs">
                        <button class="active" data-tab="flights-tab"><i class="fas fa-plane me-2"></i> Flights</button>
                        <button data-tab="hotels-tab"><i class="fas fa-hotel me-2"></i> Hotels</button>
                        <button data-tab="packages-tab"><i class="fas fa-suitcase-rolling me-2"></i> Packages</button>
                    </div>
                    
                    <!-- Flights Tab -->
                    <div id="flights-tab" class="tab-content active">
                        <form action="search_flights.php" method="GET">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="from"><i class="fas fa-map-marker-alt me-2"></i> From</label>
                                    <select name="from" id="from" required>
                                        <option value="">Select city or airport</option>
                                        <?php foreach ($airports as $airport): ?>
                                        <option value="<?= htmlspecialchars($airport['code']) ?>">
                                            <?= htmlspecialchars($airport['city']) ?> (<?= htmlspecialchars($airport['code']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="to"><i class="fas fa-map-marker-alt me-2"></i> To</label>
                                    <select name="to" id="to" required>
                                        <option value="">Select city or airport</option>
                                        <?php foreach ($airports as $airport): ?>
                                        <option value="<?= htmlspecialchars($airport['code']) ?>">
                                            <?= htmlspecialchars($airport['city']) ?> (<?= htmlspecialchars($airport['code']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="departure"><i class="far fa-calendar-alt me-2"></i> Departure</label>
                                    <input type="date" name="departure" id="departure" required 
                                           value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="return"><i class="far fa-calendar-alt me-2"></i> Return</label>
                                    <input type="date" name="return" id="return" 
                                           min="<?= date('Y-m-d') ?>" placeholder="Optional">
                                </div>
                                
                                <div class="form-group">
                                    <label for="passengers"><i class="fas fa-users me-2"></i> Travelers</label>
                                    <div class="passenger-selector" place>
                                        <select name="passengers" id="passengers" required onclick="showPassengerModal()" readonly>
                                            <option value="1">1 Adult</option>
                                            <option value="2">2 Adults</option>
                                            <option value="3">3 Adults</option>
                                            <option value="4">4 Adults</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="class"><i class="fas fa-chair me-2"></i> Class</label>
                                    <select name="class" id="class" required>
                                        <option value="economy">Economy</option>
                                        <option value="premium">Premium Economy</option>
                                        <option value="business">Business</option>
                                        <option value="first">First Class</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary search-btn">
                                        <i class="fas fa-search me-2"></i> Search Flights
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Hotels Tab -->
                    <div id="hotels-tab" class="tab-content">
                        <form action="search-hotels.php" method="GET">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="destination"><i class="fas fa-map-marker-alt me-2"></i> Destination</label>
                                    <select name="destination" id="destination" required>
                                        <option value="">Select city</option>
                                        <?php foreach ($popularDestinations as $destination): ?>
                                        <option value="<?= htmlspecialchars($destination['city']) ?>">
                                            <?= htmlspecialchars($destination['city']) ?>, <?= htmlspecialchars($destination['country']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="check-in"><i class="far fa-calendar-alt me-2"></i> Check-in</label>
                                    <input type="date" name="check-in" id="check-in" required 
                                           min="<?= date('Y-m-d') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="check-out"><i class="far fa-calendar-alt me-2"></i> Check-out</label>
                                    <input type="date" name="check-out" id="check-out" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="rooms"><i class="fas fa-bed me-2"></i> Rooms</label>
                                    <select name="rooms" id="rooms" required>
                                        <option value="1">1 Room</option>
                                        <option value="2">2 Rooms</option>
                                        <option value="3">3 Rooms</option>
                                        <option value="4">4 Rooms</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="guests"><i class="fas fa-users me-2"></i> Guests</label>
                                    <select name="guests" id="guests" required>
                                        <option value="1">1 Guest</option>
                                        <option value="2">2 Guests</option>
                                        <option value="3">3 Guests</option>
                                        <option value="4">4 Guests</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="hotel-class"><i class="fas fa-star me-2"></i> Hotel Class</label>
                                    <select name="hotel-class" id="hotel-class" required>
                                        <option value="3">3 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="5">5 Stars</option>
                                        <option value="luxury">Luxury</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary search-btn">
                                        <i class="fas fa-search me-2"></i> Search Hotels
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Packages Tab -->
                    <div id="packages-tab" class="tab-content">
                        <form action="search-packages.php" method="GET">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="package-destination"><i class="fas fa-map-marker-alt me-2"></i> Destination</label>
                                    <select name="package-destination" id="package-destination" required>
                                        <option value="">Select destination</option>
                                        <?php foreach ($popularDestinations as $destination): ?>
                                        <option value="<?= htmlspecialchars($destination['city']) ?>">
                                            <?= htmlspecialchars($destination['city']) ?>, <?= htmlspecialchars($destination['country']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="package-from"><i class="fas fa-plane-departure me-2"></i> Departure From</label>
                                    <select name="package-from" id="package-from" required>
                                        <option value="">Select city or airport</option>
                                        <?php foreach ($airports as $airport): ?>
                                        <option value="<?= htmlspecialchars($airport['code']) ?>">
                                            <?= htmlspecialchars($airport['city']) ?> (<?= htmlspecialchars($airport['code']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="package-departure"><i class="far fa-calendar-alt me-2"></i> Departure Date</label>
                                    <input type="date" name="package-departure" id="package-departure" required 
                                           min="<?= date('Y-m-d') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="package-duration"><i class="far fa-clock me-2"></i> Duration</label>
                                    <select name="package-duration" id="package-duration" required>
                                        <option value="3">3 Days</option>
                                        <option value="5">5 Days</option>
                                        <option value="7">7 Days</option>
                                        <option value="10">10 Days</option>
                                        <option value="14">14 Days</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="package-travelers"><i class="fas fa-users me-2"></i> Travelers</label>
                                    <select name="package-travelers" id="package-travelers" required>
                                        <option value="1">1 Traveler</option>
                                        <option value="2">2 Travelers</option>
                                        <option value="3">3 Travelers</option>
                                        <option value="4">4 Travelers</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="package-type"><i class="fas fa-suitcase me-2"></i> Package Type</label>
                                    <select name="package-type" id="package-type" required>
                                        <option value="family">Family Vacation</option>
                                        <option value="honeymoon">Honeymoon</option>
                                        <option value="adventure">Adventure</option>
                                        <option value="luxury">Luxury Getaway</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary search-btn">
                                        <i class="fas fa-search me-2"></i> Search Packages
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Flights -->
    <section class="section featured-flights">
        <div class="container-fluid">
            <div class="section-header">
                <h2 class="section-title">Featured Flights</h2>
                <p class="section-subtitle">Top upcoming flights from your nearest airport</p>
                <a href="flights.php" class="btn btn-outline-primary view-all">View All Flights</a>
            </div>
            
            <div class="flights-grid">
                <?php foreach ($featuredFlights as $flight): ?>
                <div class="flight-card">
                    <div class="flight-card">
                        <div class="flight-header">
                            <?php if (!empty($flight['airline_logo'])): ?>
                                <img src="<?= htmlspecialchars($flight['airline_logo']) ?>" 
                                    alt="<?= htmlspecialchars($flight['airline_name']) ?>" 
                                    class="airline-logo">
                            <?php endif; ?>
                            <div class="flight-info">
                                <h3 class="airline-name"><?= htmlspecialchars($flight['airline_name'] ?? 'Unknown Airline') ?></h3>
                                <p class="flight-number"><?= htmlspecialchars($flight['flight_number']) ?></p>
                            </div>
                        </div>
                        
                        <div class="flight-details">
                            <div class="departure">
                                <div class="time"><?= date('H:i', strtotime($flight['departure_time'])) ?></div>
                                <div class="airport-code"><?= htmlspecialchars($flight['departure_code']) ?></div>
                                <div class="city"><?= htmlspecialchars($flight['departure_city']) ?></div>
                            </div>
                            
                            <div class="col-2">
                                <div class="flight-duration text-center small text-muted">
                                    <div class="city"><?= htmlspecialchars($flight['departure_city']) ?></div>
                                </div>
                                    <div class="flight-path position-relative mt-1">
                                        <div class="position-absolute top-50 start-0 end-0 border-top border-2 border-secondary opacity-25"></div>
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fas fa-plane text-primary"></i>
                                        </div>
                                    </div>
                                            
                                    <div class="text-center small text-muted">Non-stop
                                    </div>               
                                <div class="duration-text">
                                    <?= $flight['duration_hours'] ?>h <?= $flight['duration_minutes'] ?>m
                                </div>
                            </div>
                            
                            <div class="arrival">
                                <div class="time"><?= date('H:i', strtotime($flight['arrival_time'])) ?></div>
                                <div class="airport-code"><?= htmlspecialchars($flight['arrival_code']) ?></div>
                                <div class="city"><?= htmlspecialchars($flight['arrival_city']) ?></div>
                            </div>
                        </div>
                        
                        <div class="flight-price">
                            <div class="price-container">
                                <span class="price">$<?= number_format($flight['economy_price'], 2) ?></span>
                                <small class="price-label">Economy from</small>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="booking_summary.php?flight_id=<?= $flight['flight_id'] ?>" class="btn btn-primary book-btn">
                                    Book Now
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary book-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    Book Now
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flight-card-hover">
                        <div class="hover-content">
                            <h4>Flight Details</h4>
                            <ul>
                                <li><i class="fas fa-plane-departure"></i> Departure: <?= date('D, M j', strtotime($flight['departure_time'])) ?></li>
                                <li><i class="fas fa-plane-arrival"></i> Arrival: <?= date('D, M j', strtotime($flight['arrival_time'])) ?></li>
                                <li><i class="fas fa-chair"></i> Available Classes: Economy, Business</li>
                            </ul>
                            <div class="flight-badges">
                                <span class="badge"><?= $flight['stops'] == 0 ? 'Non-stop' : $flight['stops'].' stop'.($flight['stops'] > 1 ? 's' : '') ?></span>
                                <span class="badge">Free Cancellation</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    
    <!-- Travel Inspiration Section -->
    <section class="travel-inspiration py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Find Your Travel Inspiration</h2>
                <p class="section-subtitle">Discover destinations based on your interests</p>
            </div>
            
            <div class="inspiration-categories">
                <div class="category-card">
                    <img src="assets/images/travel/beach-vacation.jpg" alt="Beach Vacation">
                    <div class="category-overlay">
                        <h3>Beach Getaways</h3>
                        <a href="#" class="btn btn-outline-light">Explore</a>
                    </div>
                </div>
                <div class="category-card">
                    <img src="assets/images/travel/city-breaks.jpg" alt="City Breaks">
                    <div class="category-overlay">
                        <h3>City Breaks</h3>
                        <a href="#" class="btn btn-outline-light">Explore</a>
                    </div>
                </div>
                <div class="category-card">
                    <img src="assets/images/travel/adventure-travel.jpg" alt="Adventure Travel">
                    <div class="category-overlay">
                        <h3>Adventure</h3>
                        <a href="#" class="btn btn-outline-light">Explore</a>
                    </div>
                </div>
                <div class="category-card">
                    <img src="assets/images/travel/luxury-retreats.jpg" alt="Luxury Retreats">
                    <div class="category-overlay">
                        <h3>Luxury Retreats</h3>
                        <a href="#" class="btn btn-outline-light">Explore</a>
                    </div>
                </div>
                <div class="category-card">
                    <img src="assets/images/travel/family-vacations.jpg" alt="Family Vacations">
                    <div class="category-overlay">
                        <h3>Family Fun</h3>
                        <a href="#" class="btn btn-outline-light">Explore</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Popular Destinations -->
    <section class="section popular-destinations">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Popular Destinations</h2>
                <p class="section-subtitle">Where other travelers are going this season</p>
                <a href="destinations.php" class="btn btn-outline-primary view-all">View All Destinations</a>
            </div>
            
            <div class="destinations-grid">
                <?php foreach ($popularDestinations as $destination): ?>
                <div class="destination-card">
                    <div class="destination-image">
                        <?php
                            $imageUrl = !empty($destination['image_url']) ? $destination['image_url'] : 'assets/images/default-destination.jpg';
                        ?>
                        <img src="<?= htmlspecialchars($imageUrl) ?>"
                             alt="<?= htmlspecialchars($destination['city']) ?>" 
                             class="img-fluid">
                        <div class="overlay"></div>
                    </div>
                    <div class="destination-info">
                        <h3><?= htmlspecialchars($destination['city']) ?></h3>
                        <p><?= htmlspecialchars($destination['country']) ?></p>
                        <div class="price-badge">
                            Flights from <span>$<?= rand(199, 599) ?></span>
                        </div>
                        <a href="search_flights.php?to=<?= urlencode($destination['city']) ?>" 
                           class="btn btn-outline-light explore-btn">
                            Explore <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Value Proposition Section -->
    <section class="value-proposition py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon bg-primary">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h3>Exclusive Discounts</h3>
                        <p>Get access to member-only deals and flash sales with savings up to 60%</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon bg-success">
                            <i class="fas fa-route"></i>
                        </div>
                        <h3>Smart Routing</h3>
                        <p>Our algorithm finds the fastest and most economical routes for you</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon bg-info">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Mobile Friendly</h3>
                        <p>Book and manage trips on the go with our award-winning app</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Travel Deals Countdown -->
    <section class="deals-countdown py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>Limited-Time Flash Sale: Book Now & Save Big!</h2>
                    <p>Book within the next 24 hours to secure these exclusive deals</p>
                </div>
                <div class="col-md-6">
                    <div class="countdown-timer">
                        <div class="countdown-item">
                            <span id="countdown-hours">24</span>
                            <small>Hours</small>
                        </div>
                        <div class="countdown-item">
                            <span id="countdown-minutes">59</span>
                            <small>Minutes</small>
                        </div>
                        <div class="countdown-item">
                            <span id="countdown-seconds">59</span>
                            <small>Seconds</small>
                        </div>
                    </div>
                    <a href="deals.php" class="btn btn-light mt-3">View All Deals</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Map Section -->
    <section class="interactive-map py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Explore Our Global Network</h2>
                <p class="section-subtitle">Click on any region to discover flight options</p>
            </div>
            
            <div class="world-map-container">
                <div class="world-map">
                    <img src="assets/images/world.svg" alt="World Map" usemap="#worldMap">
                    <map name="worldMap">
                        <area shape="circle" coords="250,150,30" href="search_flights.php?region=europe" alt="Europe">
                        <area shape="circle" coords="350,180,30" href="search_flights.php?region=asia" alt="Asia">
                        <area shape="circle" coords="150,180,30" href="search_flights.php?region=north-america" alt="North America">
                        <area shape="circle" coords="180,250,30" href="search_flights.php?region=south-america" alt="South America">
                        <area shape="circle" coords="300,250,30" href="search_flights.php?region=africa" alt="Africa">
                        <area shape="circle" coords="450,250,30" href="search_flights.php?region=australia" alt="Australia">
                    </map>
                </div>
                <div class="map-stats">
                    <div class="stat-item">
                        <h3>2,000+</h3>
                        <p>Destinations Worldwide</p>
                    </div>
                    <div class="stat-item">
                        <h3>50+</h3>
                        <p>Partner Airlines</p>
                    </div>
                    <div class="stat-item">
                        <h3>1M+</h3>
                        <p>Happy Travelers</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Travel Requirements Section -->
    <section class="travel-requirements py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Travel Requirements</h2>
                <p class="section-subtitle">Stay informed about entry restrictions</p>
            </div>
            
            <div class="requirements-search">
                <div class="search-box">
                    <h3>Check Travel Restrictions</h3>
                    <form>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="from-country" class="form-label">Traveling From</label>
                                <select class="form-select" id="from-country">
                                    <option selected>Select country</option>
                                    <?php foreach ($countries as $country): ?>
                                    <option value="<?= htmlspecialchars($country['code']) ?>"><?= htmlspecialchars($country['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="to-country" class="form-label">Traveling To</label>
                                <select class="form-select" id="to-country">
                                    <option selected>Select country</option>
                                    <?php foreach ($countries as $country): ?>
                                    <option value="<?= htmlspecialchars($country['code']) ?>"><?= htmlspecialchars($country['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Check</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="requirements-result mt-4">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                <h4 class="alert-heading">United States to France</h4>
                                <p class="mb-0">Last updated: June 15, 2023</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="requirement-card">
                                <div class="requirement-icon">
                                    <i class="fas fa-syringe"></i>
                                </div>
                                <h5>Vaccination</h5>
                                <p>Not required for entry</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="requirement-card">
                                <div class="requirement-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h5>Visa</h5>
                                <p>Not required for stays under 90 days</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="requirement-card">
                                <div class="requirement-icon">
                                    <i class="fas fa-head-side-mask"></i>
                                </div>
                                <h5>Masks</h5>
                                <p>Recommended in crowded places</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Real reviews from real travelers</p>
            </div>
            
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $testimonial): ?>
                <div class="testimonial-card card">
                    <div class="testimonial-header">
                        <img src="<?= htmlspecialchars($testimonial['user_image']) ?>" 
                             alt="<?= htmlspecialchars($testimonial['user_name']) ?>" 
                             class="user-image">
                        <div class="user-info">
                            <h3><?= htmlspecialchars($testimonial['user_name']) ?></h3>
                        </div>
                    </div>
                    <div class="testimonial-body">
                        <p>"<?= htmlspecialchars($testimonial['review']) ?>"</p>
                    </div>
                    <div class="testimonial-footer">
                        <small>
                            <?php
                                $createdAt = $testimonial['created_at'] ?? '';
                                $timestamp = strtotime($createdAt);
                                echo $timestamp ? date('F Y', $timestamp) : 'Unknown date';
                            ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="section blog-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Latest Travel Tips & Guides</h2>
                <p class="section-subtitle">Stay updated with our travel blog</p>
                <a href="blog.php" class="btn btn-outline-primary view-all">View All Posts</a>
            </div>
            
            <div class="blog-grid">
                <?php foreach ($blogPosts as $post): ?>
                <div class="blog-card card">
                    <div class="blog-image-container">
                        <img src="<?= htmlspecialchars($post['image_url']) ?>" 
                             alt="<?= htmlspecialchars($post['title']) ?>" 
                             class="blog-image">
                        <div class="blog-date">
                            <?= date('M d, Y', strtotime($post['updated_at'] ?? 'now')) ?>
                        </div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-category"><?= htmlspecialchars($post['category']) ?></div>
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <p><?= htmlspecialchars($post['excerpt']) ?></p>
                        <a href="blog.php?post_id=<?= $post['id'] ?>" class="btn btn-link read-more">
                            Read More <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Sustainability Section -->
    <section class="sustainability py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="sustainability-content">
                        <h2 class="section-title">Travel Sustainably</h2>
                        <p class="lead">We're committed to reducing the environmental impact of travel</p>
                        
                        <div class="sustainability-features">
                            <div class="feature-item">
                                <i class="fas fa-leaf"></i>
                                <div>
                                    <h4>Carbon Offset Program</h4>
                                    <p>Offset your flight's carbon emissions at checkout</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-recycle"></i>
                                <div>
                                    <h4>Eco-Certified Hotels</h4>
                                    <p>Choose from thousands of sustainable accommodations</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-solar-panel"></i>
                                <div>
                                    <h4>Green Partners</h4>
                                    <p>We work with airlines committed to sustainable aviation</p>
                                </div>
                            </div>
                        </div>
                        
                        <a href="sustainability.php" class="btn btn-outline-primary mt-3">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="sustainability-image">
                        <img src="assets/images/sustainable-travel.jpg" alt="Sustainable Travel" class="img-fluid rounded">
                        <div class="sustainability-badge">
                            <i class="fas fa-award"></i>
                            <span>Certified Sustainable Travel Partner</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Subscription -->
    <section class="section newsletter-section">
        <div class="container">
            <div class="newsletter-container">
                <div class="newsletter-content">
                    <h2>Get Travel Deals & Tips</h2>
                    <p>Subscribe to our newsletter and receive exclusive offers and travel inspiration</p>
                    <form action="subscribe.php" method="POST" class="newsletter-form">
                        <div class="input-group">
                            <input type="email" name="email" placeholder="Enter your email" required>
                            <button type="submit" class="btn btn-primary">
                                Subscribe <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="agree-terms" required>
                            <label for="agree-terms">I agree to receive emails and accept the <a href="#">privacy policy</a></label>
                        </div>
                    </form>
                </div>
                <div class="newsletter-image">
                    <img src="assets/images/newsletter-travel.png" alt="Travel newsletter illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section features-section my-5">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Safe Travel</h3>
                    <p>Your safety is our top priority with enhanced cleaning protocols</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <h3>Best Prices</h3>
                    <p>Guaranteed lowest prices or we'll match the difference</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our travel experts are available anytime to assist you</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-plane-circle-check"></i>
                    </div>
                    <h3>Flexible Booking</h3>
                    <p>Free changes on most flights up to 24 hours before departure</p>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Welcome Modal -->
<!-- <?php if ($showWelcomeModal): ?>
<div class="modal fade" id="welcomeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body">
                <div class="welcome-content">
                    <img src="assets/images/welcome-gift.png" alt="Welcome gift" class="welcome-image">
                    <h2>Welcome to SkyWings!</h2>
                    <p>Get <strong>10% off</strong> your first booking when you sign up today</p>
                    <div class="welcome-code">
                        <span>Use code: </span>
                        <strong>WELCOME10</strong>
                    </div>
                    <div class="welcome-buttons">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" class="btn btn-primary">Sign Up Now</a>
                        <button class="btn btn-link" data-bs-dismiss="modal">Maybe Later</button>
                    </div>
                    <div class="welcome-footer">
                        <small>Offer valid for new customers only. Expires in 7 days.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?> -->


<!-- Passenger Selection Modal -->
<div id="passengerModal" class="modals">
    <div class="modals-content">
        <span class="close" onclick="hidePassengerModal()">&times;</span>
        <h3>Travelers</h3>
        <p>Choose a person to join you on your journey</p>
        
        <div class="passenger-category">
            <div class="category-row">
                <div>
                    <h4>Adults</h4>
                    <p>12 years & above</p>
                </div>
                <div class="counter">
                    <button onclick="changeCount('adults', -1)">-</button>
                    <span id="adults-count">1</span>
                    <button onclick="changeCount('adults', 1)">+</button>
                </div>
            </div>
            
            <div class="category-row">
                <div>
                    <h4>Children</h4>
                    <p>From 5 to under 12</p>
                </div>
                <div class="counter">
                    <button onclick="changeCount('children', -1)">-</button>
                    <span id="children-count">0</span>
                    <button onclick="changeCount('children', 1)">+</button>
                </div>
            </div>
            
            <div class="category-row">
                <div>
                    <h4>Kids</h4>
                    <p>From 2 to under 5</p>
                </div>
                <div class="counter">
                    <button onclick="changeCount('kids', -1)">-</button>
                    <span id="kids-count">0</span>
                    <button onclick="changeCount('kids', 1)">+</button>
                </div>
            </div>
            
            <div class="category-row">
                <div>
                    <h4>Infants</h4>
                    <p>Under 2 years</p>
                </div>
                <div class="counter">
                    <button onclick="changeCount('infants', -1)">-</button>
                    <span id="infants-count">0</span>
                    <button onclick="changeCount('infants', 1)">+</button>
                </div>
            </div>
            
            <button class="btn-confirm" onclick="updatePassengerSummary()">Confirm</button>
        </div>
    </div>
</div>

<script>
function showPassengerModal() {
    document.getElementById('passengerModal').style.display = 'block';
}

function hidePassengerModal() {
    document.getElementById('passengerModal').style.display = 'none';
}

function changeCount(type, delta) {
    const countElement = document.getElementById(`${type}-count`);
    let count = parseInt(countElement.textContent);
    count += delta;
    
    // Ensure count doesn't go below 0
    if (count < 0) count = 0;
    
    // Additional validation if needed (e.g., max infants)
    countElement.textContent = count;
}

function updatePassengerSummary() {
    const adults = parseInt(document.getElementById('adults-count').textContent);
    const children = parseInt(document.getElementById('children-count').textContent);
    const kids = parseInt(document.getElementById('kids-count').textContent);
    const infants = parseInt(document.getElementById('infants-count').textContent);
    
    const total = adults + children + kids + infants;
    let summary = '';
    
    if (total === 0) {
        summary = '1 Adult'; // Default to at least 1 adult
        document.getElementById('adults-count').textContent = '1';
    } else {
        summary = `${adults} Adult${adults !== 1 ? 's' : ''}`;
        if (children > 0) summary += `, ${children} Child${children !== 1 ? 'ren' : ''}`;
        if (kids > 0) summary += `, ${kids} Kid${kids !== 1 ? 's' : ''}`;
        if (infants > 0) summary += `, ${infants} Infant${infants !== 1 ? 's' : ''}`;
    }
    
    document.getElementById('passengers').innerHTML = `<option value="${total}" selected>${summary}</option>`;
    hidePassengerModal();
}
</script>

<script>
    
// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality - Fixed version
    document.querySelectorAll('.search-tabs button').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove active class from all buttons and tabs
            document.querySelectorAll('.search-tabs button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));

            // Add active class to clicked button
            button.classList.add('active');

            // Show corresponding tab
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Date pickers initialization
    const departureInput = document.getElementById('departure');
    const returnInput = document.getElementById('return');
    const checkInInput = document.getElementById('check-in');
    const checkOutInput = document.getElementById('check-out');
    const packageDeparture = document.getElementById('package-departure');

    // Set minimum return date based on departure date
    if (departureInput && returnInput) {
        departureInput.addEventListener('change', () => {
            returnInput.min = departureInput.value;
            if (returnInput.value && returnInput.value < departureInput.value) {
                returnInput.value = '';
            }
        });
    }

    // Set minimum check-out date based on check-in date
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', () => {
            checkOutInput.min = checkInInput.value;
            if (checkOutInput.value && checkOutInput.value < checkInInput.value) {
                checkOutInput.value = '';
            }
        });
    }

    // Initialize welcome modal if it should be shown
    <?php if ($showWelcomeModal): ?>
        const welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
        setTimeout(() => {
            welcomeModal.show();
        }, 3000);
    <?php endif; ?>

    // Initialize AOS for scroll animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }

    // Initialize Choices.js for better select elements
    if (typeof Choices !== 'undefined') {
        document.querySelectorAll('select').forEach(select => {
            new Choices(select, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false,
                classNames: {
                    containerInner: 'choices__inner choices__inner-mod'
                }
            });
        });
    }
});
</script>
<script>
// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // [Previous initialization code remains the same]
    
    // Countdown timer for deals section
    function updateCountdown() {
        const now = new Date();
        const endOfDay = new Date();
        endOfDay.setHours(23, 59, 59, 999);
        
        const diff = endOfDay - now;
        
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        document.getElementById('countdown-hours').textContent = Math.floor(hours).toString().padStart(2, '0');
        document.getElementById('countdown-minutes').textContent = Math.floor(minutes).toString().padStart(2, '0');
        document.getElementById('countdown-seconds').textContent = Math.floor(seconds).toString().padStart(2, '0');
    }
    
    setInterval(updateCountdown, 1000);
    updateCountdown();
    
    // Image map highlighting
    const areas = document.querySelectorAll('map area');
    const worldMap = document.querySelector('.world-map img');
    
    if (worldMap && areas.length > 0) {
        areas.forEach(area => {
            area.addEventListener('mouseover', function() {
                const coords = this.getAttribute('coords').split(',');
                const x = parseInt(coords[0]);
                const y = parseInt(coords[1]);
                const radius = parseInt(coords[2]);
                
                const highlight = document.createElement('div');
                highlight.className = 'map-highlight';
                highlight.style.width = `${radius * 2}px`;
                highlight.style.height = `${radius * 2}px`;
                highlight.style.left = `${x - radius}px`;
                highlight.style.top = `${y - radius}px`;
                
                worldMap.parentNode.appendChild(highlight);
            });
            
            area.addEventListener('mouseout', function() {
                const highlight = document.querySelector('.map-highlight');
                if (highlight) {
                    highlight.remove();
                }
            });
        });
    }
    
    // Travel requirements search
    const requirementsForm = document.querySelector('.requirements-search form');
    if (requirementsForm) {
        requirementsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real implementation, this would fetch requirements from an API
            document.querySelector('.requirements-result').style.display = 'block';
        });
    }
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
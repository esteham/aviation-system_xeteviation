<?php
require_once __DIR__ . '/config/dbconfig.php';

// Set page title
$pageTitle = "Available Flights";

// Get database connection
try {
    $db = DBConfig::getInstance()->getConnection();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle search/filter parameters
$departureAirport = $_GET['departure'] ?? '';
$arrivalAirport = $_GET['arrival'] ?? '';
$departureDate = $_GET['date'] ?? '';
$passengers = $_GET['passengers'] ?? 1;

// Build the base query
$query = "
    SELECT f.*, 
           al.name as airline_name, al.logo_url,
           ac.model as aircraft_model,
           dep.name as departure_airport, dep.code as departure_code, dep.city as departure_city,
           arr.name as arrival_airport, arr.code as arrival_code, arr.city as arrival_city
    FROM flights f
    JOIN airlines al ON f.airline_id = al.airline_id
    JOIN aircrafts ac ON f.aircraft_id = ac.aircraft_id
    JOIN airports dep ON f.departure_airport_id = dep.airport_id
    JOIN airports arr ON f.arrival_airport_id = arr.airport_id
    WHERE f.status = 'scheduled' AND f.available_seats >= :passengers
";

$params = [':passengers' => $passengers];

// Add filters if provided
if (!empty($departureAirport)) {
    $query .= " AND dep.code = :departure_code";
    $params[':departure_code'] = $departureAirport;
}

if (!empty($arrivalAirport)) {
    $query .= " AND arr.code = :arrival_code";
    $params[':arrival_code'] = $arrivalAirport;
}

if (!empty($departureDate)) {
    $query .= " AND DATE(f.departure_time) = :departure_date";
    $params[':departure_date'] = $departureDate;
}

$query .= " ORDER BY f.departure_time ASC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $flights = $stmt->fetchAll();

    // Get all airports for dropdown
    $airports = $db->query("SELECT code, city, name FROM airports ORDER BY city")->fetchAll();
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}


?>
    <style>
        .airline-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin-right: 10px;
        }
        .flight-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .flight-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .flight-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .flight-time {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .flight-duration {
            text-align: center;
            padding: 0 10px;
        }
        .flight-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .flight-route {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .search-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .no-flights {
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<?php include_once __DIR__ . '/includes/header.php'; ?>
<div class="container py-5">
    <h1 class="mb-4 text-center">Find Your Flight</h1>
    
    <!-- Search Box -->
    <div class="search-box">
        <form method="GET" action="flights.php">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <select name="departure" class="form-select">
                        <option value="">Any departure</option>
                        <?php foreach ($airports as $airport): ?>
                        <option value="<?= $airport['code'] ?>" <?= $departureAirport === $airport['code'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($airport['city']) ?> (<?= $airport['code'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <select name="arrival" class="form-select">
                        <option value="">Any destination</option>
                        <?php foreach ($airports as $airport): ?>
                        <option value="<?= $airport['code'] ?>" <?= $arrivalAirport === $airport['code'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($airport['city']) ?> (<?= $airport['code'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Departure Date</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($departureDate) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Passengers</label>
                    <select name="passengers" class="form-select">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>" <?= $passengers == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Results Count -->
    <div class="mb-3">
        <h5><?= count($flights) ?> flights found</h5>
    </div>
    
    <?php if (empty($flights)): ?>
    <div class="no-flights">
        <i class="fas fa-plane-slash fa-3x mb-3 text-muted"></i>
        <h3>No flights available</h3>
        <p class="text-muted">Try adjusting your search criteria</p>
    </div>
    <?php endif; ?>
    
    <!-- Flight List -->
    <div class="row">
        <?php foreach ($flights as $flight): 
            $duration = calculateFlightDuration($flight['departure_time'], $flight['arrival_time']);
        ?>
        <div class="col-md-12">
            <div class="flight-card">
                <div class="row align-items-center">
                    <!-- Airline Info -->
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <?php if (!empty($flight['logo_url'])): ?>
                            <img src="<?= htmlspecialchars($flight['logo_url']) ?>" 
                                 alt="<?= htmlspecialchars($flight['airline_name']) ?>" 
                                 class="airline-logo">
                            <?php endif; ?>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($flight['airline_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($flight['flight_number']) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Flight Times -->
                    <div class="col-md-5">
                        <div class="row">
                            <div class="col-5">
                                <div class="flight-time">
                                    <?= formatDateTime($flight['departure_time'], 'H:i') ?>
                                </div>
                                <div class="flight-route">
                                    <?= htmlspecialchars($flight['departure_code']) ?> (<?= htmlspecialchars($flight['departure_city']) ?>)
                                </div>
                                <div class="small text-muted">
                                    <?= formatDateTime($flight['departure_time'], 'D, M j') ?>
                                </div>
                            </div>
                            
                            <div class="col-2 text-center">
                                <div class="flight-duration small">
                                    <i class="fas fa-plane"></i><br>
                                    <?= formatFlightDuration($duration) ?>
                                </div>
                            </div>
                            
                            <div class="col-5">
                                <div class="flight-time">
                                    <?= formatDateTime($flight['arrival_time'], 'H:i') ?>
                                </div>
                                <div class="flight-route">
                                    <?= htmlspecialchars($flight['arrival_code']) ?> (<?= htmlspecialchars($flight['arrival_city']) ?>)
                                </div>
                                <div class="small text-muted">
                                    <?= formatDateTime($flight['arrival_time'], 'D, M j') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aircraft Info -->
                    <div class="col-md-2">
                        <div class="small">
                            <i class="fas fa-plane"></i> <?= htmlspecialchars($flight['aircraft_model']) ?>
                        </div>
                        <div class="small text-muted">
                            <i class="fas fa-chair"></i> <?= $flight['available_seats'] ?> seats available
                        </div>
                    </div>
                    
                    <!-- Price and Book Button -->
                    <div class="col-md-3 text-end">
                        <div class="flight-price mb-2">
                            $<?= number_format($flight['economy_price'], 2) ?>
                        </div>
                        <a href="booking_summary.php?flight_id=<?= $flight['flight_id'] ?>&passengers=<?= $passengers ?>" 
                           class="btn btn-primary">
                            Book Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include_once __DIR__ . '/includes/footer.php'; ?>

<script>
// Set minimum date for date picker to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.querySelector('input[type="date"]');
    
    if (dateInput) {
        dateInput.min = today;
        
        // If no date is selected, default to today
        if (!dateInput.value) {
            dateInput.value = today;
        }
    }
});
</script>

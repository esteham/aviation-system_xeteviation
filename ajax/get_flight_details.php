<?php
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';

header('Content-Type: text/html');

$flightId = isset($_GET['flight_id']) ? intval($_GET['flight_id']) : 0;
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'details';

try {
    $db = DBConfig::getInstance()->getConnection();
    
    // Get basic flight info
    $stmt = $db->prepare("
        SELECT f.*, 
               dep.name as dep_airport_name, dep.code as dep_airport_code, dep.city as dep_airport_city,
               arr.name as arr_airport_name, arr.code as arr_airport_code, arr.city as arr_airport_city,
               al.name as airline_name, al.logo_url
        FROM flights f
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        JOIN airlines al ON f.airline_id = al.airline_id
        WHERE f.flight_id = ?
    ");
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch();
    
    if (!$flight) {
        echo '<div class="alert alert-danger">Flight not found</div>';
        exit();
    }
    
    // Get additional details from admin-editable content
    $stmt = $db->prepare("SELECT * FROM flight_details WHERE flight_id = ?");
    $stmt->execute([$flightId]);
    $details = $stmt->fetch();
    
    switch ($tab) {
        case 'details':
            // Flight Details tab
            echo '<div class="flight-details">';
            echo '<div class="row mb-4">';
            echo '<div class="col-md-6">';
            echo '<h6>Flight Information</h6>';
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm">';
            echo '<tr><th>Flight Number:</th><td>' . htmlspecialchars($flight['flight_number']) . '</td></tr>';
            echo '<tr><th>Airline:</th><td>' . htmlspecialchars($flight['airline_name']) . '</td></tr>';
            echo '<tr><th>Departure:</th><td>' . htmlspecialchars($flight['dep_airport_name']) . ' (' . $flight['dep_airport_code'] . ')</td></tr>';
            echo '<tr><th>Arrival:</th><td>' . htmlspecialchars($flight['arr_airport_name']) . ' (' . $flight['arr_airport_code'] . ')</td></tr>';
            echo '<tr><th>Departure Time:</th><td>' . date('M j, Y H:i', strtotime($flight['departure_time'])) . '</td></tr>';
            echo '<tr><th>Arrival Time:</th><td>' . date('M j, Y H:i', strtotime($flight['arrival_time'])) . '</td></tr>';
            echo '<tr><th>Duration:</th><td>' . gmdate('H\h i\m', strtotime($flight['arrival_time']) - strtotime($flight['departure_time'])) . '</td></tr>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="col-md-6">';
            echo '<h6>Aircraft & Services</h6>';
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm">';
            echo '<tr><th>Aircraft Type:</th><td>' . htmlspecialchars($flight['aircraft_type'] ?? 'Not specified') . '</td></tr>';
            echo '<tr><th>Baggage Allowance:</th><td>' . htmlspecialchars($details['baggage_allowance'] ?? 'Not specified') . '</td></tr>';
            echo '<tr><th>In-flight Meals:</th><td>' . htmlspecialchars($details['meals'] ?? 'Not specified') . '</td></tr>';
            echo '<tr><th>Entertainment:</th><td>' . htmlspecialchars($details['entertainment'] ?? 'Not specified') . '</td></tr>';
            echo '<tr><th>WiFi Available:</th><td>' . (isset($details['wifi']) ? ($details['wifi'] ? 'Yes' : 'No') : 'Not specified') . '</td></tr>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            // Admin-editable content
            echo '<div class="flight-description">';
            echo !empty($details['flight_description']) ? nl2br(htmlspecialchars($details['flight_description'])) : '<p class="text-muted">No additional details available for this flight.</p>';
            echo '</div>';
            echo '</div>';
            break;
            
        case 'fare':
            // Fare Summary tab
            echo '<div class="fare-summary">';
            echo '<h6>Fare Breakdown</h6>';
            echo '<div class="table-responsive">';
            echo '<table class="table">';
            echo '<thead><tr><th>Class</th><th>Base Fare</th><th>Taxes & Fees</th><th>Total</th></tr></thead>';
            echo '<tbody>';
            echo '<tr><td>Economy</td><td>$' . number_format($flight['economy_price'], 2) . '</td><td>$' . number_format($flight['economy_price'] * 0.15, 2) . '</td><td>$' . number_format($flight['economy_price'] * 1.15, 2) . '</td></tr>';
            echo '<tr><td>Business</td><td>$' . number_format($flight['business_price'], 2) . '</td><td>$' . number_format($flight['business_price'] * 0.15, 2) . '</td><td>$' . number_format($flight['business_price'] * 1.15, 2) . '</td></tr>';
            echo '<tr><td>First Class</td><td>$' . number_format($flight['first_class_price'], 2) . '</td><td>$' . number_format($flight['first_class_price'] * 0.15, 2) . '</td><td>$' . number_format($flight['first_class_price'] * 1.15, 2) . '</td></tr>';
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            
            // Admin-editable content
            if (!empty($details['fare_details'])) {
                echo '<div class="fare-details mt-4">';
                echo '<h6>Fare Details</h6>';
                echo nl2br(htmlspecialchars($details['fare_details']));
                echo '</div>';
            }
            echo '</div>';
            break;
            
        case 'rules':
            // Fare Rules tab
            echo '<div class="fare-rules">';
            if (!empty($details['fare_rules'])) {
                echo nl2br(htmlspecialchars($details['fare_rules']));
            } else {
                echo '<div class="alert alert-info">No specific fare rules available for this flight.</div>';
                echo '<h6>Standard Fare Rules</h6>';
                echo '<ul>';
                echo '<li>Tickets are non-transferable</li>';
                echo '<li>Changes to bookings may incur fees</li>';
                echo '<li>Cancellations must be made at least 24 hours before departure for a refund</li>';
                echo '<li>No-shows will result in forfeiture of the ticket</li>';
                echo '<li>Baggage fees may apply for excess luggage</li>';
                echo '</ul>';
            }
            echo '</div>';
            break;
    }
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading flight details: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
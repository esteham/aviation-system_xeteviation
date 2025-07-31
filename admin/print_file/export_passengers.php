<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check permissions (commented out in your original code)
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('login.php');
// }

$flightId = $_GET['flight_id'] ?? null;

if (!$flightId) {
    die('No flight ID provided');
}

try {
    // Get flight details (same query as in your main file)
    $stmt = $db->prepare("
        SELECT f.*, 
               a1.name AS departure_airport, 
               a2.name AS arrival_airport,
               al.name AS airline_name
        FROM flights f
        JOIN airlines al ON f.airline_id = al.airline_id
        JOIN airports a1 ON f.departure_airport_id = a1.airport_id
        JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
        WHERE f.flight_id = ?
    ");
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$flight) {
        die('Flight not found');
    }

    // Get passengers (same query as in your main file)
    $stmt = $db->prepare("
        SELECT p.*, 
               b.booking_number,
               CONCAT(u.first_name, ' ', u.last_name) AS booked_by,
               u.userEmail AS booked_by_email
        FROM passengers p
        JOIN bookings b ON p.booking_id = b.booking_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.flight_id = ? OR b.return_flight_id = ?
        ORDER BY p.is_primary DESC, p.last_name, p.first_name
    ");
    $stmt->execute([$flightId, $flightId]);
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="passengers_flight_' . $flight['flight_number'] . '_' . date('Y-m-d') . '.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write CSV header row
    fputcsv($output, [
        'No.', 
        'Passenger Name', 
        'Type', 
        'Booking Ref', 
        'Booked By', 
        'Booked By Email',
        'Seat', 
        'Class', 
        'Passport', 
        'DOB',
        'Email',
        'Phone',
        'Special Requests'
    ]);

    // Write passenger data
    foreach ($passengers as $index => $passenger) {
        fputcsv($output, [
            $index + 1,
            $passenger['first_name'] . ' ' . $passenger['last_name'],
            $passenger['type'],
            $passenger['booking_number'],
            $passenger['booked_by'],
            $passenger['booked_by_email'],
            $passenger['seat_number'] ?: 'Not assigned',
            $passenger['seat_class'],
            $passenger['passport_number'] ?: 'N/A',
            $passenger['date_of_birth'] ? formatDate($passenger['date_of_birth'], 'Y-m-d') : 'N/A',
            $passenger['email'] ?: 'N/A',
            $passenger['phone'] ?: 'N/A',
            $passenger['special_requests'] ?: 'None'
        ]);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
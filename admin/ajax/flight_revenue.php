<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/dbconfig.php';

// Prevent any output before JSON

try {
    $response = ['data' => []];
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['flight_id'])) {
        // Detailed data for specific flight
        $stmt = $db->prepare("
            SELECT 
                f.flight_id, f.flight_number, al.name AS airline_name,
                f.departure_time, f.arrival_time, b.booking_id, b.booking_number,
                b.class, p.amount AS payment_amount, p.payment_method, p.payment_date,
                b.passengers, (p.amount / b.passengers) AS revenue_per_passenger
            FROM flights f
            JOIN airlines al ON f.airline_id = al.airline_id
            JOIN bookings b ON (f.flight_id = b.flight_id OR f.flight_id = b.return_flight_id)
            JOIN payments p ON b.booking_id = p.booking_id
            WHERE p.status = 'completed' AND f.flight_id = ?
            ORDER BY p.payment_date DESC
        ");
        $stmt->execute([$_GET['flight_id']]);
        $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Summary data for all flights
        $stmt = $db->query("
            SELECT 
                f.flight_id, f.flight_number, al.name AS airline_name,
                f.departure_time, f.arrival_time,
                COUNT(DISTINCT b.booking_id) AS total_bookings,
                COALESCE(SUM(p.amount), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN b.class = 'economy' THEN p.amount ELSE 0 END), 0) AS economy_revenue,
                COALESCE(SUM(CASE WHEN b.class = 'business' THEN p.amount ELSE 0 END), 0) AS business_revenue,
                COALESCE(SUM(CASE WHEN b.class = 'first' THEN p.amount ELSE 0 END), 0) AS first_class_revenue
            FROM flights f
            JOIN airlines al ON f.airline_id = al.airline_id
            LEFT JOIN bookings b ON (f.flight_id = b.flight_id OR f.flight_id = b.return_flight_id)
            LEFT JOIN payments p ON b.booking_id = p.booking_id AND p.status = 'completed'
            GROUP BY f.flight_id, f.flight_number, al.name, f.departure_time, f.arrival_time
            ORDER BY total_revenue DESC
        ");
        $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Clear any accidental output
    ob_end_clean();
    echo json_encode($response);
    exit;
    
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
}
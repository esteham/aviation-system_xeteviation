<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

try {
    if (!isset($_GET['flight_id']) || empty($_GET['flight_id'])) {
        throw new Exception("Flight ID parameter is required and cannot be empty");
    }

    $flightId = filter_var($_GET['flight_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Get flight basic info
    $stmt = $db->prepare("
        SELECT 
            f.*, 
            al.name AS airline_name,
            al.logo_url AS airline_logo,
            dep.name AS departure_airport,
            dep.city AS departure_city,
            dep.country AS departure_country,
            arr.name AS arrival_airport,
            arr.city AS arrival_city,
            arr.country AS arrival_country
        FROM flights f
        JOIN airlines al ON f.airline_id = al.airline_id
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        WHERE f.flight_id = ?
    ");
    $stmt->execute([$flightId]);
    $flightInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$flightInfo) {
        throw new Exception("Flight not found");
    }

    // Get revenue data
    $stmt = $db->prepare("
        SELECT 
            b.booking_id,
            b.booking_number,
            b.class,
            p.amount AS payment_amount,
            p.payment_method,
            p.payment_date,
            b.passengers,
            (p.amount / b.passengers) AS revenue_per_passenger,
            CASE 
                WHEN f.flight_id = b.flight_id THEN 'outbound'
                WHEN f.flight_id = b.return_flight_id THEN 'return'
                ELSE 'unknown'
            END AS flight_direction,
            u.user_id,
            u.first_name,
            u.last_name
        FROM flights f
        JOIN bookings b ON (f.flight_id = b.flight_id OR f.flight_id = b.return_flight_id)
        JOIN payments p ON b.booking_id = p.booking_id
        JOIN users u ON b.user_id = u.user_id
        WHERE p.status = 'completed' AND f.flight_id = ?
        ORDER BY p.payment_date DESC
    ");
    $stmt->execute([$flightId]);
    $revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate summary stats
    $summary = [
        'total_revenue' => 0,
        'total_bookings' => count($revenueData),
        'total_passengers' => 0,
        'average_revenue_per_booking' => 0,
        'average_revenue_per_passenger' => 0,
        'by_class' => [
            'economy' => ['revenue' => 0, 'bookings' => 0, 'passengers' => 0],
            'business' => ['revenue' => 0, 'bookings' => 0, 'passengers' => 0],
            'first' => ['revenue' => 0, 'bookings' => 0, 'passengers' => 0],
            'premium' => ['revenue' => 0, 'bookings' => 0, 'passengers' => 0]
        ],
        'by_direction' => [
            'outbound' => ['revenue' => 0, 'bookings' => 0, 'passengers' => 0],
            'return' => ['revenue' => 0, 'bookings' => 0, 'passengers' => 0]
        ],
        'by_payment_method' => []
    ];

    foreach ($revenueData as $row) {
        $amount = (float)$row['payment_amount'];
        $passengers = (int)$row['passengers'];
        $class = $row['class'];
        $direction = $row['flight_direction'];
        $method = $row['payment_method'];

        $summary['total_revenue'] += $amount;
        $summary['total_passengers'] += $passengers;
        
        // Class breakdown
        if (isset($summary['by_class'][$class])) {
            $summary['by_class'][$class]['revenue'] += $amount;
            $summary['by_class'][$class]['bookings']++;
            $summary['by_class'][$class]['passengers'] += $passengers;
        }
        
        // Direction breakdown
        if (isset($summary['by_direction'][$direction])) {
            $summary['by_direction'][$direction]['revenue'] += $amount;
            $summary['by_direction'][$direction]['bookings']++;
            $summary['by_direction'][$direction]['passengers'] += $passengers;
        }
        
        // Payment method breakdown
        if (!isset($summary['by_payment_method'][$method])) {
            $summary['by_payment_method'][$method] = [
                'revenue' => 0,
                'bookings' => 0,
                'passengers' => 0
            ];
        }
        $summary['by_payment_method'][$method]['revenue'] += $amount;
        $summary['by_payment_method'][$method]['bookings']++;
        $summary['by_payment_method'][$method]['passengers'] += $passengers;
    }

    // Calculate averages
    if ($summary['total_bookings'] > 0) {
        $summary['average_revenue_per_booking'] = $summary['total_revenue'] / $summary['total_bookings'];
        $summary['average_revenue_per_passenger'] = $summary['total_revenue'] / $summary['total_passengers'];
    }

    // Format currency values
    $summary['total_revenue'] = number_format($summary['total_revenue'], 2);
    $summary['average_revenue_per_booking'] = number_format($summary['average_revenue_per_booking'], 2);
    $summary['average_revenue_per_passenger'] = number_format($summary['average_revenue_per_passenger'], 2);

    foreach ($summary['by_class'] as &$class) {
        $class['revenue'] = number_format($class['revenue'], 2);
    }
    foreach ($summary['by_direction'] as &$direction) {
        $direction['revenue'] = number_format($direction['revenue'], 2);
    }
    foreach ($summary['by_payment_method'] as &$method) {
        $method['revenue'] = number_format($method['revenue'], 2);
    }

    echo json_encode([
        'status' => 'success',
        'flight_info' => $flightInfo,
        'revenue_data' => $revenueData,
        'summary' => $summary
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

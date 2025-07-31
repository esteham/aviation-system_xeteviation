<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/dbconfig.php';

try {
    $stmt = $db->prepare("
        SELECT 
            f.flight_id,
            f.flight_number,
            dep.code AS departure_airport,
            arr.code AS arrival_airport,
            f.departure_time
        FROM flights f
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        WHERE f.departure_time > NOW() - INTERVAL 6 MONTH
        ORDER BY f.departure_time DESC
        LIMIT 100
    ");
    $stmt->execute();
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($flights);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
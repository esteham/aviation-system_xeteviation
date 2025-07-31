<?php
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';

session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'You must be logged in to complete a booking.']);
    exit();
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method.']);
    exit();
}

// Get form data
$flightId = intval($_POST['flight_id']);
$returnFlightId = isset($_POST['return_flight_id']) && !empty($_POST['return_flight_id']) ? intval($_POST['return_flight_id']) : null;
$class = $_POST['class'];
$passengers = intval($_POST['passengers']);
$userId = $_SESSION['user_id'];
$isInternational = isset($_POST['is_international']) && $_POST['is_international'] == '1';

// Validate required fields
$errors = [];
$requiredFields = [
    'flight_id', 'class', 'passengers',
    'first_name', 'last_name', 'email',
    'phone', 'payment_method'
];

// Only require passport if international flight
if ($isInternational) {
    $requiredFields[] = 'passport_number';
}

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
    }
}

if (!empty($errors)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => implode("; ", $errors)]);
    exit();
}

try {
    $db = DBConfig::getInstance()->getConnection();
    $db->beginTransaction();

    // Validate outbound flight and get international status if not provided
    $flightQuery = "SELECT f.*, dep.country as dep_country, arr.country as arr_country 
                   FROM flights f
                   JOIN airports dep ON f.departure_airport_id = dep.airport_id
                   JOIN airports arr ON f.arrival_airport_id = arr.airport_id
                   WHERE f.flight_id = ?";
    $stmt = $db->prepare($flightQuery);
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch();

    if (!$flight) {
        throw new Exception("Outbound flight not found");
    }

    // Determine if flight is international if not provided in form
    if (!isset($isInternational)) {
        $isInternational = ($flight['dep_country'] !== $flight['arr_country']);
    }

    // Validate return flight
    if ($returnFlightId) {
        $stmt = $db->prepare($flightQuery);
        $stmt->execute([$returnFlightId]);
        $returnFlight = $stmt->fetch();
        if (!$returnFlight) {
            $returnFlightId = null;
        }
    }

    // Seat availability check
    if ($flight['available_seats'] < $passengers) {
        throw new Exception("Not enough seats available for outbound flight");
    }
    if ($returnFlightId && $returnFlight['available_seats'] < $passengers) {
        throw new Exception("Not enough seats available for return flight");
    }

    // Calculate total price
    switch ($class) {
        case 'economy': $basePrice = $flight['economy_price']; break;
        case 'business': $basePrice = $flight['business_price']; break;
        case 'first': $basePrice = $flight['first_class_price']; break;
        case 'premium': $basePrice = $flight['economy_price'] * 1.2; break;
        default: $basePrice = $flight['economy_price'];
    }

    $totalPrice = $basePrice * $passengers;

    if ($returnFlightId) {
        switch ($class) {
            case 'economy': $returnPrice = $returnFlight['economy_price']; break;
            case 'business': $returnPrice = $returnFlight['business_price']; break;
            case 'first': $returnPrice = $returnFlight['first_class_price']; break;
            case 'premium': $returnPrice = $returnFlight['economy_price'] * 1.2; break;
            default: $returnPrice = $returnFlight['economy_price'];
        }
        $totalPrice += $returnPrice * $passengers;
    }

    // Insert booking
    $bookingNumber = 'BK' . strtoupper(uniqid());
    $stmt = $db->prepare("
        INSERT INTO bookings (
            booking_number, user_id, flight_id, return_flight_id,
            passengers, class, total_price, payment_method, payment_status,
            is_international
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $bookingNumber,
        $userId,
        $flightId,
        $returnFlightId,
        $passengers,
        $class,
        $totalPrice,
        $_POST['payment_method'],
        'pending',
        $isInternational ? 1 : 0
    ]);

    $bookingId = $db->lastInsertId();

    // Insert primary passenger
    $stmt = $db->prepare("
        INSERT INTO passengers (
            booking_id, first_name, last_name, email,
            phone, passport_number
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $bookingId,
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['phone'],
        $isInternational ? $_POST['passport_number'] : null
    ]);

    // Additional passengers
    if ($passengers > 1) {
        for ($i = 1; $i < $passengers; $i++) {
            if (isset($_POST['passengers'][$i])) {
                $passenger = $_POST['passengers'][$i];
                $stmt->execute([
                    $bookingId,
                    $passenger['first_name'],
                    $passenger['last_name'],
                    $passenger['email'] ?? $_POST['email'],
                    $passenger['phone'] ?? $_POST['phone'],
                    $isInternational ? ($passenger['passport_number'] ?? null) : null
                ]);
            }
        }
    }

    // Update seats
    $stmt = $db->prepare("UPDATE flights SET available_seats = available_seats - ? WHERE flight_id = ?");
    $stmt->execute([$passengers, $flightId]);

    if ($returnFlightId) {
        $stmt->execute([$passengers, $returnFlightId]);
    }
     $paymentMethod = $bookingData['payment_method'] ?? 'credit_card';
    
    $stmt = $db->prepare("
        INSERT INTO payments 
        (booking_id, amount, payment_method, payment_date, status)
        VALUES (?, ?, ?, NOW(), 'completed')
    ");
    $stmt->execute([$bookingId, $totalPrice, $paymentMethod]);

    $db->commit();
    // After $db->commit() in the try block, add:
    $email = $_POST['email'];
    $subject = "Booking Confirmation #$bookingNumber";
    $message = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Booking Confirmation</title>
            <style>
                body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { color: #2563eb; font-size: 24px; font-weight: 600; margin-bottom: 20px; }
                .content { background-color: #f9fafb; padding: 20px; border-radius: 8px; }
                .booking-id { font-size: 18px; font-weight: bold; color: #059669; }
                .footer { margin-top: 20px; font-size: 14px; color: #6b7280; }
            </style>
        </head>
        <body>
            <div class="header">Thank you for your booking!</div>
            <div class="content">
                <p>Your booking has been confirmed. Here are your details:</p>
                <p>Booking reference: <span class="booking-id">#$bookingNumber</span></p>
            </div>
            <div class="footer">
                <p>If you have any questions, please contact our support team.</p>
            </div>
        </body>
        </html>
        HTML;
    sendMail($email, $message, $subject);

    $_SESSION['booking_success'] = true;
    $_SESSION['booking_number'] = $bookingNumber;

    header('Content-Type: application/json');
    echo json_encode(['redirect' => "booking_confirmation.php?id=$bookingId"]);
    exit();

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['error' => "Booking failed: " . $e->getMessage()]);
    exit();
}
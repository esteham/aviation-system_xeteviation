<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check if booking ID is provided
// if (!isset($_GET['id'])) {
//     header("Location: my_booking.php");
//     exit();
// }

$booking_id = $_GET['id'];

// Fetch booking details
$stmt = $db->prepare("SELECT b.*, f.flight_number, f.departure_time, f.arrival_time, 
                     f.duration, f.economy_price, f.business_price, f.first_class_price,
                     a1.name as departure_airport, a1.city as departure_city, a1.code as departure_code,
                     a2.name as arrival_airport, a2.city as arrival_city, a2.code as arrival_code,
                     al.name as airline_name, al.logo_url as airline_logo,
                     ac.model as aircraft_model, ac.registration_number as aircraft_reg,
                     u.userName, u.userEmail, u.phone, u.first_name as user_first, u.last_name as user_last
                     FROM bookings b
                     JOIN flights f ON b.flight_id = f.flight_id
                     JOIN airports a1 ON f.departure_airport_id = a1.airport_id
                     JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
                     JOIN airlines al ON f.airline_id = al.airline_id
                     JOIN aircrafts ac ON f.aircraft_id = ac.aircraft_id
                     JOIN users u ON b.user_id = u.user_id
                     WHERE b.booking_id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if booking exists and user has permission
if (!$booking || (!isset($_SESSION['user_type']) || 
    ($_SESSION['user_type'] != 'admin' && $booking['user_id'] != $_SESSION['user_id']))) {
    $_SESSION['error'] = "Booking not found or access denied.";
    echo "<script>window.location.href='index.php?page=bookings';</script>";
    exit();
}

// Fetch passengers for this booking
$stmt = $db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch payment information if exists
$stmt = $db->prepare("SELECT * FROM payments WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details | <?= $booking['flight_number'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content -->
        <div class="lg:w-2/3">
            <!-- Booking Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Booking Details</h1>
                        <p class="text-gray-600">Reference: <?= strtoupper(substr(md5($booking['booking_id']), 0, 8)) ?></p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-medium 
                        <?= $booking['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                           ($booking['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                        <?= ucfirst($booking['payment_status']) ?>
                    </span>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Booking Date</p>
                            <p class="font-medium"><?= date('F j, Y \a\t H:i', strtotime($booking['booking_date'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Flight Number</p>
                            <p class="font-medium"><?= $booking['airline_name'] ?> <?= $booking['flight_number'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Flight Details -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center mb-6">
                    <?php if ($booking['airline_logo']): ?>
                        <img src="<?= $booking['airline_logo'] ?>" alt="<?= $booking['airline_name'] ?>" class="w-12 h-12 mr-4">
                    <?php endif; ?>
                    <h2 class="text-xl font-semibold text-gray-800">Flight Information</h2>
                </div>
                
                <div class="relative">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <div class="text-center mb-4 md:mb-0 md:text-left">
                            <p class="text-gray-500 text-sm">Departure</p>
                            <p class="text-2xl font-bold"><?= date('H:i', strtotime($booking['departure_time'])) ?></p>
                            <p class="text-gray-600"><?= date('D, M j', strtotime($booking['departure_time'])) ?></p>
                            <p class="font-medium mt-1"><?= $booking['departure_airport'] ?></p>
                            <p class="text-gray-600"><?= $booking['departure_city'] ?> (<?= $booking['departure_code'] ?>)</p>
                        </div>
                        
                        <div class="hidden md:block flex-1 px-4">
                            <div class="relative h-1 bg-gray-200 rounded-full">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="h-1 bg-blue-500 rounded-full" style="width: 100%"></div>
                                </div>
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-3 h-3 bg-blue-500 rounded-full"></div>
                                <div class="absolute right-0 top-1/2 transform -translate-y-1/2 w-3 h-3 bg-blue-500 rounded-full"></div>
                                <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 text-xs bg-white px-1 text-blue-500">
                                    <?= floor($booking['duration']/60) ?>h <?= $booking['duration']%60 ?>m
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center md:text-right">
                            <p class="text-gray-500 text-sm">Arrival</p>
                            <p class="text-2xl font-bold"><?= date('H:i', strtotime($booking['arrival_time'])) ?></p>
                            <p class="text-gray-600"><?= date('D, M j', strtotime($booking['arrival_time'])) ?></p>
                            <p class="font-medium mt-1"><?= $booking['arrival_airport'] ?></p>
                            <p class="text-gray-600"><?= $booking['arrival_city'] ?> (<?= $booking['arrival_code'] ?>)</p>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Aircraft</p>
                                <p class="font-medium"><?= $booking['aircraft_model'] ?> (<?= $booking['aircraft_reg'] ?>)</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Travel Class</p>
                                <p class="font-medium"><?= ucfirst($passengers[0]['seat_class'] ?? 'Not assigned') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Passengers -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Passengers</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passport</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DOB</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seat</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($passengers as $passenger): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-500"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= $passenger['first_name'] ?> <?= $passenger['last_name'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $passenger['passport_number'] ?: 'N/A' ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $passenger['date_of_birth'] ? date('M j, Y', strtotime($passenger['date_of_birth'])) : 'N/A' ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $passenger['seat_class'] == 'business' ? 'bg-purple-100 text-purple-800' : 
                                           ($passenger['seat_class'] == 'first' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800') ?>">
                                        <?= ucfirst($passenger['seat_class']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($passenger['seat_number']): ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs"><?= $passenger['seat_number'] ?></span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:w-1/3">
            <!-- Price Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Price Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base Fare</span>
                        <span class="font-medium">$<?= number_format($booking['total_price'], 2) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxes & Fees</span>
                        <span class="font-medium">$0.00</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3 mt-3 flex justify-between">
                        <span class="font-semibold">Total</span>
                        <span class="font-bold text-lg">$<?= number_format($booking['total_price'], 2) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <?php if ($payment): ?>
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Payment Information</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount</span>
                            <span class="font-medium">$<?= number_format($payment['amount'], 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Method</span>
                            <span class="font-medium"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date</span>
                            <span class="font-medium"><?= date('M j, Y H:i', strtotime($payment['payment_date'])) ?></span>
                        </div>
                        <div class="flex justify-between items-center pt-3 mt-3 border-t border-gray-200">
                            <span class="text-gray-600">Status</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium 
                                <?= $payment['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($payment['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($payment['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php elseif ($booking['payment_status'] == 'pending'): ?>
                <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-yellow-800">Payment Required</h3>
                            <p class="mt-1 text-sm text-yellow-700">Your booking is not yet confirmed. Please complete the payment to secure your seats.</p>
                            <div class="mt-4">
                                <a href="payment.php?booking_id=<?= $booking_id ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Proceed to Payment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Customer Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Customer Information</h2>
                
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user text-blue-500"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-900"><?= $booking['user_first'] ?> <?= $booking['user_last'] ?></h3>
                        <p class="text-gray-600"><?= $booking['userEmail'] ?></p>
                        
                        <div class="mt-3 space-y-1">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-phone-alt mr-2"></i>
                                <span><?= $booking['phone'] ?: 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back Button -->
    <div class="mt-8 text-center">
        <?php if ($_SESSION['user_type'] == 'admin'): ?>
            <a href="admin/bookings.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i> Back to All Bookings
            </a>
        <?php else: ?>
            <a href="my_booking.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i> Back to My Bookings
            </a>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
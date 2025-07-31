<?php
// Check admin authentication
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Admin Dashboard";

// Include header

try {
    // Get database connection
    $db = DBConfig::getInstance()->getConnection();
    
    // Dashboard statistics
    $stats = [
        'total_bookings' => 0,
        'total_flights' => 0,
        'total_users' => 0,
        'revenue' => 0,
        'recent_bookings' => [],
        'upcoming_flights' => [],
        'booking_trends' => [],
        'revenue_by_month' => [],
        'popular_flights' => [],
        'user_activity' => []
    ];

    // Get total bookings count
    $stmt = $db->query("SELECT COUNT(*) as count FROM bookings");
    $stats['total_bookings'] = $stmt->fetch()['count'];

    // Get total flights count
    $stmt = $db->query("SELECT COUNT(*) as count FROM flights");
    $stats['total_flights'] = $stmt->fetch()['count'];

    // Get total users count
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch()['count'];

    // Get total revenue (paid bookings only)
    $stmt = $db->query("SELECT SUM(total_price) as total FROM bookings WHERE payment_status = 'paid'");
    $stats['revenue'] = $stmt->fetch()['total'] ?? 0;

    // Get recent bookings (last 5)
    $stmt = $db->query("
        SELECT b.*, u.userName, u.userEmail, f.flight_number, 
               f.departure_time, f.arrival_time,
               dep.name as departure_airport, arr.name as arrival_airport
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN flights f ON b.flight_id = f.flight_id
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        ORDER BY b.booking_date DESC
        LIMIT 3
    ");
    $stats['recent_bookings'] = $stmt->fetchAll();

    // Get upcoming flights (next 5 departures)
    $stmt = $db->query("
        SELECT f.*, a.name as airline_name, a.logo_url as airline_logo,
               dep.name as departure_airport, arr.name as arrival_airport,
               dep.city as departure_city, arr.city as arrival_city
        FROM flights f
        JOIN airlines a ON f.airline_id = a.airline_id
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        WHERE f.departure_time > NOW()
        ORDER BY f.departure_time ASC
        LIMIT 3
    ");
    $stats['upcoming_flights'] = $stmt->fetchAll();

    // Get booking trends (last 7 days)
    $stmt = $db->query("
        SELECT DATE(booking_date) as date, COUNT(*) as count
        FROM bookings
        WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(booking_date)
        ORDER BY date ASC
    ");
    $stats['booking_trends'] = $stmt->fetchAll();

    // Get revenue by month (last 6 months)
    $stmt = $db->query("
        SELECT DATE_FORMAT(booking_date, '%Y-%m') as month, 
               SUM(total_price) as revenue
        FROM bookings
        WHERE payment_status = 'paid'
        AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(booking_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stats['revenue_by_month'] = $stmt->fetchAll();

    // Get popular flights (top 5 by bookings)
    $stmt = $db->query("
        SELECT f.flight_number, 
               a.name as airline_name,
               dep.name as departure_airport,
               arr.name as arrival_airport,
               COUNT(b.booking_id) as booking_count
        FROM flights f
        JOIN airlines a ON f.airline_id = a.airline_id
        JOIN airports dep ON f.departure_airport_id = dep.airport_id
        JOIN airports arr ON f.arrival_airport_id = arr.airport_id
        LEFT JOIN bookings b ON f.flight_id = b.flight_id
        GROUP BY f.flight_id
        ORDER BY booking_count DESC
        LIMIT 3
    ");
    $stats['popular_flights'] = $stmt->fetchAll();

    // Get recent user activity
    $stmt = $db->query("
        SELECT u.userName, u.userEmail, MAX(b.booking_date) as last_activity,
               COUNT(b.booking_id) as total_bookings
        FROM users u
        LEFT JOIN bookings b ON u.user_id = b.user_id
        GROUP BY u.user_id
        ORDER BY last_activity DESC
        LIMIT 3
    ");
    $stats['user_activity'] = $stmt->fetchAll();

} catch (PDOException $e) {
    handleException($e);
} catch (Exception $e) {
    handleException($e);
}

// Prepare data for charts
$bookingTrendsLabels = [];
$bookingTrendsData = [];
foreach ($stats['booking_trends'] as $trend) {
    $bookingTrendsLabels[] = formatDate($trend['date'], 'M j');
    $bookingTrendsData[] = $trend['count'];
}

$revenueLabels = [];
$revenueData = [];
foreach ($stats['revenue_by_month'] as $revenue) {
    $revenueLabels[] = formatDate($revenue['month'] . '-01', 'M Y');
    $revenueData[] = $revenue['revenue'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard Overview</h1>
            
            <div class="welcome-card">
                <div class="welcome-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="welcome-info">
                    <h3>Welcome back, <?= htmlspecialchars($_SESSION['userName'] ?? 'Admin') ?></h3>
                    <p>Last login: <?= formatDateTime($_SESSION['last_login'] ?? 'now') ?></p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-stats">
            <!-- Statistics Cards -->
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Bookings</h3>
                    <p><?= number_format($stats['total_bookings']) ?></p>
                    <?php if (!empty($stats['booking_trends'])): ?>
                    <div class="stat-trend <?= end($stats['booking_trends'])['count'] > reset($stats['booking_trends'])['count'] ? 'trend-up' : 'trend-down' ?>">
                        <i class="fas fa-arrow-<?= end($stats['booking_trends'])['count'] > reset($stats['booking_trends'])['count'] ? 'up' : 'down' ?>"></i>
                        <?= abs(end($stats['booking_trends'])['count'] - reset($stats['booking_trends'])['count']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Flights</h3>
                    <p><?= number_format($stats['total_flights']) ?></p>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <p><?= number_format($stats['total_users']) ?></p>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Revenue</h3>
                    <p>$<?= number_format($stats['revenue'], 2) ?></p>
                    <?php if (count($stats['revenue_by_month']) >= 2): ?>
                    <?php
                        $revenueMonths = array_values($stats['revenue_by_month']);
                        $last = $revenueMonths[count($revenueMonths) - 1]['revenue'];
                        $prev = $revenueMonths[count($revenueMonths) - 2]['revenue'];
                        $trendClass = ($last > $prev) ? 'trend-up' : 'trend-down';
                        $trendArrow = ($last > $prev) ? 'up' : 'down';
                        $trendPercent = ($prev != 0) ? round(abs(($last - $prev) / $prev * 100), 2) : 0;
                    ?>
                    <div class="stat-trend <?= $trendClass ?>">
                        <i class="fas fa-arrow-<?= $trendArrow ?>"></i>
                        <?= $trendPercent ?>%
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="dashboard-charts">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Booking Trends (Last 7 Days)</h3>
                    <div class="chart-actions">
                        <button class="btn-chart-action active" data-period="week">Week</button>
                        <button class="btn-chart-action" data-period="month">Month</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="bookingsChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Revenue by Month (Last 6 Months)</h3>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="dashboard-sections">
            <!-- Recent Bookings Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-receipt"></i> Recent Bookings</h2>
                    <a href="index.php?page=bookings" class="btn-view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Flight</th>
                                <th>Route</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_bookings'] as $booking): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($booking['userName'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($booking['userName']) ?></div>
                                            <div class="user-userEmail"><?= htmlspecialchars($booking['userEmail']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($booking['flight_number']) ?></td>
                                <td>
                                    <div class="route-info">
                                        <div><?= htmlspecialchars($booking['departure_airport']) ?></div>
                                        <div class="route-arrow"><i class="fas fa-arrow-right"></i></div>
                                        <div><?= htmlspecialchars($booking['arrival_airport']) ?></div>
                                    </div>
                                    <div class="flight-times">
                                        <?= formatDateTime($booking['departure_time'], 'M j, g:i a') ?> - 
                                        <?= formatDateTime($booking['arrival_time'], 'M j, g:i a') ?>
                                    </div>
                                </td>
                                <td><?= formatDate($booking['booking_date'], 'M j, Y') ?></td>
                                <td class="text-success">$<?= number_format($booking['total_price'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($booking['payment_status']) ?>">
                                        <?= ucfirst($booking['payment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Upcoming Flights Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-plane-departure"></i> Upcoming Flights</h2>
                    <a href="index.php?page=flights" class="btn-view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Flight</th>
                                <th>Airline</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Arrival</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['upcoming_flights'] as $flight): ?>
                            <tr>
                                <td>
                                    <div class="flight-number"><?= htmlspecialchars($flight['flight_number']) ?></div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <?php if (!empty($flight['airline_logo'])): ?>
                                        <div class="user-avatar">
                                            <img src="<?= htmlspecialchars($flight['airline_logo']) ?>" alt="<?= htmlspecialchars($flight['airline_name']) ?>" style="width:100%;height:100%;object-fit:contain;">
                                        </div>
                                        <?php endif; ?>
                                        <div class="user-name"><?= htmlspecialchars($flight['airline_name']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="route-info">
                                        <div class="route-city"><?= htmlspecialchars($flight['departure_city']) ?></div>
                                        <div class="route-arrow"><i class="fas fa-arrow-right"></i></div>
                                        <div class="route-city"><?= htmlspecialchars($flight['arrival_city']) ?></div>
                                    </div>
                                    <div class="airport-names">
                                        <?= htmlspecialchars($flight['departure_airport']) ?> → 
                                        <?= htmlspecialchars($flight['arrival_airport']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="flight-time"><?= formatDateTime($flight['departure_time'], 'M j') ?></div>
                                    <div class="flight-time"><?= formatDateTime($flight['departure_time'], 'g:i a') ?></div>
                                </td>
                                <td>
                                    <div class="flight-time"><?= formatDateTime($flight['arrival_time'], 'M j') ?></div>
                                    <div class="flight-time"><?= formatDateTime($flight['arrival_time'], 'g:i a') ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($flight['status']) ?>">
                                        <?= ucfirst($flight['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Popular Flights Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-star"></i> Popular Flights</h2>
                    <a href="index.php?page=flights" class="btn-view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="popular-flights-grid">
                    <?php foreach ($stats['popular_flights'] as $flight): ?>
                    <div class="popular-flight-card">
                        <div class="flight-header">
                            <div class="flight-number"><?= htmlspecialchars($flight['flight_number']) ?></div>
                            <div class="booking-count">
                                <i class="fas fa-ticket-alt"></i> <?= $flight['booking_count'] ?> bookings
                            </div>
                        </div>
                        <div class="flight-route">
                            <div class="route-departure">
                                <div class="city"><?= explode(',', $flight['departure_airport'])[0] ?></div>
                                <div class="airport"><?= htmlspecialchars($flight['departure_airport']) ?></div>
                            </div>
                            <div class="route-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="route-arrival">
                                <div class="city"><?= explode(',', $flight['arrival_airport'])[0] ?></div>
                                <div class="airport"><?= htmlspecialchars($flight['arrival_airport']) ?></div>
                            </div>
                        </div>
                        <div class="flight-airline">
                            <i class="fas fa-plane"></i> <?= htmlspecialchars($flight['airline_name']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Recent User Activity Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-user-clock"></i> Recent User Activity</h2>
                    <a href="index.php?page=users" class="btn-view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="user-activity-list">
                    <?php foreach ($stats['user_activity'] as $user): ?>
                    <div class="user-activity-card">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user['userName'], 0, 1)) ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?= htmlspecialchars($user['userName']) ?></div>
                            <div class="user-userEmail"><?= htmlspecialchars($user['userEmail']) ?></div>
                        </div>
                        <div class="activity-details">
                            <div class="last-activity">
                                Last active: <?= $user['last_activity'] ? formatDateTime($user['last_activity']) : 'Never' ?>
                            </div>
                            <div class="total-bookings">
                                <?= $user['total_bookings'] ?> bookings
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
    // Booking Trends Chart
    const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
    const bookingsChart = new Chart(bookingsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($bookingTrendsLabels) ?>,
            datasets: [{
                label: 'Bookings',
                data: <?= json_encode($bookingTrendsData) ?>,
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                borderColor: 'rgba(67, 97, 238, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(67, 97, 238, 1)',
                pointBorderColor: '#fff',
                pointHoverRadius: 5,
                pointHoverBackgroundColor: 'rgba(67, 97, 238, 1)',
                pointHitRadius: 10,
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($revenueLabels) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($revenueData) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: 'rgba(75, 192, 192, 0.4)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '$' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            }
        }
    });

    // Chart period toggle buttons
    document.querySelectorAll('.btn-chart-action').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.btn-chart-action').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            // Here you would typically fetch new data based on the selected period
            // For demo purposes, we're just toggling the active state
        });
    });
    </script>
</body>
</html>
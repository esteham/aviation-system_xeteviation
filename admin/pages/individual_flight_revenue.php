<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Flight Revenue Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card { margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .chart-container { height: 300px; position: relative; }
        .stat-card { border-left: 4px solid; }
        .stat-card.economy { border-color: #4e73df; }
        .stat-card.business { border-color: #1cc88a; }
        .stat-card.first { border-color: #36b9cc; }
        .stat-card.premium { border-color: #f6c23e; }
        .flight-header { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; }
        .select2-container { width: 300px !important; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card flight-header">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 id="flightNumber" class="mb-0">Flight Revenue Analysis</h1>
                                <div id="flightRoute" class="fs-5"></div>
                                <div id="flightTimes" class="text-white-50"></div>
                            </div>
                            <div class="text-end">
                                <div class="input-group">
                                    <select class="form-select" id="flightSelect">
                                        <option value="">Select a Flight</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="loadingIndicator" style="display: none;">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading flight data...</p>
            </div>
        </div>

        <div id="contentArea" style="display: none;">
            <div class="row">
                <div class="col-md-3">
                    <div class="card stat-card economy">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Economy Revenue</h6>
                            <h3 id="economyRevenue" class="card-title">$0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card business">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Business Revenue</h6>
                            <h3 id="businessRevenue" class="card-title">$0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card first">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">First Class Revenue</h6>
                            <h3 id="firstRevenue" class="card-title">$0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card premium">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Premium Revenue</h6>
                            <h3 id="premiumRevenue" class="card-title">$0.00</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Revenue Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Key Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h1 id="totalRevenue" class="display-6">$0.00</h1>
                                            <p class="text-muted mb-0">Total Revenue</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h1 id="totalBookings" class="display-6">0</h1>
                                            <p class="text-muted mb-0">Total Bookings</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h1 id="totalPassengers" class="display-6">0</h1>
                                            <p class="text-muted mb-0">Total Passengers</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h1 id="avgRevenue" class="display-6">$0.00</h1>
                                            <p class="text-muted mb-0">Avg. per Passenger</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Detailed Transactions</h5>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary" id="exportCsvBtn">
                                    <i class="bi bi-download"></i> Export CSV
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="transactionsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Class</th>
                                        <th>Direction</th>
                                        <th>Passengers</th>
                                        <th>Amount</th>
                                        <th>Per Passenger</th>
                                        <th>Payment Method</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <script>
        let revenueChart = null;
        let transactionsTable = null;

        $(document).ready(function() {
            // Initialize Select2 dropdown
            $('#flightSelect').select2({
                placeholder: "Select a flight",
                allowClear: true
            });

            // Initialize empty table
            transactionsTable = $('#transactionsTable').DataTable({
                columns: [
                    { data: 'booking_number' },
                    { data: 'class' },
                    { data: 'flight_direction' },
                    { data: 'passengers' },
                    { 
                        data: 'payment_amount',
                        render: function(data) {
                            return '$' + parseFloat(data).toFixed(2);
                        }
                    },
                    { 
                        data: 'revenue_per_passenger',
                        render: function(data) {
                            return '$' + parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'payment_method' },
                    { 
                        data: 'payment_date',
                        render: function(data) {
                            return new Date(data).toLocaleString();
                        }
                    }
                ]
            });

            // Load flights dropdown on page load
            loadFlightsDropdown();

            // Load flight data when selection changes
            $('#flightSelect').change(function() {
                const flightId = $(this).val();
                if (flightId) {
                    loadFlightData(flightId);
                } else {
                    $('#contentArea').hide();
                }
            });

            // Export CSV button
            $('#exportCsvBtn').click(function() {
                if (transactionsTable.data().any()) {
                    let csvContent = "data:text/csv;charset=utf-8,";
                    const headers = [
                        'Booking ID', 'Class', 'Direction', 'Passengers', 
                        'Amount', 'Per Passenger', 'Payment Method', 'Date'
                    ];
                    csvContent += headers.join(",") + "\r\n";
                    
                    transactionsTable.data().each(function(row) {
                        const rowData = [
                            row.booking_number,
                            row.class,
                            row.flight_direction,
                            row.passengers,
                            '$' + parseFloat(row.payment_amount).toFixed(2),
                            '$' + parseFloat(row.revenue_per_passenger).toFixed(2),
                            row.payment_method,
                            new Date(row.payment_date).toLocaleString()
                        ];
                        csvContent += rowData.join(",") + "\r\n";
                    });
                    
                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", `flight_${$('#flightNumber').text().replace(' ', '_')}_revenue.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });

            // Function to load flights dropdown
            function loadFlightsDropdown() {
                $.ajax({
                    url: 'ajax/get_flights.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const dropdown = $('#flightSelect');
                        dropdown.empty();
                        dropdown.append('<option value=""></option>');
                        
                        response.forEach(flight => {
                            const date = new Date(flight.departure_time);
                            const formattedDate = date.toLocaleDateString();
                            dropdown.append(
                                `<option value="${flight.flight_id}">
                                    ${flight.flight_number} (${flight.departure_airport} → ${flight.arrival_airport}) - ${formattedDate}
                                </option>`
                            );
                        });
                        
                        dropdown.trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading flights:', error);
                    }
                });
            }

            // Function to load flight data
            function loadFlightData(flightId) {
                $('#loadingIndicator').show();
                $('#contentArea').hide();
                
                $.ajax({
                    url: `ajax/individual_flight_revenue.php?flight_id=${flightId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            alert(response.error);
                            return;
                        }

                        // Update flight info
                        const flight = response.flight_info;
                        $('#flightNumber').text(`Flight ${flight.flight_number}`);
                        $('#flightRoute').html(`
                            <i class="bi bi-airplane"></i> ${flight.departure_airport} 
                            <i class="bi bi-arrow-right"></i> 
                            ${flight.arrival_airport}
                        `);
                        $('#flightTimes').html(`
                            ${new Date(flight.departure_time).toLocaleString()} - 
                            ${new Date(flight.arrival_time).toLocaleString()}
                        `);

                        // Update summary stats
                        const summary = response.summary;
                        $('#totalRevenue').text('$' + summary.total_revenue.toFixed(2));
                        $('#totalBookings').text(summary.total_bookings);
                        $('#totalPassengers').text(summary.total_passengers);
                        $('#avgRevenue').text(
                            '$' + (summary.total_revenue / (summary.total_passengers || 1)).toFixed(2)
                        );
                        
                        // Update class revenues
                        $('#economyRevenue').text('$' + summary.by_class.economy.toFixed(2));
                        $('#businessRevenue').text('$' + summary.by_class.business.toFixed(2));
                        $('#firstRevenue').text('$' + summary.by_class.first.toFixed(2));
                        $('#premiumRevenue').text('$' + summary.by_class.premium.toFixed(2));

                        // Update chart
                        if (revenueChart) revenueChart.destroy();
                        
                        const ctx = document.getElementById('revenueChart').getContext('2d');
                        revenueChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(summary.by_class).map(c => c.charAt(0).toUpperCase() + c.slice(1)),
                                datasets: [{
                                    data: Object.values(summary.by_class),
                                    backgroundColor: [
                                        '#4e73df',
                                        '#1cc88a',
                                        '#36b9cc',
                                        '#f6c23e'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return `$${context.raw.toFixed(2)} (${(
                                                    (context.raw / summary.total_revenue) * 100
                                                ).toFixed(1)}%)`;
                                            }
                                        }
                                    },
                                    datalabels: {
                                        formatter: (value) => {
                                            return `$${value.toFixed(2)}`;
                                        },
                                        color: '#fff',
                                        font: {
                                            weight: 'bold'
                                        }
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });

                        // Update transactions table
                        transactionsTable.clear();
                        transactionsTable.rows.add(response.revenue_data);
                        transactionsTable.draw();

                        // Show content
                        $('#loadingIndicator').hide();
                        $('#contentArea').show();
                    },
                    error: function(xhr, status, error) {
                        $('#loadingIndicator').hide();
                        alert('Error loading flight data: ' + error);
                    }
                });
            }
        });
    </script>
</body>
</html>
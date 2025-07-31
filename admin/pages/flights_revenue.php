<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Revenue Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .card { margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .revenue-chart { height: 300px; }
        .clickable-row { cursor: pointer; }
        .clickable-row:hover { background-color: #f5f5f5; }
        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 0.2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
        }
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4">Flight Revenue Dashboard</h1>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Flight Revenue Summary</h5>
                        <div class="d-flex">
                            <div class="form-group me-2 mb-0">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search flights...">
                            </div>
                            <button id="refreshBtn" class="btn btn-sm btn-primary">
                                <span id="refreshText">Refresh</span>
                                <span id="refreshSpinner" class="loading-spinner d-none"></span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="summaryError" class="alert alert-danger d-none"></div>
                        <table id="summaryTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Flight No.</th>
                                    <th>Airline</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th>Bookings</th>
                                    <th>Total Revenue</th>
                                    <th>Economy</th>
                                    <th>Business</th>
                                    <th>First Class</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4" id="detailSection" style="display: none;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Flight Revenue Details</h5>
                        <button id="backButton" class="btn btn-sm btn-secondary">Back to Summary</button>
                    </div>
                    <div class="card-body">
                        <div id="detailError" class="alert alert-danger d-none"></div>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <h6 id="flightTitle"></h6>
                                <canvas id="revenueChart" class="revenue-chart"></canvas>
                            </div>
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table id="detailTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Class</th>
                                                <th>Passengers</th>
                                                <th>Amount</th>
                                                <th>Per Passenger</th>
                                                <th>Payment Method</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
    
    <script>
        $(document).ready(function() {
            let revenueChart = null;
            let summaryTable = $('#summaryTable').DataTable({
                ajax: {
                    url: 'ajax/flight_revenue.php',
                    dataSrc: 'data',
                    error: function(xhr, error, thrown) {
                        $('#summaryError').text('Failed to load flight data: ' + (thrown || 'Unknown error')).removeClass('d-none');
                        console.error('DataTables error:', error, thrown);
                    }
                },
                columns: [
                    { data: 'flight_number' },
                    { data: 'airline_name' },
                    { 
                        data: 'departure_time',
                        render: function(data) {
                            return data ? new Date(data).toLocaleString() : 'N/A';
                        }
                    },
                    { 
                        data: 'arrival_time',
                        render: function(data) {
                            return data ? new Date(data).toLocaleString() : 'N/A';
                        }
                    },
                    { 
                        data: 'total_bookings',
                        render: function(data) {
                            return data || 0;
                        }
                    },
                    { 
                        data: 'total_revenue',
                        render: function(data) {
                            return data ? '$' + parseFloat(data).toFixed(2) : '$0.00';
                        }
                    },
                    { 
                        data: 'economy_revenue',
                        render: function(data) {
                            return data ? '$' + parseFloat(data).toFixed(2) : '$0.00';
                        }
                    },
                    { 
                        data: 'business_revenue',
                        render: function(data) {
                            return data ? '$' + parseFloat(data).toFixed(2) : '$0.00';
                        }
                    },
                    { 
                        data: 'first_class_revenue',
                        render: function(data) {
                            return data ? '$' + parseFloat(data).toFixed(2) : '$0.00';
                        }
                    }
                ],
                createdRow: function(row, data) {
                    $(row).addClass('clickable-row');
                    $(row).attr('data-flight-id', data.flight_id);
                }
            });

            // Search functionality
            $('#searchInput').keyup(function() {
                summaryTable.search($(this).val()).draw();
            });

            // Refresh button
            $('#refreshBtn').click(function() {
                const $btn = $(this);
                const $text = $('#refreshText');
                const $spinner = $('#refreshSpinner');
                
                $btn.prop('disabled', true);
                $text.text('Refreshing...');
                $spinner.removeClass('d-none');
                
                summaryTable.ajax.reload(function(json) {
                    $btn.prop('disabled', false);
                    $text.text('Refresh');
                    $spinner.addClass('d-none');
                    $('#summaryError').addClass('d-none');
                }, false);
            });

            // Click handler for flight details
            $('#summaryTable tbody').on('click', 'tr', function() {
                const flightId = $(this).data('flight-id');
                loadFlightDetails(flightId);
            });

            // Back button handler
            $('#backButton').click(function() {
                $('#detailSection').hide();
                $('#summaryTable_wrapper').show();
                if (revenueChart) {
                    revenueChart.destroy();
                    revenueChart = null;
                }
            });

            function loadFlightDetails(flightId) {
                $('#detailError').addClass('d-none');
                $('#summaryTable_wrapper').hide();
                $('#detailSection').show();
                $('#flightTitle').html('<div class="loading-spinner"></div> Loading flight details...');
                $('#detailTable tbody').empty();

                $.ajax({
                    url: 'ajax/flight_revenue.php?flight_id=' + flightId,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.data && response.data.length > 0) {
                            const flight = response.data[0];
                            $('#flightTitle').html(
                                `<strong>${flight.flight_number}</strong> - ${flight.airline_name}<br>
                                ${flight.departure_time ? new Date(flight.departure_time).toLocaleString() : 'N/A'} to 
                                ${flight.arrival_time ? new Date(flight.arrival_time).toLocaleString() : 'N/A'}`
                            );

                            // Prepare data for chart
                            const classData = response.data.reduce((acc, item) => {
                                if (!acc[item.class]) acc[item.class] = 0;
                                acc[item.class] += parseFloat(item.payment_amount || 0);
                                return acc;
                            }, {});

                            // Destroy previous chart if exists
                            if (revenueChart) revenueChart.destroy();

                            // Create new chart
                            const ctx = document.getElementById('revenueChart');
                            if (ctx && ctx.getContext) {
                                revenueChart = new Chart(ctx.getContext('2d'), {
                                    type: 'pie',
                                    data: {
                                        labels: Object.keys(classData),
                                        datasets: [{
                                            data: Object.values(classData),
                                            backgroundColor: [
                                                '#4e73df',
                                                '#1cc88a',
                                                '#36b9cc',
                                                '#f6c23e'
                                            ]
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: {
                                                position: 'bottom'
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return `$${context.raw.toFixed(2)}`;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            } else {
                                console.error('Canvas element not found or not supported');
                            }

                            // Populate detail table
                            const detailTable = $('#detailTable tbody').empty();
                            response.data.forEach(item => {
                                detailTable.append(`
                                    <tr>
                                        <td>${item.booking_number || 'N/A'}</td>
                                        <td>${item.class || 'N/A'}</td>
                                        <td>${item.passengers || 0}</td>
                                        <td>$${parseFloat(item.payment_amount || 0).toFixed(2)}</td>
                                        <td>$${parseFloat(item.revenue_per_passenger || 0).toFixed(2)}</td>
                                        <td>${item.payment_method || 'N/A'}</td>
                                        <td>${item.payment_date ? new Date(item.payment_date).toLocaleString() : 'N/A'}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            $('#detailError').text('No detailed data found for this flight').removeClass('d-none');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#detailError').text('Error loading flight details: ' + (error || 'Unknown error')).removeClass('d-none');
                        console.error('Error loading flight details:', error);
                    }
                });
            }
        });
    </script>
</body>
</html>
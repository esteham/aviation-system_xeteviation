<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';

// Check admin authentication
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Aircraft Management";

// Get database connection
$db = DBConfig::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_aircraft']) || isset($_POST['update_aircraft'])) {
            // Prepare seat map configuration
            $seatMap = json_encode([
                'decks' => [
                    [
                        'name' => 'Main Deck',
                        'sections' => [
                            [
                                'class' => 'economy',
                                'rows' => $_POST['economy_rows'],
                                'left_seats' => explode(',', $_POST['economy_left_seats']),
                                'right_seats' => explode(',', $_POST['economy_right_seats'])
                            ],
                            [
                                'class' => 'business',
                                'rows' => $_POST['business_rows'],
                                'left_seats' => explode(',', $_POST['business_left_seats']),
                                'right_seats' => explode(',', $_POST['business_right_seats'])
                            ],
                            [
                                'class' => 'first_class',
                                'rows' => $_POST['first_class_rows'],
                                'left_seats' => explode(',', $_POST['first_class_left_seats']),
                                'right_seats' => explode(',', $_POST['first_class_right_seats'])
                            ]
                        ]
                    ]
                ]
            ]);
            
            // Calculate seat counts from configuration
            $economy_seats = $_POST['economy_rows'] * 
                           (count(explode(',', $_POST['economy_left_seats'])) + 
                            count(explode(',', $_POST['economy_right_seats'])));
            
            $business_seats = $_POST['business_rows'] * 
                            (count(explode(',', $_POST['business_left_seats'])) + 
                             count(explode(',', $_POST['business_right_seats'])));
            
            $first_class_seats = $_POST['first_class_rows'] * 
                               (count(explode(',', $_POST['first_class_left_seats'])) + 
                                count(explode(',', $_POST['first_class_right_seats'])));
            
            $total_seats = $economy_seats + $business_seats + $first_class_seats;
            
            if (isset($_POST['add_aircraft'])) {
                // Add new aircraft
                $stmt = $db->prepare("
                    INSERT INTO aircrafts (
                        airline_id, model, registration_number, 
                        total_seats, economy_seats, business_seats, first_class_seats, seat_map
                    ) VALUES (
                        :airline_id, :model, :registration_number,
                        :total_seats, :economy_seats, :business_seats, :first_class_seats, :seat_map
                    )
                ");
                
                $stmt->execute([
                    ':airline_id' => $_POST['airline_id'],
                    ':model' => $_POST['model'],
                    ':registration_number' => $_POST['registration_number'],
                    ':total_seats' => $total_seats,
                    ':economy_seats' => $economy_seats,
                    ':business_seats' => $business_seats,
                    ':first_class_seats' => $first_class_seats,
                    ':seat_map' => $seatMap
                ]);
                
                $_SESSION['message'] = 'Aircraft added successfully';
                
            } elseif (isset($_POST['update_aircraft'])) {
                // Update aircraft
                $stmt = $db->prepare("
                    UPDATE aircrafts SET
                        airline_id = :airline_id,
                        model = :model,
                        registration_number = :registration_number,
                        total_seats = :total_seats,
                        economy_seats = :economy_seats,
                        business_seats = :business_seats,
                        first_class_seats = :first_class_seats,
                        seat_map = :seat_map
                    WHERE aircraft_id = :aircraft_id
                ");
                
                $stmt->execute([
                    ':airline_id' => $_POST['airline_id'],
                    ':model' => $_POST['model'],
                    ':registration_number' => $_POST['registration_number'],
                    ':total_seats' => $total_seats,
                    ':economy_seats' => $economy_seats,
                    ':business_seats' => $business_seats,
                    ':first_class_seats' => $first_class_seats,
                    ':seat_map' => $seatMap,
                    ':aircraft_id' => $_POST['aircraft_id']
                ]);
                
                $_SESSION['message'] = 'Aircraft updated successfully';
            }
            
        } elseif (isset($_POST['delete_aircraft'])) {
            // Delete aircraft
            $stmt = $db->prepare("DELETE FROM aircrafts WHERE aircraft_id = ?");
            $stmt->execute([$_POST['aircraft_id']]);
            $_SESSION['message'] = 'Aircraft deleted successfully';
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }
}

// Get all aircrafts with airline information
$stmt = $db->query("
    SELECT a.*, al.name as airline_name, al.logo_url
    FROM aircrafts a
    JOIN airlines al ON a.airline_id = al.airline_id
    ORDER BY a.model
");
$aircrafts = $stmt->fetchAll();

// Get all airlines for dropdown
$airlines = $db->query("SELECT * FROM airlines ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        img.airline-logo-sm {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        .seat-count-badge {
            font-size: 0.75rem;
            padding: 0.25em 0.4em;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <h1 class="mb-4">Aircraft Management</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">All Aircrafts</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAircraftModal">
                <i class="fas fa-plus me-1"></i> Add New Aircraft
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Airline</th>
                            <th>Model</th>
                            <th>Registration</th>
                            <th>Seats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aircrafts as $aircraft): 
                            $seatMap = json_decode($aircraft['seat_map'], true);
                        ?>
                        <tr>
                            <td><?= $aircraft['aircraft_id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($aircraft['logo_url']): ?>
                                    <img src="../<?= htmlspecialchars($aircraft['logo_url']) ?>" 
                                         alt="<?= htmlspecialchars($aircraft['airline_name']) ?>" 
                                         class="airline-logo-sm me-2">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($aircraft['airline_name']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($aircraft['model']) ?></td>
                            <td><?= htmlspecialchars($aircraft['registration_number']) ?></td>
                            <td>
                                <span class="badge bg-primary seat-count-badge">Total: <?= $aircraft['total_seats'] ?></span>
                                <span class="badge bg-success seat-count-badge">First: <?= $aircraft['first_class_seats'] ?></span>
                                <span class="badge bg-warning text-dark seat-count-badge">Business: <?= $aircraft['business_seats'] ?></span>
                                <span class="badge bg-secondary seat-count-badge">Economy: <?= $aircraft['economy_seats'] ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1 edit-aircraft" 
                                        data-aircraft='<?= json_encode($aircraft, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="aircraft_id" value="<?= $aircraft['aircraft_id'] ?>">
                                    <button type="submit" name="delete_aircraft" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this aircraft?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Aircraft Modal -->
<div class="modal fade" id="addAircraftModal" tabindex="-1" aria-labelledby="addAircraftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAircraftModalLabel">Add New Aircraft</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Airline</label>
                            <select name="airline_id" class="form-select" required>
                                <option value="">Select Airline</option>
                                <?php foreach ($airlines as $airline): ?>
                                <option value="<?= $airline['airline_id'] ?>">
                                    <?= htmlspecialchars($airline['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Registration Number</label>
                            <input type="text" name="registration_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Seats</label>
                            <input type="number" name="total_seats" class="form-control" required readonly>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Seat Configuration</h5>
                    
                    <div class="seat-configuration">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6>First Class</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Number of Rows</label>
                                <input type="number" name="first_class_rows" class="form-control" value="4" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Left Seats (comma separated)</label>
                                <input type="text" name="first_class_left_seats" class="form-control" value="A,C">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Right Seats (comma separated)</label>
                                <input type="text" name="first_class_right_seats" class="form-control" value="B,D">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6>Business Class</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Number of Rows</label>
                                <input type="number" name="business_rows" class="form-control" value="8" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Left Seats (comma separated)</label>
                                <input type="text" name="business_left_seats" class="form-control" value="A,C">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Right Seats (comma separated)</label>
                                <input type="text" name="business_right_seats" class="form-control" value="D,F">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6>Economy Class</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Number of Rows</label>
                                <input type="number" name="economy_rows" class="form-control" value="20" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Left Seats (comma separated)</label>
                                <input type="text" name="economy_left_seats" class="form-control" value="A,B,C">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Right Seats (comma separated)</label>
                                <input type="text" name="economy_right_seats" class="form-control" value="D,E,F">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_aircraft" class="btn btn-primary">Save Aircraft</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Aircraft Modal -->
<div class="modal fade" id="editAircraftModal" tabindex="-1" aria-labelledby="editAircraftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aircraft_id" id="edit_aircraft_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAircraftModalLabel">Edit Aircraft</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Airline</label>
                            <select name="airline_id" id="edit_airline_id" class="form-select" required>
                                <option value="">Select Airline</option>
                                <?php foreach ($airlines as $airline): ?>
                                <option value="<?= $airline['airline_id'] ?>">
                                    <?= htmlspecialchars($airline['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" id="edit_model" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Registration Number</label>
                            <input type="text" name="registration_number" id="edit_registration_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Seats</label>
                            <input type="number" name="total_seats" id="edit_total_seats" class="form-control" required readonly>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Seat Configuration</h5>
                    
                    <div class="seat-configuration">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6>First Class</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Number of Rows</label>
                                <input type="number" name="first_class_rows" id="edit_first_class_rows" class="form-control" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Left Seats (comma separated)</label>
                                <input type="text" name="first_class_left_seats" id="edit_first_class_left_seats" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Right Seats (comma separated)</label>
                                <input type="text" name="first_class_right_seats" id="edit_first_class_right_seats" class="form-control">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6>Business Class</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Number of Rows</label>
                                <input type="number" name="business_rows" id="edit_business_rows" class="form-control" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Left Seats (comma separated)</label>
                                <input type="text" name="business_left_seats" id="edit_business_left_seats" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Right Seats (comma separated)</label>
                                <input type="text" name="business_right_seats" id="edit_business_right_seats" class="form-control">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6>Economy Class</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Number of Rows</label>
                                <input type="number" name="economy_rows" id="edit_economy_rows" class="form-control" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Left Seats (comma separated)</label>
                                <input type="text" name="economy_left_seats" id="edit_economy_left_seats" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Right Seats (comma separated)</label>
                                <input type="text" name="economy_right_seats" id="edit_economy_right_seats" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_aircraft" class="btn btn-primary">Update Aircraft</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {

    // Calculate total seats when configuration fields change
    function calculateTotalSeats(prefix = '') {
        const fcRows = parseInt($(`#${prefix}first_class_rows`).val()) || 0;
        const fcLeft = $(`#${prefix}first_class_left_seats`).val().split(',').filter(Boolean).length;
        const fcRight = $(`#${prefix}first_class_right_seats`).val().split(',').filter(Boolean).length;
        const fcSeats = fcRows * (fcLeft + fcRight);

        const bsRows = parseInt($(`#${prefix}business_rows`).val()) || 0;
        const bsLeft = $(`#${prefix}business_left_seats`).val().split(',').filter(Boolean).length;
        const bsRight = $(`#${prefix}business_right_seats`).val().split(',').filter(Boolean).length;
        const bsSeats = bsRows * (bsLeft + bsRight);

        const ecRows = parseInt($(`#${prefix}economy_rows`).val()) || 0;
        const ecLeft = $(`#${prefix}economy_left_seats`).val().split(',').filter(Boolean).length;
        const ecRight = $(`#${prefix}economy_right_seats`).val().split(',').filter(Boolean).length;
        const ecSeats = ecRows * (ecLeft + ecRight);

        const totalSeats = fcSeats + bsSeats + ecSeats;
        $(`#${prefix}total_seats`).val(totalSeats);
    }

    // Attach input event listeners for both add and edit forms
    const seatFields = [
        'first_class_rows', 'first_class_left_seats', 'first_class_right_seats',
        'business_rows', 'business_left_seats', 'business_right_seats',
        'economy_rows', 'economy_left_seats', 'economy_right_seats'
    ];

    seatFields.forEach(field => {
        $(`#${field}`).on('input', function () {
            calculateTotalSeats('');
        });
        $(`#edit_${field}`).on('input', function () {
            calculateTotalSeats('edit_');
        });
    });

    // Handle edit-aircraft button click
    $(document).on('click', '.edit-aircraft', function () {
        let aircraftData = $(this).attr('data-aircraft');

        try {
            aircraftData = JSON.parse(aircraftData);
        } catch (err) {
            alert("Error parsing aircraft data");
            return;
        }

        const seatMap = aircraftData.seat_map ? JSON.parse(aircraftData.seat_map) : null;

        // Aircraft base info
        $('#edit_aircraft_id').val(aircraftData.aircraft_id);
        $('#edit_airline_id').val(aircraftData.airline_id);
        $('#edit_model').val(aircraftData.model);
        $('#edit_registration_number').val(aircraftData.registration_number);
        $('#edit_total_seats').val(aircraftData.total_seats);

        if (seatMap && seatMap.decks && seatMap.decks[0].sections) {
            const fc = seatMap.decks[0].sections.find(s => s.class === 'first_class') || {};
            const bs = seatMap.decks[0].sections.find(s => s.class === 'business') || {};
            const ec = seatMap.decks[0].sections.find(s => s.class === 'economy') || {};

            $('#edit_first_class_rows').val(fc.rows || '');
            $('#edit_first_class_left_seats').val((fc.left_seats || []).join(','));
            $('#edit_first_class_right_seats').val((fc.right_seats || []).join(','));

            $('#edit_business_rows').val(bs.rows || '');
            $('#edit_business_left_seats').val((bs.left_seats || []).join(','));
            $('#edit_business_right_seats').val((bs.right_seats || []).join(','));

            $('#edit_economy_rows').val(ec.rows || '');
            $('#edit_economy_left_seats').val((ec.left_seats || []).join(','));
            $('#edit_economy_right_seats').val((ec.right_seats || []).join(','));
        }

        // Show the edit modal
        new bootstrap.Modal(document.getElementById('editAircraftModal')).show();
    });
});
</script>

</body>
</html>
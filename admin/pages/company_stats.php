<?php
require_once __DIR__ . '/../../config/dbconfig.php';
require_once __DIR__ . '/../../config/functions.php';


// Check admin authentication (uncomment when ready)
// if (!isAdmin()) {
//     redirect('../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
// }

// Set page title
$pageTitle = "Flight Management";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $founded_year = $_POST['founded_year'];
    $headquarters = $_POST['headquarters'];
    $team_countries = $_POST['team_countries'];
    $airline_partners = $_POST['airline_partners'];
    $airport_partners = $_POST['airport_partners'];
    $annual_passengers = $_POST['annual_passengers'];
    $delay_reduction = $_POST['delay_reduction'];
    
    // Update the single record in company_stats
    $stmt = $db->prepare("UPDATE company_stats SET 
        founded_year = ?, 
        headquarters = ?, 
        team_countries = ?, 
        airline_partners = ?, 
        airport_partners = ?, 
        annual_passengers = ?, 
        delay_reduction = ? 
        WHERE id = 1");
    $stmt->execute([$founded_year, $headquarters, $team_countries, $airline_partners, 
                   $airport_partners, $annual_passengers, $delay_reduction]);
    
    $_SESSION['message'] = "Company stats updated successfully!";
    //header("Location: company_stats.php");
    exit();
}

// Get current stats
$stmt = $db->query("SELECT * FROM company_stats WHERE id = 1");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<div class="container">
    <h2>Company Statistics</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label>Founded Year</label>
            <input type="text" name="founded_year" class="form-control" value="<?= htmlspecialchars($stats['founded_year']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Headquarters</label>
            <input type="text" name="headquarters" class="form-control" value="<?= htmlspecialchars($stats['headquarters']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Team Countries</label>
            <input type="text" name="team_countries" class="form-control" value="<?= htmlspecialchars($stats['team_countries']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Airline Partners</label>
            <input type="number" name="airline_partners" class="form-control" value="<?= htmlspecialchars($stats['airline_partners']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Airport Partners</label>
            <input type="number" name="airport_partners" class="form-control" value="<?= htmlspecialchars($stats['airport_partners']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Annual Passengers</label>
            <input type="number" name="annual_passengers" class="form-control" value="<?= htmlspecialchars($stats['annual_passengers']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Delay Reduction (%)</label>
            <input type="number" name="delay_reduction" class="form-control" value="<?= htmlspecialchars($stats['delay_reduction']) ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Stats</button>
    </form>
</div>

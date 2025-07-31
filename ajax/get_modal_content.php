<?php
session_start();
require_once __DIR__ . '/config/dbconfig.php';

try {
    // Get active promotions
    $currentDateTime = date('Y-m-d H:i:s');
    $stmt = $db->prepare("SELECT * FROM promotional_content 
                         WHERE is_active = 1 
                         AND (start_date IS NULL OR start_date <= ?) 
                         AND (end_date IS NULL OR end_date >= ?)
                         ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$currentDateTime, $currentDateTime]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($promo) {
        // Main promo content (smaller version)
        echo '<div class="promo-content">';
        
        if ($promo['content_type'] === 'image') {
            echo '
            <a href="#" data-bs-toggle="modal" data-bs-target="#promoModal">
                <img src="' . htmlspecialchars($promo['media_path']) . '" alt="' . htmlspecialchars($promo['title']) . '" class="img-fluid rounded">
            </a>';
        } else {
            echo '
            <a href="#" data-bs-toggle="modal" data-bs-target="#promoModal">
                <div class="ratio ratio-16x9">
                    <video controls muted class="rounded">
                        <source src="' . htmlspecialchars($promo['media_path']) . '" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </a>';
        }
        
        echo '
            <div class="promo-text text-center mt-3">
                <h4>' . htmlspecialchars($promo['title']) . '</h4>
                <p class="text-muted">' . htmlspecialchars($promo['description']) . '</p>
            </div>
        </div>';
        
        // Add the button if configured
        // if (!empty($promo['button_text'])) {
        //     echo '<div class="text-center mt-3">
        //         <a href="' . htmlspecialchars($promo['button_link']) . '" class="btn btn-primary">
        //             ' . htmlspecialchars($promo['button_text']) . '
        //         </a>
        //     </div>';
        // }
        
        // Modal for full-size content
        echo '
        <div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="promoModalLabel">' . htmlspecialchars($promo['title']) . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">';
        
                        if ($promo['content_type'] === 'image') {
                            echo '<img src="' . htmlspecialchars($promo['media_path']) . '" alt="' . htmlspecialchars($promo['title']) . '" class="img-fluid" style="max-height: 80vh;">';
                        } else {
                            echo '<video controls autoplay class="w-100">
                                    <source src="' . htmlspecialchars($promo['media_path']) . '" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>';
                        }
                        
                        echo '
                    </div>
                </div>
            </div>
        </div>';
    } else {
        // Default content if no promotions are active
        echo '
        <div class="promo-content text-center py-4">
            <h4>Welcome to Our Travel Site!</h4>
            <p>Get 10% off your first booking with code: WELCOME10</p>
            <a href="special-offers.php" class="btn btn-primary">View Offers</a>
        </div>';
    }
} catch (PDOException $e) {
    // Fallback content if database fails
    echo '
    <div class="promo-content text-center py-4">
        <h4>Special Travel Offers</h4>
        <p>Check out our latest deals and discounts</p>
        <a href="special-offers.php" class="btn btn-primary">View Offers</a>
    </div>';
}
?>
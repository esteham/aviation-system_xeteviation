<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

// $user = new USER();

if(!$user->is_logged_in() || ($_SESSION['user_type'], ['admin', 'manager', 'delivaryman'])
{
    // If not logged in or not an admin, redirect to login page
    header('location: login.php');
    exit;
}
?>

<?php 
include 'includes/sidebar.php';
include 'includes/header.php';
?>


    <!-- Main Content -->
    <div class="main-content">
        <?php
        if(isset($_GET['page'])) {
            $page = $_GET['page'];
            $file = "pages/{$page}.php";

            if(file_exists($file)) {
                include $file;
            } else {
                echo '<div class="alert alert-danger">Page Not Found!</div>';
            }
        } else {
            include 'pages/dashboard.php';
        }
        ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Add active class to parent of active submenu item
        document.addEventListener('DOMContentLoaded', function() {
            const submenuLinks = document.querySelectorAll('.submenu .nav-link.active');
            submenuLinks.forEach(link => {
                link.closest('.collapse').previousElementSibling.classList.add('active');
            });
        });
    </script>

<?=include 'includes/footer.php';?>

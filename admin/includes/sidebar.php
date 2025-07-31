<div class="sidebar-modern">
    <div class="sidebar-header">
        <div class="logo-container">
            <i class="fas fa-plane-departure logo-icon"></i>
            <h3>Aviation<span>Control</span></h3>
        </div>
        <div class="sidebar-toggle">
            <i class="fas fa-chevron-left toggle-icon"></i>
        </div>
    </div>

    <div class="sidebar-menu">
        <ul class="menu-list">
            <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="index.php?page=dashboard" class="menu-link" onclick="return handleMenuClick('dashboard')">
                    <div class="menu-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <span class="menu-text">Dashboard</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </li>
            
            <!-- Flights Management -->
            <li class="menu-group <?php echo (in_array($current_page, ['flights', 'add_flight', 'edit_flight', 'flight_schedules'])) ? 'active' : ''; ?>">
                <a href="#flightSubmenu" class="menu-link has-submenu collapsed" data-bs-toggle="collapse" aria-expanded="false" onclick="return handleSubmenuClick(event, 'flightSubmenu')">
                    <div class="menu-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                    <span class="menu-text">Flights</span>
                    <i class="fas fa-chevron-right submenu-arrow"></i>
                    <div class="menu-hover-effect"></div>
                </a>
                <ul class="submenu collapse" id="flightSubmenu" data-parent=".sidebar-menu">
                    <li class="<?php echo ($current_page == 'flights') ? 'active' : ''; ?>">
                        <a href="index.php?page=flights" onclick="return handleMenuClick('flights')">
                            <i class="fas fa-list"></i>
                            <span>All Flights</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'add_flight') ? 'active' : ''; ?>">
                        <a href="index.php?page=airlines" onclick="return handleMenuClick('add_flight')">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add New Airlines</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'flight_schedules') ? 'active' : ''; ?>">
                        <a href="index.php?page=flight_schedules" onclick="return handleMenuClick('flight_schedules')">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Schedules</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Aircraft Management -->
            <li class="menu-group <?php echo (in_array($current_page, ['aircrafts', 'add_aircraft'])) ? 'active' : ''; ?>">
                <a href="#aircraftSubmenu" class="menu-link has-submenu collapsed" data-bs-toggle="collapse" aria-expanded="false" onclick="return handleSubmenuClick(event, 'aircraftSubmenu')">
                    <div class="menu-icon">
                        <i class="fas fa-fighter-jet"></i>
                    </div>
                    <span class="menu-text">Aircraft</span>
                    <i class="fas fa-chevron-right submenu-arrow"></i>
                    <div class="menu-hover-effect"></div>
                </a>
                <ul class="submenu collapse" id="aircraftSubmenu" data-parent=".sidebar-menu">
                    <li class="<?php echo ($current_page == 'aircrafts') ? 'active' : ''; ?>">
                        <a href="index.php?page=aircrafts" onclick="return handleMenuClick('aircrafts')">
                            <i class="fas fa-list"></i>
                            <span>All Aircraft</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'add_aircraft') ? 'active' : ''; ?>">
                        <a href="index.php?page=add_aircraft" onclick="return handleMenuClick('add_aircraft')">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add New Aircraft</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Airports Management -->
            <li class="<?php echo (in_array($current_page, ['airports', 'add_airport'])) ? 'active' : ''; ?>">
                <a href="index.php?page=airports" class="menu-link" onclick="return handleMenuClick('airports')">
                    <div class="menu-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <span class="menu-text">Airports</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </li>
            
            <!-- Bookings Section -->
            <li class="menu-group <?php echo (in_array($current_page, ['bookings', 'booking_details']) )? 'active' : ''; ?>">
                <a href="#bookingSubmenu" class="menu-link has-submenu collapsed" data-bs-toggle="collapse" aria-expanded="false" onclick="return handleSubmenuClick(event, 'hotelSubmenu')">
                    <div class="menu-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <span class="menu-text">Bookings</span>
                    <i class="fas fa-chevron-right submenu-arrow"></i>
                    <div class="menu-hover-effect"></div>
                </a>
                <ul class="submenu collapse" id="bookingSubmenu" data-parent=".sidebar-menu">
                    <li class="<?php echo ($current_page == 'booking') ? 'active' : ''; ?>">
                        <a href="index.php?page=bookings" onclick="return handleMenuClick('booking')">
                            <i class="fas fa-list"></i>
                            <span>Booking</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'passenger_list_by_flight') ? 'active' : ''; ?>">
                        <a href="index.php?page=passenger_list_by_flight" onclick="return handleMenuClick('passenger_list_by_flight')">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Passenger List</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!--Passenger list-->
            <li class="<?php echo (in_array($current_page, ['passenger_list_by_flight', 'passengers'])) ? 'active' : ''; ?>">
                <a href="index.php?page=passenger_list_by_flight" class="menu-link" onclick="return handleMenuClick('passengers')">
                    <div class="menu-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <span class="menu-text">Passenger List</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </li>

            <!-- Reports -->
            <li class="<?php echo (in_array($current_page, ['reports', 'financial_reports'])) ? 'active' : ''; ?>">
                <a href="#reportsSubmenu" class="menu-link has-submenu collapsed" data-bs-toggle="collapse" aria-expanded="false" onclick="return handleSubmenuClick(event, 'reportsSubmenu')">
                    <div class="menu-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="menu-text">Revenue Reports</span>
                    <i class="fas fa-chevron-right submenu-arrow"></i>
                    <div class="menu-hover-effect"></div>
                </a>
                <ul class="submenu collapse" id="reportsSubmenu" data-parent=".sidebar-menu">
                    <li class="<?php echo ($current_page == 'reports') ? 'active' : ''; ?>">
                        <a href="index.php?page=flights_revenue" onclick="return handleMenuClick('reports')">
                            <i class="fas fa-list"></i>
                            <span>Revenue Reports</span>
                        </a>
                    </li>
                    <!--<li class="<?php echo ($current_page == 'flights_reports') ? 'active' : ''; ?>">-->
                    <!--    <a href="index.php?page=individual_flight_revenue" onclick="return handleMenuClick('flights_reports')">-->
                    <!--        <i class="fas fa-plus-circle"></i>-->
                    <!--        <span>Indevudual Flights</span>-->
                    <!--    </a>-->
                    <!--</li>-->
                </ul>
            </li>

            <!-- Hotel section -->
            <!--<li class="menu-group <?php echo (in_array($current_page, ['hostel', 'hostel_bokings']) )? 'active' : ''; ?>">-->
            <!--    <a href="#hotelSubmenu" class="menu-link has-submenu collapsed" data-bs-toggle="collapse" aria-expanded="false" onclick="return handleSubmenuClick(event, 'hotelSubmenu')">-->
            <!--        <div class="menu-icon">-->
            <!--            <i class="fa-solid fa-hotel"></i>-->
            <!--        </div>-->
            <!--        <span class="menu-text">Hotel</span>-->
            <!--        <i class="fas fa-chevron-right submenu-arrow"></i>-->
            <!--        <div class="menu-hover-effect"></div>-->
            <!--    </a>-->
            <!--    <ul class="submenu collapse" id="hotelSubmenu" data-parent=".sidebar-menu">-->
            <!--        <li class="<?php echo ($current_page == 'hostel') ? 'active' : ''; ?>">-->
            <!--            <a href="index.php?page=hostel" onclick="return handleMenuClick('hostel')">-->
            <!--                <i class="fas fa-list"></i>-->
            <!--                <span>Hotels</span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li class="<?php echo ($current_page == 'hostel_bokings') ? 'active' : ''; ?>">-->
            <!--            <a href="index.php?page=hostel_bokings" onclick="return handleMenuClick('hostel_bokings')">-->
            <!--                <i class="fas fa-plus-circle"></i>-->
            <!--                <span>Hotel Bookings</span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--    </ul>-->
            <!--</li>-->

            <!-- Package Bookings Management -->
            <!--<li class="<?php echo (in_array($current_page, ['packages', 'packages_details']) )? 'active' : ''; ?>">-->
            <!--    <a href="index.php?page=packages" class="menu-link" onclick="return handleMenuClick('packages')">-->
            <!--        <div class="menu-icon">-->
            <!--            <i class="fa-solid fa-boxes-packing"></i>-->
            <!--        </div>-->
            <!--        <span class="menu-text">Packages</span>-->
            <!--        <div class="menu-hover-effect"></div>-->
            <!--    </a>-->
            <!--</li>-->
            
            <!-- Users Management -->
            <!--<li class="menu-group <?php echo (in_array($current_page, ['users', 'add_user', 'edit_user', 'user_roles']) )? 'active' : ''; ?>">-->
            <!--    <a href="#userSubmenu" class="menu-link has-submenu collapsed" data-bs-toggle="collapse" aria-expanded="false" onclick="return handleSubmenuClick(event, 'userSubmenu')">-->
            <!--        <div class="menu-icon">-->
            <!--            <i class="fas fa-users-cog"></i>-->
            <!--        </div>-->
            <!--        <span class="menu-text">Users</span>-->
            <!--        <i class="fas fa-chevron-right submenu-arrow"></i>-->
            <!--        <div class="menu-hover-effect"></div>-->
            <!--    </a>-->
            <!--    <ul class="submenu collapse" id="userSubmenu" data-parent=".sidebar-menu">-->
            <!--        <li class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>">-->
            <!--            <a href="index.php?page=users" onclick="return handleMenuClick('users')">-->
            <!--                <i class="fas fa-users"></i>-->
            <!--                <span>All Users</span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li class="<?php echo ($current_page == 'add_user') ? 'active' : ''; ?>">-->
            <!--            <a href="index.php?page=add_user" onclick="return handleMenuClick('add_user')">-->
            <!--                <i class="fas fa-user-plus"></i>-->
            <!--                <span>Add New User</span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--        <li class="<?php echo ($current_page == 'user_roles') ? 'active' : ''; ?>">-->
            <!--            <a href="index.php?page=user_roles" onclick="return handleMenuClick('user_roles')">-->
            <!--                <i class="fas fa-user-tag"></i>-->
            <!--                <span>User Roles</span>-->
            <!--            </a>-->
            <!--        </li>-->
            <!--    </ul>-->
            <!--</li>-->

            <li class="<?php echo ($current_page == 'testimonials') ? 'active' : ''; ?>">
                <a href="index.php?page=testimonials" class="menu-link" onclick="return handleMenuClick('testimonials')">
                    <div class="menu-icon">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <span class="menu-text">Testimonials</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </li>

            <li class="<?php echo ($current_page == 'blog_posts') ? 'active' : ''; ?>">
                <a href="index.php?page=blog_posts" class="menu-link" onclick="return handleMenuClick('blog_posts')">
                    <div class="menu-icon">
                        <i class="fas fa-blog"></i>
                    </div>
                    <span class="menu-text">Blog</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </li>

            <!-- About section -->
            <li class="menu-group <?php echo (in_array($current_page, ['company_stats', 'team_members', 'technologies', 'features'])) ? 'active' : ''; ?>">
                <a href="#aboutSubmenu" class="menu-link has-submenu collapsed" data-bs-toggle="collapse" aria-expanded="false" onclick="return handleSubmenuClick(event, 'aboutSubmenu')">
                    <div class="menu-icon">
                        <i class="fa-solid fa-info"></i>
                    </div>
                    <span class="menu-text">About</span>
                    <i class="fas fa-chevron-right submenu-arrow"></i>
                    <div class="menu-hover-effect"></div>
                </a>
                <ul class="submenu collapse" id="aboutSubmenu" data-parent=".sidebar-menu">
                    <li class="<?php echo ($current_page == 'company_stats') ? 'active' : ''; ?>">
                        <a href="index.php?page=company_stats" onclick="return handleMenuClick('company_stats')">
                            <i class="fas fa-chart-bar"></i>
                            <span>Company Stats</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'team_members') ? 'active' : ''; ?>">
                        <a href="index.php?page=team_members" onclick="return handleMenuClick('team_members')">
                            <i class="fas fa-users"></i>
                            <span>Team Members</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'technologies') ? 'active' : ''; ?>">
                        <a href="index.php?page=technologies" onclick="return handleMenuClick('technologies')">
                            <i class="fas fa-microchip"></i>
                            <span>Technologies</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'features') ? 'active' : ''; ?>">
                        <a href="index.php?page=features" onclick="return handleMenuClick('features')">
                            <i class="fas fa-star"></i>
                            <span>Features</span>
                        </a>
                    </li>
                </ul>
            </li>

             <!-- Message -->
            <li class="<?php echo (in_array($current_page, ['reports', 'financial_reports'])) ? 'active' : ''; ?>">
                <a href="index.php?page=admin_messages" class="menu-link" onclick="return handleMenuClick('reports')">
                    <div class="menu-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="menu-text">Message</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </li>
            
            <!-- System Settings -->
            <li class="<?php echo (in_array($current_page, ['settings', 'backup'])) ? 'active' : ''; ?>">
                <a href="index.php?page=settings" class="menu-link" onclick="return handleMenuClick('settings')">
                    <div class="menu-icon">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <span class="menu-text">Settings</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </li>
            <li class="<?php echo (in_array($current_page, [])) ? 'active' : ''; ?>">
                <a href="index.php?page=promotions" class="menu-link" onclick="return handleMenuClick('promotions')">
                    <div class="menu-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <span class="menu-text">Promotions</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- <div class="sidebar-footer">
        <div class="user-profile">
            <div class="avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="user-info">
                <span class="username"><?php echo isset($user['userName']) ? htmlspecialchars($user['userName']) : 'Admin'; ?></span>
                <span class="role">Administrator</span>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div> -->
</div>

<!-- Add this PHP at the top of your file (before HTML) -->
<?php
// Get current page from URL parameter
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* Modern Sidebar Styles */
.sidebar-modern {
    width: 280px;
    height: 100vh;
    background: linear-gradient(135deg, rgb(17, 25, 58), #2a3a7c);
    color: #fff;
    position: fixed;
    transition: all 0.3s ease;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    overflow-y: auto;
}

.sidebar-header {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    position: sticky;
    top: 0;
    background: inherit;
    z-index: 1;
}

.logo-container {
    display: flex;
    align-items: center;
}

.logo-icon {
    font-size: 24px;
    margin-right: 12px;
    color: #4fc3f7;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    white-space: nowrap;
}

.sidebar-header h3 span {
    color: #4fc3f7;
    font-weight: 300;
}

.sidebar-toggle {
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: all 0.3s;
}

.sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
}

.toggle-icon {
    transition: all 0.3s;
}

.sidebar-menu {
    padding: 15px 0;
    height: calc(100vh - 160px);
}

.menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    position: relative;
    transition: all 0.3s;
    white-space: nowrap;
}

.menu-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.05);
}

.menu-icon {
    width: 24px;
    text-align: center;
    margin-right: 15px;
    font-size: 16px;
    flex-shrink: 0;
}

.menu-text {
    flex: 1;
    font-size: 15px;
    font-weight: 400;
}

.submenu-arrow {
    font-size: 12px;
    transition: all 0.3s;
    margin-left: auto;
}

.has-submenu[aria-expanded="true"] .submenu-arrow {
    transform: rotate(90deg);
}

.menu-hover-effect {
    position: absolute;
    left: 0;
    top: 0;
    width: 3px;
    height: 100%;
    background: #4fc3f7;
    transform: scaleY(0);
    transform-origin: top;
    transition: transform 0.2s ease;
}

.menu-link:hover .menu-hover-effect,
.menu-list > li.active .menu-hover-effect {
    transform: scaleY(1);
}

.submenu {
    list-style: none;
    padding: 0;
    margin: 0;
    background: rgba(0, 0, 0, 0.1);
}

.submenu li a {
    display: flex;
    align-items: center;
    padding: 10px 20px 10px 50px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
    white-space: nowrap;
}

.submenu li a:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.05);
}

.submenu li a i {
    margin-right: 10px;
    font-size: 12px;
    width: 16px;
    text-align: center;
}

.submenu li.active a {
    color: #4fc3f7;
    background: rgba(79, 195, 247, 0.1);
}

.sidebar-footer {
    position: fixed;
    bottom: 0;
    width: 20.6%;
    padding: 7px;
    background: rgb(0 0 0 / 97%);
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.user-profile {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    font-size: 18px;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.username {
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.role {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.logout-btn {
    display: flex;
    align-items: center;
    padding: 8px 15px;
    background: rgb(0 159 255 / 54%);
    color: rgba(255, 255, 255, 0.8);
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.3s;
    width: 100%;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}

.logout-btn i {
    margin-right: 8px;
}

/* Collapsed state */
.sidebar-modern.collapsed {
    width: 80px;
}

.sidebar-modern.collapsed .sidebar-header h3,
.sidebar-modern.collapsed .menu-text,
.sidebar-modern.collapsed .submenu-arrow,
.sidebar-modern.collapsed .user-info,
.sidebar-modern.collapsed .logout-btn span {
    display: none;
}

.sidebar-modern.collapsed .sidebar-toggle .toggle-icon {
    transform: rotate(180deg);
}

.sidebar-modern.collapsed .menu-link {
    justify-content: center;
    padding: 15px 0;
}

.sidebar-modern.collapsed .menu-icon {
    margin-right: 0;
    font-size: 18px;
}

.sidebar-modern.collapsed .sidebar-footer {
    padding: 15px 5px;
}

.sidebar-modern.collapsed .logout-btn {
    justify-content: center;
    padding: 10px 0;
    width: 100%;
}

.sidebar-modern.collapsed .logout-btn i {
    margin-right: 0;
    font-size: 18px;
}

/* Scrollbar styling */
.sidebar-modern::-webkit-scrollbar {
    width: 6px;
}

.sidebar-modern::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.sidebar-modern::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.sidebar-modern::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .sidebar-modern {
        transform: translateX(-100%);
        z-index: 1050;
    }
    
    .sidebar-modern.show-mobile {
        transform: translateX(0);
    }
}
</style>


<!-- Add this JavaScript -->
<script>
// Handle menu clicks to prevent unnecessary reloads
function handleMenuClick(page) {
    // Get current page from URL
    const currentUrl = new URL(window.location.href);
    const currentPage = currentUrl.searchParams.get('page') || 'dashboard';
    
    // Only navigate if it's a different page
    if (currentPage !== page) {
        return true; // Allow navigation
    }
    
    // Prevent reload if already on the same page
    return false;
}

// Handle submenu clicks
function handleSubmenuClick(event, submenuId) {
    // Prevent default if submenu is already open
    const submenu = document.getElementById(submenuId);
    if (submenu.classList.contains('show')) {
        event.preventDefault();
        return false;
    }
    return true;
}

// Initialize all collapses properly
$(document).ready(function() {
    // Initialize all collapses
    $('.submenu').each(function() {
        var collapse = new bootstrap.Collapse(this, {
            toggle: false
        });
        
        // Open if parent is active
        if ($(this).parent('.menu-group').hasClass('active')) {
            collapse.show();
            $(this).prev('.has-submenu').removeClass('collapsed');
            $(this).prev('.has-submenu').attr('aria-expanded', 'true');
        }
    });
    
    // Handle submenu toggle clicks
    $('.has-submenu').on('click', function(e) {
        // Close other open submenus first
        if (!$(this).hasClass('collapsed')) {
            $('.submenu.show').not($(this).attr('href')).collapse('hide');
        }
    });
    
    // Rotate arrow when submenu is shown/hidden
    $('.submenu').on('show.bs.collapse', function() {
        $(this).prev('.has-submenu').removeClass('collapsed');
        $(this).prev('.has-submenu').attr('aria-expanded', 'true');
        $(this).prev('.has-submenu').find('.submenu-arrow').css('transform', 'rotate(90deg)');
    });
    
    $('.submenu').on('hide.bs.collapse', function() {
        $(this).prev('.has-submenu').addClass('collapsed');
        $(this).prev('.has-submenu').attr('aria-expanded', 'false');
        $(this).prev('.has-submenu').find('.submenu-arrow').css('transform', 'rotate(0deg)');
    });
    
    // Toggle sidebar collapse
    $('.sidebar-toggle').click(function() {
        $('.sidebar-modern').toggleClass('collapsed');
        $('.toggle-icon').toggleClass('fa-chevron-left fa-chevron-right');
        
        // Store state in localStorage
        localStorage.setItem('sidebarCollapsed', $('.sidebar-modern').hasClass('collapsed'));
    });
    
    // Check for saved state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        $('.sidebar-modern').addClass('collapsed');
        $('.toggle-icon').removeClass('fa-chevron-left').addClass('fa-chevron-right');
    }
});
</script>

<!-- Keep your existing CSS (it's fine as is) -->
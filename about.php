<?php
// about_us.php - Interactive Aviation System About Us Page
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';
require_once 'includes/header.php';

// Fetch data from database if available
try {
    $stats = $db->query("SELECT * FROM company_stats LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $teamMembers = $db->query("SELECT * FROM team_members WHERE active = 1 ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
    $testimonials = $db->query("SELECT * FROM testimonials WHERE approved = 1 ORDER BY RAND() LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback data if DB fails
    $stats = [
        'founded_year' => '2020',
        'headquarters' => 'New York',
        'team_countries' => '10',
        'airline_partners' => '25',
        'airport_partners' => '120',
        'annual_passengers' => '50000000',
        'delay_reduction' => '15'
    ];
    
    $teamMembers = [
        ["name" => "John Smith", "position" => "CEO", "photo" => "assets/img/team/john-smith.jpg", "bio" => "20+ years in aviation technology", "linkedin" => "#"],
        ["name" => "Sarah Johnson", "position" => "CTO", "photo" => "assets/img/team/sarah-johnson.jpg", "bio" => "Former aerospace engineer turned tech innovator", "linkedin" => "#"],
        ["name" => "Michael Chen", "position" => "Head of Aviation Ops", "photo" => "assets/img/team/michael-chen.jpg", "bio" => "Airline operations specialist", "linkedin" => "#"],
        ["name" => "Emily Rodriguez", "position" => "Product Director", "photo" => "assets/img/team/emily-rodriguez.jpg", "bio" => "UX expert focused on passenger experience", "linkedin" => "#"]
    ];
    
    $testimonials = [
        ["name" => "David Wilson", "title" => "COO, Skyline Airlines", "content" => "Aviation System reduced our operational delays by 22% in the first quarter.", "avatar" => "assets/img/testimonials/david-wilson.jpg"],
        ["name" => "Lisa Tanaka", "title" => "Director, Pacific Airports", "content" => "The most comprehensive aviation management platform we've used.", "avatar" => "assets/img/testimonials/lisa-tanaka.jpg"],
        ["name" => "Robert Zhang", "title" => "Tech Manager, Global Airways", "content" => "Implementation was seamless and the ROI was immediate.", "avatar" => "assets/img/testimonials/robert-zhang.jpg"]
    ];
}

// Set page metadata
$pageTitle = "About Aviation System | Modern Aviation Solutions";
$pageDescription = "Learn about Aviation System - revolutionizing air travel management through cutting-edge technology solutions.";
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.11.4/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.11.4/dist/ScrollTrigger.min.js"></script>
<script src="assets/js/about.js" defer></script>
<link rel="stylesheet" href="assets/css/about.css">

<!-- Main Content -->
<main class="about-page">
    <!-- Hero Section with Parallax -->
    <section class="hero-section pt-5 parallax" data-parallax="scroll" data-image-src="assets/img/aviation-hero.jpg">
        <div class="container">
            <h1 class="hero-title animate__animated animate__fadeInDown">Redefining Aviation Technology</h1>
            <p class="hero-subtitle animate__animated animate__fadeInUp">Innovative solutions for the modern aviation industry</p>
            <button id="playVideoBtn" class="video-play-button" data-video-id="aviationIntroVideo">
                <i class="fas fa-play"></i> Watch Our Story
            </button>
            
            <!-- Interactive Scrolling Prompt -->
            <div class="scroll-prompt">
                <div class="mouse">
                    <div class="wheel"></div>
                </div>
                <div class="prompt-text">Scroll to explore</div>
            </div>
        </div>
    </section>

    <!-- Video Modal -->
    <div id="videoModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Our Aviation System Story</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ratio ratio-16x9">
                        <iframe id="videoFrame" src="" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- About Content -->
    <section class="about-content">
        <div class="container">
            <!-- Vision Section -->
            <div class="section-block animate-on-scroll">
                <h2 class="section-title">Our Vision</h2>
                <p class="section-text">At Aviation System, we're revolutionizing air travel management through cutting-edge technology solutions. Our platform integrates the latest advancements in aviation software to create seamless operations for airlines, airports, and passengers alike.</p>
                
                <!-- Interactive Vision Timeline -->
                <div class="vision-timeline">
                    <div class="timeline-progress"></div>
                    <div class="timeline-items">
                        <div class="timeline-item" data-year="2020">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h4>Company Founded</h4>
                                <p>Established with a mission to transform aviation technology</p>
                            </div>
                        </div>
                        <div class="timeline-item" data-year="2021">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h4>First Major Client</h4>
                                <p>Partnered with Skyline Airlines for flight management</p>
                            </div>
                        </div>
                        <div class="timeline-item" data-year="2022">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h4>AI Integration</h4>
                                <p>Launched our AI-powered scheduling system</p>
                            </div>
                        </div>
                        <div class="timeline-item" data-year="2023">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h4>Global Expansion</h4>
                                <p>Opened offices in 3 new countries</p>
                            </div>
                        </div>
                        <div class="timeline-item" data-year="2024">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h4>Passenger Experience Suite</h4>
                                <p>Released comprehensive passenger management tools</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Who We Are -->
            <div class="section-block animate-on-scroll">
                <h2 class="section-title">Who We Are</h2>
                <p class="section-text">Founded in <?= htmlspecialchars($stats['founded_year'] ?? '2020') ?>, Aviation System is a team of aviation experts, software engineers, and data scientists dedicated to modernizing the aviation industry. With headquarters in <?= htmlspecialchars($stats['headquarters'] ?? 'New York') ?> and team members across <?= htmlspecialchars($stats['team_countries'] ?? '10') ?> countries, we bring global perspective to local aviation challenges.</p>
                
                <div class="stats-grid">
                    <div class="stat-item" data-count="<?= htmlspecialchars($stats['airline_partners'] ?? 25) ?>">
                        <div class="stat-number">0+</div>
                        <div class="stat-label">Airlines</div>
                        <div class="stat-icon"><i class="fas fa-plane"></i></div>
                    </div>
                    <div class="stat-item" data-count="<?= htmlspecialchars($stats['airport_partners'] ?? 120) ?>">
                        <div class="stat-number">0+</div>
                        <div class="stat-label">Airports</div>
                        <div class="stat-icon"><i class="fas fa-building"></i></div>
                    </div>
                    <div class="stat-item" data-count="<?= htmlspecialchars(($stats['annual_passengers'] ?? 50000000)/1000000) ?>">
                        <div class="stat-number">0.0M</div>
                        <div class="stat-label">Passengers Annually</div>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="stat-item" data-count="<?= htmlspecialchars($stats['delay_reduction'] ?? 15) ?>">
                        <div class="stat-number">0%</div>
                        <div class="stat-label">Reduced Delays</div>
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>

                <!-- Interactive World Map -->
                <div class="world-map-container">
                    <h3>Our Global Presence</h3>
                    <div class="world-map">
                        <img src="assets/images/world.svg" alt="World Map" usemap="#worldMap">
                        <map name="worldMap">
                            <area target="" alt="North America" title="North America" href="#" coords="90,90,140,150" shape="rect" data-region="north-america">
                            <area target="" alt="Europe" title="Europe" href="#" coords="280,80,330,140" shape="rect" data-region="europe">
                            <area target="" alt="Asia" title="Asia" href="#" coords="420,100,470,160" shape="rect" data-region="asia">
                            <area target="" alt="Middle East" title="Middle East" href="#" coords="350,150,400,200" shape="rect" data-region="middle-east">
                        </map>
                        <div class="map-markers">
                            <div class="map-marker" style="top: 25%; left: 20%;" data-city="New York"></div>
                            <div class="map-marker" style="top: 30%; left: 50%;" data-city="London"></div>
                            <div class="map-marker" style="top: 40%; left: 75%;" data-city="Singapore"></div>
                            <div class="map-marker" style="top: 60%; left: 60%;" data-city="Dubai"></div>
                        </div>
                    </div>
                    <div class="map-info" id="mapInfo">
                        <h4>Hover over map to see our offices</h4>
                        <p>Click on a location to learn more about our regional operations</p>
                    </div>
                </div>
            </div>

            <!-- Technology Section -->
            <div class="section-block animate-on-scroll">
                <h2 class="section-title">Our Technology</h2>
                <p class="section-text">Our system leverages the latest advancements in aviation technology:</p>
                
                <div class="tech-grid">
                    <?php
                    $technologies = [
                        ["icon" => "ai", "title" => "AI-powered flight scheduling", "desc" => "Machine learning algorithms optimize routes and schedules", "detail" => "Our AI analyzes weather patterns, air traffic, and historical data to predict optimal routes, reducing fuel consumption by up to 12%."],
                        ["icon" => "analytics", "title" => "Real-time data analytics", "desc" => "Instant insights for operational decisions", "detail" => "Processes 2.5 million data points per second to provide actionable insights to ground crews and flight operations."],
                        ["icon" => "cloud", "title" => "Cloud infrastructure", "desc" => "Global, reliable, and scalable platform", "detail" => "Distributed across 8 global regions with 99.99% uptime and automatic failover capabilities."],
                        ["icon" => "blockchain", "title" => "Blockchain security", "desc" => "Tamper-proof transaction records", "detail" => "Immutable ledger for all flight transactions, maintenance records, and passenger data exchanges."],
                        ["icon" => "iot", "title" => "IoT integration", "desc" => "Smart airport ecosystem connectivity", "detail" => "Connects with 150+ IoT device types from baggage trackers to gate sensors for complete situational awareness."],
                        ["icon" => "biometrics", "title" => "Biometric systems", "desc" => "Facial recognition for seamless travel", "detail" => "Reduces boarding times by 40% while maintaining strict security protocols and passenger privacy."]
                    ];
                    
                    foreach ($technologies as $tech) {
                        echo '<div class="tech-item" tabindex="0" role="button" data-tech="'.htmlspecialchars(strtolower(str_replace(' ', '-', $tech['title'])) ). '">';
                        echo '<div class="tech-icon">';
                        echo '<img src="assets/img/icons/'.$tech['icon'].'.svg" alt="'.htmlspecialchars($tech['title']).'">';
                        echo '</div>';
                        echo '<h3>'.htmlspecialchars($tech['title']).'</h3>';
                        echo '<div class="tech-details">'.htmlspecialchars($tech['desc']).'</div>';
                        echo '<div class="tech-expanded">';
                        echo '<div class="tech-close"><i class="fas fa-times"></i></div>';
                        echo '<h4>'.htmlspecialchars($tech['title']).'</h4>';
                        echo '<p>'.htmlspecialchars($tech['detail']).'</p>';
                        echo '<div class="tech-stats">';
                        echo '<div class="tech-stat"><span class="stat-value">99.9%</span> <span class="stat-label">Reliability</span></div>';
                        echo '<div class="tech-stat"><span class="stat-value">24/7</span> <span class="stat-label">Monitoring</span></div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <!-- Technology Demo Video -->
                <div class="tech-demo">
                    <div class="demo-description">
                        <h3>See Our Technology in Action</h3>
                        <p>Watch a 90-second demo of how our platform transforms aviation operations</p>
                        <button class="demo-button" data-video-id="techDemoVideo">
                            <i class="fas fa-play"></i> Play Demo
                        </button>
                    </div>
                    <div class="demo-visual">
                        <div class="demo-placeholder" id="techDemoPlaceholder">
                            <img src="assets/images/tech-demo-preview.jpg" alt="Technology Demo Preview">
                            <div class="play-overlay"><i class="fas fa-play"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="section-block features-section">
                <h2 class="section-title">Key Features</h2>
                <div class="features-tabs">
                    <div class="tab-buttons">
                        <?php
                        $features = [
                            "flight" => ["Flight Management", "fas fa-plane-departure"],
                            "passenger" => ["Passenger Experience", "fas fa-user-tie"],
                            "airport" => ["Airport Operations", "fas fa-building"],
                            "data" => ["Data Intelligence", "fas fa-chart-line"]
                        ];
                        
                        $first = true;
                        foreach ($features as $id => $feature) {
                            echo '<button class="tab-button '.($first ? 'active' : '').'" data-tab="'.$id.'">';
                            echo '<i class="'.$feature[1].'"></i> ';
                            echo htmlspecialchars($feature[0]);
                            echo '</button>';
                            $first = false;
                        }
                        ?>
                    </div>
                    
                    <div class="tab-contents">
                        <div class="tab-content flight active">
                            <div class="feature-content">
                                <div class="feature-text">
                                    <h3>Comprehensive Flight Management</h3>
                                    <p>Advanced tools for route planning, crew scheduling, and aircraft maintenance with predictive analytics.</p>
                                    <ul class="feature-list">
                                        <li><i class="fas fa-check-circle"></i> AI-optimized flight scheduling</li>
                                        <li><i class="fas fa-check-circle"></i> Real-time weather integration</li>
                                        <li><i class="fas fa-check-circle"></i> Crew management and compliance</li>
                                        <li><i class="fas fa-check-circle"></i> Predictive maintenance alerts</li>
                                    </ul>
                                    <button class="feature-cta" data-feature="flight">Request Flight Demo</button>
                                </div>
                                <div class="feature-image">
                                    <img src="assets/img/features/flight-system.jpg" alt="Flight Management System" class="feature-image">
                                    <div class="image-hotspots">
                                        <div class="hotspot" style="top: 30%; left: 40%;" data-info="Route Optimization"></div>
                                        <div class="hotspot" style="top: 60%; left: 70%;" data-info="Crew Scheduling"></div>
                                        <div class="hotspot" style="top: 45%; left: 20%;" data-info="Aircraft Status"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-content passenger">
                            <div class="feature-content">
                                <div class="feature-text">
                                    <h3>Seamless Passenger Experience</h3>
                                    <p>Streamlined booking, check-in, and in-flight services with personalized recommendations.</p>
                                    <ul class="feature-list">
                                        <li><i class="fas fa-check-circle"></i> Biometric boarding</li>
                                        <li><i class="fas fa-check-circle"></i> Personalized travel recommendations</li>
                                        <li><i class="fas fa-check-circle"></i> Real-time baggage tracking</li>
                                        <li><i class="fas fa-check-circle"></i> Multi-channel communication</li>
                                    </ul>
                                    <button class="feature-cta" data-feature="passenger">Request Passenger Demo</button>
                                </div>
                                <div class="feature-image">
                                    <img src="assets/img/features/passenger-app.jpg" alt="Passenger Experience Platform" class="feature-image">
                                </div>
                            </div>
                        </div>
                        <div class="tab-content airport">
                            <div class="feature-content">
                                <div class="feature-text">
                                    <h3>Efficient Airport Operations</h3>
                                    <p>Resource management, baggage handling, and security coordination in one unified platform.</p>
                                    <ul class="feature-list">
                                        <li><i class="fas fa-check-circle"></i> Gate assignment optimization</li>
                                        <li><i class="fas fa-check-circle"></i> Baggage handling automation</li>
                                        <li><i class="fas fa-check-circle"></i> Security queue management</li>
                                        <li><i class="fas fa-check-circle"></i> Vendor coordination</li>
                                    </ul>
                                    <button class="feature-cta" data-feature="airport">Request Airport Demo</button>
                                </div>
                                <div class="feature-image">
                                    <img src="assets/img/features/airport-ops.jpg" alt="Airport Operations Suite" class="feature-image">
                                </div>
                            </div>
                        </div>
                        <div class="tab-content data">
                            <div class="feature-content">
                                <div class="feature-text">
                                    <h3>Powerful Data Intelligence</h3>
                                    <p>Predictive analytics and customizable dashboards for improved efficiency and cost savings.</p>
                                    <ul class="feature-list">
                                        <li><i class="fas fa-check-circle"></i> Customizable KPI dashboards</li>
                                        <li><i class="fas fa-check-circle"></i> Predictive delay analytics</li>
                                        <li><i class="fas fa-check-circle"></i> Revenue optimization</li>
                                        <li><i class="fas fa-check-circle"></i> Automated reporting</li>
                                    </ul>
                                    <button class="feature-cta" data-feature="data">Request Data Demo</button>
                                </div>
                                <div class="feature-image">
                                    <img src="assets/img/features/data-dashboard.jpg" alt="Data Intelligence Dashboard" class="feature-image">
                                    <div class="dashboard-controls">
                                        <button class="dashboard-btn" data-view="operations">Operations</button>
                                        <button class="dashboard-btn" data-view="financial">Financial</button>
                                        <button class="dashboard-btn" data-view="passenger">Passenger</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Section -->
            <div class="section-block team-section">
                <h2 class="section-title">Our Leadership</h2>
                <div class="team-filters">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="executive">Executive</button>
                    <button class="filter-btn" data-filter="technology">Technology</button>
                    <button class="filter-btn" data-filter="operations">Operations</button>
                </div>
                <div class="team-slider">
                    <?php foreach ($teamMembers as $member): ?>
                        <div class="team-slide" data-department="<?= strtolower(explode(' ', $member['position'])[0]) ?>">
                            <div class="team-card">
                                <div class="team-image">
                                    <img src="<?= htmlspecialchars($member['photo']) ?>" alt="<?= htmlspecialchars($member['name']) ?>">
                                    <div class="team-social">
                                        <a href="<?= htmlspecialchars($member['linkedin'] ?? '#') ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                                        <a href="#"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                                <div class="team-info">
                                    <h3><?= htmlspecialchars($member['name']) ?></h3>
                                    <p class="position"><?= htmlspecialchars($member['position']) ?></p>
                                    <p class="bio"><?= htmlspecialchars($member['bio'] ?? '') ?></p>
                                    <button class="btn-profile" data-member="<?= htmlspecialchars($member['name']) ?>">
                                        View Full Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Team Member Modal -->
                <div id="teamModal" class="modal fade" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="teamMemberName">Team Member</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="teamMemberContent">
                                <!-- Content loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonials Section -->
            <div class="section-block testimonials-section">
                <h2 class="section-title">What Our Partners Say</h2>
                <div class="testimonial-slider">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="testimonial-slide">
                            <div class="testimonial-card">
                                <div class="testimonial-avatar">
                                    <img src="<?= htmlspecialchars($testimonial['avatar']) ?>" alt="<?= htmlspecialchars($testimonial['name']) ?>">
                                </div>
                                <div class="testimonial-content">
                                    <div class="testimonial-text">"<?= htmlspecialchars($testimonial['content']) ?>"</div>
                                    <div class="testimonial-author">
                                        <h4><?= htmlspecialchars($testimonial['name']) ?></h4>
                                        <p><?= htmlspecialchars($testimonial['title']) ?></p>
                                    </div>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Video Testimonials -->
                <div class="video-testimonials">
                    <h3>Video Testimonials</h3>
                    <div class="video-grid">
                        <div class="video-thumbnail" data-video-id="testimonial1">
                            <img src="assets/images/video-thumbnail1.jpg" alt="Client Testimonial">
                            <div class="play-button"><i class="fas fa-play"></i></div>
                            <div class="video-title">Skyline Airlines</div>
                        </div>
                        <div class="video-thumbnail" data-video-id="testimonial2">
                            <img src="assets/images/video-thumbnail2.jpg" alt="Client Testimonial">
                            <div class="play-button"><i class="fas fa-play"></i></div>
                            <div class="video-title">Pacific Airports</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Logos -->
            <div class="clients-section">
                <h3>Trusted By Leading Aviation Companies</h3>
                <div class="client-logos">
                    <img src="assets/images/clients/airline1.png" alt="Airline Partner" loading="lazy" data-client="skyline-airlines">
                    <img src="assets/images/clients/airline2.png" alt="Airline Partner" loading="lazy" data-client="global-airways">
                    <img src="assets/images/clients/airport1.png" alt="Airport Partner" loading="lazy" data-client="pacific-airports">
                    <img src="assets/images/clients/airport2.png" alt="Airport Partner" loading="lazy" data-client="metro-airport-group">
                    <img src="assets/images/clients/tech-partner1.png" alt="Technology Partner" loading="lazy" data-client="aviation-tech-solutions">
                </div>
                
                <!-- Client Case Studies -->
                <div class="case-studies">
                    <h4>Featured Case Studies</h4>
                    <div class="case-study-tabs">
                        <button class="case-tab active" data-client="skyline-airlines">Skyline Airlines</button>
                        <button class="case-tab" data-client="pacific-airports">Pacific Airports</button>
                        <button class="case-tab" data-client="global-airways">Global Airways</button>
                    </div>
                    <div class="case-content active" id="skyline-airlines">
                        <h5>Reduced Delays by 22%</h5>
                        <p>Skyline Airlines implemented our flight management system across their 150-aircraft fleet, resulting in significant operational improvements.</p>
                        <a href="#" class="read-case">Read Full Case Study <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="case-content" id="pacific-airports">
                        <h5>Improved Passenger Flow by 35%</h5>
                        <p>Pacific Airports used our passenger management tools to streamline security and boarding processes at their 3 major hubs.</p>
                        <a href="#" class="read-case">Read Full Case Study <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="case-content" id="global-airways">
                        <h5>$12M Annual Cost Savings</h5>
                        <p>Global Airways leveraged our data intelligence platform to optimize fuel usage and crew scheduling.</p>
                        <a href="#" class="read-case">Read Full Case Study <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Interactive CTA Section -->
            <div class="cta-section interactive-cta">
                <div class="cta-content">
                    <h2>Ready to Transform Your Aviation Operations?</h2>
                    <p>Schedule a personalized demo to see our system in action</p>
                    
                    <form id="demoRequest" class="cta-form">
                        <div class="form-group">
                            <label for="demoName">Your Name</label>
                            <input type="text" id="demoName" placeholder="John Smith" required>
                        </div>
                        <div class="form-group">
                            <label for="demoEmail">Email Address</label>
                            <input type="email" id="demoEmail" placeholder="john@company.com" required>
                        </div>
                        <div class="form-group">
                            <label for="demoCompany">Company</label>
                            <input type="text" id="demoCompany" placeholder="Your Company" required>
                        </div>
                        <div class="form-group">
                            <label for="demoInterest">I'm interested in...</label>
                            <select id="demoInterest" required>
                                <option value="">Select an option</option>
                                <option>Flight Management</option>
                                <option>Passenger Solutions</option>
                                <option>Airport Operations</option>
                                <option>Data Analytics</option>
                                <option>All Solutions</option>
                            </select>
                        </div>
                        <button type="submit" class="cta-button">
                            <span class="button-text">Request Demo</span>
                            <span class="button-loading">
                                <i class="fas fa-spinner fa-spin"></i> Processing...
                            </span>
                        </button>
                    </form>
                    
                    <div class="success-message" style="display:none;">
                        <i class="fas fa-check-circle"></i>
                        <h3>Thank You!</h3>
                        <p>Our team will contact you shortly to schedule your demo.</p>
                        <div class="confirmation-details">
                            <p><strong>Confirmation will be sent to:</strong> <span id="confirmEmail"></span></p>
                            <p>We'll follow up within 24 hours to confirm your demo time.</p>
                        </div>
                        <button class="cta-button" id="scheduleAnother">Schedule Another Demo</button>
                    </div>
                </div>
                <div class="cta-image">
                    <img src="assets/images/cta-demo.jpg" alt="Aviation System Demo" loading="lazy">
                    <div class="demo-highlights">
                        <div class="highlight" style="top: 20%; left: 30%;" data-highlight="Flight Management">
                            <div class="highlight-dot"></div>
                            <div class="highlight-tooltip">Flight Management</div>
                        </div>
                        <div class="highlight" style="top: 50%; left: 60%;" data-highlight="Passenger Flow">
                            <div class="highlight-dot"></div>
                            <div class="highlight-tooltip">Passenger Flow</div>
                        </div>
                        <div class="highlight" style="top: 70%; left: 20%;" data-highlight="Data Analytics">
                            <div class="highlight-dot"></div>
                            <div class="highlight-tooltip">Data Analytics</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

<script>
// AJAX functionality for the About Us page
document.addEventListener('DOMContentLoaded', function() {
    // Video Modal Handling
    const videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
    const videoFrame = document.getElementById('videoFrame');
    
    document.querySelectorAll('[data-video-id]').forEach(button => {
        button.addEventListener('click', function() {
            const videoId = this.getAttribute('data-video-id');
            let videoUrl = '';
            
            // Determine video source based on ID
            switch(videoId) {
                case 'aviationIntroVideo':
                    videoUrl = 'https://www.youtube.com/embed/example1?autoplay=1';
                    break;
                case 'techDemoVideo':
                    videoUrl = 'https://www.youtube.com/embed/example2?autoplay=1';
                    break;
                case 'testimonial1':
                    videoUrl = 'https://www.youtube.com/embed/example3?autoplay=1';
                    break;
                case 'testimonial2':
                    videoUrl = 'https://www.youtube.com/embed/example4?autoplay=1';
                    break;
            }
            
            videoFrame.src = videoUrl;
            videoModal.show();
        });
    });
    
    // Close modal when hidden to stop video playback
    videoModal._element.addEventListener('hidden.bs.modal', function() {
        videoFrame.src = '';
    });

    // Team Member Modal AJAX
    const teamModal = new bootstrap.Modal(document.getElementById('teamModal'));
    const teamMemberContent = document.getElementById('teamMemberContent');
    const teamMemberName = document.getElementById('teamMemberName');
    
    document.querySelectorAll('.btn-profile').forEach(button => {
        button.addEventListener('click', function() {
            const memberName = this.getAttribute('data-member');
            teamMemberName.textContent = memberName;
            
            // Show loading state
            teamMemberContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            // Fetch team member details via AJAX
            fetch('ajax/get_team_member.php?name=' + encodeURIComponent(memberName))
                .then(response => response.text())
                .then(data => {
                    teamMemberContent.innerHTML = data;
                    teamModal.show();
                })
                .catch(error => {
                    teamMemberContent.innerHTML = '<div class="alert alert-danger">Failed to load team member details. Please try again.</div>';
                    teamModal.show();
                });
        });
    });

    // Demo Request Form AJAX
    const demoForm = document.getElementById('demoRequest');
    const successMessage = document.querySelector('.success-message');
    
    if (demoForm) {
        demoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = demoForm.querySelector('button[type="submit"]');
            submitBtn.querySelector('.button-text').style.display = 'none';
            submitBtn.querySelector('.button-loading').style.display = 'inline-block';
            
            // Prepare form data
            const formData = new FormData(demoForm);
            
            // Simulate AJAX submission (replace with actual fetch)
            setTimeout(() => {
                // Hide form and show success message
                demoForm.style.display = 'none';
                successMessage.style.display = 'block';
                document.getElementById('confirmEmail').textContent = formData.get('demoEmail');
                
                // Reset form
                submitBtn.querySelector('.button-text').style.display = 'inline-block';
                submitBtn.querySelector('.button-loading').style.display = 'none';
                demoForm.reset();
            }, 1500);
        });
    }
    
    // Schedule Another Demo button
    document.getElementById('scheduleAnother')?.addEventListener('click', function() {
        successMessage.style.display = 'none';
        demoForm.style.display = 'block';
    });

    // Animate stats counters
    function animateStats() {
        document.querySelectorAll('.stat-item').forEach(stat => {
            const target = parseInt(stat.getAttribute('data-count'));
            const duration = 2000; // 2 seconds
            const start = 0;
            const increment = target / (duration / 16); // 60fps
            
            let current = start;
            const statNumber = stat.querySelector('.stat-number');
            const originalText = statNumber.textContent;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    clearInterval(timer);
                    current = target;
                }
                
                // Format number based on original text
                if (originalText.includes('M')) {
                    statNumber.textContent = current.toFixed(1) + 'M';
                } else if (originalText.includes('%')) {
                    statNumber.textContent = Math.round(current) + '%';
                } else {
                    statNumber.textContent = Math.round(current) + '+';
                }
            }, 16);
        });
    }
    
    // Intersection Observer for animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                
                // If it's the stats section, animate counters
                if (entry.target.querySelector('.stat-item')) {
                    animateStats();
                }
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.animate-on-scroll').forEach(section => {
        observer.observe(section);
    });

    // Tab functionality for features
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update active tab button
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update active tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelector(`.tab-content.${tabId}`).classList.add('active');
        });
    });

    // Team filter functionality
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Filter team members
            document.querySelectorAll('.team-slide').forEach(member => {
                if (filter === 'all' || member.getAttribute('data-department') === filter) {
                    member.style.display = 'block';
                } else {
                    member.style.display = 'none';
                }
            });
        });
    });

    // Case study tabs
    document.querySelectorAll('.case-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const clientId = this.getAttribute('data-client');
            
            // Update active tab
            document.querySelectorAll('.case-tab').forEach(t => {
                t.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update active content
            document.querySelectorAll('.case-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(clientId).classList.add('active');
        });
    });

    // Tech item expansion
    document.querySelectorAll('.tech-item').forEach(item => {
        item.addEventListener('click', function() {
            this.classList.toggle('expanded');
        });
        
        // Close button
        const closeBtn = this.querySelector('.tech-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                this.closest('.tech-item').classList.remove('expanded');
            });
        }
    });

    // World map interaction
    document.querySelectorAll('area').forEach(area => {
        area.addEventListener('mouseover', function() {
            const region = this.getAttribute('data-region');
            const mapInfo = document.getElementById('mapInfo');
            
            // Update map info based on region
            switch(region) {
                case 'north-america':
                    mapInfo.innerHTML = '<h4>North America Headquarters</h4><p>Our New York office serves as our global headquarters and North American operations center.</p>';
                    break;
                case 'europe':
                    mapInfo.innerHTML = '<h4>European Operations</h4><p>Our London office manages partnerships with European airlines and airports.</p>';
                    break;
                case 'asia':
                    mapInfo.innerHTML = '<h4>Asia-Pacific Hub</h4><p>Singapore serves as our regional hub for Asia with additional offices in Tokyo and Sydney.</p>';
                    break;
                case 'middle-east':
                    mapInfo.innerHTML = '<h4>Middle East Presence</h4><p>Our Dubai office coordinates with regional partners and handles implementations.</p>';
                    break;
            }
        });
        
        area.addEventListener('click', function(e) {
            e.preventDefault();
            const region = this.getAttribute('data-region');
            // You could implement a more detailed modal here
            alert(`Showing more details about our ${this.getAttribute('title')} operations`);
        });
    });

    // Hotspot interaction for feature images
    document.querySelectorAll('.hotspot').forEach(hotspot => {
        hotspot.addEventListener('mouseover', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'hotspot-tooltip';
            tooltip.textContent = this.getAttribute('data-info');
            this.appendChild(tooltip);
        });
        
        hotspot.addEventListener('mouseout', function() {
            const tooltip = this.querySelector('.hotspot-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
});
</script>
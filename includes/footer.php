<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet" href="assets/css/footer.css">
<!-- Mobile App CTA -->
    <section class="section app-cta">
        <div class="container">
            <div class="app-content">
                <div class="app-text">
                    <h2>Travel Smarter with Our <span>Mobile App</span></h2>
                    <p>Get real-time flight updates, exclusive mobile-only deals, and manage your trips on the go</p>
                    <!-- <div class="app-stats">
                        <div class="stat-item">
                            <div class="stat-number">4.8</div>
                            <div class="stat-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <div class="stat-label">App Store Rating</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">1M+</div>
                            <div class="stat-label">Downloads</div>
                        </div>
                    </div> -->
                    <div class="app-buttons">
                        <a href="#" class="app-btn app-store-btn">
                            <div class="btn-icon">
                                <i class="fab fa-apple"></i>
                            </div>
                            <div class="btn-text">
                                <span>Download on the</span>
                                <strong>App Store</strong>
                            </div>
                        </a>
                        <a href="#" class="app-btn play-store-btn">
                            <div class="btn-icon">
                                <i class="fab fa-google-play"></i>
                            </div>
                            <div class="btn-text">
                                <span>Get it on</span>
                                <strong>Google Play</strong>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="app-image">
                    <img src="assets/images/app-mockup.png" alt="Travel app screens" class="img-fluid">
                    <div class="app-highlight">
                        <div class="highlight-circle"></div>
                        <div class="highlight-text">New Feature!</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!-- Footer -->

<footer class="aviation-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                        <path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" />
                    </svg>
                    <span>AviationSystem</span>
                </div>
                <p class="footer-tagline">Elevating air travel experiences through innovation and excellence.</p>
                <div class="footer-newsletter">
                    <h4>Subscribe to our newsletter</h4>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit" class="btn-subscribe">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-links">
                <h3 class="footer-title">Quick Links</h3>
                <ul>
                    <li><a href="flights.php">Book a Flight</a></li>
                    <li><a href="manage.php">Manage Booking</a></li>
                    <li><a href="checkin.php">Online Check-in</a></li>
                    <li><a href="status.php">Flight Status</a></li>
                    <li><a href="destinations.php">Destinations</a></li>
                </ul>
            </div>
            
            <!-- Company -->
            <div class="footer-links">
                <h3 class="footer-title">Company</h3>
                <ul>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="careers.php">Careers</a></li>
                    <li><a href="press.php">Press Center</a></li>
                    <li><a href="sustainability.php">Sustainability</a></li>
                    <li><a href="investors.php">Investor Relations</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="footer-links">
                <h3 class="footer-title">Support</h3>
                <ul>
                    <li><a href="help.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="refunds.php">Refunds</a></li>
                    <li><a href="baggage.php">Baggage Policy</a></li>
                    <li><a href="faq.php">FAQs</a></li>
                </ul>
            </div>
            
            <!-- Legal -->
            <div class="footer-links">
                <h3 class="footer-title">Legal</h3>
                <ul>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="cookies.php">Cookie Policy</a></li>
                    <li><a href="conditions.php">Conditions of Carriage</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
            <div>
                <p class="copyright">&copy; <?= date('Y') ?> AviationSystem. All rights reserved.</p>
            </div>   
            <div class="copyright-payment">
                <div class="payment-methods">
                    <img src="assets/images/visa.svg" alt="Visa">
                    <img src="assets/images/mastercard.svg" alt="Mastercard">
                    <img src="assets/images/amex.svg" alt="American Express">
                    <img src="assets/images/paypal.svg" alt="PayPal">
                    <img src="assets/images/shopify.svg" alt="Shopify">
                    <img src="assets/images/bitcoin.svg" alt="Bitcoin">
                    <img src="assets/images/applepay.svg" alt="Apple Pay">
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js@10.0.1/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>

<!-- Custom Script -->
<script>
    // Initialize components
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Initialize date pickers
        const datePickers = [
            '#departure', '#return', '#check-in', '#check-out', 
            '#package-departure'
        ];
        
        datePickers.forEach(selector => {
            flatpickr(selector, {
                minDate: "today",
                dateFormat: "Y-m-d",
                disableMobile: true // better UX on mobile
            });
        });

        // Initialize enhanced select dropdowns
        const enhancedSelects = [
            '#from', '#to', '#destination', 
            '#package-destination', '#package-from'
        ];
        
        enhancedSelects.forEach(selector => {
            new Choices(selector, {
                searchEnabled: true,
                placeholder: true,
                itemSelectText: '',
                searchPlaceholderValue: 'Search...',
                shouldSort: false,
                position: 'auto'
            });
        });

        // Simple select dropdowns
        const simpleSelects = [
            '#passengers', '#class', '#rooms', '#guests', '#hotel-class',
            '#package-duration', '#package-travelers', '#package-type'
        ];
        
        simpleSelects.forEach(selector => {
            new Choices(selector, {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false
            });
        });

        // Password toggle functionality
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        // Form submission handling with SweetAlert
        const handleFormSubmit = (formId, successMessage) => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Simulate form processing
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: successMessage,
                            confirmButtonColor: '#2563eb',
                            timer: 3000
                        });
                        
                        // Reset form
                        form.reset();
                        
                        // Close modal if this is a modal form
                        const modal = form.closest('.modal');
                        if (modal) {
                            bootstrap.Modal.getInstance(modal).hide();
                        }
                    }, 1000);
                });
            }
        };

        // Initialize form handlers
        handleFormSubmit('loginForm', 'You have been successfully logged in!');
        handleFormSubmit('registerForm', 'Registration successful! Welcome aboard.');
        handleFormSubmit('forgotPasswordForm', 'Password reset instructions sent to your email.');
    });
</script>
<?php
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';
include __DIR__ . '/includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$pageTitle = "My Profile | Aviation System";
?>

<!-- Make sure jQuery is loaded in the header -->

<div class="container py-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="avatar-placeholder bg-gradient-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 40px;">
                        <?= strtoupper(substr($_SESSION['first_name'], 0, 1) . strtoupper(substr($_SESSION['last_name'], 0, 1))) ?>
                    </div>
                    <h4 class="fw-bold"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . htmlspecialchars($_SESSION['last_name'])) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($_SESSION['userEmail']) ?></p>
                    
                    <div class="kyc-status mb-3">
                        <?php if ($_SESSION['kyc_verified'] ?? false): ?>
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                <i class="fas fa-check-circle me-1"></i> KYC Verified
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning rounded-pill px-3 py-2">
                                <i class="fas fa-exclamation-circle me-1"></i> KYC Not Verified
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="d-grid gap-2">
                        <a href="my_booking.php" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="fas fa-ticket-alt me-1"></i> My Bookings
                        </a>
                        <button id="changePasswordBtn" class="btn btn-outline-info btn-sm rounded-pill">
                            <i class="fas fa-key me-1"></i> Change Password
                        </button>
                        <?php if (!($_SESSION['kyc_verified'] ?? false)): ?>
                        <button id="kycVerificationBtn" class="btn btn-outline-warning btn-sm rounded-pill">
                            <i class="fas fa-id-card me-1"></i> KYC Verification
                        </button>
                        <?php endif; ?>
                        <a href="account/logout.php" class="btn btn-outline-danger btn-sm rounded-pill">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Profile Information</h5>
                        <button id="editProfileBtn" class="btn btn-sm btn-primary rounded-pill">
                            <i class="fas fa-edit me-1"></i> Edit Profile
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="profileSuccess" class="alert alert-success d-none"></div>
                    <div id="profileErrors" class="alert alert-danger d-none"></div>
                    
                    <form id="profileForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name*</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?= htmlspecialchars($_SESSION['first_name'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name*</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?= htmlspecialchars($_SESSION['last_name'] ?? '') ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['userEmail'] ?? '') ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2" readonly><?= htmlspecialchars($_SESSION['address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Passport Number (Optional)</label>
                            <input type="text" class="form-control" name="passport_number" 
                                   value="<?= htmlspecialchars($_SESSION['passport_number'] ?? '') ?>" readonly>
                        </div>
                        
                        <div class="d-none" id="profileFormActions">
                            <button type="submit" class="btn btn-primary me-2 rounded-pill">Save Changes</button>
                            <button type="button" id="cancelProfileEdit" class="btn btn-outline-secondary rounded-pill">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Section (Hidden by default) -->
            <div class="card shadow-sm border-0 mb-4 d-none" id="changePasswordSection">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Change Password</h5>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill" id="closePasswordSection">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div id="passwordSuccess" class="alert alert-success d-none"></div>
                    <div id="passwordErrors" class="alert alert-danger d-none"></div>
                    
                    <form id="passwordForm">
                        <div class="mb-3">
                            <label class="form-label">Current Password <span style="color: red;">*</span></label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" class="form-control" name="new_password" required>
                            <div class="form-text">Password must be at least 8 characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary rounded-pill">Change Password</button>
                    </form>
                </div>
            </div>
            
            <!-- KYC Verification Section (Hidden by default) -->
            <div class="card shadow-sm border-0 d-none" id="kycVerificationSection">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">KYC Verification</h5>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill" id="closeKycSection">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($_SESSION['kyc_verified'] ?? false): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> Your KYC has been successfully verified.
                        </div>
                    <?php else: ?>
                        <div id="kycSuccess" class="alert alert-success d-none"></div>
                        <div id="kycErrors" class="alert alert-danger d-none"></div>
                        
                        <form id="kycForm">
                            <div class="mb-3">
                                <label class="form-label">Document Type*</label>
                                <select class="form-select" name="document_type" required>
                                    <option value="">Select document type</option>
                                    <option value="passport">Passport</option>
                                    <option value="nid">National ID</option>
                                    <option value="driving_license">Driving License</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Document Number*</label>
                                <input type="text" class="form-control" name="document_number" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Document (Front)*</label>
                                <input type="file" class="form-control" name="document_front" accept="image/*,.pdf" required>
                                <div class="form-text">Upload a clear image or PDF of your document</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Document (Back)</label>
                                <input type="file" class="form-control" name="document_back" accept="image/*,.pdf">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Selfie with Document</label>
                                <input type="file" class="form-control" name="selfie" accept="image/*">
                                <div class="form-text">Take a selfie holding your document (optional but recommended)</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary rounded-pill">Submit for Verification</button>
                        </form>
                        
                        <div id="otpVerificationSection" class="mt-4 d-none">
                            <p class="text-muted">We've sent an OTP to your registered email address. Please enter it below to complete KYC verification.</p>
                            
                            <form id="otpVerificationForm">
                                <div class="mb-3">
                                    <label class="form-label">OTP Code*</label>
                                    <input type="text" class="form-control" name="otp_code" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary me-2 rounded-pill">Verify OTP</button>
                                <button type="button" id="resendOtpBtn" class="btn btn-outline-secondary rounded-pill">Resend OTP</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    console.log("Document ready and jQuery loaded"); // Debugging
    
    // Profile edit toggle - Fixed version
    let isEditing = false;
    const profileForm = $('#profileForm');
    // Explicitly select the fields we want to make editable
    const editableFields = $('input[name="first_name"], input[name="last_name"], input[name="phone"], textarea[name="address"], input[name="passport_number"]');
    const profileFormActions = $('#profileFormActions');
    const editProfileBtn = $('#editProfileBtn');
    
    editProfileBtn.click(function() {
        console.log("Edit button clicked, current mode:", isEditing); // Debugging
        
        if (isEditing) {
            // Already in edit mode, submit the form
            profileForm.submit();
        } else {
            // Switch to edit mode
            isEditing = true;
            $(this).html('<i class="fas fa-save me-1"></i> Save Changes');
            editableFields.prop('readonly', false);
            profileFormActions.removeClass('d-none');
            
            // Debugging
            console.log("Fields set to editable:", editableFields);
        }
    });
    
    $('#cancelProfileEdit').click(function() {
        console.log("Cancel edit clicked"); // Debugging
        isEditing = false;
        editProfileBtn.html('<i class="fas fa-edit me-1"></i> Edit Profile');
        editableFields.prop('readonly', true);
        profileFormActions.addClass('d-none');
    });

    // Toggle Change Password Section
    $('#changePasswordBtn').click(function() {
        $('#changePasswordSection').removeClass('d-none');
        $('#kycVerificationSection').addClass('d-none');
        $('html, body').animate({
            scrollTop: $('#changePasswordSection').offset().top - 20
        }, 300);
    });
    
    $('#closePasswordSection').click(function() {
        $('#changePasswordSection').addClass('d-none');
        $('#passwordForm')[0].reset();
        $('#passwordSuccess, #passwordErrors').addClass('d-none');
    });
    
    // Toggle KYC Verification Section
    $('#kycVerificationBtn').click(function() {
        $('#kycVerificationSection').removeClass('d-none');
        $('#changePasswordSection').addClass('d-none');
        $('html, body').animate({
            scrollTop: $('#kycVerificationSection').offset().top - 20
        }, 300);
    });
    
    $('#closeKycSection').click(function() {
        $('#kycVerificationSection').addClass('d-none');
        $('#kycForm')[0].reset();
        $('#kycSuccess, #kycErrors').addClass('d-none');
    });
    
    // Profile form submission with AJAX
    profileForm.on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/update_profile.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                editProfileBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
            },
            success: function(response) {
                if (response.success) {
                    $('#profileSuccess').removeClass('d-none').text(response.message);
                    $('#profileErrors').addClass('d-none');
                    
                    // Update UI with new values
                    $('input[name="first_name"]').val(response.data.first_name);
                    $('input[name="last_name"]').val(response.data.last_name);
                    $('input[name="phone"]').val(response.data.phone);
                    $('textarea[name="address"]').val(response.data.address);
                    $('input[name="passport_number"]').val(response.data.passport_number);
                    
                    // Exit edit mode
                    isEditing = false;
                    editProfileBtn.html('<i class="fas fa-edit me-1"></i> Edit Profile').prop('disabled', false);
                    editableFields.prop('readonly', true);
                    profileFormActions.addClass('d-none');
                    
                    // Update avatar initials if name changed
                    const initials = response.data.first_name.charAt(0).toUpperCase() + response.data.last_name.charAt(0).toUpperCase();
                    $('.avatar-placeholder').text(initials);
                    
                    // Update displayed name
                    $('h4').text(response.data.first_name + ' ' + response.data.last_name);
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated',
                        text: 'Your profile has been updated successfully!',
                        confirmButtonText: 'OK'
                    });
                } else {
                    $('#profileErrors').removeClass('d-none').html(response.errors.map(error => `<div>${error}</div>`).join(''));
                    editProfileBtn.html('<i class="fas fa-save me-1"></i> Save Changes').prop('disabled', false);
                }
            },
            error: function() {
                $('#profileErrors').removeClass('d-none').html('<div>An error occurred while updating your profile. Please try again.</div>');
                editProfileBtn.html('<i class="fas fa-save me-1"></i> Save Changes').prop('disabled', false);
            }
        });
    });
    
    // Password form submission with AJAX
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/change_password.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('#passwordForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
            },
            success: function(response) {
                if (response.success) {
                    $('#passwordSuccess').removeClass('d-none').text(response.message);
                    $('#passwordErrors').addClass('d-none');
                    $('#passwordForm')[0].reset();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Changed',
                        text: 'Your password has been updated successfully!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#changePasswordSection').addClass('d-none');
                    });
                } else {
                    $('#passwordErrors').removeClass('d-none').html(response.errors.map(error => `<div>${error}</div>`).join(''));
                }
                $('#passwordForm button[type="submit"]').prop('disabled', false).text('Change Password');
            },
            error: function() {
                $('#passwordErrors').removeClass('d-none').html('<div>An error occurred while changing your password. Please try again.</div>');
                $('#passwordForm button[type="submit"]').prop('disabled', false).text('Change Password');
            }
        });
    });
    
    // KYC form submission with AJAX
    $('#kycForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: 'ajax/submit_kyc.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function() {
                $('#kycForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...');
            },
            success: function(response) {
                if (response.success) {
                    $('#kycSuccess').removeClass('d-none').text(response.message);
                    $('#kycErrors').addClass('d-none');
                    $('#kycForm').addClass('d-none');
                    $('#otpVerificationSection').removeClass('d-none');
                    
                    // Show success message with SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'KYC Submitted',
                        text: 'We have sent an OTP to your email address. Please check your inbox and enter the code to complete verification.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    $('#kycErrors').removeClass('d-none').html(response.errors.map(error => `<div>${error}</div>`).join(''));
                    $('#kycForm button[type="submit"]').prop('disabled', false).text('Submit for Verification');
                }
            },
            error: function() {
                $('#kycErrors').removeClass('d-none').html('<div>An error occurred while submitting your KYC. Please try again.</div>');
                $('#kycForm button[type="submit"]').prop('disabled', false).text('Submit for Verification');
            }
        });
    });
    
    // OTP verification form submission
    $('#otpVerificationForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/verify_kyc_otp.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('#otpVerificationForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Verifying...');
            },
            success: function(response) {
                if (response.success) {
                    // Show success message with SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'KYC Verified!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page to update KYC status
                        location.reload();
                    });
                } else {
                    $('#kycErrors').removeClass('d-none').html(response.errors.map(error => `<div>${error}</div>`).join(''));
                    $('#otpVerificationForm button[type="submit"]').prop('disabled', false).text('Verify OTP');
                }
            },
            error: function() {
                $('#kycErrors').removeClass('d-none').html('<div>An error occurred while verifying OTP. Please try again.</div>');
                $('#otpVerificationForm button[type="submit"]').prop('disabled', false).text('Verify OTP');
            }
        });
    });
    
    // Resend OTP button
    $('#resendOtpBtn').click(function() {
        $.ajax({
            url: 'ajax/resend_kyc_otp.php',
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                $('#resendOtpBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Resent',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                } else {
                    $('#kycErrors').removeClass('d-none').html(response.errors.map(error => `<div>${error}</div>`).join(''));
                }
                $('#resendOtpBtn').prop('disabled', false).text('Resend OTP');
            },
            error: function() {
                $('#kycErrors').removeClass('d-none').html('<div>An error occurred while resending OTP. Please try again.</div>');
                $('#resendOtpBtn').prop('disabled', false).text('Resend OTP');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
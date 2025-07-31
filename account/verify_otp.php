<?php
session_start();
require_once __DIR__ . '/../config/dbconfig.php';
require_once __DIR__ . '/../config/functions.php';

// Check if OTP verification data exists
if (!isset($_SESSION['otp_verification'])) {
    header("Location: index.php");
    exit();
}

// Check if OTP has expired
if (time() > $_SESSION['otp_verification']['expires']) {
    unset($_SESSION['otp_verification']);
    $_SESSION['register_errors'] = ["OTP has expired. Please register again."];
    header("Location: index.php#registerModal");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    try {
        $userOtp = sanitizeInput($_POST['otp'] ?? '');
        $storedOtp = $_SESSION['otp_verification']['otp'];

        if (empty($userOtp)) {
            throw new Exception("OTP is required");
        }

        if ($userOtp != $storedOtp) {
            throw new Exception("Invalid OTP");
        }

        // OTP verified - create the user account
        $db = DBConfig::getInstance()->getConnection();
        
        $userData = $_SESSION['otp_verification'];
        $userName = trim($userData['first_name'] . ' ' . $userData['last_name']);
        
        $stmt = $db->prepare("INSERT INTO users 
                            (userName, userPass, userEmail, first_name, last_name, is_active, created_at) 
                            VALUES 
                            (?, ?, ?, ?, ?, 1, NOW())");
        
        $success = $stmt->execute([
            $userName,
            $userData['hashed_password'],
            $userData['email'],
            $userData['first_name'],
            $userData['last_name']
        ]);

        if (!$success) {
            throw new Exception("Failed to create user account");
        }

        // Clean up session
        unset($_SESSION['otp_verification']);
        
        $_SESSION['register_success'] = "Registration successful! You can now login.";
        header("Location: ../index.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['otp_error'] = $e->getMessage();
        header("Location: verify_otp.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- OTP Verification Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Verify Your Email</h2>
                <button onclick="window.location.href='../index.php'" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <?php if (isset($_SESSION['otp_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['otp_error']; unset($_SESSION['otp_error']); ?>
                </div>
            <?php endif; ?>
            
            <p class="text-gray-600 mb-6">
                We've sent a 6-digit OTP to <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['otp_verification']['email']); ?></span>
            </p>
            
            <form method="POST" class="space-y-4">
                <div class="flex justify-center space-x-3 mb-6">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input 
                        type="text" 
                        name="otp_digit_<?php echo $i; ?>" 
                        id="otp_digit_<?php echo $i; ?>" 
                        maxlength="1" 
                        pattern="\d" 
                        inputmode="numeric" 
                        autocomplete="one-time-code"
                        class="w-12 h-12 text-center text-xl border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                        required
                    >
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="otp" id="otp_hidden">
                
                <button 
                    type="submit" 
                    name="verify_otp"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200"
                >
                    Verify OTP
                </button>
                
                <div class="text-center text-sm text-gray-500 mt-4">
                    Didn't receive OTP? 
                    <a href="resend_otp.php" class="text-blue-600 hover:text-blue-800 font-medium">Resend OTP</a>
                </div>
            </form>
            
            <script>
                // Auto-focus and navigation between OTP inputs
                const inputs = Array.from(document.querySelectorAll('input[id^="otp_digit_"]'));
                
                // Focus first input on load
                inputs[0].focus();
                
                inputs.forEach((input, idx) => {
                    input.addEventListener('input', function() {
                        if (this.value.length === 1 && idx < inputs.length - 1) {
                            inputs[idx + 1].focus();
                        }
                        updateOtpHidden();
                    });
                    
                    input.addEventListener('keydown', function(e) {
                        if (e.key === "Backspace" && this.value === "" && idx > 0) {
                            inputs[idx - 1].focus();
                        }
                    });
                });
                
                function updateOtpHidden() {
                    document.getElementById('otp_hidden').value = inputs.map(i => i.value).join('');
                }
            </script>
        </div>
    </div>
</body>
</html>
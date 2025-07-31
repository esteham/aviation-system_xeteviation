<?php
require_once __DIR__ . '/config/dbconfig.php';
require_once __DIR__ . '/config/functions.php';

include_once __DIR__ . '/includes/header.php';

// Handle form submission
$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $msg = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($subject)) $errors[] = "Subject is required";
    if (empty($msg)) $errors[] = "Message is required";
    
    if (empty($errors)) {
        // Here you would typically:
        // 1. Send an email (using PHPMailer or similar)
        // 2. Store in database
        // 3. Log the contact attempt
        
        // For demonstration, we'll just show a success message
        $message = "Thank you, $name. Your message has been received. We'll respond within 24 hours.";
        $message_class = 'success';
        
        // Clear form fields
        $name = $email = $subject = $msg = '';
    } else {
        $message = implode("<br>", $errors);
        $message_class = 'error';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Aviation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #0ea5e9;
            --dark: #1e293b;
            --light: #f8fafc;
            --success: #10b981;
            --error: #ef4444;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .contact-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .contact-header h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .contact-header p {
            font-size: 1.1rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .contact-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            max-width: 800px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .contact-info h2 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .info-icon {
            background-color: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .info-content h3 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        
        .info-content p {
            color: #64748b;
        }
        
        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-group label {
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .success {
            background-color: #ecfdf5;
            color: var(--success);
            border: 1px solid #a7f3d0;
        }
        
        .error {
            background-color: #fef2f2;
            color: var(--error);
            border: 1px solid #fecaca;
        }
        
        @media (max-width: 768px) {
            .contact-card {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container pt-5">
        <div class="contact-header">
            <h1>Contact Aviation System</h1>
            <p>Have questions about our services or need support? Our team is here to help you with all your aviation needs.</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $message_class ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="contact-card">
            <div class="contact-info">
                <h2>Get in touch</h2>
                
                <div class="info-item">
                    <div class="info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="info-content">
                        <h3>Headquarters</h3>
                        <p>123 Aviation Way<br>Agargon, NY 10001</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <div class="info-content">
                        <h3>Email Us</h3>
                        <p>support@aviationsystem.com<br>sales@aviationsystem.com</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="info-content">
                        <h3>Call Us</h3>
                        <p>+ (800) 123-4567<br>Mon-Fri: 9am-6pm EST</p>
                    </div>
                </div>
            </div>
            
            <form method="post" action="" class="contact-form">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" value="<?= htmlspecialchars($subject ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea name="message" id="message" class="form-control" required><?= htmlspecialchars($msg ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    Send Message
                </button>
            </form>
        </div>
    </div>
<?php include_once __DIR__ . '/includes/footer.php'; ?>
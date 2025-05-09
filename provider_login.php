<?php
include_once 'includes/functions.php';

// Start session
session_start();

// Redirect if already logged in
if (isset($_SESSION['provider_id'])) {
    header('Location: provider_dashboard.php');
    exit();
}

$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password']; // Don't sanitize password before verification
    
    // Validate required fields
    if (empty($email) || empty($password)) {
        $error = "Both email and password are required!";
    } else {
        // Authenticate user
        $provider = authenticateProvider($email, $password);
        
        if ($provider) {
            // Set session variables
            $_SESSION['provider_id'] = $provider['id'];
            $_SESSION['provider_name'] = $provider['name'];
            $_SESSION['provider_email'] = $provider['email'];
            
            // Redirect to dashboard
            header('Location: provider_dashboard.php');
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Login - KaamBuddy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
    <?php include_once 'includes/header.php'; ?>

    <section class="login-section">
        <div class="container">
            <div class="login-form">
                <div class="form-title">
                    <h2>Provider Login</h2>
                    <p>Access your service provider account</p>
                </div>
                
                <div id="notification-area"></div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-box" style="display: none;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form action="provider_login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" class="submit-btn">Login</button>
                        <div class="form-links">
                            <a href="forgot_password.php">Forgot Password?</a>
                            <span class="divider">|</span>
                            <a href="register.php">Register as Provider</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>काम<span>Buddy</span></h2>
                    <p>Your Partner in Getting Things Done.</p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="search.php">Find Services</a></li>
                        <li><a href="register.php">Register as Provider</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-envelope"></i>
                    <a href="mailto:KaamBuddy.info@gmail.com?subject=Enquiry from KaamBuddy.info@gmail.com"> KaamBuddy.info@gmail.com</a>
                    <p><i class="fas fa-phone"></i> 
                    <a href="tel:+919019304426">+91 9019304426</a>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> KaamBuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
    <script src="js/notifications.js"></script>
</body>
</html> 